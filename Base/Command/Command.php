<?php

namespace Base\Command;

use Base\Base\DB;

class Command
{

    private static $STATUS_STRINGS = array(
        'CMD_DEFAULT' => 0,
        'CMD_OK' => 1,
        'CMD_ERROR' => 2,
        'CMD_INSUFFICIENT_DATA' => 3,
        'CMD_ERROR_AUTORIZATION' => 4,
        'CMD_ERROR_PRIVILEGES' => 5,
        'CMD_LOCATION' => 6,
        'CMD_MISSING_ROW' => 7,
        'CMD_AJAX' => 8,
        'CMD_NONE_VIEW' => 9,
        'CMD_PARSE' => 10);

    public $res = null;

    private $status = 0;

    final function __construct()
    {
    }

    function execute(\Base\Base\Request $request)
    {
        $this->status = $this->doExecute($request);
        $request->setCommand($this);
    }

    function getStatus()
    {
        return $this->status;
    }

    static function statuses($str = 'CMD_DEFAULT')
    {
        if (empty($str)) {
            $str = 'CMD_DEFAULT';
        }
        return self::$STATUS_STRINGS[$str];
    }

    function doView()
    {
        if ($this->status == self::statuses('CMD_LOCATION')) {
           header_sent("Location: " . $this->res);
            exit();
        } elseif ($this->status == self::statuses('CMD_AJAX')) {
           header_sent('Content-Type: application/json');
            echo $this->res;
            exit();
        } elseif ($this->status == self::statuses('CMD_NONE_VIEW')) {
            return false;
        } else {
            return true;
        }
    }

    function doExecute(\Base\Base\Request $request)
    {
    }
}
