<?php

namespace Base\Base;

class BaseCookie extends BaseObj
{
    public $expire;
    public $path;
    public $domain;

    public function __construct($vars = [], $expire = null, $path = null, $domain = null)
    {
        if (is_array($vars)) {
            foreach($vars as $key => $value){
                $vars[$key] = $this->decrypt($value);
            }
            
            $this->vars = $vars;
        }
        
        if($expire){
            $this->expire = $expire;
        } else {
            $this->expire = time()+60*60*24*30;
        }
        
        if($path){
            $this->path = $path;
        } else {
            $this->path = '/';
        }
        
        if($domain){
            $this->domain = $domain;
        } else {
            $this->domain = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;
        }
    }
    
    public function set($key, $val, $expire = null, $path = null, $domain = null)
    {
        $this->vars[$key] = $val;
        
        if (!headers_sent()) {
            setcookie($key, $this->crypt($val), $expire ? $expire : $this->expire, $path ? $path : $this->path, $domain ? $domain : $this->domain);
        }
    }
    
    public function __set($key, $val)
    {
        $this->vars[$key] = $val;
        
        if (!headers_sent()) {
            setcookie($key, $this->crypt($val), $this->expire, $this->path, $this->domain);
        }
    }

    public function __unset($key)
    {
        if (isset($this->vars[$key])) {
            unset($this->vars[$key]);
            if (!headers_sent()) {
                setcookie($key, null, -1, '/');
            }
        }
    }
    
    protected function crypt($value)
    {
        return $value;
    }
    
    protected function decrypt($value)
    {
        return $value;
    }
}
