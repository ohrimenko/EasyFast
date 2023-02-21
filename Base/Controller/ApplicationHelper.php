<?php

namespace Base\Controller;

use main;

class ApplicationHelper
{
    private static $instance = null;
    private $config = null;
    private $options = [];

    private function __construct()
    {
        $this->config = \Base::app()->config('SITE_ROOT') . "/config/Options.xml";
        if (\Base::app()->config('TYPE_OPTIONS') == 'array') {
            if (isset($GLOBALS['app_options'])) {
                $this->options = $GLOBALS['app_options'];
            } elseif (file_exists(\Base::app()->config('SITE_ROOT') . '/config/Options.php')) {
                $this->options = require (\Base::app()->config('SITE_ROOT') .
                    '/config/Options.php');
            }
        }
    }

    static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    function init($configpath = null)
    {
        if (\Base::app()->config('TYPE_OPTIONS') == 'array') {
            $map = new ControllerMap();
            foreach ($this->options as $type => $cmds) {
                foreach ($cmds as $cmd => $data) {
                    if ($type == 'forwards') {
                        foreach ($data as $status => $view) {
                            $map->addForward($cmd, \Base\command\Command::statuses($status), $view);
                        }
                    }
                    if ($type == 'views') {
                        foreach ($data as $status => $view) {
                            $map->addView($cmd, \Base\command\Command::statuses($status), $view);
                        }
                    }
                    if ($type == 'classaliases') {
                        $map->addClassroot($cmd, $data);
                    }

                }
            }

            \Base\base\ApplicationRegistry::setControllerMap($map, false);
            return;
        }

        if (\Base\base\ApplicationRegistry::isRelevanceControllerMap()) {
            $map = \Base\base\ApplicationRegistry::getControllerMap();

            if (!is_null($map)) {
                if (!is_null($configpath)) {
                    $this->configpath = $configpath;
                }

                return;
            }
        }

        $this->getOptions();
    }

    private function getOptions()
    {
        $this->ensure(file_exists($this->config), "Could not find options file");

        $options = simplexml_load_file($this->config);

        $this->ensure($options instanceof \SimpleXMLElement,
            "Could not resolve options file");

        $map = new ControllerMap();

        foreach ($options->control->view as $default_view) {
            $stat_str = trim($default_view['status']);
            if (empty($stat_str)) {
                $stat_str = "CMD_DEFAULT";
            }
            $status = \Base\command\Command::statuses($stat_str);
            $map->addView('default', $status, (string )$default_view);
        }

        foreach ($options->control->status as $default_status) {
            $view = trim((string )$default_status->view);
            $forward = trim((string )$default_status->forward);
            $stat_str = trim($default_status['value']);
            $status = \Base\command\Command::statuses($stat_str);
            if ($view) {
                $map->addView('default', $status, $view);
            }
            if ($forward) {
                $map->addForward('default', $status, $forward);
            }
        }

        foreach ($options->control->command as $command_view) {
            $command = trim((string )$command_view['name']);
            if ($command_view->classalias) {
                $classroot = trim((string )$command_view->classalias['name']);
                $map->addClassroot($command, $classroot);
            }
            if ($command_view->view) {
                $view = trim((string )$command_view->view);
                $forward = trim((string )$command_view->forward);
                $map->addView($command, 0, $view);
                if ($forward) {
                    $map->addForward($command, 0, $forward);
                }

            }
            foreach ($command_view->status as $command_view_status) {
                $view = trim((string )$command_view_status->view);
                $forward = trim((string )$command_view_status->forward);
                $stat_str = trim($command_view_status['value']);
                $status = \Base\command\Command::statuses($stat_str);
                if ($view) {
                    $map->addView($command, $status, $view);
                }
                if ($forward) {
                    $map->addForward($command, $status, $forward);
                }
            }
        }

        \Base\base\ApplicationRegistry::setControllerMap($map);
    }

    private function ensure($expr, $message)
    {
        if (!$expr) {
            throw new \Base\base\AppException($message);
        }
    }
}

?>
