<?php
class Database {
    private static $instance = null;
    private $connection;

    private $host = 'localhost';
    private $db   = 'scrum_platform';
    private $user = 'root';
    private $pass = '';

    private function __construct(){
        try {
            $this->connection = new PDO(
                "mysql:host=$this->host;dbname=$this->db;charset=utf8",
                $this->user, $this->pass
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            die("Erreur DB : ".$e->getMessage());
        }
    }

    public static function getInstance(){
        if(!self::$instance){
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(){
        return $this->connection;
    }
}
