<?php
class SprintRepository {
    private $db;

    public function __construct() {
        $this->db = new PDO(
            'mysql:host=localhost;dbname=scrum_platform;charset=utf8',
            'root',
            ''
        );
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function allByProject($project_id) {
        $stmt = $this->db->prepare("SELECT * FROM sprints WHERE project_id = ? AND active = 1 ORDER BY start_date");
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
}
