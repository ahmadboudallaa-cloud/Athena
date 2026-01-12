<?php
require_once __DIR__ . '/../core/Database.php';

class ProjectRepository {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function all() {
        $stmt = $this->db->query("SELECT p.*, u.name AS owner_name FROM projects p JOIN users u ON p.owner_id = u.id ORDER BY p.id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($title, $description, $owner_id) {
        $stmt = $this->db->prepare("INSERT INTO projects (title, description, owner_id) VALUES (?, ?, ?)");
        return $stmt->execute([$title, $description, $owner_id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM projects WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $title, $description) {
        $stmt = $this->db->prepare("UPDATE projects SET title = ?, description = ? WHERE id = ?");
        return $stmt->execute([$title, $description, $id]);
    }
}
