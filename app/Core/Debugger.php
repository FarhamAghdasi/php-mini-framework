<?php
namespace App\Core;

class Debugger
{
    private static $errors = [];
    private static $startTime;
    
    public static function start(): void
    {
        self::$startTime = microtime(true);
        self::$errors = [];
        
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        
        // تنظیم هندلرهای خطا
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    public static function checkSyntax(string $file): array
    {
        $output = [];
        exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $returnCode);
        
        return [
            'file' => $file,
            'valid' => $returnCode === 0,
            'output' => implode("\n", $output),
            'errors' => $returnCode !== 0 ? $output : []
        ];
    }
    
    public static function checkProjectSyntax(): array
    {
        $results = [];
        
        $directory = new \RecursiveDirectoryIterator(
            ROOT_PATH, 
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        $iterator = new \RecursiveIteratorIterator($directory);
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $results[] = self::checkSyntax($file->getRealPath());
            }
        }
        
        return $results;
    }
    
    public static function handleError($errno, $errstr, $errfile, $errline): bool
    {
        self::$errors[] = [
            'type' => 'Error',
            'code' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'time' => microtime(true)
        ];
        
        try {
            $app = Application::getInstance();
            $logger = $app->getService('logger');
            if ($logger) {
                $logger->error("PHP Error: {$errstr}", [
                    'file' => $errfile,
                    'line' => $errline
                ]);
            }
        } catch (\Exception $e) {
        }
        
        return true;
    }
    
    public static function handleException(\Throwable $e): void
    {
        self::$errors[] = [
            'type' => 'Exception',
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'time' => microtime(true)
        ];
    }
    
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::$errors[] = [
                'type' => 'Fatal Error',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'time' => microtime(true)
            ];
        }
        
        if (!empty(self::$errors)) {
            self::saveDebugLog();
        }
    }
    
    public static function saveDebugLog(): void
    {
        $logDir = ROOT_PATH . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'execution_time' => microtime(true) - self::$startTime,
            'memory_usage' => memory_get_peak_usage(true) / 1024 / 1024 . ' MB',
            'errors' => self::$errors
        ];
        
        $logFile = $logDir . '/debug_' . date('Y-m-d') . '.json';
        file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
    }
    
    public static function getStats(): array
    {
        return [
            'execution_time' => microtime(true) - self::$startTime,
            'memory_peak' => memory_get_peak_usage(true),
            'error_count' => count(self::$errors),
            'errors' => self::$errors
        ];
    }
    
    public static function getErrors(): array
    {
        return self::$errors;
    }
}