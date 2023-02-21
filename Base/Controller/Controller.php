<?php

namespace Base\Controller;

class Controller
{
    private $applicationHelper;
    private $view;

    private function __construct()
    {
    }

    static function run()
    {
        $instance = new Controller();
        $instance->init();
        $instance->handleRequest();
    }

    function init()
    {
        $applicationHelper = ApplicationHelper::instance();
        $this->view = new \Base\Base\View();
        $applicationHelper->init();
    }

    function handleRequest()
    {
        $request = \Base\base\ApplicationRegistry::getRequest();
        $app_c = \Base\base\ApplicationRegistry::appController();

        while ($cmd = $app_c->getCommand($request)) {
            $cmd->execute($request);
        }

        \Base\model\ObjectWatcher::instance()->performOperations();

        if ($request->getLastCommand()->doView()) {
            $this->invokeView($app_c->getView($request));
        }
    }

    function invokeView($target)
    {
        $this->view->render($target);
    }
}