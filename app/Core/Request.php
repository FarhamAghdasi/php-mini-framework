<?php
namespace App\Core;

class Request
{
    private $method;
    private $uri;
    private $headers = [];
    private $params = [];
    private $body = [];
    private $query = [];
    private $files = [];
    private $cookies = [];
    private $ip;
    
    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $this->headers = $this->getAllHeaders();
        $this->query = $_GET;
        $this->body = $_POST;
        $this->files = $_FILES;
        $this->cookies = $_COOKIE;
        $this->params = array_merge($this->query, $this->body);
    }
    
    public function getMethod(): string
    {
        return $this->method;
    }
    
    public function getPath(): string
    {
        $path = parse_url($this->uri, PHP_URL_PATH);
        return $path ?: '/';
    }
    
    public function getUri(): string
    {
        return $this->uri;
    }
    
    public function getIp(): string
    {
        return $this->ip;
    }
    
    public function getHeader(string $name, $default = null)
    {
        $name = strtolower($name);
        return $this->headers[$name] ?? $default;
    }
    
    public function getHeaders(): array
    {
        return $this->headers;
    }
    
    private function getAllHeaders(): array
    {
        $headers = [];
        
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[strtolower($header)] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $header = str_replace('_', '-', $key);
                $headers[strtolower($header)] = $value;
            }
        }
        
        return $headers;
    }
    
    public function input(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }
    
    public function all(): array
    {
        return $this->params;
    }
    
    public function query(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }
    
    public function post(string $key, $default = null)
    {
        return $this->body[$key] ?? $default;
    }
    
    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }
    
    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }
    
    public function has(string $key): bool
    {
        return isset($this->params[$key]);
    }
    
    public function isAjax(): bool
    {
        return $this->getHeader('x-requested-with') === 'XMLHttpRequest';
    }
    
    public function isJson(): bool
    {
        $contentType = $this->getHeader('content-type', '');
        return strpos($contentType, 'application/json') !== false;
    }
    
    public function json()
    {
        if ($this->isJson()) {
            $input = file_get_contents('php://input');
            return json_decode($input, true);
        }
        return [];
    }
}