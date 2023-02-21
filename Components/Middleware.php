<?php

namespace App\Components;

use App\Components\ReqDetect;

trait Middleware {
    public function actAdminAuth()
    {
        if (isset($_SESSION['user']) && $_SESSION['user']['login'] == config('loginadmin') &&
            $_SESSION['user']['password'] == config('passwordadmin')) {
            return true;
        } else {
            abortAdminAuth();

            return false;
        }
    }

    public function actNotBot()
    {
        //return true;
        
        if (ReqDetect::isBot()) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                exit;
            }

            redirect('main.index');
        }

        return true;
    }
}
