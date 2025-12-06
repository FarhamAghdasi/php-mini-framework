<?php
namespace App\Core;

use App\Services\Logger;
use App\Services\Cache;
use App\Services\View;
use App\Services\Security;

/**
 * Application Container Class
 */
class Application implements ApplicationInterface
{
    /**
     * @var ApplicationInterface|null Singleton instance
     */
    private static $instance = null;
    
    private array $config = [];
    private array $services = [];
    private string $basePath = '';
    private ?Router $router = null;
    private ?Request $request = null;
    private ?Response $response = null;
    private ?Session $session = null;
    
    // Prevent instantiation
    final private function __construct() {}
    final private function __clone() {}
    final private function __wakeup() {}
    
    /**
     * Get singleton instance
     * 
     * @return ApplicationInterface
     */
    public static function getInstance(): ApplicationInterface
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function setBasePath(string $path): void
    {
        $this->basePath = $path;
    }
    
    public function getBasePath(): string
    {
        return $this->basePath;
    }
    
    public function loadConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }
    
    public function getConfig(?string $key = null)
    {
        if ($key === null) {
            return $this->config;
        }
        
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $segment) {
            if (isset($value[$segment])) {
                $value = $value[$segment];
            } else {
                return null;
            }
        }
        
        return $value;
    }
    
    public function initServices(): void
    {
        // Initialize Logger
        $this->services['logger'] = new Logger(
            $this->basePath . '/storage/logs',
            $this->getConfig('logger') ?? []
        );
        
        // Initialize Cache
        $this->services['cache'] = new Cache(
            $this->basePath . '/storage/cache',
            $this->getConfig('cache') ?? []
        );
        
        // Initialize View
        $this->services['view'] = new View(
            $this->basePath . '/resources/views',
            $this->basePath . '/storage/views',
            $this->getConfig('view') ?? []
        );
        
        // Initialize Security
        $this->services['security'] = new Security(
            $this->getConfig('security') ?? []
        );
        
        // Initialize Database if enabled
        if (($this->getConfig('database.enabled') ?? false) && 
            file_exists($this->basePath . '/config/database.php')) {
            $dbConfig = require $this->basePath . '/config/database.php';
            $this->services['database'] = \App\Models\Database::getInstance($dbConfig);
        }
        
        // Initialize Session
        $this->session = new Session();
        $this->services['session'] = $this->session;
        
        // Initialize Request & Response
        $this->request = new Request();
        $this->response = new Response();
        $this->services['request'] = $this->request;
        $this->services['response'] = $this->response;
    }
    
    public function getService(string $name)
    {
        return $this->services[$name] ?? null;
    }
    
    public function setRouter(Router $router): void
    {
        $this->router = $router;
    }
    
    public function getRouter(): ?Router
    {
        return $this->router;
    }
    
    public function getRequest(): ?Request
    {
        return $this->request;
    }
    
    public function getResponse(): ?Response
    {
        return $this->response;
    }
    
    public function getSession(): ?Session
    {
        return $this->session;
    }
    
    public function run(): void
    {
        if (!$this->router) {
            throw new \RuntimeException('Router not initialized');
        }
        
        try {
            $this->router->dispatch($this->request, $this->response);
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }
    
    private function handleException(\Throwable $e): void
    {
        /** @var Logger|null $logger */
        $logger = $this->getService('logger');
        
        if ($logger) {
            $logger->error('Application Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        $debug = $this->getConfig('app.debug') ?? false;
        
        if ($debug) {
            $this->response->setStatusCode(500)->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        } else {
            $this->response->setStatusCode(500)->json([
                'error' => 'Internal Server Error'
            ]);
        }
    }
}