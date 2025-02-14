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

class FarakoCrawler extends AbstractCategoriesCrawler
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
            $megaMenuButton = $elementExtractor->getElementByXPath($driver, '//a[contains(normalize-space(@class), "CategoriesListButton")]');

            $driver->getMouse()->mouseMove($megaMenuButton->getCoordinates());

            // get div children
            $rightSideMenu = $driver->findElements(WebDriverBy::xpath('//div[contains(@class, "dropdown") and contains(@class, "megamenu")]/ul/li'));

            $categoriesCollection = [];
            foreach ($rightSideMenu as $menuItem) {
                $driver->getMouse()->mouseMove($menuItem->getCoordinates());

                $leftMenu = $menuItem->findElement(WebDriverBy::className('megamenu__leftside'));

                $anchorTags = $leftMenu->findElements(WebDriverBy::tagName('a'));

                foreach ($anchorTags as $anchorTag) {
                    $categoryURL = $anchorTag->getAttribute('href');

                    $categoriesCollection[] = [
                        'id' => ScraperHelper::generateUniqIdFromUrl($categoryURL),
                        'title' => $anchorTag->getText(),
                        'url' => $categoryURL,
                        'merchant_id' => $slug,
                    ];
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
}
