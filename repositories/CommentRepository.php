<?php
require_once __DIR__ . '/../config/database.php';

class CommentRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // Récupère tous les commentaires d'une tâche
    public function allByTask(int $taskId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, u.name AS author
             FROM comments c
             JOIN users u ON u.id = c.user_id
             WHERE c.task_id = ? AND c.active = 1
             ORDER BY c.created_at ASC"
        );
        $stmt->execute([$taskId]);
        return $stmt->fetchAll();
    }

    // Créer un commentaire ou réponse
    public function create(int $taskId, int $userId, string $content, ?int $parentId = null): bool
    {
        $sql = "INSERT INTO comments (task_id, user_id, content, parent_id) VALUES (?, ?, ?, ?)";
        return $this->db->prepare($sql)->execute([$taskId, $userId, $content, $parentId]);
    }

    // Supprimer (logique)
    public function delete(int $id): bool
    {
        return $this->db->prepare("UPDATE comments SET active = 0 WHERE id = ?")->execute([$id]);
    }
}
