<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../repositories/TaskRepository.php';
require_once __DIR__ . '/../repositories/CommentRepository.php';

$auth = new Auth();
if (!$auth->check()) {
    header("Location: login.php");
    exit;
}

$user = $auth->user();
$taskRepo = new TaskRepository();
$commentRepo = new CommentRepository();

$taskId = $_GET['task'] ?? null;
if (!$taskId) {
    die("Tâche non spécifiée");
}

/* Création commentaire */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $parentId = $_POST['parent_id'] ?? null;
    $commentRepo->create($taskId, $user['id'], $_POST['content'], $parentId);
    header("Location: comments.php?task=$taskId");
    exit;
}

/* Suppression commentaire */
if (isset($_GET['delete']) && in_array($user['role'], ['admin','chef_projet'])) {
    $commentRepo->delete((int)$_GET['delete']);
    header("Location: comments.php?task=$taskId");
    exit;
}

$task = $taskRepo->allBySprint(0); // optionnel pour info de la tâche
$comments = $commentRepo->allByTask($taskId);
?>

<h1>Commentaires de la tâche</h1>

<ul>
<?php foreach ($comments as $c): ?>
    <li>
        <strong><?= htmlspecialchars($c['author']) ?></strong> : <?= htmlspecialchars($c['content']) ?>
        <?php if (in_array($user['role'], ['admin','chef_projet'])): ?>
            | <a href="?task=<?= $taskId ?>&delete=<?= $c['id'] ?>">Supprimer</a>
        <?php endif; ?>
        <!-- Formulaire réponse -->
        <form method="POST" style="margin-left:20px">
            <input type="hidden" name="parent_id" value="<?= $c['id'] ?>">
            <input name="content" placeholder="Répondre..." required>
            <button>Répondre</button>
        </form>
    </li>
<?php endforeach; ?>
</ul>

<a href="tasks.php">⬅ Retour tâches</a>
