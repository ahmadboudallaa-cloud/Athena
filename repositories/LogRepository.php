<?php

require_once __DIR__ . '/../config/database.php';

class LogRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function add(?int $userId, string $action, string $description): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO logs (user_id, action, description) VALUES (?, ?, ?)"
        );
        $stmt->execute([$userId, $action, $description]);
    }

    public function all(): array
    {
        $stmt = $this->db->query(
            "SELECT l.*, u.name 
             FROM logs l 
             LEFT JOIN users u ON u.id = l.user_id
             ORDER BY l.created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
