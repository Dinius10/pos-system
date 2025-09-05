<?php
/**
 * Base Model Class
 */

require_once __DIR__ . '/../config/database.php';

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function all() {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $filteredData = $this->filterFillable($data);
        $columns = implode(',', array_keys($filteredData));
        $placeholders = ':' . implode(', :', array_keys($filteredData));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($filteredData)) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    public function update($id, $data) {
        $filteredData = $this->filterFillable($data);
        $setParts = [];
        
        foreach ($filteredData as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
        }
        
        $setClause = implode(', ', $setParts);
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
        
        $filteredData['id'] = $id;
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($filteredData);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    public function where($conditions) {
        $whereParts = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $whereParts[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }
        
        $whereClause = implode(' AND ', $whereParts);
        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function paginate($page = 1, $perPage = 10, $conditions = []) {
        $offset = ($page - 1) * $perPage;
        
        $whereClause = '';
        $params = [];
        
        if (!empty($conditions)) {
            $whereParts = [];
            foreach ($conditions as $key => $value) {
                $whereParts[] = "{$key} LIKE :{$key}";
                $params[$key] = "%{$value}%";
            }
            $whereClause = ' WHERE ' . implode(' AND ', $whereParts);
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}{$whereClause}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated data
        $sql = "SELECT * FROM {$this->table}{$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    protected function filterFillable($data) {
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    public function beginTransaction() {
        $this->db->beginTransaction();
    }
    
    public function commit() {
        $this->db->commit();
    }
    
    public function rollback() {
        $this->db->rollBack();
    }
}