<?php

namespace Base\Base;

use \Base;
use \Base\Base\Request;
use \Base\Base\Middleware;

class Route
{
    private static $route;

    private static $routes = [];

    private static $parameters = [
        'promo_code',
        'action_admin',
        'source_url',
    ];

    private static $group_path;
    private static $group_middleware;

    public static function group($args, $function)
    {
        if (isset($args['prefix'])) {
            self::$group_path = $args['prefix'];
        }

        if (isset($args['middleware'])) {
            self::$group_middleware = $args['middleware'];
        }

        $function();

        self::$group_path = null;
        self::$group_middleware = null;
    }

    public static function get($rout, $as, $uses, $middleware = [], $regulares = [],
        $parameters = [], $is_js = false, $sitekey = 'main', $type = '')
    {
        self::addRoute($as, 'get', $rout, $uses, $middleware, $regulares, $parameters, $is_js, '', $sitekey);
    }

    public static function post($rout, $as, $uses, $middleware = [], $regulares = [],
        $parameters = [], $is_js = false, $sitekey = 'main', $type = '')
    {
        self::addRoute($as, 'post', $rout, $uses, $middleware, $regulares, $parameters, $is_js, '', $sitekey);
    }

    public static function put($rout, $as, $uses, $middleware = [], $regulares = [],
        $parameters = [], $is_js = false, $sitekey = 'main', $type = '')
    {
        self::addRoute($as, 'put', $rout, $uses, $middleware, $regulares, $parameters, $is_js, '', $sitekey);
    }

    public static function delete($rout, $as, $uses, $middleware = [], $regulares = [], $parameters = [], $is_js = false, $sitekey = 'main', $type = '')
    {
        self::addRoute($as, 'delete', $rout, $uses, $middleware, $regulares, $parameters, $is_js, '', $sitekey);
    }

    public static function addRoute($as, $method, $rout, $uses, $middleware = [], $regulares = [], $parameters = [], $is_js = false, $prefix = '', $sitekey = 'main', $type = '', $route_id = null)
    {
        if (is_array($rout)) {
            foreach ($rout as $var) {
                $as_new = $as; 
                $method_new = $method; 
                $rout_new = $rout; 
                $uses_new = $uses; 
                $middleware_new = $middleware; 
                $regulares_new = $regulares; 
                $parameters_new = $parameters; 
                $is_js_new = $is_js;
                $prefix_new = $prefix;
                
                if (is_array($var)) {
                    foreach ($var as $key => $value) {
                        switch ($key) {
                            case '0':
                                $rout_new = $value;
                                break;
                            case '1':
                                $prefix_new = $value;
                                
                                break;
                            case '2':
                                if (is_array($value)) {
                                    $middleware_new = array_merge($middleware_new, $value);
                                } else {
                                    $middleware_new[] = $value;
                                }
                                break;
                            case '3':
                                if (is_array($value)) {
                                    $regulares_new = array_merge($regulares_new, $value);
                                } else {
                                    $regulares_new[] = $value;
                                }
                                break;
                            case '4':
                                if (is_array($value)) {
                                    $parameters_new = array_merge($parameters_new, $value);
                                } else {
                                    $parameters_new[] = $value;
                                }
                                break;
                            case '5':
                                $is_js_new = $value;
                                break;
                        }
                    }
                } else {
                    $rout_new = $var;
                }
                
                if(!is_array($rout_new))
                    self::addRoute($as_new, $method_new, $rout_new, $uses_new, $middleware_new, $regulares_new, $parameters_new, $is_js_new, $prefix_new);
            }
            
            return;
        }
        
        $rout = trim($rout, '/');

        if (self::$group_path) {
            $rout = trim(self::$group_path, '/') . '/' . $rout;
        }

        if (self::$group_middleware) {
            $middleware = array_merge(self::$group_middleware, $middleware);
        }

        $rout = '/' . trim($rout, '/');

        preg_match_all("#\{(.*?)\}#", $rout, $matches);

        $regulare_rout = $rout;

        $i = 0;

        $vars = [];

        if (isset($matches[1])) {
            foreach ($matches[1] as $match) {
                $reg = '([^/]*?)';

                if (isset($regulares[$match])) {
                    $reg = '(' . $regulares[$match] . '*?)';
                }

                $regulare_rout = str_replace('{' . $match . '}', $reg, $regulare_rout);

                $vars[++$i] = $match;
            }
        }

        $regulare_rout = "#^" . $regulare_rout . "$#";

        self::$routes[($prefix?$prefix.'.':'').$as] = ['as' => $as, 'uses' => $uses, 'method' => $method, 'rout' =>
            $rout, 'regulare' => $regulare_rout, 'vars' => $vars, 'middleware' => $middleware,
            'parameters' => $parameters, 'is_js' => $is_js, 'prefix' => $prefix, 'type' => $type, 'route_id' => $route_id];
    }

    public static function ctrlNowRout()
    {
        if (!is_null(self::$route['parameters'])) {
            $delete_params = [];

            foreach (Request::req()->get->all() as $key => $value) {
                if (!in_array($key, self::$route['parameters']) && !in_array($key, self::$parameters)) {
                    $delete_params[] = $key;
                }
            }

            if (request()->SourceRequestAction != 'admin-panel' && !data()->is_ajax && !empty($delete_params)) {
                $link = self::now([], $delete_params);
                
               header_sent("HTTP/1.1 301 Moved Permanently");
               header_sent("Location: " . $link);
                
                exit;
            }
        }
        
        if (request()->SourceRequestAction != 'admin-panel' && !data()->is_ajax && preg_match("#\?$#", Request::req()->server->REQUEST_URI)) {
            $link = self::now([]);
            
           header_sent("HTTP/1.1 301 Moved Permanently");
           header_sent("Location: " . $link);
            
            exit;
        }
    }

    public static function isNowRout(array $params = [])
    {
        if (self::$route) {
            return true;
        } else {
            return false;
        }
    }

    public static function now(array $params = [], $delete_params = [], $is_prepare_domain = true, $is_prepare_as = true)
    {
        $parameters = [];

        if (self::$route) {
            if (Request::req()->get->count() || !empty($params)) {
                $parameters = array_merge(Request::req()->get->all(), $params);
            } else {
                $parameters = Request::req()->get->all();
            }
            
            unset($parameters['host_domain']);
            
            $item = components()->getItemPage();
            
            if (empty($item)) {
                $item = (object)$parameters;
            }
            
            if ($is_prepare_as) {
                $key_route = components()->prepareRouteAs(self::$route['as'], $parameters, $item);
            } else {
                $key_route = self::$route['as'];
            }
            
            if (isset(self::$routes[$key_route])) {
                $link = self::$routes[$key_route]['rout'];
            } else {
                $link = self::$route['rout'];
            }

            foreach (self::$route['vars'] as $var) {
                $link = str_replace('{' . $var . '}', Request::req()->vars->{$var}, $link);
            }

            foreach ($delete_params as $key) {
                if (isset($parameters[$key])) {
                    unset($parameters[$key]);
                }
            }

            if (!is_null(self::$route['parameters'])) {
                $del_params = [];

                foreach ($parameters as $key => $value) {
                    if (!in_array($key, self::$route['parameters'])) {
                        $del_params[] = $key;
                    }
                }

                foreach ($del_params as $key => $value) {
                    unset($parameters[$key]);
                }
            }

            if (!empty($parameters)) {
                $link .= '?' . http_build_query($parameters);
            }

            return self::prepareDomain($link, self::$route['as'], $item, $is_prepare_domain);
        }

        return null;
        
        throw new \Base\Base\RouteException('Текущий роут не определен');
    }

    public static function idRout()
    {
        if (self::$route) {
            return self::$route['route_id'];
        }

        return null;
    }

    public static function nowRout()
    {
        if (self::$route) {
            return self::$route['as'];
        }

        return null;
    }

    public static function typeRout()
    {
        if (self::$route) {
            return self::$route['type'];
        }

        return '';
    }

    public static function nowParametersRout()
    {
        if (self::$route) {
            $parameters = [];

            foreach (self::$route['vars'] as $var) {
                $parameters[$var] = Request::req()->vars->{$var};
            }

            $parameters = array_merge(Request::req()->get->all(), $parameters);

            return $parameters;
        }

        throw new \Base\Base\RouteException('Текущий роут не определен');
    }

    public static function route($as, $args = [], $item = null, $is_prepare_domain = true, $is_prepare_as = true)
    {
        if ($is_prepare_as) {
           $key_route = components()->prepareRouteAs($as, $args, $item); 
        } else {
            $key_route = $as;
        }
        
        if ($item) {
            if(is_object($item)){
                
            } elseif(is_array($item)){
                $item = (object)$item;
            }
        } else {
            
        }
        
        if (isset(self::$routes[$key_route])) {
            $link = self::$routes[$key_route]['rout'];

            foreach (self::$routes[$key_route]['vars'] as $var) {
                if (isset($args[$var])) {
                    $link = str_replace('{' . $var . '}', $args[$var], $link);
                    unset($args[$var]);
                } else {
                    throw new \Base\Base\RouteException('Роут: ' . $as . ' не передан параметр - ' .
                        $var);
                }
            }

            if (!is_null(self::$routes[$key_route]['parameters'])) {
                $del_params = [];

                foreach ($args as $key => $value) {
                    if (!in_array($key, self::$routes[$key_route]['parameters']) && !in_array($key, self::$parameters)) {
                        $del_params[] = $key;
                    }
                }

                foreach ($del_params as $key => $value) {
                    unset($args[$value]);
                }
            }

            if (!empty($args)) {
                $link .= '?' . http_build_query($args);
            }

            return self::prepareDomain($link, $as, $item, $is_prepare_domain);
        }
        
        set_http_response_code(404);

        throw new \Base\Base\RouteException('Роут: ' . $as . ' не найден.');
    }

    public static function prepareDomain($link, $rout = null, $item = null, $is_prepare_domain = true)
    {
        return request()->protocol . '//' . Request::req()->server->SERVER_NAME . components()->prepareLink($link, $rout);
    }

    public static function run()
    {
        if (!Base::issetData('domainType')) {
            data()->domainType = 'default';
        }
        
        if (!Base::issetData('domainId')) {
            data()->domainId = null;
        }
        
        foreach (self::$routes as $i => $route) {
            if (request()->server->REQUEST_METHOD && $route['method'] != strtolower(request()->server->REQUEST_METHOD)) {
                continue;
            }

            if (preg_match($route['regulare'], (Request::req()->uri) . '', $matches)) {
                $access = true;
                
                foreach ($route['middleware'] as $middleware) {
                    $access = Middleware::check($middleware);

                    if (!$access) {
                        break;
                    }
                }
                
                if ($access) {
                    self::$route = $route;

                    foreach ($route['vars'] as $key => $var) {
                        Request::req()->vars->{$var} = $matches[$key];
                    }

                    break;
                }
            }
        }

        if (!isset($_SESSION['routes'])) {
            $_SESSION['routes'] = [];
        }

        if (empty($_SESSION['routes']) && isset(self::$routes['main.index'])) {
            self::storeRoute(self::$routes['main.index']);
        }

        if (self::$route) {
            //self::ctrlNowRout();

            $access = true;

            foreach (self::$route['middleware'] as $middleware) {
                $access = Middleware::check($middleware);

                if (!$access) {
                    break;
                }
            }

            if ($access) {
                self::$route['data'] = request()->getDataRequest();

                self::storeRoute(self::$route);

                $parts = explode('@', self::$route['uses'], 2);

                $parts[0] = '\App\Controllers\\' . $parts[0];

                $controller = new $parts[0];

                return $controller->{$parts[1]}();
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }
    }

    private static function storeRoute($route)
    {
        if (!isset($route['data'])) {
            $route['data'] = [];
        }

        if ($route['method'] == 'post') {
            //return;
        }

        $key = key($_SESSION['routes']);
        $end = end($_SESSION['routes']);

        if ($end) {
            if ($route['as'] != $end['as']) {
                $_SESSION['routes'][] = $route;
            }
        } else {
            $_SESSION['routes'][] = $route;
        }

        if (count($_SESSION['routes']) > 10) {
            unset($_SESSION['routes'][$key]);
        }

        reset($_SESSION['routes']);
    }

    public static function lastGetRoute()
    {
        end($_SESSION['routes']);

        while ($prev = prev($_SESSION['routes'])) {
            if ($prev['method'] == 'get') {
                break;
            }
        }

        reset($_SESSION['routes']);

        if ($prev) {
            return $prev;
        } else {
            return current($_SESSION['routes']);
        }
    }

    public static function getRoutes()
    {
        return self::$routes;
    }

    public static function getJsRoutes()
    {
        $str = '';
        
        foreach (self::$routes as $route) {
            if ($route['is_js']) {
                $str .= "'".$route['as']."':{method:'".$route['method']."',rout:'".$route['rout']."'},";
            }
        }
        
        return 'window.Base.Routes = {' . $str . '};';
    }
}
