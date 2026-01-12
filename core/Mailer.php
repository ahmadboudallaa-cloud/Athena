<?php
class Mailer {
    public static function send($to, $subject, $message){
        $headers = "From: noreply@athena.com\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        return mail($to, $subject, $message, $headers);
    }

    public static function notifyTaskCreated($task, $assignedUser){
        $subject = "Nouvelle tâche assignée : {$task['title']}";
        $message = "Bonjour {$assignedUser['name']},<br>Vous avez une nouvelle tâche : <strong>{$task['title']}</strong>.<br>Description : {$task['description']}";
        self::send($assignedUser['email'], $subject, $message);
    }

    public static function notifyTaskStatusChange($task, $assignedUser){
        $subject = "Changement de statut : {$task['title']}";
        $message = "Bonjour {$assignedUser['name']},<br>Le statut de la tâche <strong>{$task['title']}</strong> a changé : <strong>{$task['status']}</strong>";
        self::send($assignedUser['email'], $subject, $message);
    }

    public static function notifyComment($task, $comment, $assignedUser){
        $subject = "Nouveau commentaire sur : {$task['title']}";
        $message = "Bonjour {$assignedUser['name']},<br>Un commentaire a été ajouté : <em>{$comment['content']}</em>";
        self::send($assignedUser['email'], $subject, $message);
    }
}
