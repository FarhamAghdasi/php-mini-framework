<?php
namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;

abstract class Middleware
{
    abstract public function handle(Request $request, Response $response): bool;
}