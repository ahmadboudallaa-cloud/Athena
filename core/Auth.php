<?php

require_once __DIR__ . '/../config/database.php';

class Auth
{
    private PDO $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = Database::connect();
    }

    public function register($name, $email, $password, $role): bool
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)";
        return $this->db->prepare($sql)->execute([$name,$email,$hash,$role]);
    }

    public function login($email, $password): bool
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email=? AND active=1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password,$user['password'])) {
            $_SESSION['user'] = $user;
            return true;
        }
        return false;
    }

    public function user()
    {
        return $_SESSION['user'] ?? null;
    }

    public function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public function logout()
    {
        session_destroy();
        header("Location: login.php");
        exit;
    }
}
