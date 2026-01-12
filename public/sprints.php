<?php
session_start();
require_once __DIR__ . '/../core/Database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

$db = Database::getInstance()->getConnection();

$projects = $db->query("SELECT * FROM projects ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

if (empty($projects)) {
    echo "Aucun projet trouvé. <a href='projects.php'>Créer un projet</a>";
    exit;
}

if (isset($_GET['project_id'])) {
    $project_id = (int)$_GET['project_id'];
} else {
    $project_id = $projects[0]['id'];
}

if (isset($_POST['create_sprint'])) {
    $name       = $_POST['name'];
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];

    $stmt = $db->prepare("INSERT INTO sprints (name, start_date, end_date, project_id) VALUES (?,?,?,?)");
    $stmt->execute([$name, $start_date, $end_date, $project_id]);

    header("Location: sprints.php?project_id=$project_id");
    exit;
}

if (isset($_POST['update_sprint'])) {
    $sprint_id  = (int)$_POST['sprint_id'];
    $name       = $_POST['name'];
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];

    $stmt = $db->prepare("UPDATE sprints SET name=?, start_date=?, end_date=? WHERE id=?");
    $stmt->execute([$name, $start_date, $end_date, $sprint_id]);

    header("Location: sprints.php?project_id=$project_id");
    exit;
}

if (isset($_GET['delete_sprint'])) {
    $id = (int)$_GET['delete_sprint'];
    $stmt = $db->prepare("DELETE FROM sprints WHERE id=?");
    $stmt->execute([$id]);
    header("Location: sprints.php?project_id=$project_id");
    exit;
}

$stmt = $db->prepare("SELECT * FROM sprints WHERE project_id=? ORDER BY id ASC");
$stmt->execute([$project_id]);
$sprints = $stmt->fetchAll(PDO::FETCH_ASSOC);

$edit_sprint = null;
if (isset($_GET['edit_sprint'])) {
    $edit_id = (int)$_GET['edit_sprint'];
    $stmt = $db->prepare("SELECT * FROM sprints WHERE id=?");
    $stmt->execute([$edit_id]);
    $edit_sprint = $stmt->fetch(PDO::FETCH_ASSOC);
}
$current_project = null;
foreach($projects as $p){
    if($p['id'] == $project_id){
        $current_project = $p;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Sprints</title>
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
<h1>Sprints du projet : <?= $current_project ? htmlspecialchars($current_project['title']) : 'Projet inconnu' ?></h1>
    <h3>Changer de projet :</h3>
    <form method="GET">
        <select name="project_id" onchange="this.form.submit()">
            <?php foreach($projects as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $p['id']==$project_id?'selected':'' ?>><?= htmlspecialchars($p['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>


    <form method="POST">
        <input type="hidden" name="sprint_id" value="<?= $edit_sprint['id'] ?? '' ?>">
        <input type="text" name="name" placeholder="Nom du sprint" required value="<?= $edit_sprint['name'] ?? '' ?>">
        <input type="date" name="start_date" required value="<?= $edit_sprint['start_date'] ?? '' ?>">
        <input type="date" name="end_date" required value="<?= $edit_sprint['end_date'] ?? '' ?>">
        <button name="<?= $edit_sprint ? 'update_sprint' : 'create_sprint' ?>">
            <?= $edit_sprint ? 'Mettre à jour' : 'Créer Sprint' ?>
        </button>
        <?php if($edit_sprint): ?>
            <a href="sprints.php?project_id=<?= $project_id ?>">Annuler</a>
        <?php endif; ?>
    </form>

    <table>
        <tr>
            <th>Nom</th>
            <th>Date début</th>
            <th>Date fin</th>
            <th>Actions</th>
        </tr>
        <?php foreach($sprints as $sprint): ?>
        <tr>
            <td><?= htmlspecialchars($sprint['name']) ?></td>
            <td><?= htmlspecialchars($sprint['start_date']) ?></td>
            <td><?= htmlspecialchars($sprint['end_date']) ?></td>
            <td>
                <a class="action" href="?edit_sprint=<?= $sprint['id'] ?>&project_id=<?= $project_id ?>">✏️</a>
                <a class="action" href="?delete_sprint=<?= $sprint['id'] ?>&project_id=<?= $project_id ?>" onclick="return confirm('Supprimer ce sprint ?')">🗑️</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

   
</main>

</body>
</html>
