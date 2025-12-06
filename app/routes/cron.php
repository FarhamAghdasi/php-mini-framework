<?php
use App\Controllers\CronController;

$router->group('/cron', function($router) {
    $router->post('/run-job', [CronController::class, 'runJob']);
    $router->post('/cleanup', [CronController::class, 'cleanup']);
    $router->post('/backup', [CronController::class, 'backup']);
});