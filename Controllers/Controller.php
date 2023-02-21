<?php

namespace App\Controllers;

use \Base;
use \Base\Base\BaseController;
use \Base\Base\Request;
use \Base\Base\DB;
use \Base\Base\BaseObj;
use \Base\Base\Route;
use \Base\Base\Data;
use \Base\Base\MainData;
use App\Components\ReqDetect;

class Controller extends BaseController
{
    public $markersMap = [];

    public function view($view)
    {
        $this->data->markersMap = $this->markersMap;
        
        Route::ctrlNowRout();
        
        $out = '';
        
        $out .= parent::view($view)->render(true);
        
        return $out;
    }
}
