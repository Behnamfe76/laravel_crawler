<?php

namespace App\Factories;

use App\Contracts\CategoriesCrawlerContract;
use App\Services\Crawlers\Categories\AloneCrawler;
use App\Services\Crawlers\Categories\GoldijCrawler;
use App\Services\Crawlers\Categories\RadagoldCrawler;
use App\Services\Crawlers\Categories\RojashopCrawler;
use App\Services\Crawlers\Categories\KiaGalleryCrawler;

class CategoriesCrawlerFactory
{
    public function runFactory($merchantId): CategoriesCrawlerContract|null
    {
        return match ($merchantId) {
            'alone' => new AloneCrawler(),
            'radagold' => new RadagoldCrawler(),
            'goldij' => new GoldijCrawler(),
            'rojashop' => new RojashopCrawler(),
            'kia-gallery' => new KiaGalleryCrawler(),

            default => null,
        };
    }
}
