<?php
$page_title = 'Le Bistrot - Suivi Commande';
$page_header = false;

require_once __DIR__ . '/../../controller/CommandeC.php';
require_once __DIR__ . '/../../controller/PlatC.php';

$commandeC = new CommandeC();
$platC = new PlatC();

$commande = null;
$commandes = null;
$error = null;

// Handle form submission for order lookup
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $commandes = $commandeC->getCommandesByEmail($email);
        if (!$commandes || count($commandes) === 0) {
            $error = "Aucune commande trouvée pour cet email.";
        }
    } elseif (isset($_POST['order_id'])) {
        $order_id = (int)$_POST['order_id'];
        $commande = $commandeC->afficherCommande($order_id);
        if (!$commande) {
            $error = "Commande introuvable. Veuillez vérifier le numéro de commande.";
        }
    }
} 
// Handle direct order ID access
elseif (isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];
    $commande = $commandeC->afficherCommande($order_id);
    if (!$commande) {
        $error = "Commande introuvable. Veuillez vérifier le numéro de commande.";
    }
}

ob_start();
?>

<!-- Track Order Header -->
<section class="track-header">
    <div class="container">
        <div class="track-header-content" data-aos="fade-up">
            <p class="section-subtitle">Suivi</p>
            <h2 class="section-title">Suivre Votre <span>Commande</span></h2>
        </div>
    </div>
</section>

<!-- Track Order Form -->
<section class="track-form">
    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert" data-aos="fade-up">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="search-container" data-aos="fade-up">
            <div class="search-options">
                <div class="search-option <?php echo (!isset($_POST['email'])) ? 'active' : ''; ?>" data-target="order-id">
                    <i class="fas fa-hashtag"></i>
                    <span>Numéro de Commande</span>
                </div>
                <div class="search-divider">ou</div>
                <div class="search-option <?php echo (isset($_POST['email'])) ? 'active' : ''; ?>" data-target="email">
                    <i class="fas fa-envelope"></i>
                    <span>Email</span>
                </div>
            </div>

            <div class="search-forms">
                <form id="order-id-form" class="search-form <?php echo (!isset($_POST['email'])) ? 'active' : ''; ?>" method="POST">
                    <div class="input-group">
                        <input type="text" name="order_id" class="form-control" placeholder="Entrez votre numéro de commande" required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Rechercher
                        </button>
                    </div>
                </form>

                <form id="email-form" class="search-form <?php echo (isset($_POST['email'])) ? 'active' : ''; ?>" method="POST">
                    <div class="input-group">
                        <input type="email" name="email" class="form-control" placeholder="Entrez votre email" required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Rechercher
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Order Results -->
        <div class="order-results" data-aos="fade-up">
            <?php if ($commande): ?>
                <!-- Single Order Display -->
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">
                            <span class="label">Commande #</span>
                            <span class="value"><?php echo $commande['id']; ?></span>
                        </div>
                        <div class="order-status <?php echo strtolower($commande['status']); ?>">
                            <?php echo $commande['status']; ?>
                        </div>
                    </div>

                    <div class="order-info">
    <div class="info-group">
        <i class="fas fa-user"></i>
        <div class="info-content">
            <span class="label">Client</span>
            <span class="value"><?php echo htmlspecialchars($commande['customer_name']); ?></span>
        </div>
    </div>

    <div class="info-group">
        <i class="fas fa-envelope"></i>
        <div class="info-content">
            <span class="label">Email</span>
            <span class="value"><?php echo htmlspecialchars($commande['email']); ?></span>
        </div>
    </div>

    <div class="info-group">
        <i class="fas fa-map-marker-alt"></i>
        <div class="info-content">
            <span class="label">Ville</span>
            <span class="value"><?php echo htmlspecialchars($commande['city']); ?></span>
        </div>
    </div>

    <div class="info-group">
        <i class="fas fa-clock"></i>
        <div class="info-content">
            <span class="label">Heure de Livraison</span>
            <span class="value"><?php echo $commande['delivery_time']; ?></span>
        </div>
    </div>
</div>

<?php if (!empty($commande['depart_place']) && !empty($commande['arrive_place'])): ?>
    <div class="mt-4 w-100">
    <div class="d-flex align-items-center mb-2">
        <i class="fas fa-route me-2"></i>
        <span class="label">Itinéraire de Livraison</span>
    </div>
    <div id="map" class="map-container"></div>
</div>

<?php endif; ?>


                    <?php 
                    $plats_commande = $commandeC->getOrderDishes($commande['id']);
                    if ($plats_commande): 
                    ?>
                    <div class="order-items">
                        <h3>Articles Commandés</h3>
                        <div class="items-grid">
                            <?php foreach ($plats_commande as $plat): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <?php if (!empty($plat['image_url'])): ?>
                                        <img src="<?php echo $plat['image_url']; ?>" alt="<?php echo htmlspecialchars($plat['name']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-image">
                                            <i class="fas fa-utensils"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($plat['name']); ?></h4>
                                    <div class="item-meta">
                                        <span class="quantity"><?php echo $plat['quantity']; ?>x</span>
                                        <span class="price"><?php echo number_format($plat['price'], 2); ?> DT</span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="order-total">
                        <?php
                        $subtotal = 0;
                        foreach ($plats_commande as $plat) {
                            $subtotal += $plat['price'] * $plat['quantity'];
                        }
                        $delivery_fee = 7;
                        $total = $subtotal + $delivery_fee;
                        ?>
                        <div class="total-row">
                            <span>Sous-total</span>
                            <span><?php echo number_format($subtotal, 2); ?> DT</span>
                        </div>
                        <div class="total-row">
                            <span>Frais de livraison</span>
                            <span><?php echo number_format($delivery_fee, 2); ?> DT</span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total</span>
                            <span><?php echo number_format($total, 2); ?> DT</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($commandes): ?>
                <!-- Multiple Orders Display -->
                <div class="orders-list">
                    <?php foreach ($commandes as $cmd): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-id">
                                <span class="label">Commande #</span>
                                <span class="value"><?php echo $cmd['id']; ?></span>
                            </div>
                            <div class="order-status <?php echo strtolower($cmd['status']); ?>">
                                <?php echo $cmd['status']; ?>
                            </div>
                        </div>

                        <div class="order-info">
                            <div class="info-group">
                                <i class="fas fa-user"></i>
                                <div class="info-content">
                                    <span class="label">Client</span>
                                    <span class="value"><?php echo htmlspecialchars($cmd['customer_name']); ?></span>
                                </div>
                            </div>
                            <div class="info-group">
                                <i class="fas fa-envelope"></i>
                                <div class="info-content">
                                    <span class="label">Email</span>
                                    <span class="value"><?php echo htmlspecialchars($cmd['email']); ?></span>
                                </div>
                            </div>
                            <div class="info-group">
                                <i class="fas fa-map-marker-alt"></i>
                                <div class="info-content">
                                    <span class="label">Ville</span>
                                    <span class="value"><?php echo htmlspecialchars($cmd['city']); ?></span>
                                </div>
                            </div>
                            <div class="info-group">
                                <i class="fas fa-clock"></i>
                                <div class="info-content">
                                    <span class="label">Heure de Livraison</span>
                                    <span class="value"><?php echo $cmd['delivery_time']; ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="order-actions">
                            <a href="?id=<?php echo $cmd['id']; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-eye"></i>
                                Voir les détails
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.min.js"></script>
<?php if (!empty($commande['depart_place']) && !empty($commande['arrive_place'])): ?>
<style>
    #map {
        height: 400px;
        width: 100%;
        max-width: 100%;
        border-radius: 10px;
        border: 1px solid #ccc;
        margin-top: 15px;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const [depLat, depLng] = "<?php echo $commande['depart_place']; ?>".split(',').map(Number);
        const [arrLat, arrLng] = "<?php echo $commande['arrive_place']; ?>".split(',').map(Number);

        const map = L.map('map').setView([depLat, depLng], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const control = L.Routing.control({
    waypoints: [
        L.latLng(depLat, depLng),
        L.latLng(arrLat, arrLng)
    ],
    routeWhileDragging: false,
    draggableWaypoints: false,
    addWaypoints: false,
    show: false // hide the floating route summary
}).addTo(map);

control.on('routesfound', function(e) {
    const route = e.routes[0];
    const durationInSeconds = route.summary.totalTime;

    const minutes = Math.round(durationInSeconds / 60);
    const durationText = `Durée estimée: ${minutes} minutes`;

    const durationDiv = document.createElement('div');
    durationDiv.className = 'delivery-duration mt-2';
    durationDiv.textContent = durationText;

    document.getElementById('map').parentElement.appendChild(durationDiv);
});

    });
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();

$additional_css = '
<style>

.delivery-duration {
    font-weight: 500;
    font-size: 16px;
    color: #ffc107; /* Bootstrap warning color */
}

    /* Track Header */
    .track-header {
        background-color: var(--secondary-dark);
        padding: 60px 0;
        margin-bottom: 50px;
        text-align: center;
    }

    /* Track Form */
    .track-form {
        padding: 0 0 100px;
    }

    .search-container {
        max-width: 800px;
        margin: 0 auto 50px;
    }

    .search-options {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 30px;
        margin-bottom: 30px;
    }

    .search-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px 30px;
        background-color: var(--secondary-dark);
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .search-option.active {
        background-color: var(--accent-color);
        color: var(--primary-dark);
    }

    .search-option i {
        font-size: 18px;
    }

    .search-divider {
        color: var(--text-secondary);
        font-size: 14px;
        text-transform: uppercase;
    }

    .search-forms {
        position: relative;
    }

    .search-form {
        display: none;
    }

    .search-form.active {
        display: block;
    }

    .input-group {
        display: flex;
        gap: 15px;
    }

    .input-group .form-control {
        flex: 1;
    }

    /* Order Results */
    .order-card {
        background-color: var(--secondary-dark);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 30px;
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .order-id {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .order-id .label {
        color: var(--text-secondary);
        font-size: 14px;
    }

    .order-id .value {
        font-size: 18px;
        font-weight: 600;
        color: var(--accent-color);
    }

    .order-status {
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 12px;
        text-transform: uppercase;
        font-weight: 500;
    }

    .order-status.pending {
        background-color: var(--warning-color);
        color: var(--primary-dark);
    }

    .order-status.processing {
        background-color: var(--accent-color);
        color: var(--primary-dark);
    }

    .order-status.completed {
        background-color: var(--success-color);
        color: var(--text-primary);
    }

    .order-status.cancelled {
        background-color: var(--error-color);
        color: var(--text-primary);
    }

    .order-info {
        padding: 20px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .info-group {
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .info-group i {
        color: var(--accent-color);
        font-size: 20px;
    }

    .info-content {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .info-content .label {
        color: var(--text-secondary);
        font-size: 12px;
    }

    .info-content .value {
        color: var(--text-primary);
        font-size: 14px;
    }

    .order-items {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .order-items h3 {
        color: var(--text-primary);
        font-size: 18px;
        margin-bottom: 20px;
        font-family: "Playfair Display", serif;
    }

    .items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .order-item {
        display: flex;
        gap: 15px;
        background-color: var(--card-bg);
        padding: 15px;
        border-radius: 8px;
    }

    .item-image {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
    }

    .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .item-details {
        flex: 1;
    }

    .item-details h4 {
        color: var(--text-primary);
        font-size: 16px;
        margin-bottom: 10px;
    }

    .item-meta {
        display: flex;
        justify-content: space-between;
        color: var(--text-secondary);
        font-size: 14px;
    }

    .order-total {
        padding: 20px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        color: var(--text-secondary);
    }

    .total-row.grand-total {
        color: var(--text-primary);
        font-size: 18px;
        font-weight: 600;
        border-top: 1px solid var(--border-color);
        margin-top: 10px;
        padding-top: 20px;
    }

    @media (max-width: 768px) {
        .search-options {
            flex-direction: column;
            gap: 15px;
        }

        .search-option {
            width: 100%;
            justify-content: center;
        }

        .input-group {
            flex-direction: column;
        }

        .order-info {
            grid-template-columns: 1fr;
        }

        .items-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Alert Styles */
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-danger {
        background-color: var(--error-color);
        color: var(--text-primary);
    }

    .alert i {
        font-size: 20px;
    }

    /* Orders List Styles */
    .orders-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .order-actions {
        padding: 20px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
    }

    .btn-outline-primary {
        border: 1px solid var(--accent-color);
        color: var(--accent-color);
        background: transparent;
        padding: 8px 20px;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background-color: var(--accent-color);
        color: var(--primary-dark);
    }
</style>';

$additional_js = '
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchOptions = document.querySelectorAll(".search-option");
        const searchForms = document.querySelectorAll(".search-form");

        searchOptions.forEach(option => {
            option.addEventListener("click", function() {
                // Remove active class from all options and forms
                searchOptions.forEach(opt => opt.classList.remove("active"));
                searchForms.forEach(form => form.classList.remove("active"));

                // Add active class to clicked option and corresponding form
                this.classList.add("active");
                const targetForm = document.getElementById(this.dataset.target + "-form");
                targetForm.classList.add("active");
            });
        });
    });
</script>';

require_once "layout.php";
?> 

<?php if (!empty($commande['depart_place']) && !empty($commande['arrive_place'])): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const [depLat, depLng] = "<?php echo $commande['depart_place']; ?>".split(',').map(Number);
    const [arrLat, arrLng] = "<?php echo $commande['arrive_place']; ?>".split(',').map(Number);

    const map = L.map('map').setView([depLat, depLng], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.Routing.control({
        waypoints: [
            L.latLng(depLat, depLng),
            L.latLng(arrLat, arrLng)
        ],
        routeWhileDragging: false,
        draggableWaypoints: false,
        addWaypoints: false
    }).addTo(map);
});
</script>
<?php endif; ?>
