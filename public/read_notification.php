<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../repositories/NotificationRepository.php';

$auth = new Auth();
if (!$auth->check()) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$repo = new NotificationRepository();
$repo->markAsRead((int) $_GET['id']);

header("Location: index.php");
exit;
