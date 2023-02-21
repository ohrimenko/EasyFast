<?php

namespace Base\Base;

use \Base;
use \Base\Base\BaseObj;
use \Base\Base\MainData;
use \Base\Base\Data;

abstract class BaseData
{
    private $data;

    public function __construct()
    {
        $this->data = new MainData();
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

    public function first()
    {
        $this->data->first();
    }
    
    public function data($model, $data, $params, $emptyFail = false)
    {
        $class = '\App\Models\\'.$model;
        
        if (Base::isPhp7()) {
            $func = $class . '::data';
            return $func($data, $params, $emptyFail);
        } else {
            return $class::data($data, $params, $emptyFail);
        }
    }

    public function arrayUserById(array $args = [])
    {
        $sql = "SELECT * FROM `users` WHERE `id` = ? LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'id', 0);

        return DB::GetRow($sql, $params);
    }

    public function arrayUserByIdActive(array $args = [])
    {
        $sql = "SELECT * FROM `users` WHERE `id` = ? AND `status` = 1 LIMIT 1";

        $params = [];

        $params[1] = array_get($args, 'id', 0);

        return DB::GetRow($sql, $params);
    }

    public function lastInsertId()
    {
        return DB::LastInsertId();
    }
}
