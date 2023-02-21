<?php

use App\Components\Multithread;
use App\Components\MultithreadAlibaba;

set_time_limit(0);

require_once (__dir__ . "/Base/Base.php");

if (isset($_SERVER) && isset($_SERVER['argv']) && isset($_SERVER['argv'][1])) {
    switch ($_SERVER['argv'][1]) {
        case 'TaskExample':
            $multithread = new Multithread();

            $multithread->addTask('App\Components\MultithreadTaskExample@exec', []);

            $multithread->run(1);

            break;
    }
}
