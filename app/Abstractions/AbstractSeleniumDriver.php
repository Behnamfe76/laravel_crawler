<?php

namespace App\Abstractions;

use App\Contracts\SeleniumDriverContract;
use Facebook\WebDriver\Remote\RemoteWebDriver;

abstract class AbstractSeleniumDriver implements SeleniumDriverContract
{

    protected RemoteWebDriver $driver;

    protected bool $available = false;
    protected bool $alive = false;

    protected string $url;
    public function get(): RemoteWebDriver
    {
        return $this->driver;
    }

    public function set(RemoteWebDriver $driver): void
    {
        $this->driver = $driver;
    }


}
