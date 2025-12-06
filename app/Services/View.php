<?php
namespace App\Services;

class View
{
    private $viewPath;
    private $cachePath;
    private $config;
    
    public function __construct(string $viewPath, string $cachePath, array $config = [])
    {
        $this->viewPath = $viewPath;
        $this->cachePath = $cachePath;
        $this->config = $config;
        
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
    }
    
    public function render(string $template, array $data = []): string
    {
        $templateFile = $this->viewPath . '/' . $template . '.php';
        
        if (!file_exists($templateFile)) {
            throw new \Exception("View template not found: {$template}");
        }
        
        // Extract data to variables
        extract($data, EXTR_SKIP);
        
        // Start output buffering
        ob_start();
        
        // Include template
        include $templateFile;
        
        // Get contents
        $content = ob_get_clean();
        
        // Apply layout if exists
        if (isset($layout)) {
            $layoutFile = $this->viewPath . '/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                ob_start();
                include $layoutFile;
                $content = ob_get_clean();
            }
        }
        
        return $content;
    }
    
    public function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    public function e(string $value): string
    {
        return $this->escape($value);
    }
}