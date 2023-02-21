<?php

namespace App\Controllers;

use \Base;
use \Base\Base\BaseController;
use \Base\Base\Request;
use \Base\Base\DB;
use \Base\Base\BaseObj;

class Cron extends BaseController
{
    public function init()
    {
        $cron = \App\Components\Cron::instance();

        $cron->addTask(['name' => 'nameCronTask', 'duration' => (60 * 60 * 24), 'function' => function () {
            
        }]);
        
        $cron->run();
    }
}
