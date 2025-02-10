<?php

namespace App\Contracts;

use Facebook\WebDriver\Remote\RemoteWebDriver;

interface SeleniumDriverContract
{
    public function get();

    public function set(RemoteWebDriver $driver): void;

    public function checkAvailable(): bool;

    public function checkAlive(): bool;

    public function setup(string $host, int $port): void;

    public function close(): void;
}
