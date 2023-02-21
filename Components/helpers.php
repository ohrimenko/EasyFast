<?php

function getProxies()
{
    return [
        [
            'proxy' => '168.196.236.139:49155',
            'userpwd' => 'ohrimenkodmitro:4pDiP2MaCe',
        ]
    ];
}

function getProxy()
{
    $proxies = getProxies();
    
    if (count($proxies)) {
        return $proxies[0];
    }
    
    return null;
}

function seleniumDriver($server, $port)
{
    return \App\Components\Selenium::instance()->driver($server, $port);
}

function meta($name)
{
    return data()->meta->{$name};
}

function renderReqDetectJS()
{
    \App\Components\ReqDetect::JS();
}

function renderMeta()
{
    data()->meta->run();
}

function pageScripts()
{
    widget('Page', ['placeType' => 'scripts']);
}

function pageScriptInit()
{
    widget('Page', ['placeType' => 'script_init']);
}

function execFunction($name, $args = [])
{
    if (isAssoc($args)) {
        return $name($args);
    } else {
        switch (count_items($args)) {
            case '1':
                return $name($args[0]);
                break;
            case '2':
                return $name($args[0], $args[1]);
                break;
            case '3':
                return $name($args[0], $args[1], $args[2]);
                break;
            case '4':
                return $name($args[0], $args[1], $args[2], $args[3]);
                break;
            case '5':
                return $name($args[0], $args[1], $args[2], $args[3], $args[4]);
                break;
            case '6':
                return $name($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
                break;
            case '7':
                return $name($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
                break;
            case '8':
                return $name($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6],
                    $args[7]);
                break;
            case '9':
                return $name($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6],
                    $args[7], $args[8]);
                break;
            case '10':
                return $name($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6],
                    $args[7], $args[8], $args[9]);
                break;
            default:
                return $name();
                break;
        }
    }
}

function getClassMethodsRoute($path)
{
    $methods = [];

    preg_match_all("#public function ([\w_-]{1,}?) {0,}\(#sui", file_get_contents($path),
        $matches);

    if (isset($matches[1])) {
        foreach ($matches[1] as $match) {
            if ($match) {
                $methods[] = $match;
            }
        }
    }

    return $methods;
}

function getDomain()
{
    //return request()->domain;

    return config('domain');
}

function clearHtml($html)
{
    $html = str_replace("<br>", '<br />', $html);
    $html = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/sui", '<$1$2>', $html);
    $html = str_replace(["<h3>", '</h3>'], ['<h3>', '</h3>'], $html);
    $html = str_replace(["<h2>", '</h2>'], ['<h3>', '</h3>'], $html);
    $html = str_replace(["<h1>", '</h1>'], ['<h3>', '</h3>'], $html);

    $html = preg_replace("/<(a)[^>]*?(\/?)>(.*?)<\/a>/isu", '$3', $html);

    $html = preg_replace("#<script[^<>/]*?>.*?</script>#sui", '', $html);
    $html = preg_replace("#<svg[^<>/]*?>.*?</svg>#sui", '', $html);
    $html = preg_replace("#<iframe[^<>/]*?>.*?</iframe>#sui", '', $html);
    $html = preg_replace("#<frame[^<>/]*?>.*?</frame>#sui", '', $html);
    $html = preg_replace("#<img[^<>/]*?/?>#sui", '', $html);
    $html = preg_replace("#<head[^<>/]*?>.*?</head>#sui", '', $html);

    $html = preg_replace("#<body[^<>/]*?>#sui", '', $html);
    $html = preg_replace("#<html[^<>/]*?>#sui", '', $html);

    $html = str_replace("<body>", '', $html);
    $html = str_replace("</body>", '', $html);
    $html = str_replace("<html>", '', $html);
    $html = str_replace("</html>", '', $html);

    $html = str_replace('справка | детали | валюта | карта страны', '', $html);

    $html = str_replace("<div>  <br/><br/>
</div>", '', $html);

    $html = close_tags($html);

    return trim($html);
}

function close_tags($content)
{
    $position = 0;
    $open_tags = array();
    //теги для игнорирования
    $ignored_tags = array(
        'br',
        'hr',
        'img');

    while (($position = strpos($content, '<', $position)) !== false) {
        //забираем все теги из контента
        if (preg_match("|^<(/?)([a-z\d]+)\b[^>]*>|i", substr($content, $position), $match)) {
            $tag = strtolower($match[2]);
            //игнорируем все одиночные теги
            if (in_array($tag, $ignored_tags) == false) {
                //тег открыт
                if (isset($match[1]) and $match[1] == '') {
                    if (isset($open_tags[$tag]))
                        $open_tags[$tag]++;
                    else
                        $open_tags[$tag] = 1;
                }
                //тег закрыт
                if (isset($match[1]) and $match[1] == '/') {
                    if (isset($open_tags[$tag]))
                        $open_tags[$tag]--;
                }
            }
            $position += strlen($match[0]);
        } else
            $position++;
    }
    //закрываем все теги
    foreach ($open_tags as $tag => $count_not_closed) {
        if ($count_not_closed > 0)
            $content .= str_repeat("</{$tag}>", $count_not_closed);
    }

    return $content;
}

function html_remove_attributes($text, $allowed = [])
{
    $attributes = implode('|', $allowed);
    $reg = '/(<[\w]+)([^>]*)(>)/i';
    $text = preg_replace_callback($reg, function ($matches)use ($attributes)
    {
        // Если нет разрешенных атрибутов, возвращаем пустой тег
        if (!$attributes) {
            return $matches[1] . $matches[3]; }

        $attr = $matches[2]; $reg = '/(' . $attributes . ')="[^"]*"/i'; preg_match_all($reg,
            $attr, $result); $attr = implode(' ', $result[0]); $attr = ($attr ? ' ' : '') .
            $attr; return $matches[1] . $attr . $matches[3]; }
    , $text);

    return $text;
}

function formatArr($arr)
{
    $t = "<?php

return [\n";

    foreach ($arr as $k => $v) {
        $t .= "    '" . $k . "' => '" . str_replace("'", "\'", $v) . "',\n";
    }

    $t .= "\n];\n";

    return $t;
}

function formatArr2($arr)
{
    $t = "<?php

return [\n";

    foreach ($arr as $k => $v) {
        $t2 = '';

        foreach ($v as $k2 => $v2) {
            $t2 .= "        '" . $k2 . "' => '" . str_replace("'", "\'", $v2) . "',\n";
        }

        $t .= "    '" . $k . "' => [\n" . $t2 . "    ],\n";
    }

    $t .= "\n];\n";

    return $t;
}

function _t($key, $text)
{
    return app()->_t($key, $text);
}

function getExecuteName($date, $type)
{
    $dt = date_create($date);

    if ($dt) {
        switch ($type) {
            case 'month':
                return getNameMonth($dt);

                break;
            case 'week':
                return getNameWeek($dt);

                break;
            case 'day':
                return getNameDayMonthFull($dt);

                break;
            case 'time':
                return getNameTime($dt);

                break;
        }
    }
}

function getExecuteFullName($date, $type)
{
    $dt = date_create($date);

    if ($dt) {
        switch ($type) {
            case 'month':
                return getFullNameMonth($dt);

                break;
            case 'week':
                return getFullNameWeek($dt);

                break;
            case 'day':
                return getFullNameDayMonth($dt);

                break;
            case 'time':
                return getFullNameTime($dt);

                break;
            case 'day-time':
                return getFullNameDayMonth($dt) . ' в ' . $dt->format('H:i');

                break;
        }
    }
}

function getNameDayWeek($dt)
{
    if ($dt) {
        switch ((int)$dt->format("N")) {
            case '1':
                return 'Пн';
                break;
            case '2':
                return 'Вт';
                break;
            case '3':
                return 'Ср';
                break;
            case '4':
                return 'Чт';
                break;
            case '5':
                return 'Пт';
                break;
            case '6':
                return 'Сб';
                break;
            case '7':
                return 'Вс';
                break;
        }
    }
}

function getFullNameWeek($dt)
{
    if ($dt) {
        $dt = clone $dt;

        if (($dt->format("Y") == (date("Y"))) && ($dt->format("W") == (date("W") - 1))) {
            return 'На предыдущей неделе';
        }
        if (($dt->format("Y") == (date("Y"))) && ($dt->format("W") == (date("W")))) {
            return 'На текущей неделе';
        }
        if (($dt->format("Y") == (date("Y"))) && ($dt->format("W") == (date("W") + 1))) {
            return 'На следующей неделе';
        }

        $dt->setISODate($dt->format("Y"), $dt->format("W"));

        $dt2 = clone $dt;

        //$dt2 = $dt2->add(new DateInterval("P1W"));
        $dt2->setTimestamp(($dt2->getTimestamp() + 7 * 24 * 60 * 60) - 1);

        if ($dt->format('n') == $dt2->format('n')) {
            return getFullNameMonth($dt) . ' с ' . $dt->format('j') . '-го по ' . $dt2->
                format('j') . '-е число';
        } else {
            return 'С ' . getFullNameDayMonth($dt) . ' по ' . getFullNameDayMonth($dt2, true);
        }

        return (getFullNameMonth($dt)) . ' ' . ((int)$dt->format("W")) . '-я неделя' . (date
            ("Y") != $dt->format("Y") ? ' ' . $dt->format("Y") . ' года' : '');
        ;
    }
}

function getNameWeek($dt)
{
    if ($dt) {
        if (($dt->format("Y") == (date("Y"))) && ($dt->format("W") == (date("W") - 1))) {
            return 'предыдущая неделя';
        }
        if (($dt->format("Y") == (date("Y"))) && ($dt->format("W") == (date("W")))) {
            return 'текущая неделя';
        }
        if (($dt->format("Y") == (date("Y"))) && ($dt->format("W") == (date("W") + 1))) {
            return 'следующая неделя';
        }

        return ((int)$dt->format("W")) . '-я неделя';
    }
}

function getFullNameTime($dt)
{
    $time = '';

    if ($dt) {
        if ($dt->format("G") >= 1 && $dt->format("G") <= 4) {
            $time = 'с 00:00 до 4:00';
        }
        if ($dt->format("G") >= 4 && $dt->format("G") <= 8) {
            $time = 'с 4:00 до 8:00';
        }
        if ($dt->format("G") >= 8 && $dt->format("G") <= 12) {
            $time = 'с 8:00 до 12:00';
        }
        if ($dt->format("G") >= 12 && $dt->format("G") <= 16) {
            $time = 'с 12:00 до 16:00';
        }
        if ($dt->format("G") >= 16 && $dt->format("G") <= 22) {
            $time = 'с 16:00 до 22:00';
        }
        if ($dt->format("G") >= 22 && $dt->format("G") <= 23) {
            $time = 'с 22:00 до 00:00';
        }
    }

    return getNameDayMonth($dt) . ' ' . $time . (date("Y") != $dt->format("Y") ? ' ' .
        $dt->format("Y") . ' года' : '');
}

function getNameTime($dt)
{
    if ($dt) {
        if ($dt->format("G") >= 1 && $dt->format("G") <= 4) {
            return 'с 00:00 до 4:00';
        }
        if ($dt->format("G") >= 4 && $dt->format("G") <= 8) {
            return 'с 4:00 до 8:00';
        }
        if ($dt->format("G") >= 8 && $dt->format("G") <= 12) {
            return 'с 8:00 до 12:00';
        }
        if ($dt->format("G") >= 12 && $dt->format("G") <= 16) {
            return 'с 12:00 до 16:00';
        }
        if ($dt->format("G") >= 16 && $dt->format("G") <= 22) {
            return 'с 16:00 до 22:00';
        }
        if ($dt->format("G") >= 22 && $dt->format("G") <= 23) {
            return 'с 22:00 до 00:00';
        }
    }
}

function getFullNameDayMonth($dt, $is = false)
{
    $months = array(
        1 => 'января',
        'февраля',
        'марта',
        'апреля',
        'мая',
        'июня',
        'июля',
        'августа',
        'сентября',
        'октября',
        'ноября',
        'лекабря');

    if ($dt) {
        if ($dt->format("Y.m.j") == (date("Y.m.") . (date("j") - 1))) {
            return 'вчера';
        }
        if ($dt->format("Y.m.j") == date("Y.m.j")) {
            return 'сегодня';
        }
        if ($dt->format("Y.m.j") == (date("Y.m.") . (date("j") + 1))) {
            return 'завтра';
        }

        return $dt->format("j") . ($is ? '-те ' : '-го ') . $months[$dt->format("n")] . (date
            ("Y") != $dt->format("Y") ? ' ' . $dt->format("Y") . ' года' : '');
    }
}

function getNameDayMonthFull($dt)
{
    $months = array(
        1 => 'января',
        'февраля',
        'марта',
        'апреля',
        'мая',
        'июня',
        'июля',
        'августа',
        'сентября',
        'октября',
        'ноября',
        'лекабря');

    if ($dt) {
        if ($dt->format("Y.m.j") == (date("Y.m.") . (date("j") - 1))) {
            return 'вчера';
        }
        if ($dt->format("Y.m.j") == date("Y.m.j")) {
            return 'сегодня';
        }
        if ($dt->format("Y.m.j") == (date("Y.m.") . (date("j") + 1))) {
            return 'завтра';
        }
        return $dt->format("j") . ' ' . $months[$dt->format("n")] . (date("Y") != $dt->
            format("Y") ? ' ' . $dt->format("Y") . ' года' : '');
    }
}

function getNameDayMonth($dt)
{
    $months = array(
        1 => 'января',
        'февраля',
        'марта',
        'апреля',
        'мая',
        'июня',
        'июля',
        'августа',
        'сентября',
        'октября',
        'ноября',
        'лекабря');

    if ($dt) {
        if ($dt->format("Y.m.j") == (date("Y.m.") . (date("j") - 1))) {
            return 'вчера';
        }
        if ($dt->format("Y.m.j") == date("Y.m.j")) {
            return 'сегодня';
        }
        if ($dt->format("Y.m.j") == (date("Y.m.") . (date("j") + 1))) {
            return 'завтра';
        }
        return $dt->format("j") . ' ' . $months[$dt->format("n")];
    }
}

function getFullNameMonth($dt)
{
    $months = array(
        1 => 'в Январе',
        'в Феврале',
        'в Марте',
        'в Апреле',
        'в Мае',
        'в Июне',
        'в Июле',
        'в Августе',
        'в Сентябре',
        'в Октябре',
        'в Ноябре',
        'в Декабре');

    if ($dt) {
        return $months[$dt->format("n")] . (date("Y") != $dt->format("Y") ? ' ' . $dt->
            format("Y") . ' года' : '');
    }
}

function getNameMonth($dt)
{
    $months = array(
        1 => 'Январь',
        'Февраль',
        'Март',
        'Апрель',
        'Май',
        'Июнь',
        'Июль',
        'Август',
        'Сентябрь',
        'Октябрь',
        'Ноябрь',
        'Декабрь');

    if ($dt) {
        return $months[$dt->format("n")];
    }
}

function prepareArgsFilter($data)
{
    $data = new \Base\Base\BaseObj($data);

    $args = [];

    if ($data->search) {
        $args['search'] = $data->search;
    }

    if ($data->sort) {
        switch ($data->sort) {
            case '0':
                break;
            case '1':
                $args['sort'] = '`projects`.`price`, `projects`.`created_at` desc';
                break;
            case '2':
                $args['sort'] = '`projects`.`price` desc, `projects`.`created_at` desc';
                break;
        }
    }

    if ($data->currency) {
        $args['currency_id'] = intval($data->currency);
    }

    if ($data->type) {
        $args['type'] = intval($data->type);
    }

    if ($data->new) {
        $args['created_at-from'] = date('Y-m-d H:i:s', time() - 3600 * 24 * 3);
    }

    if ($data->category_2) {
        $args['category_2'] = intval($data->category_2);
    }

    if ($data->price_from) {
        $args['price_from'] = ($data->price_from);
    }

    if ($data->price_to) {
        $args['price_to'] = ($data->price_to);
    }

    if ($data->category_id) {
        $category = Base::dataModel('Category', 'arrayCategoryById', ['id' => $data->
            category_id], true);

        $args['categories'][1]['nm'] = $category->nm;
        $args['categories'][1]['min_nm'] = $category->min_nm;
        $args['categories'][1]['max_nm'] = $category->max_nm;
    }

    if (count_items($data->categories)) {
        $categories = [];
        $categories_obj = [];

        foreach ($data->categories as $id) {
            $category = Base::dataModel('Category', 'arrayCategoryById', ['id' => $id]);

            if ($category) {
                $categories[] = ['nm' => $category->nm, 'min_nm' => $category->min_nm, 'max_nm' =>
                    $category->max_nm, ];
            }
        }

        if (!empty($categories)) {
            foreach ($categories as $category) {
                $args['categories'][] = $category;
            }
        }
    }

    if ($data->country_id) {
        $args['country_id'] = $data->country_id;
    }

    if ($data->region_id) {
        $args['region_id'] = $data->region_id;
    }

    if ($data->area_id) {
        $args['area_id'] = $data->area_id;
    }

    if ($data->city_id) {
        $args['city_id'] = $data->city_id;
    }

    if ($data->address_id) {
        $args['address_id'] = $data->address_id;
    }

    return $args;
}

function prepareDescriptionInterkassa($description)
{
    //return 'Payment Service';

    return iconv('utf-8', 'utf-8', str_limit(trim(str_replace(["\r", "\n"], '', $description)),
        100));
}

function getInterkassaSign($dataSet, $is_test = false)
{
    if (isset($dataSet['ik_sign'])) {
        unset($dataSet['ik_sign']); //Delete string with signature from dataset
    }
    foreach ($dataSet as $key => $value) {
        if (stripos($key, 'ik_') === 0) {
        } else {
            unset($dataSet[$key]);
        }
    }
    ksort($dataSet, SORT_STRING); // Sort elements in array by var names in alphabet queue
    array_push($dataSet, $is_test ? config('InterkassaTestKey') : config('InterkassaSecretKey')); // Adding secret key at the end of the string
    $signString = implode(':', $dataSet); // Concatenation calues using symbol ":"
    //$sign = base64_encode(md5($signString, true)); // Get MD5 hash as binare view using generate string and code it in BASE64
    $sign = base64_encode(hash('sha256', $signString, true)); // Get sha256 hash as binare view using generate string and code it in BASE64

    return $sign; // Return the result
}

function isGooglePushIsSubscribe()
{
    if (isset($_SESSION['routes']) && count($_SESSION['routes']) >= 10) {
        return true;
    }

    return false;
}

function initTwitterOauthSession()
{
    if (isset($_SESSION['AppTwitter'])) {
        if (isset($_SESSION['AppTwitter']['OAuth'])) {
            config('AppTwitterOAuthToken', $_SESSION['AppTwitter']['OAuth']['Token']);
            config('AppTwitterOAuthSecretToken', $_SESSION['AppTwitter']['OAuth']['SecretToken']);
        }
    }
}

function initTwitterOauthToken()
{
    $oauth_nonce = md5(uniqid(rand(), true));
    $oauth_timestamp = time();

    $params = array(
        'oauth_callback=' . urlencode(route('auth.twitter.redirect', [], null, false, true, true)) .
            URL_SEPARATOR,
        'oauth_consumer_key=' . config('AppTwitterKey') . URL_SEPARATOR,
        'oauth_nonce=' . $oauth_nonce . URL_SEPARATOR,
        'oauth_signature_method=HMAC-SHA1' . URL_SEPARATOR,
        'oauth_timestamp=' . $oauth_timestamp . URL_SEPARATOR,
        'oauth_version=1.0');

    $oauth_base_text = implode('', array_map('urlencode', $params));
    $key = config('AppTwitterSecretKey') . URL_SEPARATOR;
    $oauth_base_text = 'GET' . URL_SEPARATOR . urlencode(config('AppTwitterTokenUrl')) .
        URL_SEPARATOR . $oauth_base_text;
    $oauth_signature = base64_encode(hash_hmac('sha1', $oauth_base_text, $key, true));

    // получаем токен запроса
    $params = array(
        URL_SEPARATOR . 'oauth_consumer_key=' . config('AppTwitterKey'),
        'oauth_nonce=' . $oauth_nonce,
        'oauth_signature=' . urlencode($oauth_signature),
        'oauth_signature_method=HMAC-SHA1',
        'oauth_timestamp=' . $oauth_timestamp,
        'oauth_version=1.0');

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, config('AppTwitterTokenUrl') .
        '?oauth_callback=' . urlencode(route('auth.twitter.redirect', [], null, false, true, true)) .
        implode('&', $params));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($curl);
    curl_close($curl);

    parse_str($response, $response);

    $oauth_token = $response['oauth_token'];
    $oauth_token_secret = $response['oauth_token_secret'];

    config('AppTwitterOAuthToken', $oauth_token);
    config('AppTwitterOAuthSecretToken', $oauth_token_secret);

    $_SESSION['AppTwitter']['OAuth']['Token'] = $oauth_token;
    $_SESSION['AppTwitter']['OAuth']['SecretToken'] = $oauth_token_secret;
}

function getOauthLinkTwitter()
{
    initTwitterOauthToken();

    return config('AppTwitterAuthUrl') . '?oauth_token=' . config('AppTwitterOAuthToken');
}

function eachTableInBd($class, $callback, $connect = null, $is_model = true)
{
    if ($is_model) {
        if (preg_match("#^[A-Z]#", $class)) {
            $class = 'App\Models\\' . $class;
            $func = $class . '::' . 'table';

            $table = (\Base::isPhp7() ? $func() : $class::table());
        } else {
            $table = $class;
            $class = '\Base\Base\BaseObj';
        }

        $offset = 0;
        $limit = 50;
        $i = 0;
        while ($rows = \Base\Base\DB::GetAll("SELECT * FROM `" . ($table) . "` LIMIT " .
            $offset . ", " . $limit, null, PDO::FETCH_ASSOC, $connect)) {
            $offset += $limit;

            foreach ($rows as $row) {
                $callback(new $class($row));
            }

            if (count($rows) != $limit)
                break;
        }
    } else {
        $table = $class;
        $class = '\Base\Base\BaseObj';

        $offset = 0;
        $limit = 50;
        $i = 0;
        while ($rows = \Base\Base\DB::GetAll("SELECT * FROM `" . ($table) . "` LIMIT " .
            $offset . ", " . $limit, null, PDO::FETCH_ASSOC, $connect)) {
            $offset += $limit;

            foreach ($rows as $row) {
                $callback(new $class($row));
            }

            if (count($rows) != $limit)
                break;
        }
    }
}

function plur($num, $type1, $type2, $type3)
{
    $num_end = $num % 10; // Последняя цифра
    if ($num >= 11 && $num <= 14)
        return $num . ' ' . $type2; // Дней, часов, минут, секунд
    if ($num_end >= 2 && $num_end <= 4)
        return $num . ' ' . $type3; // Дня, часа, минуты, секунды
    if ($num == 1 || $num_end == 1)
        return $num . ' ' . $type1; // День, час, минута, секунда
    if ($num >= 2 && $num <= 4)
        return $num . ' ' . $type3; // Дня, часа, минуты, секунды
    return $num . ' ' . $type2; // Дней, часов, минут, секунд
}

function isMobile(&$mobilename = '')
{
    $mobile_agent_array = array(
        'ipad',
        'iphone',
        'android',
        'pocket',
        'palm',
        'windows ce',
        'windowsce',
        'cellphone',
        'opera mobi',
        'ipod',
        'small',
        'sharp',
        'sonyericsson',
        'symbian',
        'opera mini',
        'nokia',
        'htc_',
        'samsung',
        'motorola',
        'smartphone',
        'blackberry',
        'playstation portable',
        'tablet browser');
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    // var_dump($agent);exit;
    foreach ($mobile_agent_array as $value) {
        if (strpos($agent, $value) !== false) {
            $mobilename = $value;
            return true;
        }
    }
    return false;
}

function isBot(&$botname = '')
{
    /* Эта функция будет проверять, является ли посетитель роботом поисковой системы */
    $bots = array(
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
        'yandex',
        'yandexSomething',
        'Copyscape.com',
        'AdsBot-Google',
        'domaintools.com',
        'Nigma.ru',
        'bing.com',
        'dotnetdotcom',

        // Yandex
        'YandexBot',
        'YandexAccessibilityBot',
        'YandexMobileBot',
        'YandexDirectDyn',
        'YandexScreenshotBot',
        'YandexImages',
        'YandexVideo',
        'YandexVideoParser',
        'YandexMedia',
        'YandexBlogs',
        'YandexFavicons',
        'YandexWebmaster',
        'YandexPagechecker',
        'YandexImageResizer',
        'YandexAdNet',
        'YandexDirect',
        'YaDirectFetcher',
        'YandexCalendar',
        'YandexSitelinks',
        'YandexMetrika',
        'YandexNews',
        'YandexNewslinks',
        'YandexCatalog',
        'YandexAntivirus',
        'YandexMarket',
        'YandexVertis',
        'YandexForDomain',
        'YandexSpravBot',
        'YandexSearchShop',
        'YandexMedianaBot',
        'YandexOntoDB',
        'YandexOntoDBAPI',
        'YandexTurbo',
        'YandexVerticals',

        // Google
        'Googlebot',
        'Googlebot-Image',
        'Mediapartners-Google',
        'AdsBot-Google',
        'APIs-Google',
        'AdsBot-Google-Mobile',
        'AdsBot-Google-Mobile',
        'Googlebot-News',
        'Googlebot-Video',
        'AdsBot-Google-Mobile-Apps',

        // Other
        'Mail.RU_Bot',
        'bingbot',
        'Accoona',
        'ia_archiver',
        'Ask Jeeves',
        'OmniExplorer_Bot',
        'W3C_Validator',
        'WebAlta',
        'YahooFeedSeeker',
        'Yahoo!',
        'Ezooms',
        'Tourlentabot',
        'MJ12bot',
        'AhrefsBot',
        'SearchBot',
        'SiteStatus',
        'Nigma.ru',
        'Baiduspider',
        'Statsbot',
        'SISTRIX',
        'AcoonBot',
        'findlinks',
        'proximic',
        'OpenindexSpider',
        'statdom.ru',
        'Exabot',
        'Spider',
        'SeznamBot',
        'oBot',
        'C-T bot',
        'Updownerbot',
        'Snoopy',
        'heritrix',
        'Yeti',
        'DomainVader',
        'DCPbot',
        'PaperLiBot',
        'StackRambler',
        'msnbot',
        'msnbot-media',
        'msnbot-news',
        );

    $USER_AGENT = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] :
        '';

    if (!$USER_AGENT) {
        return true;
    }

    foreach ($bots as $bot)
        if (stripos($USER_AGENT, $bot) !== false) {
            $botname = $bot;
            return true;
        }
    return false;
}
