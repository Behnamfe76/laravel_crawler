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

class HomsaCrawler extends AbstractCategoriesCrawler
{
    private array $provinces = [
        [
            'title' => 'ویلا و سوئیت در استان آذربایجان شرقی',
            'url' => 'https://www.homsa.net/province-east-azerbaijan',
        ],
        [
            'title' => 'ویلا و سوئیت در استان آذربایجان غربی',
            'url' => 'https://www.homsa.net/province-west-azerbaijan',
        ],
        [
            'title' => 'ویلا و سوئیت در استان اردبیل',
            'url' => 'https://www.homsa.net/province-ardabil',
        ],
        [
            'title' => 'ویلا و سوئیت در استان اصفهان',
            'url' => 'https://www.homsa.net/province-isfahan',
        ],
        [
            'title' => 'ویلا و سوئیت در استان البرز',
            'url' => 'https://www.homsa.net/province-alborz',
        ],
        [
            'title' => 'ویلا و سوئیت در استان ایلام',
            'url' => 'https://www.homsa.net/province-ilam',
        ],
        [
            'title' => 'ویلا و سوئیت در استان بوشهر',
            'url' => 'https://www.homsa.net/province-bushehr',
        ],
        [
            'title' => 'ویلا و سوئیت در استان تهران',
            'url' => 'https://www.homsa.net/province-tehran',
        ],
        [
            'title' => 'ویلا و سوئیت در استان چهارمحال و بختیاری',
            'url' => 'https://www.homsa.net/province-chaharmahal-and-bakhtiari',
        ],
        [
            'title' => 'ویلا و سوئیت در استان خراسان رضوی',
            'url' => 'https://www.homsa.net/province-razavi-khorasan',
        ],
        [
            'title' => 'ویلا و سوئیت در استان خراسان جنوبی',
            'url' => 'https://www.homsa.net/province-south-khorasan',
        ],
        [
            'title' => 'ویلا و سوئیت در استان خراسان شمالی',
            'url' => 'https://www.homsa.net/province-north-khorasan',
        ],
        [
            'title' => 'ویلا و سوئیت در استان خوزستان',
            'url' => 'https://www.homsa.net/province-khuzestan',
        ],
        [
            'title' => 'ویلا و سوئیت در استان زنجان',
            'url' => 'https://www.homsa.net/province-zanjan',
        ],
        [
            'title' => 'ویلا و سوئیت در استان سمنان',
            'url' => 'https://www.homsa.net/province-semnan',
        ],
        [
            'title' => 'ویلا و سوئیت در استان 	سیستان و بلوچستان',
            'url' => 'https://www.homsa.net/province-sistan-and-baluchestan',
        ],
        [
            'title' => 'ویلا و سوئیت در استان فارس',
            'url' => 'https://www.homsa.net/province-fars',
        ],
        [
            'title' => 'ویلا و سوئیت در استان قزوین',
            'url' => 'https://www.homsa.net/province-qazvin',
        ],
        [
            'title' => 'ویلا و سوئیت در استان قم',
            'url' => 'https://www.homsa.net/province-qom',
        ],
        [
            'title' => 'ویلا و سوئیت در استان کردستان',
            'url' => 'https://www.homsa.net/province-kurdistan',
        ],
        [
            'title' => 'ویلا و سوئیت در استان کرمان',
            'url' => 'https://www.homsa.net/province-kerman',
        ],
        [
            'title' => 'ویلا و سوئیت در استان کرمانشاه',
            'url' => 'https://www.homsa.net/province-kermanshah',
        ],
        [
            'title' => 'ویلا و سوئیت در استان کهگیلویه و بویراحمد',
            'url' => 'https://www.homsa.net/province-kohgiluyeh-and-boyer-ahmad',
        ],
        [
            'title' => 'ویلا و سوئیت در استان گلستان',
            'url' => 'https://www.homsa.net/province-golestan',
        ],
        [
            'title' => 'ویلا و سوئیت در استان گیلان',
            'url' => 'https://www.homsa.net/province-gilan',
        ],
        [
            'title' => 'ویلا و سوئیت در استان لرستان',
            'url' => 'https://www.homsa.net/province-lorestan',
        ],
        [
            'title' => 'ویلا و سوئیت در استان مازندران',
            'url' => 'https://www.homsa.net/province-mazandaran',
        ],
        [
            'title' => 'ویلا و سوئیت در استان مرکزی',
            'url' => 'https://www.homsa.net/province-markazi',
        ],
        [
            'title' => 'ویلا و سوئیت در استان 	هرمزگان',
            'url' => 'https://www.homsa.net/province-hormozgan',
        ],
        [
            'title' => 'ویلا و سوئیت در استان همدان',
            'url' => 'https://www.homsa.net/province-hamedan',
        ],
        [
            'title' => 'ویلا و سوئیت در استان یزد',
            'url' => 'https://www.homsa.net/province-yazd'
        ],
    ];
    /**
     * @override
     */
    public function fetchCategories(string $url, array $params, string $slug): array|\Illuminate\Support\Collection|\Throwable|\Exception
    {
        try {
            $categoriesCollection = [];
            foreach ($this->provinces as $province) {
                $categoriesCollection[] = [
                    'id' => ScraperHelper::generateUniqIdFromUrl($province['url']),
                    'title' => $province['title'],
                    'url' => $province['url'],
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
