<?php
namespace App\Jobs;

class ExampleJob
{
    public function handle(): array
    {
        // Your job logic here
        $result = [
            'status' => 'success',
            'message' => 'Job executed successfully',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Example: Clean up old cache files
        $cacheDir = STORAGE_PATH . '/cache';
        $this->cleanOldCache($cacheDir);
        
        return $result;
    }
    
    private function cleanOldCache(string $cacheDir, int $maxAge = 86400): void
    {
        if (!is_dir($cacheDir)) {
            return;
        }
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        $now = time();
        
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                if ($now - $file->getMTime() > $maxAge) {
                    unlink($file->getRealPath());
                }
            }
        }
    }
}