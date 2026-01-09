<?php

class MailService
{
    public static function send($to, $subject, $message)
    {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: Scrum Platform <no-reply@scrum.com>\r\n";

        // En local XAMPP le mail peut ne pas arriver
        @mail($to, $subject, $message, $headers);
    }
}
