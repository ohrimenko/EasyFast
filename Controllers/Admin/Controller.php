<?php

namespace App\Controllers\Admin;

use \Base;
use \Base\Base\BaseController;
use \Base\Base\Request;
use \Base\Base\DB;
use \Base\Base\BaseObj;
use \Base\Base\Route;
use \Base\Base\Data;
use \Base\Base\MainData;
use App\Components\ReqDetect;

class Controller extends \App\Controllers\Controller
{
    public $markersMap = [];

    public function view($view)
    {
        $out = parent::view($view);
        
        $out .= widget('Admin', ['placeType' => 'stats-pnl'], false);
        
        return $out;
    }
}
