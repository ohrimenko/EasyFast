<?php

namespace main;

class CurlExec
{
    protected static $total_count = 0;
    protected static $total_time = 0;

    protected $vars;

    public function __isset($key)
    {
        if (array_key_exists($key, $this->vars)) {
            return true;
        }
        
        return false;
    }

    public function __get($key)
    {
        if (isset($this->vars[$key])) {
            return $this->vars[$key];
        }
        return null;
    }

    public function __set($key, $val)
    {
        $this->vars[$key] = $val;
    }

    public function __unset($key)
    {
        if (isset($this->vars[$key])) {
            unset($this->vars[$key]);
        }
    }

    public function count()
    {
        return count($this->vars);
    }

    public function all()
    {
        return $this->vars;
    }

    public function rewind()
    {
        reset($this->vars);
    }

    public function current()
    {
        return current($this->vars);
    }

    public function key()
    {
        return key($this->vars);
    }

    public function next()
    {
        return next($this->vars);
    }

    public function valid()
    {
        $key = key($this->vars);
        
        $var = ($key !== null && $key !== false);
        
        return $var;
    }

    public function uasort($callback)
    {
        if (is_callable($callback)) {
            uasort($this->vars, $callback);
        }
    }
    // Стек ссылок
    public $UrlStack = array();

    private $key_stack = 0;

    // Опции по умочанию
    public $OptionsDefault = array(
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        );

    private $_MaxConnect = 3;
    private $_i = 0;
    private $_active = null;
    private $_status = false;
    private $_mh = null;
    private $_mrc = null;
    private $_ch = null;
    private $_CallbackFunction = null;
    private $_HandleStack = array();
    private $_Handle = null;

    private $_microtime_delay;

    private $proxy = [];
    private $i_proxy = 0;
    private $count_proxy = 0;

    private static $is_php_7 = null;

    private $objParseSource = null;

    public function __construct($options_default = null, $function_default = null, $microtime_delay = null,
        $proxy = null)
    {
        if ($options_default && is_array($options_default)) {
            $this->OptionsDefault = $options_default;
        }

        if ($function_default && ((is_string($function_default) && function_exists($function_default)) || is_array($function_default))) {
            $this->_CallbackFunction = $function_default;
        }

        if (!empty($microtime_delay) && $microtime_delay > 0) {
            $this->_microtime_delay = intval($microtime_delay);
        }

        if ($proxy && is_array($proxy)) {
            foreach ($proxy as $key => $value) {
                $this->addProxy($value);
            }
        }

        $this->count_proxy = count($this->proxy);

        if ($this->count_proxy) {
            shuffle($this->proxy);
        }

        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            self::$is_php_7 = true;
        } else {
            self::$is_php_7 = false;
        }
    }

    public function setObjParseSource($objParseSource)
    {
        if (is_object($objParseSource) || is_null($objParseSource)) {
            $this->objParseSource = $objParseSource;
        }
    }

    public function addProxy($proxy)
    {
        if (is_array($proxy)) {
            if (isset($proxy['proxy'])) {
                if (true || preg_match("#\d{1,3}\.\d{1,3}\.\d{1,3}.\d{1,3}(:\d{1,8})?#", $proxy['proxy'])) {
                    $this->proxy[] = $proxy;
                }
            }
        } else {
            $expl = explode(':', trim($proxy));
            
            if (count($expl) == 4) {
                $this->proxy[] = [
                    'proxy' => $expl[0].':'.$expl[1],
                    'userpwd' => $expl[2].':'.$expl[3],
                ];
            } else {
                $this->proxy[] = ['proxy' => trim($proxy)];
            }
        }
    }

    public function AddUrls($urls = array())
    {
        if (is_array($urls) && !isset($urls['url'])) {
            foreach ($urls as $url) {
                self::AddUrl($url);
            }
        } else {
            self::AddUrl($urls);
        }
    }

    // Добавляем url в стек.
    public function AddUrl($url)
    {
        $this->UrlStack[$this->key_stack++] = $this->prepareUrl($url);
    }

    public function prepareUrl($url)
    {
        if (is_array($url)) {
            if (isset($url['url']) && self::is_url($url['url'])) {
                if (!isset($url['options']) || !is_array($url['options'])) {
                    $url['options'] = null;
                }

                if (!(isset($url['function']) && is_string($url['function']) && function_exists
                    ($url['function'])) && !(isset($url['function']) && 
                    is_array($url['function']) && 
                    isset($url['function'][0]) &&
                    isset($url['function'][1]) && 
                    method_exists(($url['function'][0] == '$this' && $this->
                    objParseSource ? $this->objParseSource : $url['function'][0]), $url['function'][1]))) {
                    $url['function'] = null;
                }

                return $url;
            }
        } else {
            if (self::is_url($url)) {
                return array(
                    'url' => $url,
                    'options' => null,
                    'function' => null);
            }
        }
    }

    // Валидация url
    public function is_url($url)
    {
        return true;

        $chars = "a-zA-Z0-9АаБбВвГгҐґДдЕеЄєЭэЖжЗзИиІіЇїЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЮюЯяЬьЪъёЫы";

        if (preg_match("#((http|https):\/\/|www\.)([" . $chars . "][" . $chars .
            "_-]*(?:.[" . $chars . "][" . $chars . "@\#$%&*().:;_-]*\/{0,1})+):?(d+)?\/?#Diu",
            $url)) {
            return true;
        } else {
            return false;
        }
    }

    // Запрашиваем все страницы паралельными потоками.
    public function ExecuteMulti($max_connect = null)
    {
        if ($max_connect) {
            $this->_MaxConnect = intval($max_connect);
        }

        if (!$this->_mh && !$this->_active && !$this->_ch && !$this->_status) {
            $this->_status = true;

            // 1. множественный обработчик
            $this->_mh = curl_multi_init();

            // 2. добавляем множество URL
            $this->fillMultiStack();

            // 3. инициализация выполнения
            $this->MultiExec();

            // 4. основной цикл
            while ($this->_active && $this->_mrc == CURLM_OK) {
                curl_multi_select($this->_mh);

                // 5. если всё прошло успешно
                if (true) { // curl_multi_select($mh) != -1 Не работает на некоторых версия php. Всегда возвращает -1

                    // 6. делаем дело
                    $this->MultiExec();

                    // 7. если есть инфа?
                    if ($mhinfo = curl_multi_info_read($this->_mh)) {
                        // это значит, что запрос завершился

                        // 8. извлекаем инфу
                        $chinfo = curl_getinfo($mhinfo['handle']);
                        
                        self::$total_time += intval($chinfo['total_time']);
                        
                        self::$total_count++;

                        $chdata = curl_multi_getcontent($mhinfo['handle']); // get results

                        $function = null;

                        $url = array();

                        $keyHandleStack = null;

                        foreach ($this->_HandleStack as $keyHandle => $valueHandle) {
                            if ($valueHandle['ch'] === $mhinfo['handle']) {
                                $keyHandleStack = $keyHandle;
                            }
                        }

                        if (!is_null($keyHandleStack)) {
                            $key = $this->_HandleStack[$keyHandleStack]['i'];

                            if (isset($this->UrlStack[$key])) {
                                $url = $this->UrlStack[$key];
                                if ($this->UrlStack[$key]['function']) {
                                    $function = $this->UrlStack[$key]['function'];
                                }

                                unset($this->UrlStack[$key]);
                            } else {
                                $url = array();
                            }

                            unset($this->_HandleStack[$keyHandleStack]);
                        }

                        if (!$function && $this->_CallbackFunction) {
                            $function = $this->_CallbackFunction;
                        }

                        if ($function && is_string($function) && function_exists($function)) {
                            $function(array_replace($url, array('info' => $chinfo, 'data' => $chdata)));
                        } elseif (is_array($function)) {
                            if ($function[0] == '$this') {
                                $this->objParseSource->{$function[1]}(array_replace($url, array('info' => $chinfo,
                                        'data' => $chdata)));
                            } else {
                                if (self::$is_php_7) {
                                    $callback = $function[0] . '::' . $function[1];
                                    $callback(array_replace($url, array('info' => $chinfo, 'data' => $chdata)));
                                } else {
                                    $function[0]::$function[1](array_replace($url, array('info' => $chinfo, 'data' =>
                                            $chdata)));
                                }
                            }
                        }

                        // 12. чистим за собой
                        curl_multi_remove_handle($this->_mh, $mhinfo['handle']); // в случае зацикливания, закомментируйте данный вызов
                        curl_close($mhinfo['handle']);

                        // 13. добавляем новый url и продолжаем работу
                        if ($this->fillMultiStack() > 0) {
                            $this->MultiExec();
                        }
                    }
                }
            }

            $this->_status = false;

            // 14. завершение
            self::StopMultiCurl();
        }
    }

    private function fillMultiStack()
    {
        $count = 0;

        for ($i = 0; $i < $this->_MaxConnect; $i++) {
            if (count($this->_HandleStack) < $this->_MaxConnect) {
                if ($this->AddUrlToMultiHandle()) {
                    $count++;
                }
            } else {
                break;
            }
        }

        return $count;
    }

    // Запуск дескрипторов стека
    private function MultiExec()
    {
        if ($this->_mh) {
            do {
                $this->_mrc = curl_multi_exec($this->_mh, $this->_active);
            } while ($this->_mrc == CURLM_CALL_MULTI_PERFORM);
        } else {
            self::StopMultiCurl();
        }

    }

    // Добавляем ссылку на выполнение
    private function AddUrlToMultiHandle()
    {
        // если у нас есть ещё url, которые нужно достать
        if ($this->_mh && isset($this->UrlStack[$this->_i]) && isset($this->UrlStack[$this->
            _i]['url'])) {
            if (isset($this->UrlStack[$this->_i]['res']['ctrlPro']) && !$this->UrlStack[$this->_i]['res']['ctrlPro']->isProcess()) {
                $this->_i++;
                
                return false;
            }  
            
            // новый curl обработчик
            $ch = curl_init();

            $this->_HandleStack[] = array('i' => $this->_i, 'ch' => $ch);

            $options = null;

            if (isset($this->UrlStack[$this->_i]['options'])) {
                $options = array_replace($this->OptionsDefault, $this->UrlStack[$this->_i]['options']);
            } else {
                $options = $this->OptionsDefault;
            }

            if ($this->count_proxy) {
                if ($this->i_proxy >= $this->count_proxy) {
                    $this->i_proxy = 0;
                }

                if (isset($this->proxy[$this->i_proxy])) {
                    $options[CURLOPT_PROXY] = $this->proxy[$this->i_proxy]['proxy'];

                    if (isset($this->proxy[$this->i_proxy]['userpwd'])) {
                        $options[CURLOPT_PROXYUSERPWD] = $this->proxy[$this->i_proxy]['userpwd'];
                    }

                    if (isset($this->proxy[$this->i_proxy]['useragent'])) {
                        $options[CURLOPT_USERAGENT] = $this->proxy[$this->i_proxy]['useragent'];
                    }

                    if (isset($this->proxy[$this->i_proxy]['type'])) {
                        switch ($this->proxy[$this->i_proxy]['type']) {
                            case 'SOCKS5':
                                $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
                                break;
                            case 'IPv6':
                                break;
                        }
                    }
                }

                $this->i_proxy++;
            }

            $options[CURLOPT_URL] = $this->UrlStack[$this->_i]['url'];
            $options[CURLOPT_RETURNTRANSFER] = 1;

            curl_setopt_array($ch, $options);

            curl_multi_add_handle($this->_mh, $ch);

            // переходим на следующий url
            $this->_i++;

            return true;
        } else {
            // добавление новых URL завершено
            return false;
        }
    }

    // Очищаем стек
    public function ClearStack()
    {
        $this->UrlStack = array();
        $this->key_stack = 0;
        $this->_HandleStack = array();
        $this->_i = 0;
        $this->_active = null;
        $this->_mh = null;
        $this->_mrc;

        $this->_ch = null;
        $this->_Handle = null;
        $this->_status = false;
    }

    // Закрывает набор cURL дескрипторов
    public function StopMultiCurl()
    {
        if ($this->_mh) {
            foreach ($this->_HandleStack as $key => $value) {
                curl_multi_remove_handle($this->_mh, $value['ch']); // в случае зацикливания, закомментируйте данный вызов
                curl_close($value['ch']);
            }

            curl_multi_close($this->_mh);
        }

        $this->_active = false;
        $this->_mh = null;
        $this->_mrc = null;
        $this->ClearStack();
    }

    // Запрашиваем страницы последовательно
    public function Execute($count_connect = 1)
    {
        if ($count_connect > 1) {
            $this->ExecuteMulti($count_connect);
            return;
        }

        if (!$this->_ch && !$this->_status && !$this->_mh && !$this->_active) {
            $this->_status = true;

            while ($this->_status && isset($this->UrlStack[$this->_i]) && isset($this->
                UrlStack[$this->_i]['url'])) {
                if (isset($this->UrlStack[$this->_i]['res']['ctrlPro'])) {
                    if (!$this->UrlStack[$this->_i]['res']['ctrlPro']->isProcess()) {
                        $this->_i++;
                        
                        continue;
                    }
                }
                
                if ($this->_status) {
                    if ($this->_microtime_delay) {
                        usleep($this->_microtime_delay);
                    }

                    $this->_ch = curl_init();

                    $this->_Handle = array('i' => $this->_i, 'ch' => $this->_ch);

                    $options = null;

                    if (isset($this->UrlStack[$this->_i]['options'])) {
                        $options = array_replace($this->OptionsDefault, $this->UrlStack[$this->_i]['options']);
                    } else {
                        $options = $this->OptionsDefault;
                    }

                    if ($this->count_proxy) {
                        if ($this->i_proxy >= $this->count_proxy) {
                            $this->i_proxy = 0;
                        }

                        if (isset($this->proxy[$this->i_proxy])) {
                            $options[CURLOPT_PROXY] = $this->proxy[$this->i_proxy]['proxy'];

                            if (isset($this->proxy[$this->i_proxy]['userpwd'])) {
                                $options[CURLOPT_PROXYUSERPWD] = $this->proxy[$this->i_proxy]['userpwd'];
                            }

                            if (isset($this->proxy[$this->i_proxy]['useragent'])) {
                                $options[CURLOPT_USERAGENT] = $this->proxy[$this->i_proxy]['useragent'];
                            }

                            if (isset($this->proxy[$this->i_proxy]['type'])) {
                                switch ($this->proxy[$this->i_proxy]['type']) {
                                    case 'SOCKS5':
                                        $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
                                        break;
                                    case 'IPv6':
                                        break;
                                }
                            }
                        }

                        $this->i_proxy++;
                    }

                    $options[CURLOPT_URL] = $this->UrlStack[$this->_i]['url'];
                    $options[CURLOPT_RETURNTRANSFER] = 1;

                    curl_setopt_array($this->_ch, $options);

                    curl_exec($this->_ch);

                    $chinfo = curl_getinfo($this->_ch);
                    
                    self::$total_count++;
                    
                    self::$total_time += intval($chinfo['total_time']);

                    $chdata = curl_multi_getcontent($this->_ch); // get results

                    $function = null;

                    if ($this->UrlStack[$this->_i]['function']) {
                        $function = $this->UrlStack[$this->_i]['function'];
                    }

                    if (!$function && $this->_CallbackFunction) {
                        $function = $this->_CallbackFunction;
                    }

                    if ($function && is_string($function) && function_exists($function)) {
                        $function(array_replace($this->UrlStack[$this->_i], array('info' => $chinfo,
                                'data' => $chdata)));
                    } elseif (is_array($function)) {
                        if ($function[0] == '$this') {
                            $this->objParseSource->{$function[1]}(array_replace($this->UrlStack[$this->_i],
                                array('info' => $chinfo, 'data' => $chdata)));
                        } else {
                            if (self::$is_php_7) {
                                $callback = $function[0] . '::' . $function[1];
                                $callback(array_replace($this->UrlStack[$this->_i], array('info' => $chinfo,
                                        'data' => $chdata)));
                            } else {
                                $function[0]::$function[1](array_replace($this->UrlStack[$this->_i], array('info' =>
                                        $chinfo, 'data' => $chdata)));
                            }
                        }
                    }

                    curl_close($this->_ch);

                    unset($this->UrlStack[$this->_i]);

                    $this->_ch = null;

                    $this->_Handle = null;

                    $this->_i++;
                } else {
                    break;
                }
            }

            self::StopCurl();
        }
    }

    // Останавливаем выполнение curl
    public function StopCurl()
    {
        if ($this->_ch) {
            curl_close($this->_ch);
        }

        $this->_ch = null;
        $this->_Handle = null;
        $this->_status = false;
        $this->ClearStack();
    }

    // Останавливаем выполнение
    public function Stop()
    {
        $this->StopCurl();
        $this->StopMultiCurl();
        $this->ClearStack();
    }

    // Выполняется ли пороцесс
    public function isActive()
    {
        if ($this->_active) {
            return true;
        }

        if ($this->_status) {
            return true;
        }

        return false;
    }

    // Запрашиваем страницу
    public function Exec($url)
    {
        $url = $this->prepareUrl($url);

        if ($this->_microtime_delay) {
            usleep($this->_microtime_delay);
        }

        $ch = curl_init();

        $options = null;

        if (isset($url['options'])) {
            $options = array_replace($this->OptionsDefault, $url['options']);
        } else {
            $options = $this->OptionsDefault;
        }

        if ($this->count_proxy) {
            if ($this->i_proxy >= $this->count_proxy) {
                $this->i_proxy = 0;
            }

            if (isset($this->proxy[$this->i_proxy])) {
                $options[CURLOPT_PROXY] = $this->proxy[$this->i_proxy]['proxy'];

                if (isset($this->proxy[$this->i_proxy]['userpwd'])) {
                    $options[CURLOPT_PROXYUSERPWD] = $this->proxy[$this->i_proxy]['userpwd'];
                }

                if (isset($this->proxy[$this->i_proxy]['useragent'])) {
                    $options[CURLOPT_USERAGENT] = $this->proxy[$this->i_proxy]['useragent'];
                }

                if (isset($this->proxy[$this->i_proxy]['type'])) {
                    switch ($this->proxy[$this->i_proxy]['type']) {
                        case 'SOCKS5':
                            $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
                            break;
                        case 'IPv6':
                            break;
                    }
                }
            }

            $this->i_proxy++;
        }

        $options[CURLOPT_URL] = $url['url'];
        $options[CURLOPT_RETURNTRANSFER] = 1;

        curl_setopt_array($ch, $options);

        curl_exec($ch);

        $chinfo = curl_getinfo($ch);
        
        self::$total_count++;
        
        self::$total_time += intval($chinfo['total_time']);

        $chdata = curl_multi_getcontent($ch); // get results

        curl_close($ch);

        return ['info' => $chinfo, 'data' => $chdata, ];
    }
    
    public static function getTotalTime()
    {
        return self::$total_time;
    }
    
    public static function getTotalCount()
    {
        return self::$total_count;
    }
}
