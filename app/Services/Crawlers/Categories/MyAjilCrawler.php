<?php

namespace App\Services\Crawlers\Categories;

use App\Helpers\ScraperHelper;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Facades\Log;
use Facebook\WebDriver\WebDriverWait;
use App\Helpers\ElementExtractorHelper;
use App\Abstractions\AbstractCategoriesCrawler;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\WebDriverException;

class MyAjilCrawler extends AbstractCategoriesCrawler
{
    /**
     * @override
     */
    public function fetchCategories(string $url, array $params, string $slug): array|\Illuminate\Support\Collection|\Throwable|\Exception
    {
        $elementExtractor = new ElementExtractorHelper();
        try {
            $driver = $this->driver->get();
            $url = rtrim($url, '/');
            $driver = $driver->driver;
            $driver->get($url);

            $elements = $elementExtractor->getElementByXPath($driver, '/html/body/div[1]/div[1]/div[2]/header[2]/div[1]/div[1]/div[1]/div[1]/div[1]/div[1]/nav[1]/ul[1]');

            // get a children
            $categoriesElements = $elements->findElements(WebDriverBy::className('awb-menu__main-a'));

            $categoriesCollection = [];

            // preparing category links
            foreach ($categoriesElements as $categoryElement) {
                $subCategoryLink = $categoryElement->getAttribute('href');
                $title = $categoryElement->getText();

                if (ScraperHelper::isStopWord($title) || $title == 'همه محصولات') {
                    Log::info("Category stop word : $title");
                    continue;
                }

                $categoriesCollection[] = [
                    'id' => ScraperHelper::generateUniqIdFromUrl($subCategoryLink),
                    'title' => $title,
                    'url' => $subCategoryLink,
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
