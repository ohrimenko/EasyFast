<?php

namespace Base\Base;

class BaseHeader extends BaseObj
{
    public function set($key, $val)
    {
        $this->vars[$key] = $val;
        
        if (!headers_sent()) {
           header_sent($key.': '.$val);
        }
    }
    
    public function __set($key, $val)
    {
        $this->vars[$key] = $val;
        
        if (!headers_sent()) {
           header_sent($key.': '.$val);
        }
    }

    public function __unset($key)
    {
        if (isset($this->vars[$key])) {
            unset($this->vars[$key]);
            
            if (!headers_sent()) {
               header_sent($key.': ');
            }
        }
    }
}
