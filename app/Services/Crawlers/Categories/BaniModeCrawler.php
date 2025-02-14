<?php

namespace App\Services\Crawlers\Categories;

use App\Helpers\ScraperHelper;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Facades\Log;
use App\Helpers\ElementExtractorHelper;
use App\Abstractions\AbstractCategoriesCrawler;
use Facebook\WebDriver\Exception\WebDriverException;

class BaniModeCrawler extends AbstractCategoriesCrawler
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
            $megaMenuButton = $elementExtractor->getElementByXPath($driver, '/html/body/div[3]/header[1]/nav[1]/div[1]/ul/li[1]');

            $driver->getMouse()->mouseMove($megaMenuButton->getCoordinates());


            // get div children
            $rightSideMenu = $megaMenuButton->findElements(WebDriverBy::className('category-details-li'));

            $categoriesCollection = [];
            foreach ($rightSideMenu as $menuItem) {
                $driver->getMouse()->mouseMove($menuItem->getCoordinates());
                $linkItems = $menuItem->findElements(WebDriverBy::className('third-child-li'));
                foreach ($linkItems as $linkItem) {
                    $anchorTag = $elementExtractor->getElementByName($driver, $linkItem, 'a');

                    $categoriesCollection[] = [
                        'id' => ScraperHelper::generateUniqIdFromUrl($url . $anchorTag->getAttribute('href')),
                        'title' => $anchorTag->getText(),
                        'url' => $url . $anchorTag->getAttribute('href'),
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
