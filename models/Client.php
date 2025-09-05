<?php
/**
 * Client Model
 */

require_once 'Model.php';

class Client extends Model {
    protected $table = 'clients';
    protected $fillable = ['code', 'name', 'ci_nit', 'phone', 'email', 'address', 'status'];
    
    public function getActive() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 1 ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function searchClients($search) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 1 AND (
                    code LIKE :search OR 
                    name LIKE :search OR 
                    ci_nit LIKE :search OR 
                    phone LIKE :search
                ) 
                ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['search' => "%{$search}%"]);
        return $stmt->fetchAll();
    }
    
    public function getTopClients($limit = 5) {
        $sql = "SELECT c.*, COUNT(s.id) as total_purchases, SUM(s.total) as total_spent
                FROM {$this->table} c 
                INNER JOIN sales s ON c.id = s.client_id
                WHERE c.status = 1 AND s.status = 'completed'
                GROUP BY c.id 
                ORDER BY total_spent DESC 
                LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}