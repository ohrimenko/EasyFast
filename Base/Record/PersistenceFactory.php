<?php

namespace Base\Record;

use Base\Base\DB;

abstract class PersistenceFactory
{

    abstract function getRecord();
    abstract function getModelObjectFactory();
    abstract function getCollection(array $array, $classTarget = null);

    public static function getFactory($target_class)
    {
        switch ($target_class) {
            case '\Base\Model\BaseModel';
                return new BasePersistenceFactory();
                break;
        }
    }
}

abstract class ModelObjectFactory
{
    protected abstract function targetClass();

    public function createObject(array $array)
    {
        $class = $this->targetClass();
        $old = $this->getFromMap($class, $array['id']);
        if ($old) {
            return $old;
        }
        $obj = new $class($array);

        //$obj->build();

        $this->addToMap($obj);
        $obj->markClean();
        return $obj;
    }

    protected function getFromMap($class, $id)
    {
        return \Base\model\ObjectWatcher::exists($class, $id);
    }

    protected function addToMap(\Base\model\ModelObject $obj)
    {
        return \Base\model\ObjectWatcher::add($obj);
    }

}

class BaseModelObjectFactory extends ModelObjectFactory
{
    public function targetClass()
    {
        return "\Base\model\BaseModel";
    }
}

class BasePersistenceFactory extends PersistenceFactory
{
    public function getRecord()
    {
        return new BaseRecord();
    }

    public function getModelObjectFactory()
    {
        return new BaseModelObjectFactory();
    }

    public function getCollection(array $array, $classTarget = null)
    {
        if(!$classTarget){
            abort(404);
        }
        
        return new BaseCollection($array, $classTarget);
    }
}
