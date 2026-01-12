<?php

require_once __DIR__ . '/../config/database.php';

class NotificationRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function create(int $userId, string $message): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_id, message) VALUES (?, ?)"
        );
        $stmt->execute([$userId, $message]);
    }

    public function getUnreadByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM notifications
             WHERE user_id = ? AND is_read = 0
             ORDER BY created_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE notifications SET is_read = 1 WHERE id = ?"
        );
        $stmt->execute([$id]);
    }
}
