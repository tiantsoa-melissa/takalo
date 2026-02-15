<?php
require 'config/config.php';
require 'includes/header.php';

$objet_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($objet_id === 0) {
    header('Location: /home');
    exit;
}

// Récupération des détails de l'objet
$sql = "SELECT o.*, u.username as proprietaire_nom, u.email as proprietaire_email, c.nom as categorie_nom 
        FROM objets o 
        LEFT JOIN users u ON o.proprietaire = u.id
        LEFT JOIN categorie c ON o.categorie_id = c.id
        WHERE o.id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$objet_id]);
$objet = $stmt->fetch();

if (!$objet) {
    header('Location: /home');
    exit;
}

// Récupération des autres objets du même propriétaire
$autresObjetsSql = "SELECT * FROM objets WHERE proprietaire = ? AND id != ? AND status = 'disponible' LIMIT 4";
$autresObjetsStmt = $pdo->prepare($autresObjetsSql);
$autresObjetsStmt->execute([$objet['proprietaire'], $objet_id]);
$autresObjets = $autresObjetsStmt->fetchAll();

// Récupération des objets similaires (même catégorie)
$objetsSimilairesSql = "SELECT o.*, u.username as proprietaire_nom
                        FROM objets o 
                        LEFT JOIN users u ON o.proprietaire = u.id
                        WHERE o.categorie_id = ? AND o.id != ? AND o.status = 'disponible' 
                        LIMIT 4";
$objetsSimilairesStmt = $pdo->prepare($objetsSimilairesSql);
$objetsSimilairesStmt->execute([$objet['categorie_id'], $objet_id]);
$objetsSimilaires = $objetsSimilairesStmt->fetchAll();

$isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $objet['proprietaire'];
$canExchange = isset($_SESSION['user_id']) && !$isOwner && $objet['status'] == 'disponible';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/home">Accueil</a></li>
            <?php if ($objet['categorie_nom']): ?>
                <li class="breadcrumb-item">
                    <a href="/home?categorie=<?= $objet['categorie_id'] ?>">
                        <?= htmlspecialchars($objet['categorie_nom']) ?>
                    </a>
                </li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?= htmlspecialchars($objet['nom']) ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <?php if (!empty($objet['photo'])): ?>
                <img src="uploads/<?= htmlspecialchars($objet['photo']) ?>" 
                     class="img-fluid rounded shadow" 
                     alt="<?= htmlspecialchars($objet['nom']) ?>">
            <?php else: ?>
                <div class="bg-light rounded d-flex align-items-center justify-content-center shadow" 
                     style="height: 400px;">
                    <i class="fas fa-image fa-5x text-muted"></i>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-6">
            <h1 class="mb-3"><?= htmlspecialchars($objet['nom']) ?></h1>
            
            <div class="mb-3">
                <span class="badge bg-<?= $objet['status'] == 'disponible' ? 'success' : 'warning' ?> fs-6">
                    <?= ucfirst(str_replace('_', ' ', $objet['status'])) ?>
                </span>
                <?php if ($objet['categorie_nom']): ?>
                    <span class="badge bg-secondary fs-6 ms-2">
                        <i class="fas fa-tag"></i> <?= htmlspecialchars($objet['categorie_nom']) ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-align-left"></i> Description</h5>
                </div>
                <div class="card-body">
                    <p class="card-text"><?= nl2br(htmlspecialchars($objet['description'])) ?></p>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Propriétaire</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        <strong><?= htmlspecialchars($objet['proprietaire_nom']) ?></strong><br>
                        <small class="text-muted">
                            Publié le <?= date('d/m/Y à H:i', strtotime($objet['date_creation'])) ?>
                        </small>
                    </p>
                </div>
            </div>
            
            <?php if ($canExchange): ?>
                <div class="d-grid gap-2">
                    <a href="/echange/demande?objet=<?= $objet['id'] ?>" 
                       class="btn btn-success btn-lg">
                        <i class="fas fa-exchange-alt"></i> Proposer un échange
                    </a>
                    <a href="mailto:<?= htmlspecialchars($objet['proprietaire_email']) ?>?subject=Échange pour <?= urlencode($objet['nom']) ?>" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-envelope"></i> Contacter le propriétaire
                    </a>
                </div>
            <?php elseif ($isOwner): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Ceci est votre objet.
                    <a href="/objets/edit/<?= $objet['id'] ?>" class="btn btn-sm btn-outline-primary ms-2">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                </div>
            <?php elseif (!isset($_SESSION['user_id'])): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-sign-in-alt"></i> 
                    <a href="/login">Connectez-vous</a> pour proposer un échange.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Autres objets du même propriétaire -->
    <?php if (!empty($autresObjets)): ?>
        <hr class="my-5">
        <h3>Autres objets de <?= htmlspecialchars($objet['proprietaire_nom']) ?></h3>
        <div class="row">
            <?php foreach ($autresObjets as $autre): ?>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card h-100">
                        <?php if (!empty($autre['photo'])): ?>
                            <img src="uploads/<?= htmlspecialchars($autre['photo']) ?>" 
                                 class="card-img-top" style="height: 150px; object-fit: cover;"
                                 alt="<?= htmlspecialchars($autre['nom']) ?>">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 150px;">
                                <i class="fas fa-image fa-2x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h6 class="card-title"><?= htmlspecialchars($autre['nom']) ?></h6>
                            <a href="/objet/<?= $autre['id'] ?>" class="btn btn-sm btn-outline-primary">
                                Voir détails
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Objets similaires -->
    <?php if (!empty($objetsSimilaires)): ?>
        <hr class="my-5">
        <h3>Objets similaires</h3>
        <div class="row">
            <?php foreach ($objetsSimilaires as $similaire): ?>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card h-100">
                        <?php if (!empty($similaire['photo'])): ?>
                            <img src="uploads/<?= htmlspecialchars($similaire['photo']) ?>" 
                                 class="card-img-top" style="height: 150px; object-fit: cover;"
                                 alt="<?= htmlspecialchars($similaire['nom']) ?>">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 150px;">
                                <i class="fas fa-image fa-2x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h6 class="card-title"><?= htmlspecialchars($similaire['nom']) ?></h6>
                            <small class="text-muted">Par <?= htmlspecialchars($similaire['proprietaire_nom']) ?></small><br>
                            <a href="/objet/<?= $similaire['id'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                                Voir détails
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>