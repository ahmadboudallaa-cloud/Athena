<?php
session_start();
require_once __DIR__ . '/../core/db.php';

if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// Compter les stats
$nbProjects = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$nbTasks    = $db->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
$nbUsers    = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard Scrum</title>
<link rel="stylesheet" href="style.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="sidebar">
    <h2>ScrumBoard</h2>
    <ul>
        <li><a href="index.php">🏠 Dashboard</a></li>
        <li><a href="projects.php">📁 Projets</a></li>
        <li><a href="sprints.php">🏃 Sprints</a></li>
        <li><a href="tasks.php">✅ Tâches</a></li>
        <?php if($user['role'] === 'admin'): ?>
            <li><a href="users.php">👤 Utilisateurs</a></li>
        <?php endif; ?>
        <li><a href="logout.php">🚪 Déconnexion</a></li>
    </ul>
</div>

<div class="main">
    <header>
        <h1>Bienvenue, <?= htmlspecialchars($user['name']) ?></h1>
        <p>Rôle : <?= htmlspecialchars($user['role']) ?></p>
    </header>

    <div class="stats">
        <div class="card">
            <h3><?= $nbProjects ?></h3>
            <p>Projets</p>
        </div>
        <div class="card">
            <h3><?= $nbTasks ?></h3>
            <p>Tâches</p>
        </div>
        <?php if($user['role'] === 'admin'): ?>
        <div class="card">
            <h3><?= $nbUsers ?></h3>
            <p>Utilisateurs</p>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
