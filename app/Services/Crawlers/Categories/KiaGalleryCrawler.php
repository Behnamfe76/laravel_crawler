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

class KiaGalleryCrawler extends AbstractCategoriesCrawler
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
                    WebDriverBy::xpath('/html/body/div[3]/div[2]/nav[1]/div[1]/ul')
                )
            );

            $navWrapper = $elementExtractor->getElementByXPath($driver, '/html/body/div[3]/div[2]/nav[1]/div[1]/ul');
            $shopMegaMenu = $navWrapper->findElement(WebDriverBy::xpath('li/a/span[contains(text(), "فروشگاه")]'));
            $driver->getMouse()->mouseMove($shopMegaMenu->getCoordinates());
            $linkWrappers = $navWrapper->findElements(WebDriverBy::xpath('li[2]/div/div/div[2]/div[1]/div[contains(@class, "sub-categories")]'));

            $categoriesCollection = [];
            foreach ($linkWrappers as $linkWrapper) {
                $anchorTags = $linkWrapper->findElements(WebDriverBy::tagName('a'));
                foreach ($anchorTags as $anchorTag) {
                    $subCategoryLink = $anchorTag->getAttribute('href');

                    $categoriesCollection[] = [
                        'id' => ScraperHelper::generateUniqIdFromUrl($subCategoryLink),
                        'title' => $anchorTag->getText(),
                        'url' => rtrim($url, '/fa') . $subCategoryLink,
                        'merchant_id' => $slug,
                    ];
                }
            }


            $galleryMegaMenu = $navWrapper->findElement(WebDriverBy::xpath('li/a/span[contains(text(), "گالری")]'));
            $driver->getMouse()->mouseMove($galleryMegaMenu->getCoordinates());
            $linkWrappers = $navWrapper->findElements(WebDriverBy::xpath('li[3]/div/div/div[2]/div[1]/div[contains(@class, "sub-categories")]'));
            foreach ($linkWrappers as $linkWrapper) {
                $anchorTags = $linkWrapper->findElements(WebDriverBy::tagName('a'));
                foreach ($anchorTags as $anchorTag) {
                    $subCategoryLink = $anchorTag->getAttribute('href');

                    $categoriesCollection[] = [
                        'id' => ScraperHelper::generateUniqIdFromUrl($subCategoryLink),
                        'title' => $anchorTag->getText(),
                        'url' => rtrim($url, '/fa') . $subCategoryLink,
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
