<?php
session_start();
require_once __DIR__ . '/../core/Database.php';

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$db = Database::getInstance()->getConnection();


if(isset($_POST['create_project'])){
    $title = $_POST['title'];
    $description = $_POST['description'];

    $stmt = $db->prepare("INSERT INTO projects (title, description, owner_id) VALUES (?,?,?)");
    $stmt->execute([$title, $description, $user['id']]);
    header("Location: projects.php");
    exit;
}

if(isset($_POST['edit_project'])){
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    $stmt = $db->prepare("UPDATE projects SET title=?, description=? WHERE id=?");
    $stmt->execute([$title,$description,$id]);
    header("Location: projects.php");
    exit;
}

if(isset($_GET['delete_project'])){
    $id = (int)$_GET['delete_project'];
    $stmt = $db->prepare("DELETE FROM projects WHERE id=?");
    $stmt->execute([$id]);
    header("Location: projects.php");
    exit;
}


// Ajouter un commentaire à un projet
if(isset($_POST['add_comment'])){
    $project_id = (int)$_POST['project_id'];
    $content    = trim($_POST['content']);
    $parent_id  = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

    $stmt = $db->prepare("
        INSERT INTO comments (user_id, project_id, task_id, content, parent_id)
        VALUES (?, ?, NULL, ?, ?)
    ");
    $stmt->execute([
        $user['id'],
        $project_id,
        $content,
        $parent_id
    ]);

    header("Location: projects.php");
    exit;
}


$search_title = $_GET['search_title'] ?? '';
$search_owner = $_GET['search_owner'] ?? '';

$params = [];
$query = "SELECT p.*, u.name AS owner_name FROM projects p LEFT JOIN users u ON p.owner_id=u.id WHERE p.title LIKE :title";
$params[':title'] = "%$search_title%";

if($search_owner){
    $query .= " AND p.owner_id = :owner";
    $params[':owner'] = $search_owner;
}

$stmt_total = $db->prepare($query);
$stmt_total->execute($params);
$total_projects_filtered = $stmt_total->rowCount();
$total_pages = ceil($total_projects_filtered/5);

$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$limit = 5;
$offset = ($page-1)*$limit;

$query .= " ORDER BY p.id DESC LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$users = $db->query("SELECT id, name FROM users WHERE active=1")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Projets</title>
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
    <h1>Gestion des Projets</h1>

    <form method="POST" class="form-project">
        <input type="text" name="title" placeholder="Titre du projet" required>
        <textarea name="description" placeholder="Description du projet"></textarea>
        <button name="create_project">Créer Projet</button>
    </form>

    <form method="GET" class="form-filter">
        <input type="text" name="search_title" placeholder="Titre" value="<?= htmlspecialchars($search_title) ?>">
        <select name="search_owner">
            <option value="">Tous chefs de projet</option>
            <?php foreach($users as $u): ?>
                <option value="<?= $u['id'] ?>" <?= $search_owner==$u['id']?'selected':'' ?>><?= htmlspecialchars($u['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Rechercher</button>
    </form>

    <table>
        <tr>
            <th>Titre</th>
            <th>Description</th>
            <th>Chef</th>
            <th>Actions</th>
        </tr>
        <?php foreach($projects as $project): ?>
        <tr>
            <td><?= htmlspecialchars($project['title']) ?></td>
            <td><?= htmlspecialchars($project['description']) ?></td>
            <td><?= htmlspecialchars($project['owner_name']) ?></td>
            <td>
                <a href="sprints.php?project_id=<?= $project['id'] ?>">Voir Sprints</a>
                <a href="?delete_project=<?= $project['id'] ?>" onclick="return confirm('Supprimer ?')">Supprimer</a>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <?php
                $stmt_comments = $db->prepare("SELECT c.*, u.name AS user_name FROM comments c JOIN users u ON c.user_id=u.id WHERE c.project_id=? ORDER BY c.created_at ASC");
                $stmt_comments->execute([$project['id']]);
                $comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="comments">
                    <strong>Commentaires :</strong>
                    <?php foreach($comments as $c): ?>
                        <div style="margin-left:<?= $c['parent_id'] ? '20px':'0px' ?>; border-left:<?= $c['parent_id'] ? '1px solid #ccc':'none' ?>; padding-left:5px;">
                            <strong><?= htmlspecialchars($c['user_name']) ?></strong> (<?= $c['created_at'] ?>) : <?= htmlspecialchars($c['content']) ?>
                            <a href="?reply_to=<?= $c['id'] ?>&project_id=<?= $project['id'] ?>">Répondre</a>
                        </div>
                    <?php endforeach; ?>

                    <form method="POST">
                        <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                        <input type="hidden" name="parent_id" value="<?= $_GET['reply_to'] ?? '' ?>">
                        <textarea name="content" placeholder="Écrire un commentaire..." required></textarea>
                        <button name="add_comment">Commenter</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="pagination">
        <?php for($p=1;$p<=$total_pages;$p++): ?>
            <a href="?page=<?= $p ?>&search_title=<?= htmlspecialchars($search_title) ?>&search_owner=<?= $search_owner ?>" class="<?= $p==$page?'active':'' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
</main>
</body>
</html>
