<!-- includes/header.php -->
<header style="background:#f2f2f2; padding:10px; margin-bottom:20px;">
    <h1 style="text-align:center;">Projet Takalo - Gestion Utilisateurs</h1>
    <?php if (isset($_SESSION['user_id'])): ?>
        <p style="text-align:center;">
            Connecté en tant que <?= htmlspecialchars($_SESSION['username']) ?> |
            <a href="/logout">Déconnexion</a>
        </p>
    <?php endif; ?>
</header>
