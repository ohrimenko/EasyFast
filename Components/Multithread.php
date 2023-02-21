<?php

namespace App\Components;

class Multithread
{
    protected static $is_initialize = false;

    protected static $params = [];

    protected static $loc_files = [];

    protected $tasks = [];

    protected $is_windows = false;
    protected $status_proccess = true;
    protected $streams = [];

    public function __construct(array $args = [])
    {
        if (!self::$is_initialize) {
            self::$is_initialize = true;

            self::initialize($args);
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->is_windows = true;
        } else {
            $this->is_windows = false;
        }
    }

    public static function initialize(array $args = [])
    {
        if (isset($args['tmp_dir']) && is_dir($args['tmp_dir'])) {
            self::$params['tmp_dir'] = $args['tmp_dir'];
        } else {
            self::$params['tmp_dir'] = config('DIR_TMP');

            if (!is_dir(self::$params['tmp_dir'])) {
                mkdir(self::$params['tmp_dir'], 0777);
            }
        }
    }

    public function addTask($uses, $data)
    {
        if (true) {
            $this->tasks[] = ['uses' => $uses, 'data' => $data];
        }
    }

    public function execTaskToStream($keystream)
    {
        if ($this->streams[$keystream]['status'] === 0) {
            $this->streams[$keystream]['task'] = array_shift($this->tasks);
            
            if ($this->streams[$keystream]['task']) {
                $this->streams[$keystream]['status'] = 1;
                
                $this->streams[$keystream]['task']['data']['keystream'] = $keystream;

                file_put_contents(self::$params['tmp_dir'] . '/task_' . $keystream . '.txt',
                    json_encode(['params' => self::$params, 'task' => $this->streams[$keystream]['task']]));

                $this->cmdexec('php ' . config('SITE_ROOT') . '/multithread.php multithreadruntask:' . self::$params['tmp_dir'] .
                    '/task_' . $keystream . '.txt', $keystream);
            }
        }
    }

    public function run($count_stream = 1)
    {
        $this->streams = [];

        for ($i = 0; $i < $count_stream; $i++) {
            $this->streams[$i] = ['status' => 0, 'task' => null, ];
        }

        foreach ($this->streams as $keystream => $stream) {
            if (file_exists(self::$params['tmp_dir'] . '/out_tmp_' . $keystream)) {
                unlink(self::$params['tmp_dir'] . '/out_tmp_' . $keystream);
            }
            if (file_exists(self::$params['tmp_dir'] . '/task_' . $keystream . '.txt')) {
                unlink(self::$params['tmp_dir'] . '/task_' . $keystream . '.txt');
            }
            if (file_exists(self::$params['tmp_dir'] . '/task_' . $keystream .
                '.txt.proccess')) {
                unlink(self::$params['tmp_dir'] . '/task_' . $keystream . '.txt.proccess');
            }
        }

        while ($this->status_proccess) {
            $this->status_proccess = false;

            foreach ($this->streams as $keystream => $stream) {
                $this->execTaskToStream($keystream);

                if ($this->streams[$keystream]['status'] === 1) {
                    if (file_exists(self::$params['tmp_dir'] . '/task_' . $keystream .
                        '.txt.proccess')) {
                        $this->streams[$keystream]['status'] = 2;
                    }
                }

                if ($this->streams[$keystream]['status'] === 2) {
                    if (self::lockFile(self::$params['tmp_dir'] . '/task_' . $keystream .
                        '.txt.proccess')) {
                        $this->streams[$keystream]['status'] = 3;
                        self::unlockFile(self::$params['tmp_dir'] . '/task_' . $keystream .
                            '.txt.proccess');
                        //usleep(100);
                    }
                }

                if ($this->streams[$keystream]['status'] === 3) {
                    $response = null;

                    if (is_readable(self::$params['tmp_dir'] . '/task_' . $keystream .
                        '.txt.proccess')) {
                        $response = file_get_contents(self::$params['tmp_dir'] . '/task_' . $keystream .
                            '.txt.proccess');

                        unlink(self::$params['tmp_dir'] . '/task_' . $keystream . '.txt.proccess');

                        if ($this->is_windows) {
                            unlink(self::$params['tmp_dir'] . '/out_tmp_' . $keystream);
                        }
                    }

                    if ($response == 'status:3') {
                        // success finish
                    } else {
                        // error finish
                    }

                    $this->streams[$keystream]['status'] = 0;
                    $this->streams[$keystream]['task'] = null;

                    $this->execTaskToStream($keystream);
                }

                if ($this->streams[$keystream]['status'] > 0) {
                    $this->status_proccess = true;
                }
            }

            if ($this->status_proccess) {
                usleep(100000);
            }
        }
    }

    public static function runTask($tmp_file)
    {
        ini_set('error_reporting', 0);
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        ini_set('log_errors', 'off');
        
        if (is_file($tmp_file)) {
            $json = json_decode(file_get_contents($tmp_file), true);

            rename($tmp_file, $tmp_file . '.proccess');

            file_put_contents($tmp_file . '.proccess', 'status:1');

            self::lockFile($tmp_file . '.proccess');

            if ($json) {
                if (isset($json['timeout'])) {
                    set_time_limit($json['timeout']);
                }

                $multithread = new self($json['params']);

                $parts = explode('@', $json['task']['uses'], 2);

                $class = new $parts[0]($json['task']['data']);

                $class->{$parts[1]}($json['task']['data']);
            }

            self::unlockFile($tmp_file . '.proccess');

            file_put_contents($tmp_file . '.proccess', 'status:3');
        }
    }

    public function cmdexec($command, $key = 1)
    {
        if ($this->is_windows) {
            $file_out = self::$params['tmp_dir'] . "/out_tmp_" . $key;

            $is = false;

            if (file_exists($file_out)) {
                if (is_readable($file_out)) {
                    if (unlink($file_out)) {
                        $is = true;
                    }
                }
            } else {
                $is = true;
            }

            //windows
            if ($is) {
                pclose(popen("start /B " . $command . " 1> " . $file_out . " 2>&1 &", "r"));
            }
        } else {
            //linux
            shell_exec($command . " > /dev/null 2>&1 &");
        }
    }

    public function str_rand($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function lockFile($file_name, $wait = false)
    {
        $loc_file = fopen($file_name, 'c');
        if (!$loc_file) {
            return false;
        }
        if ($wait) {
            $lock = flock($loc_file, LOCK_EX);
        } else {
            $lock = flock($loc_file, LOCK_EX | LOCK_NB);
        }
        if ($lock) {
            self::$loc_files[$file_name] = $loc_file;
            return $loc_file;
        } else {
            return false;
        }
    }

    public static function unlockFile($file_name)
    {
        fclose(self::$loc_files[$file_name]);
        unset(self::$loc_files[$file_name]);
    }
}
