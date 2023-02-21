<?php

namespace App\Components;

use App\Components\ReqDetect;
use \Base\Base\Route;

class DataCache
{
    private $data;

    public function __get($key)
    {
        return $this->data->{$key};
    }

    public function __set($key, $val)
    {
        $this->data->{$key} = $val;
    }

    public function __isset($key)
    {
        if (isset($this->data->{$key})) {
            return true;
        }
        return false;
    }

    public function __call($name, $arguments)
    {
        if (config('cache_data.'.$name)) {
            if (config('cache_data.'.$name.'.type') == 'url') {
                $key = $name.'_'.Route::now();
            } else {
                $key = $this->genKey($name, $arguments); 
            }
            
            if (config('cache_data.'.$name.'.interval')) {
                $interval = config('cache_data.'.$name.'.interval');
            } else {
                $interval = 60;
            }
            
            $key = translit($key);
            
            $cache_data = cache($key);
            
            if (is_null($cache_data) || $cache_data === false) {
                $data = $this->getCall($name, $arguments);
                
                cache($key, $data, $interval);
                
                return $data;
            } else {
                return $cache_data;
            }
        }
        
        return $this->getCall($name, $arguments);
    }

    public function getCall($name, $arguments)
    {
        switch (count($arguments)) {
            case '0':
                return $this->data->{$name}();
                break;
            case '1':
                return $this->data->{$name}($arguments[0]);
                break;
            case '2':
                return $this->data->{$name}($arguments[0], $arguments[1]);
                break;
            case '3':
                return $this->data->{$name}($arguments[0], $arguments[1], $arguments[2]);
                break;
            case '4':
                return $this->data->{$name}($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
                break;
            case '5':
                return $this->data->{$name}($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
                break;
            case '6':
                return $this->data->{$name}($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5]);
                break;
            case '7':
                return $this->data->{$name}($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5], $arguments[6]);
                break;
            case '8':
                return $this->data->{$name}($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5], $arguments[6], $arguments[7]);
                break;
            case '9':
                return $this->data->{$name}($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5], $arguments[6], $arguments[7], $arguments[8]);
                break;
            case '10':
                return $this->data->{$name}($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5], $arguments[6], $arguments[7], $arguments[8], $arguments[9]);
                break;
            default:
                return null;
                break;
        }
    }

    public function genKey($name, $arguments)
    {
        return $name.'.'.$this->genKeyArr($arguments);
    }

    public function genKeyArr($arg)
    {
        if (is_array($arg)) {
            foreach($arg as $k => $v){
                return $k.'.'.$this->genKeyArr($v);
            } 
        } else {
            return $arg;
        }
    }

    public function __construct()
    {
        $this->data = new \App\Components\Data;
    }
}
