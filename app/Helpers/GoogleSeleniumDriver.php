<?php

namespace App\Helpers;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class GoogleSeleniumDriver extends \Facebook\WebDriver\Remote\RemoteWebDriver
{
    public $seleniumServerUrl;
    public $chromeArgument;
    public RemoteWebDriver $driver;

    public function __construct($seleniumServerUrl, $chromeArgument)
    {
        $this->seleniumServerUrl = $seleniumServerUrl;
        $this->chromeArgument = $chromeArgument;
        $this->driver = $this->createChromeDriver();
    }

    /**
     * Create a WebDriver instance with ChromeOptions.
     *
     * @return RemoteWebDriver
     */
    private function createChromeDriver(): RemoteWebDriver
    {
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments($this->chromeArgument);

        // Existing preferences to disable images and fonts
        $prefs = [
            'profile.managed_default_content_settings.images' => 2, // Disable images
            'profile.default_content_setting_values.fonts' => 0, // Disable fonts
            'safebrowsing.enabled' => false, // Disable Safe Browsing
            'profile.content_settings.exceptions.media_stream_camera' => [],
            'profile.content_settings.exceptions.media_stream_mic' => [],
            'profile.content_settings.exceptions.media_stream_capture' => [],
            'profile.default_content_setting_values.media_stream' => 2, // Block media streams (audio/video)
            'profile.default_content_setting_values.autoplay' => 1, // Block autoplay
        ];
        $chromeOptions->setExperimentalOption("prefs", $prefs);


        return RemoteWebDriver::create($this->seleniumServerUrl, DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $chromeOptions));
    }

    public function close(): void
    {
        // Implement the logic to close the driver -
        // For example, you might want to quit the WebDriver session
        $this->driver->quit();
    }
}
