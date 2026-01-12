<?php
require_once __DIR__ . '/../core/Database.php';

class SprintRepository {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function all($project_id) {
        $stmt = $this->db->prepare("SELECT * FROM sprints WHERE project_id = ? ORDER BY start_date ASC");
        $stmt->execute([$project_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($project_id, $name, $start_date, $end_date) {
        $stmt = $this->db->prepare("INSERT INTO sprints (project_id, name, start_date, end_date) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$project_id, $name, $start_date, $end_date]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM sprints WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM sprints WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
