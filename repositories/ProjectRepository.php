<?php
class ProjectRepository {
    private $db;
    public function __construct(){
        require __DIR__.'/../core/db.php';
        $this->db = $db;
    }

    public function all(){
        $stmt = $this->db->query("
            SELECT p.*, u.name AS owner_name 
            FROM projects p 
            LEFT JOIN users u ON p.owner_id = u.id
            ORDER BY p.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($title, $desc, $owner){
        $stmt = $this->db->prepare("INSERT INTO projects (title, description, owner_id) VALUES (?,?,?)");
        $stmt->execute([$title, $desc, $owner]);
    }

    public function delete($id){
        $stmt = $this->db->prepare("DELETE FROM projects WHERE id=?");
        $stmt->execute([$id]);
    }
}
