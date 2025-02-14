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

class BornosModeCrawler extends AbstractCategoriesCrawler
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

            $categoriesCollection = [];

            // getting right side nav-links categories
            $rightNavList = $elementExtractor->getElementByXPath($driver, '/html/body/header[1]/div[1]/div[1]/div[1]/div[1]/nav/ul');
            $rightNavLinks = $rightNavList->findElements(WebDriverBy::xpath('./*'));
            for ($i = 0; $i < count($rightNavLinks); $i++) {
                if ($i !== 0) {
                    $anchorTagHref = $elementExtractor->getElementByName($driver, $rightNavLinks[$i], 'a')->getAttribute('href');
                    $anchorTagText = $elementExtractor->getElementByName($driver, $rightNavLinks[$i], 'a')->getText();

                    $categoriesCollection[] = [
                        'id' => ScraperHelper::generateUniqIdFromUrl($anchorTagHref),
                        'title' => $anchorTagText,
                        'url' => $anchorTagHref,
                        'merchant_id' => $slug,
                    ];
                } else {
                    $driver->getMouse()->mouseMove($rightNavLinks[$i]->getCoordinates());
                    sleep(1);
                    $anchorTags = $rightNavLinks[$i]->findElements(WebDriverBy::tagName('a'));
                    foreach ($anchorTags as $anchorTag) {
                        $anchorTagHref = $anchorTag->getAttribute('href');
                        if ($anchorTagHref && $anchorTagHref !== '#' && $anchorTagHref !== 'https://bornosmode.com/shop/') {

                            $categoriesCollection[] = [
                                'id' => ScraperHelper::generateUniqIdFromUrl($anchorTagHref),
                                'title' => $anchorTag->getText(),
                                'url' => $anchorTagHref,
                                'merchant_id' => $slug,
                            ];
                        }
                    }
                }
            }

            // getting left side nav-links categories
            $leftNavList = $elementExtractor->getElementByXPath($driver, '/html/body/header[1]/div[1]/div[1]/div[3]/div[1]/ul');
            $leftNavLinks = $leftNavList->findElements(WebDriverBy::xpath('./*'));
            for ($i = 0; $i < count($leftNavLinks); $i++) {
                if ($i === 0) {
                    $anchorTagHref = $elementExtractor->getElementByName($driver, $leftNavLinks[$i], 'a')->getAttribute('href');
                    $anchorTagText = $elementExtractor->getElementByName($driver, $leftNavLinks[$i], 'a')->getText();

                    $categoriesCollection[] = [
                        'id' => ScraperHelper::generateUniqIdFromUrl($anchorTagHref),
                        'title' => $anchorTagText,
                        'url' => $anchorTagHref,
                        'merchant_id' => $slug,
                    ];
                }
                $anchorTagText = $elementExtractor->getElementByName($driver, $leftNavLinks[$i], 'a')->getText();
                if ($anchorTagText === 'برندها') {
                    $leftNavLinks[$i]->click();
                    $wait->until(
                        WebDriverExpectedCondition::presenceOfElementLocated(
                            WebDriverBy::xpath('//div[contains(@class, "brands")]')
                        )
                    );

                    $brands = $driver->findElements(WebDriverBy::xpath('//div[contains(@class, "brands")]/div[contains(@class, "brand")]'));
                    foreach ($brands as $brand) {
                        $anchorTag = $brand->findElement(WebDriverBy::tagName('a'));

                        $anchorTagHref = $anchorTag->getAttribute('href');
                        $categoriesCollection[] = [
                            'id' => ScraperHelper::generateUniqIdFromUrl($anchorTagHref),
                            'title' => $anchorTag->getText(),
                            'url' => $anchorTagHref,
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
