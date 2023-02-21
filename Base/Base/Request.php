<?php

namespace Base\Base;

use \Base\Base\BaseObj;
use \Base\Base\ObjSess;
use \Base\Base\ObjErr;
use \Base\Base\BaseCookie;

class Request
{
    private static $req;
    
    private $appreg;
    private $properties;
    private $objects = array();
    private $feedback = array();
    private $lastCommand;
    
    public $get;
    public $post;
    public $cookie;
    public $request;
    public $files;
    public $argv;
    public $vars;
    
    public $errors;
    public $headers;
    public $old_request;
    
    public $server;
    
    public $uri;

    private function __construct()
    {
        if(!isset($_SESSION['request'])){
            $_SESSION['request'] = [];
        }
        
        $this->properties = new BaseObj;
        
        $this->get = new BaseObj(isset($_GET) ? prepareRequestData($_GET) : []);
        $this->post = new BaseObj(isset($_POST) ? prepareRequestData($_POST) : []);
        $this->request = new BaseObj(isset($_REQUEST) ? prepareRequestData($_REQUEST) : []);
        $this->server = new BaseObj(isset($_SERVER) ? prepareRequestData($_SERVER) : []);
        $this->files = new BaseObj(isset($_FILES) ? prepareRequestData($_FILES) : []);
        $this->cookie = new BaseCookie(isset($_COOKIE) ?prepareRequestData( $_COOKIE) : []);
        $this->argv = new BaseObj(isset($_SERVER['argv']) ? prepareRequestData($_SERVER['argv']) : []);
        
        $this->vars = new BaseObj([]);
        
        $this->headers = new BaseHeader(prepareRequestData(requestallheaders()));
        
        $this->old_request = new BaseObj(isset($_SESSION['request']['old_request']) ? prepareRequestData($_SESSION['request']['old_request']) : []);
        
        $_SESSION['request']['old_request'] = $_REQUEST;
        
        $this->errors = new ObjErr('errors');
        $this->messages = new ObjSess('messages');
        
        $this->init();
    }
    
    public function getDataRequest()
    {
        return array_merge($this->request->all(), $this->vars->all());
    }

    public static function inst ($new = false)
    {
        if(!self::$req || $new){
            self::$req = new self;
        }
        
        return self::$req;
    }

    public static function req ()
    {
        return self::$req;
    }

    function init()
    {
        // https://worklancer.net/?action_admin=panel&url_admin=https%3A%2F%2Fuslugi.worklancer.net
        
        if ($this->get->source_url) {
            $this->SourceRequestUrl = $this->get->source_url;
        } 
        
        if ($this->headers->{'source-request-url'}) {
            $this->SourceRequestUrl = $this->headers->{'source-request-url'};
        }
        
        if ($this->headers->{'source-request-action'})
            $this->SourceRequestAction = $this->headers->{'source-request-action'};
        
        if ($this->request->{'source-request-action'})
            $this->SourceRequestAction = $this->request->{'source-request-action'};
        
        if ($this->SourceRequestUrl && 
            ($this->server->REDIRECT_URL == '/siteadmin' || 
             $this->server->REDIRECT_URL == '/public/siteadmin')) {
            $this->SourceRequestAction = 'admin-panel';
        } 
        
        if ($this->SourceRequestUrl && $this->SourceRequestAction != 'admin-ctrl') {
            $purl = parse_url($this->SourceRequestUrl);
            
            if (isset($purl['scheme']) && 
                isset($purl['host'])) {
                if (!$this->request->host_domain) {
                    $this->request->host_domain = $purl['host'];
                }
                
                //$this->server->SERVER_NAME = $purl['host'];
                $this->server->REQUEST_SCHEME = $purl['scheme'];
                
                if (isset($purl['path'])) {
                    $this->server->REQUEST_URI = $purl['path'];
                } else {
                    $this->server->REQUEST_URI = '/';
                }
                
                $this->server->REDIRECT_URL = $this->server->REQUEST_URI;
                
                if (isset($purl['query'])) {
                    $this->server->QUERY_STRING = $purl['query'];
                    
                     $this->server->REQUEST_URI .= '?' . $purl['query'];
                } else {
                    $this->server->QUERY_STRING = '';
                }
                
                if (isset($purl['fragment'])) {
                    $this->server->REQUEST_FRAGMENT = $purl['fragment'];
                }
                
                if (isset($purl['port'])) {
                    $this->server->SERVER_PORT = $purl['port'];
                } else {
                    if ($this->server->REQUEST_SCHEME == 'https') {
                        $this->server->SERVER_PORT = 443;
                    } else {
                        $this->server->SERVER_PORT = 80;
                    }
                }
                
                if ($this->server->REQUEST_SCHEME == 'https') {
                    $this->server->HTTPS = 'on';
                } else {
                    $this->server->HTTPS = 'off';
                }
                
                parse_str($this->server->QUERY_STRING, $result);
                
                foreach ($result as $key => $value) {
                    $this->request->{$key} = $this->get->{$key} = $value;
                }
            }
        }
        
        $uri = preg_replace("#^.*?/public#", '', preg_replace("#\?.*$#", "", $this->server->REQUEST_URI));

        $this->uri = components()->repareLink($uri);
        
        if (!$this->server->SERVER_NAME) {
            $this->server->SERVER_NAME = config('domain');
        }   
        
        if ($this->request->host_domain) {
            $this->domain = $this->request->host_domain;
        } elseif($this->server->SERVER_NAME) {
            $this->domain = $this->server->SERVER_NAME;
        } else {
            $this->domain = config('domain');
        }
        
        $this->protocol = config('is_ssl') ? 'https:' : (!$this->server->HTTPS || $this->server->HTTPS == "off" ? 'http:' : 'https:');
    }

    function getProperty($key)
    {
        if (isset($this->properties->{$key})) {
            return $this->properties->{$key};
        }
        return null;
    }

    function setProperty($key, $val)
    {
        $this->properties->{$key} = $val;
    }

    function __clone()
    {
        $this->properties = new BaseObj;
    }

    function addFeedback($msg)
    {
        array_push($this->feedback, $msg);
    }

    function getFeedback()
    {
        return $this->feedback;
    }

    function getFeedbackString($separator = "\n")
    {
        return implode($separator, $this->feedback);
    }

    function setObject($name, $object)
    {
        $this->objects[$name] = $object;
    }

    function getObject($name)
    {
        if (isset($this->objects[$name])) {
            return $this->objects[$name];
        }
        return null;
    }

    function clearLastCommand()
    {
        $this->lastCommand = null;
    }

    function setCommand(\Base\command\Command $command)
    {
        $this->lastCommand = $command;
    }

    function getLastCommand()
    {
        return $this->lastCommand;
    }

    function __get($key)
    {
        if (isset($this->properties->{$key})) {
            return $this->properties->{$key};
        }
        
        if(isset($this->vars->{$key})){
            return $this->vars->{$key};
        }
        
        if(isset($this->get->{$key})){
            return $this->get->{$key};
        }
        
        if(isset($this->post->{$key})){
            return $this->post->{$key};
        }
        
        if(isset($this->request->{$key})){
            return $this->request->{$key};
        }
        
        if(isset($this->argv->{$key})){
            return $this->argv->{$key};
        }
        
        return null;
    }

    function __set($key, $val)
    {
        $this->properties->{$key} = $val;
    }

    function __isset($key)
    {
        if (isset($this->properties->{$key})) {
            return true;
        }
        
        if(isset($this->vars->{$key})){
            return true;
        }
        
        if(isset($this->get->{$key})){
            return true;
        }
        
        if(isset($this->post->{$key})){
            return true;
        }
        
        if(isset($this->request->{$key})){
            return true;
        }
        
        if(isset($this->argv->{$key})){
            return true;
        }
        return false;
    }

    function getVar($key)
    {
        return $this->vars->{$key};
    }

    function setVar($key, $val)
    {
        $this->vars->{$key} = $val;
    }
}
