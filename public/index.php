<?php
session_start();
require_once __DIR__ . '/../core/Database.php';

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$user = $_SESSION['user'];

$projects_count = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$sprints_count  = $db->query("SELECT COUNT(*) FROM sprints")->fetchColumn();
$tasks_count    = $db->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>
<body>
<div class="sidebar">
    <div class="logo">Athena</div>

    <nav>
        <a href="index.php">Dashboard</a>
        <a href="projects.php">Projets</a>
        <a href="sprints.php">Sprints</a>
        <a href="tasks.php">Tâches</a>

        <?php if($_SESSION['user']['role']==='admin'): ?>
            <a href="users.php">Utilisateurs</a>
        <?php endif; ?>

        <a href="logout.php" class="logout">Déconnexion</a>
    </nav>
</div>



<main class="content">
    <h1>Dashboard</h1>
    <div class="cards">
        <div class="card">Projets : <?= $projects_count ?></div>
        <div class="card">Sprints : <?= $sprints_count ?></div>
        <div class="card">Tâches : <?= $tasks_count ?></div>
    </div>
</main>
</body>
</html>
