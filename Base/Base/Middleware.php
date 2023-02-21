<?php

namespace Base\Base;

class Middleware
{
    private static $obj;
    
    use \App\Components\Middleware;

    private function __construct()
    {
    }

    public static function inst()
    {
        if(!self::$obj){
            self::$obj = new self;
        }
        
        return self::$obj;
    }

    public static function check($act)
    {
        $args = explode(':', $act);
        
        $act = $args[0];
        
        unset($args[0]);
        
        return self::$obj->{'act' . $act}($args) ? true : false;
    }
}

Middleware::inst();
