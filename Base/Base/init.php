<?php

namespace Base\Base;

use Base;

ini_set('display_errors', Base::app()->config('DEBUGGING') ? 'on' : 'off');
ini_set('error_reporting', Base::app()->config('ERROR_TYPES'));

if (!isset($_SESSION)) {
    session_start();
}
setlocale(LC_ALL, 'ru_RU.utf8', 'rus_RUS.utf8', 'ru_RU.CP1251', 'rus_RUS.CP1251', 'Russian_Russia.1251', 'russian');
mb_internal_encoding("utf-8");

// рандомизировать микросекундами
function make_seed()
{
    list($usec, $sec) = explode(' ', microtime());
    return intval((float)$sec + ((float)$usec * 100000));
}
srand(make_seed());

define('URL_SEPARATOR', '&');
