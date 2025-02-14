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

class ShiferShoesCrawler extends AbstractCategoriesCrawler
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

            $navBar = $elementExtractor->getElementByXPath($driver, '/html/body/nav[1]/div[1]/ul[1]');

            $anchorTagMen = $elementExtractor->getElementByXPath($driver, '/html/body/nav[1]/div[1]/ul[1]/li/a[contains(normalize-space(text()), "مردانه")]');
            $anchorTagWomen = $elementExtractor->getElementByXPath($driver, '/html/body/nav[1]/div[1]/ul[1]/li/a[contains(normalize-space(text()), "زنانه")]');

            $categoriesCollection = [];
            $driver->getMouse()->mouseMove($anchorTagMen->getCoordinates());
            $megaMenu = $navBar->findElement(WebDriverBy::className('megamenu'));
            $menCategories = $megaMenu->findElements(WebDriverBy::className('dropdown-item'));
            foreach ($menCategories as $categoryElement) {
                $subCategoryLink = $categoryElement->getAttribute('href');
                $title = $categoryElement->getText();

                $categoriesCollection[] = [
                    'id' => ScraperHelper::generateUniqIdFromUrl($subCategoryLink),
                    'title' => $categoryElement->getText(),
                    'url' => $url . $subCategoryLink,
                    'merchant_id' => $slug,
                ];
            }

            $driver->getMouse()->mouseMove($anchorTagWomen->getCoordinates());
            $megaMenu = $navBar->findElement(WebDriverBy::className('megamenu'));
            $womenCategories = $megaMenu->findElements(WebDriverBy::className('dropdown-item'));

            foreach ($womenCategories as $categoryElement) {
                $subCategoryLink = $categoryElement->getAttribute('href');

                $categoriesCollection[] = [
                    'id' => ScraperHelper::generateUniqIdFromUrl($subCategoryLink),
                    'title' => $categoryElement->getText(),
                    'url' => $url . $subCategoryLink,
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
