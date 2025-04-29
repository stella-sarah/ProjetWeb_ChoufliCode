<?php
// Session start and role check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Veuillez vous connecter.";
    header("Location: index.php?controller=auth&action=showLoginForm");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Mon Profil</title>
    <link rel="stylesheet" href="assets/css/styleback.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        .profile-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--gold-primary);
        }
        .profile-name {
            font-family: 'Cinzel', serif;
            font-size: 24px;
            color: var(--gold-primary);
        }
        .profile-role {
            color: var(--gold-light);
            font-size: 14px;
        }
        .info-group h4 {
            margin-bottom: 10px;
            color: var(--gold-primary);
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid rgba(201, 168, 108, 0.1);
        }
        .info-label {
            color: var(--gold-light);
        }
        .info-value {
            color: var(--light-text);
        }
        .edit-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--gold-primary);
            color: var(--darker-bg);
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        .edit-btn:hover {
            background-color: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
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
                </div>
            </div>
        </div>
        
        <div class="sidebar-nav">
            <ul>
                <!-- Removed unimplemented sections -->
                <!--
                <li>
                    <a href="index.php?controller=admin&action=dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tableau de Bord</span>
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
                -->
                <li class="active">
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
                <h2>Mon Profil</h2>
            </div>
            
            <div class="nav-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher...">
                </div>
                
                <div class="user-profile">
                    <?php if (!empty($_SESSION['user']['photo'])): ?>
                        <img src="<?php echo htmlspecialchars($_SESSION['user']['photo']); ?>" alt="Photo de profil">
                    <?php else: ?>
                        <img src="/api/placeholder/100/100" alt="Photo de profil">
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <div class="card">
                <div class="card-header">
                    <h3>Mon Profil</h3>
                </div>
                <div class="card-body">
                    <?php if(isset($_SESSION['success'])): ?>
                        <div class="alert success">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert error">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="profile-container">
                        <div class="profile-header">
                            <?php if (!empty($user['photo'])): ?>
                                <img src="<?php echo htmlspecialchars($user['photo']); ?>" alt="Photo de profil" class="profile-photo">
                            <?php else: ?>
                                <img src="/api/placeholder/100/100" alt="Photo de profil" class="profile-photo">
                            <?php endif; ?>
                            <div>
                                <div class="profile-name"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></div>
                                <div class="profile-role"><?php echo $user['role'] === 'admin' ? 'Administrateur' : 'Utilisateur'; ?></div>
                            </div>
                        </div>

                        <div class="user-info-grid">
                            <div class="info-group">
                                <h4><i class="fas fa-user"></i> Informations personnelles</h4>
                                <div class="info-item">
                                    <span class="info-label">Nom complet</span>
                                    <span class="info-value"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Email</span>
                                    <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Téléphone</span>
                                    <span class="info-value"><?php echo htmlspecialchars($user['telephone']) ?: 'Non renseigné'; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Genre</span>
                                    <span class="info-value"><?php echo htmlspecialchars($user['genre']) ?: 'Non spécifié'; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Date de naissance</span>
                                    <span class="info-value"><?php echo htmlspecialchars($user['date_naissance']) ?: 'Non spécifiée'; ?></span>
                                </div>
                            </div>
                            <div class="info-group">
                                <h4><i class="fas fa-clock"></i> Informations du compte</h4>
                                <div class="info-item">
                                    <span class="info-label">Date d'inscription</span>
                                    <span class="info-value"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Newsletter</span>
                                    <span class="info-value"><?php echo $user['newsletter'] ? 'Inscrit' : 'Non inscrit'; ?></span>
                                </div>
                            </div>
                        </div>

                        <a href="index.php?controller=user&action=edit&id=<?php echo $user['id']; ?>" class="edit-btn">Modifier le Profil</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

        // Auto-hide Alerts
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);

            // Set active menu item
            const currentPath = window.location.href;
            $('.sidebar-nav a').each(function() {
                if (currentPath.includes($(this).attr('href'))) {
                    $('.sidebar-nav li').removeClass('active');
                    $(this).parent().addClass('active');
                }
            });
        });
    </script>
</body>
</html>