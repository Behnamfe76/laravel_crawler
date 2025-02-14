<?php

namespace App\Abstractions;

use Carbon\Carbon;
use App\Models\SeleniumDriver;
use App\Services\RabbitMQService;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;
use App\Entities\SeleniumDriverEntity;
use App\Contracts\CategoriesCrawlerContract;
use App\Services\Selenium\SeleniumDriverClient;
use Facebook\WebDriver\Exception\WebDriverException;

abstract class AbstractCategoriesCrawler implements CategoriesCrawlerContract
{
    public null|SeleniumDriverClient $driver = null;
    public null|SeleniumDriver $driverEntity = null;

    protected int $duration = 0;
    protected Carbon $startedAt;
    protected Carbon $endAt;

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

    public function run(array $params, $slug, string $job)
    {
        try {
            ini_set('max_execution_time', 0);
            if (!$this->driver) {
                $this->setup();
            }

            $this->driverEntity->setIsWorking(true);
            $this->driverEntity->setWorkingSubject('categories');
            $this->startedAt = Carbon::now();

            $categories = $this->fetchCategories($params['merchant_url'], $params, $slug);

            if ($categories instanceof WebDriverException || $categories instanceof \Throwable) {
                throw $categories;
            }

            if (!is_array($categories)) {
                throw new \Exception("Failed to fetch categories.");
            }

            $this->endAt = Carbon::now();
            $this->duration = $this->startedAt->diffInRealSeconds($this->endAt);

            $this->driverEntity->setWorkingData([
                'crawling_type' => 'crawling',
                'crawling_subject' => 'categories',
                'crawling_url' => $params['merchant_url'],
                'merchant' => $params['merchant_id'],
                'started_at' => $this->startedAt,
                'end_at' => $this->endAt,
                'duration' => $this->duration,
                'numberOfCrawledCategories' => count($categories),
                'job' => $job
            ]);

            $this->store([[
                'scope' => 'categories',
                'categories' => $categories,
                'reports' => $this->driverEntity->getWorkingData(),
                'job' => $job
            ]], $slug);
        } catch (\Throwable $tr) {
            Log::error($tr->getMessage());

            return $tr;
        } finally {
            // Ensure the driver is reset even if an error occurs
            $this->driverEntity->setIsWorking(false);
            $this->driverEntity->setDuration($this->duration);
            $this->driverEntity->setLastUsage($this->startedAt);

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
            $this->startedAt = Carbon::now();

            $categories = $this->fetchCategories($params['merchant_url'], $params, $slug);

            if(empty($categories)){
                throw new \Exception('failed to crawl Categories');
            }

            $this->endAt = Carbon::now();
            $this->duration = $this->startedAt->diffInSeconds($this->endAt);
            $this->driverEntity->setDuration($this->duration);
            $this->driverEntity->setLastUsage($this->startedAt);

            $this->driverEntity->setWorkingData([
                'crawling_type' => 'testing',
                'crawling_subject' => 'categories',
                'crawling_url' => $params['merchant_url'],
                'merchant' => $params['merchant_id'],
                'started_at' => $this->startedAt,
                'end_at' => $this->endAt,
                'duration' => $this->duration,
                'numberOfCrawledCategories' => count($categories),
            ]);

            $this->driverEntity->setIsWorking(false);
            $driver = $this->driver->get();
            $driver->close();

            return [
                'categories' => $categories,
                'workingData' => $this->driverEntity->getWorkingData()
            ];
        } catch (\Throwable $tr) {
            Log::error($tr->getMessage());

            return $tr;
        } finally {
            // Ensure the driver is reset even if an error occurs
            $this->driverEntity->setIsWorking(false);
            $this->driverEntity->setDuration($this->duration);
            $this->driverEntity->setLastUsage($this->startedAt);

            if ($this->driver) {
                $driver = $this->driver->get();
                if ($driver) {
                    $driver->close();
                }
            }
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
