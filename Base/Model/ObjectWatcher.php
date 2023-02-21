<?php

namespace Base\Model;

use Base\Base\DB;

class ObjectWatcher
{
    static $test = false;

    private $all = array();
    private $dirty = array();
    private $new = array();
    private $delete = array();
    private static $instance = null;

    private function __construct()
    {
    }

    static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new ObjectWatcher();
        }
        return self::$instance;
    }

    function globalKey(ModelObject $obj)
    {
        $key = get_class($obj) . "." . $obj->getId();
        return $key;
    }

    static function add(ModelObject $obj)
    {
        if (ObjectWatcher::$test) {
            echo $obj->getName() . "(add of all)<br />";
        }

        $inst = self::instance();
        $inst->all[$inst->globalKey($obj)] = $obj;
    }

    static function exists($classname, $id)
    {
        if (ObjectWatcher::$test) {
            echo $obj->getName() . "(exists)<br />";
        }

        $inst = self::instance();
        $key = "$classname.$id";
        if (isset($inst->all[$key])) {
            return $inst->all[$key];
        }
        return null;
    }

    static function addDelete(ModelObject $obj)
    {
        if (ObjectWatcher::$test) {
            echo $obj->getName() . "(add of delete)<br />";
        }

        $self = self::instance();
        $self->delete[$self->globalKey($obj)] = $obj;
    }

    static function addDirty(ModelObject $obj)
    {
        if (ObjectWatcher::$test) {
            echo $obj->getName() . "(add of dirty)<br />";
        }

        $inst = self::instance();
        if (!in_array($obj, $inst->new, true)) {
            $inst->dirty[$inst->globalKey($obj)] = $obj;
        }
    }

    static function addNew(ModelObject $obj)
    {
        if (ObjectWatcher::$test) {
            echo $obj->getName() . "(add of new)<br />";
        }

        $inst = self::instance();
        // we don't yet have an id
        $inst->new[] = $obj;
    }

    static function addClean(ModelObject $obj)
    {
        if (ObjectWatcher::$test) {
            echo $obj->getName() . "(clean)<br />";
        }

        $self = self::instance();
        unset($self->delete[$self->globalKey($obj)]);
        unset($self->dirty[$self->globalKey($obj)]);

        $self->new = array_filter($self->new, function ($a)use ($obj)
        {
            return !($a === $obj); }
        );
    }

    static function saveObject(ModelObject $obj)
    {
        $self = self::instance();

        foreach ($self->new as $key => $value) {
            if ($obj === $value) {
                if (isset($obj->events['save'])) {
                    $func = $obj->events['save'];
                    $func($obj);
                }

                if (isset($obj->events['insert'])) {
                    $func = $obj->events['insert'];
                    $func($obj);
                }

                $obj->finder()->insert($obj);

                if (isset($obj->events['insert-after'])) {
                    $func = $obj->events['insert-after'];
                    $func($obj);
                }

                if (isset($obj->events['save-after'])) {
                    $func = $obj->events['save-after'];
                    $func($obj);
                }

                unset($self->new[$key]);
            }
        }

        if (isset($self->dirty[$self->globalKey($obj)])) {
            if (isset($obj->events['save'])) {
                $func = $obj->events['save'];
                $func($obj);
            }

            if (isset($obj->events['update'])) {
                $func = $obj->events['update'];
                $func($obj);
            }

            $obj->finder()->update($obj);

            if (isset($obj->events['update-after'])) {
                $func = $obj->events['update-after'];
                $func($obj);
            }

            if (isset($obj->events['save-after'])) {
                $func = $obj->events['save-after'];
                $func($obj);
            }
            unset($self->dirty[$self->globalKey($obj)]);
        }
    }

    static function deleteObject(ModelObject $obj)
    {
        if (isset($obj->events['delete'])) {
            $func = $obj->events['delete'];
            $func($obj);
        }

        $obj->finder()->delete($obj);

        if (isset($obj->events['delete-after'])) {
            $func = $obj->events['delete-after'];
            $func($obj);
        }

        self::unsetObject($obj);
    }

    static function unsetObject(ModelObject $obj)
    {
        $self = self::instance();

        $key = $self->globalKey($obj);

        foreach ($self->new as $key => $value) {
            if ($obj === $value) {
                unset($self->new[$key]);
            }
        }

        if (isset($self->dirty[$key])) {
            unset($self->dirty[$key]);
        }

        if (isset($self->delete[$key])) {
            unset($self->delete[$key]);
        }

        if (isset($self->all[$key])) {
            unset($self->all[$key]);
        }
    }

    function performOperations()
    {
        if (ObjectWatcher::$test) {
            echo "(ObjectWatcher::performOperations)<br />";
        }

        foreach ($this->dirty as $key => $obj) {
            if (isset($obj->events['save'])) {
                $func = $obj->events['save'];
                $func($obj);
            }

            if (isset($obj->events['update'])) {
                $func = $obj->events['update'];
                $func($obj);
            }

            $obj->finder()->update($obj);

            if (isset($obj->events['update-after'])) {
                $func = $obj->events['update-after'];
                $func($obj);
            }

            if (isset($obj->events['save-after'])) {
                $func = $obj->events['save-after'];
                $func($obj);
            }
        }

        foreach ($this->new as $key => $obj) {
            if (isset($obj->events['save'])) {
                $func = $obj->events['save'];
                $func($obj);
            }

            if (isset($obj->events['insert'])) {
                $func = $obj->events['insert'];
                $func($obj);
            }

            $obj->finder()->insert($obj);

            if (isset($obj->events['insert-after'])) {
                $func = $obj->events['insert-after'];
                $func($obj);
            }

            if (isset($obj->events['save-after'])) {
                $func = $obj->events['save-after'];
                $func($obj);
            }
        }
        $this->dirty = array();
        $this->new = array();
    }
}

?>
