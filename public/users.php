<?php
session_start();
require_once __DIR__ . '/../core/Database.php';

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

if($user['role'] !== 'admin'){
    die("Accès refusé !");
}

$db = Database::getInstance()->getConnection();

if(isset($_POST['create_user'])){
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $role     = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO users (name,email,role,password,active) VALUES (?,?,?,?,1)");
    $stmt->execute([$name,$email,$role,$password]);
    header("Location: users.php");
    exit;
}

if(isset($_POST['update_user'])){
    $id   = $_POST['user_id'];
    $name = $_POST['name'];
    $email= $_POST['email'];
    $role = $_POST['role'];
    $active = isset($_POST['active']) ? 1 : 0;

    $stmt = $db->prepare("UPDATE users SET name=?, email=?, role=?, active=? WHERE id=?");
    $stmt->execute([$name,$email,$role,$active,$id]);
    header("Location: users.php");
    exit;
}

if(isset($_GET['delete_user'])){
    $id = (int)$_GET['delete_user'];
    $stmt = $db->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$id]);
    header("Location: users.php");
    exit;
}

$edit_user = null;
if(isset($_GET['edit_user'])){
    $id = (int)$_GET['edit_user'];
    $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$id]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}

$stmt = $db->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des utilisateurs</title>
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
    <h1>Gestion des utilisateurs</h1>

    <form method="POST">
        <input type="hidden" name="user_id" value="<?= $edit_user['id'] ?? '' ?>">
        <input type="text" name="name" placeholder="Nom" value="<?= $edit_user['name'] ?? '' ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?= $edit_user['email'] ?? '' ?>" required>
        <select name="role" required>
            <option value="admin" <?= ($edit_user && $edit_user['role']=='admin')?'selected':'' ?>>Admin</option>
            <option value="chef" <?= ($edit_user && $edit_user['role']=='chef')?'selected':'' ?>>Chef de projet</option>
            <option value="member" <?= ($edit_user && $edit_user['role']=='member')?'selected':'' ?>>Membre</option>
        </select>
        <?php if(!$edit_user): ?>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <?php else: ?>
        <label><input type="checkbox" name="active" value="1" <?= $edit_user['active']?'checked':'' ?>> Actif</label>
        <?php endif; ?>
        <button name="<?= $edit_user?'update_user':'create_user' ?>"><?= $edit_user?'Modifier':'Créer' ?></button>
        <?php if($edit_user): ?><a href="users.php">Annuler</a><?php endif; ?>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Actif</th>
            <th>Actions</th>
        </tr>
        <?php foreach($users as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= $u['role'] ?></td>
            <td><?= $u['active']?'Oui':'Inactif' ?></td>
            <td>
                <a href="?edit_user=<?= $u['id'] ?>">modifier</a>
                <a href="?delete_user=<?= $u['id'] ?>" onclick="return confirm('Supprimer ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</main>
</body>
</html>
