<?php
require_once __DIR__ . '/../repositories/TaskRepository.php';
require_once __DIR__ . '/../core/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $repo = new TaskRepository($db);

    $repo->create(
        $_POST['title'],
        $_POST['status'],
        $_POST['priority'],
        $_POST['assigned_to']
    );

    header("Location: dashboard.php");
    exit;
}
