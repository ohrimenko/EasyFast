<?php

namespace Base\Base;

use \Base;
use \Base\Base\BaseException;
use \Base\Base\BaseObj;

class MainData
{
    private $data;

    private $lazyLoad;

    public function __construct(array $data = [])
    {
        $this->data = new BaseObj();
        $this->lazyLoad = new BaseObj();

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function __get($key)
    {
        if (isset($this->data->{$key})) {
            return $this->data->{$key};
        }

        if (isset($this->lazyLoad->{$key})) {
            $func = $this->lazyLoad->{$key};
            $this->data->{$key} = $func();

            return $this->data->{$key};
        }

        return null;
    }

    public function __set($key, $val)
    {
        if (isset($this->data->{$key}) || isset($this->lazyLoad->{$key})) {
            throw new BaseException('Переменная: ' . $key . ' уже определена.');
        }

        if(!is_string($val) && is_callable($val)){
            $this->lazyLoad->{$key} = $val;
            if (isset($this->data->{$key}))
                unset($this->data->{$key});
        } else {
            $this->data->{$key} = $val;
        }
    }

    public function __isset($key)
    {
        if (isset($this->data->{$key}) || isset($this->lazyLoad->{$key})) {
            return true;
        }
        return false;
    }

    public function first()
    {
        return $this->data->first();
    }
}
