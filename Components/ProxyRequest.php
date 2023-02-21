<?php

namespace App\Components;

class ProxyRequest
{
    protected static $HTTP_URL_REPLACE = 1;
    protected static $HTTP_URL_JOIN_PATH = 2;
    protected static $HTTP_URL_JOIN_QUERY = 4;
    protected static $HTTP_URL_STRIP_USER = 8;
    protected static $HTTP_URL_STRIP_PASS = 16;
    protected static $HTTP_URL_STRIP_AUTH = 32;
    protected static $HTTP_URL_STRIP_PORT = 64;
    protected static $HTTP_URL_STRIP_PATH = 128;
    protected static $HTTP_URL_STRIP_QUERY = 256;
    protected static $HTTP_URL_STRIP_FRAGMENT = 512;
    protected static $HTTP_URL_STRIP_ALL = 1024;

    protected $url = '';
    protected $method = '';
    protected $headers = [];
    protected $cookie = [];
    protected $get = [];
    protected $post = [];
    protected $files = [];
    protected $user_pwd = null;

    public function __construct($url, $method = 'GET', $headers = [], $cookie = [],
        $get = [], $post = [], $files = [], $user_pwd = null)
    {
        $this->url = $url;

        if ($method) {
            $this->method = $method;
        }

        if (is_array($headers)) {
            $this->headers = $headers;
        }

        if (is_array($cookie)) {
            $this->cookie = $cookie;
        }

        if (is_array($get)) {
            $this->get = $get;
        }

        if (is_array($post)) {
            $this->post = $post;
        }

        if (is_array($files)) {
            $this->files = $files;
        }

        if ($user_pwd) {
            $this->user_pwd = $user_pwd;
        }

        if (isset($this->headers['Cookie']) && !empty($cookie)) {
            unset($this->headers['Cookie']);
        }
    }

    public function get($return_body = false)
    {
        // Initialize and configure our curl session
        $session = curl_init($this->url);

        $proxy = getProxy();

        if ($proxy) {
            curl_setopt($session, CURLOPT_PROXY, $proxy['proxy']);

            if (isset($proxy['userpwd'])) {
                curl_setopt($session, CURLOPT_PROXYUSERPWD, $proxy['userpwd']);
            }

            if (isset($proxy['useragent'])) {
                curl_setopt($session, CURLOPT_USERAGENT, $proxy['useragent']);
            }

            if (isset($proxy['type'])) {
                switch ($proxy['type']) {
                    case 'SOCKS5':
                        curl_setopt($session, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                        break;
                    case 'IPv6':
                        break;
                }
            }
        }

        curl_setopt($session, CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.82 Safari/537.36');

        curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($session, CURLOPT_TIMEOUT, 15);

        $headers = [];

        foreach ($this->headers as $key => $value) {
            $key = strtolower($key);

            if (!in_array($key, [
                'host', 
                'connection', 
                'accept-encoding', 
                'origin', 
                'accept',
                'proxy-base-host', 
                'proxy-connect-key', 
                'content-length',
                
                'proxy-connection',
                'cache-control',
                'transfer-encoding',
                
                'pragma',
            ])) {
                $headers[$key] = $key . ': ' . $value;
            }
        }             

        // HTTP headers
        if (isset($headers['content-type'])) {
            $request_content_type = $headers['content-type'];

            if (stripos($request_content_type, 'multipart/form-data') !== false) {
                $request_content_type = 'multipart/form-data';
                
                $headers['content-type'] = "Content-Type: " . $request_content_type;
            }
        }
        
        $tmpfiles = [];
        
        // This implementation supports POST and GET only, add custom login here as needed
        if ($this->method === 'POST') {
            if (isset($headers['content-type'])) {
                unset($headers['content-type']);
            }
            
            curl_setopt($session, CURLOPT_POST, true);

            $postdata = self::getArrayList($this->post);

            if (!empty($this->files)) {
                $i_key = 0;
                
                //print_r($this->files);
                
                foreach ($this->files as $filekey => $file) {
                    $i_key++;
                    
                    if ($file['tmp_name'] && file_exists($file['tmp_name'])) {
                        if (file_exists(config('DIR_TMP') . '/tmp_' . $i_key)) {
                            unlink(config('DIR_TMP') . '/tmp_' . $i_key);
                        }
                        
                        copy($file['tmp_name'], config('DIR_TMP') . '/tmp_' . $i_key);
                        
                        if (file_exists(config('DIR_TMP') . '/tmp_' . $i_key)) {
                            $tmpfiles[] = config('DIR_TMP') . '/tmp_' . $i_key;
                            
                            $postdata[$filekey] = new \CURLFile(
                                config('DIR_TMP') . '/tmp_' . $i_key, 
                                $file['type'], 
                                basename($file['name'])
                            );
                        }
                    }
                }
            }

            curl_setopt($session, CURLOPT_POSTFIELDS, $postdata);
            //curl_setopt($session, CURLOPT_POSTFIELDS, file_get_contents("php://input"));
        } else {
            curl_setopt($session, CURLOPT_CUSTOMREQUEST, $this->method);
        }

        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);

        if ($this->user_pwd) {
            curl_setopt($session, CURLOPT_USERPWD, $this->user_pwd);
        }
        curl_setopt($session, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($session, CURLOPT_HEADER, true);

        // Here we pass our request cookies to curl's request
        if (!empty($this->cookie)) {
            $cookie_string = '';

            foreach ($this->cookie as $key => $value) {
                $cookie_string .= "$key=$value;";
            }

            curl_setopt($session, CURLOPT_COOKIE, $cookie_string);
        }

        // Finally, trigger the request
        $response = curl_exec($session);

        $result = ['http_code' => 0, 'head' => '', 'headers' => [], 'body' => '', ];

        // Due to CURLOPT_HEADER=1 we will receive body and headers, so we need to split them
        $header_size = curl_getinfo($session, CURLINFO_HEADER_SIZE);
        $response_header = substr($response, 0, $header_size);
        $response_body = substr($response, $header_size);

        $response_header = preg_replace("#^.*\r\n\r\n#sui", '', trim($response_header));

        $response_httpcode = curl_getinfo($session, CURLINFO_HTTP_CODE);
        $response_content_type = curl_getinfo($session, CURLINFO_CONTENT_TYPE);
        $response_error = curl_error($session);
        curl_close($session);
        
        foreach ($tmpfiles as $tmpfile) {
            if (file_exists($tmpfile)) {
                unlink($tmpfile);
            }
        }

        if ($return_body) {
            return $response;
        }

        // This part copies all Set-Cookie headers from curl's response to this php response

        foreach (explode("\r\n", $response_header) as $i => $line) {
            $line = trim($line);

            if ($line && (self::starts_with($line, "Set-Cookie") || self::starts_with($line,
                "WWW-Authenticate") || self::starts_with($line, "Location"))) {
                $tmpexpl = explode(':', $line, 2);

                if (count($tmpexpl) == 2) {
                    $result['headers'][strtolower($tmpexpl[0])] = $line;
                } else {
                    $result['headers'][] = $line;
                }
            }
        }

        // Send the response output
        $response = $response_error ? $response_error : $response_body;

        $result['content_type'] = $response_content_type;
        $result['http_code'] = $response_httpcode;
        $result['head'] = $response_header;
        $result['headers']['content-type'] = 'Content-Type: ' . $response_content_type;

        $result['body'] = $response;

        return $result;
    }

    public static function getArrayList($arr)
    {
        if (!is_array($arr)) {
            return [];
        }

        $result = [];

        $tmp = http_build_query($arr);

        $tmp = explode('&', $tmp);

        $tmp = array_map(function ($row)
        {
            $tmp = explode('=', $row, 2); if (count($tmp) == 2) {
                $tmp[0] = urldecode($tmp[0]); $tmp[1] = urldecode($tmp[1]); }
        else {
            $tmp = [0 => $tmp[0], 1 => '', ]; }

        return $tmp; }
    , $tmp);

    foreach ($tmp as $key => $value) {
        $result[$value[0]] = $value[1];
    }

    return $result;
}


public static function http_build_url($url, $parts = array(), $flags = null, &$new_url =
    array())
{
    if (is_null($flags)) {
        $flags = self::$HTTP_URL_REPLACE;
    }

    is_array($url) || $url = parse_url($url);
    is_array($parts) || $parts = parse_url($parts);

    isset($url['query']) && is_string($url['query']) || $url['query'] = null;
    isset($parts['query']) && is_string($parts['query']) || $parts['query'] = null;

    $keys = array(
        'user',
        'pass',
        'port',
        'path',
        'query',
        'fragment');

    // HTTP_URL_STRIP_ALL and HTTP_URL_STRIP_AUTH cover several other flags.
    if ($flags & self::$HTTP_URL_STRIP_ALL) {
        $flags |= self::$HTTP_URL_STRIP_USER | self::$HTTP_URL_STRIP_PASS | self::$HTTP_URL_STRIP_PORT |
            self::$HTTP_URL_STRIP_PATH | self::$HTTP_URL_STRIP_QUERY | self::$HTTP_URL_STRIP_FRAGMENT;
    } elseif ($flags & self::$HTTP_URL_STRIP_AUTH) {
        $flags |= self::$HTTP_URL_STRIP_USER | self::$HTTP_URL_STRIP_PASS;
    }

    // Schema and host are alwasy replaced
    foreach (array('scheme', 'host') as $part) {
        if (isset($parts[$part])) {
            $url[$part] = $parts[$part];
        }
    }

    if ($flags & self::$HTTP_URL_REPLACE) {
        foreach ($keys as $key) {
            if (isset($parts[$key])) {
                $url[$key] = $parts[$key];
            }
        }
    } else {
        if (isset($parts['path']) && ($flags & self::$HTTP_URL_JOIN_PATH)) {
            if (isset($url['path']) && substr($parts['path'], 0, 1) !== '/') {
                // Workaround for trailing slashes
                $url['path'] .= 'a';
                $url['path'] = rtrim(str_replace(basename($url['path']), '', $url['path']), '/') .
                    '/' . ltrim($parts['path'], '/');
            } else {
                $url['path'] = $parts['path'];
            }
        }

        if (isset($parts['query']) && ($flags & self::$HTTP_URL_JOIN_QUERY)) {
            if (isset($url['query'])) {
                parse_str($url['query'], $url_query);
                parse_str($parts['query'], $parts_query);

                $url['query'] = http_build_query(array_replace_recursive($url_query, $parts_query));
            } else {
                $url['query'] = $parts['query'];
            }
        }
    }

    if (isset($url['path']) && $url['path'] !== '' && substr($url['path'], 0, 1) !==
        '/') {
        $url['path'] = '/' . $url['path'];
    }

    if ($flags & self::$HTTP_URL_STRIP_USER) {
        unset($url[$key]);
    }
    if ($flags & self::$HTTP_URL_STRIP_PASS) {
        unset($url[$key]);
    }
    if ($flags & self::$HTTP_URL_STRIP_PATH) {
        unset($url[$key]);
    }
    if ($flags & self::$HTTP_URL_STRIP_QUERY) {
        unset($url[$key]);
    }
    if ($flags & self::$HTTP_URL_STRIP_FRAGMENT) {
        unset($url[$key]);
    }

    $parsed_string = '';

    if (!empty($url['scheme'])) {
        $parsed_string .= $url['scheme'] . '://';
    }

    if (!empty($url['user'])) {
        $parsed_string .= $url['user'];

        if (isset($url['pass'])) {
            $parsed_string .= ':' . $url['pass'];
        }

        $parsed_string .= '@';
    }

    if (!empty($url['host'])) {
        $parsed_string .= $url['host'];
    }

    if (!empty($url['port'])) {
        $parsed_string .= ':' . $url['port'];
    }

    if (!empty($url['path'])) {
        $parsed_string .= $url['path'];
    }

    if (!empty($url['query'])) {
        $parsed_string .= '?' . $url['query'];
    }

    if (!empty($url['fragment'])) {
        $parsed_string .= '#' . $url['fragment'];
    }

    $new_url = $url;

    return $parsed_string;
}

public static function starts_with($string, $query)
{
    return true;

    return substr($string, 0, strlen($query)) === $query;
}
}
