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

class TrendyaabCrawler extends AbstractCategoriesCrawler
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
                    WebDriverBy::xpath('/html/body/div[1]/div[1]/header[1]/div[3]/div[1]/div[1]/div[1]/button/span[4]')
                )
            );

            $megaMenuButton = $elementExtractor->getElementByXPath($driver, '/html/body/div[1]/div[1]/header[1]/div[3]/div[1]/div[1]/div[1]');
            $driver->getMouse()->mouseMove($megaMenuButton->getCoordinates());

            $parentCategoryElements = $driver->findElements(WebDriverBy::className('mega-menu-item'));
            $previousElement = null;
            foreach ($parentCategoryElements as $parentCategoryElement) {

                if ($previousElement) {
                    $driver->executeScript("arguments[0].classList.remove('active');", [$previousElement]);
                }

                $driver->executeScript("arguments[0].classList.add('active');", [$parentCategoryElement]);

                $previousElement = $parentCategoryElement;

                usleep(500000);

                $anchorTags = $parentCategoryElement->findElements(WebDriverBy::tagName('a'));
                foreach ($anchorTags as $anchorTag) {
                    $subCategoryLink = $anchorTag->getAttribute('href');

                    $categoriesCollection[] = [
                        'id' => ScraperHelper::generateUniqIdFromUrl($subCategoryLink),
                        'title' => $anchorTag->getText(),
                        'url' => $url . $subCategoryLink,
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
