<?php
require 'config/config.php';
if (!isset($_SESSION['user_id'])) {
    Flight::redirect('/login');
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<h2 style="text-align:center;">Liste des utilisateurs</h2>
<p style="text-align:center;"><a href="/user/create">Ajouter un utilisateur</a></p>

<table border="1" cellpadding="5" cellspacing="0" style="margin:auto;">
    <tr>
        <th>ID</th>
        <th>Email</th>
        <th>Username</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($users as $user): ?>
    <tr>
        <td><?= $user['id'] ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td><?= htmlspecialchars($user['username']) ?></td>
        <td>
            <a href="/user/edit/<?= $user['id'] ?>">Modifier</a> |
            <a href="/user/delete/<?= $user['id'] ?>" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php include 'includes/footer.php'; ?>
