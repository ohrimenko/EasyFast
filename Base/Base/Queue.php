<?php

namespace Base\Base;

use \Base;
use \Base\Base\View;
use \Base\Base\Data;


class Queue
{
    private $token;
    private $curlExec;
    const WHILE_TIME = 3;

    public function __construct()
    {
        require_once (config('base_dir') . '/libs/CurlExec.php');

        //this->curlExec = new \CurlExec([], null, 0, null);

        $this->token = str_rand(20);

        if (!file_exists(config('storage_dir') . '/queue/data.txt')) {
            file_put_contents(config('storage_dir') . '/queue/data.txt', $this->token);
        }

        $data = self::getData();

        $data['token'] = $this->token;

        self::saveData($data);
    }

    public function run()
    {
        while (true) {
            $data = self::getData();

            if ($data['token'] != $this->token) {
                //echo $data['token'] . ' != ' . $this->token;
                return;
            }

            while (!empty($data['items'])) {
                $item = $data['items'][0];

                unset($data['items'][0]);

                $data = self::saveData($data);

                $this->sendExecution($item);
            }

            sleep(self::WHILE_TIME);
        }
    }

    private function sendExecution($item)
    {
        //echo file_get_contents(route('quenie.exec', ['item' => $item]));
        //fast_request(route('quenie.exec', ['item' => $item]));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, route('quenie.exec', ['item' => $item]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        //curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT_MS, 500);
        //curl_setopt ($ch, CURLOPT_TIMEOUT_MS, 500);
        curl_exec($ch);
        curl_close($ch);
    }

    private function execution($item)
    {
        $this->curlExec->ExecuteMulti(3);
    }

    public static function exec($item)
    {
        if (file_exists(config('storage_dir') . '/queue/data/' . $item . '.txt')) {
            $data = unserialize(file_get_contents(config('storage_dir') . '/queue/data/' . $item .
                '.txt'));

            if ($data) {
                $obj = new $data['class'];

                $obj->{$data['method']}($data['data']);
            }

            unlink(config('storage_dir') . '/queue/data/' . $item . '.txt');
        }
    }

    private static function getData()
    {
        $items = file(config('storage_dir') . '/queue/data.txt');

        foreach ($items as $key => $value) {
            $items[$key] = trim($value);
            if (empty($items[$key])) {
                unset($items[$key]);
            }
        }

        $token = $items[0];

        unset($items[0]);

        $items = array_values($items);

        return ['token' => $token, 'items' => $items];
    }

    private static function setData($item = null)
    {
        $data = self::getData();

        if ($item) {
            $data['items'][] = $item;
        }

        if (empty($data['items'])) {
            $text = $data['token'];
        } else {
            $data['items'] = array_values($data['items']);

            $text = $data['token'] . "\n" . implode("\n", $data['items']);
        }

        file_put_contents(config('storage_dir') . '/queue/data.txt', $text);

        return $data;
    }

    private static function saveData($data)
    {
        if (empty($data['items'])) {
            $text = $data['token'];
        } else {
            $data['items'] = array_values($data['items']);

            $text = $data['token'] . "\n" . implode("\n", $data['items']);
        }

        file_put_contents(config('storage_dir') . '/queue/data.txt', $text);

        return $data;
    }

    public static function add($class, $method, $data)
    {
        $item = str_rand(10);

        file_put_contents(config('storage_dir') . '/queue/data/' . $item . '.txt',
            serialize(['class' => $class, 'method' => $method, 'data' => $data, ]));

        self::setData($item);
    }
}
