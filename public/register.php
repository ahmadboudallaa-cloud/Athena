<?php
session_start();
$db = new PDO('mysql:host=localhost;dbname=scrum_platform;charset=utf8','root','');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$message = '';

if(isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
    if($stmt->execute([$name,$email,$password,$role])) {
        $message = "Compte créé avec succès ! <a href='login.php'>Connectez-vous</a>";
    } else {
        $message = "Erreur lors de la création du compte.";
    }
}
?>

<h2>Créer un compte</h2>
<form method="POST">
    <input type="text" name="name" placeholder="Nom complet" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Mot de passe" required><br>
    <select name="role" required>
        <option value="admin">Admin</option>
        <option value="chef_projet">Chef de projet</option>
        <option value="membre">Membre</option>
    </select><br>
    <button type="submit" name="register">Créer le compte</button>
</form>

<p><?= $message ?></p>
