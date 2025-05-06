<?php
require_once 'config.php';

// Initialize PDO connection
try {
    $pdo = Config::getConnexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Check for event ID
if (!isset($_GET['id'])) {
    header("Location: dashboard.php?error=ID de l'événement manquant");
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);

// Fetch event details
try {
    $sql = "SELECT title FROM events WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) {
        header("Location: dashboard.php?error=Événement non trouvé");
        exit;
    }
} catch (PDOException $e) {
    header("Location: dashboard.php?error=Erreur lors de la récupération de l'événement : " . urlencode($e->getMessage()));
    exit;
}

// Handle form submission for deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Delete associated reservations to avoid foreign key constraint
        $sql = "DELETE FROM reservations WHERE event_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        // Delete the event
        $sql = "DELETE FROM events WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        header("Location: dashboard.php?success=Événement supprimé avec succès");
        exit;
    } catch (PDOException $e) {
        header("Location: dashboard.php?error=Erreur lors de la suppression de l'événement : " . urlencode($e->getMessage()));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Events - Supprimer Événement</title>
    <link rel="stylesheet" href="assets/css/styleback.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert.error { background-color: #f2dede; color: #a94442; }
        .form-actions { text-align: right; }
        .form-actions button, .form-actions a { padding: 10px 20px; margin-left: 10px; }
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
                <h2>Supprimer Événement</h2>
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
            <div class="card">
                <div class="card-header">
                    <h3>Confirmer la Suppression de l'Événement #<?php echo htmlspecialchars($id); ?></h3>
                </div>
                <div class="card-body">
                    <p>Voulez-vous vraiment supprimer l'événement "<?php echo htmlspecialchars($event['title']); ?>" ? Cette action supprimera également toutes les réservations associées et ne peut pas être annulée.</p>
                    <form method="POST">
                        <input type="hidden" name="confirm_delete" value="1">
                        <div class="form-actions">
                            <a href="dashboard.php" class="cancel-btn">Annuler</a>
                            <button type="submit" class="submit-btn">Supprimer</button>
                        </div>
                    </form>
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