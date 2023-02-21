<?php

namespace Base\Model;

use Base\Base\DB;

interface BaseCollection extends \Iterator
{
    function add(ModelObject $event);
}

namespace Base\Record;

class BaseCollection extends Collection implements \Base\Model\BaseCollection
{
    public $classTarget = null;
    
    function __construct(array $raw = null, $classTarget)
    {
        $this->raw = $raw;
        $this->total = count($raw);
            
        $this->classTarget = $classTarget;
    }
    
    function targetClass()
    {
        return "\Base\Model\BaseModel";
    }
    
    protected function getRow($num)
    {
        $this->notifyAccess();
        if ($num >= $this->total || $num < 0) {
            return null;
        }
        if (isset($this->objects[$num])) {
            return $this->objects[$num];
        }

        if (isset($this->raw[$num])) {
            $classTarget = $this->classTarget;
            
            if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
                $callback = $classTarget . '::' . 'createObject';
                
                $this->objects[$num] = $callback($this->raw[$num]);
            } else {
                $this->objects[$num] = $classTarget::createObject($this->raw[$num]);
            }
            
            return $this->objects[$num];
        }
    }
}
