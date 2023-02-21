<?php

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REDIRECT_URL'] = '/cron/init';
$_SERVER['SERVER_NAME'] = '';
$_SERVER['HTTPS'] = 'on';

require_once (__dir__ . "/../Base/Base.php");

app()->run();
