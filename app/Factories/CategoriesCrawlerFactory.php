<?php

namespace App\Factories;

use App\Contracts\CategoriesCrawlerContract;
use App\Services\Crawlers\Categories\AloneCrawler;

class CategoriesCrawlerFactory
{
    public function runFactory($merchantId): CategoriesCrawlerContract|null
    {
        return match ($merchantId) {
            'alone' => new AloneCrawler(),

            default => null,
        };
    }
}
