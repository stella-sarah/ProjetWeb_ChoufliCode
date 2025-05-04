<?php
$page_title = 'Le Bistrot - Commander';
$page_header = false;

require_once __DIR__ . '/../../config';
require_once __DIR__ . '/../../controller/PlatC.php';
require_once __DIR__ . '/../../controller/CommandeC.php';

$platC = new PlatC();
$commandeC = new CommandeC();

// Debug information
error_reporting(E_ALL);
ini_set('display_errors', 1);

$plats = $platC->afficherPlats();
$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Processing form submission...");
    error_log("POST data: " . print_r($_POST, true));
    
    if (
        isset(
            $_POST['customer_name'],
            $_POST['email'],
            $_POST['city'],
            $_POST['delivery_time'],
            $_POST['quantity'],
            $_POST['depart_place'],
            $_POST['arrive_place']
        )
    ) {
        $has_items = false;
        foreach ($_POST['quantity'] as $dish_id => $quantity) {
            if ((int)$quantity > 0) {
                $has_items = true;
                break;
            }
        }
        
        if ($has_items) {
            try {
                // Create the order with new place fields
                $order_id = $commandeC->ajouterCommande(
                    $_POST['customer_name'],
                    $_POST['email'],
                    $_POST['city'],
                    $_POST['delivery_time'],
                    $_POST['depart_place'],
                    $_POST['arrive_place']
                );
                
                error_log("Order created with ID: " . ($order_id ? $order_id : "false"));
                
                if ($order_id) {
                    $success = true;
                    // Add dishes to order
                    foreach ($_POST['quantity'] as $dish_id => $quantity) {
                        if ((int)$quantity > 0) {
                            $result = $commandeC->ajouterPlatCommande($order_id, (int)$dish_id, (int)$quantity);
                            if (!$result) {
                                error_log("Failed to add dish ID $dish_id to order $order_id");
                                $success = false;
                            }
                        }
                    }
                    
                    if ($success) {
                        header("Location: suivi_commande.php?id=" . $order_id);
                        exit;
                    } else {
                        $error_message = "Erreur lors de l'ajout des plats à la commande.";
                    }
                } else {
                    $error_message = "Erreur lors de la création de la commande.";
                }
            } catch (Exception $e) {
                error_log("Error in order creation: " . $e->getMessage());
                $error_message = "Une erreur est survenue lors de la création de la commande.";
            }
        } else {
            $error_message = "Veuillez sélectionner au moins un plat.";
        }
    } else {
        $error_message = "Veuillez remplir tous les champs requis.";
        error_log("Missing required fields. Available fields: " . implode(", ", array_keys($_POST)));
    }
}

ob_start();
?>

<?php if ($error_message): ?>
<div class="alert alert-danger">
    <?php echo htmlspecialchars($error_message); ?>
</div>
<?php endif; ?>

<?php if ($success_message): ?>
<div class="alert alert-success">
    <?php echo htmlspecialchars($success_message); ?>
</div>
<?php endif; ?>

<!-- Order Header -->
<section class="order-header">
    <div class="container">
        <div class="order-header-content" data-aos="fade-up">
            <p class="section-subtitle">Commandez en ligne</p>
            <h2 class="section-title">Votre <span class="accent">Commande</span></h2>
            <p class="header-description">Sélectionnez vos plats préférés et nous vous les livrerons à votre porte</p>
        </div>
    </div>
</section>

<!-- Order Summary -->
<section class="order-summary" data-aos="fade-up">
    <div class="container">
        <div class="summary-card">
            <h3>Résumé de la Commande</h3>
            <div class="summary-items">
                <!-- Will be populated by JavaScript -->
            </div>
            <div class="summary-total">
                <div class="subtotal">
                    <span>Sous-total:</span>
                    <span class="amount">0.00 DT</span>
                </div>
                <div class="delivery-fee">
                    <span>Frais de livraison:</span>
                    <span class="amount">7.00 DT</span>
                </div>
                <div class="total">
                    <span>Total:</span>
                    <span class="amount">0.00 DT</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Menu Selection -->
<section class="menu-selection">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h3>Sélectionnez vos Plats</h3>
        </div>
        
        <form id="orderForm" method="POST" onsubmit="return validateOrder()">
            <div class="menu-grid" data-aos="fade-up">
                <?php if ($plats && count($plats) > 0): ?>
                    <?php foreach ($plats as $plat): ?>
                        <div class="menu-item">
                            <div class="item-image">
                                <?php if (!empty($plat['image_url'])): ?>
                                    <img src="../../<?php echo htmlspecialchars($plat['image_url']); ?>" alt="<?php echo htmlspecialchars($plat['name']); ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="placeholder-image">
                                        <i class="fas fa-utensils"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="item-details">
                                <h3 class="item-title"><?php echo htmlspecialchars($plat['name']); ?></h3>
                                <div class="item-price"><?php echo number_format($plat['price'], 2); ?> DT</div>
                                <div class="item-quantity">
                                    <button type="button" class="quantity-btn minus">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" 
                                           name="quantity[<?php echo $plat['id']; ?>]" 
                                           class="quantity-input" 
                                           value="0" 
                                           min="0" 
                                           max="10"
                                           data-price="<?php echo $plat['price']; ?>"
                                           data-name="<?php echo htmlspecialchars($plat['name']); ?>"
                                           data-id="<?php echo $plat['id']; ?>">
                                    <button type="button" class="quantity-btn plus">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-items">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Aucun plat disponible pour le moment.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="customer-info">
                <div class="form-group">
                    <label class="form-label" for="customer_name">
                        <i class="fas fa-user"></i>
                        Nom Complet
                    </label>
                    <input type="text" id="customer_name" name="customer_name" class="form-control" required
                           placeholder="Votre nom complet">
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class="fas fa-envelope"></i>
                        Email
                    </label>
                    <input type="email" id="email" name="email" class="form-control" required
                           placeholder="votre@email.com">
                </div>

                <div class="form-group">
                    <label class="form-label" for="city">
                        <i class="fas fa-map-marker-alt"></i>
                        Ville
                    </label>
                    <input type="text" id="city" name="city" class="form-control" required
                           placeholder="Votre ville">
                </div>

                <div class="form-group">
                    <label class="form-label" for="delivery_time">
                        <i class="fas fa-clock"></i>
                        Date et Heure de Livraison Souhaitée
                    </label>
                    <input type="datetime-local" id="delivery_time" name="delivery_time" class="form-control" required
                           min="<?php echo date('Y-m-d\TH:i'); ?>">
                </div>

                <input type="hidden" name="depart_place" id="depart_place">
                <input type="hidden" name="arrive_place" id="arrive_place">

                  <div class="form-group">
                   <label class="form-label">
                     <i class="fas fa-map"></i> Sélectionnez les emplacements sur la carte
                   </label>
                  <div id="map" style="height: 400px; border: 1px solid #ccc; border-radius: 8px;"></div>
                       <p id="map-instructions" style="margin-top: 8px; color: #555;">Cliquez pour choisir le lieu de départ, puis le lieu d’arrivée.</p>
                    </div>


                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i>
                        Confirmer la Commande
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>


<script>
let map = L.map('map').setView([36.8065, 10.1815], 7); // Default to Tunisia
let clickCount = 0;
let departMarker, arriveMarker;

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

map.on('click', function (e) {
    const lat = e.latlng.lat.toFixed(6);
    const lng = e.latlng.lng.toFixed(6);
    const coordStr = `${lat}, ${lng}`;

    if (clickCount === 0) {
        if (departMarker) map.removeLayer(departMarker);
        departMarker = L.marker([lat, lng], { title: "Départ" }).addTo(map)
            .bindPopup("Départ sélectionné").openPopup();
        document.getElementById('depart_place').value = coordStr;
        document.getElementById('map-instructions').innerText = "Maintenant, cliquez pour choisir le lieu d’arrivée.";
        clickCount++;
    } else if (clickCount === 1) {
        if (arriveMarker) map.removeLayer(arriveMarker);
        arriveMarker = L.marker([lat, lng], { title: "Arrivée", icon: L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41]
        }) }).addTo(map)
            .bindPopup("Arrivée sélectionnée").openPopup();
        document.getElementById('arrive_place').value = coordStr;
        document.getElementById('map-instructions').innerText = "Les deux lieux sont maintenant sélectionnés.";
        clickCount++;
    }
});
</script>

<?php
$content = ob_get_clean();

$additional_css = '
<style>
    /* Order Header */
    .order-header {
        background-color: var(--secondary-dark);
        padding: 80px 0 60px;
        margin-bottom: 50px;
        text-align: center;
        position: relative;
    }

    .header-description {
        color: var(--text-secondary);
        max-width: 600px;
        margin: 20px auto 0;
        font-size: 16px;
        line-height: 1.6;
    }

    /* Order Summary */
    .order-summary {
        margin-bottom: 50px;
    }

    .summary-card {
        background-color: var(--secondary-dark);
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .summary-card h3 {
        color: var(--text-primary);
        font-size: 20px;
        margin-bottom: 20px;
        font-family: "Playfair Display", serif;
    }

    .summary-items {
        margin-bottom: 20px;
        max-height: 200px;
        overflow-y: auto;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .item-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .item-quantity {
        color: var(--accent-color);
        font-weight: 600;
    }

    .summary-total {
        border-top: 2px solid var(--border-color);
        padding-top: 20px;
    }

    .summary-total > div {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        color: var(--text-secondary);
    }

    .summary-total .total {
        color: var(--text-primary);
        font-size: 18px;
        font-weight: 600;
        margin-top: 10px;
    }

    /* Menu Selection */
    .menu-selection {
        padding: 0 0 50px;
    }

    .section-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
        margin-bottom: 50px;
    }

    .menu-item {
        background-color: var(--secondary-dark);
        border-radius: 10px;
        overflow: hidden;
        transition: transform 0.3s ease;
        position: relative;
    }

    .menu-item:hover {
        transform: translateY(-5px);
    }

    .item-image {
        height: 200px;
        position: relative;
        overflow: hidden;
    }

    .item-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background-color: var(--accent-color);
        color: var(--text-primary);
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 600;
    }

    .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .menu-item:hover .item-image img {
        transform: scale(1.05);
    }

    .placeholder-image {
        width: 100%;
        height: 100%;
        background-color: var(--hover-dark);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .placeholder-image i {
        font-size: 48px;
        color: var(--accent-color);
    }

    .item-details {
        padding: 20px;
    }

    .item-title {
        font-family: "Playfair Display", serif;
        font-size: 20px;
        color: var(--text-primary);
        margin-bottom: 10px;
    }

    .item-description {
        color: var(--text-secondary);
        font-size: 14px;
        margin-bottom: 15px;
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .item-price {
        color: var(--accent-color);
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .item-quantity {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .quantity-input {
        width: 60px;
        text-align: center;
        padding: 8px;
        border: 1px solid var(--border-color);
        background-color: var(--primary-dark);
        color: var(--text-primary);
        border-radius: 5px;
    }

    .quantity-btn {
        width: 36px;
        height: 36px;
        border: none;
        background-color: var(--hover-dark);
        color: var(--text-primary);
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .quantity-btn:hover {
        background-color: var(--accent-color);
    }

    .quantity-btn i {
        font-size: 14px;
    }

    /* Form Styling */
    .order-form {
        padding: 50px 0;
        background-color: var(--secondary-dark);
        border-radius: 10px;
        margin-bottom: 50px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
        max-width: 800px;
        margin: 0 auto;
    }

    .form-group {
        position: relative;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-primary);
        margin-bottom: 8px;
        font-weight: 500;
    }

    .form-label i {
        color: var(--accent-color);
    }

    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid var(--border-color);
        background-color: var(--primary-dark);
        color: var(--text-primary);
        border-radius: 5px;
        transition: border-color 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--accent-color);
        outline: none;
    }

    .form-control::placeholder {
        color: var(--text-secondary);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
    }

    .btn {
        padding: 12px 25px;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .btn-primary {
        background-color: var(--accent-color);
        color: var(--text-primary);
    }

    .btn-primary:hover {
        background-color: var(--accent-hover);
    }

    .btn-outline {
        background-color: transparent;
        border: 1px solid var(--border-color);
        color: var(--text-primary);
    }

    .btn-outline:hover {
        border-color: var(--accent-color);
        color: var(--accent-color);
    }

    .no-items {
        grid-column: 1 / -1;
        text-align: center;
        padding: 40px;
        background-color: var(--secondary-dark);
        border-radius: 10px;
    }

    .no-items i {
        font-size: 48px;
        color: var(--accent-color);
        margin-bottom: 20px;
    }

    .no-items p {
        color: var(--text-primary);
        font-size: 18px;
        margin-bottom: 10px;
    }

    .no-items .sub-text {
        color: var(--text-secondary);
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .menu-grid {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>';

$additional_js = "
<script>
function updateSummary() {
    let subtotal = 0;
    let hasItems = false;
    const summaryItems = document.querySelector('.summary-items');
    summaryItems.innerHTML = '';
    
    document.querySelectorAll('.quantity-input').forEach(input => {
        const quantity = parseInt(input.value) || 0;
        const price = parseFloat(input.dataset.price);
        const name = input.dataset.name;
        const itemTotal = quantity * price;
        
        if (quantity > 0) {
            hasItems = true;
            const itemHtml = 
                '<div class=\"summary-item\">' +
                '<div class=\"item-info\">' +
                '<span class=\"item-quantity\">' + quantity + 'x</span>' +
                '<span class=\"item-name\">' + name + '</span>' +
                '</div>' +
                '<span class=\"item-total\">' + itemTotal.toFixed(2) + ' DT</span>' +
                '</div>';
            summaryItems.insertAdjacentHTML('beforeend', itemHtml);
        }
        
        subtotal += itemTotal;
    });

    const subtotalElement = document.querySelector('.subtotal .amount');
    subtotalElement.textContent = subtotal.toFixed(2) + ' DT';
    
    const deliveryFee = 7.00;
    const total = subtotal + deliveryFee;
    const totalElement = document.querySelector('.total .amount');
    totalElement.textContent = total.toFixed(2) + ' DT';
    
    return hasItems;
}

function validateOrder() {
    // Check if at least one dish is selected
    if (!updateSummary()) {
        alert('Veuillez sélectionner au moins un plat avant de commander.');
        return false;
    }

    // Validate customer information
    const customerName = document.getElementById('customer_name').value.trim();
    const email = document.getElementById('email').value.trim();
    const city = document.getElementById('city').value.trim();
    const deliveryTime = document.getElementById('delivery_time').value;

    if (!customerName) {
        alert('Veuillez entrer votre nom complet.');
        return false;
    }

    if (!email) {
        alert('Veuillez entrer votre email.');
        return false;
    }

    if (!city) {
        alert('Veuillez entrer votre ville.');
        return false;
    }

    if (!deliveryTime) {
        alert('Veuillez sélectionner une date et heure de livraison.');
        return false;
    }

    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Veuillez entrer une adresse email valide.');
        return false;
    }

    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    updateSummary();
    
    // Add form submission handler
    const orderForm = document.querySelector('form');
    orderForm.addEventListener('submit', function(e) {
        if (!validateOrder()) {
            e.preventDefault();
            return false;
        }
    });
    
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const input = this.parentElement.querySelector('.quantity-input');
            const currentValue = parseInt(input.value) || 0;
            
            if (this.classList.contains('minus')) {
                input.value = Math.max(0, currentValue - 1);
            } else {
                input.value = Math.min(10, currentValue + 1);
            }
            
            input.dispatchEvent(new Event('change'));
            updateSummary();
        });
    });

    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            this.value = Math.max(0, Math.min(10, parseInt(this.value) || 0));
            updateSummary();
        });
    });
});
</script>";

require_once "layout.php";
?> 