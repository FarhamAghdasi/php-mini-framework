<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

class HomeController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $data = [
            'title' => 'Welcome to Mini Framework',
            'message' => 'Hello from MVC Framework!'
        ];
        
        $this->render('home/index', $data);
    }
    
    public function about(Request $request, Response $response)
    {
        $this->render('home/about');
    }
    
    public function getData(Request $request, Response $response)
    {
        // Check cache first
        $cacheKey = 'api_data';
        if ($data = $this->cache->get($cacheKey)) {
            return $this->json($data);
        }
        
        // Generate data
        $data = [
            'timestamp' => time(),
            'data' => ['item1', 'item2', 'item3']
        ];
        
        // Cache for 5 minutes
        $this->cache->set($cacheKey, $data, 300);
        
        $this->json($data);
    }
}