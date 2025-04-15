<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    error_log("Session started in header.php - Session ID: " . session_id());
    error_log("Current session state: " . print_r($_SESSION, true));
}

// Debug: Afficher les informations de session
error_log("Session user_role: " . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'non défini'));
error_log("Session data: " . print_r($_SESSION, true));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy - Administration</title>
    <link rel="stylesheet" href="assets/css/styleback.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <img src="assets/images/logo.png" alt="TuniFy Logo">
                <div class="logo-text">
                    <h1>TuniFy</h1>
                    
                </div>
            </div>
        </div>
        
        <div class="sidebar-nav">
            <ul>
                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <li>
                        <a href="index.php?controller=admin&action=dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Tableau de Bord</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?controller=user&action=index">
                            <i class="fas fa-users"></i>
                            <span>Utilisateurs</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?controller=transport&action=index">
                            <i class="fas fa-car"></i>
                            <span>Transports</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?controller=hebergement&action=index">
                            <i class="fas fa-hotel"></i>
                            <span>Hébergements</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?controller=restaurant&action=index">
                            <i class="fas fa-utensils"></i>
                            <span>Restauration</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?controller=forum&action=index">
                            <i class="fas fa-comments"></i>
                            <span>Forums</span>
                        </a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="index.php?controller=user&action=profile">
                        <i class="fas fa-user"></i>
                        <span>Mon Profil</span>
                    </a>
                </li>
                <li>
                    <a href="index.php" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Voir le Site</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?controller=auth&action=logout">
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
              
            </div>
            
            <div class="nav-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher...">
                </div>
                
                <?php if(isset($_SESSION['user_nom']) && isset($_SESSION['user_prenom'])): ?>
                    <div class="user-profile">
                        <div class="user-avatar <?php echo empty($_SESSION['user']['photo']) ? 'no-image' : ''; ?>">
                            <?php if (!empty($_SESSION['user']['photo'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['user']['photo']); ?>" alt="Photo de profil">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <span><?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Content Area with proper centering -->
        <div class="content-area">
            <div class="content-wrapper">
                <!-- Le contenu de la page sera inséré ici -->
            </div>
        </div>
    </div>
</body>
</html> 