<?php

use \Base\Base\MainData;
use \Base\Base\ObjData;
use \App\Models\Stat;
use \Base\Base\DB;
use \Base\Base\Route;

class Base
{
    private $config = [];

    private static $app = null;

    private $event = null;

    private $data = null;

    private static $path = null;

    private $cache = null;

    private $setting = null;

    private $request = null;

    private $components = null;

    private $is_php_7;
    
    private $langs;
    
    private $template = 'default';

    private function __construct()
    {
        if (isset($GLOBALS['app_config'])) {
            $this->config = $GLOBALS['app_config'];
        } elseif (file_exists(__dir__ . "/../config/App.php")) {
            $this->config = require (__dir__ . "/../config/App.php");
        }

        self::$path = ['App' => $this->config['SITE_ROOT'], 'Base' => $this->config['SITE_ROOT'] .
            '/Base', 'Components' => $this->config['SITE_ROOT'] . "/Components", 'libs' =>
            $this->config['SITE_ROOT'] . "/libs", ];

        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            $this->is_php_7 = true;
        } else {
            $this->is_php_7 = false;
        }
    }

    public function template() 
    {
        return $this->data->template_views;
    }

    public function _t($key, $text) 
    {
        $expl = explode('.', $key);
            
        $k = '';
        $n = '';
            
        if(count($expl) == '1') {
            $k = $expl[0];
            $n = 'main';
        } else {
            $l_k = count($expl) - 1;
                
            $k = $expl[$l_k];
            unset($expl[$l_k]);
                
            $n = implode('.', $expl);
        }
        
        if (!isset($this->langs[$n])) {
            if (file_exists(config('SITE_ROOT') . '/langs/' . \App\Models\Language::getLanguage()->shortname . '/' . $n . '.php')) {
                //print_r(config('SITE_ROOT') . '/langs/' . \App\Models\Language::getLanguage()->shortname . '/' . $n . '.php');
                
                $this->langs[$n] = require(config('SITE_ROOT') . '/langs/' . \App\Models\Language::getLanguage()->shortname . '/' . $n . '.php');
                
                
            } else {
                $this->langs[$n] = [];
            }                    
            
            if(!is_array($this->langs[$n])){
                $this->langs[$n] = [];
            }
        }
        
        
        
        if (isset($this->langs[$n][$k])) {
            return $this->langs[$n][$k];
        } else {
            return $text;
        }
    }

    public static function isPhp7()
    {
        return self::$app->is_php_7;
    }

    public static function session($key, $value = null)
    {

    }

    public static function Setting($key, $value = null)
    {
        if ($value) {
            self::instance()->setting->{$key} = $value;
        } else {
            return self::instance()->setting->{$key};
        }
    }

    public static function config($key)
    {
        $args = func_get_args();

        $cargs = count($args);

        if ($cargs == 1) {
            return array_get(self::$app->config, $key);
            
            if (is_null($args[0])) {
                return null;
            }

            if (array_key_exists($args[0], self::$app->config)) {
                return self::$app->config[$args[0]];
            }

            $array = self::$app->config;

            foreach (explode('.', $args[0]) as $segment) {
                if (is_array($array) && array_key_exists($segment, $array)) {
                    $array = $array[$segment];
                } else {
                    return null;
                }
            }

            return $array;
        } elseif ($cargs == 2) {
            if (is_null($args[1])) {
                if (isset($this->config[$args[0]])) {
                    unset($this->config[$args[0]]);
                }
            } else {
                self::$app->config[$args[0]] = $args[1];
            }
        } elseif ($cargs > 2) {
            self::$app->config[$args[0]] = array();

            for ($i = 1; $i < $cargs; $i++) {
                self::$app->config[$args[0]][] = $args[$i];
            }
        }
    }

    public static function instance($new = false)
    {
        if (is_null(self::$app) || $new) {
            self::$app = new self();

            self::$app->init($new);
        }
        return self::$app;
    }

    public static function app()
    {
        return self::$app;
    }

    public function components()
    {
        return $this->components;
    }
    
    public $begin_time;

    private function init($new = false)
    {
        $this->begin_time = microtime(true);
        
        spl_autoload_register("\Base::autoload");

        if (file_exists($this->config['LOG_ERRORS_FILE']) && filesize($this->config['LOG_ERRORS_FILE']) >
            10485760) {
            unlink($this->config['LOG_ERRORS_FILE']);
        }

        if (!is_dir($this->config['DIR_TMP'])) {
            mkdir($this->config['DIR_TMP'], 0777, true);
        }

        require_once ($this->Base . "/Base/Functions.php");

        require_once ($this->Base . "/Base/init.php");
        require_once ($this->Base . "/Base/Exceptions.php");

        \Base\Base\ErrorHandler::SetHandler($this->config['ERROR_TYPES']);

        require_once ($this->Components . "/helpers.php");

        require_once ($this->Base . "/Base/Registry.php");
        require_once ($this->Base . "/Model/Finder.php");

        require_once ($this->Base . "/Record/Collections.php");
        require_once ($this->Base . "/Record/PersistenceFactory.php");

        require_once ($this->Base . "/Controller/ApplicationHelper.php");
        require_once ($this->Base . "/Controller/AppController.php");
        require_once ($this->Base . "/Controller/Controller.php");

        $this->cache = new \Base\Base\CacheFile;

        $this->event = new \Base\Base\Event;

        $this->components = \App\Components\Components::instance($new);

        $this->request = \Base\Base\Request::inst($new);

        $this->data = new \App\Components\DataCache;

        $this->setting = new \Base\Base\Setting;
        
        $this->data->widgets = new \Base\Base\BaseObj;
        
        $this->data->template_views = 'default';
        
        require_once ($this->App . "/routes/web.php");
        
        if (!isset($_SESSION['setting'])) {
            $_SESSION['setting'] = [
                'language_id' => 1,
            ];
        }

        if (!isset($this->data->user) && request()->request->auth_token) {
            $user = Base::dataModel('User', 'arrayUserByAuthToken', ['auth_token' => request()->request->auth_token]);
            
            if ($user) {
                $user->auth_token = str_rand(40);
                
                $user->authorize();
                
                $this->data->user = $user;
            }
        } 

        if (false && !isset($this->data->user) && request()->request->cookie_token_request) {
            $user = Base::dataModel('User', 'arrayUserByCookieToken', ['cookie_token' => request()->request->cookie_token_request]);
            
            if ($user) {
                $user->authorize();
                
                $this->data->user = $user;
            }
        } 
        
        if (!isset($this->data->user) && isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
            $this->data->user = function () {
                $user = Base::dataModel('User', 'arrayUserByIdActive', ['id' => $_SESSION['user']['id']]);

                if (!isset($_SESSION['user']['auth_by_admin'])) {
                    if (!$user || !$user->auth_token || ($user->login != $_SESSION['user']['login'] ||
                        $user->password != $_SESSION['user']['password'] || $user->auth_token != $_SESSION['user']['auth_token'])) {
                        $user = null;
                        unset($_SESSION['user']);
                    }

                    if ($user) {
                    } elseif (request()->cookie->cookie_auth_token) {
                        $user = Base::dataModel('User', 'arrayUserByCookieToken', ['cookie_token' => request()->cookie->cookie_auth_token]);
                    }
                }
                
                if($user) {
                    $user->authorize();
                }

                return $user;
            };
        } 
        
        if (!isset($this->data->user) && request()->cookie->cookie_auth_token) {
            $this->data->user = function () {
                $user = Base::dataModel('User', 'arrayUserByCookieToken', ['cookie_token' => request()->cookie->cookie_auth_token]);
                
                if ($user) {
                    $user->authorize();
                }
                
                return $user;
            };
        }
        
        if (!isset($this->data->user)){
            $this->data->user = null;
        }

        $this->components()->init();
    }

    public static function data()
    {
        return self::$app->data;
    }

    public static function issetData($key)
    {
        return isset(self::$app->data->{$key});
    }

    public static function isAdmin()
    {
        $data = self::instance()->data;
        
        if (isset($data->user)) {
            if ($data->user) {
                if ($data->user->type == '1' || isset($_SESSION['user']['auth_by_admin'])) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function dataModel($model, $data, $params = [], $emptyFail = false)
    {
        return self::$app->data->data($model, $data, $params, $emptyFail);
    }

    public static function autoload($name)
    {
        $spaces = explode("\\", ltrim($name, "\\"));

        if (isset(self::$path[$spaces[0]])) {
            $spaces[0] = self::$path[$spaces[0]];

            $path = implode(DIRECTORY_SEPARATOR, $spaces);

            $path .= ".php";

            if (is_readable($path)) {
                require_once ($path);

                return true;
            }
        }

        return false;
    }

    public function __get($key)
    {
        if ($key == 'cache') {
            return self::$app->cache;
        }
        
        if (isset(self::$path[$key])) {
            return self::$path[$key];
        } else {
            return null;
        }
    }

    public function event($event, $var = null, $handle = null)
    {
        return self::$app->event->event($event, $var, $handle);
    }

    public function listen($event, $handle)
    {
        return self::$app->event->listen($event, $handle);
    }

    public static function cache($key)
    {
        $args = func_get_args();

        $cargs = count($args);

        if ($cargs == 1) {
            return self::$app->cache->get($args[0]);
        } elseif ($cargs >= 2) {
            if (is_null($args[1])) {
                //if (isset(self::$app->config[$args[0]])) {
                    self::$app->cache->delete($args[0]);
                //}
            } else {
                self::$app->cache->set($args[0], $args[1], isset($args[2]) ? $args[2] : 60);
            }
        }
    }

    public function run()
    {
        try {
            $response = \Base\Base\Route::run();
            
            switch (gettype($response) == 'object' ? get_class($response) : gettype($response)) {
                case 'string':
                    echo $response;

                    break;
                case 'Base\Base\View':
                    $response->render();

                    break;
            }

            //\App\Components\Cron::instance()->run();

            \Base\Model\ObjectWatcher::instance()->performOperations();
        }
        catch (\Base\Base\RouteException $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
            exit;
        }
        catch (\Base\Base\BaseException $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
            exit;
        }
        
        if(!$stat = Base::dataModel('Stat', 'arrayStatByRout', ['rout' => Route::nowRout()])){
            $stat = new Stat;
            
            $stat->rout = Route::nowRout();
        }
        
        $cp = null;

        if (function_exists('getrusage')) {
            $dat = getrusage();
            $cp = (((float)($dat["ru_utime.tv_usec"] + (float)$dat["ru_stime.tv_usec"]))/1000000);
        }
        
        $stat->cp = $cp;
        $stat->count = $stat->count + 1;
        $stat->duration = microtime(true) - $this->begin_time;
        $stat->memory = memory_get_usage()*0.000001;
        $stat->count_db = DB::getCountQuery();
        $stat->duration_db = DB::getDurationQuery();
        
        $stat->cp_all = $stat->cp_all + $stat->cp;
        $stat->duration_all = $stat->duration_all + $stat->duration;
        $stat->memory_all = $stat->memory_all + $stat->memory;
        $stat->count_db_all = $stat->count_db_all + $stat->count_db;
        $stat->duration_db_all = $stat->duration_db_all + $stat->duration_db;
        
        $stat->cp_average = $stat->cp_all / $stat->count;
        $stat->duration_average = $stat->duration_all / $stat->count;
        $stat->memory_average = $stat->memory_all / $stat->count;
        $stat->count_db_average = $stat->count_db_all / $stat->count;
        $stat->duration_db_average = $stat->duration_db_all / $stat->count;
        
        $stat->save();
    }
}

Base::instance();
