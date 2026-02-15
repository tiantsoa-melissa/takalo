<?php
require 'config/config.php';
require 'includes/header.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$objet_demande_id = isset($_GET['objet']) ? (int)$_GET['objet'] : 0;
$success_message = '';
$error_message = '';

if ($objet_demande_id === 0) {
    header('Location: /home');
    exit;
}

// Récupération des informations de l'objet demandé
$sql = "SELECT o.*, u.username as proprietaire_nom 
        FROM objets o 
        LEFT JOIN users u ON o.proprietaire = u.id
        WHERE o.id = ? AND o.status = 'disponible'";

$stmt = $pdo->prepare($sql);
$stmt->execute([$objet_demande_id]);
$objet_demande = $stmt->fetch();

if (!$objet_demande) {
    header('Location: /home');
    exit;
}

// Vérifier que l'utilisateur n'est pas le propriétaire
if ($objet_demande['proprietaire'] == $_SESSION['user_id']) {
    header('Location: /objet/' . $objet_demande_id);
    exit;
}

// Récupération des objets de l'utilisateur connecté
$mesObjetsSql = "SELECT * FROM objets WHERE proprietaire = ? AND status = 'disponible' ORDER BY nom";
$mesObjetsStmt = $pdo->prepare($mesObjetsSql);
$mesObjetsStmt->execute([$_SESSION['user_id']]);
$mesObjets = $mesObjetsStmt->fetchAll();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $objet_propose_id = isset($_POST['objet_propose']) ? (int)$_POST['objet_propose'] : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    if ($objet_propose_id === 0) {
        $error_message = 'Veuillez sélectionner un objet à proposer.';
    } else {
        // Vérifier que l'objet proposé appartient bien à l'utilisateur
        $verificationSql = "SELECT id FROM objets WHERE id = ? AND proprietaire = ? AND status = 'disponible'";
        $verificationStmt = $pdo->prepare($verificationSql);
        $verificationStmt->execute([$objet_propose_id, $_SESSION['user_id']]);
        
        if (!$verificationStmt->fetch()) {
            $error_message = 'Objet proposé invalide.';
        } else {
            // Vérifier qu'une demande similaire n'existe pas déjà
            $existingSql = "SELECT id FROM demandes_echange 
                           WHERE objet_demande = ? AND objet_propose = ? AND demandeur = ? 
                           AND status = 'en_attente'";
            $existingStmt = $pdo->prepare($existingSql);
            $existingStmt->execute([$objet_demande_id, $objet_propose_id, $_SESSION['user_id']]);
            
            if ($existingStmt->fetch()) {
                $error_message = 'Vous avez déjà fait une demande d\'échange pour ces objets.';
            } else {
                // Insérer la demande d'échange
                $insertSql = "INSERT INTO demandes_echange (objet_propose, objet_demande, demandeur, proprietaire, message)
                             VALUES (?, ?, ?, ?, ?)";
                $insertStmt = $pdo->prepare($insertSql);
                
                if ($insertStmt->execute([$objet_propose_id, $objet_demande_id, $_SESSION['user_id'], $objet_demande['proprietaire'], $message])) {
                    $success_message = 'Votre demande d\'échange a été envoyée avec succès !';
                } else {
                    $error_message = 'Erreur lors de l\'envoi de la demande.';
                }
            }
        }
    }
}
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/home">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/objet/<?= $objet_demande_id ?>">
                <?= htmlspecialchars($objet_demande['nom']) ?>
            </a></li>
            <li class="breadcrumb-item active">Demande d'échange</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-exchange-alt"></i> 
                        Proposer un échange
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?= $success_message ?>
                            <div class="mt-2">
                                <a href="/objet/<?= $objet_demande_id ?>" class="btn btn-sm btn-outline-primary">
                                    Retour à l'objet
                                </a>
                                <a href="/echange/gestion" class="btn btn-sm btn-primary ms-2">
                                    Mes demandes
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?= $error_message ?>
                            </div>
                        <?php endif; ?>

                        <!-- Objet demandé -->
                        <div class="card mb-4 bg-light">
                            <div class="card-body">
                                <h5 class="card-title text-success">
                                    <i class="fas fa-bullseye"></i> Objet souhaité
                                </h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <?php if (!empty($objet_demande['photo'])): ?>
                                            <img src="uploads/<?= htmlspecialchars($objet_demande['photo']) ?>" 
                                                 class="img-fluid rounded" 
                                                 alt="<?= htmlspecialchars($objet_demande['nom']) ?>">
                                        <?php else: ?>
                                            <div class="bg-white rounded d-flex align-items-center justify-content-center" 
                                                 style="height: 100px;">
                                                <i class="fas fa-image fa-2x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-9">
                                        <h6><?= htmlspecialchars($objet_demande['nom']) ?></h6>
                                        <p class="text-muted mb-1">
                                            Propriétaire : <?= htmlspecialchars($objet_demande['proprietaire_nom']) ?>
                                        </p>
                                        <p class="small mb-0">
                                            <?= htmlspecialchars(substr($objet_demande['description'], 0, 150)) ?>
                                            <?= strlen($objet_demande['description']) > 150 ? '...' : '' ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (empty($mesObjets)): ?>
                            <div class="alert alert-warning">
                                <h5>Aucun objet disponible</h5>
                                <p>Vous n'avez aucun objet disponible pour proposer un échange.</p>
                                <a href="/objets/ajouter" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Ajouter un objet
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Formulaire de demande -->
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-gift"></i> <strong>Votre objet à proposer :</strong>
                                    </label>
                                    <div class="row">
                                        <?php foreach ($mesObjets as $index => $monObjet): ?>
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card">
                                                    <input type="radio" name="objet_propose" value="<?= $monObjet['id'] ?>" 
                                                           id="objet_<?= $monObjet['id'] ?>" 
                                                           class="d-none objet-radio"
                                                           <?= $index === 0 ? 'checked' : '' ?>>
                                                    <label for="objet_<?= $monObjet['id'] ?>" class="card-body p-2 text-center objet-label" 
                                                           style="cursor: pointer;">
                                                        <?php if (!empty($monObjet['photo'])): ?>
                                                            <img src="uploads/<?= htmlspecialchars($monObjet['photo']) ?>" 
                                                                 class="img-fluid rounded mb-2" 
                                                                 style="height: 80px; object-fit: cover;"
                                                                 alt="<?= htmlspecialchars($monObjet['nom']) ?>">
                                                        <?php else: ?>
                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center mb-2" 
                                                                 style="height: 80px;">
                                                                <i class="fas fa-image text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <h6 class="card-title small mb-0">
                                                            <?= htmlspecialchars($monObjet['nom']) ?>
                                                        </h6>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">
                                        <i class="fas fa-comment"></i> Message (optionnel)
                                    </label>
                                    <textarea name="message" id="message" class="form-control" rows="4" 
                                              placeholder="Expliquez pourquoi cet échange vous intéresse..."></textarea>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="/objet/<?= $objet_demande_id ?>" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Annuler
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-paper-plane"></i> Envoyer la demande
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.objet-radio:checked + .objet-label {
    border: 2px solid #28a745 !important;
    background-color: #f8fff9 !important;
}
.objet-label {
    border: 2px solid #dee2e6;
    transition: all 0.2s;
}
.objet-label:hover {
    border-color: #28a745;
    background-color: #f8fff9;
}
</style>

<?php require 'includes/footer.php'; ?>