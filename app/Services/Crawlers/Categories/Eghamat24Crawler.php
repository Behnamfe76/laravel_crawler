<?php

namespace App\Services\Crawlers\Categories;

use Carbon\Carbon;
use App\Helpers\ScraperHelper;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Facades\Log;
use Facebook\WebDriver\WebDriverWait;
use App\Helpers\ElementExtractorHelper;
use App\Abstractions\AbstractCategoriesCrawler;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\WebDriverException;

class Eghamat24Crawler extends AbstractCategoriesCrawler
{
    protected int $duration = 0;
    protected Carbon $startedAt;
    protected Carbon $endAt;
    /**
     * @override
     */
    public function run(array $params, $slug, string $job): void
    {
        try {
            ini_set('max_execution_time', 0);
            if (!$this->driver) {
                $this->setup();
            }

            $this->driverEntity->setIsWorking(true);
            $this->driverEntity->setWorkingSubject('categories');

            // fetching hotels
            $this->startedAt = Carbon::now();
            $hotelCategories = $this->fetchHotels($params['merchant_url'], $params, $slug);
            if (!is_array($hotelCategories)) {
                throw new \Exception("Failed to fetch hotel categories.");
            }
            $this->endAt = Carbon::now();
            $this->duration = $this->startedAt->diffInRealSeconds($this->endAt);
            $this->driverEntity->setWorkingData([
                'crawling_type' => 'crawling',
                'crawling_subject' => 'hotel categories',
                'crawling_url' => $params['merchant_url'],
                'merchant' => $params['merchant_id'],
                'started_at' => $this->startedAt,
                'end_at' => $this->endAt,
                'duration' => $this->duration,
                'numberOfCrawledCategories' => count($hotelCategories),
                'job' => $job
            ]);
            $this->store([[
                'scope' => 'categories',
                'categories' => $hotelCategories,
                'reports' => $this->driverEntity->getWorkingData(),
                'job' => $job
            ]], $slug);

            sleep(3);

            // fetch tours
            $this->startedAt = Carbon::now();
            $tourCategories = $this->fetchTours($params['merchant_url'], $params, $slug);
            if (empty($tourCategories)) {
                throw new \Exception("Failed to fetch tour categories.");
            }
            $this->endAt = Carbon::now();
            $this->duration = $this->startedAt->diffInRealSeconds($this->endAt);
            $this->driverEntity->setWorkingData([
                'crawling_type' => 'crawling',
                'crawling_subject' => 'tour categories',
                'crawling_url' => $params['merchant_url'],
                'merchant' => $params['merchant_id'],
                'started_at' => $this->startedAt,
                'end_at' => $this->endAt,
                'duration' => $this->duration,
                'numberOfCrawledCategories' => count($tourCategories),
                'job' => $job
            ]);
            $this->store([[
                'scope' => 'categories',
                'categories' => $tourCategories,
                'reports' => $this->driverEntity->getWorkingData(),
                'job' => $job
            ]], $slug);

        } catch (\Throwable $tr) {
            Log::error($tr->getMessage());
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

    /**
     * @override
     */
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

            $categories = $this->fetchTours($params['merchant_url'], $params, $slug);
            if (empty($categories)) {
                throw new \Exception('crawling failed');
            }
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

    protected function fetchHotels(string $url, array $params, string $slug): array|\Illuminate\Support\Collection|\Throwable|\Exception
    {
        try {
            $driver = $this->driver->get();
            $driver = $driver->driver;

            $hotelCategories = [
                'https://www.eghamat24.com/IranHotels.html',
                'https://www.eghamat24.com/InternationalHotels.html',
            ];
            $categoriesCollection = [];

            // getting hotels
            foreach ($hotelCategories as $hotelCategoryURL) {
                $url = rtrim($hotelCategoryURL, '/');
                $driver->get($url);

                $wait = new WebDriverWait($driver, 10);
                $wait->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(
                        WebDriverBy::xpath('/html/body/div[contains(@id, "app")]/main[1]/div[1]/div[contains(@class, "properties__grid")]')
                    )
                );

                $mainContainer = $driver->findElement(WebDriverBy::xpath('/html/body/div[contains(@id, "app")]/main[1]/div[1]/div[contains(@class, "properties__grid")]'));
                $containers = $mainContainer->findElements(WebDriverBy::xpath('./*'));
                foreach ($containers as $container) {
                    // $title = $container->findElement(WebDriverBy::tagName('h3'))->getText();

                    $anchorTags = $container->findElements(WebDriverBy::tagName('a'));

                    foreach ($anchorTags as $anchorTag) {
                        $anchorTagHref = $anchorTag->getAttribute('href');
                        $categoriesCollection[] = [
                            'id' => ScraperHelper::generateUniqIdFromUrl($anchorTagHref),
                            'title' => $anchorTag->getText(),
                            'url' => $anchorTagHref,
                            'merchant_id' => $slug,
                        ];
                    }
                }
            }

            return $categoriesCollection;
        } catch (WebDriverException $e) {
            Log::error("WebDriverException: " . $e->getMessage());

            return $e;
        } catch (\Throwable $tr) {
            Log::error("WebDriverException: " . $tr->getMessage());

            return $tr;
        }
    }

    protected function fetchTours(string $url, array $params, string $slug): array|\Illuminate\Support\Collection|\Throwable|\Exception
    {
        try {
            $driver = $this->driver->get();
            $driver = $driver->driver;

            // getting tours
            $driver->get('https://www.eghamat24.com/Tours.html');

            $wait = new WebDriverWait($driver, 10);
            $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath('/html/body/div[contains(@id, "app")]/main[1]/div[contains(@class, "tours")]')
                )
            );
            $container = $driver->findElement(WebDriverBy::xpath('/html/body/div[contains(@id, "app")]/main[1]/div[contains(@class, "tours")]'));
            $anchorWrappers = $container->findElements(WebDriverBy::className('col-md-4'));

            foreach ($anchorWrappers as $anchorWrapper) {
                $anchorTag = $anchorWrapper->findElement(WebDriverBy::tagName('a'));
                $anchorTagHref = $anchorTag->getAttribute('href');

                $categoriesCollection[] = [
                    'id' => ScraperHelper::generateUniqIdFromUrl($anchorTagHref),
                    'title' => $anchorTag->getText(),
                    'url' => $params['merchant_url'] . $anchorTagHref,
                    'merchant_id' => $slug,
                ];
            }

            return $categoriesCollection;
        } catch (WebDriverException $e) {
            Log::error("WebDriverException: " . $e->getMessage());

            return $e;
        } catch (\Throwable $tr) {
            Log::error("WebDriverException: " . $tr->getMessage());

            return $tr;
        }
    }
}
