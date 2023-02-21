<?php

use App\Components\Multithread;

set_time_limit(0);

require_once (__dir__ . "/Base/Base.php");

if (isset($_SERVER) && isset($_SERVER['argv']) && isset($_SERVER['argv'][1])) {
    $tmpexpl = explode(':', $_SERVER['argv'][1], 2);

    if (count($tmpexpl) == 2) {
        if ($tmpexpl[0] == 'multithreadruntask') {
            Multithread::runTask($tmpexpl[1]);
        }
    }
}
