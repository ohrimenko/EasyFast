<?php

namespace App\Components;

use \Base;
use \Base\Base\BaseController;
use \Base\Base\Request;
use \Base\Base\DB;
use \App\Models\Category;
use \App\Models\Country;
use \App\Models\Project;
use \App\Models\Region;
use \App\Models\User;
use \App\Models\Area;
use \App\Models\City;
use \App\Components\ReqDetect;
use \App\Components\Bot;
use \App\Widgets\LinkPager\LinkPager;
use \Base\Base\Route;

class Cron
{
    protected $tasks_bd = null;

    protected $tasks = [];

    private static $instance = null;

    private function __construct()
    {
    }

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();

            foreach (data()->dataArrayAllCron() as $task) {
                self::$instance->tasks_bd[$task['name']] = $task;
            }

            self::$instance->init();
        }
        return self::$instance;
    }

    public function addTask($task)
    {
        if (is_array($task) && isset($task['name']) && isset($task['duration']) && isset
            ($task['function']) && is_callable($task['function'])) {

            if (!isset($this->tasks_bd[$task['name']])) {
                $new_task = ['name' => $task['name'], 'launch_at' => date("Y-m-d H:i:s"), ];

                data()->insertCron($new_task);

                $new_task['id'] = data()->lastInsertId();

                $this->tasks_bd[$task['name']] = $new_task;
            }

            $this->tasks[$task['name']] = ['name' => $task['name'], 'duration' => $task['duration'],
                'function' => $task['function'], 'from' => isset($task['from']) ? $task['from'] : null,
                'to' => isset($task['to']) ? $task['to'] : null, ];
        }
    }

    public function run()
    {
        foreach ($this->tasks as $task) {
            if ($task['from']) {
                if (date("H:i") < $task['from']) {
                    continue;
                }
            }
            if ($task['to']) {
                if (date("H:i") > $task['to']) {
                    continue;
                }
            }

            if (isset($this->tasks_bd[$task['name']])) {
                if (time() - date_create($this->tasks_bd[$task['name']]['launch_at'])->
                    getTimestamp() > $task['duration']) {
                    data()->updateCron(['id' => $this->tasks_bd[$task['name']]['id'], 'launch_at' =>
                        date("Y-m-d H:i:s")]);
                    $task['function']();
                }
            }
        }
    }

    protected function init()
    {
    }
}
