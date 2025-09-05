<?php
/**
 * Client Controller
 */

require_once 'Controller.php';
require_once __DIR__ . '/../models/Client.php';

class ClientController extends Controller {
    private $clientModel;
    
    public function __construct() {
        $this->clientModel = new Client();
    }
    
    public function index() {
        $this->requireLogin();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
        
        $conditions = [];
        if ($search) {
            $conditions['name'] = $search;
        }
        
        $clients = $this->clientModel->paginate($page, RECORDS_PER_PAGE, $conditions);
        
        $data = [
            'clients' => $clients,
            'search' => $search,
            'title' => 'Gestión de Clientes'
        ];
        
        $this->view('clients/index', $data);
    }
    
    public function create() {
        $this->requireLogin();
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $this->validateCsrf();
            
            $data = [
                'code' => $this->generateClientCode(),
                'name' => sanitizeInput($_POST['name']),
                'ci_nit' => sanitizeInput($_POST['ci_nit'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'address' => sanitizeInput($_POST['address'] ?? ''),
                'status' => 1
            ];
            
            if (empty($data['name'])) {
                throw new Exception('El nombre es requerido');
            }
            
            $clientId = $this->clientModel->create($data);
            
            if ($clientId) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Cliente creado exitosamente',
                    'client_id' => $clientId
                ]);
            } else {
                throw new Exception('Error al crear el cliente');
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
            
            $clientId = (int)$_POST['client_id'];
            
            $data = [
                'name' => sanitizeInput($_POST['name']),
                'ci_nit' => sanitizeInput($_POST['ci_nit'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'address' => sanitizeInput($_POST['address'] ?? '')
            ];
            
            if (empty($data['name'])) {
                throw new Exception('El nombre es requerido');
            }
            
            $success = $this->clientModel->update($clientId, $data);
            
            if ($success) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Cliente actualizado exitosamente'
                ]);
            } else {
                throw new Exception('Error al actualizar el cliente');
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
            
            $clientId = (int)$_POST['client_id'];
            
            // Soft delete
            $success = $this->clientModel->update($clientId, ['status' => 0]);
            
            if ($success) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Cliente eliminado exitosamente'
                ]);
            } else {
                throw new Exception('Error al eliminar el cliente');
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
            jsonResponse(['clients' => []]);
        }
        
        $clients = $this->clientModel->searchClients($search);
        
        jsonResponse(['clients' => $clients]);
    }
    
    public function getClient() {
        $this->requireLogin();
        
        $clientId = (int)$_GET['id'];
        $client = $this->clientModel->find($clientId);
        
        if ($client) {
            jsonResponse(['client' => $client]);
        } else {
            jsonResponse(['error' => 'Cliente no encontrado'], 404);
        }
    }
    
    private function generateClientCode() {
        $lastClient = $this->clientModel->db->query("SELECT code FROM clients ORDER BY id DESC LIMIT 1")->fetch();
        
        if ($lastClient) {
            $lastNumber = (int)substr($lastClient['code'], 3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return 'CLI' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}