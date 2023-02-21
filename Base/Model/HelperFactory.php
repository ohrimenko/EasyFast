<?php

namespace Base\Model;

use \Base\Base\DB;

class HelperFactory
{
    private static $finderObjects = [];

    static function getFinder($type)
    {
        $type = preg_replace('|^.*\\\|', "", $type);
        $record = "\\Base\\Record\\{$type}Record";

        if (isset(self::$finderObjects[$record])) {
            return self::$finderObjects[$record];
        }

        if (class_exists($record)) {
            self::$finderObjects[$record] = new $record();
            return self::$finderObjects[$record];
        }
        
        $record = "\\Base\\Record\\BaseRecord";
        
        if (class_exists($record)) {
            self::$finderObjects[$record] = new $record();
            return self::$finderObjects[$record];
        }

        throw new \Base\Base\BaseException("Unknown: $record");
    }

    static function getCollection($type)
    {
        $type = preg_replace('|^.*\\\|', "", $type);
        $collection = "\\Base\\Record\\{$type}Collection";
        if (class_exists($collection)) {
            return new $collection();
        }
        throw new \Base\Base\BaseException("Unknown: $collection");
    }
}
