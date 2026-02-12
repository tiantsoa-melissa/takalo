<?php
require 'config/config.php';
if (!isset($_SESSION['user_id'])) {
    Flight::redirect('/login');
}

$id = $_GET['id'] ?? null;
if (!$id) { Flight::redirect('/users'); }

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user)  { Flight::redirect('/users'); }

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'] ?? '';

    if ($password) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET email=?, username=?, password_hash=? WHERE id=?");
        $stmt->execute([$email, $username, $password_hash, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET email=?, username=? WHERE id=?");
        $stmt->execute([$email, $username, $id]);
    }
    Flight::redirect('/users');
}
?>

<?php include 'includes/header.php'; ?>

<h2 style="text-align:center;">Modifier utilisateur</h2>
<form method="post" action="/user/edit/<?= $id ?>" style="text-align:center;">
    Email: <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>
    Username: <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br><br>
    Mot de passe (laisser vide pour garder l'ancien): <input type="password" name="password"><br><br>
    <button type="submit">Modifier</button>
</form>
<p style="color:red; text-align:center;"><?= $message ?></p>
<p style="text-align:center;"><a href="/users">Retour à la liste</a></p>

<?php include 'includes/footer.php'; ?>
