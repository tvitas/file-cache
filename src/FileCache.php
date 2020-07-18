<?php
namespace tvitas\FileCache;

use Psr\SimpleCache\CacheInterface;

class FileCache implements CacheInterface
{

    private $cacheDir = '';

    private $ttl;

    public function __construct($cacheDir = '', $ttl = null)
    {
        ('' === $cacheDir) ? $this->cacheDir = __DIR__ . '/../cache' : $this->cacheDir = $cacheDir;
        $this->ttl = $ttl;

    }

    public function get($key, $default = null)
    {
        $filename = $this->cacheDir . '/' . $key;
        if (file_exists($filename)) {
            return unserialize(file_get_contents($filename));
        }
        return $default;
    }

    public function set($key, $value, $ttl = null)
    {
        $filename = $this->cacheDir . '/' . $key;

        if (null === $ttl) {
            $ttl = $this->ttl;
        }
        if (file_exists($filename)) {
            $filemtime = filemtime($filename);
            $now = time();
            if ($now - $filemtime < $ttl) {
                return true;
            } else {
                $this->delete($key);
            }
        }
        return (false === file_put_contents($filename, serialize($value))) ? false : true;
    }

    public function delete($key)
    {
        $filename = $this->cacheDir . '/' . $key;
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    public function clear()
    {
        $keys = array_values(array_diff(scandir($this->cacheDir), ['.', '..']));
        $this->deleteMultiple($keys);
    }

    public function getMultiple($keys, $default = null)
    {
        $multiple = [];
        foreach ($keys as $key) {
            $multiple[$key] = $this->get($key, $default);
        }
        return $multiple;
    }

    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
    }

    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
    }

    public function has($key)
    {
        $filename = $this->cacheDir . '/' . $key;
        if (file_exists($filename)) {

            if (null == $this->ttl) {
                return true;
            }

            $now = time();
            $filemtime = filemtime($filename);

            if ($now - $filemtime < $this->ttl) {
                return true;
            }

            return false;
        }
        return false;
    }
}
