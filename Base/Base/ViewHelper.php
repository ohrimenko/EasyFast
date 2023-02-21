<?php

namespace Base\Base;

class ViewHelper {
    static function getRequest() {
        return \Base\base\ApplicationRegistry::getRequest();
    }
    
    static function access($cmd, $status){
        $comand = \Base\base\ApplicationRegistry::getRequest()->getLastCommand();
        
        $class = "\Base\command\\".$cmd;
        
        if(class_exists($class)){
            $class = new \ReflectionClass($class);

            if($class->isInstance($comand)){
                if(in_array($comand->getStatus(), $status)){
                    return true;
                }            
            }
        }         
        
        return false;
    }
}

?>
