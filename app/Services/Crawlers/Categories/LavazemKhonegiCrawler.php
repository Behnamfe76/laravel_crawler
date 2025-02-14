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

class LavazemKhonegiCrawler extends AbstractCategoriesCrawler
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
            // find li>span with `دسته بندی محصولات` text and click on
            $elements = $elementExtractor->getElementByXPath($driver, '/html/body/div[1]/div[2]/div[1]/div[2]/div[1]/div[1]/div[1]/div[1]/div[1]');

            // get div children
            $parentCategoryElements = $elements->findElements(WebDriverBy::className('item'));

            $parentCategoriesCollection = [];
            $subCategoriesCollection = [];

            // preparing the parent category links
            foreach ($parentCategoryElements as $parentCategory) {
                $anchorTag = $elementExtractor->getElementByName($driver, $parentCategory, 'a');

                $parentCategoriesCollection[] = $url . $anchorTag->getAttribute('href');
            }

            // preparing the subcategories
            foreach (array_unique($parentCategoriesCollection) as $parentCategoryUrl) {
                $driver->get($parentCategoryUrl);
                $subCategoryElements = $elementExtractor->getElementByXPath($driver, '/html/body/div[1]/div[3]/div[1]/div[1]/div[1]//div[contains(@class, "wrapper")]/div[1]');

                $subCategories = $subCategoryElements->findElements(WebDriverBy::className('product-four-box'));

                foreach ($subCategories as $subCategory) {
                    $anchorTag = $elementExtractor->getElementByName($driver, $subCategory, 'a');
                    $boldTag = $elementExtractor->getElementByName($driver, $subCategory, 'b');

                    $subCategoryLink = $url . $anchorTag->getAttribute('href');
                    $subCategoriesCollection[] = [
                        'id' => ScraperHelper::generateUniqIdFromUrl($subCategoryLink),
                        'title' => $boldTag->getText(),
                        'url' => $subCategoryLink,
                        'merchant_id' => $slug,
                    ];
                }
            }

            return $subCategoriesCollection;
        } catch (WebDriverException $e) {
            Log::error("WebDriverException: " . $e->getMessage());

            return $e;
        } catch (\Throwable $tr) {
            Log::error("WebDriverException: " . $tr->getMessage());

            return $tr;
        }
    }
}
