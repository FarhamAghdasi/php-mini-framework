<?php
use App\Controllers\HomeController;
use App\Middlewares\CsrfMiddleware;
use App\Middlewares\RateLimitMiddleware;

$router->get('/', [HomeController::class, 'index']);
$router->get('/about', [HomeController::class, 'about']);

// Protected routes
$router->group('/admin', function($router) {
    $router->get('/dashboard', [HomeController::class, 'dashboard']);
    $router->get('/users', [HomeController::class, 'users']);
}, ['AuthMiddleware']);

// Form routes with CSRF protection
$router->group('/form', function($router) {
    $router->get('/contact', [HomeController::class, 'contact']);
    $router->post('/contact', [HomeController::class, 'submitContact']);
}, [CsrfMiddleware::class]);

// API routes with rate limiting
$router->group('/api', function($router) {
    $router->get('/data', [HomeController::class, 'getData']);
    $router->post('/submit', [HomeController::class, 'submitData']);
}, [RateLimitMiddleware::class]);