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

class JeanswestCrawler extends AbstractCategoriesCrawler
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
            $megaMenuButtons = [
                "/html/body/main[1]/header/div[1]/div[2]/div[2]/div[1]/div[1]/div[1]/div[2]",
                "/html/body/main[1]/header/div[1]/div[2]/div[2]/div[1]/div[1]/div[1]/div[3]",
                "/html/body/main[1]/header/div[1]/div[2]/div[2]/div[1]/div[1]/div[1]/div[4]",
            ];
            $wait = new WebDriverWait($driver, 10);
            $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath('/html/body/main[1]/header/div[1]/div[2]/div[2]/div[1]/div[1]/div[1]/div[2]')
                )
            );
            foreach ($megaMenuButtons as $megaMenuButton) {

                $megaMenuButtonElement = $elementExtractor->getElementByXPath($driver, $megaMenuButton);

                $driver->getMouse()->mouseMove($megaMenuButtonElement->getCoordinates());

                $linkWrapper = $megaMenuButtonElement->findElement(WebDriverBy::xpath('div[1]/div[1]/div[1]/div[1]/div[1]'));

                $anchorTags = $linkWrapper->findElements(WebDriverBy::tagName('a'));

                $categoriesCollection = [];

                foreach ($anchorTags as $anchorTag) {
                    $categoryURL = $url . $anchorTag->getAttribute('href');
                    $categoryTitle = $anchorTag->getText();

                    if ($categoryTitle) {
                        $categoriesCollection[] = [
                            'id' => ScraperHelper::generateUniqIdFromUrl($categoryURL),
                            'title' => $categoryTitle,
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
