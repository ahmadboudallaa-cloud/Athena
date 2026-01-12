<?php
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance()->getConnection();
$error = "";

if(isset($_POST['register'])){
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];


    $allowed_roles = ['admin','manager','member'];
    if(!in_array($role, $allowed_roles)){
        $role = 'member';
    }

  
    $check = $db->prepare("SELECT id FROM users WHERE email=?");
    $check->execute([$email]);

    if($check->rowCount() > 0){
        $error = "Cet email existe déjà";
    } else {
        $stmt = $db->prepare("
            INSERT INTO users (name, email, password, role, active)
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmt->execute([$name, $email, $password, $role]);

        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Inscription</title>
<link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>
<body class="auth-page">

<div class="auth-box">
    <h2>Créer un compte</h2>

    <?php if($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Nom complet" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>

      
        <select name="role" required>
            <option value="">-- Choisir un rôle --</option>
            <option value="member">Membre</option>
            <option value="manager">Chef de projet</option>
            <option value="admin">Administrateur</option>
        </select>

        <button name="register">S'inscrire</button>
    </form>

    <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
</div>

</body>
</html>
