<?php

class TaskRepository
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function all()
    {
        $stmt = $this->db->query("
            SELECT t.*, u.name AS user_name
            FROM tasks t
            JOIN users u ON u.id = t.assigned_to
            ORDER BY t.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
