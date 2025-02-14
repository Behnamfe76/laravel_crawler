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

class HajiBadoomiCrawler extends AbstractCategoriesCrawler
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
            $megaMenuButton = $elementExtractor->getElementByXPath($driver, '//strong[contains(text(), "محصولات")]');

            $driver->getMouse()->mouseMove($megaMenuButton->getCoordinates());

            // get div children
            $rightSideMenu = $driver->findElements(WebDriverBy::className('sminner-m7'));

            $categoriesCollection = [];
            foreach ($rightSideMenu as $menuItem) {
                $driver->getMouse()->mouseMove($menuItem->getCoordinates());

                $linkWrapper = $menuItem->findElement(WebDriverBy::xpath('div[1]/div[1]/div[2]/div[1]/ul'));
                $anchorTags = $linkWrapper->findElements(WebDriverBy::tagName('a'));

                foreach ($anchorTags as $anchorTag) {
                    $categoryURL = $url . $anchorTag->getAttribute('href');

                    if (!isset($categoriesCollection[$categoryURL])) {
                        $categoriesCollection[$categoryURL] = [
                            'id' => ScraperHelper::generateUniqIdFromUrl($categoryURL),
                            'title' => $anchorTag->getText(),
                            'url' => $categoryURL,
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
}
