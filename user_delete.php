<?php
require 'config/config.php';
 if (!isset($_SESSION['user_id'])) {
    Flight::redirect('/login');
}

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
}

Flight::redirect('/users'); 
