<?php
session_start();
require_once __DIR__ . '/../core/db.php';

$error = '';
$success = '';

/* ===== INSCRIPTION ===== */
if(isset($_POST['register'])){
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role  = $_POST['role'];
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $db->prepare("SELECT id FROM users WHERE email=?");
    $check->execute([$email]);

    if($check->rowCount() > 0){
        $error = "Email déjà utilisé";
    }else{
        $stmt = $db->prepare("INSERT INTO users (name,email,password,role,active) VALUES(?,?,?,?,1)");
        $stmt->execute([$name,$email,$pass,$role]);
        $success = "Compte créé avec succès, connecte-toi.";
    }
}

/* ===== LOGIN ===== */
if(isset($_POST['login'])){
    $email = $_POST['email'];
    $pass  = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE email=? AND active=1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($pass,$user['password'])){
        $_SESSION['user'] = [
            'id'   => $user['id'],
            'name' => $user['name'],
            'role' => $user['role']
        ];
        header("Location: index.php");
        exit;
    } else {
        $error = "Email ou mot de passe incorrect";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Login | Scrum</title>
<link rel="stylesheet" href="style.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="auth">

<div class="auth-box">
    <h2>Connexion</h2>
    <?php if($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
    <?php if($success): ?><p class="success"><?= $success ?></p><?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button name="login">Se connecter</button>
    </form>

    <hr>

    <h3>Créer un compte</h3>
    <form method="POST">
        <input type="text" name="name" placeholder="Nom" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <select name="role" required>
            <option value="member">Membre</option>
            <option value="chef">Chef de projet</option>
            <option value="admin">Admin</option>
        </select>
        <button name="register">Créer le compte</button>
    </form>
</div>
</body>
</html>
