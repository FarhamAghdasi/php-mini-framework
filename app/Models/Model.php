<?php
namespace App\Models;

use App\Core\Application;

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct()
    {
        $app = Application::getInstance();
        $this->db = $app->getService('database');
    }
    
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->query($sql, [$id])->fetch();
    }
    
    public function all(array $conditions = [], array $params = [])
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $where = implode(' AND ', $conditions);
            $sql .= " WHERE {$where}";
        }
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    public function create(array $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($sql, array_values($data));
        
        return $this->db->lastInsertId();
    }
    
    public function update($id, array $data)
    {
        $set = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $set[] = "{$column} = ?";
            $params[] = $value;
        }
        
        $params[] = $id;
        $setClause = implode(', ', $set);
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?";
        return $this->db->query($sql, $params)->rowCount();
    }
    
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->query($sql, [$id])->rowCount();
    }
}