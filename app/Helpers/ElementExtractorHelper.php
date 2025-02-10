<?php

namespace App\Helpers;

use Exception;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Facades\Log;

class ElementExtractorHelper
{
    public function extractStoreContainer(RemoteWebDriver $driver, $selector)
    {

        try {
            return $driver->findElement(WebDriverBy::cssSelector($selector));
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function extractProductCards($containerElement, $selector)
    {
        try {
            return $containerElement->findElements(WebDriverBy::cssSelector($selector));
        } catch (NoSuchElementException $e) {
            throw new \Exception('Error: Product card elements not found: ' . $e->getMessage());
        }
    }

    public function extractProductTitle($productElement, $selector)
    {
        try {
            return $productElement->findElement(WebDriverBy::cssSelector($selector));
        } catch (NoSuchElementException $e) {
            throw new \Exception('Error: Product title element not found: ' . $e->getMessage());
        }
    }

    public function extractProductPrice($productElement, $selector)
    {
        $elements = $productElement->findElements(WebDriverBy::cssSelector($selector));
        return empty($elements) ? null : $elements[0];
    }

    public function extractProductDiscount($productElement, $selector)
    {
        try {
            $elements = $productElement->findElements(WebDriverBy::cssSelector($selector));
            return empty($elements) ? null : $elements[0];
        } catch (NoSuchElementException $e) {
            return null;
            //throw new \Exception('Error: Product discount element not found: ' . $e->getMessage());
        }
    }

    public function extractProductUrl($productElement, $selector)
    {
        try {
            return $productElement->findElement(WebDriverBy::cssSelector($selector));
        } catch (NoSuchElementException $e) {
            throw new \Exception('Error: Product URL element not found: ' . $e->getMessage());
        }
    }

    public function extractProductImageUrl($productElement, $selector)
    {
        try {
            return $productElement->findElement(WebDriverBy::cssSelector($selector));
        } catch (NoSuchElementException $e) {
            Log::info('Error: Product image URL element not found: ' . $e->getMessage());
            return null;
            throw new \Exception('Error: Product image URL element not found: ' . $e->getMessage());
        }
    }

    public function extractCategoriesSelector(RemoteWebDriver $driver, $selector)
    {

        try {
            return $driver->findElements(WebDriverBy::cssSelector($selector));
        } catch (NoSuchElementException $e) {
            throw new \Exception('Error: Store container element not found: ' . $e->getMessage());
        }
    }

    public function extractTitleFromElements($element)
    {
        $title = $element->getText();

        if (empty($title)) {
            $title = $element->getAttribute('innerText');
        }

        if (empty($title)) {
            $title = $element->getAttribute('title');
        }

        if (empty($title)) {
            $title = $element->getAttribute('textContent');
        }

        if (empty($title)) {
            $title = $element->getAttribute('value');
        }

        if (empty($title)) {
            $title = $element->getDomProperty('innerHTML');
            //remove all html tags and \n and extra space
            $title = preg_replace('/\s+/', ' ', strip_tags($title));
        }

        // If $title is still empty or null, assign a default value if necessary
        return $title ?? ''; // Return an empty string if no title found
    }

    public function extractInStock($productElement, $selector)
    {
        try {
            return $productElement->findElement(WebDriverBy::cssSelector($selector));
        } catch (Exception $e) {
            return null;
        }
    }

    public function getElementByXPath($driver, $xpath)
    {
        try {
            return $driver->findElement(WebDriverBy::xpath($xpath));
        } catch (NoSuchElementException $e) {
            throw new \Exception('Error: Element not found: ' . $e->getMessage());
        }
    }

    public function getElementByName($driver,$element, $name)
    {
        try {
            return $element->findElement(WebDriverBy::tagName($name));
        } catch (NoSuchElementException $e) {
            throw new \Exception('Error: Element not found: ' . $e->getMessage());
        }
    }

}
