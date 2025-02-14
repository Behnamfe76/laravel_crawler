<?php

namespace App\Abstractions;

use App\Contracts\CategoriesCrawlerContract;
use App\Entities\SeleniumDriverEntity;
use App\Helpers\ElementExtractorHelper;
use App\Helpers\ScraperHelper;
use App\Models\SeleniumDriver;
use App\Services\RabbitMQService;
use App\Services\Selenium\SeleniumDriverClient;
use Carbon\Carbon;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;

abstract class AbstractCategoriesCrawler implements CategoriesCrawlerContract
{
    public null|SeleniumDriverClient $driver = null;
    public null|SeleniumDriver $driverEntity = null;

    public function setup(): void
    {
        $singletonDriver = app(SeleniumDriverEntity::class);
        $driverEntity = $singletonDriver->get();

        if (empty($driverEntity)) {
            throw new \Exception('No available driver');
        }

        $this->driverEntity = $driverEntity;
        $driver = new SeleniumDriverClient();
        $driver->setup($driverEntity->host, $driverEntity->port);

        $this->driver = $driver;
    }

    public function run(array $params, $slug, string $job): void
    {
        try {
            ini_set('max_execution_time', 0);
            if (!$this->driver) {
                $this->setup();
            }

            $this->driverEntity->setIsWorking(true);
            $this->driverEntity->setWorkingSubject('categories');
            $startedAt = Carbon::now();

            $categories = $this->fetchCategories($params['merchant_url'], $params, $slug);

            if (!is_array($categories)) {
                throw new \Exception("Failed to fetch categories.");
            }

            $endAt = Carbon::now();
            $duration = $startedAt->diffInRealSeconds($endAt);

            $this->driverEntity->setWorkingData([
                'crawling_type' => 'crawling',
                'crawling_subject' => 'categories',
                'crawling_url' => $params['merchant_url'],
                'merchant' => $params['merchant_id'],
                'started_at' => $startedAt,
                'end_at' => $endAt,
                'duration' => $duration,
                'numberOfCrawledCategories' => count($categories),
                'job' => $job
            ]);

            $this->store([[
                'categories' => $categories,
                'reports' => $this->driverEntity->getWorkingData(),
                'job' => $job
            ]], $slug);
        } catch (\Throwable $tr) {
            Log::error($tr->getMessage());
        } finally {
            // Ensure the driver is reset even if an error occurs
            $this->driverEntity->setIsWorking(false);
            $this->driverEntity->setDuration($duration);
            $this->driverEntity->setLastUsage($startedAt);

            if ($this->driver) {
                $driver = $this->driver->get();
                if ($driver) {
                    $driver->close();
                }
            }
        }
    }

    public function test(array $params, $slug): array|\Throwable|\Exception
    {
        try {
            ini_set('max_execution_time', 0);
            if (!$this->driver) {
                $this->setup();
            }

            $this->driverEntity->setIsWorking(true);
            $this->driverEntity->setWorkingSubject('categories');
            $startedAt = Carbon::now();

            $categories = $this->fetchCategories($params['merchant_url'], $params, $slug);

            $endAt = Carbon::now();
            $duration = $startedAt->diffInSeconds($endAt);
            $this->driverEntity->setDuration($duration);
            $this->driverEntity->setLastUsage($startedAt);

            $this->driverEntity->setWorkingData([
                'crawling_type' => 'testing',
                'crawling_subject' => 'categories',
                'crawling_url' => $params['merchant_url'],
                'merchant' => $params['merchant_id'],
                'started_at' => $startedAt,
                'end_at' => $endAt,
                'duration' => $duration,
                'numberOfCrawledCategories' => count($categories),
            ]);

            $this->driverEntity->setIsWorking(false);
            $driver = $this->driver->get();
            $driver->close();

            return $this->driverEntity->getWorkingData();
        } catch (\Throwable $tr) {
            dd($tr->getMessage());
            return $tr;
        }
    }

    public function fetchCategories(string $url, array $params, string $slug): array|\Illuminate\Support\Collection|\Throwable|\Exception
    {
        try {

            return [];
        } catch (\Throwable $tr) {
            Log::error("WebDriverException: " . $tr->getMessage());

            return $tr;
        }
    }

    public function store(array $data, $slug): \Throwable|\Exception|int
    {
        try {
            $exchangeName = 'categories_exchange';
            $queueName = 'categories_queue';
            $routingKey = 'categories_routing_key';

            return $this->storeInRabbitMQ($data, $slug, $exchangeName, $queueName, $routingKey);
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function storeInRabbitMQ(array $data, $slug, $exchangeName, $queueName, $routingKey): int
    {
        try {
            $connection = RabbitMQService::getConnection();
            $channel = $connection->channel();

            $channel->exchange_declare($exchangeName, 'direct', false, true, false);
            $channel->queue_declare($queueName, false, true, false, false);
            $channel->queue_bind($queueName, $exchangeName, $routingKey);
            foreach ($data as $item) {
                $msg = new AMQPMessage(json_encode([
                    'data' => $item,
                    'slug' => $slug,
                ], JSON_UNESCAPED_UNICODE), ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
                $channel->basic_publish($msg, $exchangeName, $routingKey);
            }
            $channel->close();
            $connection->close();
            return 1;
        } catch (\Exception $e) {
            Log::error('RabbitMQ Connection Error: ' . $e->getMessage());
            return -1;
        }
    }
}
