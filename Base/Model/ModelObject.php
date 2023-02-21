<?php

namespace Base\Model;

use \Base\Base\DB;
use \Base\Base\ObjDataModel;
use \Base\Base\Data;

abstract class ModelObject implements \JsonSerializable
{
    const TABLE = null;

    protected $properties = array('id' => null);

    protected $columns = array();

    protected $selects = array();
    protected $updates = array();

    protected $data = null;

    const WATCHER = false;

    public $events = [];

    public function __construct($arg = array())
    {
        $this->data = new ObjDataModel();
        $this->data->setObject($this);

        if (is_array($arg)) {
            if (!isset($arg['id']) || is_null($arg['id'])) {
                if (static::WATCHER)
                    $this->markNew();
            }

            foreach ($arg as $key => $value) {
                $this->properties[$key] = $value;

                $this->selects[$key] = $value;
            }
        }

        $this->build();
    }

    public function jsonSerialize() : mixed
    {
        return $this->properties;
    }

    public function isLazyLoad($key)
    {
        return $this->data->isLazyLoad($key);
    }

    public function build()
    {
    }

    public function isWatcher()
    {
        if (static::WATCHER) {
            return true;
        } else {
            return false;
        }
    }

    public function markNew()
    {
        ObjectWatcher::addNew($this);
    }

    public function markDeleted()
    {
        ObjectWatcher::addDelete($this);
    }

    public function markDirty()
    {
        ObjectWatcher::addDirty($this);
    }

    public function markClean()
    {
        ObjectWatcher::addClean($this);
    }

    public function save()
    {
        ObjectWatcher::saveObject($this);
    }

    public function delete()
    {
        ObjectWatcher::deleteObject($this);
    }

    public function remove()
    {
        ObjectWatcher::unsetObject($this);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function collection()
    {
        return self::getCollection(get_class($this));
    }

    public function finder()
    {
        return self::getFinder(get_class($this));
    }

    public static function getFinder($type = null)
    {
        if (is_null($type)) {
            return HelperFactory::getFinder(get_called_class());
        }
        return HelperFactory::getFinder($type);
    }

    public static function getCollection($type = null)
    {
        if (is_null($type)) {
            return HelperFactory::getCollection(get_called_class());
        }
        return HelperFactory::getCollection($type);
    }

    public static function findAll()
    {
        $finder = self::getFinder();
        return $finder->findAll();
    }

    public static function GetAll($sql, $array)
    {
        $finder = self::getFinder();
        return $finder->GetAll($sql, $array);
    }

    public static function find($id)
    {
        $finder = self::getFinder();
        return $finder->find($id);
    }

    public static function Get($sql, $array)
    {
        $finder = self::getFinder();
        return $finder->get($sql, $array);
    }

    public function __clone()
    {
        $this->id = -1;
    }

    public function __get($key)
    {
        if (isset($this->properties[$key])) {
            return $this->properties[$key];
        }
        if (isset($this->data->{$key})) {
            return $this->data->{$key};
        }
        return null;
    }

    public function __call($method, $args)
    {
        return null;
    }

    public static function __callStatic($method, $args)
    {
        return null;
    }

    public function isUpdate($key)
    {
        if (isset($this->updates[$key])) {
            return true;
        }

        return false;
    }

    public function getSelect($key)
    {
        if (isset($this->selects[$key])) {
            return $this->selects[$key];
        }
        return null;
    }

    public function getProperty($key)
    {
        if (isset($this->properties[$key])) {
            return $this->properties[$key];
        }
        return null;
    }

    public function setProperty($key, $val)
    {
        if (isset($this->columns[$key])) {
            $this->properties[$key] = $val;

            $this->updates[$key] = ($val);

            if (static::WATCHER)
                $this->markDirty();
        }
    }

    public function getData($key)
    {
        if (isset($this->data->{$key})) {
            return $this->data->{$key};
        }
        return null;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function __set($key, $val)
    {
        if (isset($this->columns[$key])) {
            if (!isset($this->properties[$key]) || $this->properties[$key] != $val) {
                $this->properties[$key] = $val;

                if ($this->id) {
                    $this->updates[$key] = $val;
                }

                if (static::WATCHER) {
                    $this->markDirty();
                }
            }
        } else {
            $this->data->{$key} = $val;
        }
    }

    public function __isset($key)
    {
        if (array_key_exists($key, $this->properties) || isset($this->data->{$key})) {
            return true;
        }
        return false;
    }

    public function issetProperties($key)
    {
        if (array_key_exists($key, $this->properties)) {
            return true;
        }
        return false;
    }

    public function issetData($key)
    {
        if ($this->data->issetData($key)) {
            return true;
        }
        return false;
    }

    public function __unset($key)
    {
        if (isset($this->properties[$key])) {
            unset($this->properties[$key]);
        }
        if (isset($this->data->{$key})) {
            unset($this->data->{$key});
        }
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getColumnsInsert()
    {
        $data = array();

        foreach ($this->properties as $key => $value) {
            if (isset($this->columns[$key]) && !is_null($value)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public function getColumnsUpdate()
    {
        $data = array();

        foreach ($this->updates as $key => $value) {
            if (isset($this->columns[$key]) && array_key_exists($key, $this->properties)) {
                $data[$key] = array('value' => $this->properties[$key], 'type' => $this->
                        columns[$key]);

                unset($this->updates[$key]);
            }
        }

        return array('where' => array('id' => array('value' => $this->id, 'type' =>
                        'integer 11')), 'set' => $data);
    }

    public static function table()
    {
        return static::TABLE;
    }
}

?>