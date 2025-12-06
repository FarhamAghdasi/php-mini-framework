<?php
namespace App\Services;

class Security
{
    private $config;
    
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }
    
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, [
            'cost' => $this->config['bcrypt_cost'] ?? 12
        ]);
    }
    
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    public function generateCsrfToken(): string
    {
        return bin2hex(random_bytes(32));
    }
    
    public function verifyCsrfToken(string $token, $session): bool
    {
        $storedToken = $session->get('csrf_token');
        return hash_equals($storedToken ?? '', $token);
    }
    
    public function sanitize(string $input): string
    {
        // Remove HTML tags
        $input = strip_tags($input);
        
        // Convert special characters
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        // Remove control characters
        $input = preg_replace('/[\x00-\x1F\x7F]/u', '', $input);
        
        return trim($input);
    }
    
    public function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $ruleSet) {
            $rules = explode('|', $ruleSet);
            $value = $data[$field] ?? null;
            
            foreach ($rules as $rule) {
                if ($rule === 'required' && empty($value)) {
                    $errors[$field][] = "The {$field} field is required.";
                }
                
                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "The {$field} must be a valid email address.";
                }
                
                if (strpos($rule, 'min:') === 0) {
                    $min = (int) substr($rule, 4);
                    if (strlen($value) < $min) {
                        $errors[$field][] = "The {$field} must be at least {$min} characters.";
                    }
                }
                
                if (strpos($rule, 'max:') === 0) {
                    $max = (int) substr($rule, 4);
                    if (strlen($value) > $max) {
                        $errors[$field][] = "The {$field} may not be greater than {$max} characters.";
                    }
                }
            }
        }
        
        return $errors;
    }
    
    public function generateRandomString(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }
}