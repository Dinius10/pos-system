<?php
/**
 * Sale Model
 */

require_once 'Model.php';

class Sale extends Model {
    protected $table = 'sales';
    protected $fillable = ['code', 'client_id', 'user_id', 'subtotal', 'discount', 'tax', 'total', 'payment_method', 'status'];
    
    public function getSalesWithDetails() {
        $sql = "SELECT s.*, c.name as client_name, u.full_name as user_name
                FROM {$this->table} s 
                LEFT JOIN clients c ON s.client_id = c.id
                INNER JOIN users u ON s.user_id = u.id
                ORDER BY s.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getSaleDetails($saleId) {
        $sql = "SELECT sd.*, p.name as product_name
                FROM sale_details sd
                LEFT JOIN products p ON sd.product_id = p.id
                WHERE sd.sale_id = :sale_id
                ORDER BY sd.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['sale_id' => $saleId]);
        return $stmt->fetchAll();
    }
    
    public function createSaleWithDetails($saleData, $saleDetails, $userId) {
        try {
            $this->beginTransaction();
            
            // Generate sale code
            $saleData['code'] = $this->generateSaleCode();
            $saleData['user_id'] = $userId;
            
            // Create sale
            $saleId = $this->create($saleData);
            
            if (!$saleId) {
                throw new Exception('Error creating sale');
            }
            
            // Create sale details and update stock
            $productModel = new Product();
            
            foreach ($saleDetails as $detail) {
                $detailData = [
                    'sale_id' => $saleId,
                    'product_id' => $detail['product_id'],
                    'product_code' => $detail['product_code'],
                    'product_name' => $detail['product_name'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'subtotal' => $detail['subtotal']
                ];
                
                // Insert sale detail
                $sql = "INSERT INTO sale_details (sale_id, product_id, product_code, product_name, quantity, unit_price, subtotal) 
                        VALUES (:sale_id, :product_id, :product_code, :product_name, :quantity, :unit_price, :subtotal)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($detailData);
                
                // Update product stock
                $product = $productModel->find($detail['product_id']);
                $newStock = $product['stock'] - $detail['quantity'];
                
                $productModel->updateStock(
                    $detail['product_id'],
                    $newStock,
                    $userId,
                    'salida',
                    $detail['quantity'],
                    'sale',
                    $saleId
                );
            }
            
            $this->commit();
            return $saleId;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    private function generateSaleCode() {
        $date = date('Ymd');
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetch()['count'] + 1;
        
        return 'VNT' . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
    
    public function getDailySales($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $sql = "SELECT COUNT(*) as total_sales, COALESCE(SUM(total), 0) as total_amount
                FROM {$this->table} 
                WHERE DATE(sale_date) = :date AND status = 'completed'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['date' => $date]);
        return $stmt->fetch();
    }
    
    public function getMonthlySales($year = null, $month = null) {
        if (!$year) $year = date('Y');
        if (!$month) $month = date('m');
        
        $sql = "SELECT DATE(sale_date) as date, COUNT(*) as sales_count, SUM(total) as daily_total
                FROM {$this->table} 
                WHERE YEAR(sale_date) = :year AND MONTH(sale_date) = :month AND status = 'completed'
                GROUP BY DATE(sale_date)
                ORDER BY date";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $year, 'month' => $month]);
        return $stmt->fetchAll();
    }
    
    public function getSalesByDateRange($startDate, $endDate) {
        $sql = "SELECT s.*, c.name as client_name, u.full_name as user_name
                FROM {$this->table} s 
                LEFT JOIN clients c ON s.client_id = c.id
                INNER JOIN users u ON s.user_id = u.id
                WHERE DATE(s.sale_date) BETWEEN :start_date AND :end_date 
                AND s.status = 'completed'
                ORDER BY s.sale_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetchAll();
    }
    
    public function getPaymentMethodStats($startDate = null, $endDate = null) {
        $whereClause = "WHERE status = 'completed'";
        $params = [];
        
        if ($startDate && $endDate) {
            $whereClause .= " AND DATE(sale_date) BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }
        
        $sql = "SELECT payment_method, COUNT(*) as count, SUM(total) as total_amount
                FROM {$this->table} 
                {$whereClause}
                GROUP BY payment_method
                ORDER BY total_amount DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}