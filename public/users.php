<?php
session_start();
require_once __DIR__ . '/../core/db.php';

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// Récupérer tous les utilisateurs
$users = $db->query("SELECT id, name, email, role FROM users WHERE active=1")->fetchAll(PDO::FETCH_ASSOC);

// Suppression d'un utilisateur
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$id]);
    header("Location: users.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Utilisateurs - ScrumBoard</title>
<link rel="stylesheet" href="style.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<!-- Sidebar identique à index.php -->
<div class="sidebar">
    <div class="brand">
        <h2>ScrumBoard</h2>
    </div>
    <ul class="menu">
        <li><a href="index.php">🏠 Dashboard</a></li>
        <li><a href="projects.php">📁 Projets</a></li>
        <li><a href="sprints.php">🏃 Sprints</a></li>
        <li><a href="tasks.php">✅ Tâches</a></li>
        <?php if($user['role']=='admin'): ?>
        <li><a href="users.php" class="active">👤 Utilisateurs</a></li>
        <?php endif; ?>
        <li><a href="logout.php">🚪 Déconnexion</a></li>
    </ul>
</div>

<!-- Contenu principal -->
<div class="main">
    <header>
        <h1>Liste des utilisateurs</h1>
    </header>

    <div class="users-table">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td>
                        <a href="?delete=<?= $u['id'] ?>" onclick="return confirm('Supprimer cet utilisateur ?')">🗑️ Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($users)): ?>
                <tr>
                    <td colspan="4" style="text-align:center;">Aucun utilisateur trouvé</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
