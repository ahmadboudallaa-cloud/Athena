<?php
session_start();
require_once __DIR__ . '/../core/db.php';

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// ===== Création d'une tâche =====
if(isset($_POST['create_task'])){
    $title       = $_POST['title'];
    $desc        = $_POST['description'];
    $sprint_id   = $_POST['sprint_id'];
    $assigned_to = $_POST['assigned_to'];
    $status      = $_POST['status'] ?? 'pending';

    $stmt = $db->prepare("INSERT INTO tasks (title, description, sprint_id, assigned_to, status) VALUES (?,?,?,?,?)");
    $stmt->execute([$title, $desc, $sprint_id, $assigned_to, $status]);

    header("Location: tasks.php");
    exit;
}

// ===== Suppression d'une tâche =====
if(isset($_GET['delete_task'])){
    $id = (int)$_GET['delete_task'];
    $stmt = $db->prepare("DELETE FROM tasks WHERE id=?");
    $stmt->execute([$id]);
    header("Location: tasks.php");
    exit;
}

// ===== RECHERCHE & FILTRAGE =====
$search_title  = $_GET['search_title'] ?? '';
$search_status = $_GET['search_status'] ?? '';
$search_member = $_GET['search_member'] ?? '';

// Pagination
$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$limit = 5;
$offset = ($page-1)*$limit;

// Construction de la requête
$params = [];
$query = "SELECT t.*, s.name AS sprint_name, u.name AS member_name 
          FROM tasks t 
          LEFT JOIN sprints s ON t.sprint_id = s.id
          LEFT JOIN users u ON t.assigned_to = u.id
          WHERE t.title LIKE :title ";

$params[':title'] = "%$search_title%";

if($search_status){
    $query .= " AND t.status = :status ";
    $params[':status'] = $search_status;
}

if($search_member){
    $query .= " AND t.assigned_to = :member ";
    $params[':member'] = $search_member;
}

// Compter total pour pagination
$stmt_total = $db->prepare($query);
$stmt_total->execute($params);
$total_tasks_filtered = $stmt_total->rowCount();
$total_pages = ceil($total_tasks_filtered/$limit);

// Ajouter LIMIT OFFSET
$query .= " ORDER BY t.created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Liste des sprints pour le formulaire
$sprints = $db->query("SELECT id, name FROM sprints ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Liste des membres pour le formulaire et filtre
$members = $db->query("SELECT id, name FROM users WHERE active=1")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Tâches</title>
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
        <?php if($user['role']=='admin'): ?>
        <li><a href="users.php">👤 Utilisateurs</a></li>
        <?php endif; ?>
        <li><a href="logout.php">🚪 Déconnexion</a></li>
    </ul>
</div>

<div class="main">
    <h1>Gestion des tâches</h1>

    <!-- FORMULAIRE AJOUT TÂCHE -->
    <form method="POST" class="form-task">
        <input type="text" name="title" placeholder="Titre tâche" required>
        <textarea name="description" placeholder="Description"></textarea>
        <select name="sprint_id" required>
            <option value="">-- Choisir un sprint --</option>
            <?php foreach($sprints as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="assigned_to" required>
            <option value="">-- Assigner à --</option>
            <?php foreach($members as $m): ?>
                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" required>
            <option value="pending">Pending</option>
            <option value="doing">Doing</option>
            <option value="done">Done</option>
        </select>
        <button name="create_task">Créer la tâche</button>
    </form>

    <!-- FORMULAIRE FILTRAGE -->
    <form method="GET" class="form-filter">
        <input type="text" name="search_title" placeholder="Titre tâche" value="<?= htmlspecialchars($search_title) ?>">
        <select name="search_status">
            <option value="">Tous statuts</option>
            <option value="pending" <?= $search_status=='pending'?'selected':'' ?>>Pending</option>
            <option value="doing" <?= $search_status=='doing'?'selected':'' ?>>Doing</option>
            <option value="done" <?= $search_status=='done'?'selected':'' ?>>Done</option>
        </select>
        <select name="search_member">
            <option value="">Tous membres</option>
            <?php foreach($members as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $search_member==$m['id']?'selected':'' ?>><?= htmlspecialchars($m['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Rechercher</button>
    </form>

    <!-- TABLEAU DES TÂCHES -->
    <table>
        <tr>
            <th>Titre</th>
            <th>Sprint</th>
            <th>Membre</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
        <?php foreach($tasks as $task): ?>
        <tr>
            <td><?= htmlspecialchars($task['title']) ?></td>
            <td><?= htmlspecialchars($task['sprint_name']) ?></td>
            <td><?= htmlspecialchars($task['member_name']) ?></td>
            <td><?= ucfirst($task['status']) ?></td>
            <td>
                <a href="edit_task.php?id=<?= $task['id'] ?>">✏️</a>
                <a href="?delete_task=<?= $task['id'] ?>" onclick="return confirm('Supprimer ?')">🗑️</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- PAGINATION -->
    <div class="pagination">
        <?php for($p=1;$p<=$total_pages;$p++): ?>
            <a href="?page=<?= $p ?>&search_title=<?= htmlspecialchars($search_title) ?>&search_status=<?= $search_status ?>&search_member=<?= $search_member ?>" class="<?= $p==$page?'active':'' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
</div>
</body>
</html>
