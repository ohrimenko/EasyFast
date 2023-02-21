<?php

namespace Base\Base;

class Setting extends BaseObj
{
    protected $vars;

    public function __construct()
    {
        if (file_exists(config('storage_dir') . '/data/settings.txt')) {
            $this->vars = unserialize(file_get_contents(config('storage_dir') . '/data/settings.txt'));
        }
        
        if (!is_array($this->vars)) {
            $this->vars = [];
        }
    }

    public function __get($key)
    {
        if (isset($this->vars[$key])) {
            return $this->vars[$key];
        }
        
        return '';
    }

    public function __set($key, $val)
    {
        if ($key && $val && !(isset($this->vars[$key]) && $this->vars[$key] == $val)) {
            $this->vars[$key] = $val;
            
            $this->save();
        }
    }

    public function __unset($key)
    {
        if (isset($this->vars[$key])) {
            unset($this->vars[$key]);
            
            $this->save();
        }
    }

    protected function save()
    {
        file_put_contents(config('storage_dir') . '/data/settings.txt', serialize($this->vars));
    }
}
