<?php
use App\Core\Application;
use App\Core\Router;
use App\Core\Request;
use App\Core\Response;

// Serve static files directly
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot', 'pdf'];

// Check if request is for a static file
foreach ($staticExtensions as $ext) {
    if (preg_match('/\.' . $ext . '$/i', $requestUri)) {
        $filePath = PUBLIC_PATH . $requestUri;
        if (file_exists($filePath)) {
            $mimeTypes = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'ico' => 'image/x-icon',
                'svg' => 'image/svg+xml',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
                'eot' => 'application/vnd.ms-fontobject',
                'pdf' => 'application/pdf'
            ];
            
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
            
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        }
        break;
    }
}

// Create application instance
$app = Application::getInstance();

// Set base path
$app->setBasePath(ROOT_PATH);

// Load configuration
$config = require CONFIG_PATH . '/app.php';
$app->loadConfig($config);

// Initialize services
$app->initServices();

// Create router
$router = new Router();

// Load routes
require APP_PATH . '/routes/web.php';
require APP_PATH . '/routes/api.php';
require APP_PATH . '/routes/cron.php';

// Set router to application
$app->setRouter($router);

// Return application instance
return $app;