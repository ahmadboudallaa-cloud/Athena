<?php
// core/db.php
try {
    $db = new PDO('mysql:host=localhost;dbname=scrum_platform;charset=utf8','root','');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}
