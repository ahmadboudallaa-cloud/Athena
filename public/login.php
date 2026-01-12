<?php
session_start();
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance()->getConnection();
$error = '';

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE email=? AND active=1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($password, $user['password'])){
        $_SESSION['user'] = $user;
        header("Location: index.php");
        exit;
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion - Athena</title>
<link rel="stylesheet" href="style.css?v=<?= time() ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div class="login-container">
    <div class="login-box">
        <h1>Se connecter</h1>
        <?php if($error): ?>
            <p style="color:red; margin-bottom:15px;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button name="login">Se connecter</button>
        </form>
        <a href="register.php">Créer un compte</a>
    </div>
</div>
</body>
</html>
