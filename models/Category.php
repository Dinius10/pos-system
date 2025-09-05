<?php
/**
 * Category Model
 */

require_once 'Model.php';

class Category extends Model {
    protected $table = 'categories';
    protected $fillable = ['name', 'description', 'status'];
    
    public function getActive() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 1 ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getWithProductCount() {
        $sql = "SELECT c.*, COUNT(p.id) as product_count 
                FROM {$this->table} c 
                LEFT JOIN products p ON c.id = p.category_id AND p.status = 1
                WHERE c.status = 1
                GROUP BY c.id 
                ORDER BY c.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}