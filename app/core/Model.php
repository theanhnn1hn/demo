<?php
namespace App\Core;

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    // Find a record by ID
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->single($sql, [$id]);
    }
    
    // Get all records
    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->all($sql);
    }
    
    // Get records with filters
    public function where($conditions, $values = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions}";
        return $this->db->all($sql, $values);
    }
    
    // Get a single record with filters
    public function firstWhere($conditions, $values = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions}";
        return $this->db->single($sql, $values);
    }
    
    // Insert a new record
    public function create($data)
    {
        $keys = array_keys($data);
        $fieldStr = implode(', ', $keys);
        $valueStr = implode(', ', array_fill(0, count($keys), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$fieldStr}) VALUES ({$valueStr})";
        
        $this->db->query($sql, array_values($data));
        return $this->db->lastInsertId();
    }
    
    // Update a record
    public function update($id, $data)
    {
        $keys = array_keys($data);
        $fieldUpdates = [];
        
        foreach($keys as $key) {
            $fieldUpdates[] = "{$key} = ?";
        }
        
        $fieldUpdatesStr = implode(', ', $fieldUpdates);
        
        $sql = "UPDATE {$this->table} SET {$fieldUpdatesStr} WHERE {$this->primaryKey} = ?";
        
        $values = array_values($data);
        $values[] = $id;
        
        return $this->db->query($sql, $values);
    }
    
    // Delete a record
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->query($sql, [$id]);
    }
    
    // Count total records
    public function count()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->single($sql);
        return $result['count'];
    }
    
    // Count records with condition
    public function countWhere($conditions, $values = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$conditions}";
        $result = $this->db->single($sql, $values);
        return $result['count'];
    }
    
    // Paginate results
    public function paginate($page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} LIMIT {$perPage} OFFSET {$offset}";
        $data = $this->db->all($sql);
        
        $totalCount = $this->count();
        $lastPage = ceil($totalCount / $perPage);
        
        return [
            'data' => $data,
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $totalCount
        ];
    }
    
    // Custom raw query
    public function query($sql, $params = [])
    {
        return $this->db->query($sql, $params);
    }
}
