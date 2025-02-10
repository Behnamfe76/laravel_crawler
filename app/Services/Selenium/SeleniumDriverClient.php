<?php

namespace App\Services\Selenium;

use App\Abstractions\AbstractSeleniumDriver;
use App\Helpers\GoogleSeleniumDriver;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class SeleniumDriverClient extends AbstractSeleniumDriver
{
    public function checkAlive(): bool
    {
        $this->alive = false;
        try {
            $ch = curl_init($this->url . '/status');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode == 200) {
                $this->alive = true;
            } else {
                $this->alive = false;
            }
        }catch (\Exception $e) {
            $this->alive = false;
        }

        return $this->alive;
    }

    public function setup($host, $port): void
    {
        $driver = $host . ':' . $port;
        $this->url = $driver;

        $googleDriver = new GoogleSeleniumDriver($driver, [
            '--disable-gpu',
            '--window-size=1920,1080',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--remote-debugging-port=9222',
        ]);
        $this->set($googleDriver);
        $this->use();
        $isAlive = $this->checkAlive();
        if (!$isAlive) {
            throw new \Exception('Driver is not alive');
        }
    }

    public function set(RemoteWebDriver $driver): void
    {
        $this->driver = $driver;
    }

    public function close(): void
    {
        $this->available = true;
//        $this->driver->quit();
    }

    public function use(): void
    {
        $this->available = false;
    }

    public function checkAvailable(): bool
    {
        return $this->available;
    }
}
