<?php

namespace App\Controllers;

use Base;
use Base\Base\BaseController;
use Base\Base\Request;
use Base\Base\DB;
use Base\Base\Route;
use Base\Base\Mail;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\FileDetector;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverActions;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Remote\LocalFileDetector;
use App\Models\Product;
use App\Components\ReqDetect;
use App\Widgets\LinkPager\LinkPager;
use App\Components\Multithread;

class MainController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        //redirect('admin.index', []);

        return $this->view('/main/index');
    }

    public function errorPage()
    {
        return $this->view('/error/404');
    }

    public function printColumns()
    {
        $columns = DB::GetAll('SHOW COLUMNS FROM `' . Request::req()->table . '`');

        echo '&nbsp;&nbsp;&nbsp;&nbsp;protected $columns = array(<br />';

        foreach ($columns as $column) {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'" . $column['Field'] .
                "' => '" . str_replace("'", "\'", $column['Type']) . "',<br />";
        }

        echo '&nbsp;&nbsp;&nbsp;&nbsp;);';
    }

    public function test()
    {
        $driver = seleniumDriver('http://212.110.137.20', 4444);
        
        if ($driver) {
            try {
                $driver->get('https://intoli.com/blog/not-possible-to-block-chrome-headless/chrome-headless-test.html');
                $html = $driver->getPageSource();

                echo $html;
            }
            catch (WebDriverException $e) {
                echo $e->getMessage();
            }

            //$driver->quit();
        }
    }

    public function chrome()
    {
        seleniumDriver('http://localhost', 4444);
    }
}
