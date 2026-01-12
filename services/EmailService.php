<?php

class EmailService {
    
    private $from = "no-reply@athena.com"; // Adresse email de l'expéditeur
    
    /**
     * Envoyer un email
     * @param string $to Destinataire
     * @param string $subject Sujet
     * @param string $message Contenu HTML
     */
    public static function send($to, $subject, $message) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: no-reply@athena.com" . "\r\n";

        if(mail($to, $subject, $message, $headers)) {
            return true;
        } else {
            return false;
        }
    }
}
