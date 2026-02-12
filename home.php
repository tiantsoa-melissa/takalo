<?php
require 'config/config.php';
require 'includes/header.php';

// Récupération des paramètres de recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categorie = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

// Construction de la requête de recherche
$where = "WHERE o.status = 'disponible'";
$params = [];

if (!empty($search)) {
    $where .= " AND (o.nom LIKE ? OR o.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categorie > 0) {
    $where .= " AND o.categorie_id = ?";
    $params[] = $categorie;
}

// Requête pour compter le nombre total d'objets
$countSql = "SELECT COUNT(*) FROM objets o $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalObjets = $countStmt->fetchColumn();
$totalPages = ceil($totalObjets / $limit);

// Requête principale pour récupérer les objets
$sql = "SELECT o.*, u.username as proprietaire_nom, c.nom as categorie_nom 
        FROM objets o 
        LEFT JOIN users u ON o.proprietaire = u.id
        LEFT JOIN categorie c ON o.categorie_id = c.id
        $where
        ORDER BY o.date_creation DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$objets = $stmt->fetchAll();

// Récupération des catégories pour le filtre
$categoriesStmt = $pdo->query("SELECT * FROM categorie ORDER BY nom");
$categories = $categoriesStmt->fetchAll();
?>

<div class="container mt-4">
    <h1 class="mb-4">Objets disponibles pour échange</h1>
    
    <!-- Barre de recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/home" class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Rechercher par nom ou description..."
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <select name="categorie" class="form-select">
                        <option value="0">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" 
                                    <?= $categorie == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Résultats de recherche -->
    <?php if (!empty($search) || $categorie > 0): ?>
        <div class="alert alert-info">
            <?= $totalObjets ?> objet(s) trouvé(s) 
            <?php if (!empty($search)): ?>
                pour "<?= htmlspecialchars($search) ?>"
            <?php endif; ?>
            <?php if ($categorie > 0): ?>
                dans la catégorie "<?= htmlspecialchars($categories[array_search($categorie, array_column($categories, 'id'))]['nom'] ?? '') ?>"
            <?php endif; ?>
            <a href="/home" class="btn btn-sm btn-outline-secondary ms-2">Effacer les filtres</a>
        </div>
    <?php endif; ?>

    <!-- Grille d'objets -->
    <?php if (empty($objets)): ?>
        <div class="alert alert-warning text-center">
            <h4>Aucun objet trouvé</h4>
            <p class="mb-0">Essayez de modifier vos critères de recherche.</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($objets as $objet): ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($objet['photo'])): ?>
                            <img src="uploads/<?= htmlspecialchars($objet['photo']) ?>" 
                                 class="card-img-top" style="height: 200px; object-fit: cover;"
                                 alt="<?= htmlspecialchars($objet['nom']) ?>">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($objet['nom']) ?></h5>
                            
                            <?php if ($objet['categorie_nom']): ?>
                                <small class="text-muted mb-2">
                                    <i class="fas fa-tag"></i> <?= htmlspecialchars($objet['categorie_nom']) ?>
                                </small>
                            <?php endif; ?>
                            
                            <p class="card-text text-muted small mb-2">
                                Par <?= htmlspecialchars($objet['proprietaire_nom']) ?>
                            </p>
                            
                            <p class="card-text flex-grow-1">
                                <?= htmlspecialchars(substr($objet['description'], 0, 100)) ?>
                                <?= strlen($objet['description']) > 100 ? '...' : '' ?>
                            </p>
                            
                            <div class="mt-auto">
                                <a href="/objet/<?= $objet['id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> Voir détails
                                </a>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $objet['proprietaire']): ?>
                                    <a href="/echange/demande?objet=<?= $objet['id'] ?>" 
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-exchange-alt"></i> Échanger
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Navigation pagination">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&categorie=<?= $categorie ?>">
                                Précédent
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&categorie=<?= $categorie ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&categorie=<?= $categorie ?>">
                                Suivant
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>