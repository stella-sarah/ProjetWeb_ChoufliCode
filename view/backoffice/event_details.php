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

// Check for event ID
if (!isset($_GET['id'])) {
    header("Location: dashboard.php?error=ID de l'événement manquant");
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);

// Fetch event details and reservations
try {
    $sql = "SELECT * FROM events WHERE id = ?";
    $debug_queries[] = $sql;
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        header("Location: dashboard.php?error=Événement non trouvé");
        exit;
    }

    $sql = "SELECT * FROM reservations WHERE event_id = ? ORDER BY created_at DESC";
    $debug_queries[] = $sql;
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header("Location: dashboard.php?error=Erreur lors de la récupération des détails : " . urlencode($e->getMessage()));
    exit;
}

// Handle reservation status update
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['update_reservation'])) {
    $reservation_id = filter_input(INPUT_GET, 'reservation_id', FILTER_VALIDATE_INT);
    $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING);

    if ($reservation_id && in_array($status, ['confirmed', 'canceled'])) {
        try {
            $sql = "UPDATE reservations SET status = ? WHERE id = ?";
            $debug_queries[] = $sql;
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$status, $reservation_id]);
            header("Location: event_details.php?id=" . urlencode($id) . "&success=Réservation " . ($status == 'confirmed' ? 'acceptée' : 'refusée') . " avec succès");
            exit;
        } catch (PDOException $e) {
            header("Location: event_details.php?id=" . urlencode($id) . "&error=Erreur lors de la mise à jour de la réservation : " . urlencode($e->getMessage()));
            exit;
        }
    } else {
        header("Location: event_details.php?id=" . urlencode($id) . "&error=Erreur : Paramètres invalides");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Events - Détails Événement</title>
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
                <li>
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
                <h2>Détails de l'Événement</h2>
            </div>
            
            <div class="nav-right">
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
                                            <a href="event_details.php?id=<?php echo urlencode($id); ?>&update_reservation=1&reservation_id=<?php echo $reservation['id']; ?>&status=confirmed" class="action-btn confirm" title="Accepter"><i class="fas fa-check"></i></a>
                                            <a href="event_details.php?id=<?php echo urlencode($id); ?>&update_reservation=1&reservation_id=<?php echo $reservation['id']; ?>&status=canceled" class="action-btn cancel" title="Refuser"><i class="fas fa-times"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                    <div class="form-actions">
                        <a href="dashboard.php" class="cancel-btn">Retour</a>
                    </div>
                </div>
            </div>
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