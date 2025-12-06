<?php
namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\Application;

class RateLimitMiddleware extends Middleware
{
    private $limits = [
        'default' => ['requests' => 100, 'period' => 60], // 100 requests per minute
        'api' => ['requests' => 60, 'period' => 60],
        'login' => ['requests' => 5, 'period' => 60],
    ];
    
    public function handle(Request $request, Response $response): bool
    {
        $app = Application::getInstance();
        $cache = $app->getService('cache');
        
        $clientIp = $request->getIp();
        $path = $request->getPath();
        
        // Determine limit type
        $limitType = $this->getLimitType($path);
        $limit = $this->limits[$limitType];
        
        $key = "rate_limit:{$clientIp}:{$limitType}";
        
        // Get current count
        $data = $cache->get($key) ?: ['count' => 0, 'reset' => time() + $limit['period']];
        
        // Reset if period passed
        if (time() > $data['reset']) {
            $data = ['count' => 0, 'reset' => time() + $limit['period']];
        }
        
        // Check limit
        if ($data['count'] >= $limit['requests']) {
            $response->setHeaders([
                'X-RateLimit-Limit' => $limit['requests'],
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => $data['reset']
            ])->setStatusCode(429)->json([
                'error' => 'Too many requests'
            ]);
            return false;
        }
        
        // Increment count
        $data['count']++;
        $cache->set($key, $data, $limit['period']);
        
        // Add headers
        $response->setHeaders([
            'X-RateLimit-Limit' => $limit['requests'],
            'X-RateLimit-Remaining' => $limit['requests'] - $data['count'],
            'X-RateLimit-Reset' => $data['reset']
        ]);
        
        return true;
    }
    
    private function getLimitType(string $path): string
    {
        if (strpos($path, '/api/') === 0) return 'api';
        if (strpos($path, '/login') !== false) return 'login';
        return 'default';
    }
}