<?php
session_start();
require_once __DIR__ . '/../core/Database.php';

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$db = Database::getInstance()->getConnection();


if(isset($_POST['create_task'])){
    $title       = $_POST['title'];
    $description = $_POST['description'];
    $sprint_id   = $_POST['sprint_id'];
    $assigned_to = $_POST['assigned_to'];
    $status      = $_POST['status'] ?? 'pending';

    $stmt = $db->prepare("INSERT INTO tasks (title, description, sprint_id, assigned_to, status) VALUES (?,?,?,?,?)");
    $stmt->execute([$title, $description, $sprint_id, $assigned_to, $status]);

    header("Location: tasks.php");
    exit;
}

if(isset($_POST['edit_task'])){
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $sprint_id = $_POST['sprint_id'];
    $assigned_to = $_POST['assigned_to'];
    $status = $_POST['status'];

    $stmt = $db->prepare("UPDATE tasks SET title=?, description=?, sprint_id=?, assigned_to=?, status=? WHERE id=?");
    $stmt->execute([$title, $description, $sprint_id, $assigned_to, $status, $id]);
    header("Location: tasks.php");
    exit;
}

if(isset($_GET['delete_task'])){
    $id = (int)$_GET['delete_task'];
    $stmt = $db->prepare("DELETE FROM tasks WHERE id=?");
    $stmt->execute([$id]);
    header("Location: tasks.php");
    exit;
}

if(isset($_POST['add_comment'])){
    $task_id = $_POST['task_id'];
    $content = $_POST['content'];
    $parent_id = $_POST['parent_id'] ?: null;

$stmt = $db->prepare("INSERT INTO comments (user_id, task_id, content, parent_id) VALUES (?,?,?,?)");
    $stmt->execute([$user['id'], $task_id, $content, $parent_id]);
    header("Location: tasks.php");
    exit;
}

$search_title  = $_GET['search_title'] ?? '';
$search_status = $_GET['search_status'] ?? '';
$search_member = $_GET['search_member'] ?? '';

$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$limit = 5;
$offset = ($page-1)*$limit;

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

$stmt_total = $db->prepare($query);
$stmt_total->execute($params);
$total_tasks_filtered = $stmt_total->rowCount();
$total_pages = ceil($total_tasks_filtered/$limit);

$query .= " ORDER BY t.id DESC LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sprints = $db->query("SELECT id, name FROM sprints ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$members = $db->query("SELECT id, name FROM users WHERE active=1")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Tâches</title>
<link rel="stylesheet" href="style.css?v=<?= time() ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <h1>Gestion des tâches</h1>

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

    <table>
        <tr>
            <th>Titre</th>
            <th>Sprint</th>
            <th>Membre</th>
            <th>Statut</th>
            <th>Commentaires</th>
            <th>Actions</th>
        </tr>
        <?php foreach($tasks as $task): ?>
        <tr>
            <td><?= htmlspecialchars($task['title']) ?></td>
            <td><?= htmlspecialchars($task['sprint_name']) ?></td>
            <td><?= htmlspecialchars($task['member_name']) ?></td>
            <td><?= ucfirst($task['status']) ?></td>
            <td>
                <?php
                $stmt_comments = $db->prepare("SELECT c.*, u.name AS user_name FROM comments c JOIN users u ON c.user_id=u.id WHERE c.task_id=? ORDER BY c.created_at ASC");
                $stmt_comments->execute([$task['id']]);
                $comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <?php foreach($comments as $c): ?>
                    <div style="margin-left:<?= $c['parent_id'] ? '20px':'0px' ?>;">
                        <strong><?= htmlspecialchars($c['user_name']) ?></strong> : <?= htmlspecialchars($c['content']) ?>
                        <a href="?reply_to=<?= $c['id'] ?>">Répondre</a>
                    </div>
                <?php endforeach; ?>
                <form method="POST">
                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                    <input type="hidden" name="parent_id" value="<?= $_GET['reply_to'] ?? '' ?>">
                    <textarea name="content" placeholder="Écrire un commentaire..." required></textarea>
                    <button name="add_comment">Commenter</button>
                </form>
            </td>
            <td>
                <a href="?delete_task=<?= $task['id'] ?>" onclick="return confirm('Supprimer ?')">🗑️</a>
                <a href="edit_task.php?id=<?= $task['id'] ?>">✏️</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="pagination">
        <?php for($p=1;$p<=$total_pages;$p++): ?>
            <a href="?page=<?= $p ?>&search_title=<?= htmlspecialchars($search_title) ?>&search_status=<?= $search_status ?>&search_member=<?= $search_member ?>" class="<?= $p==$page?'active':'' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
</main>
</body>
</html>
