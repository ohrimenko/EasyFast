<?php

namespace Base\Base;

use \Base;
use \Base\Base\View;
use \Base\Base\Data;

class BaseController
{
    public $app;
    protected $request;

    protected $data;

    public function __construct()
    {
        $this->data = new Data;
        
        $this->app = Base::instance();

        $this->request = Request::req();
        
        data()->data = $this->data;
    }

    public function view($view)
    {
        return new View($view, $this->data);
    }

    public function json($data)
    {
       header_sent("Content-Type: application/json");
        
        echo json_encode($data);
    }
}
