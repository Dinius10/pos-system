<?php
/**
 * Authentication Controller
 */

require_once 'Controller.php';
require_once __DIR__ . '/../models/User.php';

class AuthController extends Controller {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function showLogin() {
        if (isLoggedIn()) {
            redirect(BASE_URL . 'dashboard/');
        }
        
        $data = [
            'csrf_token' => $this->generateCsrf(),
            'title' => 'Iniciar Sesión'
        ];
        
        $this->view('auth/login', $data);
    }
    
    public function login() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $this->validateCsrf();
            
            $username = sanitizeInput($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                throw new Exception('Usuario y contraseña son requeridos');
            }
            
            $user = $this->userModel->login($username, $password);
            
            if (!$user) {
                throw new Exception('Credenciales incorrectas');
            }
            
            // Create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            
            jsonResponse([
                'success' => true,
                'message' => 'Login exitoso',
                'redirect' => BASE_URL . 'dashboard/'
            ]);
            
        } catch (Exception $e) {
            jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    public function logout() {
        session_destroy();
        redirect(BASE_URL . 'auth/login.php');
    }
    
    public function checkSession() {
        if (!isLoggedIn()) {
            jsonResponse(['authenticated' => false], 401);
        }
        
        // Check session timeout (30 minutes)
        if (time() - $_SESSION['last_activity'] > 1800) {
            session_destroy();
            jsonResponse(['authenticated' => false, 'message' => 'Sesión expirada'], 401);
        }
        
        $_SESSION['last_activity'] = time();
        jsonResponse(['authenticated' => true]);
    }
}
