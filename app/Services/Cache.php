<?php
namespace App\Services;

class Cache
{
    private $cachePath;
    private $config;
    
    public function __construct(string $cachePath, array $config = [])
    {
        $this->cachePath = $cachePath;
        $this->config = $config;
        
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
    }
    
    public function get(string $key, $default = null)
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($file));
        
        if ($data['expires'] > 0 && time() > $data['expires']) {
            $this->delete($key);
            return $default;
        }
        
        return $data['value'];
    }
    
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        $file = $this->getFilePath($key);
        $dir = dirname($file);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $data = [
            'value' => $value,
            'expires' => $ttl > 0 ? time() + $ttl : 0,
            'created' => time()
        ];
        
        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }
    
    public function delete(string $key): bool
    {
        $file = $this->getFilePath($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }
    
    public function clear(): bool
    {
        return $this->deleteDirectory($this->cachePath, true);
    }
    
    public function has(string $key): bool
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return false;
        }
        
        $data = unserialize(file_get_contents($file));
        return $data['expires'] === 0 || time() <= $data['expires'];
    }
    
    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        $dir1 = substr($hash, 0, 2);
        $dir2 = substr($hash, 2, 2);
        
        return $this->cachePath . '/' . $dir1 . '/' . $dir2 . '/' . $hash . '.cache';
    }
    
    private function deleteDirectory(string $dir, bool $keepRoot = false): bool
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        if (!$keepRoot) {
            return rmdir($dir);
        }
        
        return true;
    }
}