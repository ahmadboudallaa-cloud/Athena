<?php
session_start();
require_once __DIR__ . '/../core/db.php';

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// Vérifier que project_id est défini
if(!isset($_GET['project_id'])){
    die("Projet non défini");
}
$project_id = (int)$_GET['project_id'];

// Récupérer le projet
$stmt = $db->prepare("SELECT * FROM projects WHERE id=?");
$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$project){
    die("Projet introuvable");
}

// Ajouter un sprint
if(isset($_POST['create_sprint'])){
    $title = $_POST['title'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $stmt = $db->prepare("INSERT INTO sprints (project_id, title, start_date, end_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$project_id, $title, $start_date, $end_date]);
    header("Location: sprints.php?project_id=".$project_id);
    exit;
}

// Supprimer un sprint
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM sprints WHERE id=?");
    $stmt->execute([$id]);
    header("Location: sprints.php?project_id=".$project_id);
    exit;
}

// Récupérer les sprints du projet
$stmt = $db->prepare("SELECT * FROM sprints WHERE project_id=? ORDER BY start_date DESC");
$stmt->execute([$project_id]);
$sprints = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Sprints - <?= htmlspecialchars($project['title']) ?></title>
<link rel="stylesheet" href="style.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <h2>ScrumBoard</h2>
    </div>
    <ul class="menu">
        <li><a href="index.php">🏠 Dashboard</a></li>
        <li><a href="projects.php">📁 Projets</a></li>
        <li><a href="#" class="active">🏃 Sprints</a></li>
        <li><a href="tasks.php">✅ Tâches</a></li>
        <?php if($user['role']=='admin'): ?>
        <li><a href="users.php">👤 Utilisateurs</a></li>
        <?php endif; ?>
        <li><a href="logout.php">🚪 Déconnexion</a></li>
    </ul>
</div>

<div class="main">
    <h1>Sprints du projet : <?= htmlspecialchars($project['title']) ?></h1>

    <!-- Formulaire création sprint -->
    <h3>Créer un Sprint</h3>
    <form method="POST">
        <input type="text" name="title" placeholder="Titre sprint" required>
        <input type="date" name="start_date" required>
        <input type="date" name="end_date" required>
        <button type="submit" name="create_sprint">Créer Sprint</button>
    </form>

    <!-- Liste des sprints -->
    <h3>Liste des Sprints</h3>
    <table>
        <thead>
            <tr>
                <th>Titre</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($sprints as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['title']) ?></td>
                <td><?= htmlspecialchars($s['start_date']) ?></td>
                <td><?= htmlspecialchars($s['end_date']) ?></td>
                <td>
                    <a href="tasks.php?sprint_id=<?= $s['id'] ?>">✅ Tâches</a>
                    <a href="?delete=<?= $s['id'] ?>&project_id=<?= $project_id ?>" onclick="return confirm('Supprimer ce sprint ?')">🗑️</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($sprints)): ?>
            <tr>
                <td colspan="4" style="text-align:center;">Aucun sprint trouvé</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p><a href="projects.php">⬅ Retour aux projets</a></p>
</div>

</body>
</html>
