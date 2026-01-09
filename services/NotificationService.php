<?php
require_once __DIR__ . '/../services/MailService.php';

class NotificationService
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function notify($user_id, $message, $email)
    {
        // Enregistrer notification
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_id, message) VALUES (?, ?)"
        );
        $stmt->execute([$user_id, $message]);

        // Envoyer email
        MailService::send(
            $email,
            "Notification Scrum",
            "<p>$message</p>"
        );
    }
}
