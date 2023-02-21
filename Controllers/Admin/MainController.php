<?php

namespace App\Controllers\Admin;

use \Base;
use \Base\Base\BaseController;
use \Base\Base\Request;
use \Base\Base\DB;
use \App\Components\ReqDetect;
use \App\Widgets\LinkPager\LinkPager;
use \Base\Base\Route;
use \Base\Base\Mail;
use App\Controllers\Admin\Controller;
use App\Models\Product;

class MainController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        return $this->view('/admin/main/index');
    }
}
