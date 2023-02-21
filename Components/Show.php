<?php

namespace App\Components;

class Show
{
    public static function widget($widget, array $params = [], $is_view = true)
    {
        $class = '\App\Widgets\\' . $widget.'\\'.$widget;
        return $class::widget($params, $is_view);
    }
}
