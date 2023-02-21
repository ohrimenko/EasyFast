<?php

namespace Base\Base;

use \Base\Base\Route;

abstract class Registry
{
    abstract protected function get($key);
    abstract protected function set($key, $val);
}

class RequestRegistry extends Registry
{
    private $values = array();
    private static $instance = null;

    private function __construct()
    {
    }

    static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function get($key)
    {
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }
        return null;
    }

    protected function set($key, $val)
    {
        $this->values[$key] = $val;
    }

    static function getRequest()
    {
        $inst = self::instance();
        if (is_null($inst->get("request"))) {
            $inst->set('request', new \Base\controller\Request());
        }
        return $inst->get("request");
    }

}

class SessionRegistry extends Registry
{
    private static $instance = null;
    private function __construct()
    {
        session_start();
    }

    static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function get($key)
    {
        if (isset($_SESSION[__class__][$key])) {
            return $_SESSION[__class__][$key];
        }
        return null;
    }

    protected function set($key, $val)
    {
        $_SESSION[__class__][$key] = $val;
    }

    function setDSN($dsn)
    {
        self::instance()->set('dsn', $dsn);
    }

    function getDSN()
    {
        return self::instance()->get("dsn");
    }
}

class ApplicationRegistry extends Registry
{
    private static $instance = null;
    private $freezedir = null;
    private $values = array();
    private $mtimes = array();

    private $request = null;

    private function __construct()
    {
        $this->freezedir = \Base::app()->config('DIR_DATA');
    }

    static function clean()
    {
        self::$instance = null;
    }

    static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key)
    {
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }

        $path = $this->freezedir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], '.', $key);

        if (file_exists($path)) {
            clearstatcache();
            $mtime = filemtime($path);
            if (!isset($this->mtimes[$key])) {
                $this->mtimes[$key] = 0;
            }

            if ($mtime > $this->mtimes[$key]) {
                $data = file_get_contents($path);
                $this->mtimes[$key] = $mtime;
                return ($this->values[$key] = unserialize($data));
            }
        }

        return null;
    }

    public function set($key, $val, $write = true)
    {
        $this->values[$key] = $val;

        if ($write) {
            $path = $this->freezedir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], '.', $key);
            file_put_contents($path, serialize($val));
            $this->mtimes[$key] = time();

            return filemtime($path);
        }
    }

    static function getDSN()
    {
        return PDO_DSN;
    }

    static function setDSN($dsn)
    {
        return true;
    }

    static function setControllerMap(\Base\controller\ControllerMap $map, $write = true)
    {
        $instance = self::instance();

        $instance->set('cmap', $map, $write);

        if ($write) {
            $path = $instance->freezedir . DIRECTORY_SEPARATOR . 'options.xml';

            if (file_exists($path)) {
                $instance->set('timeUpdateCmap', filemtime($path));
            }
        }
    }

    static function getControllerMap()
    {
        return self::instance()->get('cmap');
    }

    static function isRelevanceControllerMap()
    {
        return self::instance()->relevanceControllerMap();
    }

    protected function relevanceControllerMap()
    {
        $time_last_update = self::instance()->get('timeUpdateCmap');

        if (!is_null($time_last_update)) {
            $path = $this->freezedir . DIRECTORY_SEPARATOR . 'options.xml';

            if (file_exists($path)) {
                $time_now_update = filemtime($path);
                if ($time_now_update && $time_last_update == $time_now_update) {
                    return true;
                }
            }
        }

        return false;
    }

    static function appController()
    {
        $obj = self::instance();
        if (!isset($obj->appController)) {
            $cmap = $obj->getControllerMap();
            $obj->appController = new \Base\controller\AppController($cmap);
        }
        return $obj->appController;
    }

    static function getRequest()
    {
        $inst = self::instance();
        if (is_null($inst->request)) {
            $inst->request = \Base\Base\Request::inst();
        }
        return $inst->request;
    }
}

class MemApplicationRegistry extends Registry
{
    private static $instance = null;
    private $values = array();
    private $id;

    private function __construct()
    {
    }

    static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key)
    {
        return \apc_fetch($key);
    }

    public function set($key, $val)
    {
        return \apc_store($key, $val);
    }

    static function getDSN()
    {
        return self::instance()->get("dsn");
    }

    static function setDSN($dsn)
    {
        return self::instance()->set("dsn", $dsn);
    }

}

class MemcacheApplicationRegistry extends Registry
{
    private $memcache;

    private static $instance = null;
    private $values = array();
    private $id;

    private function __construct()
    {
    }

    static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key)
    {
        if(!$this->memcache){
            $this->memcache = memcache_connect('localhost', 11211);
        }
        
        return $this->memcache->get($key);
    }

    public function set($key, $val, $time = 60)
    {
        if(!$this->memcache){
            $this->memcache = memcache_connect('localhost', 11211);
        }
        
        return $this->memcache->set($key, $val, 0, $time);
    }

    static function getDSN()
    {
        return self::instance()->get("dsn");
    }

    static function setDSN($dsn)
    {
        return self::instance()->set("dsn", $dsn);
    }

}

interface CacherInterface {

    /**
     * Записывает кеш в файл
     *
     * @param string $key
     * @param $data
     * @param int|null $interval
     * @return bool
     */
    public function set (string $key, $data, int $interval = null) : bool;

    /**
     * Возвращает значение кеша
     *
     * @param string $key
     * @return mixed
     */
    public function get (string $key);

    /**
     * Проверяет существует ли ключ
     *
     * @param string $key
     * @return bool
     */
    public function has (string $key) : bool;

    /**
     * Удаление кеша
     *
     * @param string $key
     * @return bool
     */
    public function delete (string $key) : bool;

    /**
     * Очистка всего кеша
     *
     * @return bool
     */
    public function clear () : bool;

    /**
     * Задаёт конфигурацию
     *
     * @param array $configuration
     * @return array
     */
    public function configure (array $configuration = []) : array;

}

/**
 * Работа с кэшем
 *
 * Class CacherFileSystemAdapter
 * @package arhone\caching\cacher
 * @author Алексей Арх <info@arh.one>
 */
class CacherFileSystemAdapter implements CacherInterface {

    /**
     * Настройки класса
     *
     * @var array
     */
    protected $configuration = [
        'state'     => true,
        'directory' => __DIR__ . '/cache'
    ];

    /**
     * CacherFileSystemAdapter constructor.
     * @param array $configuration
     */
    public function __construct (array $configuration = []) {

        $this->configure($configuration);

    }

    /**
     * Проверяет и включает/отключат кеш
     *
     * @param bool $state
     * @return bool
     */
    protected function getState (bool $state = null) : bool {

        if ($state !== null) {
            $this->configuration['state'] = $state == true;
        }

        return ($this->configuration['state'] ?? false) == true;

    }

    /**
     * Возвращает значение кэша
     *
     * @param string $key
     * @return mixed
     */
    public function get (string $key) {

        if (!$this->getState()) {
            return null;
        }

        $path = $this->getPath($key);

        if (is_file($path)) {

            $data = unserialize(file_get_contents($path));

            if (!empty($data['remove']) && $data['remove'] < time()) {
                $this->delete($key);

                return null;

            }

            return $data['data'] ?? null;

        }

        return null;

    }

    /**
     * Записывает кэш в файл
     *
     * @param string $key
     * @param $data
     * @param int|null $interval
     * @return bool
     */
    public function set (string $key, $data, int $interval = null) : bool {

        if (!$this->getState()) {
            return false;
        }

        $path = $this->getPath($key);
        $dir = dirname($path);

        if (!is_dir($dir)) {

            mkdir($dir, 0700, true);

        }

        $data = [
            'created' => time(),
            'remove'  => $interval ? time() + $interval : null,
            'data'    => $data
        ];
        
        return file_put_contents($path, serialize($data), LOCK_EX) == true;

    }

    /**
     * Удаление кеша
     *
     * @param string $key
     * @return bool
     */
    public function delete (string $key) : bool {

        return $this->deleteRecursive($this->getPath($key)) == true;

    }

    /**
     * Проверка ключа
     *
     * @param string $key
     * @return bool
     */
    public function has (string $key) : bool {

        return !empty($this->getPath($key));

    }

    /**
     * Очистка кеша
     * 
     * @return bool
     */
    public function clear () : bool {

        return $this->deleteRecursive($this->configuration['directory']) == true;

    }

    /**
     * Рекурсивное удаление файлов
     *
     * @param $path
     * @return bool
     */
    protected function deleteRecursive ($path) : bool {

        if (is_dir($path)) {

            foreach (scandir($path) as $file) {

                if ($file != '.' && $file != '..') {

                    if (is_file($file)) {

                        unlink($file);

                    } else {

                        $this->deleteRecursive($path . DIRECTORY_SEPARATOR . $file);

                    }

                }

            }

            $emptyDir = count(glob($path . '*')) ? true : false;
            if($emptyDir) {
                return rmdir($path);
            }

        } elseif (is_file($path)) {

            return unlink($path);

        }

        return false;

    }

    /**
     * Возврщает путь до файла
     *
     * @param string $key
     * @return string
     */
    protected function getPath (string $key) : string {

        $path = $this->configuration['directory'] . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $key);

        if (is_dir($path) || is_file($path)) {

            return $path;

        } else {

            $dir  = dirname($path);
            $hash = md5(basename($path));
            return $dir . DIRECTORY_SEPARATOR . '.' . $hash[0] . $hash[1] . DIRECTORY_SEPARATOR . '.' . $hash[2] . $hash[3] . DIRECTORY_SEPARATOR . '.' . $hash;

        }


    }

    /**
     * Задаёт конфигурацию
     * 
     * @param array $configuration
     * @return array
     */
    public function configure (array $configuration = []) : array {

        return $this->configuration = array_merge($this->configuration, $configuration);

    }

}

/**
 * Работа с кэшем
 *
 * Class CacherMemcachedAdapter
 * @package arhone\caching\cacher
 * @author Алексей Арх <info@arh.one>
 */
 
class CacherMemcachedAdapter implements CacherInterface {

    /**
     * Настройки класса
     *
     * @var array
     */
    protected $configuration = [
        'state' => true
    ];

    /**
     * @var \Memcached
     */
    protected $Memcached;

    /**
     * CacherMemcachedAdapter constructor.
     * @param \Memcached $memcached
     * @param array $configuration
     */
    public function __construct (\Memcached $memcached, array $configuration = []) {

        $this->Memcached = $memcached;
        //$this->Memcached->setCompressThreshold(0, 1);

        $this->configure($configuration);

    }

    /**
     * Проверяет и включает/отключат кеш
     *
     * @param bool $state
     * @return bool
     */
    protected function getState (bool $state = null) : bool {

        if ($state !== null) {
            $this->configuration['state'] = $state == true;
        }

        return ($this->configuration['state'] ?? false) == true;

    }

    /**
     * Возвращает значение кэша
     *
     * @param string $key
     * @return mixed
     */
    public function get (string $key) {

        if (!$this->getState()) {
            return false;
        }

        $data = unserialize($this->Memcached->get($key));

        if (!empty($data['remove']) && $data['remove'] < time()) {

            return false;

        }

        return $data['data'] ?? null;

    }

    /**
     * Записывает кэш в файл
     *
     * @param string $key
     * @param $data
     * @param int|null $interval
     * @return bool
     */
    public function set (string $key, $data, int $interval = null) : bool {

        if (!$this->getState()) {
            return false;
        }

        return $this->Memcached->set($key, gzencode($data, 9), MEMCACHE_COMPRESSED, $interval) == true;

    }

    /**
     * Удаление кеша
     *
     * @param string|null $key
     * @return bool
     */
    public function delete (string $key = null) : bool {

        return $this->Memcached->set($key, false) == true;

    }

    /**
     * Удаление кеша
     *
     * @param string|null $key
     * @return bool
     */
    public function has (string $key = null) : bool {

        return !empty($this->Memcached->get($key));

    }

    /**
     * Очистка кеша
     *
     * @return bool
     */
    public function clear () : bool {

        return $this->Memcached->flush() == true;

    }

    /**
     * Задаёт конфигурацию
     *
     * @param array $configuration
     * @return array
     */
    public function configure (array $configuration = []) : array {

        return $this->configuration = array_merge($this->configuration, $configuration);

    }

}

/**
 * Работа с кэшем
 *
 * Class CacherRedisAdapter
 * @package arhone\caching\cacher
 * @author Алексей Арх <info@arh.one>
 */
class CacherRedisAdapter implements CacherInterface {

    /**
     * Настройки класса
     *
     * @var array
     */
    protected $configuration = [
        'state' => true,
    ];

    /**
     * @var \Redis 
     */
    protected $Redis;

    /**
     * CacherRedisAdapter constructor.
     * @param \Redis $redis
     * @param array $configuration
     */
    public function __construct (\Redis $redis, array $configuration = []) {

        $this->Redis = $redis;
        $this->configure($configuration);

    }

    /**
     * Проверяет и включает/отключат кеш
     *
     * @param bool $state
     * @return bool
     */
    protected function getState (bool $state = null) : bool {

        if ($state !== null) {
            $this->configuration['state'] = $state == true;
        }

        return ($this->configuration['state'] ?? false) == true;

    }

    /**
     * Возвращает значение кэша
     *
     * @param string $key
     * @return bool
     */
    public function get (string $key) {

        if (!$this->getState()) {
            return false;
        }

        $data = unserialize($this->Redis->get($key));

        if (!empty($data['remove']) && $data['remove'] < time()) {

            return false;

        }

        return $data['data'] ?? null;

    }

    /**
     * Записывает кэш в файл
     *
     * @param string $key
     * @param $data
     * @param int|null $interval
     * @return bool
     */
    public function set (string $key, $data, int $interval = null) : bool {

        if (!$this->getState()) {
            return false;
        }

        $data = [
            'created' => time(),
            'remove'  => $interval ? time() + $interval : null,
            'data'    => $data
        ];
        return $this->Redis->set($key, serialize($data)) == true;

    }

    /**
     * Удаление кеша
     *
     * @param string $key
     * @return bool
     */
    public function delete (string $key) : bool {

        $result = false;
        $this->Redis->delete($key);
        foreach ($this->Redis->keys($key . '.*') as $key) {
            $this->Redis->delete($key);
            $result = true;
        }

        return $result;

    }

    /**
     * Проверяет существование ключа
     *
     * @param string $key
     * @return bool
     */
    public function has (string $key) : bool {

        return $this->Redis->exists($key);

    }

    /**
     * Очищает кеш
     *
     * @return bool
     */
    public function clear () : bool {

        return $this->Redis->flushAll() == true;

    }

    /**
     * Задаёт конфигурацию
     *
     * @param array $configuration
     * @return array
     */
    public function configure (array $configuration = []) : array {

        return $this->configuration = array_merge($this->configuration, $configuration);

    }

}

class CacheFile extends CacherFileSystemAdapter 
{
    const CLEAR_CACHE_TIME = 86400;
    
    public function __construct (array $configuration = []) {
        
        $configuration['directory'] = config('cache_dir');

        parent::__construct($configuration);
        
        $is_clear_cache_time = $this->get('is_clear_cache_time');
        
        if ($is_clear_cache_time) {
            
        } else {
            $this->clear();
            $this->set('is_clear_cache_time', 1, self::CLEAR_CACHE_TIME);
        }
    }
    
    public function increment (string $key, $value = 1) 
    {
        if ($this->get($key)) {
            $v = $this->get($key);
            
            $v = $v + $value;
            
            $this->set($key, $v);
        }
    }
}

class CacheMemcached extends CacherMemcachedAdapter 
{
    public function __construct (array $configuration = []) {

        $this->Memcached = new \Memcached('mc');
        $this->Memcached->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
        $this->Memcached->addServer('localhost', 11211);

        $this->configure($configuration);

    }
    
    public function set (string $key, $data, int $interval = null) : bool {

        if (!$this->getState()) {
            return false;
        }

        return $this->Memcached->set($key, serialize($data), $interval) == true;
    }
    
    public function del($key)
    {
        return parent::delete($key);
    }
}
