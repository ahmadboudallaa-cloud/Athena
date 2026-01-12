<?php
require_once __DIR__ . '/../core/Database.php';

class TaskRepository {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function all($sprint_id) {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE sprint_id = ? ORDER BY id ASC");
        $stmt->execute([$sprint_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($sprint_id, $title, $assigned_to, $status = 'pending') {
        $stmt = $this->db->prepare("INSERT INTO tasks (sprint_id, title, assigned_to, status) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$sprint_id, $title, $assigned_to, $status]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
