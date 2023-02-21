<?php

namespace Base\Base;

class BaseException extends \Exception
{
    public function __construct($errorMessage)
    {
        $this->message = $errorMessage;
    }
}

class RouteException extends \Exception
{
    public function __construct($errorMessage)
    {
        $this->message = $errorMessage;
    }
}

class DBException extends \Exception
{
    private $error;
    
    public function __construct(DB_Error $error)
    {
        parent::__construct($error->getMessage(), $error->getCode());
        $this->error = $db_error;
    }

    public function getErrorObject()
    {
        return $this->error;
    }
}
