<?php

namespace App\Components;

use \Base;

class ReqDetect
{
    /**
     * Флаг подключения всегда активных Ip адресов.
     */
    const isBotIp = true;
    /**
     * Флаг сохранять ли Ip адреса ботов.
     */
    const isSaveBotIp = true;
    /**
     * Флаг подключения всегда заблокированных пользователей.
     */
    const isNotBotIp = true;
    /**
     * Флаг нужно ли учитывать заголовки поисковых ботов.
     */
    const isBotAgent = true;
    /**
     * Флаг контроля по JS.
     */
    const isControlJs = true;
    /**
     * null - без ajax и перезагрузки.
     * true - ajax запрос.
     * false - Перезагрузка страницы.
     */
    const isUpdateJs = null;
    /**
     * Флаги контроля по JS.
     */
    const defaultIsBotControlJs = true;
    const maxCountControlJs = 3;
    const maxCountRenderJs = 15;
    const isSaveControlJsBot = false;

    const isControlInDB = false;

    /**
     * время актуальности токена секунд.
     */
    const timeActualToken = 120;

    private $routeLoadAjax = 'load.php';
    
    public $botname = '';

    private static $instance = null;
    private static $SxGeo = null;
    private $isBot = null;
    private $isJsCoookie = null;
    private $isMobile = null;
    private $ip = null;
    private $country = null;

    private static $pathSxGeo = 'SxGeo.php';
    private static $pathSxGeoCityDat = 'data/SxGeoCity.dat';

    /**
     * Список Ip адресов ботов.
     */
    protected $pathBotIp = 'data/bot_ip.php';
    protected $botIp = array();
    /**
     * Список Ip адресов ботов/
     */
    protected $pathNotBotIp = 'data/not_bot_ip.php';
    protected $notBotIp = array();
    /**
     * Список заголовков которые используют поисковики.
     */
    protected $pathBotsAgent = 'data/bots_agent.php';
    protected $botsAgent = array();

    protected $pathMobileAgent = 'data/mobile_agent.php';
    protected $mobileAgent = array();

    protected static $isSrand = null;

    public $ctrl_cnt_data = array('sqlite_db' => 'ctrl_cnt.db', );

    public $ctrl_cnt_pdo = null;

    public $infObj = null;

    protected function __construct()
    {
        $this->infObj = new Info;
        
        //self::$pathSxGeo = __dir__ . '/../api_files/geo/SxGeo.php';
        //self::$pathSxGeoCityDat = __dir__ . '/../api_files/geo/SxGeo.dat';

        $this->pathBotIp = Base::app()->config('SITE_ROOT') . DIRECTORY_SEPARATOR .
            'config' . DIRECTORY_SEPARATOR . 'bot_ip.php';
        $this->pathNotBotIp = Base::app()->config('SITE_ROOT') . DIRECTORY_SEPARATOR .
            'config' . DIRECTORY_SEPARATOR . 'not_bot_ip.php';
        $this->pathBotsAgent = Base::app()->config('SITE_ROOT') . DIRECTORY_SEPARATOR .
            'config' . DIRECTORY_SEPARATOR . 'bots_agent.php';
        $this->pathMobileAgent = Base::app()->config('SITE_ROOT') .
            DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'mobile_agent.php';
        $this->ctrl_cnt_data = array('sqlite_db' => Base::app()->config('DIR_DATA') .
                DIRECTORY_SEPARATOR . 'ctrl_cnt.db', );

        if (self::isControlInDB)
            $this->ctrl_cnt_init();

        if ($this->pathBotIp) {
            $arr = null;
            if (file_exists($this->pathBotIp)) {
                $arr = require ($this->pathBotIp);
            }
            if (is_array($arr)) {
                $this->botIp = $arr;
            } else {
                $this->pathBotIp = null;
            }
        }
        if ($this->pathNotBotIp) {
            $arr = null;
            if (file_exists($this->pathNotBotIp)) {
                $arr = require ($this->pathNotBotIp);
            }
            if (is_array($arr)) {
                $this->notBotIp = $arr;
            } else {
                $this->pathNotBotIp = null;
            }
        }
        if ($this->pathBotsAgent) {
            $arr = null;
            if (file_exists($this->pathBotsAgent)) {
                $arr = require ($this->pathBotsAgent);
            }
            if (is_array($arr)) {
                $this->botsAgent = $arr;
            } else {
                $this->pathBotsAgent = null;
            }
        }
        if ($this->pathMobileAgent) {
            $arr = null;
            if (file_exists($this->pathMobileAgent)) {
                $arr = require ($this->pathMobileAgent);
            }
            if (is_array($arr)) {
                $this->mobileAgent = $arr;
            } else {
                $this->pathMobileAgent = null;
            }
        }

        if (!isset($_SESSION)) {
            session_start();
        }

        //var_dump($_SESSION);
        //unset($_SESSION['ReqDetect']);
        //var_dump($_COOKIE);

        if (!isset($_SESSION['ReqDetect'])) {
            $_SESSION['ReqDetect'] = array(
                'token' => self::strRandom(),
                'time' => time(),
                'count' => 0,
                'isJsCoookie' => false);
        }

        if (!$_SESSION['ReqDetect']['isJsCoookie']) {
            if ($_SESSION['ReqDetect']['time'] - time() > self::timeActualToken) {
                $_SESSION['ReqDetect'] = array(
                    'token' => self::strRandom(),
                    'time' => time(),
                    'count' => 0,
                    'isJsCoookie' => false);
            }

            if (isset($_COOKIE['token_req_detect'])) {
                if ($_COOKIE['token_req_detect'] == $_SESSION['ReqDetect']['token']) {
                    $_SESSION['ReqDetect']['isJsCoookie'] = true;

                    if (self::isControlInDB)
                        $this->ctrl_cnt_set_cookie();
                } else {
                    $_SESSION['ReqDetect']['isJsCoookie'] = false;
                }
            }
        }
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new ReqDetect();
        }

        return self::$instance;
    }

    public static function getSxGeo()
    {
        if (!self::$SxGeo) {
            if (!class_exists('SxGeo') && file_exists(self::$pathSxGeo)) {
                require_once (self::$pathSxGeo);
            }

            self::$SxGeo = new SxGeo(self::$pathSxGeoCityDat);
        }

        return self::$SxGeo;
    }

    public static function init($data = array())
    {
        $instance = self::getInstance();

        if (isset($data['botIp'])) {
            $instance->botIp = $data['botIp'];
        }
        if (isset($data['notBotIp'])) {
            $instance->notBotIp = $data['notBotIp'];
        }
        if (isset($data['botsAgent'])) {
            $instance->botsAgent = $data['botsAgent'];
        }
        if (isset($data['mobileAgent'])) {
            $instance->mobileAgent = $data['mobileAgent'];
        }
        if (isset($data['pathSxGeoCityDat'])) {
            self::$pathSxGeoCityDat = $data['pathSxGeoCityDat'];
        }

        return $instance;
    }

    public static function isBot()
    {
        return self::getInstance()->itIsBot();
    }

    public static function isNotBot()
    {
        return !self::getInstance()->itIsBot();
    }

    public static function isMobile()
    {
        return self::getInstance()->itIsMobile();
    }

    public static function isNotMobile()
    {
        return !self::getInstance()->itIsMobile();
    }

    public static function city()
    {
        return self::getInstance()->getCity();
    }

    public static function country()
    {
        return self::getInstance()->getCountry();
    }

    public static function isCountry($country)
    {
        if (is_string($country)) {
            return $country == self::getInstance()->getCountry();
        } elseif (is_array($country)) {
            $status = false;

            foreach ($country as $value) {
                if ($value == self::getInstance()->getCountry()) {
                    $status = true;
                }
            }

            return $status;
        }
    }

    public static function isNotBotAndMobileAndCountry($country)
    {
        return self::isNotBot() && self::isMobile() && self::isCountry($country);
    }

    public static function isMobileAndCountry($country)
    {
        return self::isMobile() && self::isCountry($country);
    }

    public static function isNotBotAndCountry($country)
    {
        return self::isNotBot() && self::isCountry($country);
    }

    public static function isNotBotAndMobile()
    {
        return self::isNotBot() && self::isMobile();
    }

    public static function JS($data = array())
    {
        self::getInstance()->pageJavascript();
        
        if (true || !self::getInstance()->isJsCoookie()) {
            self::getInstance()->renderJavascript($data);
        }
    }

    public function getCity()
    {
        return self::getSxGeo()->getCity($this->_getIp());
    }

    public function getCountry()
    {
        if (is_null($this->country)) {
            $this->country = self::getSxGeo()->getCountry($this->_getIp());
        }

        return $this->country;
    }

    /**
     * Метод получения текущего ip-адреса из переменных сервера.
     */
    protected function _getIp()
    {
        if (is_null($this->ip)) {
            // Массив возможных ip-адресов
            $addrs = array();

            // Сбор данных возможных ip-адресов
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                // Проверяется массив ip-клиента установленных прозрачными прокси-серверами
                foreach (array_reverse(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])) as $value) {
                    $value = trim($value);
                    // Собирается ip-клиента
                    if (preg_match('#^\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}$#', $value)) {
                        $addrs[] = $value;
                    }
                }
            }
            // Собирается ip-клиента
            if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $addrs[] = $_SERVER['HTTP_CLIENT_IP'];
            }
            // Собирается ip-клиента
            if (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
                $addrs[] = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
            }
            // Собирается ip-клиента
            if (isset($_SERVER['HTTP_PROXY_USER'])) {
                $addrs[] = $_SERVER['HTTP_PROXY_USER'];
            }
            // Собирается ip-клиента
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $addrs[] = $_SERVER['REMOTE_ADDR'];
            }

            // Фильтрация возможных ip-адресов, для выявление нужного
            foreach ($addrs as $value) {
                // Выбирается ip-клиента
                if (preg_match('#^(\d{1,3}).(\d{1,3}).(\d{1,3}).(\d{1,3})$#', $value, $matches)) {
                    $value = $matches[1] . '.' . $matches[2] . '.' . $matches[3] . '.' . $matches[4];
                    if ('...' != $value) {
                        $this->ip = $value;
                        break;
                    }
                }
            }
        }

        return $this->ip;
    }

    protected function _getHttpUserAgent()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            return $_SERVER['HTTP_USER_AGENT'];
        } else {
            return null;
        }
    }

    public function isBotAgent()
    {
        $user_agent = $this->_getHttpUserAgent();

                if (!$user_agent) {
                    $this->saveBot();
                    return true;
                }

                foreach ($this->botsAgent as $bot) {
                    if (stripos($user_agent, $bot) !== false) {
                        $this->botname = $bot;
                        $this->saveBot();
                        return true;
                    }
                }
        
        return false;
    }

    public function itIsBot(&$botname = '')
    {
        if (is_null($this->isBot)) {
            if (isset($_GET['utm_source'])) {
                if ($_GET['utm_source'] == 'bot') {
                    return $this->isBot = true;
                }
            }

            if (self::isBotIp && in_array($this->_getIp(), $this->botIp)) {
                return $this->isBot = true;
            }

            if (self::isNotBotIp && in_array($this->_getIp(), $this->notBotIp)) {
                return $this->isBot = false;
            }
            
            if (self::isBotAgent) {
                if ($this->isBotAgent()) {
                    return $this->isBot = true;
                }
            }

            if (self::isControlJs) {
                if (self::isControlInDB)
                    if ($this->ctrl_cnt_get_count_set_cookie() > self::maxCountRenderJs) {
                        if (self::isSaveControlJsBot) {
                            $this->saveBot();
                        }
                        return $this->isBot = true;
                    }

                if (!$this->isJsCoookie()) {
                    if (self::defaultIsBotControlJs) {
                        return $this->isBot = true;
                    }
                }
            }
            
            if ($_SESSION['ReqDetect']['count'] > self::maxCountRenderJs) {
                //return $this->isBot = true;
            }

            return $this->isBot = false;
        }

        return $this->isBot;
    }

    public function getBotname()
    {
        if (!$this->botname && is_array($this->botsAgent)) {
            $user_agent = $this->_getHttpUserAgent();
            
            foreach ($this->botsAgent as $bot) {
                if (stripos($user_agent, $bot) !== false) {
                    $this->botname = $bot;
                }
            }
        }
        
        return $this->botname;
    }

    public function itIsMobile(&$mobilename = '')
    {
        if (is_null($this->isMobile)) {
            if (isset($_GET['utm_source'])) {
                if ($_GET['utm_source'] == 'mobile') {
                    return $this->isMobile = true;
                }
            }

            $user_agent = $this->_getHttpUserAgent();

            if (!$user_agent) {
                return $this->isMobile = false;
            }

            foreach ($this->mobileAgent as $agent) {
                if (stripos($user_agent, $agent) !== false) {
                    $mobilename = $agent;
                    return $this->isMobile = true;
                }
            }

            return $this->isMobile = false;
        }

        return $this->isMobile;
    }

    public function isJsCoookie()
    {
        if (is_null($this->isJsCoookie)) {
            if ($_SESSION['ReqDetect']['isJsCoookie'] === true) {
                $this->isJsCoookie = true;
            } else {
                $this->isJsCoookie = false;
            }

            // if(self::isControlInDB)
            // if ($this->ctrl_cnt_is_cookie()) {
            //     $this->isJsCoookie = true;
            // }
        }

        return $this->isJsCoookie;
    }

    private function renderJavascript($data = array())
    {
        $_SESSION['ReqDetect']['count']++;
        if(self::isControlInDB)
            $this->ctrl_cnt_count_set_cookie();
        
        if ($_SESSION['ReqDetect']['count'] > self::maxCountControlJs) {
            if (self::isSaveControlJsBot) {
                $this->saveBot();
            }
        }
        
        ?>
        <script type="text/javascript">
           var data_load_req_detect = {};
           
           <?php
           foreach ($data as $key => $value) {
               ?>data_load_req_detect['<?= $key ?>'] = '<?= $value ?>';<?php
           }
           ?>
           
           function reloadPage() {
               cookiePage();
               if ($.cookie('token_req_detect') == '<?= $_SESSION['ReqDetect']['token'] ?>') {
                   location.reload();
               }
           }
           function loadData() {
               cookiePage();
               if ($.cookie('token_req_detect') == '<?= $_SESSION['ReqDetect']['token'] ?>') {
                   $.ajax({
                       url: '<?= $this->routeLoadAjax ?>',
                       data: data_load_req_detect,
                       type: 'POST',
                       dataType: 'json',
                       beforeSend: function (result) {
                       },
                       success: function (result) {
                           if (result.data) {
                               for (var i in result.data) {
                                   if ($("#" + i).length) {
                                      $("#" + i).html(result.data[i]); 
                                   }
                               }
                           }
                       },
                       error: function (result) {
                       },
                       complete: function (result) {
                       }
                   });
               }
           }
           <?php if(is_null(self::isUpdateJs) || !$this->itIsBot()) { ?>
           $(document).ready(function(){
               cookiePage();
           });
           <?php } elseif(self::isUpdateJs === false) { ?>
           reloadPage();
           <?php } elseif(self::isUpdateJs === true) { ?>
           $(document).ready(function(){
               loadData();
           });
           <?php } else { ?>
           $(document).ready(function(){
               cookiePage();
           });
           <?php } ?>
        </script>
        <?php
    }
    
    private function pageJavascript() {
        ?>
<script>
var statusSetCookieTokenReqDetect = false;
var statusSetCookieTokenReqDetectIsJsCoookie = <?= $_SESSION['ReqDetect']['isJsCoookie'] ? 'true' : 'false' ?>;

function cookiePage() {
               document.onmousemove = function () {
                  if (statusSetCookieTokenReqDetect === false && !statusSetCookieTokenReqDetectIsJsCoookie) {
                      statusSetCookieTokenReqDetect = true;
                      $.cookie('token_req_detect', '<?= $_SESSION['ReqDetect']['token'] ?>');
                      
                      $.ajax({
                       url: '/req_detect/set_cookie',
                       data: {
                        trcp_request_user_inf: window.trcpRequestUserInf,
                        guest_id: '<?= \App\Models\Guest::guest()->id ?>'
                       },
                       type: 'POST',
                       dataType: 'json',
                       beforeSend: function (result) {
                       },
                       success: function (result) {
                        //alert(JSON.stringify(result));
                       },
                       error: function (result) {
                        //alert(JSON.stringify(result));
                       },
                       complete: function (result) {
                       }
                   });
                  }
               }
}
</script>
<script>
(function(){window.trcpRequestUserInf={};function b(){if(navigator.geolocation){}if(typeof navigator.standalone!="undefined"){window.trcpRequestUserInf.browser_standalone={name:"Работает ли браузер в автономном режиме",value:navigator.standalone,}}if(typeof navigator.productSub!="undefined"){window.trcpRequestUserInf.browser_build_id={name:"Номер сборки браузера",value:navigator.productSub,}}if(typeof navigator.cookieEnabled!="undefined"){window.trcpRequestUserInf.browser_is_cookie={name:"Включены ли куки в браузере",value:navigator.cookieEnabled,}}if(typeof navigator.userAgent!="undefined"){window.trcpRequestUserInf.user_agent={name:"Заголовок браузера",value:navigator.userAgent,}}if(typeof navigator.buildID!="undefined"){window.trcpRequestUserInf.browser_build_id={name:"Идентификатор сборки браузера",value:navigator.buildID,}}if(typeof navigator.plugins!="undefined"){var a=[];for(var f in navigator.plugins){a.push(navigator.plugins[f].name)}window.trcpRequestUserInf.plugins={name:"Поддерживаемые плагины",value:a,}}if(typeof navigator.platform!="undefined"){window.trcpRequestUserInf.platform={name:"Платформма браузера",value:navigator.platform,}}if(typeof navigator.oscpu!="undefined"){window.trcpRequestUserInf.operating_system={name:"Операционная система",value:navigator.oscpu,}}if(typeof navigator.onLine!="undefined"){window.trcpRequestUserInf.is_online={name:"Работает ли браузер в сети",value:navigator.onLine,}}if(typeof navigator.language!="undefined"){window.trcpRequestUserInf.language={name:"Языки",value:navigator.language,}}if(typeof navigator.mimeTypes!="undefined"){var e=[];for(var f in navigator.mimeTypes){e.push(navigator.mimeTypes[f].type)}window.trcpRequestUserInf.mime_types={name:"Поддерживаемые типы",value:e,}}if(typeof navigator.languages!="undefined"){window.trcpRequestUserInf.languages={name:"Языки",value:navigator.languages,}}if(typeof navigator.javaEnabled!="undefined"){window.trcpRequestUserInf.is_java={name:"Поддержка Java",value:navigator.javaEnabled?1:0,}}if(typeof navigator.connection!="undefined"){if(typeof navigator.connection.effectiveType!="undefined"){window.trcpRequestUserInf.browser_connection_type={name:"Тип подключения",value:navigator.connection.effectiveType,}}if(typeof navigator.connection.saveData!="undefined"){window.trcpRequestUserInf.browser_connection_is_save_data={name:"Сокращенное использования данных",value:navigator.connection.saveData?1:0,}}if(typeof navigator.connection.rtt!="undefined"){window.trcpRequestUserInf.browser_connection_rtt={name:"Время приема-передачи",value:navigator.connection.rtt,}}if(typeof navigator.connection.effectiveType!="undefined"){window.trcpRequestUserInf.browser_connection_type={name:"Тип соединения",value:navigator.connection.effectiveType,}}if(typeof navigator.connection.downlinkMax!="undefined"){window.trcpRequestUserInf.browser_connection_downlink_max={name:"Максимальная пропускная способность Mb/s",value:navigator.connection.downlinkMax,}}if(typeof navigator.connection.downlink!="undefined"){window.trcpRequestUserInf.browser_connection_downlink={name:"Рабочая пропускная способность Mb/s",value:navigator.connection.downlink,}}}if(typeof navigator.appVersion!="undefined"){window.trcpRequestUserInf.browser_version={name:"Версия браузера",value:navigator.appVersion,}}if(typeof navigator.appName!="undefined"){window.trcpRequestUserInf.browser_name={name:"Название браузера",value:navigator.appName,}}if(typeof navigator.appCodeName!="undefined"){window.trcpRequestUserInf.browser_code_name={name:'Внутренний "код" браузера',value:navigator.appCodeName,}}window.trcpRequestUserInf.is_javascript={name:"Поддержка Javascript",value:1,};window.trcpRequestUserInf.is_activex={name:"Поддержка ActiveX",value:(function(){try{var d=ActiveXObject;return 1}catch(c){return 0}}()),};window.trcpRequestUserInf.is_webrtc={name:"Поддержка WebRTC",value:(function(){try{var i=window.RTCPeerConnection||window.mozRTCPeerConnection||window.webkitRTCPeerConnection;if(!i){var c=iframe.contentWindow;i=c.RTCPeerConnection||c.mozRTCPeerConnection||c.webkitRTCPeerConnection}if(i){return 1}else{return 0}return 0}catch(d){return 0}return 0}()),};window.trcpRequestUserInf.is_vbscript={name:"Поддержка VBScript",value:(function(){try{VBSEnabled();return 1}catch(c){return 0}return 0}()),};window.trcpRequestUserInf.is_firebug={name:"Поддержка Firebug",value:(function(){try{if(window.console&&(window.console.firebug||window.console.exception)){return 1}else{return 0}}catch(c){return 0}return 0}()),};window.trcpRequestUserInf.screen={name:"Экран",value:(function(){var c=[];var i=["colorDepth","pixelDepth","height","width","availHeight","availWidth","window-size"];for(var d in i){var j=i[d]=="window-size"?(function(){var l=null;var g=null;try{if(document.all){l=document.body.offsetWidth;g=document.body.offsetHeight}else{if(document.layers){l=document.body.width;g=document.body.height}else{if(document.body.clientWidth!=null){l=document.body.clientWidth;g=document.body.clientHeight}}}g=$(window).height()}catch(h){}return{width:l,height:g,screen_width:l,screen_height:g}}()):window.screen[i[d]];if(typeof j!="undefined"){c.push(j)}}return c}()),}}if(document.isReadyDOMContentLoaded){b()}else{document.addEventListener("DOMContentLoaded",function(){b();document.isReadyDOMContentLoaded=true})}}());
</script>
        <?php
    } 

    public static function sendJson($data = array())
    {
        headers_sent('Content-Type: application/json');

        if (self::isNotBot()) {
            echo json_encode(array('data' => $data));
            exit();
        }
    }

    private function saveBot()
    {
        if (self::isSaveBotIp && $this->pathBotIp) {
            $ip = $this->_getIp();

            if (!in_array($ip, $this->botIp)) {
                array_unshift($this->botIp, $ip);
                $this->saveBotIps();
            }
        }
    }

    private function saveBotIps()
    {
        if ($this->pathBotIp) {
            if (file_put_contents(preg_replace("#\.php$#", '', $this->pathBotIp), $this->
                formatArr(array_slice($this->botIp, 0, 3000)))) {
                file_put_contents($this->pathBotIp, $this->formatArr(array_slice($this->botIp, 0,
                    3000)));
            }
        }
    }

    private function formatArr($arr)
    {
        $str = "<?php \n\n";

        $str .= 'return ' . rtrim($this->format(null, $arr, 0), "\n,") . ";";

        $str .= "\n";

        return $str;
    }

    private function format($key = '', $data = null, $iteration = 0)
    {
        $result = "";

        $left_indent = str_repeat(' ', $iteration * 4);

        if (is_array($data)) {
            if (!empty($key) || $key === 0) {
                $result .= $left_indent . (is_numeric($key) ? intval($key) : "'" . $key . "'") .
                    " => array(\n";
            } else {
                $result .= $left_indent . "array(\n";
            }

            $iteration++;

            foreach ($data as $key => $value) {
                $result .= $this->format($key, $value, $iteration);
            }

            $result .= $left_indent . "),\n";
        } else {
            if (!empty($key) || $key === 0) {
                $result .= $left_indent . (is_numeric($key) ? intval($key) : "'" . $key . "'") .
                    " => " . (is_numeric($data) ? str_replace(',', '.', round((float)$data, 4)) :
                    "'" . str_replace("'", "\'", $data) . "'") . ",\n";
            } else {
                $result .= $left_indent . "" . (is_numeric($data) ? str_replace(',', '.', round
                    ((float)$data, 4)) : "'" . str_replace("'", "\'", $data) . "'") . ",\n";
            }
        }

        return $result;
    }

    private static function strRandom()
    {
        if (is_null(self::$isSrand)) {
            self::$isSrand = true;

            srand(self::make_seed());
        }

        return md5(uniqid(rand(), true));
    }

    private static function make_seed()
    {
        list($usec, $sec) = explode(' ', microtime());
        return (float)$sec + ((float)$usec * 100000);
    }

    public static function getUserInfo()
    {
        return self::getInstance()->infObj->getUserInfo();
    }

    public function ctrl_cnt_db_init()
    {
        if (file_exists($this->ctrl_cnt_data['sqlite_db'])) {
            $this->ctrl_cnt_pdo = new \PDO('sqlite:' . $this->ctrl_cnt_data['sqlite_db']);
        } else {
            $this->ctrl_cnt_pdo = new \PDO('sqlite:' . $this->ctrl_cnt_data['sqlite_db']);

            $stat = $this->ctrl_cnt_pdo->prepare("CREATE TABLE IF NOT EXISTS `ips`(ip VARCHAR(60), count INT(11) NOT NULL DEFAULT 1, is_set_cookie INT(1) NOT NULL DEFAULT 0, count_set_cookie INT(11) NOT NULL DEFAULT 0, PRIMARY KEY (`ip`));");
            $stat->execute();

            $stat = $this->ctrl_cnt_pdo->prepare("CREATE TABLE IF NOT EXISTS `options`(name VARCHAR(60), value TEXT, PRIMARY KEY (`name`));");
            $stat->execute();
        }
    }

    public function ctrl_cnt_db_del()
    {
        $this->ctrl_cnt_pdo = null;

        unlink($this->ctrl_cnt_data['sqlite_db']);
    }

    public function ctrl_cnt_init()
    {
        $date = date('Y-m-d');

        $this->ctrl_cnt_db_init();

        if ($this->ctrl_cnt_option_get('date') != $date) {
            $this->ctrl_cnt_db_del();

            $this->ctrl_cnt_db_init();

            $this->ctrl_cnt_ip_clear();

            $this->ctrl_cnt_option_del('date');

            $this->ctrl_cnt_option_add('date', $date);
        }

        $this->ctrl_cnt_run();
    }

    public function ctrl_cnt_run()
    {
        if (!$this->ctrl_cnt_ip_is()) {
            $this->ctrl_cnt_ip_add();
        } else {
            $this->ctrl_cnt_ip_update();
        }
    }

    public function ctrl_cnt_ip_is()
    {
        $stat = $this->ctrl_cnt_pdo->prepare("SELECT COUNT(*) FROM `ips` WHERE ip=:ip");

        if ($stat) {
            $stat->execute(array('ip' => $this->_getIp()));
            $result = $stat->fetch(\PDO::FETCH_NUM);

            if ($result[0] > 0) {
                return true;
            } elseif ($result[0] == 0) {
                return false;
            }
        }

        return null;
    }

    public function ctrl_cnt_ip_count()
    {
        $stat = $this->ctrl_cnt_pdo->prepare("SELECT COUNT(*) FROM `ips` WHERE 1");

        if ($stat) {
            $stat->execute();
            $result = $stat->fetch(\PDO::FETCH_NUM);

            return $result[0];
        }

        return null;
    }

    public function ctrl_cnt_ip_get()
    {
        $stat = $this->ctrl_cnt_pdo->prepare("SELECT * FROM `ips` WHERE ip=:ip LIMIT 1");

        if ($stat) {
            $stat->execute(array('ip' => $this->_getIp()));
            $result = $stat->fetch(\PDO::FETCH_ASSOC);

            if (is_array($result)) {
                return $result;
            }
        }

        return null;
    }

    public function ctrl_cnt_ip_add()
    {
        $stat = $this->ctrl_cnt_pdo->prepare("INSERT INTO `ips`(`ip`, `count`) VALUES (:ip, 1);");

        if ($stat) {
            $stat->execute(array('ip' => $this->_getIp()));
        }
    }

    public function ctrl_cnt_ip_update()
    {
        $stat = $this->ctrl_cnt_pdo->prepare("UPDATE `ips` SET `count`=`count`+1 WHERE `ip`=:ip;");

        if ($stat) {
            $stat->execute(array('ip' => $this->_getIp()));
        }
    }

    public function ctrl_cnt_is_cookie()
    {
        $stat = $this->ctrl_cnt_pdo->prepare("SELECT `is_set_cookie` FROM `ips` WHERE ip=:ip LIMIT 1");

        if ($stat) {
            $stat->execute(array('ip' => $this->_getIp()));
            $result = $stat->fetch(\PDO::FETCH_NUM);

            if ($result[0] > 0) {
                return true;
            } elseif ($result[0] == 0) {
                return false;
            }
        }

        return null;
    }

    public function ctrl_cnt_set_cookie()
    {
        $stat = $this->ctrl_cnt_pdo->prepare("UPDATE `ips` SET `is_set_cookie` = 1, `count_set_cookie` = 0 WHERE `ip`=:ip;");

        if ($stat) {
            $stat->execute(array('ip' => $this->_getIp()));
        }
    }

    public function ctrl_cnt_count_set_cookie()
    {
        $stat = $this->ctrl_cnt_pdo->prepare("UPDATE `ips` SET `count_set_cookie`=`count_set_cookie`+1 WHERE `ip`=:ip;");

        if ($stat) {
            $stat->execute(array('ip' => $this->_getIp()));
        }
    }

    public function ctrl_cnt_get_count_set_cookie()
    {
        $stat = $this->ctrl_cnt_pdo->prepare("SELECT `count_set_cookie` FROM `ips` WHERE ip=:ip LIMIT 1");

        if ($stat) {
            $stat->execute(array('ip' => $this->_getIp()));
            $result = $stat->fetch(\PDO::FETCH_NUM);

            if ($result) {
                return $result[0];
            }
        }

        return null;
    }

    public function ctrl_cnt_ip_del()
    {
        $stat = $this->ctrl_cnt_pdo->prepare("DELETE FROM `ips` WHERE ip=:ip;");

        if ($stat) {
            $stat->execute(array('ip' => $this->_getIp()));
        }
    }

    public function ctrl_cnt_ip_clear()
    {
        $stat = $this->ctrl_cnt_pdo->prepare("DELETE FROM `ips` WHERE 1;");

        if ($stat) {
            $stat->execute();
        }
    }

    public function ctrl_cnt_option_add($name, $value)
    {
        $stat = $this->ctrl_cnt_pdo->prepare("INSERT INTO `options`(`name`, `value`) VALUES (:name, :value);");

        if ($stat) {
            $stat->execute(array('name' => $name, 'value' => $value));
        }
    }

    public function ctrl_cnt_option_get($name)
    {
        $stat = $this->ctrl_cnt_pdo->prepare("SELECT * FROM `options` WHERE name=:name LIMIT 1");

        if ($stat) {
            $stat->execute(array('name' => $name));
            $result = $stat->fetch(\PDO::FETCH_ASSOC);

            if (is_array($result)) {
                return $result['value'];
            }
        }

        return null;
    }

    public function ctrl_cnt_option_del($name)
    {
        $stat = $this->ctrl_cnt_pdo->prepare("DELETE FROM `options` WHERE name=:name;");

        if ($stat) {
            $stat->execute(array('name' => $name));
        }
    }

    public function ctrl_cnt_option_clear()
    {
        $stat = $this->ctrl_cnt_pdo->prepare("DELETE FROM `options` WHERE 1;");

        if ($stat) {
            $stat->execute();
        }
    }

    public function ctrl_cnt_option_update($name, $value)
    {
        $stat = $this->ctrl_cnt_pdo->prepare("UPDATE `options` SET `value`=:value WHERE `name`=:name;");

        if ($stat) {
            $stat->execute(array('name' => $name, 'value' => $value));
        }
    }
}

class Info
{
    private $layout = [];

    private $node;
    private $debug;
    
    private $geoip_path;
    
    private $browser;
    private $geoplugin;

    public function __construct()
    {
        $this->geoip_path = config('storage_dir') . '/GeoIP';
        
        $this->requireGeoip();
        
        $this->browser = new Browser;
        $this->geoplugin = new Geoplugin;
    }

    protected function getIpApi($ip)
    {
        $inf = file_get_contents('http://ip-api.com/php/' . $ip);

        if ($inf && $inf = unserialize($inf)) {

            if (isset($inf['status']) && $inf['status'] == 'success') {
                return $inf;
            }
        }
    }

    public function getUserInfo()
    {
		$this->layout['mainpage'] = true;
        $this->layout['userip'] = $this->getUserIP();
        $this->layout['browser'] = $this->browser->getBrowser() . ' ' . $this->browser->
            getVersion();
        $this->layout['os'] = $this->browser->getPlatform();
        $this->layout['proxy'] = $this->_checkProxy();
        //$this->layout['proxytwo'] = $this->_checkPorts($this->getUserIP());
        //$this->layout['tor'] = $this->_checkTor($this->getUserIP(), $_SERVER['SERVER_PORT'], $_SERVER['SERVER_ADDR']);
        //$this->layout['blocklist'] = $this->_checkBlockList($this->getUserIP());
        $this->layout['browser_langs'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ?
            strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']) : null;

        $date_utc = new \DateTime("now", new \DateTimeZone("UTC"));

        $this->layout['utc'] = $date_utc->format(\DateTime::RFC850);
        
        $reader = new \GeoIp2\Database\Reader($this->geoip_path . '/GeoIP2-City.mmdb');
        
        try {
            $record = $reader->city($this->layout['userip']);
        } catch (\Exception $e) {
            $record = null;
        }

        if (false && $infIpApi = $this->getIpApi($this->layout['userip'])) {
            $this->layout['city'] = $infIpApi['city'];
            $this->layout['country'] = $infIpApi['country'];
            $this->layout['countryflag'] = mb_strtolower($infIpApi['countryCode']);
            $this->layout['long'] = $infIpApi['lon'];
            $this->layout['lat'] = $infIpApi['lat'];
            $this->layout['timezone'] = $infIpApi['timezone'];
        } elseif ($record) {
            $this->layout['city'] = $record->city ? $record->city->name : null;
            $this->layout['country'] = $record->country ? $record->country->name : null;
            $this->layout['countryflag'] = mb_strtolower($record->country ? $record->country->isoCode : null);
            $this->layout['long'] = $record->location ? $record->location->longitude : null;
            $this->layout['lat'] = $record->location ? $record->location->latitude : null;
            $this->layout['timezone'] = isset($record->raw['location']) && isset($record->raw['location']['time_zone']) ? $record->raw['location']['time_zone'] : null;
        } elseif(false) {
            $this->geoplugin->locate($this->layout['userip']);

            if (!empty($this->geoplugin->city)) {

                $this->layout['city'] = $this->geoplugin->city;
                $this->layout['country'] = $this->geoplugin->countryName;
                $this->layout['countryflag'] = mb_strtolower($this->geoplugin->countryCode);
                $this->layout['long'] = $this->geoplugin->longitude;
                $this->layout['lat'] = $this->geoplugin->latitude;
                $this->layout['timezone'] = $this->geoplugin->continentName . '/' . $this->
                    geoplugin->countryName;
            } else {
                $alter_data = json_decode($this->request('http://api.ipstack.com/' . $this->
                    layout['userip'] . '?access_key=e8f1e790e60c72b4e69635d0fa4d898f'));
                if (!empty($alter_data)) {
                    $this->layout['city'] = $alter_data->city;
                    $this->layout['country'] = $alter_data->country_name;
                    $this->layout['countryflag'] = mb_strtolower($alter_data->country_code);
                    $this->layout['long'] = $alter_data->longitude;
                    $this->layout['lat'] = $alter_data->latitude;

                    $this->layout['timezone'] = $alter_data->continent_name . '/' . $alter_data->
                        country_name;
                }
            }
        }
        
        if (isset($this->layout['long']) && isset($this->layout['lat'])) {
            $this->layout['sunrise'] = date_sunrise(time(), SUNFUNCS_RET_STRING, $this->
                layout['lat'], $this->layout['long']);
            $this->layout['sunset'] = date_sunset(time(), SUNFUNCS_RET_STRING, $this->
                layout['lat'], $this->layout['long']);
        }

        //$this->layout['hostname'] = gethostbyaddr($this->layout['userip']);
        //$ns = @dns_get_record($this->layout['hostname']);
        //$this->layout['dns'] = isset($ns[0]['ip']) ? $ns[0]['ip'] : '';

        /*
        $json_provider = $this->request('https://rest.db.ripe.net/search.json?source=ripe&query-string=' .
            $this->layout['userip'] . '&flags=no-filtering&flags=no-referenced');
        $array_provider = json_decode($json_provider);

        if (is_array($array_provider->objects->object)) {
            foreach ($array_provider->objects->object as $obj) {
                foreach ($obj->attributes->attribute as $attr) {
                    //if ($attr->name == 'descr') {
                    $this->layout['provider'] = $attr->value;
                    break;
                    //}
                }
            }
        }
        */
        
        $res = [];
        
        if (isset($this->layout['userip']) && !empty($this->layout['userip'])) {
            $res['ip'] = [
                'name' => 'IP адрес',
                'value' => $this->layout['userip'],
            ];
        }
        
        if (isset($this->layout['browser']) && !empty($this->layout['browser'])) {
            $res['browser'] = [
                'name' => 'Браузер',
                'value' => trim(preg_replace("#\d.*$#", '', $this->layout['browser'])),
            ];
        }
        
        if (isset($this->layout['os']) && !empty($this->layout['os'])) {
            $res['operating_system'] = [
                'name' => 'Операционная система',
                'value' => $this->layout['os'],
            ];
        }
        
        if (isset($this->layout['proxy']) && !empty($this->layout['proxy'])) {
            $res['proxy'] = [
                'name' => 'Прокси',
                'value' => $this->layout['proxy'],
            ];
        }
        
        if (isset($this->layout['proxytwo']) && !empty($this->layout['proxytwo'])) {
            $res['proxytwo'] = [
                'name' => 'Статус IP',
                'value' => $this->layout['proxytwo'],
            ];
        }
        
        if (isset($this->layout['tor']) && !empty($this->layout['tor'])) {
            $res['tor'] = [
                'name' => 'Прокси TOR',
                'value' => $this->layout['tor'],
            ];
        }
        
        if (isset($this->layout['blocklist']) && !empty($this->layout['blocklist'])) {
            $res['blocklist'] = [
                'name' => 'Соответствие DNS',
                'value' => $this->layout['blocklist'],
            ];
        }
        
        if (isset($this->layout['browser_langs']) && !empty($this->layout['browser_langs'])) {
            $lngs = explode(';', $this->layout['browser_langs']);
            
            if (isset($lngs[0])) {
                $res['language'] = [
                    'name' => 'Язык браузера',
                    'value' => $lngs[0],
                ];
            }
        }
        
        if (false && isset($this->layout['utc']) && !empty($this->layout['utc'])) {
            $res['date'] = [
                'name' => 'Дата',
                'value' => $this->layout['utc'],
            ];
        }
        
        if (isset($this->layout['city']) && !empty($this->layout['city'])) {
            $res['city'] = [
                'name' => 'Город',
                'value' => $this->layout['city'],
            ];
        }
        
        if (isset($this->layout['country']) && !empty($this->layout['country'])) {
            $res['country'] = [
                'name' => 'Страна',
                'value' => $this->layout['country'],
            ];
        }
        
        if (isset($this->layout['countryflag']) && !empty($this->layout['countryflag'])) {
            $res['country_flag'] = [
                'name' => 'Флаг страны',
                'value' => $this->layout['countryflag'],
            ];
        }
        
        if (false && isset($this->layout['long']) && !empty($this->layout['long'])) {
            $res['coordinate_lng'] = [
                'name' => 'Географическая долгота',
                'value' => $this->layout['long'],
            ];
        }
        
        if (false && isset($this->layout['lat']) && !empty($this->layout['lat'])) {
            $res['coordinate_lat'] = [
                'name' => 'Географическая широта',
                'value' => $this->layout['lat'],
            ];
        }
        
        if (false && isset($this->layout['lat']) && !empty($this->layout['lat'])) {
            $res['coordinate_lat'] = [
                'name' => 'Географическая широта',
                'value' => $this->layout['lat'],
            ];
        }
        
        if (isset($this->layout['timezone']) && !empty($this->layout['timezone'])) {
            $res['timezone'] = [
                'name' => 'Часовой пояс',
                'value' => $this->layout['timezone'],
            ];
        }
        
        if (false && isset($this->layout['sunrise']) && !empty($this->layout['sunrise'])) {
            $res['sunrise'] = [
                'name' => 'Время рассвета',
                'value' => $this->layout['sunrise'],
            ];
        }
        
        if (false && isset($this->layout['sunset']) && !empty($this->layout['sunset'])) {
            $res['sunset'] = [
                'name' => 'Время захода солнца',
                'value' => $this->layout['sunset'],
            ];
        }
        
        if (isset($this->layout['provider']) && !empty($this->layout['provider'])) {
            $res['provider'] = [
                'name' => 'Провайдер',
                'value' => $this->layout['provider'],
            ];
        }
        
        if (is_array(request()->post->{'trcp_request_user_inf'})) {
            $inf = request()->post->{'trcp_request_user_inf'};
            
            if (!isset($res['browser_standalone']) && isset($inf['browser_standalone'])) {
                $res['browser_standalone'] = $inf['browser_standalone'];
            }
            
            if (!isset($res['browser_build_id']) && isset($inf['browser_build_id'])) {
                //$res['browser_build_id'] = $inf['browser_build_id'];
            }
            
            if (!isset($res['browser_is_cookie']) && isset($inf['browser_is_cookie'])) {
                $res['browser_is_cookie'] = $inf['browser_is_cookie'];
            }
            
            if (false && !isset($res['user_agent']) && isset($inf['user_agent'])) {
                $res['user_agent'] = $inf['user_agent'];
            }
            
            if (!isset($res['browser_build_id']) && isset($inf['browser_build_id'])) {
                //$res['browser_build_id'] = $inf['browser_build_id'];
            }
            
            if (isset($inf['plugins']) && is_array($inf['plugins'])) {
                foreach ($inf['plugins']['value'] as $plugin) {
                    if (!isset($res['browser_standalone'])) {
                        $res['browser_plugin_'.translit(str_limit($plugin, 200, ''))] = [
                            'name' => $inf['plugins']['name'],
                            'value' => str_limit($plugin, 250, ''),
                        ];
                    }
                }
            }
            
            if (!isset($res['platform']) && isset($inf['platform'])) {
                $res['platform'] = $inf['platform'];
            }
            
            if (!isset($res['operating_system']) && isset($inf['operating_system'])) {
                $res['operating_system'] = $inf['operating_system'];
            }
            
            if (!isset($res['is_online']) && isset($inf['is_online'])) {
                $res['is_online'] = $inf['is_online'] == 'true' ? 1 : 0;
            }
            
            if (!isset($res['language']) && isset($inf['language'])) {
                $res['language'] = $inf['language'];
            }
            
            if (isset($inf['mime_types']) && is_array($inf['mime_types'])) {
                foreach ($inf['mime_types']['value'] as $mime) {
                    if (!isset($res['mime_type_'.translit(str_limit($mime, 200, ''))])) {
                        $res['mime_type_'.translit(str_limit($mime, 200, ''))] = [
                            'name' => $inf['mime_types']['name'],
                            'value' => str_limit($mime, 250, ''),
                        ];
                    }
                }
            }
            
            if (!isset($res['languages']) && isset($inf['languages']) && is_array($inf['languages']) && isset($inf['languages'][0])) {
                //$lngs = explode(';', $inf['languages']);
            
                if (true) {
                    $res['language'] = [
                        'name' => 'Язык браузера',
                        'value' => $inf['languages'][0],
                    ];
                }
            }
            
            if (!isset($res['is_java']) && isset($inf['is_java'])) {
                $res['is_java'] = $inf['is_java'];
            }
            
            if (!isset($res['browser_connection_type']) && isset($inf['browser_connection_type'])) {
                $res['browser_connection_type'] = $inf['browser_connection_type'];
            }
            
            if (!isset($res['browser_connection_is_save_data']) && isset($inf['browser_connection_is_save_data'])) {
                $res['browser_connection_is_save_data'] = $inf['browser_connection_is_save_data'];
            }
            
            if (!isset($res['browser_connection_rtt']) && isset($inf['browser_connection_rtt'])) {
                $res['browser_connection_rtt'] = $inf['browser_connection_rtt'];
            }
            
            if (!isset($res['browser_connection_type']) && isset($inf['browser_connection_type'])) {
                $res['browser_connection_type'] = $inf['browser_connection_type'];
            }
            
            if (!isset($res['browser_connection_downlink_max']) && isset($inf['browser_connection_downlink_max'])) {
                $res['browser_connection_downlink_max'] = $inf['browser_connection_downlink_max'];
            }
            
            if (!isset($res['browser_connection_downlink']) && isset($inf['browser_connection_downlink'])) {
                $res['browser_connection_downlink'] = $inf['browser_connection_downlink'];
            }
            
            if (!isset($res['appVersion']) && isset($inf['appVersion'])) {
                $res['appVersion'] = $inf['appVersion'];
            }
            
            if (!isset($res['browser_name']) && isset($inf['browser_name'])) {
                $res['browser_name'] = $inf['browser_name'];
            }
            
            if (!isset($res['browser_code_name']) && isset($inf['browser_code_name'])) {
                $res['browser_code_name'] = $inf['browser_code_name'];
            }
            
            if (!isset($res['is_javascript']) && isset($inf['is_javascript'])) {
                $res['is_javascript'] = $inf['is_javascript'];
            }
            
            if (!isset($res['is_activex']) && isset($inf['is_activex'])) {
                $res['is_activex'] = $inf['is_activex'];
            }
            
            if (!isset($res['is_webrtc']) && isset($inf['is_webrtc'])) {
                $res['is_webrtc'] = $inf['is_webrtc'];
            }
            
            if (isset($res['is_vbscript']) && isset($inf['is_vbscript'])) {
                $res['is_vbscript'] = $inf['is_vbscript'];
            }
            
            if (!isset($res['is_firebug']) && isset($inf['is_firebug'])) {
                $res['is_firebug'] = $inf['is_firebug'];
            }
            
            if (!isset($res['screen']) && isset($inf['screen'])) {
                $res['screen'] = json_encode($inf['screen']);
            }
        }
        
        foreach ($res as $key => $inf) {
            if (is_array($inf) && 
                isset($inf['name']) && 
                isset($inf['value'])) {
                if ((is_string($inf['name']) || is_numeric($inf['name'])) && 
                    (is_string($inf['value']) || is_numeric($inf['value']))) {
                    if (is_bool($inf['value'])) {
                        $res[$key]['value'] = $inf['value'] ? '1' : '0';
                    } else {
                        $res[$key]['value'] = strval($inf['value']);
                    }
                } else {
                    $res[$key]['value'] = json_encode($inf['value']);
                }
                
                if (empty($res[$key]['value'])) {
                    unset($res[$key]);
                }
            } else {
                unset($res[$key]);
            }
        }
        
        return $res;
    }

    public function whois()
    {
        if ($this->input->is_ajax_request()) {
            $ip = $this->input->post('ip');

            $json_provider = $this->request('https://rest.db.ripe.net/search.json?source=ripe&query-string=' .
                $ip . '&flags=no-filtering&flags=no-referenced');
            $array_provider = json_decode($json_provider);

            $html = '';
            if (is_array($array_provider->objects->object)) {
                foreach ($array_provider->objects->object as $obj) {
                    foreach ($obj->attributes->attribute as $attr) {
                        $html .= '<div>' . $attr->name . ': ' . $attr->value . '</div>';
                    }
                }
            }

            echo $html;
        }
    }

    public function requireGeoip()
    {
        //require_once $this->geoip_path . '/autoload.php';
    }

    public function gettimelocal()
    {
        $reader = new \GeoIp2\Database\Reader($this->geoip_path . '/GeoLite2-City.mmdb');

        $record = $reader->city($this->getUserIP());

        if (!empty($record) && isset($record->raw['location']) && isset($record->raw['location']['time_zone'])) {
            $dateTimeZone = new DateTimeZone($record->raw['location']['time_zone']);
            $localtime = new DateTime("now", $dateTimeZone);

            $offset = $dateTimeZone->getOffset($localtime) / 3600;
            if (preg_match('/^\+?\d+$/', $offset)) {
                $date_offset = '+0' . $offset . '00';
            } else {
                $date_offset = '-0' . $offset . '00';
            }
            $array['localtime'] = $localtime->format('D M d Y H:i:s') . ' GMT ' . $date_offset;
        } else {
            $offset = $this->input->post('offset');
            $offset = -($offset) / 60;
            if (preg_match('/^\+?\d+$/', $offset)) {
                $date_offset = '+0' . $offset . '00';
            } else {
                $date_offset = '-0' . $offset . '00';
            }
            $date = new DateTime("now", new DateTimeZone($date_offset));

            $array['localtime'] = $date->format('D M d Y H:i:s') . ' GMT ' . $date_offset;
        }

        $lat = $this->input->post('lat');
        $long = $this->input->post('long');

        $array['sunrise'] = date_sunrise(time(), SUNFUNCS_RET_STRING, $lat, $long,
            ini_get("date.sunrise_zenith"), $offset);
        $array['sunset'] = date_sunset(time(), SUNFUNCS_RET_STRING, $lat, $long, ini_get
            ("date.sunrise_zenith"), $offset);

        headers_sent("content-type:application/json");
        echo json_encode($array);

    }

    public function getcountryip()
    {
        $ips = $this->input->get('ips');
        $array = array();

        if (!empty($ips)) {
            $i = 0;
            foreach (explode(',', $ips) as $ip) {
                $this->geoplugin->locate($ip);
                $array['ips'][$i]['country'] = is_null($this->geoplugin->countryName) ? '-' : $this->
                    geoplugin->countryName;
                $array['ips'][$i]['ip'] = $ip;
                $array['ips'][$i]['iso'] = empty(mb_strtolower($this->geoplugin->countryCode)) ?
                    '-' : mb_strtolower($this->geoplugin->countryCode);
                $i++;
            }
            headers_sent("content-type:application/json");
            echo json_encode($array);
        }
    }

    public function getipinfo($ip = 0)
    {
        if ($ip) {
            $this->geoplugin->locate($ip);
            echo $ip . ' <i class="flag-icon flag-icon-' . mb_strtolower($this->geoplugin->
                countryCode) . '"></i>';
        }
    }

    public function dns()
    {
        $domain = $this->input->get('domain');
        $array = array();
        $ips = '';
        if (!empty($ips)) {
            $i = 0;
            foreach (explode(',', $ips) as $ip) {
                $this->geoplugin->locate($ip);
                $array[$i]['country'] = is_null($this->geoplugin->countryName) ? '-' : $this->
                    geoplugin->countryName;
                $array[$i]['ip'] = $ip;
                $array[$i]['iso'] = empty(mb_strtolower($this->geoplugin->countryCode)) ? '-' :
                    mb_strtolower($this->geoplugin->countryCode);
                $i++;
            }
            headers_sent("content-type:application/json");
            echo json_encode($array);
        }
    }

    protected function request($url, $post = 0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__file__) . '/cookie.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__file__) . '/cookie.txt');
        curl_setopt($ch, CURLOPT_POST, $post !== 0);
        if ($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $data = curl_exec($ch);
        curl_close($ch);
        //return iconv('windows-1251','utf-8',$data);
        //return $this->_conv($data);
        return $data;
    }

    protected function getUserIP()
    {
        //return '77.83.173.254';
        $IP = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        //return $IP;
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            $IP = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
            if (!preg_match("#\d+\.\d+\.\d+\.\d+#", $IP)) {
                $IP = $_SERVER['REMOTE_ADDR'];
            }
        }
        return $IP;
    }

    private function _checkProxy()
    {
        $test_HTTP_proxy_headers = array(
            'HTTP_VIA',
            'VIA',
            'Proxy-Connection',
            //'HTTP_X_FORWARDED_FOR',
            'HTTP_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
            'HTTP_FORWARDED_FOR_IP',
            'X-PROXY-ID',
            'MT-PROXY-ID',
            'X-TINYPROXY',
            'X_FORWARDED_FOR',
            'FORWARDED_FOR',
            'X_FORWARDED',
            'FORWARDED',
            'CLIENT-IP',
            'CLIENT_IP',
            'PROXY-AGENT',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'FORWARDED_FOR_IP',
            'HTTP_PROXY_CONNECTION');

        foreach ($test_HTTP_proxy_headers as $header) {
            if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
                return true;
            }
        }
        return false;
    }

    private function _checkPorts($ip)
    {
        $port = 8080;
        //$online = '<span style="color: green">Порт включен</span>';
        //$offline = '<span style="color: red">Порт выключен</span>';
        $errno = 0;
        $errstr = '';
        $fp = @fsockopen($ip, $port, $errno, $errstr, 1);
        if (!$fp) {
            $status = false;
        } else {
            $status = true;
        }
        @fclose($fp);
        return $status;
    }

    private function _checkBlockList($ip)
    {
        $dnsbl_lookup = ["dnsbl-1.uceprotect.net", "dnsbl-2.uceprotect.net",
            "dnsbl-3.uceprotect.net", "dnsbl.dronebl.org", "dnsbl.sorbs.net",
            "zen.spamhaus.org", "bl.spamcop.net", "list.dsbl.org", "sbl.spamhaus.org",
            "xbl.spamhaus.org"];
        $listed = "";
        if ($ip) {
            $reverse_ip = implode(".", array_reverse(explode(".", $ip)));
            foreach ($dnsbl_lookup as $host) {
                if (checkdnsrr($reverse_ip . "." . $host . ".", "A")) {
                    $listed .= $reverse_ip . '.' . $host . ' <font color="red">Listed</font><br />';
                }
            }
        }
        if (empty($listed)) {
            return lang('No');
        } else {
            return $listed;
        }
    }

    private function _checkTor($remoteIp, $port, $serverIp)
    {
        $detectHost = sprintf('%s.%s.%s.ip-port.exitlist.torproject.org', $this->
            _reverseIPOctets($remoteIp), $port, $this->_reverseIPOctets($serverIp));
        // According to the guide, if this returns 127.0.0.2, it's a Tor exit node
        return gethostbyname($detectHost) === '127.0.0.2';
    }
    private function _reverseIPOctets($ip)
    {
        return implode('.', array_reverse(explode('.', $ip)));
    }

}

class Browser
{
    private $_agent = '';
    private $_browser_name = '';
    private $_version = '';
    private $_platform = '';
    private $_os = '';
    private $_is_aol = false;
    private $_is_mobile = false;
    private $_is_tablet = false;
    private $_is_robot = false;
    private $_is_facebook = false;
    private $_aol_version = '';
    const BROWSER_UNKNOWN = '';
    const VERSION_UNKNOWN = '';
    const BROWSER_OPERA = 'Opera'; // http://www.opera.com/
    const BROWSER_OPERA_MINI = 'Opera Mini'; // http://www.opera.com/mini/
    const BROWSER_WEBTV = 'WebTV'; // http://www.webtv.net/pc/
    const BROWSER_EDGE = 'Edge'; // https://www.microsoft.com/edge
    const BROWSER_IE = 'Internet Explorer'; // http://www.microsoft.com/ie/
    const BROWSER_POCKET_IE = 'Pocket Internet Explorer'; // http://en.wikipedia.org/wiki/Internet_Explorer_Mobile
    const BROWSER_KONQUEROR = 'Konqueror'; // http://www.konqueror.org/
    const BROWSER_ICAB = 'iCab'; // http://www.icab.de/
    const BROWSER_OMNIWEB = 'OmniWeb'; // http://www.omnigroup.com/applications/omniweb/
    const BROWSER_FIREBIRD = 'Firebird'; // http://www.ibphoenix.com/
    const BROWSER_FIREFOX = 'Firefox'; // http://www.mozilla.com/en-US/firefox/firefox.html
    const BROWSER_ICEWEASEL = 'Iceweasel'; // http://www.geticeweasel.org/
    const BROWSER_SHIRETOKO = 'Shiretoko'; // http://wiki.mozilla.org/Projects/shiretoko
    const BROWSER_MOZILLA = 'Mozilla'; // http://www.mozilla.com/en-US/
    const BROWSER_AMAYA = 'Amaya'; // http://www.w3.org/Amaya/
    const BROWSER_LYNX = 'Lynx'; // http://en.wikipedia.org/wiki/Lynx
    const BROWSER_SAFARI = 'Safari'; // http://apple.com
    const BROWSER_IPHONE = 'iPhone'; // http://apple.com
    const BROWSER_IPOD = 'iPod'; // http://apple.com
    const BROWSER_IPAD = 'iPad'; // http://apple.com
    const BROWSER_CHROME = 'Chrome'; // http://www.google.com/chrome
    const BROWSER_ANDROID = 'Android'; // http://www.android.com/
    const BROWSER_GOOGLEBOT = 'GoogleBot'; // http://en.wikipedia.org/wiki/Googlebot
    const BROWSER_YANDEXBOT = 'YandexBot'; // http://yandex.com/bots
    const BROWSER_YANDEXIMAGERESIZER_BOT = 'YandexImageResizer'; // http://yandex.com/bots
    const BROWSER_YANDEXIMAGES_BOT = 'YandexImages'; // http://yandex.com/bots
    const BROWSER_YANDEXVIDEO_BOT = 'YandexVideo'; // http://yandex.com/bots
    const BROWSER_YANDEXMEDIA_BOT = 'YandexMedia'; // http://yandex.com/bots
    const BROWSER_YANDEXBLOGS_BOT = 'YandexBlogs'; // http://yandex.com/bots
    const BROWSER_YANDEXFAVICONS_BOT = 'YandexFavicons'; // http://yandex.com/bots
    const BROWSER_YANDEXWEBMASTER_BOT = 'YandexWebmaster'; // http://yandex.com/bots
    const BROWSER_YANDEXDIRECT_BOT = 'YandexDirect'; // http://yandex.com/bots
    const BROWSER_YANDEXMETRIKA_BOT = 'YandexMetrika'; // http://yandex.com/bots
    const BROWSER_YANDEXNEWS_BOT = 'YandexNews'; // http://yandex.com/bots
    const BROWSER_YANDEXCATALOG_BOT = 'YandexCatalog'; // http://yandex.com/bots
    const BROWSER_SLURP = 'Yahoo! Slurp'; // http://en.wikipedia.org/wiki/Yahoo!_Slurp
    const BROWSER_W3CVALIDATOR = 'W3C Validator'; // http://validator.w3.org/
    const BROWSER_BLACKBERRY = 'BlackBerry'; // http://www.blackberry.com/
    const BROWSER_ICECAT = 'IceCat'; // http://en.wikipedia.org/wiki/GNU_IceCat
    const BROWSER_NOKIA_S60 = 'Nokia S60 OSS Browser'; // http://en.wikipedia.org/wiki/Web_Browser_for_S60
    const BROWSER_NOKIA = 'Nokia Browser'; // * all other WAP-based browsers on the Nokia Platform
    const BROWSER_MSN = 'MSN Browser'; // http://explorer.msn.com/
    const BROWSER_MSNBOT = 'MSN Bot'; // http://search.msn.com/msnbot.htm
    const BROWSER_BINGBOT = 'Bing Bot'; // http://en.wikipedia.org/wiki/Bingbot
    const BROWSER_VIVALDI = 'Vivalidi'; // https://vivaldi.com/
    const BROWSER_YANDEX = 'Yandex'; // https://browser.yandex.ua/
    const BROWSER_NETSCAPE_NAVIGATOR = 'Netscape Navigator'; // http://browser.netscape.com/ (DEPRECATED)
    const BROWSER_GALEON = 'Galeon'; // http://galeon.sourceforge.net/ (DEPRECATED)
    const BROWSER_NETPOSITIVE = 'NetPositive'; // http://en.wikipedia.org/wiki/NetPositive (DEPRECATED)
    const BROWSER_PHOENIX = 'Phoenix'; // http://en.wikipedia.org/wiki/History_of_Mozilla_Firefox (DEPRECATED)
    const BROWSER_PLAYSTATION = "PlayStation";
    const BROWSER_SAMSUNG = "SamsungBrowser";
    const BROWSER_SILK = "Silk";
    const BROWSER_I_FRAME = "Iframely";
    const BROWSER_COCOA = "CocoaRestClient";
    const PLATFORM_UNKNOWN = '';
    const PLATFORM_WINDOWS = 'Windows';
    const PLATFORM_WINDOWS_CE = 'Windows CE';
    const PLATFORM_APPLE = 'Apple';
    const PLATFORM_LINUX = 'Linux';
    const PLATFORM_OS2 = 'OS/2';
    const PLATFORM_BEOS = 'BeOS';
    const PLATFORM_IPHONE = 'iPhone';
    const PLATFORM_IPOD = 'iPod';
    const PLATFORM_IPAD = 'iPad';
    const PLATFORM_BLACKBERRY = 'BlackBerry';
    const PLATFORM_NOKIA = 'Nokia';
    const PLATFORM_FREEBSD = 'FreeBSD';
    const PLATFORM_OPENBSD = 'OpenBSD';
    const PLATFORM_NETBSD = 'NetBSD';
    const PLATFORM_SUNOS = 'SunOS';
    const PLATFORM_OPENSOLARIS = 'OpenSolaris';
    const PLATFORM_ANDROID = 'Android';
    const PLATFORM_PLAYSTATION = "Sony PlayStation";
    const PLATFORM_ROKU = "Roku";
    const PLATFORM_APPLE_TV = "Apple TV";
    const PLATFORM_TERMINAL = "Terminal";
    const PLATFORM_FIRE_OS = "Fire OS";
    const PLATFORM_SMART_TV = "SMART-TV";
    const PLATFORM_CHROME_OS = "Chrome OS";
    const PLATFORM_JAVA_ANDROID = "Java/Android";
    const PLATFORM_POSTMAN = "Postman";
    const PLATFORM_I_FRAME = "Iframely";
    const OPERATING_SYSTEM_UNKNOWN = '';
    /**
     * Class constructor
     */
    public function __construct($userAgent = "")
    {
        $this->reset();
        if ($userAgent != "") {
            $this->setUserAgent($userAgent);
        } else {
            $this->determine();
        }
    }
    /**
     * Reset all properties
     */
    public function reset()
    {
        $this->_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
        $this->_browser_name = self::BROWSER_UNKNOWN;
        $this->_version = self::VERSION_UNKNOWN;
        $this->_platform = self::PLATFORM_UNKNOWN;
        $this->_os = self::OPERATING_SYSTEM_UNKNOWN;
        $this->_is_aol = false;
        $this->_is_mobile = false;
        $this->_is_tablet = false;
        $this->_is_robot = false;
        $this->_is_facebook = false;
        $this->_aol_version = self::VERSION_UNKNOWN;
    }
    /**
     * Check to see if the specific browser is valid
     * @param string $browserName
     * @return bool True if the browser is the specified browser
     */
    function isBrowser($browserName)
    {
        return (0 == strcasecmp($this->_browser_name, trim($browserName)));
    }
    /**
     * The name of the browser.  All return types are from the class contants
     * @return string Name of the browser
     */
    public function getBrowser()
    {
        return $this->_browser_name;
    }
    /**
     * Set the name of the browser
     * @param $browser string The name of the Browser
     */
    public function setBrowser($browser)
    {
        $this->_browser_name = $browser;
    }
    /**
     * The name of the platform.  All return types are from the class contants
     * @return string Name of the browser
     */
    public function getPlatform()
    {
        return $this->_platform;
    }
    /**
     * Set the name of the platform
     * @param string $platform The name of the Platform
     */
    public function setPlatform($platform)
    {
        $this->_platform = $platform;
    }
    /**
     * The version of the browser.
     * @return string Version of the browser (will only contain alpha-numeric characters and a period)
     */
    public function getVersion()
    {
        return $this->_version;
    }
    /**
     * Set the version of the browser
     * @param string $version The version of the Browser
     */
    public function setVersion($version)
    {
        $this->_version = preg_replace('/[^0-9,.,a-z,A-Z-]/', '', $version);
    }
    /**
     * The version of AOL.
     * @return string Version of AOL (will only contain alpha-numeric characters and a period)
     */
    public function getAolVersion()
    {
        return $this->_aol_version;
    }
    /**
     * Set the version of AOL
     * @param string $version The version of AOL
     */
    public function setAolVersion($version)
    {
        $this->_aol_version = preg_replace('/[^0-9,.,a-z,A-Z]/', '', $version);
    }
    /**
     * Is the browser from AOL?
     * @return boolean True if the browser is from AOL otherwise false
     */
    public function isAol()
    {
        return $this->_is_aol;
    }
    /**
     * Is the browser from a mobile device?
     * @return boolean True if the browser is from a mobile device otherwise false
     */
    public function isMobile()
    {
        return $this->_is_mobile;
    }
    /**
     * Is the browser from a tablet device?
     * @return boolean True if the browser is from a tablet device otherwise false
     */
    public function isTablet()
    {
        return $this->_is_tablet;
    }
    /**
     * Is the browser from a robot (ex Slurp,GoogleBot)?
     * @return boolean True if the browser is from a robot otherwise false
     */
    public function isRobot()
    {
        return $this->_is_robot;
    }
    /**
     * Is the browser from facebook?
     * @return boolean True if the browser is from facebook otherwise false
     */
    public function isFacebook()
    {
        return $this->_is_facebook;
    }
    /**
     * Set the browser to be from AOL
     * @param $isAol
     */
    public function setAol($isAol)
    {
        $this->_is_aol = $isAol;
    }
    /**
     * Set the Browser to be mobile
     * @param boolean $value is the browser a mobile browser or not
     */
    protected function setMobile($value = true)
    {
        $this->_is_mobile = $value;
    }
    /**
     * Set the Browser to be tablet
     * @param boolean $value is the browser a tablet browser or not
     */
    protected function setTablet($value = true)
    {
        $this->_is_tablet = $value;
    }
    /**
     * Set the Browser to be a robot
     * @param boolean $value is the browser a robot or not
     */
    protected function setRobot($value = true)
    {
        $this->_is_robot = $value;
    }
    /**
     * Set the Browser to be a Facebook request
     * @param boolean $value is the browser a robot or not
     */
    protected function setFacebook($value = true)
    {
        $this->_is_facebook = $value;
    }
    /**
     * Get the user agent value in use to determine the browser
     * @return string The user agent from the HTTP header
     */
    public function getUserAgent()
    {
        return $this->_agent;
    }
    /**
     * Set the user agent value (the construction will use the HTTP header value - this will overwrite it)
     * @param string $agent_string The value for the User Agent
     */
    public function setUserAgent($agent_string)
    {
        $this->reset();
        $this->_agent = $agent_string;
        $this->determine();
    }
    /**
     * Used to determine if the browser is actually "chromeframe"
     * @since 1.7
     * @return boolean True if the browser is using chromeframe
     */
    public function isChromeFrame()
    {
        return (strpos($this->_agent, "chromeframe") !== false);
    }
    /**
     * Returns a formatted string with a summary of the details of the browser.
     * @return string formatted string with a summary of the browser
     */
    public function __toString()
    {
        return "<strong>Browser Name:</strong> {$this->getBrowser()}<br/>\n" .
            "<strong>Browser Version:</strong> {$this->getVersion()}<br/>\n" .
            "<strong>Browser User Agent String:</strong> {$this->getUserAgent()}<br/>\n" .
            "<strong>Platform:</strong> {$this->getPlatform()}<br/>";
    }
    /**
     * Protected routine to calculate and determine what the browser is in use (including platform)
     */
    protected function determine()
    {
        $this->checkPlatform();
        $this->checkBrowsers();
        $this->checkForAol();
    }
    /**
     * Protected routine to determine the browser type
     * @return boolean True if the browser was detected otherwise false
     */
    protected function checkBrowsers()
    {
        return (
            // well-known, well-used
            // Special Notes:
            // (1) Opera must be checked before FireFox due to the odd
            //     user agents used in some older versions of Opera
            // (2) WebTV is strapped onto Internet Explorer so we must
            //     check for WebTV before IE
            // (3) (deprecated) Galeon is based on Firefox and needs to be
            //     tested before Firefox is tested
            // (4) OmniWeb is based on Safari so OmniWeb check must occur
            //     before Safari
            // (5) Netscape 9+ is based on Firefox so Netscape checks
            //     before FireFox are necessary
            // (6) Vivalid is UA contains both Firefox and Chrome so Vivalid checks
            //     before Firefox and Chrome
            $this->checkBrowserWebTv() ||
            $this->checkBrowserEdge() ||
            $this->checkBrowserInternetExplorer() ||
            $this->checkBrowserOpera() ||
            $this->checkBrowserGaleon() ||
            $this->checkBrowserNetscapeNavigator9Plus() ||
            $this->checkBrowserVivaldi() ||
            $this->checkBrowserYandex() ||
            $this->checkBrowserFirefox() ||
            $this->checkBrowserChrome() ||
            $this->checkBrowserOmniWeb() ||
            // common mobile
            $this->checkBrowserAndroid() ||
            $this->checkBrowseriPad() ||
            $this->checkBrowseriPod() ||
            $this->checkBrowseriPhone() ||
            $this->checkBrowserBlackBerry() ||
            $this->checkBrowserNokia() ||
            // common bots
            $this->checkBrowserGoogleBot() ||
            $this->checkBrowserMSNBot() ||
            $this->checkBrowserBingBot() ||
            $this->checkBrowserSlurp() ||
            // Yandex bots
            $this->checkBrowserYandexBot() ||
            $this->checkBrowserYandexImageResizerBot() ||
            $this->checkBrowserYandexBlogsBot() ||
            $this->checkBrowserYandexCatalogBot() ||
            $this->checkBrowserYandexDirectBot() ||
            $this->checkBrowserYandexFaviconsBot() ||
            $this->checkBrowserYandexImagesBot() ||
            $this->checkBrowserYandexMediaBot() ||
            $this->checkBrowserYandexMetrikaBot() ||
            $this->checkBrowserYandexNewsBot() ||
            $this->checkBrowserYandexVideoBot() ||
            $this->checkBrowserYandexWebmasterBot() ||
            // check for facebook external hit when loading URL
            $this->checkFacebookExternalHit() ||
            // WebKit base check (post mobile and others)
            $this->checkBrowserSamsung() ||
            $this->checkBrowserSilk() ||
            $this->checkBrowserSafari() ||
            // everyone else
            $this->checkBrowserNetPositive() ||
            $this->checkBrowserFirebird() ||
            $this->checkBrowserKonqueror() ||
            $this->checkBrowserIcab() ||
            $this->checkBrowserPhoenix() ||
            $this->checkBrowserAmaya() ||
            $this->checkBrowserLynx() ||
            $this->checkBrowserShiretoko() ||
            $this->checkBrowserIceCat() ||
            $this->checkBrowserIceweasel() ||
            $this->checkBrowserW3CValidator() ||
            $this->checkBrowserPlayStation() ||
            $this->checkBrowserIframely() ||
            $this->checkBrowserCocoa() ||
            $this->checkBrowserMozilla() /* Mozilla is such an open standard that you must check it last */
        );
    }
    /**
     * Determine if the user is using a BlackBerry (last updated 1.7)
     * @return boolean True if the browser is the BlackBerry browser otherwise false
     */
    protected function checkBrowserBlackBerry()
    {
        if (stripos($this->_agent, 'blackberry') !== false) {
            $aresult = explode("/", stristr($this->_agent, "BlackBerry"));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->_browser_name = self::BROWSER_BLACKBERRY;
                $this->setMobile(true);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the user is using an AOL User Agent (last updated 1.7)
     * @return boolean True if the browser is from AOL otherwise false
     */
    protected function checkForAol()
    {
        $this->setAol(false);
        $this->setAolVersion(self::VERSION_UNKNOWN);
        if (stripos($this->_agent, 'aol') !== false) {
            $aversion = explode(' ', stristr($this->_agent, 'AOL'));
            if (isset($aversion[1])) {
                $this->setAol(true);
                $this->setAolVersion(preg_replace('/[^0-9\.a-z]/i', '', $aversion[1]));
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is the GoogleBot or not (last updated 1.7)
     * @return boolean True if the browser is the GoogletBot otherwise false
     */
    protected function checkBrowserGoogleBot()
    {
        if (stripos($this->_agent, 'googlebot') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'googlebot'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion(str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_GOOGLEBOT;
                $this->setRobot(true);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is the YandexBot or not
     * @return boolean True if the browser is the YandexBot otherwise false
     */
    protected function checkBrowserYandexBot()
    {
        if (stripos($this->_agent, 'YandexBot') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'YandexBot'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion(str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXBOT;
                $this->setRobot(true);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is the YandexImageResizer or not
     * @return boolean True if the browser is the YandexImageResizer otherwise false
     */
    protected function checkBrowserYandexImageResizerBot()
    {
        if (stripos($this->_agent, 'YandexImageResizer') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'YandexImageResizer'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion(str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXIMAGERESIZER_BOT;
                $this->setRobot(true);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is the YandexCatalog or not
     * @return boolean True if the browser is the YandexCatalog otherwise false
     */
    protected function checkBrowserYandexCatalogBot()
    {
        if (stripos($this->_agent, 'YandexCatalog') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'YandexCatalog'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion(str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXCATALOG_BOT;
                $this->setRobot(true);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is the YandexNews or not
     * @return boolean True if the browser is the YandexNews otherwise false
     */
    protected function checkBrowserYandexNewsBot()
    {
        if (stripos($this->_agent, 'YandexNews') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'YandexNews'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion(str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXNEWS_BOT;
                $this->setRobot(true);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is the YandexMetrika or not
     * @return boolean True if the browser is the YandexMetrika otherwise false
     */
    protected function checkBrowserYandexMetrikaBot()
    {
        if (stripos($this->_agent, 'YandexMetrika') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'YandexMetrika'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion(str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXMETRIKA_BOT;
                $this->setRobot(true);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is the YandexDirect or not
     * @return boolean True if the browser is the YandexDirect otherwise false
     */
    protected function checkBrowserYandexDirectBot()
    {
        if (stripos($this->_agent, 'YandexDirect') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'YandexDirect'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion(str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXDIRECT_BOT;
                $this->setRobot(true);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is the YandexWebmaster or not
     * @return boolean True if the browser is the YandexWebmaster otherwise false
     */
    protected function checkBrowserYandexWebmasterBot()
    {
        if (stripos($this->_agent, 'YandexWebmaster') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'YandexWebmaster'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion(str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXWEBMASTER_BOT;
                $this->setRobot(true);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is the YandexFavicons or not
     * @return boolean True if the browser is the YandexFavicons otherwise false
     */
    protected function checkBrowserYandexFaviconsBot()
    {
        if (stripos($this->_agent, 'YandexFavicons') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'YandexFavicons'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion(str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXFAVICONS_BOT;
                $this->setRobot(true);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is the YandexBlogs or not
     * @return boolean True if the browser is the YandexBlogs otherwise false
     */
    protected function checkBrowserYandexBlogsBot()
    {
        if (stripos($this->_agent, 'YandexBlogs') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'YandexBlogs'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion(str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXBLOGS_BOT;
                $this->setRobot(true);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is the YandexMedia or not
     * @return boolean True if the browser is the YandexMedia otherwise false
     */
    protected function checkBrowserYandexMediaBot()
    {
        if (stripos($this->_agent, 'YandexMedia') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'YandexMedia'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion(str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXMEDIA_BOT;
                $this->setRobot(true);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is the YandexVideo or not
     * @return boolean True if the browser is the YandexVideo otherwise false
     */
    protected function checkBrowserYandexVideoBot()
    {
        if (stripos($this->_agent, 'YandexVideo') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'YandexVideo'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion(str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXVIDEO_BOT;
                $this->setRobot(true);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is the YandexImages or not
     * @return boolean True if the browser is the YandexImages otherwise false
     */
    protected function checkBrowserYandexImagesBot()
    {
        if (stripos($this->_agent, 'YandexImages') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'YandexImages'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion(str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXIMAGES_BOT;
                $this->setRobot(true);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is the MSNBot or not (last updated 1.9)
     * @return boolean True if the browser is the MSNBot otherwise false
     */
    protected function checkBrowserMSNBot()
    {
        if (stripos($this->_agent, "msnbot") !== false) {
            $aresult = explode("/", stristr($this->_agent, "msnbot"));
            if (isset($aresult[1])) {
                $aversion = explode(" ", $aresult[1]);
                $this->setVersion(str_replace(";", "", $aversion[0]));
                $this->_browser_name = self::BROWSER_MSNBOT;
                $this->setRobot(true);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is the BingBot or not (last updated 1.9)
     * @return boolean True if the browser is the BingBot otherwise false
     */
    protected function checkBrowserBingBot()
    {
        if (stripos($this->_agent, "bingbot") !== false) {
            $aresult = explode("/", stristr($this->_agent, "bingbot"));
            if (isset($aresult[1])) {
                $aversion = explode(" ", $aresult[1]);
                $this->setVersion(str_replace(";", "", $aversion[0]));
                $this->_browser_name = self::BROWSER_BINGBOT;
                $this->setRobot(true);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is the W3C Validator or not (last updated 1.7)
     * @return boolean True if the browser is the W3C Validator otherwise false
     */
    protected function checkBrowserW3CValidator()
    {
        if (stripos($this->_agent, 'W3C-checklink') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'W3C-checklink'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->_browser_name = self::BROWSER_W3CVALIDATOR;
                return true;
            }
        } else if (stripos($this->_agent, 'W3C_Validator') !== false) {
            // Some of the Validator versions do not delineate w/ a slash - add it back in
            $ua = str_replace("W3C_Validator ", "W3C_Validator/", $this->_agent);
            $aresult = explode('/', stristr($ua, 'W3C_Validator'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->_browser_name = self::BROWSER_W3CVALIDATOR;
                return true;
            }
        } else if (stripos($this->_agent, 'W3C-mobileOK') !== false) {
            $this->_browser_name = self::BROWSER_W3CVALIDATOR;
            $this->setMobile(true);
            return true;
        }
        return false;
    }
    /**
     * Determine if the browser is the Yahoo! Slurp Robot or not (last updated 1.7)
     * @return boolean True if the browser is the Yahoo! Slurp Robot otherwise false
     */
    protected function checkBrowserSlurp()
    {
        if (stripos($this->_agent, 'slurp') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'Slurp'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->_browser_name = self::BROWSER_SLURP;
                $this->setRobot(true);
                $this->setMobile(false);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is Edge or not
     * @return boolean True if the browser is Edge otherwise false
     */
    protected function checkBrowserEdge()
    {
        if (stripos($this->_agent, 'Edge/') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'Edge'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->setBrowser(self::BROWSER_EDGE);
                if (stripos($this->_agent, 'Windows Phone') !== false || stripos($this->_agent, 'Android') !== false) {
                    $this->setMobile(true);
                }
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is Internet Explorer or not (last updated 1.7)
     * @return boolean True if the browser is Internet Explorer otherwise false
     */
    protected function checkBrowserInternetExplorer()
    {
        //  Test for IE11
        if (stripos($this->_agent, 'Trident/7.0; rv:11.0') !== false) {
            $this->setBrowser(self::BROWSER_IE);
            $this->setVersion('11.0');
            return true;
        } // Test for v1 - v1.5 IE
        else if (stripos($this->_agent, 'microsoft internet explorer') !== false) {
            $this->setBrowser(self::BROWSER_IE);
            $this->setVersion('1.0');
            $aresult = stristr($this->_agent, '/');
            if (preg_match('/308|425|426|474|0b1/i', $aresult)) {
                $this->setVersion('1.5');
            }
            return true;
        } // Test for versions > 1.5
        else if (stripos($this->_agent, 'msie') !== false && stripos($this->_agent, 'opera') === false) {
            // See if the browser is the odd MSN Explorer
            if (stripos($this->_agent, 'msnb') !== false) {
                $aresult = explode(' ', stristr(str_replace(';', '; ', $this->_agent), 'MSN'));
                if (isset($aresult[1])) {
                    $this->setBrowser(self::BROWSER_MSN);
                    $this->setVersion(str_replace(array('(', ')', ';'), '', $aresult[1]));
                    return true;
                }
            }
            $aresult = explode(' ', stristr(str_replace(';', '; ', $this->_agent), 'msie'));
            if (isset($aresult[1])) {
                $this->setBrowser(self::BROWSER_IE);
                $this->setVersion(str_replace(array('(', ')', ';'), '', $aresult[1]));
                if(preg_match('#trident/([0-9\.]+);#i', $this->_agent, $aresult)){
                    if($aresult[1] == '3.1'){
                        $this->setVersion('7.0');
                    }
                    else if($aresult[1] == '4.0'){
                        $this->setVersion('8.0');
                    }
                    else if($aresult[1] == '5.0'){
                        $this->setVersion('9.0');
                    }
                    else if($aresult[1] == '6.0'){
                        $this->setVersion('10.0');
                    }
                    else if($aresult[1] == '7.0'){
                        $this->setVersion('11.0');
                    }
                    else if($aresult[1] == '8.0'){
                        $this->setVersion('11.0');
                    }
                }
                if(stripos($this->_agent, 'IEMobile') !== false) {
                    $this->setBrowser(self::BROWSER_POCKET_IE);
                    $this->setMobile(true);
                }
                return true;
            }
        } // Test for versions > IE 10
        else if (stripos($this->_agent, 'trident') !== false) {
            $this->setBrowser(self::BROWSER_IE);
            $result = explode('rv:', $this->_agent);
            if (isset($result[1])) {
                $this->setVersion(preg_replace('/[^0-9.]+/', '', $result[1]));
                $this->_agent = str_replace(array("Mozilla", "Gecko"), "MSIE", $this->_agent);
            }
        } // Test for Pocket IE
        else if (stripos($this->_agent, 'mspie') !== false || stripos($this->_agent, 'pocket') !== false) {
            $aresult = explode(' ', stristr($this->_agent, 'mspie'));
            if (isset($aresult[1])) {
                $this->setPlatform(self::PLATFORM_WINDOWS_CE);
                $this->setBrowser(self::BROWSER_POCKET_IE);
                $this->setMobile(true);
                if (stripos($this->_agent, 'mspie') !== false) {
                    $this->setVersion($aresult[1]);
                } else {
                    $aversion = explode('/', $this->_agent);
                    if (isset($aversion[1])) {
                        $this->setVersion($aversion[1]);
                    }
                }
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is Opera or not (last updated 1.7)
     * @return boolean True if the browser is Opera otherwise false
     */
    protected function checkBrowserOpera()
    {
        if (stripos($this->_agent, 'opera mini') !== false) {
            $resultant = stristr($this->_agent, 'opera mini');
            if (preg_match('/\//', $resultant)) {
                $aresult = explode('/', $resultant);
                if (isset($aresult[1])) {
                    $aversion = explode(' ', $aresult[1]);
                    $this->setVersion($aversion[0]);
                }
            } else {
                $aversion = explode(' ', stristr($resultant, 'opera mini'));
                if (isset($aversion[1])) {
                    $this->setVersion($aversion[1]);
                }
            }
            $this->_browser_name = self::BROWSER_OPERA_MINI;
            $this->setMobile(true);
            return true;
        } else if (stripos($this->_agent, 'opera') !== false) {
            $resultant = stristr($this->_agent, 'opera');
            if (preg_match('/Version\/(1*.*)$/', $resultant, $matches)) {
                $this->setVersion($matches[1]);
            } else if (preg_match('/\//', $resultant)) {
                $aresult = explode('/', str_replace("(", " ", $resultant));
                if (isset($aresult[1])) {
                    $aversion = explode(' ', $aresult[1]);
                    $this->setVersion($aversion[0]);
                }
            } else {
                $aversion = explode(' ', stristr($resultant, 'opera'));
                $this->setVersion(isset($aversion[1]) ? $aversion[1] : "");
            }
            if (stripos($this->_agent, 'Opera Mobi') !== false) {
                $this->setMobile(true);
            }
            $this->_browser_name = self::BROWSER_OPERA;
            return true;
        } else if (stripos($this->_agent, 'OPR') !== false) {
            $resultant = stristr($this->_agent, 'OPR');
            if (preg_match('/\//', $resultant)) {
                $aresult = explode('/', str_replace("(", " ", $resultant));
                if (isset($aresult[1])) {
                    $aversion = explode(' ', $aresult[1]);
                    $this->setVersion($aversion[0]);
                }
            }
            if (stripos($this->_agent, 'Mobile') !== false) {
                $this->setMobile(true);
            }
            $this->_browser_name = self::BROWSER_OPERA;
            return true;
        }
        return false;
    }
    /**
     * Determine if the browser is Chrome or not (last updated 1.7)
     * @return boolean True if the browser is Chrome otherwise false
     */
    protected function checkBrowserChrome()
    {
        if (stripos($this->_agent, 'Chrome') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'Chrome'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->setBrowser(self::BROWSER_CHROME);
                //Chrome on Android
                if (stripos($this->_agent, 'Android') !== false) {
                    if (stripos($this->_agent, 'Mobile') !== false) {
                        $this->setMobile(true);
                    } else {
                        $this->setTablet(true);
                    }
                }
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is WebTv or not (last updated 1.7)
     * @return boolean True if the browser is WebTv otherwise false
     */
    protected function checkBrowserWebTv()
    {
        if (stripos($this->_agent, 'webtv') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'webtv'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->setBrowser(self::BROWSER_WEBTV);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is NetPositive or not (last updated 1.7)
     * @return boolean True if the browser is NetPositive otherwise false
     */
    protected function checkBrowserNetPositive()
    {
        if (stripos($this->_agent, 'NetPositive') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'NetPositive'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion(str_replace(array('(', ')', ';'), '', $aversion[0]));
                $this->setBrowser(self::BROWSER_NETPOSITIVE);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is Galeon or not (last updated 1.7)
     * @return boolean True if the browser is Galeon otherwise false
     */
    protected function checkBrowserGaleon()
    {
        if (stripos($this->_agent, 'galeon') !== false) {
            $aresult = explode(' ', stristr($this->_agent, 'galeon'));
            $aversion = explode('/', $aresult[0]);
            if (isset($aversion[1])) {
                $this->setVersion($aversion[1]);
                $this->setBrowser(self::BROWSER_GALEON);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is Konqueror or not (last updated 1.7)
     * @return boolean True if the browser is Konqueror otherwise false
     */
    protected function checkBrowserKonqueror()
    {
        if (stripos($this->_agent, 'Konqueror') !== false) {
            $aresult = explode(' ', stristr($this->_agent, 'Konqueror'));
            $aversion = explode('/', $aresult[0]);
            if (isset($aversion[1])) {
                $this->setVersion($aversion[1]);
                $this->setBrowser(self::BROWSER_KONQUEROR);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is iCab or not (last updated 1.7)
     * @return boolean True if the browser is iCab otherwise false
     */
    protected function checkBrowserIcab()
    {
        if (stripos($this->_agent, 'icab') !== false) {
            $aversion = explode(' ', stristr(str_replace('/', ' ', $this->_agent), 'icab'));
            if (isset($aversion[1])) {
                $this->setVersion($aversion[1]);
                $this->setBrowser(self::BROWSER_ICAB);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is OmniWeb or not (last updated 1.7)
     * @return boolean True if the browser is OmniWeb otherwise false
     */
    protected function checkBrowserOmniWeb()
    {
        if (stripos($this->_agent, 'omniweb') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'omniweb'));
            $aversion = explode(' ', isset($aresult[1]) ? $aresult[1] : "");
            $this->setVersion($aversion[0]);
            $this->setBrowser(self::BROWSER_OMNIWEB);
            return true;
        }
        return false;
    }
    /**
     * Determine if the browser is Phoenix or not (last updated 1.7)
     * @return boolean True if the browser is Phoenix otherwise false
     */
    protected function checkBrowserPhoenix()
    {
        if (stripos($this->_agent, 'Phoenix') !== false) {
            $aversion = explode('/', stristr($this->_agent, 'Phoenix'));
            if (isset($aversion[1])) {
                $this->setVersion($aversion[1]);
                $this->setBrowser(self::BROWSER_PHOENIX);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is Firebird or not (last updated 1.7)
     * @return boolean True if the browser is Firebird otherwise false
     */
    protected function checkBrowserFirebird()
    {
        if (stripos($this->_agent, 'Firebird') !== false) {
            $aversion = explode('/', stristr($this->_agent, 'Firebird'));
            if (isset($aversion[1])) {
                $this->setVersion($aversion[1]);
                $this->setBrowser(self::BROWSER_FIREBIRD);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is Netscape Navigator 9+ or not (last updated 1.7)
     * NOTE: (http://browser.netscape.com/ - Official support ended on March 1st, 2008)
     * @return boolean True if the browser is Netscape Navigator 9+ otherwise false
     */
    protected function checkBrowserNetscapeNavigator9Plus()
    {
        if (stripos($this->_agent, 'Firefox') !== false && preg_match('/Navigator\/([^ ]*)/i', $this->_agent, $matches)) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_NETSCAPE_NAVIGATOR);
            return true;
        } else if (stripos($this->_agent, 'Firefox') === false && preg_match('/Netscape6?\/([^ ]*)/i', $this->_agent, $matches)) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_NETSCAPE_NAVIGATOR);
            return true;
        }
        return false;
    }
    /**
     * Determine if the browser is Shiretoko or not (https://wiki.mozilla.org/Projects/shiretoko) (last updated 1.7)
     * @return boolean True if the browser is Shiretoko otherwise false
     */
    protected function checkBrowserShiretoko()
    {
        if (stripos($this->_agent, 'Mozilla') !== false && preg_match('/Shiretoko\/([^ ]*)/i', $this->_agent, $matches)) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_SHIRETOKO);
            return true;
        }
        return false;
    }
    /**
     * Determine if the browser is Ice Cat or not (http://en.wikipedia.org/wiki/GNU_IceCat) (last updated 1.7)
     * @return boolean True if the browser is Ice Cat otherwise false
     */
    protected function checkBrowserIceCat()
    {
        if (stripos($this->_agent, 'Mozilla') !== false && preg_match('/IceCat\/([^ ]*)/i', $this->_agent, $matches)) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_ICECAT);
            return true;
        }
        return false;
    }
    /**
     * Determine if the browser is Nokia or not (last updated 1.7)
     * @return boolean True if the browser is Nokia otherwise false
     */
    protected function checkBrowserNokia()
    {
        if (preg_match("/Nokia([^\/]+)\/([^ SP]+)/i", $this->_agent, $matches)) {
            $this->setVersion($matches[2]);
            if (stripos($this->_agent, 'Series60') !== false || strpos($this->_agent, 'S60') !== false) {
                $this->setBrowser(self::BROWSER_NOKIA_S60);
            } else {
                $this->setBrowser(self::BROWSER_NOKIA);
            }
            $this->setMobile(true);
            return true;
        }
        return false;
    }
    /**
     * Determine if the browser is Firefox or not (last updated 1.7)
     * @return boolean True if the browser is Firefox otherwise false
     */
    protected function checkBrowserFirefox()
    {
        if (stripos($this->_agent, 'safari') === false) {
            if (preg_match("/Firefox[\/ \(]([^ ;\)]+)/i", $this->_agent, $matches)) {
                $this->setVersion($matches[1]);
                $this->setBrowser(self::BROWSER_FIREFOX);
                //Firefox on Android
                if (stripos($this->_agent, 'Android') !== false) {
                    if (stripos($this->_agent, 'Mobile') !== false) {
                        $this->setMobile(true);
                    } else {
                        $this->setTablet(true);
                    }
                }
                return true;
            } else if (preg_match("/Firefox$/i", $this->_agent, $matches)) {
                $this->setVersion("");
                $this->setBrowser(self::BROWSER_FIREFOX);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is Firefox or not (last updated 1.7)
     * @return boolean True if the browser is Firefox otherwise false
     */
    protected function checkBrowserIceweasel()
    {
        if (stripos($this->_agent, 'Iceweasel') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'Iceweasel'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->setBrowser(self::BROWSER_ICEWEASEL);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is Mozilla or not (last updated 1.7)
     * @return boolean True if the browser is Mozilla otherwise false
     */
    protected function checkBrowserMozilla()
    {
        if (stripos($this->_agent, 'mozilla') !== false && preg_match('/rv:[0-9].[0-9][a-b]?/i', $this->_agent) && stripos($this->_agent, 'netscape') === false) {
            $aversion = explode(' ', stristr($this->_agent, 'rv:'));
            preg_match('/rv:[0-9].[0-9][a-b]?/i', $this->_agent, $aversion);
            $this->setVersion(str_replace('rv:', '', $aversion[0]));
            $this->setBrowser(self::BROWSER_MOZILLA);
            return true;
        } else if (stripos($this->_agent, 'mozilla') !== false && preg_match('/rv:[0-9]\.[0-9]/i', $this->_agent) && stripos($this->_agent, 'netscape') === false) {
            $aversion = explode('', stristr($this->_agent, 'rv:'));
            $this->setVersion(str_replace('rv:', '', $aversion[0]));
            $this->setBrowser(self::BROWSER_MOZILLA);
            return true;
        } else if (stripos($this->_agent, 'mozilla') !== false && preg_match('/mozilla\/([^ ]*)/i', $this->_agent, $matches) && stripos($this->_agent, 'netscape') === false) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_MOZILLA);
            return true;
        }
        return false;
    }
    /**
     * Determine if the browser is Lynx or not (last updated 1.7)
     * @return boolean True if the browser is Lynx otherwise false
     */
    protected function checkBrowserLynx()
    {
        if (stripos($this->_agent, 'lynx') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'Lynx'));
            $aversion = explode(' ', (isset($aresult[1]) ? $aresult[1] : ""));
            $this->setVersion($aversion[0]);
            $this->setBrowser(self::BROWSER_LYNX);
            return true;
        }
        return false;
    }
    /**
     * Determine if the browser is Amaya or not (last updated 1.7)
     * @return boolean True if the browser is Amaya otherwise false
     */
    protected function checkBrowserAmaya()
    {
        if (stripos($this->_agent, 'amaya') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'Amaya'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->setBrowser(self::BROWSER_AMAYA);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is Safari or not (last updated 1.7)
     * @return boolean True if the browser is Safari otherwise false
     */
    protected function checkBrowserSafari()
    {
        if (stripos($this->_agent, 'Safari') !== false
            && stripos($this->_agent, 'iPhone') === false
            && stripos($this->_agent, 'iPod') === false
        ) {
            $aresult = explode('/', stristr($this->_agent, 'Version'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
            } else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            $this->setBrowser(self::BROWSER_SAFARI);
            return true;
        }
        return false;
    }
    protected function checkBrowserSamsung()
    {
        if (stripos($this->_agent, 'SamsungBrowser') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'SamsungBrowser'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
            } else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            $this->setBrowser(self::BROWSER_SAMSUNG);
            return true;
        }
        return false;
    }
    protected function checkBrowserSilk()
    {
        if (stripos($this->_agent, 'Silk') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'Silk'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
            } else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            $this->setBrowser(self::BROWSER_SILK);
            return true;
        }
        return false;
    }
    protected function checkBrowserIframely()
    {
        if (stripos($this->_agent, 'Iframely') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'Iframely'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
            } else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            $this->setBrowser(self::BROWSER_I_FRAME);
            return true;
        }
        return false;
    }
    protected function checkBrowserCocoa()
    {
        if (stripos($this->_agent, 'CocoaRestClient') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'CocoaRestClient'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
            } else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            $this->setBrowser(self::BROWSER_COCOA);
            return true;
        }
        return false;
    }
    /**
     * Detect if URL is loaded from FacebookExternalHit
     * @return boolean True if it detects FacebookExternalHit otherwise false
     */
    protected function checkFacebookExternalHit()
    {
        if (stristr($this->_agent, 'FacebookExternalHit')) {
            $this->setRobot(true);
            $this->setFacebook(true);
            return true;
        }
        return false;
    }
    /**
     * Detect if URL is being loaded from internal Facebook browser
     * @return boolean True if it detects internal Facebook browser otherwise false
     */
    protected function checkForFacebookIos()
    {
        if (stristr($this->_agent, 'FBIOS')) {
            $this->setFacebook(true);
            return true;
        }
        return false;
    }
    /**
     * Detect Version for the Safari browser on iOS devices
     * @return boolean True if it detects the version correctly otherwise false
     */
    protected function getSafariVersionOnIos()
    {
        $aresult = explode('/', stristr($this->_agent, 'Version'));
        if (isset($aresult[1])) {
            $aversion = explode(' ', $aresult[1]);
            $this->setVersion($aversion[0]);
            return true;
        }
        return false;
    }
    /**
     * Detect Version for the Chrome browser on iOS devices
     * @return boolean True if it detects the version correctly otherwise false
     */
    protected function getChromeVersionOnIos()
    {
        $aresult = explode('/', stristr($this->_agent, 'CriOS'));
        if (isset($aresult[1])) {
            $aversion = explode(' ', $aresult[1]);
            $this->setVersion($aversion[0]);
            $this->setBrowser(self::BROWSER_CHROME);
            return true;
        }
        return false;
    }
    /**
     * Determine if the browser is iPhone or not (last updated 1.7)
     * @return boolean True if the browser is iPhone otherwise false
     */
    protected function checkBrowseriPhone()
    {
        if (stripos($this->_agent, 'iPhone') !== false) {
            $this->setVersion(self::VERSION_UNKNOWN);
            $this->setBrowser(self::BROWSER_IPHONE);
            $this->getSafariVersionOnIos();
            $this->getChromeVersionOnIos();
            $this->checkForFacebookIos();
            $this->setMobile(true);
            return true;
        }
        return false;
    }
    /**
     * Determine if the browser is iPad or not (last updated 1.7)
     * @return boolean True if the browser is iPad otherwise false
     */
    protected function checkBrowseriPad()
    {
        if (stripos($this->_agent, 'iPad') !== false) {
            $this->setVersion(self::VERSION_UNKNOWN);
            $this->setBrowser(self::BROWSER_IPAD);
            $this->getSafariVersionOnIos();
            $this->getChromeVersionOnIos();
            $this->checkForFacebookIos();
            $this->setTablet(true);
            return true;
        }
        return false;
    }
    /**
     * Determine if the browser is iPod or not (last updated 1.7)
     * @return boolean True if the browser is iPod otherwise false
     */
    protected function checkBrowseriPod()
    {
        if (stripos($this->_agent, 'iPod') !== false) {
            $this->setVersion(self::VERSION_UNKNOWN);
            $this->setBrowser(self::BROWSER_IPOD);
            $this->getSafariVersionOnIos();
            $this->getChromeVersionOnIos();
            $this->checkForFacebookIos();
            $this->setMobile(true);
            return true;
        }
        return false;
    }
    /**
     * Determine if the browser is Android or not (last updated 1.7)
     * @return boolean True if the browser is Android otherwise false
     */
    protected function checkBrowserAndroid()
    {
        if (stripos($this->_agent, 'Android') !== false) {
            $aresult = explode(' ', stristr($this->_agent, 'Android'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
            } else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            if (stripos($this->_agent, 'Mobile') !== false) {
                $this->setMobile(true);
            } else {
                $this->setTablet(true);
            }
            $this->setBrowser(self::BROWSER_ANDROID);
            return true;
        }
        return false;
    }
    /**
     * Determine if the browser is Vivaldi
     * @return boolean True if the browser is Vivaldi otherwise false
     */
    protected function checkBrowserVivaldi()
    {
        if (stripos($this->_agent, 'Vivaldi') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'Vivaldi'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->setBrowser(self::BROWSER_VIVALDI);
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is Yandex
     * @return boolean True if the browser is Yandex otherwise false
     */
    protected function checkBrowserYandex()
    {
        if (stripos($this->_agent, 'YaBrowser') !== false) {
            $aresult = explode('/', stristr($this->_agent, 'YaBrowser'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->setBrowser(self::BROWSER_YANDEX);
                if (stripos($this->_agent, 'iPad') !== false) {
                    $this->setTablet(true);
                } elseif (stripos($this->_agent, 'Mobile') !== false) {
                    $this->setMobile(true);
                } elseif (stripos($this->_agent, 'Android') !== false) {
                    $this->setTablet(true);
                }
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the browser is a PlayStation
     * @return boolean True if the browser is PlayStation otherwise false
     */
    protected function checkBrowserPlayStation()
    {
        if (stripos($this->_agent, 'PlayStation ') !== false) {
            $aresult = explode(' ', stristr($this->_agent, 'PlayStation '));
            $this->setBrowser(self::BROWSER_PLAYSTATION);
            if (isset($aresult[0])) {
                $aversion = explode(')', $aresult[2]);
                $this->setVersion($aversion[0]);
                if (stripos($this->_agent, 'Portable)') !== false || stripos($this->_agent, 'Vita') !== false) {
                    $this->setMobile(true);
                }
                return true;
            }
        }
        return false;
    }
    /**
     * Determine the user's platform (last updated 2.0)
     */
    protected function checkPlatform()
    {
		$this->_platform = $this->_getOS();
        /*if (stripos($this->_agent, 'windows') !== false) {
            $this->_platform = self::PLATFORM_WINDOWS;
        } else if (stripos($this->_agent, 'iPad') !== false) {
            $this->_platform = self::PLATFORM_IPAD;
        } else if (stripos($this->_agent, 'iPod') !== false) {
            $this->_platform = self::PLATFORM_IPOD;
        } else if (stripos($this->_agent, 'iPhone') !== false) {
            $this->_platform = self::PLATFORM_IPHONE;
        } elseif (stripos($this->_agent, 'mac') !== false) {
            $this->_platform = self::PLATFORM_APPLE;
        } elseif (stripos($this->_agent, 'android') !== false) {
            $this->_platform = self::PLATFORM_ANDROID;
        } elseif (stripos($this->_agent, 'Silk') !== false) {
            $this->_platform = self::PLATFORM_FIRE_OS;
        } elseif (stripos($this->_agent, 'linux') !== false && stripos($this->_agent, 'SMART-TV') !== false ) {
            $this->_platform = self::PLATFORM_LINUX .'/'.self::PLATFORM_SMART_TV;
        } elseif (stripos($this->_agent, 'linux') !== false) {
            $this->_platform = self::PLATFORM_LINUX;
        } else if (stripos($this->_agent, 'Nokia') !== false) {
            $this->_platform = self::PLATFORM_NOKIA;
        } else if (stripos($this->_agent, 'BlackBerry') !== false) {
            $this->_platform = self::PLATFORM_BLACKBERRY;
        } elseif (stripos($this->_agent, 'FreeBSD') !== false) {
            $this->_platform = self::PLATFORM_FREEBSD;
        } elseif (stripos($this->_agent, 'OpenBSD') !== false) {
            $this->_platform = self::PLATFORM_OPENBSD;
        } elseif (stripos($this->_agent, 'NetBSD') !== false) {
            $this->_platform = self::PLATFORM_NETBSD;
        } elseif (stripos($this->_agent, 'OpenSolaris') !== false) {
            $this->_platform = self::PLATFORM_OPENSOLARIS;
        } elseif (stripos($this->_agent, 'SunOS') !== false) {
            $this->_platform = self::PLATFORM_SUNOS;
        } elseif (stripos($this->_agent, 'OS\/2') !== false) {
            $this->_platform = self::PLATFORM_OS2;
        } elseif (stripos($this->_agent, 'BeOS') !== false) {
            $this->_platform = self::PLATFORM_BEOS;
        } elseif (stripos($this->_agent, 'win') !== false) {
            $this->_platform = self::PLATFORM_WINDOWS;
        } elseif (stripos($this->_agent, 'Playstation') !== false) {
            $this->_platform = self::PLATFORM_PLAYSTATION;
        } elseif (stripos($this->_agent, 'Roku') !== false) {
            $this->_platform = self::PLATFORM_ROKU;
        } elseif (stripos($this->_agent, 'iOS') !== false) {
            $this->_platform = self::PLATFORM_IPHONE . '/' . self::PLATFORM_IPAD;
        } elseif (stripos($this->_agent, 'tvOS') !== false) {
            $this->_platform = self::PLATFORM_APPLE_TV;
        } elseif (stripos($this->_agent, 'curl') !== false) {
            $this->_platform = self::PLATFORM_TERMINAL;
        } elseif (stripos($this->_agent, 'CrOS') !== false) {
            $this->_platform = self::PLATFORM_CHROME_OS;
        } elseif (stripos($this->_agent, 'okhttp') !== false) {
            $this->_platform = self::PLATFORM_JAVA_ANDROID;
        } elseif (stripos($this->_agent, 'PostmanRuntime') !== false) {
            $this->_platform = self::PLATFORM_POSTMAN;
        } elseif (stripos($this->_agent, 'Iframely') !== false) {
            $this->_platform = self::PLATFORM_I_FRAME;
        }*/
    }
	
	protected function _getOS() { 

		$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

		$os_platform    =   "";

		$os_array       =   array(
								'/windows nt 10/i'     =>  'Windows 10',
								'/windows nt 6.3/i'     =>  'Windows 8.1',
								'/windows nt 6.2/i'     =>  'Windows 8',
								'/windows nt 6.1/i'     =>  'Windows 7',
								'/windows nt 6.0/i'     =>  'Windows Vista',
								'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
								'/windows nt 5.1/i'     =>  'Windows XP',
								'/windows xp/i'         =>  'Windows XP',
								'/windows nt 5.0/i'     =>  'Windows 2000',
								'/windows me/i'         =>  'Windows ME',
								'/win98/i'              =>  'Windows 98',
								'/win95/i'              =>  'Windows 95',
								'/win16/i'              =>  'Windows 3.11',
								'/macintosh|mac os x/i' =>  'Mac OS X',
								'/mac_powerpc/i'        =>  'Mac OS 9',
								'/linux/i'              =>  'Linux',
								'/ubuntu/i'             =>  'Ubuntu',
								'/iphone/i'             =>  'iPhone',
								'/ipod/i'               =>  'iPod',
								'/ipad/i'               =>  'iPad',
								'/android/i'            =>  'Android',
								'/blackberry/i'         =>  'BlackBerry',
								'/webos/i'              =>  'Mobile'
							);

		foreach ($os_array as $regex => $value) { 

			if (preg_match($regex, $user_agent)) {
				$os_platform    =   $value;
			}

		}   

		return $os_platform;

	}
}

class Geoplugin {
	//the geoPlugin server
	var $host = 'http://www.geoplugin.net/php.gp?ip={IP}&base_currency={CURRENCY}';
 
	//the default base currency
	var $currency = 'USD';
 
	//initiate the geoPlugin vars
	var $ip = null;
	var $city = null;
	var $region = null;
	var $areaCode = null;
	var $dmaCode = null;
	var $countryCode = null;
	var $countryName = null;
	var $continentCode = null;
	var $latitute = null;
	var $longitude = null;
	var $currencyCode = null;
	var $currencySymbol = null;
	var $currencyConverter = null;
	
	public function __construct() {
		
	}
 
	public function geoPlugin() {
 
	}
 
	public function locate($ip = null) {
 
		global $_SERVER;
 
		if ( is_null( $ip ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
 
		$host = str_replace( '{IP}', $ip, $this->host );
		$host = str_replace( '{CURRENCY}', $this->currency, $host );
 
		$data = array();
 
		$response = $this->fetch($host);
 
		$data = unserialize($response);
 
		//set the geoPlugin vars
		$this->ip = $ip;
		$this->city = $data['geoplugin_city'];
		$this->region = $data['geoplugin_region'];
		$this->areaCode = $data['geoplugin_areaCode'];
		$this->dmaCode = $data['geoplugin_dmaCode'];
		$this->countryCode = $data['geoplugin_countryCode'];
		$this->countryName = $data['geoplugin_countryName'];
		$this->continentCode = $data['geoplugin_continentCode'];
		$this->continentName = $data['geoplugin_continentName'];
		$this->latitude = $data['geoplugin_latitude'];
		$this->longitude = $data['geoplugin_longitude'];
		$this->currencyCode = $data['geoplugin_currencyCode'];
		$this->currencySymbol = $data['geoplugin_currencySymbol'];
		$this->currencyConverter = $data['geoplugin_currencyConverter'];
 
	}
 
	private function fetch($host) {
 
		if ( function_exists('curl_init') ) {
 
			//use cURL to fetch data
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $host);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'geoPlugin PHP Class v1.0');
			$response = curl_exec($ch);
			curl_close ($ch);
 
		} else if ( ini_get('allow_url_fopen') ) {
 
			//fall back to fopen()
			$response = file_get_contents($host, 'r');
 
		} else {
 
			trigger_error ('geoPlugin class Error: Cannot retrieve data. Either compile PHP with cURL support or enable allow_url_fopen in php.ini ', E_USER_ERROR);
			return;
 
		}
 
		return $response;
	}
 
	public function convert($amount, $float=2, $symbol=true) {
 
		//easily convert amounts to geolocated currency.
		if ( !is_numeric($this->currencyConverter) || $this->currencyConverter == 0 ) {
			trigger_error('geoPlugin class Notice: currencyConverter has no value.', E_USER_NOTICE);
			return $amount;
		}
		if ( !is_numeric($amount) ) {
			trigger_error ('geoPlugin class Warning: The amount passed to geoPlugin::convert is not numeric.', E_USER_WARNING);
			return $amount;
		}
		if ( $symbol === true ) {
			return $this->currencySymbol . round( ($amount * $this->currencyConverter), $float );
		} else {
			return round( ($amount * $this->currencyConverter), $float );
		}
	}
 
	public function nearby($radius=10, $limit=null) {
 
		if ( !is_numeric($this->latitude) || !is_numeric($this->longitude) ) {
			trigger_error ('geoPlugin class Warning: Incorrect latitude or longitude values.', E_USER_NOTICE);
			return array( array() );
		}
 
		$host = "http://www.geoplugin.net/extras/nearby.gp?lat=" . $this->latitude . "&long=" . $this->longitude . "&radius={$radius}";
 
		if ( is_numeric($limit) )
			$host .= "&limit={$limit}";
 
		return unserialize( $this->fetch($host) );
 
	}
}

