<?php
namespace App\Core;

class Response
{
    private $statusCode = 200;
    private $headers = [];
    private $body;
    
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        http_response_code($code);
        return $this;
    }
    
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        header("{$name}: {$value}");
        return $this;
    }
    
    public function setHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
        return $this;
    }
    
    public function json(array $data, int $statusCode = null): void
    {
        if ($statusCode !== null) {
            $this->setStatusCode($statusCode);
        }
        
        $this->setHeader('Content-Type', 'application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    public function html(string $content, int $statusCode = null): void
    {
        if ($statusCode !== null) {
            $this->setStatusCode($statusCode);
        }
        
        $this->setHeader('Content-Type', 'text/html; charset=utf-8');
        echo $content;
        exit;
    }
    
    public function redirect(string $url, int $statusCode = 302): void
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Location', $url);
        exit;
    }
    
    public function download(string $filePath, string $fileName = null): void
    {
        if (!file_exists($filePath)) {
            $this->setStatusCode(404)->json(['error' => 'File not found']);
        }
        
        $fileName = $fileName ?? basename($filePath);
        $fileSize = filesize($filePath);
        
        $this->setHeaders([
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length' => $fileSize,
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'public'
        ]);
        
        readfile($filePath);
        exit;
    }
    
    public function withCookie(string $name, string $value, int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true): self
    {
        setcookie($name, $value, [
            'expires' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httpOnly,
            'samesite' => 'Strict'
        ]);
        
        return $this;
    }
}