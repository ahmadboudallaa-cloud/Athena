<?php
session_start();
require_once __DIR__ . '/../core/Database.php';

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$db = Database::getInstance()->getConnection();

$task_id = $_GET['task_id'] ?? null;
$project_id = $_GET['project_id'] ?? null;

if(!$task_id && !$project_id){
    die("Aucune tâche ou projet spécifié.");
}

if(isset($_POST['add_comment'])){
    $content   = $_POST['content'];
    $parent_id = $_POST['parent_id'] ?? null;

    $stmt = $db->prepare("INSERT INTO comments (parent_id, task_id, project_id, user_id, content) VALUES (?,?,?,?,?)");
    $stmt->execute([$parent_id, $task_id, $project_id, $user['id'], $content]);
    header("Location: comments.php?".($task_id?"task_id=$task_id":"project_id=$project_id"));
    exit;
}

$query = "SELECT c.*, u.name AS user_name FROM comments c
          LEFT JOIN users u ON c.user_id=u.id
          WHERE ".($task_id ? "c.task_id=?" : "c.project_id=?")."
          ORDER BY c.created_at ASC";
$stmt = $db->prepare($query);
$stmt->execute([$task_id ?? $project_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

function render_comments($comments, $parent_id = null) {
    foreach($comments as $c){
        if($c['parent_id'] == $parent_id){
            echo "<div class='comment'>";
            echo "<strong>".htmlspecialchars($c['user_name'])."</strong> <small>".$c['created_at']."</small><br>";
            echo htmlspecialchars($c['content']);
            echo "<a href='#' onclick='showReplyForm({$c['id']});return false;'>Répondre</a>";

            echo "<div id='reply-form-{$c['id']}' style='display:none;margin-left:20px;'>";
            echo "<form method='POST'>
                    <input type='hidden' name='parent_id' value='{$c['id']}'>
                    <textarea name='content' placeholder='Votre réponse'></textarea>
                    <button name='add_comment'>Répondre</button>
                  </form>";
            echo "</div>";

            render_comments($comments, $c['id']);
            echo "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Commentaires</title>
<link rel="stylesheet" href="style.css?v=<?= time() ?>">
<script>
function showReplyForm(id){
    let f = document.getElementById('reply-form-'+id);
    f.style.display = (f.style.display==='none') ? 'block' : 'none';
}
</script>
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
    <h1>Commentaires</h1>

    <form method="POST">
        <textarea name="content" placeholder="Ajouter un commentaire..." required></textarea>
        <button name="add_comment">Envoyer</button>
    </form>

    <div class="comments">
        <?php render_comments($comments); ?>
    </div>
</main>
</body>
</html>
