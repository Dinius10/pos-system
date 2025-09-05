<?php
/**
 * User Model
 */

require_once 'Model.php';

class User extends Model {
    protected $table = 'users';
    protected $fillable = ['username', 'password', 'full_name', 'email', 'role', 'status'];
    
    public function login($username, $password) {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username AND status = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    public function create($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return parent::create($data);
    }
    
    public function update($id, $data) {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        return parent::update($id, $data);
    }
    
    public function getActiveUsers() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 1 ORDER BY full_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}