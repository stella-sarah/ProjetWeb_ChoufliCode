<?php
session_start();
require_once __DIR__ . '/../../controller/LivraisonC.php';

$livraisonC = new LivraisonC();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'modifier_statut':
                if (isset($_POST['id'], $_POST['status'])) {
                    $livraisonC->modifierStatutLivraison($_POST['id'], $_POST['status']);
                }
                break;
            case 'supprimer':
                if (isset($_POST['id'])) {
                    $livraisonC->supprimerLivraison($_POST['id']);
                }
                break;
        }
    }
}

$livraisons = $livraisonC->afficherLivraisons();

$page_title = 'Gestion des Livraisons';
ob_start();
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

<!-- Deliveries Card -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-truck"></i> Liste des Livraisons</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table" id="deliveriesTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-user"></i> Client</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-map-marker-alt"></i> Ville</th>
                        <th><i class="fas fa-map-pin"></i> Adresse</th>
                        <th><i class="fas fa-clock"></i> Date de livraison</th>
                        <th><i class="fas fa-flag"></i> Statut</th>
                        <th><i class="fas fa-calendar-plus"></i> Créée le</th>
                        <th><i class="fas fa-cogs"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($livraisons as $livraison): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($livraison['id']); ?></td>
                        <td><?php echo htmlspecialchars($livraison['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($livraison['email']); ?></td>
                        <td><?php echo htmlspecialchars($livraison['city']); ?></td>
                        <td><?php echo htmlspecialchars($livraison['address']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($livraison['delivery_time'])); ?></td>
                        <td>
                            <form method="POST" class="status-form">
                                <input type="hidden" name="action" value="modifier_statut">
                                <input type="hidden" name="id" value="<?php echo $livraison['id']; ?>">
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
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($livraison['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="details_livraison.php?id=<?php echo $livraison['id']; ?>" class="btn btn-outline" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-outline delete-btn" onclick="deleteLivraison(<?php echo $livraison['id']; ?>)" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
</style>';

$additional_js = '
<script>
function deleteLivraison(id) {
    if (confirm("Êtes-vous sûr de vouloir supprimer cette livraison ?")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.innerHTML = `
            <input type="hidden" name="action" value="supprimer">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Initialize DataTables
$(document).ready(function() {
    $("#deliveriesTable").DataTable({
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
});
</script>';

require_once 'layout.php';
?> 