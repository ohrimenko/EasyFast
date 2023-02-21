<?php

use \Base\Base\View;
use \Base\Base\Route;
use \Base\Base\Request;
use \App\Components\Show;
use \Base\Base\BaseObj;

function strfloatval($val)
{
    if (is_numeric($val)) {
        return str_replace('.', ',', $val);
    }

    return $val;
}

function parsefloatstrval($val)
{
    $val = preg_replace("#[^\d.]#", '', $val);

    $val = preg_replace('#\.{2,}#', '.', $val);
    
    $val = floatval($val);
    
    return ($val);
}

function floatstrval($val)
{
    return floatval(str_replace(',', '.', $val));
}

function Zip($source, $destination)
{
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }
    
    if (file_exists($destination)) {
        unlink($destination);
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }

    $sourcepath = str_replace('\\', '/', realpath($source));

    if (is_dir($sourcepath) === true) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourcepath),
            RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {
            $file = str_replace('\\', '/', $file);

            // Ignore "." and ".." folders
            if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
                continue;

            $filepath = realpath($file);

            if (is_dir($filepath) === true) {
                $zip->addEmptyDir(ltrim(str_replace($sourcepath, '', $file), "/\\"));
            } elseif (is_file($filepath) === true) {
                $zip->addFromString(ltrim(str_replace($sourcepath, '', $file), "/\\"), file_get_contents($filepath));
            }
        }
    } elseif (is_file($sourcepath) === true) {
        $zip->addFromString(basename($sourcepath), file_get_contents($sourcepath));
    }

    return $zip->close();
}

function header_sent($header)
{
    if (isset($GLOBALS['start-php-http-server'])) {
        $GLOBALS['headers-php-http-server'][] = $header;
        //header($header, true);
    } else {
        header($header, true);
    }
}

function set_http_response_code($code)
{
    if (isset($GLOBALS['start-php-http-server'])) {
        $GLOBALS['http_response_code-php-http-server'] = $code;

        //http_response_code($code);
    } else {
        http_response_code($code);
    }
}

function requestallheaders()
{
    $headers = [];
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name,
                5)))))] = $value;
        }
    }
    return $headers;
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '" />';
}

function csrf_token()
{
    return str_rand(20);
}

function toObjects($items)
{
    $objects = new \Base\Base\BaseObj;

    $i = 0;

    foreach ($items as $item) {
        $objects->{++$i} = $item;
    }

    return $objects;
}

function toArray($items)
{
    $array = [];

    foreach ($items as $item) {
        $array[] = $item;
    }

    return $array;
}

function prepareSaveUploadImage($imagepath, $width = null, $height = null, $ratio = null,
    $maxwidth = null, $maxheight = null, $minwidth = null, $minheight = null)
{
    list($nowwidth, $nowheight) = getimagesize($imagepath);

    $pthinfo = pathinfo($imagepath);

    if (!$ratio && $width && $height) {
        $ratio = $width / $height;
    } elseif (!$ratio) {
        $ratio = $nowwidth / $nowheight;
    }

    $image = imageCreateFromAny($imagepath);

    $imagewidth = $nowwidth;

    $imageheight = floor($imagewidth / $ratio);

    if ($imageheight > $nowheight) {
        $imageheight = $nowheight;

        $imagewidth = floor($imageheight * $ratio);
    }

    if ($imagewidth > $nowwidth) {
        $imagewidth = $nowwidth;

        $ratio = $imagewidth / $imageheight;
    }

    $imageleft = 0;
    $imagetop = 0;

    $imageleft = floor(($nowwidth - $imagewidth) / 2);
    $imagetop = floor(($nowheight - $imageheight) / 2);

    if ($width && $height) {
        $newwidth = $width;
        $newheight = $height;
    } else {
        $newwidth = $imagewidth;
        $newheight = floor($newwidth / $ratio);
    }

    if ($minwidth && $newwidth < $minwidth) {
        $newwidth = $minwidth;
        $newheight = floor($newwidth / $ratio);
    }

    if ($minheight && $newheight < $minheight) {
        $newheight = $minheight;
        $newwidth = floor($newheight * $ratio);
    }

    if ($maxwidth && $newwidth > $maxwidth) {
        $newwidth = $maxwidth;
        $newheight = floor($newwidth / $ratio);
    }

    if ($maxheight && $newheight > $maxheight) {
        $newheight = $maxheight;
        $newwidth = floor($newheight * $ratio);
    }

    if ($image) {
        $image_p = imagecreatetruecolor($newwidth, $newheight);

        imagepalettetotruecolor($image_p);
        imagealphablending($image_p, true);
        imagesavealpha($image_p, true);
        $trans_colour = imagecolorallocatealpha($image_p, 0, 0, 0, 127);
        imagefill($image_p, 0, 0, $trans_colour);

        imagecopyresampled($image_p, $image, 0, 0, $imageleft, $imagetop, $newwidth, $newheight,
            $imagewidth, $imageheight);

        switch ($pthinfo['extension']) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($image_p, $imagepath, 100);

                break;
            case 'gif':
                imagegif($image_p, $imagepath);

                break;
            case 'png':
                imagepng($image_p, $imagepath, 9);

                break;
            case 'bmp':
                imagewbmp($image_p, $imagepath);

                break;
            case 'webp':
                imagewebp($image_p, $imagepath, 100);

                break;
            default:
                imagejpeg($image_p, $imagepath, 100);

                break;
        }

        imagedestroy($image_p);
    }
}

function prepareUploadImage($imagepath, $arealeft, $areatop, $areawidth, $areaheight)
{
    list($nowwidth, $nowheight) = getimagesize($imagepath);

    $ratio = $nowwidth / $nowheight;

    $pthinfo = pathinfo($imagepath);

    $newleft = floor($nowwidth * ($arealeft / 100));
    $newtop = floor($nowheight * ($areatop / 100));

    $newwidth = floor($nowwidth * ($areawidth / 100));
    $newheight = floor($nowheight * ($areaheight / 100));

    if (($newleft + $newwidth) > $nowwidth) {
        $newleft = $nowwidth - $newwidth;
    }
    if (($newtop + $newheight) > $nowheight) {
        $newtop = $nowheight - $newheight;
    }

    $ratio = $nowwidth / $nowheight;

    $imagewidth = $newwidth;

    $imageheight = $newheight;

    $image = imageCreateFromAny($imagepath);

    if ($image) {
        $image_p = imagecreatetruecolor($newwidth, $newheight);

        imagepalettetotruecolor($image_p);
        imagealphablending($image_p, true);
        imagesavealpha($image_p, true);
        $trans_colour = imagecolorallocatealpha($image_p, 0, 0, 0, 127);
        imagefill($image_p, 0, 0, $trans_colour);

        imagecopyresampled($image_p, $image, 0, 0, $newleft, $newtop, $newwidth, $newheight,
            $imagewidth, $imageheight);

        switch ($pthinfo['extension']) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($image_p, $imagepath, 100);

                break;
            case 'gif':
                imagegif($image_p, $imagepath);

                break;
            case 'png':
                imagepng($image_p, $imagepath, 9);

                break;
            case 'bmp':
                imagewbmp($image_p, $imagepath);

                break;
            case 'webp':
                imagewebp($image_p, $imagepath, 100);

                break;
            default:
                imagejpeg($image_p, $imagepath, 100);

                break;
        }

        imagedestroy($image_p);
    }
}

function getVarsSetting($key)
{
    $response = new BaseObj;

    $setting = \Base::Setting($key);

    if ($setting) {
        foreach (explode(';', $setting) as $var) {
            $var = explode(':', $var);
            if (count($var) == 2) {
                $var[0] = trim($var[0]);
                $var[1] = trim($var[1]);

                if ($var[0] && $var[1]) {
                    if ($var[0] == 'extensions') {
                        $response->{$var[0]} = array_map(function ($item)
                        {
                            return trim($item); }
                        , explode('|', $var[1]));
                    } else {
                        $response->{$var[0]} = $var[1];
                    }
                }
            }
        }
    }

    return $response;
}

function base64_encode_imagepng_to_webp($dataimg, $max_width = 64, $max_height =
    64)
{
    list($width, $height) = getimagesize($dataimg);

    $dst_x = 0;
    $dst_y = 0;
    $src_x = 0;
    $src_y = 0;

    $dst_w = $width;
    $dst_h = $height;
    $src_w = $width;
    $src_h = $height;

    if ($dst_w > $max_width) {
        $percent = $max_width / $dst_w;

        $dst_w = round($dst_w * $percent);
        $dst_h = round($dst_h * $percent);
    }

    if ($dst_h > $max_height) {
        $percent = $max_height / $dst_h;

        $dst_w = round($dst_w * $percent);
        $dst_h = round($dst_h * $percent);
    }

    $image = imageCreateFromPng($dataimg);

    if ($image) {
        $image_p = imagecreatetruecolor($dst_w, $dst_h);

        imagepalettetotruecolor($image_p);
        imagealphablending($image_p, true);
        imagesavealpha($image_p, true);

        $trans_colour = imagecolorallocatealpha($image_p, 0, 0, 0, 127);
        imagefill($image_p, 0, 0, $trans_colour);

        imagecopyresampled($image_p, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h,
            $src_w, $src_h);

        imagewebp($image_p, config('storage_dir') . '/tmp/tmpimagewebp', 100);

        imagedestroy($image_p);

        $webpbase64 = base64_encode_image(config('storage_dir') . '/tmp/tmpimagewebp',
            'webp');

        unlink(config('storage_dir') . '/tmp/tmpimagewebp');

        return $webpbase64;
    }
}

function base64_encode_image($filename, $filetype)
{
    if ($filename) {
        $imgbinary = fread(fopen($filename, "r"), filesize($filename));
        return 'data:image/' . $filetype . ';base64,' . base64_encode($imgbinary);
    }
}

function listen($event, $handle)
{
    return app()->listen($event, $handle);
}

function event($event, $var = null, $handle = null)
{
    return app()->event($event, $var, $handle);
}

function isSuccesSetAuthUserTokenHost($host)
{
    if (!isset($GLOBALS['dataSuccesSetAuthUserTokenHost'][$host])) {
        $GLOBALS['dataSuccesSetAuthUserTokenHost'][$host] = false;

        if (gethostbyname($host) == $_SERVER['SERVER_ADDR']) {
            $GLOBALS['dataSuccesSetAuthUserTokenHost'][$host] = true;
        }
    }

    return $GLOBALS['dataSuccesSetAuthUserTokenHost'][$host];
}

function setAuthUserToken($url)
{
    $parsed_url = parse_url($url);

    if (isset($parsed_url['host']) && isSuccesSetAuthUserTokenHost($parsed_url['host'])) {
        $args = [];

        if (isset($parsed_url['query'])) {
            parse_str($parsed_url['query'], $args);
        }

        if (isset($args['cookie_token_request'])) {
            unset($args['cookie_token_request']);
        }

        if (isset($args['auth_token_request'])) {
            unset($args['auth_token_request']);
        }

        if (isset($args['auth_token'])) {
            unset($args['auth_token']);
        }

        if (isset($_SESSION['user']['auth_token'])) {
            $args['auth_token'] = $_SESSION['user']['auth_token'];
        } elseif (data()->user && data()->user->auth_token) {
            $args['auth_token'] = data()->user->auth_token;
        }

        if (!empty($args)) {
            $parsed_url['query'] = http_build_query($args);
        }
    }

    return unparse_url($parsed_url);
}

function unparse_url($parsed_url)
{
    $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
    $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
    $pass = ($user || $pass) ? $pass . "@" : '';
    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
    return $scheme . $user . $pass . $host . $port . $path . $query . $fragment;
}

function mb_parse_url($url)
{
    $enc_url = preg_replace_callback('%[^:/@?&=#]+%usD', function ($matches)
    {
        return urlencode($matches[0]); }
    , $url);

    $parts = parse_url($enc_url);

    if ($parts === false) {
        throw new \InvalidArgumentException('Malformed URL: ' . $url);
    }

    foreach ($parts as $name => $value) {
        $parts[$name] = urldecode($value);
    }

    return $parts;
}

function baseObj($arr = null)
{
    return new \Base\Base\BaseObj($arr);
}

function isAssoc(array $arr)
{
    $i = 0;

    foreach ($arr as $key => $value) {
        if ($i . '' != $key) {
            return true;
        }

        $i++;
    }

    return false;
}

function widget($widget, array $params = [], $is_view = true)
{
    return Show::widget($widget, $params, $is_view);
}

function widget_db($widget, array $params = [], $is_view = true)
{
    return Show::widgetDb($widget, $params, $is_view);
}

function cache($key)
{
    $args = func_get_args();

    $cargs = count($args);

    if ($cargs == 1) {
        return app()->cache->get($args[0]);
    } elseif ($cargs >= 2) {
        if (is_null($args[1])) {
            app()->cache->delete($args[0]);
        } else {
            app()->cache->set($args[0], $args[1], isset($args[2]) ? $args[2] : 60);
        }
    }
}

function imageCreateFromAny($filepath)
{
    $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()
    $allowedTypes = array(
        1, // [] gif
        2, // [] jpg
        3, // [] png
        6 // [] bmp
            );
    if (!in_array($type, $allowedTypes)) {
        return false;
    }
    switch ($type) {
        case 1:
            $im = imageCreateFromGif($filepath);
            break;
        case 2:
            $im = imageCreateFromJpeg($filepath);
            break;
        case 3:
            $im = imageCreateFromPng($filepath);
            break;
        case 6:
            $im = imageCreateFromBmp($filepath);
            break;
        case 15:
            $im = imagecreateFromWebP($filepath);
            break;
    }
    return $im;
}

function dataModel($model, $data, $params = [], $emptyFail = false)
{
    return Base::dataModel($model, $data, $params, $emptyFail);
}

function syncModel($m1, $m2, $name, $key1, $key2)
{
    return Base::app()->components()->syncModel($m1, $m2, $name, $key1, $key2);
}

function syncModels($m1, $m2, $name, $key1, $key2)
{
    return Base::app()->components()->syncModels($m1, $m2, $name, $key1, $key2);
}

function syncChildModels($m1, $m2, $name1, $name2, $key1, $key2)
{
    return Base::app()->components()->syncChildModels($m1, $m2, $name1, $name2, $key1,
        $key2);
}

function getPhotoUser()
{
    if (data()->user) {
        return data()->user->getPhoto();
    } else {
        return asset('img/no-img.png');
    }
}

function fast_request($url)
{
    $parts = parse_url($url);

    print_r($parts);

    $fp = @fsockopen('ssl://' . $parts['host'], 443, $errno, $errstr, 1);
    if ($fp) {
        stream_set_timeout($fp, 1);
        $out = "GET " . $parts['path'] . " HTTP/1.0\r\n";
        $out .= "Host: " . $parts['host'] . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        if (fwrite($fp, $out)) {
            fgets($fp, 128);
            fclose($fp);
        }
    }
}

function isGooglePushIsTokenSentToServer()
{
    if (data()->user) {
        return data()->user->isGooglePushIsTokenSentToServer();
    } else {
        if (!isset($_SESSION['GooglePushToken'])) {
            return true;
        }
    }
}

function components()
{
    return app()->components();
}

function data()
{
    return \Base::data();
}

function isAuth()
{
    if (data()->user) {
        return true;
    }

    return false;
}

function isAdmin()
{
    return \Base::isAdmin();
}

function config($key, $value = null)
{
    if (is_null($value)) {
        return Base::app()->config($key);
    } else {
        return Base::app()->config($key, $value);
    }
}

function in_values($value, $values)
{
    foreach ($values as $k => $v) {
        if ($value == $v) {
            return true;
        }
    }

    return false;
}

function array_in_array(array $ar1, array $ar2)
{
    foreach ($ar1 as $key => $value) {
        if (!in_array($value, $ar2)) {
            return false;
        }
    }

    return true;
}

function attrsRouteNow(array $args = [], $item = null, $is_attrs_route = true, $is_prepare_domain = false,
    $is_prepare_as = false)
{
    $as = Route::nowRout();
    $args = array_merge(Route::nowParametersRout(), $args);

    return attrs_route($as, $args, $item, $is_attrs_route, $is_prepare_domain, $is_prepare_as);
}

function routeNow(array $args = [], $delete_params = [], $is_prepare_domain = false,
    $is_prepare_as = true)
{
    return Route::now($args, $delete_params, $is_prepare_domain, $is_prepare_as);
}

function nowRoutValue()
{
    $values = [];

    foreach (request()->vars as $key => $value) {
        if ($key != 'trans') {
            $values[$key] = $key . '=' . $value;
        }
    }

    sort($values);

    return implode('&', $values);
}

function nowRout()
{
    return Route::nowRout();
}

function count_items($items)
{
    if (is_array($items)) {
        return count($items);
    } elseif ($items instanceof \Base\Record\Collection || $items instanceof \Iterator) {
        return $items->count();
    } else {
        return null;
    }
}

function prepareArgsRoute($as, &$args, $item)
{
    if ($item) {
        if (is_object($item)) {
            if (isset($item->indexing)) {
                $args['indexing'] = $item->indexing;
            }
        }
        if (is_array($item)) {
            if (isset($item['indexing'])) {
                $args['indexing'] = $item['indexing'];
            }
        }
    }

    return;
}

function attrs_route($as, $args = [], $item = null, $is_attrs_route = true, $is_prepare_domain = false,
    $is_prepare_as = false)
{
    $attrs = [];

    prepareArgsRoute($as, $args, $item);

    app()->components()->ctrlRoutAttr($as, $args, $attrs, $item);

    if (!isset($attrs['href'])) {
        $attrs['href'] = route($as, $args, $item, $is_attrs_route);

        $href_load = route($as, $args, $item, $is_attrs_route, $is_prepare_domain, $is_prepare_as);

        if ($href_load != $attrs['href']) {
            $attrs['data-href-ajax'] = $href_load;
        }
    }

    if (!data()->is_ajax && !app()->components()->isRoutIndexing($as, $args) &&
        isset($attrs['rel']) && $attrs['rel'] == 'nofollow') {
        $attrs['data-href'] = $attrs['href'];
        $attrs['href'] = '#';
        $attrs['data-rel-nofollow'] = '1';
        unset($attrs['rel']);
    }

    $res = [];

    if ($as == 'projects.country.category') {
        //print_r($attrs);
        //exit;
    }

    foreach ($attrs as $attr_key => $attr_value) {
        // if ($attr_key == 'rel')
        //     exit;
        $res[] = $attr_key .= '="' . $attr_value . '"';
    }

    return ' ' . implode(' ', $res);
}

function prepareRequestData($data)
{
    $data = prepareData($data);

    //unset($data['action_admin']);

    return $data;
}

function prepareData($data)
{
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = prepareData($value);
        }
    } elseif ($data) {
        $data = $data;
        //$data = strip_tags(htmlentities($data));
    }

    return $data;
}

function is_img($filename)
{
    return in_array(pathinfo($filename, PATHINFO_EXTENSION), ['jpg', 'png', 'jpeg',
        'ico']) ? true : false;
}

function view($view, $data = [], $is_view = false)
{
    if ($is_view) {
        (new View($view, $data))->render(true);
    } else {
        return (new View($view, $data))->render(false);
    }
}

function unsetOldRequestKeyValueForm()
{
    unset($GLOBALS['oldRequestKeyForm']);
    unset($GLOBALS['oldRequestValueForm']);
}

function setOldRequestKeyForm($key)
{
    $GLOBALS['oldRequestKeyForm'] = $key;
}

function setOldRequestValueForm($value)
{
    $GLOBALS['oldRequestValueForm'] = $value;
}

function isAccesOldRequest()
{
    if (isset($GLOBALS['oldRequestKeyForm'])) {
        if (isset($GLOBALS['oldRequestValueForm'])) {
            if (isset(Request::req()->old_request->{$GLOBALS['oldRequestKeyForm']})) {
                if (Request::req()->old_request->{$GLOBALS['oldRequestKeyForm']} != $GLOBALS['oldRequestValueForm']) {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    return true;
}

function old($key, $default = null)
{
    return value_get(request()->old_request, $key, $default);
}

function app()
{
    return \Base::app();
}

function request($keys = null, $default = null)
{
    return value_get(Request::req(), $keys, $default);

    return Request::req();
}

function route($as, $args = [], $item = null, $is_attrs_route = false, $is_prepare_domain = true,
    $is_prepare_as = true)
{
    if (!(is_object($item) || is_array($item))) {
        $item = null;
    }

    prepareArgsRoute($as, $args, $item);

    if (!$is_attrs_route) {
        app()->components()->ctrlRoutAttr($as, $args, $attrs, []);
    }

    if (array_key_exists('indexing', $args)) {
        unset($args['indexing']);
    }

    return Route::route($as, $args, $item, $is_prepare_domain, $is_prepare_as);
}

function key_token()
{
    return str_rand();
}

function asset($url, $base = true)
{
    return (!Request::req()->server->HTTPS || Request::req()->server->HTTPS == "off" ?
        'http:' : 'https:') . '//' . Request::req()->server->SERVER_NAME . '/' . ltrim($url,
        '/');
}

function caheParam()
{
    //return 't=9456842356';
    return 'time=' . time();
}

function redirect_301($as, $args = [], $item = null)
{
    if (!headers_sent())
        header_sent("HTTP/1.1 301 Moved Permanently");
    if (!headers_sent())
        header_sent("Location: " . route($as, $args, $item));

    if (!isset($GLOBALS['start-php-http-server'])) {
        exit;
    }
}

function redirectBack($code = 200)
{
    $route = Route::lastGetRoute();

    if ($route['as'] == 'auth.logout') {
        redirect('main.index');
    } else {
        redirect($route['as'], $route['data'], $code);
    }
}

function redirect($as, $args = [], $code = 200)
{
    redirect_url(route($as, $args, null, false, false), $code);
}

function redirect_url($url, $code = 200)
{
    set_http_response_code($code);

    if (!headers_sent())
        header_sent("Location: " . $url);

    if (!isset($GLOBALS['start-php-http-server'])) {
        exit;
    }
}

function abort($code = 404)
{
    if (!Route::isNowRout() || Route::nowRout() != 'main.error_page') {
        return redirect('main.error_page', [], 301);
    }

    set_http_response_code($code);

    renderError($code);
}

function abortAdminAuth($code = 404)
{
    set_http_response_code($code);

    redirect('admin.form');

    if (!isset($GLOBALS['start-php-http-server'])) {
        exit;
    }
}

function abortAuth($code = 404)
{
    set_http_response_code($code);

    $view = new View('auth/auth');

    $view->render();

    if (!isset($GLOBALS['start-php-http-server'])) {
        exit;
    }
}

function renderError($code, $data = [])
{
    set_http_response_code($code);

    $view = new View('errors/' . $code, $data);

    $view->render();

    if (!isset($GLOBALS['start-php-http-server'])) {
        exit;
    }
}

function object_get($object, $keys, $default = null)
{
    if (is_null($keys)) {
        return $object;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    foreach ($keys as $segment) {
        if (isset($object->{$segment})) {
            $object = $object->{$segment};
        } else {
            return $default;
        }
    }

    return $object;
}

function array_get($array, $keys, $default = null)
{
    if (is_null($keys)) {
        return $array;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    foreach ($keys as $segment) {
        if (isset($array[$segment])) {
            $array = $array[$segment];
        } else {
            return $default;
        }
    }

    return $array;
}

function value_get($value, $keys, $default = null)
{
    if (is_null($keys)) {
        return $value;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    foreach ($keys as $segment) {
        if ($pos = stripos($segment, '(')) {
            $method = substr($segment, 0, $pos);
            $args = array_map(function ($item)
            {
                return trim($item); }
            , explode(',', trim(rtrim(substr($segment, $pos + 1), ')'))));

            /*
            print_r($value);echo "\n";
            print_r($segment);echo "\n";
            print_r($method);echo "\n";
            print_r($args);echo "\n";
            
            exit();
            */

            if (is_object($value)) {
                switch (count($args)) {
                    case 1:
                        $value = $value->{$method}($args[0]);
                        break;
                    case 2:
                        $value = $value->{$method}($args[0], $args[1]);
                        break;
                    case 3:
                        $value = $value->{$method}($args[0], $args[1], $args[2]);
                        break;
                    case 4:
                        $value = $value->{$method}($args[0], $args[1], $args[2], $args[3]);
                        break;
                    case 5:
                        $value = $value->{$method}($args[0], $args[1], $args[2], $args[3], $args[4]);
                        break;
                    case 6:
                        $value = $value->{$method}($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
                        break;
                    case 7:
                        $value = $value->{$method}($args[0], $args[1], $args[2], $args[3], $args[4], $args[5],
                            $args[6]);
                        break;
                    case 8:
                        $value = $value->{$method}($args[0], $args[1], $args[2], $args[3], $args[4], $args[5],
                            $args[6], $args[7]);
                        break;
                    case 9:
                        $value = $value->{$method}($args[0], $args[1], $args[2], $args[3], $args[4], $args[5],
                            $args[6], $args[7], $args[8]);
                        break;
                    case 10:
                        $value = $value->{$method}($args[0], $args[1], $args[2], $args[3], $args[4], $args[5],
                            $args[6], $args[7], $args[8], $args[9]);
                        break;
                    default:
                        $value = $value->{$method}();
                        break;
                }
            } elseif (is_array($value)) {
                switch (count($args)) {
                    case 1:
                        $value = $value[$method]($args[0]);
                        break;
                    case 2:
                        $value = $value[$method]($args[0], $args[1]);
                        break;
                    case 3:
                        $value = $value[$method]($args[0], $args[1], $args[2]);
                        break;
                    case 4:
                        $value = $value[$method]($args[0], $args[1], $args[2], $args[3]);
                        break;
                    case 5:
                        $value = $value[$method]($args[0], $args[1], $args[2], $args[3], $args[4]);
                        break;
                    case 6:
                        $value = $value[$method]($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
                        break;
                    case 7:
                        $value = $value[$method]($args[0], $args[1], $args[2], $args[3], $args[4], $args[5],
                            $args[6]);
                        break;
                    case 8:
                        $value = $value[$method]($args[0], $args[1], $args[2], $args[3], $args[4], $args[5],
                            $args[6], $args[7]);
                        break;
                    case 9:
                        $value = $value[$method]($args[0], $args[1], $args[2], $args[3], $args[4], $args[5],
                            $args[6], $args[7], $args[8]);
                        break;
                    case 10:
                        $value = $value[$method]($args[0], $args[1], $args[2], $args[3], $args[4], $args[5],
                            $args[6], $args[7], $args[8], $args[9]);
                        break;
                    default:
                        $value = $value[$method]();
                        break;
                }
            } else {
                return $default;
            }
        } else {
            if (is_object($value) && isset($value->{$segment})) {
                $value = $value->{$segment};
            } elseif (is_array($value) && isset($value[$segment])) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
    }

    return $value;
}

function value_set(&$value, $keys, $var, $recurs = true)
{
    if (is_null($keys)) {
        return $value = $var;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    while ($count = count($keys) > 1) {
        $key = array_shift($keys);

        if (is_object($value)) {
            if ($recurs) {
                if (!isset($value->{$key})) {
                    $value->{$key} = new \Base\Base\BaseObj;
                }

                if (!is_object($value->{$key})) {
                    $value->{$key} = new \Base\Base\BaseObj([$value->{$key}]);
                }
            } else {
                if (!isset($value->{$key}) || !is_object($value->{$key})) {
                    return false;
                }
            }

            $value = $value->{$key};
        } elseif (is_array($value)) {
            if ($recurs) {
                if (!isset($value[$key])) {
                    $value[$key] = [];
                }

                if (!is_array($value[$key])) {
                    $value[$key] = [$value[$key]];
                }
            } else {
                if (!isset($value[$key]) || !is_array($value[$key])) {
                    return false;
                }
            }

            $value = &$value[$key];
        } else {
            return false;
        }
    }

    if (!is_object($value) && !is_array($value)) {
        return false;
    }

    $key = array_shift($keys);

    if (is_object($value)) {
        $value->{$key} = $var;
    } elseif (is_array($value)) {
        $array[$key] = $value;
    }

    return true;
}

function object_set(&$object, $keys, $value, $recurs = true)
{
    if (is_null($keys)) {
        return $object = $value;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    while ($count = count($keys) > 1) {
        $key = array_shift($keys);

        if ($recurs) {
            if (!isset($object->{$key})) {
                $object->{$key} = new \Base\Base\BaseObj;
            }

            if (!is_object($object->{$key})) {
                $object->{$key} = new \Base\Base\BaseObj([$object->{$key}]);
            }
        } else {
            if (!isset($object->{$key}) || !is_object($object->{$key})) {
                return false;
            }
        }

        $object = $object->{$key};
    }

    if (!is_object($object)) {
        return false;
    }

    $key = array_shift($keys);

    $object->{$key} = $value;

    return true;
}

function array_set(&$array, $keys, $value, $recurs = true)
{
    if (is_null($keys)) {
        return $array = $value;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    while ($count = count($keys) > 1) {
        $key = array_shift($keys);

        if ($recurs) {
            if (!isset($array[$key])) {
                $array[$key] = [];
            }

            if (!is_array($array[$key])) {
                $array[$key] = [$array[$key]];
            }
        } else {
            if (!isset($array[$key]) || !is_array($array[$key])) {
                return false;
            }
        }

        $array = &$array[$key];
    }

    if (!is_array($array)) {
        return false;
    }

    $key = array_shift($keys);

    $array[$key] = $value;

    return true;
}

function array_replaces(&$array, $keys, $value, $recurs = true)
{
    if (is_null($keys)) {
        return $array = $value;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    while ($count = count($keys) > 1) {
        $key = array_shift($keys);

        if ($recurs) {
            if (!isset($array[$key])) {
                $array[$key] = [];
            }

            if (!is_array($array[$key])) {
                $array[$key] = [$array[$key]];
            }
        } else {
            if (!isset($array[$key]) || !is_array($array[$key])) {
                return false;
            }
        }

        $array = &$array[$key];
    }

    if (!is_array($array)) {
        return false;
    }

    $key = array_shift($keys);

    if (isset($array[$key])) {
        $array[$key] = $value;

        return true;
    }

    return false;
}

function array_append(&$array, $keys, $value, $recurs = true)
{
    if (is_null($keys)) {
        return $array = $value;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    while ($count = count($keys) > 0) {
        $key = array_shift($keys);

        if ($recurs) {
            if (!isset($array[$key])) {
                $array[$key] = [];
            }

            if (!is_array($array[$key])) {
                $array[$key] = [$array[$key]];
            }
        } else {
            if (!isset($array[$key]) || !is_array($array[$key])) {
                return false;
            }
        }

        $array = &$array[$key];
    }

    if (!is_array($array)) {
        return false;
    }

    $array = array_merge($array, [$value]);

    return true;
}

function array_prepend(&$array, $keys, $value, $recurs = true)
{
    if (is_null($keys)) {
        return $array = $value;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    while ($count = count($keys) > 0) {
        $key = array_shift($keys);

        if ($recurs) {
            if (!isset($array[$key])) {
                $array[$key] = [];
            }

            if (!is_array($array[$key])) {
                $array[$key] = [$array[$key]];
            }
        } else {
            if (!isset($array[$key]) || !is_array($array[$key])) {
                return false;
            }
        }

        $array = &$array[$key];
    }

    if (!is_array($array)) {
        return false;
    }

    $array = array_merge([$value], $array);

    return true;
}

function array_exists(&$array, $keys)
{
    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    if (empty($keys)) {
        return false;
    }

    while (count($keys) > 1) {
        $key = array_shift($keys);

        if (!isset($array[$key]) || !is_array($array[$key])) {
            return false;
        }

        $array = &$array[$key];
    }

    $key = array_shift($keys);

    if (!isset($array[$key])) {
        return false;
    }

    return true;
}

function array_forget(&$array, $keys)
{
    $original = &$array;

    $keys = (array )$keys;

    if (count($keys) === 0) {
        return;
    }

    foreach ($keys as $key) {
        // if the exact key exists in the top-level, remove it
        if (isset($array[$key])) {
            unset($array[$key]);

            continue;
        }

        $parts = explode('.', $key);

        // clean up before each pass
        $array = &$original;

        while (count($parts) > 1) {
            $part = array_shift($parts);

            if (isset($array[$part]) && is_array($array[$part])) {
                $array = &$array[$part];
            } else {
                continue 2;
            }
        }

        unset($array[array_shift($parts)]);
    }
}

function array_pull(&$array, $key, $default = null)
{
    $value = array_get($array, $key, $default);

    array_forget($array, $key);

    return $value;
}

function delFolder($dir)
{
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir($dir . DIRECTORY_SEPARATOR . $file)) ? delFolder($dir .
                DIRECTORY_SEPARATOR . $file) : unlink($dir . DIRECTORY_SEPARATOR . $file);
        }
        return rmdir($dir);
    }
}

function squeeze_values_array($values)
{
    $result = [];

    if (is_array($values)) {
        foreach ($values as $value) {
            if (!empty($value['key']) && !empty($value['value'])) {
                $result[] = $value;
            }
        }
    }

    return serialize($result);
}

function squeeze_curlopt_array($curlopts)
{
    $result = [];

    if (is_array($curlopts)) {
        foreach ($curlopts as $curlopt) {
            if (preg_match("#^CURLOPT_#", $curlopt['key']) && !empty($curlopt['value'])) {
                if (defined($curlopt['key'])) {
                    $result[] = $curlopt;
                }
            }
        }
    }

    return serialize($result);
}

function build_fields_triplet($request, $key, $key_1, $key_2, $key_3)
{
    $result = [];

    $method_key_1 = $key . '_' . $key_1;
    $method_key_2 = $key . '_' . $key_2;
    $method_key_3 = $key . '_' . $key_3;

    if ($request->$method_key_1 && $request->$method_key_2 && $request->$method_key_3 &&
        is_array($request->$method_key_1) && is_array($request->$method_key_2) &&
        is_array($request->$method_key_3)) {
        $req_key_1 = $request->$method_key_1;
        $req_key_2 = $request->$method_key_2;
        $req_key_3 = $request->$method_key_3;

        foreach ($req_key_1 as $key => $value) {
            if (isset($req_key_2[$key]) && isset($req_key_3[$key]) && !empty($req_key_1[$key]) &&
                !empty($req_key_2[$key]) && !empty($req_key_3[$key])) {
                $result[] = [$key_1 => trim($req_key_1[$key]), $key_2 => trim($req_key_2[$key]),
                    $key_3 => trim($req_key_3[$key]), ];
            }
        }
    }

    return $result;
}

function build_fields_couple($request, $key, $suffix_, $_suffix)
{
    $result = [];

    $method_phrase = $key . '_' . $suffix_;
    $method_attribute = $key . '_' . $_suffix;

    if ($request->$method_phrase && $request->$method_attribute && is_array($request->
        $method_attribute) && is_array($request->$method_attribute)) {
        $phrase = $request->$method_phrase;
        $attribute = $request->$method_attribute;

        foreach ($phrase as $key => $value) {
            if (isset($attribute[$key]) && !empty($phrase[$key])) {
                $result[] = [$suffix_ => trim($phrase[$key]), $_suffix => trim($attribute[$key]), ];
            }
        }
    }

    return $result;
}

function squeeze($var, $pattern = null)
{
    $result = array();

    if (!empty($var) && is_array($var)) {
        foreach ($var as $key => $value) {
            if (!empty($value)) {
                if ($pattern) {
                    if (preg_match($pattern, $value)) {
                        $result[$key] = trim($value);
                    }
                } else {
                    $result[$key] = trim($value);
                }
            }
        }
    }

    return serialize($result);
}

function unsqueeze($var)
{
    $result = null;

    if (!empty($var)) {
        $result = unserialize($var);
    }

    if (is_array($result)) {
        return $result;
    } else {
        return array();
    }
}

function getPaginator($count, $limit, $targetpage, $symbol = '?')
{
    $stages = 3;
    $page = (isset($_GET['p']) ? intval($_GET['p']) : 1);

    global $start;
    if ($page) {
        $start = ($page - 1) * $limit;
    } else {
        $start = 0;
    }

    // Инициализируем начальные параметры
    if ($page == 0)
        $page = 1;
    $prev = $page - 1;
    $next = $page + 1;
    $lastpage = ceil($count / $limit);
    $LastPagem1 = $lastpage - 1; // Предпоследняя страница

    $paginate = ''; // div блок, в котором будет содержаться навигация

    $current = 'style="color: #000080; background-color: #EEE8AA;"';

    if ($lastpage > 1) {
        $paginate .= '<ul class="pagination">';
        // Формирование ссылки "Предыдущая"
        if ($page > 1) {
            $paginate .= "<li><a href='$targetpage" . $symbol . "p=$prev'>&laquo;</a></li>";
        } else {
            $paginate .= "<li><span class='disabled'>&laquo;</span></li>";
        }

        // Страницы
        if ($lastpage < 7 + ($stages * 2))
            // Недостаточно страниц для создания троеточия
            {
            for ($counter = 1; $counter <= $lastpage; $counter++) {
                if ($counter == $page) {
                    $paginate .= "<li $current><span $current class='current'>$counter</span></li>";
                } else {
                    $paginate .= "<li><a href='$targetpage" . $symbol . "p=$counter'>$counter</a></li>";
                }
            }
        } elseif ($lastpage > 5 + ($stages * 2))
        // Достаточно страниц, чтобы скрыть несколько из них
            {
            if ($page < 1 + ($stages * 2)) {
                for ($counter = 1; $counter < 4 + ($stages * 2); $counter++) {
                    if ($counter == $page) {
                        $paginate .= "<li $current><span $current class='current'>$counter</span></li>";
                    } else {
                        $paginate .= "<li><a href='$targetpage" . $symbol . "p=$counter'>$counter</a></li>";
                    }
                }
                $paginate .= "<li><span>...</span></li>";
                $paginate .= "<li><a href='$targetpage" . $symbol . "p=$LastPagem1'>$LastPagem1</a></li>";
                $paginate .= "<li><a href='$targetpage" . $symbol . "p=$lastpage'>$lastpage</a></li>";
            } elseif ($lastpage - ($stages * 2) > $page && $page > ($stages * 2)) {
                $paginate .= "<li><a href='$targetpage" . $symbol . "p=1'>1</a></li>";
                $paginate .= "<li><a href='$targetpage" . $symbol . "p=2'>2</a></li>";
                $paginate .= "<li><span>...</span></li>";
                for ($counter = $page - $stages; $counter <= $page + $stages; $counter++) {
                    if ($counter == $page) {
                        $paginate .= "<li $current><span $current class='current'>$counter</span></li>";
                    } else {
                        $paginate .= "<li><a href='$targetpage" . $symbol . "p=$counter'>$counter</a></li>";
                    }
                }
                $paginate .= "<li><span>...</span></li>";
                $paginate .= "<li><a href='$targetpage" . $symbol . "p=$LastPagem1'>$LastPagem1</a></li>";
                $paginate .= "<li><a href='$targetpage" . $symbol . "p=$lastpage'>$lastpage</a></li>";
            } else {
                $paginate .= "<li><a href='$targetpage" . $symbol . "p=1'>1</a></li>";
                $paginate .= "<li><a href='$targetpage" . $symbol . "p=2'>2</a></li>";
                $paginate .= "<li><span>...</span></li>";
                for ($counter = $lastpage - (2 + ($stages * 2)); $counter <= $lastpage; $counter++) {
                    if ($counter == $page) {
                        $paginate .= "<li $current><span $current class='current'>$counter</span></li>";
                    } else {
                        $paginate .= "<li><a href='$targetpage" . $symbol . "p=$counter'>$counter</a></li>";
                    }
                }
            }
        }

        // Формирование ссылки "Следующая"
        if ($page < $counter - 1) {
            $paginate .= "<li><a href='$targetpage" . $symbol . "p=$next'>&raquo;</a></li>";
        } else {
            $paginate .= "<li><span class='disabled'>&raquo;</span></li>";
        }

        $paginate .= '</ul>';
    }
    return $paginate; // Возвращаем текстовую переменную, которая содержит блок со страничной навигацией
}

function download_file($filename)
{
    preg_match('/^.+\/([^\/]+)$/i', $filename, $matches);

    if (!headers_sent())
        header_sent('Content-Disposition: attachment; filename=' . $matches[1]);
    if (!headers_sent())
        header_sent('Content-Length: ' . filesize($filename));
    if (!headers_sent())
        header_sent('Keep-Alive: timeout=5, max=100');
    if (!headers_sent())
        header_sent('Connection: Keep-Alive');
    if (!headers_sent())
        header_sent('Content-Type: octet-stream');
    readfile($filename);
}

function str_limit($value, $limit = 100, $end = '...')
{
    if (mb_strwidth($value, 'UTF-8') <= $limit) {
        return $value;
    }

    return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
}

function translit($s, $del = '_')
{
    $s = (string )$s; // преобразуем в строковое значение

    $s = mb_convert_encoding($s, "UTF-8");

    $s = strip_tags($s); // убираем HTML-теги
    $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
    $s = trim($s); // убираем пробелы в начале и конце строки
    $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
    $s = strtr($s, array(
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'е' => 'e',
        'ё' => 'e',
        'ж' => 'zh',
        'з' => 'z',
        'и' => 'i',
        'й' => 'y',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'h',
        'ц' => 'c',
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'shch',
        'ы' => 'y',
        'э' => 'e',
        'ю' => 'yu',
        'я' => 'ya',
        'ъ' => '',
        'ь' => '',
        "і" => "i",
        "ї" => "i",
        "є" => "ie"));

    $s = preg_replace("/[^0-9a-z]/i", $del, $s); // очищаем строку от недопустимых символов

    $s = preg_replace("#" . ($del == '.' ? '\.' : $del) . "{2,}#", $del, $s);

    $s = trim($s, $del);

    return $s; // возвращаем результат
}

function encode_emoji($content)
{
    if (function_exists('mb_convert_encoding')) {
        $regex = '/(
		     \x23\xE2\x83\xA3               # Digits
		     [\x30-\x39]\xE2\x83\xA3
		   | \xF0\x9F[\x85-\x88][\xA6-\xBF] # Enclosed characters
		   | \xF0\x9F[\x8C-\x97][\x80-\xBF] # Misc
		   | \xF0\x9F\x98[\x80-\xBF]        # Smilies
		   | \xF0\x9F\x99[\x80-\x8F]
		   | \xF0\x9F\x9A[\x80-\xBF]        # Transport and map symbols
		)/x';

        $matches = array();
        if (preg_match_all($regex, $content, $matches)) {
            if (!empty($matches[1])) {
                foreach ($matches[1] as $emoji) {
                    /*
                    * UTF-32's hex encoding is the same as HTML's hex encoding.
                    * So, by converting the emoji from UTF-8 to UTF-32, we magically
                    * get the correct hex encoding.
                    */
                    $unpacked = unpack('H*', mb_convert_encoding($emoji, 'UTF-32', 'UTF-8'));
                    if (isset($unpacked[1])) {
                        $entity = '&#x' . ltrim($unpacked[1], '0') . ';';
                        $content = str_replace($emoji, $entity, $content);
                    }
                }
            }
        }
    }

    return $content;
}

function getCodeTextDir($dirname)
{
    $texts = [];

    $dir = opendir($dirname);
    while (($file = readdir($dir)) !== false) {
        if ($file != "." && $file != "..") {
            if (is_file($dirname . '/' . $file)) {
                $path_parts = pathinfo($dirname . '/' . $file);
                if ($path_parts && isset($path_parts['extension']) && $path_parts['extension'] ==
                    'php') {
                    if (!in_array($dirname . '/' . $file, ['Base/command/DefaultSources.php',
                        'Base/base/ViewHelper.php', 'Base/base/IncludeFile.php',
                        'Base/base/View.php'])) {
                        $view = getCodeText($dirname . '/' . $file);

                        if (!empty($view))
                            $texts[] = $view;
                    }

                }
            }
        }
    }

    closedir($dir);

    return implode("", $texts);
}

function getCodeTextViewDir($dirname)
{
    $texts = [];

    $dir = opendir($dirname);
    while (($file = readdir($dir)) !== false) {
        if ($file != "." && $file != "..") {
            if (is_file($dirname . '/' . $file)) {
                $path_parts = pathinfo($dirname . '/' . $file);
                if ($path_parts && isset($path_parts['extension']) && $path_parts['extension'] ==
                    'php') {
                    $view = getCodeText($dirname . '/' . $file);

                    $view = str_replace('namespace main;', '', $view);

                    $view = trim($view);

                    $texts[] = '    private function view' . str_replace('.php', '', $file) . " () {\r\n" .
                        $view . "\r\n    }";
                }
            }
        }
    }

    closedir($dir);

    return implode("\r\n\r\n", $texts);
}

function getCodeTextJavascript($path)
{
    $text = getCodeText($path);

    return '    private function javascript' . str_replace(['/', '\\', '.', '-'],
        '_', trim(str_replace('.js', '', $path), './ ')) . " () {\r\n?><script type=\"text/javascript\">\r\n" .
        $text . "\r\n</script><?php\r\n    }\r\n";
}

function getCodeTextCss($path)
{
    $text = getCodeText($path);

    return '    private function css' . str_replace(['/', '\\', '.', '-'], '_', trim
        (str_replace('.css', '', $path), './ ')) . " () {\r\n?><style>\r\n" . $text . "\r\n</style><?php\r\n    }\r\n";
}

function str_rand($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getCodeText($path)
{
    global $paths;

    if (!is_array($paths)) {
        $paths = [];
    }

    if (in_array($path, $paths)) {
        return '';
        echo 'Dublicate File: ' . $path;
        exit();
    }

    if (file_exists(\Base::app()->config('SITE_ROOT') . '/' . $path)) {
        $paths[] = $path;
        return prepareCodeText(file_get_contents(\Base::app()->config('SITE_ROOT') .
            '/' . $path));
    } else {
        echo 'File does not exist: ' . $path;
        exit();
    }

    return '';
}

function prepareCodeText($text)
{
    $text = trim($text);
    $text = preg_replace("#^<\?php#si", '', $text);
    $text = preg_replace("#\?>$#si", '', $text);
    $text = trim($text);
    $text = "\r\n" . $text . "\r\n";
    return $text;
}

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = false)
    {
        $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
        $str_end = "";
        if ($lower_str_end) {
            $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding),
                $encoding);
        } else {
            $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
        }
        $str = $first_letter . $str_end;
        return $str;
    }
}

if (!function_exists('mb_lcfirst')) {
    function mb_lcfirst($str, $encoding = "UTF-8", $lower_str_end = false)
    {
        $first_letter = mb_strtolower(mb_substr($str, 0, 1, $encoding), $encoding);
        $str_end = "";
        if ($lower_str_end) {
            $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding),
                $encoding);
        } else {
            $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
        }
        $str = $first_letter . $str_end;
        return $str;
    }
}

if (!function_exists('eachArray')) {
    function eachArray(array & $array)
    {
        $key = key($array);
        $result = ($key === null) ? false : [$key, current($array), 'key' => $key,
            'value' => current($array)];
        next($array);
        return $result;
    }
}

function cmdexec($command)
{
    if (file_exists(__dir__ . "/update_log")) {
        unlink(__dir__ . "/update_log");
    }

    if (substr(php_uname(), 0, 7) == "Windows") {
        //windows
        pclose(popen("start /B " . $command . " 1> " . __dir__ . "/update_log 2>&1 &",
            "r"));
    } else {
        //linux
        shell_exec($command . " > /dev/null 2>&1 &");
    }
}
