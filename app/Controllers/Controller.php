<?php
namespace App\Controllers;

use App\Core\Application;
use App\Core\Request;
use App\Core\Response;
use App\Services\View;

abstract class Controller
{
    protected $app;
    protected $view;
    protected $logger;
    protected $cache;
    protected $security;
    
    public function __construct()
    {
        $this->app = Application::getInstance();
        $this->view = $this->app->getService('view');
        $this->logger = $this->app->getService('logger');
        $this->cache = $this->app->getService('cache');
        $this->security = $this->app->getService('security');
    }
    
    protected function json(array $data, int $statusCode = 200): void
    {
        $response = new Response();
        $response->setStatusCode($statusCode)->json($data);
    }
    
    protected function render(string $view, array $data = []): void
    {
        echo $this->view->render($view, $data);
    }
    
    protected function redirect(string $url, int $statusCode = 302): void
    {
        $response = new Response();
        $response->redirect($url, $statusCode);
    }
    
    protected function validate(Request $request, array $rules): array
    {
        return $this->security->validate($request->all(), $rules);
    }
}