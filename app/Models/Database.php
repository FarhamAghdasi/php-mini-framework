<?php
namespace App\Models;

use PDO;
use PDOException;

class Database
{
    private static $instance;
    private $connection;
    
    private function __construct(array $config)
    {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? '3306';
        $database = $config['database'] ?? '';
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';
        
        try {
            $dsn = "{$driver}:host={$host};port={$port};dbname={$database};charset={$charset}";
            
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false
            ]);
            
            // Set timezone if provided
            if (isset($config['timezone'])) {
                $this->connection->exec("SET time_zone = '{$config['timezone']}'");
            }
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance(array $config = null)
    {
        if (self::$instance === null && $config !== null) {
            self::$instance = new self($config);
        }
        
        return self::$instance;
    }
    
    public function getConnection(): PDO
    {
        return $this->connection;
    }
    
    public function query(string $sql, array $params = [])
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }
    
    public function commit(): bool
    {
        return $this->connection->commit();
    }
    
    public function rollback(): bool
    {
        return $this->connection->rollback();
    }
    
    public function tableExists(string $tableName): bool
    {
        $sql = "SELECT 1 FROM information_schema.tables WHERE table_name = ?";
        $stmt = $this->query($sql, [$tableName]);
        return $stmt->rowCount() > 0;
    }
}