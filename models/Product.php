<?php
/**
 * Product Model
 */

require_once 'Model.php';

class Product extends Model {
    protected $table = 'products';
    protected $fillable = ['code', 'name', 'description', 'price', 'stock', 'min_stock', 'category_id', 'status'];
    
    public function getWithCategory() {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 1 
                ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function searchProducts($search) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 1 AND (
                    p.code LIKE :search OR 
                    p.name LIKE :search OR 
                    c.name LIKE :search
                ) 
                ORDER BY p.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['search' => "%{$search}%"]);
        return $stmt->fetchAll();
    }
    
    public function getLowStock() {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 1 AND p.stock <= p.min_stock 
                ORDER BY p.stock ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function updateStock($productId, $newStock, $userId, $movementType, $quantity, $referenceType, $referenceId = null) {
        try {
            $this->beginTransaction();
            
            // Get current stock
            $product = $this->find($productId);
            $previousStock = $product['stock'];
            
            // Update product stock
            $this->update($productId, ['stock' => $newStock]);
            
            // Record stock movement
            $movementData = [
                'product_id' => $productId,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'user_id' => $userId
            ];
            
            $sql = "INSERT INTO stock_movements (product_id, movement_type, quantity, previous_stock, new_stock, reference_type, reference_id, user_id) 
                    VALUES (:product_id, :movement_type, :quantity, :previous_stock, :new_stock, :reference_type, :reference_id, :user_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($movementData);
            
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    public function getTopSelling($limit = 5) {
        $sql = "SELECT p.*, SUM(sd.quantity) as total_sold, SUM(sd.subtotal) as total_revenue
                FROM {$this->table} p 
                INNER JOIN sale_details sd ON p.id = sd.product_id
                INNER JOIN sales s ON sd.sale_id = s.id
                WHERE p.status = 1 AND s.status = 'completed'
                GROUP BY p.id 
                ORDER BY total_sold DESC 
                LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}