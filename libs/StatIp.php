<?php

/**
 * Класс проверки и блокировки ip-адреса.
 */
abstract class StatIp
{
    /**
     * Время блокировки в секундах.
     */
    const blockSeconds = 60;
    /**
     * Интервал времени запросов страниц.
     */
    const intervalSeconds = 30;
    /**
     * Количество запросов страницы в интервал времени.
     */
    const intervalTimes = 5;
    /**
     * Флаг подключения всегда активных пользователей.
     */
    const isAlwaysActive = true;
    /**
     * Флаг подключения всегда заблокированных пользователей.
     */
    const isAlwaysBlock = true;
    /**
     * Флаг нужно ли пропускать заголовки поисковых ботов.
     */
    const isBotBlock = true;
    /**
     * Путь к директории кэширования активных пользователей.
     */
    const pathActive = 'active';
    /**
     * Путь к директории кэширования заблокированных пользователей.
     */
    const pathBlock = 'block';
    /**
     * Флаг абсолютных путей к директориям.
     */
    const pathIsAbsolute = false;
    /**
     * Список всегда активных пользователей.
     */
    public static $alwaysActive = array(
        // Список IP адресов Google
        '209.185.108.134',
        '209.185.108.135',
        '209.185.108.138',
        '209.185.108.139',
        '209.185.108.140',
        '209.185.108.141',
        '209.185.108.142',
        '209.185.108.143',
        '209.185.108.144',
        '209.185.108.145',
        '209.185.108.146',
        '209.185.108.147',
        '209.185.108.148',
        '209.185.108.149',
        '209.185.108.150',
        '209.185.108.151',
        '209.185.108.152',
        '209.185.108.153',
        '209.185.108.154',
        '209.185.108.155',
        '209.185.108.156',
        '209.185.108.157',
        '209.185.108.158',
        '209.185.108.159',
        '209.185.108.160',
        '209.185.108.161',
        '209.185.108.162',
        '209.185.108.163',
        '209.185.108.164',
        '209.185.108.165',
        '209.185.253.167',
        '209.185.253.168',
        '209.185.253.169',
        '209.185.253.170',
        '209.185.253.171',
        '209.185.253.172',
        '209.185.253.173',
        '209.185.253.174',
        '209.185.253.175',
        '209.185.253.176',
        '209.185.253.177',
        '209.185.253.178',
        '209.185.253.179',
        '209.185.253.180',
        '209.185.253.181',
        '209.185.253.182',
        '209.185.253.183',
        '209.185.253.184',
        '209.185.253.185',
        '209.185.253.186',
        '209.185.253.187',
        '209.185.253.188',
        '216.239.33.100',
        '216.239.35.100',
        '216.239.37.100',
        '216.239.39.100',
        '216.239.41.100',
        '216.239.51.100',
        '216.239.53.100',
        '216.239.55.100',
        '216.239.57.100',
        '216.239.59.100',
        '64.208.33.33',
        '64.209.181.52',
        '64.209.181.53',
        '64.68.80.14',
        '64.68.80.32',
        '64.68.80.33',
        '64.68.80.68',
        '64.68.82.143',
        '64.68.82.164',
        '64.68.82.167',
        '64.68.82.168',
        '64.68.82.170',
        '64.68.82.178',
        '64.68.82.18',
        '64.68.82.202',
        '64.68.82.203',
        '64.68.82.204',
        '64.68.82.27',
        '64.68.82.44',
        '64.68.82.50',
        '64.68.82.7',
        '64.68.82.79',
        '66.102.11.100',
        '66.102.7.100',
        '66.102.9.100',
        '66.249.64.16',
        '66.249.64.160',
        '66.249.64.18',
        '66.249.64.181',
        '66.249.64.189',
        '66.249.64.28',
        '66.249.64.30',
        '66.249.64.33',
        '66.249.64.37',
        '66.249.64.38',
        '66.249.64.55',
        '66.249.64.6',
        '66.249.64.66',
        '66.249.64.68',
        '66.249.64.79',
        '66.249.65.105',
        '66.249.65.109',
        '66.249.65.137',
        '66.249.65.143',
        '66.249.65.162',
        '66.249.65.171',
        '66.249.65.201',
        '66.249.65.207',
        '66.249.65.230',
        '66.249.65.38',
        '66.249.66.107',
        '66.249.66.11',
        '66.249.66.112',
        '66.249.66.131',
        '66.249.66.16',
        '66.249.66.164',
        '66.249.66.161',
        '66.249.66.171',
        '66.249.66.196',
        '66.249.66.33',
        '66.249.66.42',
        '66.249.66.73',
        '66.249.66.78',
        '66.249.66.79',
        '66.249.66.81',
        '66.249.66.99',
        '66.249.71.18',
        '66.249.71.28',
        '66.249.71.32',
        '66.249.71.33',
        '66.249.71.40',
        '66.249.71.44',
        '66.249.71.57',
        '66.249.71.67',
        '66.249.71.69',
        '66.249.71.70',
        '66.249.71.72',
        '66.249.71.73',
        '66.249.72.103',
        '66.249.72.114',
        '66.249.72.131',
        '66.249.72.52',
        '66.249.72.76',
        '66.249.72.9',

        // Список IP адресов Yandex
        '213.180.217.10',
        '213.180.217.219',
        '213.180.217.7',
        '213.180.210.1',
        '213.180.194.138',
        '213.180.194.164',
        '213.180.194.185',
        '213.180.216.233',
        '213.180.216.234',
        '213.180.210.5',
        '213.180.210.9',
        '213.180.210.7',
        '213.180.210.10',
        '213.180.216.30',
        '213.180.216.160',
        '213.180.216.164',
        '213.180.216.165',
        '213.180.216.7',
        '213.180.206.248',
        '213.180.210.2',
        '213.180.194.148',
        '213.180.194.163',
        '213.180.194.113',
        '213.180.193.57',
        '213.180.194.65',
        '213.180.193.30',
        '213.180.194.129',
        '213.180.194.139',
        '213.180.194.136',
        '213.180.194.135',
        '213.180.194.137',
        '213.180.194.141',
        '213.180.194.143',
        '213.180.194.144',
        '213.180.194.145',
        '213.180.194.146',
        '213.180.194.151',
        '213.180.194.158',
        '213.180.194.159',
        '213.180.194.161',
        '213.180.194.167',
        '213.180.194.168',
        '213.180.194.171',
        '213.180.194.172',
        '213.180.194.173',
        '87.250.253.242',
        '95.108.150.235',
        '77.88.25.27',

        // Список IP адресов Meta
        '194.0.131.128',
        '194.0.131.129',
        '194.0.131.131',
        '194.0.131.133',
        '194.0.131.135',
        '194.0.131.136',
        '194.0.131.137',
        '194.0.131.139',
        '194.0.131.140',
        '194.0.131.143',
        '194.0.131.144',
        '194.0.131.145',
        '194.0.131.146',
        '194.0.131.147',
        '194.0.131.148',
        '194.0.131.150',
        '194.0.131.151',
        '194.0.131.153',
        '194.0.131.154',
        '194.0.131.155',
        '194.0.131.156',
        '194.0.131.157',
        '194.0.131.161',
        '194.0.131.164',
        '194.0.131.165',
        '194.0.131.167',
        '194.0.131.169',
        '194.0.131.170',
        '194.0.131.171',
        '194.0.131.172',
        '194.0.131.174',
        '194.0.131.175',
        '194.0.131.178',
        '194.0.131.182',
        '194.0.131.184',
        '194.0.131.185',
        '194.0.131.188',
        '194.0.131.189',
        '194.0.131.134',
        '194.0.131.138',
        '194.0.131.141',
        '194.0.131.142',
        '194.0.131.149',
        '194.0.131.152',
        '194.0.131.158',
        '194.0.131.159',
        '194.0.131.160',
        '194.0.131.162',
        '194.0.131.163',
        '194.0.131.166',
        '194.0.131.168',
        '194.0.131.173',
        '194.0.131.176',
        '194.0.131.179',
        '194.0.131.180',
        '194.0.131.181',
        '194.0.131.183',
        '194.0.131.186',
        '194.0.131.187',
        '194.0.131.190',
        '194.0.131.191',

        // Список IP адресов Yahoo
        '67.195.112.50',
        '67.195.37.172',
        '67.195.111.216',

        // Список IP адресов Rambler.ru
        '81.19.66.84',
        '81.19.66.90',

        // Список IP адресов Mail.ru
        '217.69.134.168',

        // Список IP адресов MSN
        '207.46.13.100',
        '207.46.199.55',
        '65.55.3.173',
        '65.52.110.72',
        '65.52.110.18',
        '57.55.116.40',
        '207.46.12.159',
        '207.46.12.22',
        '207.46.12.238',
        '207.46.13.131',
        '207.46.13.132',
        '207.46.13.136',
        '207.46.13.137',
        '207.46.13.138',
        '207.46.13.145',
        '207.46.13.146',
        '207.46.13.45',
        '207.46.13.47',
        '207.46.13.48',
        '207.46.13.51',
        '207.46.13.84',
        '207.46.13.89',
        '207.46.13.90',
        '207.46.13.91',
        '207.46.13.93',
        '207.46.195.226',
        '207.46.195.229',
        '207.46.195.231',
        '207.46.195.232',
        '207.46.195.233',
        '207.46.195.235',
        '207.46.195.236',
        '207.46.195.237',
        '207.46.195.240',
        '207.46.199.37',
        '207.46.199.38',
        '207.46.199.39',
        '207.46.199.40',
        '207.46.199.47',
        '207.46.199.48',
        '207.46.199.49',
        '207.46.199.51',
        '207.46.199.52',
        '207.46.199.53',
        '207.46.199.54',
        '207.46.204.179',
        '207.46.204.192',
        '207.46.204.227',
        '207.46.204.232',
        '207.46.204.243',
        '57.55.112.211',
        '65.52.109.59',
        '65.52.109.60',
        '65.52.110.23',
        '65.52.110.29',
        '65.52.110.35',
        '65.52.110.39',
        '65.52.110.64',
        '65.52.110.68',
        '65.52.110.71',
        '65.52.110.86',
        );

    /**
     * Список всегда заблокированных пользователей.
     */
    public static $alwaysBlock = array('172.16.1.1', );

    /**
     * Список заголовков которые используют поисковики.
     */
    public static $bots = array(
        'rambler',
        'googlebot',
        'aport',
        'yahoo',
        'msnbot',
        'turtle',
        'mail.ru',
        'omsktele',
        'yetibot',
        'picsearch',
        'sape.bot',
        'sape_context',
        'gigabot',
        'snapbot',
        'alexa.com',
        'megadownload.net',
        'askpeter.info',
        'igde.ru',
        'ask.com',
        'qwartabot',
        'yanga.co.uk',
        'scoutjet',
        'similarpages',
        'oozbot',
        'shrinktheweb.com',
        'aboutusbot',
        'followsite.com',
        'dataparksearch',
        'google-sitemaps',
        'appEngine-google',
        'feedfetcher-google',
        'liveinternet.ru',
        'xml-sitemaps.com',
        'agama',
        'metadatalabs.com',
        'h1.hrn.ru',
        'googlealert.com',
        'seo-rus.com',
        'yaDirectBot',
        'yandeG',
        'YandexBot',
        'YandexMobileBot',
        'yandexSomething',
        'Copyscape.com',
        'AdsBot-Google',
        'domaintools.com',
        'Nigma.ru',
        'bing.com',
        'dotnetdotcom',
        'MJ12bot',
        'SocredactorBot',
        'Mediapartners-Google',
        'AhrefsBot',
        'DomainCrawler',
        'MegaIndex',
        'SemrushBot',


        'rambler',
        'feedfetcher-google',
        'liveinternet.ru',
        'xml-sitemaps.com',
        'agama',
        'metadatalabs.com',
        'h1.hrn.ru',
        'googlealert.com',
        'seo-rus.com',
        'yaDirectBot',
        'yandeG',
        'yandexSomething',
        'Copyscape.com',
        'AdsBot-Google',
        'domaintools.com',
        'Nigma.ru',
        'dotnetdotcom',
        'MJ12bot',
        'SocredactorBot',
        'Mediapartners-Google',
        'AhrefsBot',
        'DomainCrawler',
        'MegaIndex',
        'appEngine-google',
        'google-sitemaps',
        'dataparksearch',
        'snapbot',
        'aport',
        'yahoo',
        'msnbot',
        'turtle',
        'mail.ru',
        'omsktele',
        'yetibot',
        'picsearch',
        'sape.bot',
        'sape_context',
        'gigabot',
        'alexa.com',
        'followsite.com',
        'megadownload.net',
        'askpeter.info',
        'igde.ru',
        'ask.com',
        'qwartabot',
        'yanga.co.uk',
        'scoutjet',
        'similarpages',
        'oozbot',
        'shrinktheweb.com',
        'aboutusbot',
        'SemrushBot',


        );

    /**
     * Метод получения текущего ip-адреса из переменных сервера.
     */
    public static function _getIp()
    {

        // ip-адрес по умолчанию
        $ip_address = '127.0.0.1';

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
                    $ip_address = $value;
                    break;
                }
            }
        }

        // Возврат полученного ip-адреса
        return $ip_address;
    }

    public static function isBot(&$botname = '')
    {
        /* Эта функция будет проверять, является ли посетитель роботом поисковой системы */

        $user_agent = getHttpUserAgent();

        foreach (self::$bots as $bot)
            if (stripos($user_agent, $bot) !== false) {
                $botname = $bot;
                return true;
            }
        return false;
    }

    public static function renderBlockAdmin()
    {
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        echo '<html xmlns="http://www.w3.org/1999/xhtml">';
        echo '<head>';
        echo '<title>Вы заблокированы</title>';
        echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
        echo '</head>';
        echo '<body>';
        echo '<p style="background:#ccc;border:solid 1px #aaa;margin:30px;padding:20px;text-align:center;">';
        echo 'Вы заблокированы администрацией ресурса.<br />';
        echo '</p>';
        echo '</body>';
        echo '</html>';
        exit();
    }

    public static function renderBlock($time_block)
    {
       header_sent('HTTP/1.0 502 Bad Gateway');
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        echo '<html xmlns="http://www.w3.org/1999/xhtml">';
        echo '<head>';
        echo '<title>502 Bad Gateway</title>';
        echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
        echo '</head>';
        echo '<body>';
        echo '<h1 style="text-align:center">502 Bad Gateway</h1>';
        echo '<p style="background:#ccc;border:solid 1px #aaa;margin:30px;padding:20px;text-align:center;">';
        echo 'К сожалению, Вы временно заблокированы, из-за частого запроса страниц сайта.<br />';
        echo 'Вам придется подождать. Через <span id="time_block">' . $time_block .
            '</span>' . ' секунд(ы) Вы будете автоматически разблокированы.';
        echo '</p>';
        echo '</body>';
        echo '</html>';
        echo '<script>function block_time_proccess() { setTimeout(function() { var i = document.getElementById("time_block").innerHTML; if(i){ i = parseInt(i); if(i && i > 0){ document.getElementById("time_block").innerHTML = i - 1; block_time_proccess(); } else { setTimeout(function() { location.reload(true); }, 1100); } } }, 1000); } block_time_proccess();</script>';
        exit();
    }

    /**
     * Метод проверки ip-адреса на активность и блокировку.
     */
    public static function checkIp()
    {

    }
}

class StatIpFiles extends StatIp
{
    public static function checkIp()
    {
        // Получение ip-адреса
        $ip_address = self::_getIp();

        // Пропускаем всегда активных пользователей
        if (in_array($ip_address, self::$alwaysActive) && self::isAlwaysActive) {
            return;
        }

        $bootname = null;

        if (self::isBotBlock && self::isBot($bootname)) {
            return;
        }

        // Блокируем всегда заблокированных пользователей
        if (in_array($ip_address, self::$alwaysBlock) && self::isAlwaysBlock) {
            self::renderBlockAdmin();
        }

        // Установка путей к директориям
        $path_active = self::pathActive;
        $path_block = self::pathBlock;

        // Приведение путей к директориям к абсолютному виду
        if (!self::pathIsAbsolute) {
            $path_active = str_replace('\\', '/', dirname(__file__) . '/' . $path_active .
                '/');
            $path_block = str_replace('\\', '/', dirname(__file__) . '/' . $path_block . '/');
        }

        // Проверка возможности записи в директории
        if (!is_writable($path_active)) {
            die('Директория кэширования активных пользователей не создана или закрыта для записи.');
        }
        if (!is_writable($path_block)) {
            die('Директория кэширования заблокированных пользователей не создана или закрыта для записи.');
        }

        // Проверка активных ip-адресов
        $is_active = false;
        if ($dir = opendir($path_active)) {
            while (false !== ($filename = readdir($dir))) {
                // Выбирается ip + время активации этого ip
                if (preg_match('#^(\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3})_(\d+)$#', $filename, $matches)) {
                    if ($matches[2] >= time() - self::intervalSeconds) {
                        if ($matches[1] == $ip_address) {
                            $times = intval(trim(file_get_contents($path_active . $filename)));
                            if ($times >= self::intervalTimes - 1) {
                                touch($path_block . $filename);
                                unlink($path_active . $filename);
                            } else {
                                file_put_contents($path_active . $filename, $times + 1);
                            }
                            $is_active = true;
                        }
                    } else {
                        unlink($path_active . $filename);
                    }
                }
            }
            closedir($dir);
        }

        // Проверка заблокированных ip-адресов
        $is_block = false;
        if ($dir = opendir($path_block)) {
            while (false !== ($filename = readdir($dir))) {
                // Выбирается ip + время блокировки этого ip
                if (preg_match('#^(\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3})_(\d+)$#', $filename, $matches)) {
                    if ($matches[2] >= time() - self::blockSeconds) {
                        if ($matches[1] == $ip_address) {
                            $is_block = true;
                            $time_block = $matches[2] - (time() - self::blockSeconds) + 1;
                        }
                    } else {
                        unlink($path_block . $filename);
                    }
                }
            }
            closedir($dir);
        }

        // ip-адрес заблокирован
        if ($is_block) {
            self::renderBlock($time_block);
        }

        // Создание идентификатора активного ip-адреса
        if (!$is_active) {
            touch($path_active . $ip_address . '_' . time());
        }
    }
}

class StatIpSqLite extends StatIp
{
    public static function checkIp()
    {
        // Получение ip-адреса
        $ip_address = self::_getIp();

        // Пропускаем всегда активных пользователей
        if (in_array($ip_address, self::$alwaysActive) && self::isAlwaysActive) {
            return;
        }

        $bootname = null;

        if (self::isBotBlock && self::isBot($bootname)) {
            return;
        }

        // Блокируем всегда заблокированных пользователей
        if (in_array($ip_address, self::$alwaysBlock) && self::isAlwaysBlock) {
            self::renderBlockAdmin();
        }

        if ($ip_data = db_ips::getActive($ip_address)) {
            db_ips::updateActive($ip_address, $ip_data['count'] + 1);
        } else {
            db_ips::addActive($ip_address, 1);

            $ip_data = array(
                'ip' => $ip_address,
                'time' => time(),
                'count' => 1);
        }

        if ($ip_data) {
            if ($ip_data['time'] >= time() - self::intervalSeconds) {
                if ($ip_data['count'] > self::intervalTimes) {
                    if ($ip_block = db_ips::getBlock($ip_data['ip'])) {
                        if ($ip_block['time'] < time() - self::blockSeconds) {
                            db_ips::updateBlock($ip_block['ip'], $ip_block['count'] + 1);
                        }
                    } else {
                        db_ips::addBlock($ip_data['ip'], $ip_data['count']);
                    }
                }
            } else {
                db_ips::deleteActive($ip_data['ip']);
            }
        }

        $is_block = false;

        if ($ip_block = db_ips::getBlock($ip_data['ip'])) {
            if ($ip_block['time'] > time() - self::blockSeconds) {
                $is_block = true;

                $time_block = $ip_block['time'] - (time() - self::blockSeconds);
            }
        }

        // ip-адрес заблокирован
        if ($is_block) {
            self::renderBlock($time_block);
        }

        db_ips::clearActive(time() - self::blockSeconds);
        db_ips::clearBlock(time() - self::blockSeconds);
    }

    public static function writeIp()
    {
        $bot = null;

        self::isBot($bot);

        $ip_address = trim(self::_getIp());

        if (!empty($ip_address)) {
            if ($ip_for_db = db_ips::get($ip_address)) {
                db_ips::update($ip_address, $ip_for_db['count'] + 1, $bot);
            } else {
                db_ips::add($ip_address, 1, $bot);
            }
        }
    }

    public static function writeUrl()
    {

        $url = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off" ?
            'http:' : 'https:')."//" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        $method = 1;

        if (!empty($_POST)) {
            $method = 2;
        }

        if (!empty($url)) {
            if ($url_for_db = db_ips::getUlr($url, $method)) {
                db_ips::updateUlr($url, $url_for_db['count'] + 1, $url_for_db['duration']);
            } else {
                db_ips::addUlr($url, 1, $method);
            }
        }
    }

    public static function writeAgent()
    {

        $agent = getHttpUserAgent();

        if (!empty($agent)) {
            if ($agent_for_db = db_ips::getAgent($agent)) {
                db_ips::updateAgent($agent, $agent_for_db['count'] + 1);
            } else {
                db_ips::addAgent($agent, 1);
            }
        }
    }

    public static function writeCountAll()
    {
        $count = 0;

        if (file_exists(db_ips::SQLITE_DIR . '/count.txt')) {
            $count = intval(file_get_contents(db_ips::SQLITE_DIR . '/count.txt'));

            if ($count > 0) {
                $count++;

                file_put_contents(db_ips::SQLITE_DIR . '/count.txt', $count);
            }
        }
    }
}

class StatRes
{
    private static $all = false;

    public static function getStatictics()
    {
        $result = array();

        $result_all = array(
            'sum_all' => 0,
            'sum_all_user' => 0,
            'sum_all_bot' => 0,
            );

        $files_bd = array();

        if (is_dir(db_ips::SQLITE_DIR)) {
            $dir = opendir(db_ips::SQLITE_DIR);
            while (($file = readdir($dir)) !== false) {
                if ($file != "." && $file != "..") {
                    if (is_file(db_ips::SQLITE_DIR . '/' . $file)) {
                        $path_parts = pathinfo(db_ips::SQLITE_DIR . '/' . $file);
                        if ($path_parts && isset($path_parts['extension']) && $path_parts['extension']
                            === 'db') {
                            if (preg_match("#^\d{4}\.\d{2}\.\d{2}\.db$#", $file)) {
                                $files_bd[] = $file;
                            }
                        }
                    }
                }
            }
        }

        usort($files_bd, function ($a, $b)
        {
            // $a = intval(preg_replace("#[^\d]#", '', $a));
            //$b = intval(preg_replace("#[^\d]#", '', $b));

            if ($a == $b) {
                return 0; }

            return ($a > $b) ? -1 : 1; }
        );

        $result_all['files_bd'] = $files_bd;

        if (self::$all && !isset($_GET['bd'])) {
            foreach ($files_bd as $file_bd) {
                db_ips::init($file_bd);

                $result[$file_bd] = array(
                    'sum_all_ips' => self::getSumAllIps(),
                    'sum_user_ips' => self::getSumUserIps(),
                    'sum_bot_ips' => self::getSumBotIps(),
                    'sum_list_bot_ips' => self::getListSumBots(),
                    'list_ips' => self::getListIps(),

                    'list_agents' => self::getListAgents(),

                    'list_urls' => self::getListUrls(),
                    );
            }
        } else {
            $file_bd = date('Y.m.d') . '.db';

            if (isset($_GET['bd'])) {
                if (preg_match("#^\d{4}\.\d{2}\.\d{2}\.db$#", $_GET['bd'])) {
                    $file_bd = $_GET['bd'];
                }
            } else {
                foreach ($files_bd as $bd) {
                    $file_bd = $bd;
                    break;
                }
            }

            db_ips::init($file_bd);

            $result[$file_bd] = array(
                'sum_all_ips' => self::getSumAllIps(),
                'sum_user_ips' => self::getSumUserIps(),
                'sum_bot_ips' => self::getSumBotIps(),
                'sum_list_bot_ips' => self::getListSumBots(),
                'list_ips' => self::getListIps(),

                'list_agents' => self::getListAgents(),

                'list_urls' => self::getListUrls(),
                );
        }

        uksort($result, function ($a, $b)
        {
            if ($a == $b) {
                return 0; }

            return ($a > $b) ? -1 : 1; }
        );

        foreach ($result as $data) {
            $result_all['sum_all'] = $result_all['sum_all'] + $data['sum_all_ips'];
            $result_all['sum_all_user'] = $result_all['sum_all_user'] + $data['sum_user_ips'];
            $result_all['sum_all_bot'] = $result_all['sum_all_bot'] + $data['sum_bot_ips'];
        }

        return array('all' => $result_all, 'items' => $result);
    }

    private static function process()
    {
        $result = array();

        return $result;
    }

    private static function getListIps()
    {
        $data = db_ips::getListIps();

        if (is_array($data)) {
            return $data;
        } else {
            return array();
        }
    }

    private static function getSumAllIps()
    {
        $result = null;

        $data = db_ips::getCountAll();

        if (is_array($data) && isset($data['sum']) && $data['sum'] > 0) {
            $result = $data['sum'];
        } else {
            $result = 0;
        }

        return $result;
    }

    private static function getSumBotIps()
    {
        $result = array();

        $data = db_ips::getCountBotForAgent('bot');

        if (is_array($data) && isset($data['sum']) && $data['sum'] > 0) {
            $result = $data['sum'];
        } else {
            $result = 0;
        }

        return $result;
    }

    private static function getSumUserIps()
    {
        $result = null;

        $data = db_ips::getCountUserNotAgent('bot');

        if (is_array($data) && isset($data['sum']) && $data['sum'] > 0) {
            $result = $data['sum'];
        } else {
            $result = 0;
        }

        return $result;
    }

    private static function getListSumBots()
    {
        $result = array();

        //$agents = db_ips::getAgentsBot();

        $bots = StatIp::$bots;

        //foreach ($agents as $agent) {
           // if (!in_array($agent['agent'], $bots)) {
               // $bots[] = $agent['agent'];
          //  }
       // }
        
        $bots = array_unique($bots);                

        foreach ($bots as $bot) {
            $data = db_ips::getCountBotForAgent($bot);

            if (is_array($data) && isset($data['sum']) && $data['sum'] > 0) {
                $result[$bot] = $data['sum'];
            }
        }

        uasort($result, function ($a, $b)
        {
            if ($a == $b) {
                return 0; }

            return ($a > $b) ? -1 : 1; }
        );

        return $result;
    }

    private static function getListAgents()
    {
        $data = db_ips::getListAgents();

        if (is_array($data)) {
            return $data;
        } else {
            return array();
        }
    }

    private static function getListUrls()
    {
        $data = db_ips::getListUrls();

        if (is_array($data)) {
            return $data;
        } else {
            return array();
        }
    }
}

class db_ips
{
    const SQLITE_DIR = 'tmp.sqlite';

    private static $PDO = null;

    private static $start_time = null;

    private static $limit_list = 80;

    private function __construct()
    {
    }

    public static function init($file_db = null)
    {
        self::$start_time = microtime(true);

        if (!$file_db) {
            $file_db = date('Y.m.d') . '.db';
        }

        if (file_exists(self::SQLITE_DIR . '/' . $file_db)) {
            self::$PDO = new \PDO('sqlite:' . self::SQLITE_DIR . '/' . $file_db);
        } else {
            if (!is_dir(self::SQLITE_DIR)) {
                mkdir(self::SQLITE_DIR, 0777, true);
            }

            self::$PDO = new \PDO('sqlite:' . self::SQLITE_DIR . '/' . $file_db);

            $stat = self::$PDO->prepare("CREATE TABLE IF NOT EXISTS `ips`(ip VARCHAR(60), agent VARCHAR(100), bot VARCHAR(60), count INT(11), time INT(11), PRIMARY KEY (`ip`));");
            $stat->execute();
            $stat = self::$PDO->prepare("CREATE TABLE IF NOT EXISTS `ips_active`(ip VARCHAR(60), count INT(11), time INT(11), PRIMARY KEY (`ip`));");
            $stat->execute();
            $stat = self::$PDO->prepare("CREATE TABLE IF NOT EXISTS `ips_block`(ip VARCHAR(60), count INT(11), time INT(11), PRIMARY KEY (`ip`));");
            $stat->execute();
            $stat = self::$PDO->prepare("CREATE TABLE IF NOT EXISTS `urls`(url VARCHAR(100), count INT(11), time INT(11), duration FLOAT, cp FLOAT, method INT(1), memory INT(11), PRIMARY KEY (`url`));");
            $stat->execute();
            $stat = self::$PDO->prepare("CREATE TABLE IF NOT EXISTS `agents`(agent VARCHAR(100), count INT(11), time INT(11), PRIMARY KEY (`agent`));");
            $stat->execute();

            file_put_contents(self::SQLITE_DIR . '/count.txt', 1);
        }
    }

    public static function is($ip)
    {
        $stat = self::$PDO->prepare("SELECT COUNT(*) FROM `ips` WHERE ip=:ip");

        if ($stat) {
            $stat->execute(array('ip' => $ip));
            $result = $stat->fetch(\PDO::FETCH_NUM);

            if ($result[0] > 0) {
                return true;
            } elseif ($result[0] == 0) {
                return false;
            }
        }

        return null;
    }

    public static function get($ip)
    {
        $stat = self::$PDO->prepare("SELECT * FROM `ips` WHERE ip=:ip LIMIT 1");

        if ($stat) {
            $stat->execute(array('ip' => $ip));
            $result = $stat->fetch(\PDO::FETCH_ASSOC);

            if (is_array($result)) {
                return $result;
            }
        }

        return null;
    }

    public static function add($ip, $count, $bot = null)
    {
        $stat = self::$PDO->prepare("INSERT INTO `ips`(`ip`, `agent`, `bot`, `count`, `time`) VALUES (:ip, :agent, :bot, :count, :time);");

        if ($stat) {
            $stat->execute(array(
                'ip' => $ip,
                'count' => $count,
                'agent' => getHttpUserAgent(),
                'bot' => $bot,
                'time' => time()));
        }
    }

    public static function update($ip, $count, $bot = null)
    {
        $stat = self::$PDO->prepare("UPDATE `ips` SET `agent`=:agent, `bot`=:bot, `count`=`count`+1, `time`=:time WHERE `ip`=:ip;");

        if ($stat) {
            $stat->execute(array(
                'ip' => $ip,
                'agent' => getHttpUserAgent(),
                'bot' => $bot,
                //'count' => $count,
                'time' => time()));
        }
    }

    public static function max($ip)
    {
        $stat = self::$PDO->prepare("SELECT MAX(count) FROM `ips` WHERE ip=:ip");

        if ($stat) {
            $stat->execute(array('ip' => $ip));
            $result = $stat->fetch(\PDO::FETCH_NUM);

            if ($result[0] > 0) {
                return $result[0];
            } elseif ($result[0] == 0) {
                return 0;
            }
        }

        return 0;
    }

    public static function delete($ip)
    {
        $stat = self::$PDO->prepare("DELETE FROM `ips` WHERE ip=:ip;");

        if ($stat) {
            $stat->execute(array('ip' => $ip));
        }
    }

    public static function getActive($ip)
    {
        $stat = self::$PDO->prepare("SELECT * FROM `ips_active` WHERE ip=:ip LIMIT 1");

        if ($stat) {
            $stat->execute(array('ip' => $ip));
            $result = $stat->fetch(\PDO::FETCH_ASSOC);

            if (is_array($result)) {
                return $result;
            }
        }

        return null;
    }

    public static function getBlock($ip)
    {
        $stat = self::$PDO->prepare("SELECT * FROM `ips_block` WHERE ip=:ip LIMIT 1");

        if ($stat) {
            $stat->execute(array('ip' => $ip));
            $result = $stat->fetch(\PDO::FETCH_ASSOC);

            if (is_array($result)) {
                return $result;
            }
        }

        return null;
    }

    public static function deleteActive($ip)
    {
        $stat = self::$PDO->prepare("DELETE FROM `ips_active` WHERE ip=:ip;");

        if ($stat) {
            $stat->execute(array('ip' => $ip));
        }
    }

    public static function addBlock($ip, $count)
    {
        $stat = self::$PDO->prepare("INSERT INTO `ips_block`(`ip`, `count`, `time`) VALUES (:ip, :count, :time);");

        if ($stat) {
            $stat->execute(array(
                'ip' => $ip,
                'count' => $count,
                'time' => time()));
        }
    }

    public static function addActive($ip, $count)
    {
        $stat = self::$PDO->prepare("INSERT INTO `ips_active`(`ip`, `count`, `time`) VALUES (:ip, :count, :time);");

        if ($stat) {
            $stat->execute(array(
                'ip' => $ip,
                'count' => $count,
                'time' => time()));
        }
    }

    public static function updateActive($ip, $count)
    {
        $stat = self::$PDO->prepare("UPDATE `ips_active` SET `count`=`count`+1 WHERE `ip`=:ip;");

        if ($stat) {
            $stat->execute(array('ip' => $ip, //'count' => $count
                    ));
        }
    }

    public static function updateBlock($ip, $count)
    {
        $stat = self::$PDO->prepare("UPDATE `ips_block` SET `count`=`count`+1, `time`=:time WHERE `ip`=:ip;");

        if ($stat) {
            $stat->execute(array('ip' => $ip, //'count' => $count,
                    'time' => time()));
        }
    }

    public static function clearActive($time)
    {
        $stat = self::$PDO->prepare("DELETE FROM `ips_active` WHERE time<:time;");

        if ($stat) {
            $stat->execute(array('time' => $time));
        }
    }

    public static function clearBlock($time)
    {
        $stat = self::$PDO->prepare("DELETE FROM `ips_block` WHERE time<:time;");

        if ($stat) {
            $stat->execute(array('time' => $time));
        }
    }

    public static function getUlr($url, $method)
    {
        $stat = self::$PDO->prepare("SELECT * FROM `urls` WHERE url=:url AND method=:method LIMIT 1");

        if ($stat) {
            $stat->execute(array('url' => $url, 'method' => $method));
            $result = $stat->fetch(\PDO::FETCH_ASSOC);

            if (is_array($result)) {
                return $result;
            }
        }

        return null;
    }

    public static function addUlr($url, $count, $method)
    {
        $stat = self::$PDO->prepare("INSERT INTO `urls`(`url`, `count`, `duration`, `cp`, `time`, `method`, `memory`) VALUES (:url, :count, :duration, :cp, :time, :method, :memory);");

        $cp = null;

        if (function_exists('getrusage')) {
            $dat = getrusage();
            $cp = (((float)($dat["ru_utime.tv_usec"] + (float)$dat["ru_stime.tv_usec"]))/1000000);
        }

        if ($stat) {
            $stat->execute(array(
                'url' => $url,
                'count' => $count,
                'duration' => microtime(true) - self::$start_time,
                'cp' => $cp,
                'time' => time(),
                'method' => $method,
                'memory' => memory_get_usage()));
        }
    }

    public static function updateUlr($url, $count, $duration)
    {
        $stat = self::$PDO->prepare("UPDATE `urls` SET `count`=`count`+1, `time`=:time, `duration`=`duration`+:duration, `cp`=`cp`+:cp, `memory`=`memory`+:memory WHERE `url`=:url;");

        $cp = null;

        if (function_exists('getrusage')) {
            $dat = getrusage();
            $cp = (((float)($dat["ru_utime.tv_usec"] + (float)$dat["ru_stime.tv_usec"]))/1000000);
        }

        if ($stat) {
            $stat->execute(array(
                'url' => $url,
                //'count' => $count,
                'duration' => microtime(true) - self::$start_time,
                'cp' => $cp,
                'time' => time(),
                'memory' => memory_get_usage()));
        }
    }

    public static function getAgent($agent)
    {
        $stat = self::$PDO->prepare("SELECT * FROM `agents` WHERE agent=:agent LIMIT 1");

        if ($stat) {
            $stat->execute(array('agent' => $agent));
            $result = $stat->fetch(\PDO::FETCH_ASSOC);

            if (is_array($result)) {
                return $result;
            }
        }

        return null;
    }

    public static function addAgent($agent, $count)
    {
        $stat = self::$PDO->prepare("INSERT INTO `agents`(`agent`, `count`, `time`) VALUES (:agent, :count, :time);");

        if ($stat) {
            $stat->execute(array(
                'agent' => $agent,
                'count' => $count,
                'time' => time()));
        }
    }

    public static function updateAgent($agent, $count)
    {
        $stat = self::$PDO->prepare("UPDATE `agents` SET `count`=`count`+1, `time`=:time WHERE `agent`=:agent;");

        if ($stat) {
            $stat->execute(array('agent' => $agent, //'count' => $count,
                    'time' => time()));
        }
    }

    public static function getListIps()
    {
        $stat = self::$PDO->prepare("SELECT * FROM `ips` ORDER BY `count` desc LIMIT " .
            self::$limit_list);

        if ($stat) {
            $stat->execute();
            $result = $stat->fetchAll(\PDO::FETCH_ASSOC);

            if (is_array($result)) {
                return $result;
            }
        }

        return null;
    }

    public static function getCountAll()
    {
        $stat = self::$PDO->prepare("SELECT SUM(count) as sum FROM `ips`");

        if ($stat) {
            $stat->execute();
            $result = $stat->fetch(\PDO::FETCH_ASSOC);

            if (is_array($result)) {
                return $result;
            }
        }

        return null;
    }

    public static function getAgentsBot()
    {
        $stat = self::$PDO->prepare("SELECT agent FROM `ips` WHERE (LOWER(agent) LIKE '%bot%') GROUP BY agent");

        if ($stat) {
            $stat->execute(array());
            $result = $stat->fetchAll(\PDO::FETCH_ASSOC);

            if (is_array($result)) {
                return $result;
            }
        }

        return null;
    }

    public static function getCountBotForAgent($agent)
    {
        $stat = self::$PDO->prepare("SELECT SUM(count) as sum FROM `ips` WHERE (LOWER(agent) LIKE :agent)");

        if ($stat) {
            $stat->execute(array('agent' => '%' . strtolower($agent) . '%'));
            $result = $stat->fetch(\PDO::FETCH_ASSOC);

            if (is_array($result)) {
                return $result;
            }
        }

        return null;
    }

    public static function getCountUserNotAgent($agent)
    {
        $stat = self::$PDO->prepare("SELECT SUM(count) as sum FROM `ips` WHERE LOWER(agent) NOT LIKE :agent AND LOWER(agent) NOT LIKE '%bot%' AND count<150 ");

        if ($stat) {
            $stat->execute(array('agent' => '%' . strtolower($agent) . '%'));
            $result = $stat->fetch(\PDO::FETCH_ASSOC);

            if (is_array($result)) {
                return $result;
            }
        }

        return null;
    }

    public static function getListAgents()
    {
        $stat = self::$PDO->prepare("SELECT * FROM `agents` ORDER BY `count` desc LIMIT " .
            self::$limit_list);

        if ($stat) {
            $stat->execute();
            $result = $stat->fetchAll(\PDO::FETCH_ASSOC);

            if (is_array($result)) {
                return $result;
            }
        }

        return null;
    }

    public static function getListUrls()
    {
        $stat = self::$PDO->prepare("SELECT * FROM `urls` ORDER BY `duration` desc LIMIT " .
            self::$limit_list);

        if ($stat) {
            $stat->execute();
            $result = $stat->fetchAll(\PDO::FETCH_ASSOC);

            if (is_array($result)) {
                return $result;
            }
        }

        return null;
    }
}

function getHttpUserAgent()
{
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        return $_SERVER['HTTP_USER_AGENT'];
    } else {
        return 'NOT HTTP USER AGENT ';
    }
}
