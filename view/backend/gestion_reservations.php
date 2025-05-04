<?php
session_start();
require_once __DIR__ . '/../../controller/ReservationC.php';

$reservationC = new ReservationC();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'modifier_statut':
                if (isset($_POST['id'], $_POST['status'])) {
                    $reservationC->modifierStatutReservation($_POST['id'], $_POST['status']);
                }
                break;
            case 'supprimer':
                if (isset($_POST['id'])) {
                    $reservationC->supprimerReservation($_POST['id']);
                }
                break;
        }
    }
}

$reservations = $reservationC->afficherReservations();

$page_title = 'Gestion des Réservations';
ob_start();
?>

<!-- Filter Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter"></i> Filtres</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="form-inline">
            <div class="form-group mx-sm-3 mb-2">
                <label for="date" class="mr-2"><i class="fas fa-calendar"></i> Date:</label>
                <input type="date" id="date" name="date" class="form-control">
            </div>
            <div class="form-group mx-sm-3 mb-2">
                <label for="status" class="mr-2"><i class="fas fa-flag"></i> Statut:</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Tous</option>
                    <option value="pending">En attente</option>
                    <option value="confirmed">Confirmée</option>
                    <option value="cancelled">Annulée</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary mb-2">
                <i class="fas fa-search"></i> Filtrer
            </button>
        </form>
    </div>
</div>

<!-- Reservations List -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-calendar-alt"></i> Liste des Réservations</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-user"></i> Client</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-phone"></i> Téléphone</th>
                        <th><i class="fas fa-calendar"></i> Date</th>
                        <th><i class="fas fa-clock"></i> Heure</th>
                        <th><i class="fas fa-users"></i> Personnes</th>
                        <th><i class="fas fa-flag"></i> Statut</th>
                        <th><i class="fas fa-calendar-plus"></i> Créée le</th>
                        <th><i class="fas fa-cogs"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['client_name']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['client_email']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['client_phone']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($reservation['reservation_date'])); ?></td>
                        <td><?php echo date('H:i', strtotime($reservation['reservation_time'])); ?></td>
                        <td><?php echo htmlspecialchars($reservation['guest_count']); ?></td>
                        <td>
                            <form method="POST" class="status-form">
                                <input type="hidden" name="action" value="modifier_statut">
                                <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                                <select name="status" onchange="this.form.submit()" class="form-control form-control-sm">
                                    <option value="pending" <?php echo $reservation['status'] === 'pending' ? 'selected' : ''; ?>>
                                        En attente
                                    </option>
                                    <option value="confirmed" <?php echo $reservation['status'] === 'confirmed' ? 'selected' : ''; ?>>
                                        Confirmée
                                    </option>
                                    <option value="cancelled" <?php echo $reservation['status'] === 'cancelled' ? 'selected' : ''; ?>>
                                        Annulée
                                    </option>
                                </select>
                            </form>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($reservation['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-primary" onclick="deleteReservation(<?php echo $reservation['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function deleteReservation(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
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
    $('#dataTable').DataTable({
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require_once 'layout.php';
?> 