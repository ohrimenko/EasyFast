<?php

namespace Base\Base;

use \Base\Base\ObjSess;

class ObjErr extends ObjSess
{
    public function __get($key)
    {
        if (isAccesOldRequest() && isset($this->vars[$key])) {
            return $this->vars[$key];
        }
        return null;
    }
}
