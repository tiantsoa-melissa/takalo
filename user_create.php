<?php
require 'config/config.php';
if (!isset($_SESSION['user_id'])) {
    Flight::redirect('/login');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (email, username, password_hash) VALUES (?, ?, ?)");
    if ($stmt->execute([$email, $username, $password])) {
        Flight::redirect('/users');
    } else {
        $message = "Erreur lors de l'ajout";
    }
}
?>
<?php include 'includes/header.php'; ?>

<h2 style="text-align:center;">Ajouter un utilisateur</h2>
<form method="post" action="/user/create" style="text-align:center;">
    Email: <input type="email" name="email" required><br><br>
    Username: <input type="text" name="username" required><br><br>
    Mot de passe: <input type="password" name="password" required><br><br>
    <button type="submit">Ajouter</button>
</form>
<p style="color:red; text-align:center;"><?= $message ?></p>
<p style="text-align:center;"><a href="/users">Retour à la liste</a></p>

<?php include 'includes/footer.php'; ?>
