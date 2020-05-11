<?php
namespace tvitas\FileCache;

use Psr\SimpleCache\CacheInterface;

class FileCache implements CacheInterface
{

    private $cacheDir = '';

    public function __construct($cacheDir = '')
    {
        ('' === $cacheDir) ? $this->cacheDir = __DIR__ . '/../cache' : $this->cacheDir = $cacheDir;

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
        if (file_exists($filename)) {
            $filemtime = filemtime($filename);
            $now = time();
            if (null !== $ttl and ($now - $filemtime < $ttl)) {
                return true;
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
        $has = false;
        if (file_exists($filename)) {
            $has = true;
        }
        return $has;
    }
}
