<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Backoffice Réservations</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styleback.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="TuniFy Logo">
                <div class="logo-text">
                    <h1>TuniFy</h1>
                    <p>ADMINISTRATION</p>
                </div>
            </div>
        </div>
        <div class="sidebar-nav">
            <ul>
                <li class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Tableau de Bord</span></a>
                </li>
                <li class="<?php echo $activePage === 'transports' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>transportback.php"><i class="fas fa-car"></i><span>Transports</span></a>
                </li>
                <li class="<?php echo $activePage === 'hebergements' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>hebergements.php"><i class="fas fa-hotel"></i><span>Hébergements</span></a>
                </li>
                <li class="<?php echo $activePage === 'restauration' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>restauration.php"><i class="fas fa-utensils"></i><span>Restauration</span></a>
                </li>
                <li class="<?php echo $activePage === 'forums' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>forums.php"><i class="fas fa-comments"></i><span>Forums</span></a>
                </li>
                <li class="<?php echo $activePage === 'users' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>users.php"><i class="fas fa-users"></i><span>Utilisateurs</span></a>
                </li>
                <li class="<?php echo $activePage === 'settings' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>settings.php"><i class="fas fa-cog"></i><span>Paramètres</span></a>
                </li>
                <li>
                    <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i><span>Voir le Site</span></a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>logout.php"><i class="fas fa-sign-out-alt"></i><span>Déconnexion</span></a>
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
                <h2>Réservations</h2>
            </div>
            <div class="nav-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher..." id="searchInput" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
                <button class="add-btn" id="addReservationBtn">
                    <i class="fas fa-plus"></i> Nouvelle Réservation
                </button>
                <div class="user-profile">
                    <img src="<?php echo BASE_URL; ?>assets/images/admin.jpg" alt="Admin Profile">
                    <span>Admin</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <?php echo $content; ?>
        </div>
    </div>

    <!-- JavaScript for Sidebar Toggle -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');

        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
    </script>
</body>
</html>