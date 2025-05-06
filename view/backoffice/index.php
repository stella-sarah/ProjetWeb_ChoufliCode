<?php
require_once 'config.php';

// Initialize PDO connection
try {
    $pdo = Config::getConnexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Debug: Log queries (remove in production)
$debug_queries = [];

// Fetch all events
$events = [];
try {
    $sql = "SELECT id, title, event_date, venue, max_capacity FROM events ORDER BY event_date DESC";
    $debug_queries[] = $sql;
    $stmt = $pdo->query($sql);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des événements : " . $e->getMessage();
}

// Handle form submission for adding an event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $event_date = filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_STRING);
    $venue = filter_input(INPUT_POST, 'venue', FILTER_SANITIZE_STRING);
    $max_capacity = filter_input(INPUT_POST, 'max_capacity', FILTER_VALIDATE_INT);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $image_url = filter_input(INPUT_POST, 'image_url', FILTER_SANITIZE_URL) ?: '';
    $latitude = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT);
    $longitude = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT);

    if ($title && $event_date && $venue && $max_capacity !== false && $latitude !== false && $longitude !== false) {
        try {
            $id = '#' . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4);
            $sql = "INSERT INTO events (id, title, event_date, venue, max_capacity, description, image_url, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $debug_queries[] = $sql;
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id, $title, $event_date, $venue, $max_capacity, $description, $image_url, $latitude, $longitude]);
            header("Location: dashboard.php?success=Événement ajouté avec succès");
            exit;
        } catch (PDOException $e) {
            header("Location: dashboard.php?error=Erreur lors de l'ajout de l'événement : " . urlencode($e->getMessage()));
            exit;
        }
    } else {
        header("Location: dashboard.php?error=Erreur : Veuillez remplir tous les champs obligatoires correctement");
        exit;
    }
}

// Handle form submission for editing an event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_event'])) {
    $id = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_STRING);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $event_date = filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_STRING);
    $venue = filter_input(INPUT_POST, 'venue', FILTER_SANITIZE_STRING);
    $max_capacity = filter_input(INPUT_POST, 'max_capacity', FILTER_VALIDATE_INT);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $image_url = filter_input(INPUT_POST, 'image_url', FILTER_SANITIZE_URL) ?: '';

    if ($id && $title && $event_date && $venue && $max_capacity !== false) {
        try {
            $sql = "UPDATE events SET title = ?, event_date = ?, venue = ?, max_capacity = ?, description = ?, image_url = ? WHERE id = ?";
            $debug_queries[] = $sql;
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $event_date, $venue, $max_capacity, $description, $image_url, $id]);
            header("Location: dashboard.php?success=Événement modifié avec succès");
            exit;
        } catch (PDOException $e) {
            header("Location: dashboard.php?error=Erreur lors de la modification de l'événement : " . urlencode($e->getMessage()));
            exit;
        }
    } else {
        header("Location: dashboard.php?error=Erreur : Veuillez remplir tous les champs obligatoires correctement");
        exit;
    }
}

// Handle event deletion
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_event'])) {
    $id = filter_input(INPUT_GET, 'delete_event', FILTER_SANITIZE_STRING);
    try {
        $sql = "DELETE FROM events WHERE id = ?";
        $debug_queries[] = $sql;
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        header("Location: dashboard.php?success=Événement supprimé avec succès");
        exit;
    } catch (PDOException $e) {
        header("Location: dashboard.php?error=Erreur lors de la suppression de l'événement : " . urlencode($e->getMessage()));
        exit;
    }
}

// Handle reservation status update
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['update_reservation'])) {
    $reservation_id = filter_input(INPUT_GET, 'reservation_id', FILTER_VALIDATE_INT);
    $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING);
    $event_id = filter_input(INPUT_GET, 'event_id', FILTER_SANITIZE_STRING);

    if ($reservation_id && in_array($status, ['confirmed', 'canceled']) && $event_id) {
        try {
            $sql = "UPDATE reservations SET status = ? WHERE id = ?";
            $debug_queries[] = $sql;
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$status, $reservation_id]);
            header("Location: dashboard.php?event_id=$event_id&success=Réservation " . ($status == 'confirmed' ? 'acceptée' : 'refusée') . " avec succès");
            exit;
        } catch (PDOException $e) {
            header("Location: dashboard.php?event_id=$event_id&error=Erreur lors de la mise à jour de la réservation : " . urlencode($e->getMessage()));
            exit;
        }
    } else {
        header("Location: dashboard.php?event_id=$event_id&error=Erreur : Paramètres invalides");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Events - Backoffice</title>
    <link rel="stylesheet" href="assets/css/styleback.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert.success { background-color: #dff0d8; color: #3c763d; }
        .alert.error { background-color: #f2dede; color: #a94442; }
        .alert.debug { background-color: #d9edf7; color: #31708f; }
        .status.pending { color: orange; font-weight: bold; }
        .status.confirmed { color: green; font-weight: bold; }
        .status.canceled { color: red; font-weight: bold; }
        .action-btn { margin: 0 5px; cursor: pointer; }
        .action-btn.confirm { color: green; }
        .action-btn.cancel { color: red; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <img src="assets/images/logo.png" alt="TuniFy Logo">
                <div class="logo-text">
                    <h1>TuniFy</h1>
                    <p>ADMINISTRATION</p>
                </div>
            </div>
        </div>
        
        <div class="sidebar-nav">
            <ul>
                <li class="active">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tableau de Bord</span>
                    </a>
                </li>
                <li>
                    <a href="../index.php" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Voir le Site</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="nav-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h2>Tableau de Bord - Événements</h2>
            </div>
            
            <div class="nav-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher un événement...">
                </div>
                
                <button class="add-btn" id="addEventBtn">
                    <i class="fas fa-plus"></i>
                    Nouvel Événement
                </button>
                
                <div class="user-profile">
                    <img src="assets/images/admin.jpg" alt="Admin Profile">
                    <span>Admin</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <?php
            if (isset($_GET['success'])) {
                echo '<div class="alert success">' . htmlspecialchars($_GET['success']) . '</div>';
            }
            if (isset($_GET['error'])) {
                echo '<div class="alert error">' . htmlspecialchars($_GET['error']) . '</div>';
            }
            // Debug: Display executed queries (remove in production)
            if (!empty($debug_queries)) {
                echo '<div class="alert debug"><strong>Queries executed:</strong> ' . htmlspecialchars(implode('; ', $debug_queries)) . '</div>';
            }
            ?>
            <!-- Events List -->
            <div class="card">
                <div class="card-header">
                    <h3>Liste des Événements</h3>
                    <div class="card-actions">
                        <select class="filter-select">
                            <option>Tous</option>
                            <option>Futurs</option>
                            <option>Passés</option>
                        </select>
                        <button class="refresh-btn">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Date</th>
                                <th>Lieu</th>
                                <th>Capacité Max</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['id']); ?></td>
                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                <td><?php echo htmlspecialchars(date('d M Y', strtotime($event['event_date']))); ?></td>
                                <td><?php echo htmlspecialchars($event['venue']); ?></td>
                                <td><?php echo htmlspecialchars($event['max_capacity']); ?></td>
                                <td>
                                    <button class="action-btn view" data-id="<?php echo $event['id']; ?>"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit" data-id="<?php echo $event['id']; ?>"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete" data-id="<?php echo $event['id']; ?>"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (isset($_GET['event_id'])): ?>
            <?php
            $event_id = filter_input(INPUT_GET, 'event_id', FILTER_SANITIZE_STRING);
            try {
                $sql = "SELECT * FROM events WHERE id = ?";
                $debug_queries[] = $sql;
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$event_id]);
                $event = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $sql = "SELECT * FROM reservations WHERE event_id = ? ORDER BY created_at DESC";
                $debug_queries[] = $sql;
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$event_id]);
                $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo '<div class="alert error">Erreur lors de la récupération des détails : ' . htmlspecialchars($e->getMessage()) . '</div>';
                $event = null;
                $reservations = [];
            }
            if ($event):
            ?>
            <!-- Event Details -->
            <div class="card">
                <div class="card-header">
                    <h3>Détails de l'Événement : <?php echo htmlspecialchars($event['title']); ?></h3>
                </div>
                <div class="card-body">
                    <p><strong>ID:</strong> <?php echo htmlspecialchars($event['id']); ?></p>
                    <p><strong>Titre:</strong> <?php echo htmlspecialchars($event['title']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars(date('d M Y', strtotime($event['event_date']))); ?></p>
                    <p><strong>Lieu:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>
                    <p><strong>Capacité Max:</strong> <?php echo htmlspecialchars($event['max_capacity']); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($event['description'] ?: 'Aucune'); ?></p>
                    <p><strong>Image URL:</strong> <?php echo htmlspecialchars($event['image_url'] ?: 'Aucune'); ?></p>
                    
                    <h4>Réservations</h4>
                    <?php if (empty($reservations)): ?>
                        <p>Aucune réservation pour cet événement.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Statut</th>
                                    <th>Date de Réservation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservations as $reservation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['name']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['email']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['phone']); ?></td>
                                    <td><span class="status <?php echo strtolower($reservation['status']); ?>"><?php echo htmlspecialchars($reservation['status']); ?></span></td>
                                    <td><?php echo htmlspecialchars(date('d M Y H:i', strtotime($reservation['created_at']))); ?></td>
                                    <td>
                                        <?php if ($reservation['status'] == 'pending'): ?>
                                            <a href="dashboard.php?update_reservation=1&reservation_id=<?php echo $reservation['id']; ?>&status=confirmed&event_id=<?php echo $event_id; ?>" class="action-btn confirm" title="Accepter"><i class="fas fa-check"></i></a>
                                            <a href="dashboard.php?update_reservation=1&reservation_id=<?php echo $reservation['id']; ?>&status=canceled&event_id=<?php echo $event_id; ?>" class="action-btn cancel" title="Refuser"><i class="fas fa-times"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div class="modal" id="addEventModal">
        <div class="modal-content">
            <span class="close-modal" id="closeAddModal">×</span>
            <h2>Ajouter un Événement</h2>
            <form id="addEventForm" method="POST">
                <div class="form-group">
                    <label>Titre</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Date de l'Événement</label>
                    <input type="date" name="event_date" required>
                </div>
                <div class="form-group">
                    <label>Lieu</label>
                    <input type="text" name="venue" required>
                </div>
                <div class="form-group">
                    <label>Capacité Maximum</label>
                    <input type="number" name="max_capacity" min="1" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Description de l'événement"></textarea>
                </div>
                <div class="form-group">
                    <label>URL de l'Image</label>
                    <input type="url" name="image_url" placeholder="https://example.com/image.jpg">
                </div>
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="number" name="latitude" step="any" required>
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="number" name="longitude" step="any" required>
                </div>
                <input type="hidden" name="add_event" value="1">
                <div class="form-actions">
                    <button type="button" class="cancel-btn" id="cancelAddEvent">Annuler</button>
                    <button type="submit" class="submit-btn">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div class="modal" id="editEventModal">
        <div class="modal-content">
            <span class="close-modal" id="closeEditModal">×</span>
            <h2>Modifier un Événement</h2>
            <form id="editEventForm" method="POST">
                <input type="hidden" name="event_id" id="editEventId">
                <div class="form-group">
                    <label>Titre</label>
                    <input type="text" name="title" id="editTitle" required>
                </div>
                <div class="form-group">
                    <label>Date de l'Événement</label>
                    <input type="date" name="event_date" id="editEventDate" required>
                </div>
                <div class="form-group">
                    <label>Lieu</label>
                    <input type="text" name="venue" id="editVenue" required>
                </div>
                <div class="form-group">
                    <label>Capacité Maximum</label>
                    <input type="number" name="max_capacity" id="editMaxCapacity" min="1" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="editDescription" placeholder="Description de l'événement"></textarea>
                </div>
                <div class="form-group">
                    <label>URL de l'Image</label>
                    <input type="url" name="image_url" id="editImageUrl" placeholder="https://example.com/image.jpg">
                </div>
                <input type="hidden" name="edit_event" value="1">
                <div class="form-actions">
                    <button type="button" class="cancel-btn" id="cancelEditEvent">Annuler</button>
                    <button type="submit" class="submit-btn">Modifier</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

        // Modal Handling for Add Event
        const addEventModal = document.getElementById('addEventModal');
        const addEventBtn = document.getElementById('addEventBtn');
        const closeAddModal = document.getElementById('closeAddModal');
        const cancelAddEvent = document.getElementById('cancelAddEvent');
        
        addEventBtn.addEventListener('click', function() {
            addEventModal.style.display = 'flex';
        });
        
        closeAddModal.addEventListener('click', function() {
            addEventModal.style.display = 'none';
        });
        
        cancelAddEvent.addEventListener('click', function() {
            addEventModal.style.display = 'none';
        });

        // Modal Handling for Edit Event
        const editEventModal = document.getElementById('editEventModal');
        const closeEditModal = document.getElementById('closeEditModal');
        const cancelEditEvent = document.getElementById('cancelEditEvent');
        
        closeEditModal.addEventListener('click', function() {
            editEventModal.style.display = 'none';
        });
        
        cancelEditEvent.addEventListener('click', function() {
            editEventModal.style.display = 'none';
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === addEventModal) {
                addEventModal.style.display = 'none';
            }
            if (event.target === editEventModal) {
                editEventModal.style.display = 'none';
            }
        });

        // Handle View, Edit, Delete actions
        document.querySelectorAll('.action-btn.view').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                window.location.href = `dashboard.php?event_id=${id}`;
            });
        });

        document.querySelectorAll('.action-btn.edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                fetch(`get_event.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }
                        document.getElementById('editEventId').value = data.id;
                        document.getElementById('editTitle').value = data.title;
                        document.getElementById('editEventDate').value = data.event_date.split(' ')[0];
                        document.getElementById('editVenue').value = data.venue;
                        document.getElementById('editMaxCapacity').value = data.max_capacity;
                        document.getElementById('editDescription').value = data.description || '';
                        document.getElementById('editImageUrl').value = data.image_url || '';
                        editEventModal.style.display = 'flex';
                    })
                    .catch(error => alert('Erreur lors du chargement des détails : ' + error));
            });
        });

        document.querySelectorAll('.action-btn.delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                if (confirm(`Voulez-vous vraiment supprimer l'événement #${id} ?`)) {
                    window.location.href = `dashboard.php?delete_event=${id}`;
                }
            });
        });

        // Active menu item
        document.querySelectorAll('.sidebar-nav li').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.sidebar-nav li').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>