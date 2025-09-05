<?php
/**
 * Product Controller
 */

require_once 'Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

class ProductController extends Controller {
    private $productModel;
    private $categoryModel;
    
    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }
    
    public function index() {
        $this->requireLogin();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
        
        $conditions = [];
        if ($search) {
            $conditions['name'] = $search;
        }
        
        $products = $this->productModel->paginate($page, RECORDS_PER_PAGE, $conditions);
        $categories = $this->categoryModel->getActive();
        
        $data = [
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'title' => 'Gestión de Productos'
        ];
        
        $this->view('products/index', $data);
    }
    
    public function create() {
        $this->requireLogin();
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $this->validateCsrf();
            
            $data = [
                'code' => sanitizeInput($_POST['code']),
                'name' => sanitizeInput($_POST['name']),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'price' => (float)$_POST['price'],
                'stock' => (int)$_POST['stock'],
                'min_stock' => (int)($_POST['min_stock'] ?? 5),
                'category_id' => (int)$_POST['category_id'],
                'status' => 1
            ];
            
            // Validation
            if (empty($data['code']) || empty($data['name']) || $data['price'] <= 0) {
                throw new Exception('Código, nombre y precio son requeridos');
            }
            
            $productId = $this->productModel->create($data);
            
            if ($productId) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Producto creado exitosamente',
                    'product_id' => $productId
                ]);
            } else {
                throw new Exception('Error al crear el producto');
            }
            
        } catch (Exception $e) {
            jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    public function update() {
        $this->requireLogin();
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $this->validateCsrf();
            
            $productId = (int)$_POST['product_id'];
            
            $data = [
                'code' => sanitizeInput($_POST['code']),
                'name' => sanitizeInput($_POST['name']),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'price' => (float)$_POST['price'],
                'stock' => (int)$_POST['stock'],
                'min_stock' => (int)($_POST['min_stock'] ?? 5),
                'category_id' => (int)$_POST['category_id']
            ];
            
            if (empty($data['code']) || empty($data['name']) || $data['price'] <= 0) {
                throw new Exception('Código, nombre y precio son requeridos');
            }
            
            $success = $this->productModel->update($productId, $data);
            
            if ($success) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Producto actualizado exitosamente'
                ]);
            } else {
                throw new Exception('Error al actualizar el producto');
            }
            
        } catch (Exception $e) {
            jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    public function delete() {
        $this->requireLogin();
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $this->validateCsrf();
            
            $productId = (int)$_POST['product_id'];
            
            // Soft delete by updating status
            $success = $this->productModel->update($productId, ['status' => 0]);
            
            if ($success) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Producto eliminado exitosamente'
                ]);
            } else {
                throw new Exception('Error al eliminar el producto');
            }
            
        } catch (Exception $e) {
            jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    public function search() {
        $this->requireLogin();
        
        $search = sanitizeInput($_GET['search'] ?? '');
        
        if (empty($search)) {
            jsonResponse(['products' => []]);
        }
        
        $products = $this->productModel->searchProducts($search);
        
        jsonResponse(['products' => $products]);
    }
    
    public function getProduct() {
        $this->requireLogin();
        
        $productId = (int)$_GET['id'];
        $product = $this->productModel->find($productId);
        
        if ($product) {
            jsonResponse(['product' => $product]);
        } else {
            jsonResponse(['error' => 'Producto no encontrado'], 404);
        }
    }
}