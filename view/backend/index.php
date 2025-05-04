<?php
session_start();
require_once __DIR__ . '/../../config';
require_once __DIR__ . '/../../controller/ReservationC.php';
require_once __DIR__ . '/../../controller/CommandeC.php';
require_once __DIR__ . '/../../controller/LivraisonC.php';
require_once __DIR__ . '/../../controller/PlatC.php';

// Initialize controllers
$reservationC = new ReservationC();
$commandeC = new CommandeC();
$livraisonC = new LivraisonC();
$platC = new PlatC();

// Get data with calculated totals
$commandes = $commandeC->getOrdersWithTotals();
$reservations = $reservationC->afficherReservations();
$livraisons = $livraisonC->afficherLivraisons();
$plats = $platC->afficherPlats();

$page_title = 'Tableau de bord';
ob_start();
?>



<!-- Recent Activity -->
<div class="activity-grid">
    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-clock-rotate-left"></i> Dernières commandes</h3>
            <a href="gestion_commandes.php" class="btn btn-outline">Voir tout</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table" id="ordersTable">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Créé le</th>
                            <th>Livraison</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($commandes, 0, 5) as $commande): ?>
                        <tr>
                            <td>
                                <a href="details_commande.php?id=<?php echo $commande['id']; ?>" class="order-link">
                                    <?php echo htmlspecialchars($commande['customer_name']); ?>
                                </a>
                            </td>
                            <td><?php 
                                if ($commande['created_at']) {
                                    $created = new DateTime($commande['created_at']);
                                    echo $created->format('d/m/Y H:i');
                                } else {
                                    echo 'N/A';
                                }
                            ?></td>
                            <td><?php 
                                if ($commande['delivery_time']) {
                                    $delivery = new DateTime($commande['delivery_time']);
                                    echo $delivery->format('d/m/Y H:i');
                                } else {
                                    echo 'N/A';
                                }
                            ?></td>
                            <td><?php 
                                $total = isset($commande['total']) ? $commande['total'] : 0;
                                echo number_format($total, 2); 
                            ?> DT</td>
                            <td>
                                <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $commande['status'])); ?>">
                                    <?php echo htmlspecialchars($commande['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Reservations -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-clock-rotate-left"></i> Dernières réservations</h3>
            <a href="gestion_reservations.php" class="btn btn-outline">Voir tout</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table" id="reservationsTable">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Personnes</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($reservations, 0, 5) as $reservation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reservation['client_name']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($reservation['reservation_date'])); ?></td>
                            <td><?php echo htmlspecialchars($reservation['guest_count']); ?></td>
                            <td>
                                <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $reservation['status'])); ?>">
                                    <?php echo htmlspecialchars($reservation['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$additional_css = '
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">

<style>
    :root {
        --font-family: "Poppins", sans-serif;
    }

    body {
        font-family: var(--font-family);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        display: flex;
        align-items: center;
        padding: 25px;
        position: relative;
        overflow: hidden;
        background-color: var(--darker-bg);
        border: 1px solid rgba(201, 168, 108, 0.1);
    }

    .stat-icon {
        font-size: 24px;
        color: var(--gold-primary);
        background-color: rgba(201, 168, 108, 0.1);
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        transition: all var(--transition-speed) ease;
    }

    .stat-content {
        flex: 1;
    }

    .stat-content h3 {
        font-size: 16px;
        color: var(--gold-light);
        margin: 0 0 5px 0;
        font-weight: 500;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 600;
        color: var(--light-text);
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 14px;
        color: var(--gold-light);
        opacity: 0.7;
    }

    .stat-link {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gold-primary);
        opacity: 0;
        transition: all var(--transition-speed) ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.1);
    }

    .stat-card:hover .stat-link {
        opacity: 1;
        right: 25px;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .quick-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 25px;
        background-color: var(--darker-bg);
        border-radius: 10px;
        color: var(--light-text);
        text-decoration: none;
        transition: all var(--transition-speed) ease;
        border: 1px solid rgba(201, 168, 108, 0.1);
    }

    .quick-action-btn i {
        font-size: 24px;
        color: var(--gold-primary);
        margin-bottom: 15px;
    }

    .quick-action-btn span {
        font-size: 14px;
        text-align: center;
        color: var(--light-text);
    }

    .quick-action-btn:hover {
        transform: translateY(-5px);
        background-color: rgba(201, 168, 108, 0.1);
        color: var(--gold-primary);
    }

    .activity-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 30px;
        margin-top: 30px;
    }

    .card {
        background-color: var(--darker-bg);
        border: 1px solid rgba(201, 168, 108, 0.1);
    }

    .card-header {
        padding: 20px;
        border-bottom: 1px solid rgba(201, 168, 108, 0.1);
    }

    .card-header h3 {
        color: var(--gold-light);
        margin: 0;
        font-size: 18px;
        font-weight: 500;
    }

    .card-body {
        padding: 20px;
    }

    .data-table {
        width: 100%;
        color: var(--light-text);
    }

    .data-table th {
        color: var(--gold-light);
        font-weight: 500;
        border-bottom: 2px solid var(--gold-primary);
    }

    .data-table td {
        padding: 12px;
        border-bottom: 1px solid rgba(201, 168, 108, 0.1);
    }

    .status-badge {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 500;
        text-transform: capitalize;
    }

    .status-badge.en-attente {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }

    .status-badge.en-preparation {
        background-color: rgba(23, 162, 184, 0.1);
        color: #17a2b8;
    }

    .status-badge.livre {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .btn-outline {
        color: var(--gold-primary);
        border: 1px solid var(--gold-primary);
        padding: 8px 16px;
        border-radius: 5px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-outline:hover {
        background-color: var(--gold-primary);
        color: var(--dark-bg);
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        .activity-grid {
            grid-template-columns: 1fr;
        }

        .quick-actions {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
    }
</style>';

$additional_js = '
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTables with common options
    const commonOptions = {
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
        },
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
        order: [[1, "desc"]],
        dom: "<\'row\'<\'col-sm-12\'tr>>"
    };

    // Initialize Orders table
    $("#ordersTable").DataTable({
        ...commonOptions,
        columnDefs: [
            { targets: -1, orderable: false }
        ]
    });

    // Initialize Reservations table
    $("#reservationsTable").DataTable({
        ...commonOptions,
        columnDefs: [
            { targets: -1, orderable: false }
        ]
    });
});
</script>';

require_once 'layout.php';
?> 