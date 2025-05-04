<?php
session_start();
require_once __DIR__ . '/../../controller/LivraisonC.php';

$livraisonC = new LivraisonC();

// Get delivery ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: gestion_livraisons.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'modifier_statut':
                if (isset($_POST['status'])) {
                    $livraisonC->modifierStatutLivraison($id, $_POST['status']);
                }
                break;
        }
    }
}

// Get delivery details
$livraison = $livraisonC->afficherLivraison($id);
if (!$livraison) {
    header('Location: gestion_livraisons.php');
    exit;
}

// Parse dishes from JSON
$plats = json_decode($livraison['plats'], true);

$page_title = 'Détails de la Livraison #' . $id;
ob_start();
?>

<!-- Customer Information -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-user-circle"></i> Informations Client</h3>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item">
                <i class="fas fa-user"></i>
                <div class="info-content">
                    <label>Nom</label>
                    <span><?php echo htmlspecialchars($livraison['customer_name']); ?></span>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-envelope"></i>
                <div class="info-content">
                    <label>Email</label>
                    <span><?php echo htmlspecialchars($livraison['email']); ?></span>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <div class="info-content">
                    <label>Ville</label>
                    <span><?php echo htmlspecialchars($livraison['city']); ?></span>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-map-pin"></i>
                <div class="info-content">
                    <label>Adresse</label>
                    <span><?php echo htmlspecialchars($livraison['address']); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delivery Information -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-info-circle"></i> Informations de Livraison</h3>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item">
                <i class="fas fa-clock"></i>
                <div class="info-content">
                    <label>Date de livraison</label>
                    <span><?php echo date('d/m/Y H:i', strtotime($livraison['delivery_time'])); ?></span>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-flag"></i>
                <div class="info-content">
                    <label>Statut</label>
                    <form method="POST" class="status-form">
                        <input type="hidden" name="action" value="modifier_statut">
                        <select name="status" onchange="this.form.submit()" class="status-select">
                            <option value="en attente" <?php echo $livraison['status'] === 'en attente' ? 'selected' : ''; ?>>
                                En attente
                            </option>
                            <option value="en préparation" <?php echo $livraison['status'] === 'en préparation' ? 'selected' : ''; ?>>
                                En préparation
                            </option>
                            <option value="livré" <?php echo $livraison['status'] === 'livré' ? 'selected' : ''; ?>>
                                Livré
                            </option>
                        </select>
                    </form>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-calendar-plus"></i>
                <div class="info-content">
                    <label>Date de création</label>
                    <span><?php echo date('d/m/Y H:i', strtotime($livraison['created_at'])); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ordered Dishes -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-utensils"></i> Plats Commandés</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hamburger"></i> Plat</th>
                        <th><i class="fas fa-sort-amount-up"></i> Quantité</th>
                        <th><i class="fas fa-tag"></i> Prix unitaire</th>
                        <th><i class="fas fa-calculator"></i> Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    foreach ($plats as $plat): 
                        $subtotal = $plat['price'] * $plat['quantity'];
                        $total += $subtotal;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($plat['name']); ?></td>
                        <td><?php echo htmlspecialchars($plat['quantity']); ?></td>
                        <td><?php echo number_format($plat['price'], 2); ?> DT</td>
                        <td><?php echo number_format($subtotal, 2); ?> DT</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="3">Total</td>
                        <td><strong><?php echo number_format($total, 2); ?> DT</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="action-buttons">
    <a href="gestion_livraisons.php" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i>
        Retour
    </a>
</div>

<?php
$content = ob_get_clean();

$additional_css = '
<style>
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 15px;
        background-color: var(--darker-bg);
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .info-item:hover {
        background-color: rgba(201, 168, 108, 0.1);
    }

    .info-item i {
        font-size: 24px;
        color: var(--gold-primary);
        margin-top: 5px;
    }

    .info-content {
        flex: 1;
    }

    .info-content label {
        display: block;
        color: var(--gold-light);
        font-size: 14px;
        margin-bottom: 5px;
    }

    .info-content span {
        color: var(--light-text);
        font-size: 16px;
    }

    .status-form {
        margin: 0;
    }

    .status-select {
        background-color: var(--darker-bg);
        border: 1px solid var(--gold-primary);
        color: var(--light-text);
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
    }

    .status-select:hover {
        background-color: rgba(201, 168, 108, 0.1);
    }

    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table th {
        background-color: var(--darker-bg);
        color: var(--gold-light);
        font-weight: 600;
        padding: 15px;
        border-bottom: 2px solid var(--gold-primary);
    }

    .table td {
        padding: 15px;
        border-bottom: 1px solid rgba(201, 168, 108, 0.1);
        vertical-align: middle;
    }

    .table tr:hover {
        background-color: rgba(201, 168, 108, 0.05);
    }

    .total-row {
        background-color: var(--darker-bg);
        font-weight: 600;
    }

    .total-row td {
        color: var(--gold-light);
    }

    .action-buttons {
        margin-top: 20px;
        text-align: center;
    }

    @media screen and (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .table {
            display: block;
            overflow-x: auto;
        }
    }
</style>';

require_once 'layout.php';
?> 