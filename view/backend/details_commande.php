<?php
session_start();
require_once __DIR__ . '/../../controller/CommandeC.php';
require_once __DIR__ . '/../../controller/LivraisonC.php';
require_once __DIR__ . '/../../controller/PlatC.php';

// Constants
define('DELIVERY_FEE', 7.00);

$commandeC = new CommandeC();
$livraisonC = new LivraisonC();
$platC = new PlatC();

// Get order ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: gestion_commandes.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'modifier_statut':
                if (isset($_POST['status'])) {
                    $commandeC->modifierStatutCommande($id, $_POST['status']);
                    // Refresh order details after status update
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $id);
                    exit;
                }
                break;
        }
    }
}

// Get order details with total
$commande = $commandeC->getOrderWithTotal($id);
if (!$commande) {
    header('Location: gestion_commandes.php');
    exit;
}

// Get order dishes
$plats = $commandeC->getOrderDishes($id);

// Get delivery details if exists
$livraison = $livraisonC->afficherLivraison($id);

$page_title = 'Détails de la Commande #' . $id;
ob_start();
?>

<!-- Order Status Banner -->
<div class="status-banner <?php echo strtolower($commande['status']); ?>">
    <div class="status-icon">
        <?php switch($commande['status']) {
            case 'en attente':
                echo '<i class="fas fa-clock"></i>';
                break;
            case 'en préparation':
                echo '<i class="fas fa-utensils"></i>';
                break;
            case 'livré':
                echo '<i class="fas fa-check-circle"></i>';
                break;
        } ?>
    </div>
    <div class="status-text">
        <h4>Statut de la Commande</h4>
        <p><?php echo $commande['status']; ?></p>
    </div>
</div>

<!-- Customer Information Card -->
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
                    <span><?php echo htmlspecialchars($commande['customer_name']); ?></span>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-envelope"></i>
                <div class="info-content">
                    <label>Email</label>
                    <span><?php echo htmlspecialchars($commande['email']); ?></span>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <div class="info-content">
                    <label>Ville</label>
                    <span><?php echo htmlspecialchars($commande['city']); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Information Card -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-info-circle"></i> Informations de la Commande</h3>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item">
                <i class="fas fa-clock"></i>
                <div class="info-content">
                    <label>Date de livraison souhaitée</label>
                    <span><?php 
                        $delivery_time = new DateTime($commande['delivery_time']);
                        echo $delivery_time->format('d/m/Y à H:i');
                    ?></span>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-flag"></i>
                <div class="info-content">
                    <label>Statut</label>
                    <form method="POST" class="status-form">
                        <input type="hidden" name="action" value="modifier_statut">
                        <select name="status" class="status-select <?php echo strtolower($commande['status']); ?>">
                            <option value="en attente" <?php echo $commande['status'] === 'en attente' ? 'selected' : ''; ?>>
                                En attente
                            </option>
                            <option value="en préparation" <?php echo $commande['status'] === 'en préparation' ? 'selected' : ''; ?>>
                                En préparation
                            </option>
                            <option value="livré" <?php echo $commande['status'] === 'livré' ? 'selected' : ''; ?>>
                                Livré
                            </option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save"></i> Mettre à jour
                        </button>
                    </form>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-calendar-plus"></i>
                <div class="info-content">
                    <label>Date de création</label>
                    <span><?php 
                        $created_at = new DateTime($commande['created_at']);
                        echo $created_at->format('d/m/Y à H:i');
                    ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Items Card -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-shopping-basket"></i> Articles Commandés</h3>
    </div>
    <div class="card-body">
        <?php if (empty($plats)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Aucun plat dans cette commande.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Plat</th>
                            <th>Prix unitaire</th>
                            <th>Quantité</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotal = 0;
                        foreach ($plats as $plat): 
                            $item_total = $plat['price'] * $plat['quantity'];
                            $subtotal += $item_total;
                        ?>
                        <tr>
                            <td>
                                <div class="dish-info">
                                    <?php if (!empty($plat['image_url'])): ?>
                                        <img src="../../<?php echo htmlspecialchars($plat['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($plat['name']); ?>" 
                                             class="dish-thumbnail">
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($plat['name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo number_format($plat['price'], 2); ?> DT</td>
                            <td><?php echo $plat['quantity']; ?></td>
                            <td><?php echo number_format($item_total, 2); ?> DT</td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total-row subtotal">
                            <td colspan="3">Sous-total</td>
                            <td><?php echo number_format($subtotal, 2); ?> DT</td>
                        </tr>
                        <tr class="total-row delivery">
                            <td colspan="3">Frais de livraison</td>
                            <td><?php echo number_format(DELIVERY_FEE, 2); ?> DT</td>
                        </tr>
                        <tr class="total-row grand-total">
                            <td colspan="3"><strong>Total</strong></td>
                            <td class="total-amount"><?php echo number_format($subtotal + DELIVERY_FEE, 2); ?> DT</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="action-buttons">
    <a href="gestion_commandes.php" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i>
        Retour
    </a>
</div>

<?php
$content = ob_get_clean();

$additional_css = '
<style>
    /* Status Banner */
    .status-banner {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px;
        margin-bottom: 30px;
        border-radius: 10px;
        background-color: var(--darker-bg);
        transition: all 0.3s ease;
    }

    .status-banner.en.attente {
        border-left: 4px solid #ffc107;
    }

    .status-banner.en.preparation {
        border-left: 4px solid #0dcaf0;
    }

    .status-banner.livre {
        border-left: 4px solid #198754;
    }

    .status-icon {
        font-size: 2.5rem;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: rgba(201, 168, 108, 0.1);
    }

    .status-banner.en.attente .status-icon {
        color: #ffc107;
    }

    .status-banner.en.preparation .status-icon {
        color: #0dcaf0;
    }

    .status-banner.livre .status-icon {
        color: #198754;
    }

    .status-text h4 {
        margin: 0;
        color: var(--gold-light);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .status-text p {
        margin: 5px 0 0;
        font-size: 1.2rem;
        color: var(--light-text);
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 20px;
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

    /* Status Select */
    .status-select {
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid var(--border-color);
        background-color: var(--darker-bg);
        color: var(--light-text);
        font-size: 14px;
        margin-right: 10px;
    }

    .status-select.en.attente {
        border-color: #ffc107;
    }

    .status-select.en.preparation {
        border-color: #0dcaf0;
    }

    .status-select.livre {
        border-color: #198754;
    }

    /* Table Styles */
    .table {
        margin-top: 20px;
    }

    .dish-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .dish-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
    }

    .total-row {
        background-color: var(--darker-bg);
    }

    .total-row td {
        padding: 15px !important;
    }

    .total-row.subtotal {
        color: var(--text-secondary);
    }

    .total-row.delivery {
        color: var(--text-secondary);
        border-top: 1px solid var(--border-color);
    }

    .total-row.grand-total {
        color: var(--gold-primary);
        font-size: 1.1rem;
        border-top: 2px solid var(--border-color);
    }

    .total-amount {
        font-weight: bold;
    }

    /* Action Buttons */
    .action-buttons {
        margin-top: 30px;
        display: flex;
        gap: 15px;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 14px;
    }
</style>';

require_once 'layout.php';
?> 