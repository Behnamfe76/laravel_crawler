<?php

namespace App\Helpers;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Illuminate\Support\Facades\Log;

class ScraperHelper
{
    public static function isScrollable($driver)
    {
        $scrollHeight = $driver->executeScript('return document.body.scrollHeight;');
        $scrollPosition = $driver->executeScript('return window.pageYOffset;');
        $windowHeight = $driver->executeScript('return window.innerHeight;');

        return $scrollHeight > $scrollPosition + $windowHeight;
    }

    public static function stringToInt($string)
    {
        //Convert all Arabic and Persian numbers to English numbers
        $persianArabicDigits = [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', // Persian digits
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'  // Arabic digits
        ];
        $string = strtr($string, $persianArabicDigits);

        // Conversion logic for string to integer, possibly removing formatting
        return (int)preg_replace('/\D/', '', $string);
    }

    public static function removeTomanAndOther($string)
    {
        $string = str_replace(['تومان', ',', ' '], '', $string);
        return trim($string);
    }

    public static function addDomainToUrl($url, $baseUrl)
    {
        $storeDomain = parse_url($baseUrl, PHP_URL_HOST);

        if ($url !== null && !str_contains($url, $storeDomain)) {
            $url = "https://" . $storeDomain . '/' . ltrim($url, '/');
        }

        return $url;
    }

    public static function goToNextPage($driver, $selector)
    {
        try {
            $currentURL = $driver->getCurrentURL(); // Capture the current URL before clicking
            $nextPageButton = $driver->findElement(WebDriverBy::cssSelector($selector));
            if ($nextPageButton) {
                $nextPageButton->click();
                usleep(5000000); // Wait for 3 seconds to allow some time for the page to start loading

                // Use a lambda function to check if the URL has changed from the captured URL
                $driver->wait()->until(
                    function () use ($driver, $currentURL) {
                        return $driver->getCurrentURL() != $currentURL;
                    }
                );
                return true;
            }
        } catch (Exception $e) {
            Log::info("Pagination ended: " . $e->getMessage());
        }
        return false;
    }

    public static function generateUniqIdFromUrl($url) {
        // Normalize the URL
        $normalizedUrl = strtolower(trim($url));
        $normalizedUrl = preg_replace('/^(https?:\/\/)?(www\.)?/', '', $normalizedUrl);
        $normalizedUrl = rtrim($normalizedUrl, "/");

        // Hash the URL using SHA-256
        $hash = hash('sha256', $normalizedUrl);

        return $hash;
    }

    public static function isStopWord($title)
    {
        $stopWords = [
            'درباره ما',
            'تماس با ما',
            'تماس',
            'درباره',
            'ما',
            'صفحه اصلی',
            'صفحه',
            'اصلی',
            'داشبورد',
            'خانه',
            'ورود',
            'ثبت نام',
            'ورود / ثبت نام',
            'لاگین',
            "ثبت شکایات",
            "راهنمای خرید",
            "راهنمای اعتبار تارا",
            "برند ها",
            "راهنمای تعیین سایز",
            "رهگیری سفارشات",
            "وبلاگ",
        ];

        return in_array($title, $stopWords);
    }
}
