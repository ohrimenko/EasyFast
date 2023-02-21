<?php

namespace Base\Base;

use \Base;
use \Base\Base\Data;
use \Base\Base\BaseView;

abstract class BaseWidget extends BaseView
{
    public $data;

    protected $path;

    public function __construct(array $data = [])
    {
        $this->path = str_replace('\\', DIRECTORY_SEPARATOR, preg_replace("#\\\\\w*$#", '', preg_replace("#^App\\\\#", config('SITE_ROOT') . DIRECTORY_SEPARATOR, static::class, 1))) . DIRECTORY_SEPARATOR . 'view';

        $this->data = new Data($data);

        if (!isset($this->data->placeType))
            $this->data->placeType = 'index';
            
        $this->init();
    }
    
    public function __get($key)
    {
        if (isset($this->data->{$key})) {
            return $this->data->{$key};
        }
        return null;
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

    public function __unset($key)
    {
        if (isset($this->data->{$key})) {
            unset($this->data->{$key});
        }        
    }

    protected function init()
    {
    }

    public function run(array $data = [], $is_view = true)
    {
        foreach ($data as $key => $value) {
            $this->data->{$key} = $value;
        }
        
        return $this->render($this->data->placeType, $is_view);
    }

    public static function widget(array $data = [], $is_view = true)
    {
        $obj = new static($data);

        return $obj->run([], $is_view);
    }

    protected function render($view = null, $is_view = true)
    {
        ob_start();
        
        if ($view) {
            $data = $this->data;
            
            if (file_exists($this->path($view))) {
                require ($this->path($view));
            } else {
                echo '<div style="color: red; font-family: cursive; font-size: 12px;">Not Widget View: ' .
                    static::class . '::' . $view . '</div>';
            }
        }
        
        $out = ob_get_clean();
        
        if ($is_view) {
            echo $out;
        } else {
            return $out;
        }
    }

    public function view($view = null, $data = [])
    {
        if ($view) {
            extract($data);

            $data = $this->data;
            
            if ($path = $this->path($view, true)) {
                require ($path);
            } else {
                echo '<div style="color: red; font-family: cursive; font-size: 12px;">Not View in Widget: ' . static::class . '::' . $view . '</div>';
            }
        }
    }

    public function path($view, $is_exists = false)
    {
        if ($is_exists && app()->template() != 'default') {
            $path = $this->path . DIRECTORY_SEPARATOR . app()->template() . DIRECTORY_SEPARATOR . ltrim($view, DIRECTORY_SEPARATOR);

            $pinf = pathinfo($path);

            if (!isset($pinf['extension'])) {
                $path .= '.php';
            }

            if (file_exists($path)) {
                return $path;
            } else {
                $path = $this->path . DIRECTORY_SEPARATOR . ltrim($view, DIRECTORY_SEPARATOR);

                $pinf = pathinfo($path);

                if (!isset($pinf['extension'])) {
                    $path .= '.php';
                }

                if (file_exists($path)) {
                    return $path;
                } else {
                    return null;
                }
            }
        } else {
            $path = $this->path . DIRECTORY_SEPARATOR . ltrim($view, DIRECTORY_SEPARATOR);

            $pinf = pathinfo($path);

            if (!isset($pinf['extension'])) {
                $path .= '.php';
            }

            return $path;
        }
    }
}
