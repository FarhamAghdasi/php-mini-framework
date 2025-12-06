<?php
namespace App\Services;

class Logger
{
    private $logPath;
    private $config;
    
    public function __construct(string $logPath, array $config = [])
    {
        $this->logPath = $logPath;
        $this->config = $config;
        
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }
    }
    
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }
    
    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }
    
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }
    
    public function debug(string $message, array $context = []): void
    {
        if ($this->config['debug'] ?? false) {
            $this->log('DEBUG', $message, $context);
        }
    }
    
    private function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}";
        
        if (!empty($context)) {
            $logMessage .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        $logMessage .= PHP_EOL;
        
        $logFile = $this->logPath . '/' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        
        // Also log to syslog if configured
        if ($this->config['syslog'] ?? false) {
            $syslogLevel = $this->getSyslogLevel($level);
            syslog($syslogLevel, $message);
        }
    }
    
    private function getSyslogLevel(string $level): int
    {
        $levels = [
            'DEBUG' => LOG_DEBUG,
            'INFO' => LOG_INFO,
            'WARNING' => LOG_WARNING,
            'ERROR' => LOG_ERR
        ];
        
        return $levels[$level] ?? LOG_INFO;
    }
}