<?php

namespace App\Factories;

use App\Contracts\CategoriesCrawlerContract;
use App\Services\Crawlers\Categories\AloneCrawler;
use App\Services\Crawlers\Categories\HomsaCrawler;
use App\Services\Crawlers\Categories\FarakoCrawler;
use App\Services\Crawlers\Categories\GoldijCrawler;
use App\Services\Crawlers\Categories\MyAjilCrawler;
use App\Services\Crawlers\Categories\BaniModeCrawler;
use App\Services\Crawlers\Categories\DigilandCrawler;
use App\Services\Crawlers\Categories\RadagoldCrawler;
use App\Services\Crawlers\Categories\RojashopCrawler;
use App\Services\Crawlers\Categories\Eghamat24Crawler;
use App\Services\Crawlers\Categories\JeanswestCrawler;
use App\Services\Crawlers\Categories\TrendyaabCrawler;
use App\Services\Crawlers\Categories\BornosModeCrawler;
use App\Services\Crawlers\Categories\KiaGalleryCrawler;
use App\Services\Crawlers\Categories\HajiBadoomiCrawler;
use App\Services\Crawlers\Categories\ShiferShoesCrawler;
use App\Services\Crawlers\Categories\LavazemKhonegiCrawler;

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
            'lavazemkhonegi' => new LavazemKhonegiCrawler(),
            'dgland' => new DigilandCrawler(),
            'eghamat24' => new Eghamat24Crawler(),
            'homsa' => new HomsaCrawler(),
            'banimode' => new BaniModeCrawler(),
            'jeanswest' => new JeanswestCrawler(),
            'trendyaab' => new TrendyaabCrawler(),
            'farako' => new FarakoCrawler(),
            'hajibadoomi' => new HajiBadoomiCrawler(),
            'bornosmode' => new BornosModeCrawler(),
            'shifershoes' => new ShiferShoesCrawler(),
            'myajil' => new MyAjilCrawler(),

            default => null,
        };
    }
}
