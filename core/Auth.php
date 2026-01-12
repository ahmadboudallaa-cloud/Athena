<?php
session_start();
require_once __DIR__.'/Database.php';

class Auth {
    private $db;

    public function __construct(){
        $this->db = Database::getInstance()->getConnection();
    }

    public function login($email, $password){
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email=? AND active=1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if($user && password_verify($password, $user['password'])){
            $_SESSION['user'] = $user;
            return true;
        }
        return false;
    }

    public function logout(){
        session_destroy();
    }

    public static function check(){
        return isset($_SESSION['user']);
    }
}
