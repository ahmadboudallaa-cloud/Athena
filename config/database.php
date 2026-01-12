<?php

class Database
{
    public static function connect(): PDO
    {
        return new PDO(
            "mysql:host=localhost;dbname=scrum_platform;charset=utf8",
            "root",
            "",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }
}
