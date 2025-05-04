<?php
session_start();
require_once __DIR__ . '/../../controller/CommandeC.php';

$commandeC = new CommandeC();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'modifier_statut':
                if (isset($_POST['id'], $_POST['status'])) {
                    $commandeC->modifierStatutCommande($_POST['id'], $_POST['status']);
                }
                break;
            case 'supprimer':
                if (isset($_POST['id'])) {
                    $result = $commandeC->supprimerCommande($_POST['id']);
                    $_SESSION['message'] = $result 
                        ? ['type' => 'success', 'text' => 'Commande supprimée avec succès.']
                        : ['type' => 'error', 'text' => 'Erreur lors de la suppression de la commande.'];
                }
                break;
        }
    }
}
$commandeC = new CommandeC();

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'ASC'; // Default sorting
$limit = 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$totalCommandes = $commandeC->countCommandes();
$totalPages = ceil($totalCommandes / $limit);

$commandes = $commandeC->fetchSortedCommandes($sort, $limit, $offset);



$page_title = 'Gestion des Commandes';
ob_start();

// Display message if exists
if (isset($_SESSION['message'])) {
    $messageType = $_SESSION['message']['type'] === 'success' ? 'success' : 'danger';
    echo '<div class="alert alert-' . $messageType . ' alert-dismissible fade show" role="alert">
            ' . $_SESSION['message']['text'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['message']);
}
?>

<!-- Filter Card -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-filter"></i> Filtres</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="form-group">
                <label for="status">
                    <i class="fas fa-flag"></i>
                    Statut
                </label>
                <select name="status" id="status" class="form-control">
                    <option value="">Tous</option>
                    <option value="en attente">En attente</option>
                    <option value="en préparation">En préparation</option>
                    <option value="livré">Livré</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
                Filtrer
            </button>
        </form>
    </div>
</div>

<!-- Orders Card -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-shopping-cart"></i> Liste des Commandes</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table" id="ordersTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-user"></i> Client</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-map-marker-alt"></i> Ville</th>
                        <th><i class="fas fa-clock"></i> Date de livraison</th>
                        <th><i class="fas fa-flag"></i> Statut</th>
                        <th><i class="fas fa-calendar-plus"></i> Créée le</th>
                        <th><i class="fas fa-cogs"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $commande): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($commande['id']); ?></td>
                        <td><?php echo htmlspecialchars($commande['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($commande['email']); ?></td>
                        <td><?php echo htmlspecialchars($commande['city']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($commande['delivery_time'])); ?></td>
                        <td>
                            <form method="POST" class="status-form">
                                <input type="hidden" name="action" value="modifier_statut">
                                <input type="hidden" name="id" value="<?php echo $commande['id']; ?>">
                                <select name="status" onchange="this.form.submit()" class="status-select">
                                    <option value="en attente" <?php echo $commande['status'] === 'en attente' ? 'selected' : ''; ?>>En attente</option>
                                    <option value="en préparation" <?php echo $commande['status'] === 'en préparation' ? 'selected' : ''; ?>>En préparation</option>
                                    <option value="livré" <?php echo $commande['status'] === 'livré' ? 'selected' : ''; ?>>Livré</option>
                                </select>
                            </form>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($commande['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons d-flex flex-wrap gap-1">
                                <a href="details_commande.php?id=<?php echo $commande['id']; ?>" class="btn btn-primary" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ?');">
                                    <input type="hidden" name="action" value="supprimer">
                                    <input type="hidden" name="id" value="<?php echo $commande['id']; ?>">
                                    <button type="submit" class="btn btn-primary" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <a 
                                    class="btn btn-secondary" 
                                    title="Itinéraire sur Google Maps"
                                    target="_blank"
                                    href="https://www.google.com/maps/dir/<?php echo urlencode($commande['depart_place']); ?>/<?php echo urlencode($commande['arrive_place']); ?>"
                                >
                                    <i class="fas fa-route"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1): ?>
    <div class="d-flex justify-content-center mt-4">
        <ul class="pagination">

            <!-- Lien première page -->
            <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=1&sort=<?php echo $sort; ?>">«</a>
            </li>

            <!-- Lien page précédente -->
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>&sort=<?php echo $sort; ?>">Précédent</a>
            </li>

            <!-- Pages numérotées dynamiques -->
            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <!-- Lien page suivante -->
            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>&sort=<?php echo $sort; ?>">Suivant</a>
            </li>

            <!-- Lien dernière page -->
            <li class="page-item <?php echo $page == $totalPages ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $totalPages; ?>&sort=<?php echo $sort; ?>">»</a>
            </li>

        </ul>
    </div>
<?php endif; ?>


    </div>
</div>


<?php
$content = ob_get_clean();

$additional_css = '
<style>
    .filter-form {
        display: flex;
        gap: 20px;
        align-items: flex-end;
    }

    .form-group {
        flex: 1;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--gold-light);
    }

    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 20px;
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

    .status-form {
        margin: 0;
    }

    .status-select {
        background-color: var(--darker-bg);
        border: 1px solid var(--gold-primary);
        color: var(--light-text);
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .status-select:hover {
        background-color: rgba(201, 168, 108, 0.1);
    }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .delete-btn {
        color: var(--error-color);
        border-color: var(--error-color);
    }

    .delete-btn:hover {
        background-color: var(--error-color);
        border-color: var(--error-color);
        color: var(--light-text);
    }

    @media screen and (max-width: 768px) {
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }

        .table {
            display: block;
            overflow-x: auto;
        }
    }

    .alert {
        margin-bottom: 20px;
        padding: 15px;
        border-radius: 5px;
        position: relative;
    }
    
    .alert-success {
        background-color: rgba(40, 167, 69, 0.2);
        border: 1px solid #28a745;
        color: #28a745;
    }
    
    .alert-danger {
        background-color: rgba(220, 53, 69, 0.2);
        border: 1px solid #dc3545;
        color: #dc3545;
    }
    
    .alert-dismissible .btn-close {
        position: absolute;
        top: 0;
        right: 0;
        padding: 1rem;
        color: inherit;
        background: none;
        border: 0;
        cursor: pointer;
    }
</style>';

$additional_js = '
<script>
$(document).ready(function() {
    // Initialize DataTables
    var table = $("#ordersTable").DataTable({
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        dom: "<\'row\'<\'col-sm-12 col-md-6\'l><\'col-sm-12 col-md-6\'f>>" +
             "<\'row\'<\'col-sm-12\'tr>>" +
             "<\'row\'<\'col-sm-12 col-md-5\'i><\'col-sm-12 col-md-7\'p>>",
        pageLength: 10,
        order: [[0, "desc"]]
    });

    // Handle delete form submission
    $(document).on("submit", "form", function(e) {
        if ($(this).find("input[name=action]").val() === "supprimer") {
            e.preventDefault();
            if (confirm("Êtes-vous sûr de vouloir supprimer cette commande ?")) {
                var form = $(this);
                $.ajax({
                    url: window.location.href,
                    type: "POST",
                    data: form.serialize(),
                    success: function(response) {
                        // Remove the row from the table
                        table.row(form.closest("tr")).remove().draw();
                        
                        // Show success message
                        var alert = $(`<div class="alert alert-success alert-dismissible fade show" role="alert">
                            Commande supprimée avec succès.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`);
                        $(".card:first").before(alert);
                        
                        // Auto-hide alert after 3 seconds
                        setTimeout(function() {
                            alert.alert("close");
                        }, 3000);
                    },
                    error: function() {
                        // Show error message
                        var alert = $(`<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Erreur lors de la suppression de la commande.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`);
                        $(".card:first").before(alert);
                    }
                });
            }
        }
    });

    // Auto-hide alerts after 3 seconds
    setTimeout(function() {
        $(".alert").alert("close");
    }, 3000);
});
</script>';

require_once 'layout.php';
?> 
