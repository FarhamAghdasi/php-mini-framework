<?php
namespace App\Core;

class Session
{
    private $started = false;
    
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_secure' => isset($_SERVER['HTTPS']),
                'cookie_httponly' => true,
                'cookie_samesite' => 'Strict'
            ]);
            $this->started = true;
        }
    }
    
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }
    
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }
    
    public function destroy(): void
    {
        if ($this->started) {
            session_destroy();
            $_SESSION = [];
        }
    }
    
    public function regenerate(bool $deleteOld = true): void
    {
        session_regenerate_id($deleteOld);
    }
    
    public function flash(string $key, $value = null)
    {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        
        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
    
    public function all(): array
    {
        return $_SESSION;
    }
}