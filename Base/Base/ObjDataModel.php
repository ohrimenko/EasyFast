<?php

namespace Base\Base;

use \Base;
use \Base\Base\BaseException;
use \Base\Base\BaseObj;
use \Base\Model\ModelObject;

class ObjDataModel
{
    private $data;
    
    private $obj;
    
    private $lazyLoad;
    
    public function __construct(array $data = [])
    {
        $this->data = new BaseObj();
        $this->lazyLoad = new BaseObj();
        
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
    
    public function setObject(ModelObject $obj){
        $this->obj = $obj;
    }

    public function isLazyLoad($key)
    {
        if (!isset($this->data->{$key}) && isset($this->lazyLoad->{$key})) {
            return true;
        }
        
        return false;
    }

    public function __get($key)
    {
        if (isset($this->data->{$key})) {
            return $this->data->{$key};
        }
        
        if (isset($this->lazyLoad->{$key})) {
            $func = $this->lazyLoad->{$key};            
            $this->data->{$key} = $func($this->obj);
            
            return $this->data->{$key};
        }
        
        return null;
    }

    public function __set($key, $val)
    {
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

    public function issetData($key)
    {
        if (isset($this->data->{$key})) {
            return true;
        }
        return false;
    }

    public function issetLazyLoad($key)
    {
        if (isset($this->lazyLoad->{$key})) {
            return true;
        }
        return false;
    }

    public function __unset($key)
    {
        if (isset($this->data->{$key})) {
            unset($this->data->{$key});
        }
        if (isset($this->lazyLoad->{$key})) {
            unset($this->lazyLoad->{$key});
        }
    }
}
