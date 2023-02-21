<?php

namespace App\Components;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\FileDetector;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverActions;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\WebDriverPlatform;
use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Firefox\FirefoxProfile;
use Facebook\WebDriver\Firefox\FirefoxDriver;

class Selenium
{
    protected static $instance = null;

    protected $drivers = [];

    protected function __construct()
    {
    }

    public function driver($server, $port)
    {
        if (!array_key_exists($server . ':' . $port, $this->drivers)) {
            $this->drivers[$server . ':' . $port] = null;

            try {
                $capabilities = [];
                
                $capabilities[WebDriverCapabilityType::PLATFORM] = WebDriverPlatform::ANY;
                
                if (config('proccess_proxy') != 'none' && config('local_proxy')) {
                    $capabilities[WebDriverCapabilityType::PROXY] = [
                        'proxyType' => 'manual',
                        'httpProxy' => config('local_proxy'),
                        'sslProxy' => config('local_proxy'),
                    ];
                }
                
                if (config('selenium_browser') == 'firefox') {
                    $capabilities[WebDriverCapabilityType::BROWSER_NAME] = WebDriverBrowserType::FIREFOX;
                } else {
                    $capabilities[WebDriverCapabilityType::BROWSER_NAME] = WebDriverBrowserType::CHROME;
                }
                
                $desiredCapabilities  = new DesiredCapabilities($capabilities);
                
                if (config('selenium_browser') == 'chrome') {
                    $chromeOptions = new ChromeOptions();
                    
                    $chromeOptions->addArguments([
                         //"--no-sandbox",
                         //"--headless", // без открытия окна     
                         "--disable-blink-features=AutomationControlled",
                    ]);
                    
                    //driver.execute_script("Object.defineProperty(navigator, 'webdriver', {get: () => undefined})")
                    //driver.execute_cdp_cmd('Network.setUserAgentOverride', {"userAgent": 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.53 Safari/537.36'})
                    
                    $chromeOptions->setExperimentalOption("useAutomationExtension", false);
                    $chromeOptions->setExperimentalOption("excludeSwitches", ["enable-automation"]);
                    
                    if (config('cache_static_selenium')) {
                        $is = true;
                        
                        $filemtime = filemtime(dirname(dirname(config('SITE_ROOT'))) . '/extensions/requests/manifest.json') . 
                            filemtime(dirname(dirname(config('SITE_ROOT'))) . '/extensions/requests/background.js') . 
                            filemtime(dirname(dirname(config('SITE_ROOT'))) . '/extensions/requests/content.js');
                        
                        if (file_exists(config('DIR_DATA') . '/requests.time')) {
                            if ($filemtime == file_get_contents(config('DIR_DATA') . '/requests.time')) {
                                $is = false;
                            }
                        }
                        
                        if ($is) {
                            Zip(
                                dirname(dirname(config('SITE_ROOT'))) . '/extensions/requests', 
                                config('DIR_DATA') . '/requests.crx'
                            );
                            
                            file_put_contents(config('DIR_DATA') . '/requests.time', $filemtime);
                        }
                        
                        $chromeOptions->addExtensions([config('DIR_DATA') . '/requests.crx']);
                    }
                    
                    $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
                }
                
                if (config('selenium_browser') == 'firefox') {
                    $firefoxOptions = new FirefoxOptions();
                    
                    $firefoxProfile = new FirefoxProfile();
                    
                    $firefoxProfile->setPreference("security.fileuri.strict_origin_policy", false);
                    
                    if (config('cache_static_selenium')) {
                        //$firefoxProfile->addExtension(config('DIR_DATA') . '/requests.xpi');
                    }
                    
                    $desiredCapabilities->setCapability(FirefoxOptions::CAPABILITY, $firefoxOptions);
                    
                    $desiredCapabilities->setCapability(FirefoxDriver::PROFILE, $firefoxProfile);
                }
                
                // Disable accepting SSL certificates
                //$desiredCapabilities->setCapability('acceptSslCerts', false);

                $this->drivers[$server . ':' . $port] = RemoteWebDriver::create(
                    $server . ':' . $port, 
                    $desiredCapabilities, 
                    120000,
                    130000
                );
            }
            catch (WebDriverException $e) {
                echo $e->getMessage();
                $this->drivers[$server . ':' . $port] = null;
            }
        }

        return $this->drivers[$server . ':' . $port];
    }
    
    public function isStartSelenium($server, $port)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $server . ':' . $port);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json;charset=UTF-8',
            'Accept: application/json',
            ));

        curl_setopt($curl, /* CURLOPT_TIMEOUT_MS */ 155, 130000);
        curl_setopt($curl, /* CURLOPT_CONNECTTIMEOUT_MS */ 156, 120000);

        $raw_results = trim(curl_exec($curl));

        $results = json_decode($raw_results, true);

        if (!($results === null && json_last_error() !== JSON_ERROR_NONE) && is_array($results)) {
            return true;
        }
        
        if ($raw_results) {
            return true;
        } 

        return false;
    }

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
