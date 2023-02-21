<?php

namespace Base\Controller;

use Base\App;
use Base\Base\Request;

class AppController
{
    private static $base_cmd = null;
    private static $default_cmd = null;
    private $controllerMap;
    private $invoked = array();

    public static $cmd = 'cmd';

    function __construct(ControllerMap $map)
    {
        $this->controllerMap = $map;
        if (is_null(self::$base_cmd)) {
            self::$base_cmd = new \ReflectionClass("\Base\command\Command");
            self::$default_cmd = new \Base\command\Command();
        }
    }

    function reset()
    {
        $this->invoked = array();
    }

    function getView(Request $req)
    {
        $view = $this->getResource($req, "View");
        return $view;
    }

    private function getForward(Request $req)
    {
        $forward = $this->getResource($req, "Forward");
        if ($forward) {
            $req->setProperty(self::$cmd, $forward);
        }
        return $forward;
    }

    private function getResource(Request $req, $res)
    {
        $cmd_str = $req->getProperty(self::$cmd);
        $previous = $req->getLastCommand();
        $status = $previous->getStatus();
        if (!isset($status) || !is_int($status)) {
            $status = 0;
        }
        $acquire = "get$res";
        $resource = $this->controllerMap->$acquire($cmd_str, $status);
        if (is_null($resource)) {
            $resource = $this->controllerMap->$acquire($cmd_str, 0);
        }
        if (is_null($resource)) {
            $resource = $this->controllerMap->$acquire('Command', $status);
        }
        if (is_null($resource)) {
            $resource = $this->controllerMap->$acquire('Command', 0);
        }
        return $resource;
    }

    function getCommand(Request $req)
    {
        $previous = $req->getLastCommand();
        if (is_null($previous)) {
            $cmd = $req->getProperty(self::$cmd);
            if (is_null($cmd)) {
                $req->setProperty(self::$cmd, 'Command');
                return self::$default_cmd;
            }
        } else {
            $cmd = $this->getForward($req);
            if (is_null($cmd)) {
                return null;
            }
        }

        $cmd_obj = $this->resolveCommand($cmd);
        if (is_null($cmd_obj)) {
            $req->setProperty(self::$cmd, 'NotFound');
            $cmd_obj = new \Base\command\NotFound();
        }

        $cmd_class = get_class($cmd_obj);
        if (isset($this->invoked[$cmd_class])) {
            throw new \Base\base\AppException("circular forwarding");
        }

        $this->invoked[$cmd_class] = 1;
        return $cmd_obj;
    }

    function resolveCommand($cmd)
    {
        $cmd = str_replace(array(
            '.',
            '/',
            '\\'), "", $cmd);

        $cmd = preg_replace("#[^a-zA-Z0-9]#", "", $cmd);

        $classroot = $this->controllerMap->getClassroot($cmd);

        $filepath = "Base/command/$classroot.php";
        $classname = "\\Base\\command\\$classroot";
        if (class_exists($classname) || \Base::autoload($classname)) {
            if (class_exists($classname)) {
                $cmd_class = new \ReflectionClass($classname);
                if ($cmd_class->isSubClassOf(self::$base_cmd)) {
                    return $cmd_class->newInstance();
                }
            }
        }
        return null;
    }
}

class ControllerMap
{
    private $viewMap = array();
    private $forwardMap = array();
    private $classrootMap = array();

    function addClassroot($command, $classroot)
    {
        $this->classrootMap[$command] = $classroot;
    }

    function getClassroot($command)
    {
        if (isset($this->classrootMap[$command])) {
            return $this->classrootMap[$command];
        }
        return $command;
    }

    function addView($command = 'Command', $status = 0, $view = '')
    {
        $this->viewMap[$command][$status] = $view;
    }

    function getView($command, $status)
    {
        if (isset($this->viewMap[$command][$status])) {
            return $this->viewMap[$command][$status];
        } elseif (isset($this->viewMap['default'][$status])) {
            return $this->viewMap['default'][$status];
        }
        return null;
    }

    function addForward($command, $status = 0, $newCommand = '')
    {
        $this->forwardMap[$command][$status] = $newCommand;
    }

    function getForward($command, $status)
    {
        if (isset($this->forwardMap[$command][$status])) {
            return $this->forwardMap[$command][$status];
        } elseif (isset($this->forwardMap['default'][$status])) {
            return $this->forwardMap['default'][$status];
        }
        return null;
    }
}
