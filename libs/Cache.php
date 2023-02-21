<?php

interface CacherInterface {

    /**
     * ���������� ��� � ����
     *
     * @param string $key
     * @param $data
     * @param int|null $interval
     * @return bool
     */
    public function set (string $key, $data, int $interval = null) : bool;

    /**
     * ���������� �������� ����
     *
     * @param string $key
     * @return mixed
     */
    public function get (string $key);

    /**
     * ��������� ���������� �� ����
     *
     * @param string $key
     * @return bool
     */
    public function has (string $key) : bool;

    /**
     * �������� ����
     *
     * @param string $key
     * @return bool
     */
    public function delete (string $key) : bool;

    /**
     * ������� ����� ����
     *
     * @return bool
     */
    public function clear () : bool;

    /**
     * ����� ������������
     *
     * @param array $configuration
     * @return array
     */
    public function configure (array $configuration = []) : array;

}

/**
 * ������ � �����
 *
 * Class CacherFileSystemAdapter
 * @package arhone\caching\cacher
 * @author ������� ��� <info@arh.one>
 */
class CacherFileSystemAdapter implements CacherInterface {

    /**
     * ��������� ������
     *
     * @var array
     */
    protected $configuration = [
        'state'     => true,
        'directory' => __DIR__ . '/../data/cache'
    ];

    /**
     * CacherFileSystemAdapter constructor.
     * @param array $configuration
     */
    public function __construct (array $configuration = []) {

        $this->configure($configuration);

    }

    /**
     * ��������� � ��������/�������� ���
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
     * ���������� �������� ����
     *
     * @param string $key
     * @return mixed
     */
    public function get (string $key) {

        if (!$this->getState()) {
            return false;
        }

        $path = $this->getPath($key);

        if (is_file($path)) {

            $data = unserialize(file_get_contents($path));

            if (!empty($data['remove']) && $data['remove'] < time()) {
                $this->delete($key);

                return false;

            }

            return $data['data'] ?? false;

        }

        return null;

    }

    /**
     * ���������� ��� � ����
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
     * �������� ����
     *
     * @param string $key
     * @return bool
     */
    public function delete (string $key) : bool {

        return $this->deleteRecursive($this->getPath($key)) == true;

    }

    /**
     * �������� �����
     *
     * @param string $key
     * @return bool
     */
    public function has (string $key) : bool {

        return !empty($this->getPath($key));

    }

    /**
     * ������� ����
     * 
     * @return bool
     */
    public function clear () : bool {

        return $this->deleteRecursive($this->configuration['directory']) == true;

    }

    /**
     * ����������� �������� ������
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
     * ��������� ���� �� �����
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
     * ����� ������������
     * 
     * @param array $configuration
     * @return array
     */
    public function configure (array $configuration = []) : array {

        return $this->configuration = array_merge($this->configuration, $configuration);

    }

}

/**
 * ������ � �����
 *
 * Class CacherMemcachedAdapter
 * @package arhone\caching\cacher
 * @author ������� ��� <info@arh.one>
 */
 
class CacherMemcachedAdapter implements CacherInterface {

    /**
     * ��������� ������
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
        $this->Memcached->setCompressThreshold(0, 1);

        $this->configure($configuration);

    }

    /**
     * ��������� � ��������/�������� ���
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
     * ���������� �������� ����
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
     * ���������� ��� � ����
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
     * �������� ����
     *
     * @param string|null $key
     * @return bool
     */
    public function delete (string $key = null) : bool {

        return $this->Memcached->set($key, false) == true;

    }

    /**
     * �������� ����
     *
     * @param string|null $key
     * @return bool
     */
    public function has (string $key = null) : bool {

        return !empty($this->Memcached->get($key));

    }

    /**
     * ������� ����
     *
     * @return bool
     */
    public function clear () : bool {

        return $this->Memcached->flush() == true;

    }

    /**
     * ����� ������������
     *
     * @param array $configuration
     * @return array
     */
    public function configure (array $configuration = []) : array {

        return $this->configuration = array_merge($this->configuration, $configuration);

    }

}

/**
 * ������ � �����
 *
 * Class CacherRedisAdapter
 * @package arhone\caching\cacher
 * @author ������� ��� <info@arh.one>
 */
class CacherRedisAdapter implements CacherInterface {

    /**
     * ��������� ������
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
     * ��������� � ��������/�������� ���
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
     * ���������� �������� ����
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
     * ���������� ��� � ����
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
     * �������� ����
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
     * ��������� ������������� �����
     *
     * @param string $key
     * @return bool
     */
    public function has (string $key) : bool {

        return $this->Redis->exists($key);

    }

    /**
     * ������� ���
     *
     * @return bool
     */
    public function clear () : bool {

        return $this->Redis->flushAll() == true;

    }

    /**
     * ����� ������������
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
    const CLEAR_CACHE_TIME = 3600;
    
    public function __construct (array $configuration = []) {

        parent::__construct();
        
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