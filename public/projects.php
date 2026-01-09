<?php
session_start();
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../repositories/ProjectRepository.php';

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$projectRepo = new ProjectRepository();

// ===== Création du projet =====
if(isset($_POST['create_project'])){
    $title = $_POST['title'];
    $desc  = $_POST['description'];
    $owner = $user['id'];

    $projectRepo->create($title, $desc, $owner);
    header("Location: projects.php"); // redirige après création
    exit;
}

// ===== Suppression du projet =====
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $projectRepo->delete($id);
    header("Location: projects.php"); // redirige après suppression
    exit;
}

// ===== Récupération de tous les projets =====
$projects = $projectRepo->all();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Projets</title>
<link rel="stylesheet" href="style.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div class="sidebar">
    <h2>ScrumBoard</h2>
    <ul>
        <li><a href="index.php">🏠 Dashboard</a></li>
        <li><a href="projects.php">📁 Projets</a></li>
        <li><a href="sprints.php?project_id=<?= $project['id'] ?>">🏃 Sprints</a></li>
        <li><a href="tasks.php">✅ Tâches</a></li>
        <?php if($user['role']=='admin'): ?>
        <li><a href="users.php">👤 Utilisateurs</a></li>
        <?php endif; ?>
        <li><a href="logout.php">🚪 Déconnexion</a></li>
    </ul>
</div>

<div class="main">
    <h1>Projets</h1>

    <form method="POST">
        <input type="text" name="title" placeholder="Titre projet" required>
        <textarea name="description" placeholder="Description"></textarea>
        <button name="create_project">Créer le projet</button>
    </form>

    <table>
        <tr>
            <th>Titre</th>
            <th>Description</th>
            <th>Chef</th>
            <th>Actions</th>
        </tr>
        <?php foreach($projects as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['title']) ?></td>
            <td><?= htmlspecialchars($p['description']) ?></td>
            <td><?= htmlspecialchars($p['owner_name']) ?></td>
            <td>
                <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Supprimer ?')">🗑️</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
