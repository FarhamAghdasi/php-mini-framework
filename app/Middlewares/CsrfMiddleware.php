<?php
namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\Application;

class CsrfMiddleware extends Middleware
{
    public function handle(Request $request, Response $response): bool
    {
        $app = Application::getInstance();
        $session = $app->getService('session');
        $security = $app->getService('security');
        
        // Skip for GET, HEAD, OPTIONS
        if (in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'])) {
            return true;
        }
        
        $token = $request->input('_token') ?: $request->getHeader('X-CSRF-Token');
        
        if (!$security->verifyCsrfToken($token, $session)) {
            $response->setStatusCode(419)->json(['error' => 'CSRF token mismatch']);
            return false;
        }
        
        return true;
    }
}