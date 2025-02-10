<?php

namespace App\Contracts;

use Facebook\WebDriver\Remote\RemoteWebDriver;

interface CategoriesCrawlerContract
{
    public function setup();
    public function run(array $params, string $slug, string $job): void;
    public function test(array $params, string $slug): array|\Throwable|\Exception;
    public function fetchCategories(string $url, array $params, string $slug): array|\Illuminate\Support\Collection;
    public function store(array $data, string $slug);
    public function storeInRabbitMQ(array $data, string $slug, string $exchangeName, string $queueName, string $routingKey): int;
}
