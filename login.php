<?php
require 'config/config.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND username = :username");
    $stmt->execute(['email' => $email, 'username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        Flight::redirect('/users');
    } else {
        $message = "Identifiants incorrects";
    }
}
?>

<?php include 'includes/header.php'; ?>

<h2 style="text-align:center;">Login</h2>
<form method="post" action="/login" style="text-align:center;">
    Email: <input type="email" name="email" required><br><br>
    Username: <input type="text" name="username" required><br><br>
    Mot de passe: <input type="password" name="password" required><br><br>
    <button type="submit">Se connecter</button>
</form>
<p style="color:red; text-align:center;"><?= $message ?></p>

<?php include 'includes/footer.php'; ?>
