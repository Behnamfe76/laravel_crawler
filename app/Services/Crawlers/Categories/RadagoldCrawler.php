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

class RadagoldCrawler extends AbstractCategoriesCrawler
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

            $wait = new WebDriverWait($driver, 10);
            $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath('/html/body/div[contains(@class, "website-wrapper")]/header[1]/div[1]/div[3]/div[1]/div[1]/div[2]/div[1]/ul')
                )
            );

            $megaMenuButton = $elementExtractor->getElementByXPath($driver, '/html/body/div[contains(@class, "website-wrapper")]/header[1]/div[1]/div[3]/div[1]/div[1]/div[2]/div[1]/ul[1]/li[2]');

            $driver->getMouse()->mouseMove($megaMenuButton->getCoordinates());

            $categoryWrapper = $megaMenuButton->findElement(WebDriverBy::xpath("div[1]/div[1]/ul"));
            $anchorTags = $categoryWrapper->findElements(WebDriverBy::tagName('a'));

            $categoriesCollection = [];
            foreach ($anchorTags as $anchorTag) {
                $subCategoryLink = $anchorTag->getAttribute('href');
                $title = $anchorTag->getText();

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
