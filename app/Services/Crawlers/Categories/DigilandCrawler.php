<?php

namespace App\Services\Crawlers\Categories;

use App\Helpers\ScraperHelper;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Facades\Log;
use Facebook\WebDriver\WebDriverWait;
use App\Helpers\ElementExtractorHelper;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use App\Abstractions\AbstractCategoriesCrawler;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\WebDriverException;

class DigilandCrawler extends AbstractCategoriesCrawler
{
    private string $megaMenuButtonXPath = "/html/body/div[1]/main/div[1]/div/section[1]/section[1]/header[1]/div[2]/div[1]/div[1]/nav[1]";
    private array $subCategoriesCollection = [];
    private array $tempSubCategoryElements = [];
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

            [$rightSideMenuItems] = $this->openMegaMenu($driver, $elementExtractor);

            define('NUMBER_OF_RIGHT_SIDE_MENU_ITEMS', count($rightSideMenuItems));

            $counter = 0;

            // fetching parent categories
            do {
                [$rightSideMenuItems, $wait] = $this->openMegaMenu($driver, $elementExtractor);

                // Hover over the right-side menu items
                $driver->getMouse()->mouseMove($rightSideMenuItems[$counter]->getCoordinates());

                // preparing the general categories
                $this->gettingDigilandGeneralCategories($wait, $driver, $elementExtractor, $slug);

                $counter++;
            } while ($counter < NUMBER_OF_RIGHT_SIDE_MENU_ITEMS);


            // fetching subcategories
            $counter = 0;
            do {
                [$rightSideMenuItems, $wait] = $this->openMegaMenu($driver, $elementExtractor);

                // Hover over the right-side menu items
                $driver->getMouse()->mouseMove($rightSideMenuItems[$counter]->getCoordinates());

                // preparing subcategories
                $this->gettingDigilandSubCategoryElements($wait, $driver, $elementExtractor);

                $counter++;
            } while ($counter < NUMBER_OF_RIGHT_SIDE_MENU_ITEMS);

            $this->fetchingSubCategories($driver, $elementExtractor, $slug);


            return $this->subCategoriesCollection;
        } catch (WebDriverException $e) {
            Log::error("WebDriverException: " . $e->getMessage());

            return $e;
        } catch (\Throwable $tr) {
            Log::error("WebDriverException: " . $tr->getMessage());

            return $tr;
        }
    }
    protected function openMegaMenu(RemoteWebDriver  $driver, ElementExtractorHelper $elementExtractor)
    {
        // waiting until the element is loaded
        $wait = new WebDriverWait($driver, 15);
        $wait->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath($this->megaMenuButtonXPath))
        );

        // find with `دسته بندی محصولات` text and hover on
        $categoryMenuButton = $elementExtractor->getElementByXPath($driver, $this->megaMenuButtonXPath);

        $driver->getMouse()->mouseMove($categoryMenuButton->getCoordinates());

        // finding ride side menu
        $wait->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath("/html/body/div[1]/main/div[1]/div/section[1]/section[1]/header[1]/div[2]/div[1]/div[1]/nav/div[2]/div[1]/div[1]/div[1]"))
        );
        $categoryMenu = $elementExtractor->getElementByXPath($driver, "/html/body/div[1]/main/div[1]/div/section[1]/section[1]/header[1]/div[2]/div[1]/div[1]/nav/div[2]/div[1]/div[1]/div[1]");
        $rightSideMenuItems = $categoryMenu->findElements(WebDriverBy::tagName('span'));

        return [$rightSideMenuItems, $wait];
    }
    protected function gettingDigilandGeneralCategories(WebDriverWait $wait, RemoteWebDriver  $driver, ElementExtractorHelper $elementExtractor, string $slug)
    {
        try {
            $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath("/html/body/div[1]/main/div[1]/div/section[1]/section[1]/header[1]/div[2]/div[1]/div[1]/nav/div[2]/div[1]/div[1]/div[2]/div[1]")
                )
            );

            // Adding general category
            $generalCategory = $elementExtractor->getElementByXPath(
                $driver,
                "/html/body/div[1]/main/div[1]/div/section[1]/section[1]/header[1]/div[2]/div[1]/div[1]/nav/div[2]/div[1]/div[1]/div[2]/div[1]/h2[1]/span[1]"
            );

            $previousURL = $driver->getCurrentURL();
            $generalCategoryTitle = $generalCategory->getText();
            $generalCategory->click();

            // Wait for the URL to change
            $wait->until(function ($driver) use ($previousURL) {
                return $driver->getCurrentURL() !== $previousURL;
            });

            // Collect the current URL and other information
            $currentURL = $driver->getCurrentURL();
            $this->subCategoriesCollection[] = [
                'id' => ScraperHelper::generateUniqIdFromUrl($currentURL),
                'title' => $generalCategoryTitle,
                'url' => $currentURL,
                'merchant_id' => $slug,
            ];

            // Navigate back to the previous page
            $driver->navigate()->back();

            // Wait for the page to reload
            $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath($this->megaMenuButtonXPath)
                )
            );
        } catch (\Throwable $th) {
            return $th;
        }
    }
    protected function gettingDigilandSubCategoryElements(WebDriverWait $wait, RemoteWebDriver  $driver, ElementExtractorHelper $elementExtractor)
    {
        try {
            $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath("/html/body/div[1]/main/div[1]/div/section[1]/section[1]/header[1]/div[2]/div[1]/div[1]/nav/div[2]/div[1]/div[1]/div[2]/div[1]")
                )
            );


            $subCategoriesWrapper = $elementExtractor->getElementByXPath(
                $driver,
                "/html/body/div[1]/main/div[1]/div/section[1]/section[1]/header[1]/div[2]/div[1]/div[1]/nav/div[2]/div[1]/div[1]/div[2]/div[1]/div[1]"
            );

            $subCategoryWrappers = $subCategoriesWrapper->findElements(WebDriverBy::tagName('div'));

            foreach ($subCategoryWrappers as $subCategoryWrapper) {
                $subCategories = $subCategoryWrapper->findElements(WebDriverBy::tagName('p'));

                for ($i = 0; $i < count($subCategories); $i++) {
                    if ($i === 0) {
                        continue;
                    }
                    $this->tempSubCategoryElements[] = $subCategories[$i]->getText();
                }
            }
        } catch (\Throwable $th) {
            return $th;
        }
    }
    protected function fetchingSubCategories(RemoteWebDriver $driver, ElementExtractorHelper $elementExtractor, string $slug)
    {
        try {
            if (count($this->tempSubCategoryElements)) {
                $element = array_shift($this->tempSubCategoryElements);
                [$rightSideMenuItems, $wait] = $this->openMegaMenu($driver, $elementExtractor);

                $endOfIteration = false;
                $counter = 0;
                do {
                    if ($endOfIteration) {
                        break;
                    }
                    // Hover over the right-side menu items
                    $driver->getMouse()->mouseMove($rightSideMenuItems[$counter]->getCoordinates());

                    $wait->until(
                        WebDriverExpectedCondition::presenceOfElementLocated(
                            WebDriverBy::xpath("/html/body/div[1]/main/div[1]/div/section[1]/section[1]/header[1]/div[2]/div[1]/div[1]/nav/div[2]/div[1]/div[1]/div[2]/div[1]")
                        )
                    );


                    $subCategoriesWrapper = $elementExtractor->getElementByXPath(
                        $driver,
                        "/html/body/div[1]/main/div[1]/div/section[1]/section[1]/header[1]/div[2]/div[1]/div[1]/nav/div[2]/div[1]/div[1]/div[2]/div[1]/div[1]"
                    );
                    $subCategoryWrappers = $subCategoriesWrapper->findElements(WebDriverBy::tagName('div'));

                    for ($j = 0; $j < count($subCategoryWrappers); $j++) {
                        if ($endOfIteration) {
                            break;
                        }
                        $subCategories = $subCategoryWrappers[$j]->findElements(WebDriverBy::tagName('p'));

                        for ($i = 0; $i < count($subCategories); $i++) {
                            if ($i === 0) {
                                continue;
                            }
                            if ($subCategories[$i]->getText() === $element) {
                                $previousURL = $driver->getCurrentURL();
                                $subCategories[$i]->click();
                                // Wait for the URL to change
                                $wait->until(function ($driver) use ($previousURL) {
                                    return $driver->getCurrentURL() !== $previousURL;
                                });

                                // Collect the current URL and other information
                                $currentURL = $driver->getCurrentURL();
                                $this->subCategoriesCollection[] = [
                                    'id' => ScraperHelper::generateUniqIdFromUrl($currentURL),
                                    'title' => $element,
                                    'url' => $currentURL,
                                    'merchant_id' => $slug,
                                ];
                                // $driver->navigate()->back();

                                // // Wait for the page to reload
                                // $wait->until(
                                //     WebDriverExpectedCondition::presenceOfElementLocated(
                                //         WebDriverBy::xpath($this->megaMenuButtonXPath)
                                //     )
                                // );
                                $endOfIteration = true;
                                break;
                            }
                        }
                    }

                    $counter++;
                } while ($counter < count($rightSideMenuItems));

                if (count($this->tempSubCategoryElements)) {
                    $this->fetchingSubCategories($driver, $elementExtractor, $slug);
                }
            }
        } catch (\Throwable $th) {
            return $th;
        }
    }
}
