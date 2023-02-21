<?php

namespace Base\Base;

use \Base;
use \Base\Base\Data;

abstract class BaseView
{
    protected static $stack_buffer = [];
    protected static $i_buffer = 0;

    protected static $block_stack = [];
    protected static $i_block = 0;

    protected static $stack_include = [];
    protected static $active_includes = [];

    public static function set($area, $out, $act = 'append')
    {
        foreach (self::$block_stack as $areas) {
            if ($areas[count($areas) - 1] == $area) {
                self::$stack_buffer[++self::$i_buffer] = ['areas' => $areas, 'out' => $out,
                    'act' => $act, 'type' => 'out'];

                foreach (self::$active_includes as $include_key) {
                    self::$stack_include[$include_key]['buffer'][] = self::$i_buffer;
                }

                return;
            }
        }
    }


    public static function ob_end_flush()
    {
        ob_end_flush();

        self::$stack_buffer = [];
        self::$i_buffer = 0;

        self::$block_stack = [];
        self::$i_block = 0;

        self::$stack_include = [];
        self::$active_includes = [];
    }
}
