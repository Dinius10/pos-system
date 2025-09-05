<?php
/**
 * Base Controller Class
 */

abstract class Controller {
    
    protected function view($viewName, $data = []) {
        extract($data);
        
        $viewFile = __DIR__ . '/../views/' . $viewName . '.php';
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View file not found: {$viewName}");
        }
    }
    
    protected function layout($layoutName, $content, $data = []) {
        $data['content'] = $content;
        $this->view("layouts/{$layoutName}", $data);
    }
    
    protected function requireLogin() {
        if (!isLoggedIn()) {
            redirect('/auth/login.php');
        }
    }
    
    protected function requireAdmin() {
        $this->requireLogin();
        if (!isAdmin()) {
            redirect('/dashboard/');
        }
    }
    
    protected function validateCsrf() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }
    }


    
    protected function generateCsrf() {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
}