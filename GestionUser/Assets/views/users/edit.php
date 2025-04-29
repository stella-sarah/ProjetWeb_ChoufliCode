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
    <title>TuniFy Village - Modifier l'Utilisateur</title>
    <link rel="stylesheet" href="assets/css/styleback.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            flex: 1;
        }
        .form-group label {
            display: block;
            color: var(--gold-light);
            margin-bottom: 8px;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            background-color: rgba(17, 17, 17, 0.5);
            border: 1px solid rgba(201, 168, 108, 0.3);
            border-radius: 4px;
            color: var(--light-text);
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gold-light);
        }
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .cancel-btn, .submit-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .cancel-btn {
            background-color: transparent;
            border: 1px solid rgba(201, 168, 108, 0.3);
            color: var(--gold-light);
        }
        .submit-btn {
            background-color: var(--gold-primary);
            color: var(--darker-bg);
        }
        .error-message {
            background-color: rgba(255, 99, 132, 0.1);
            color: #ff6384;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
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
                    <p>ADMINISTRATION</p>
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
                <li>
                    <a href="index.php?controller=user&action=index">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
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
                <h2>Modifier l'Utilisateur</h2>
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
                    <h3>Modifier l'Utilisateur</h3>
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
                    
                    <?php if(isset($_SESSION['errors'])): ?>
                        <div class="error-message">
                            <ul>
                                <?php foreach($_SESSION['errors'] as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['errors']); ?>
                    <?php endif; ?>
                    
                    <div class="form-container">
                        <form action="index.php?controller=user&action=update&id=<?php echo $user['id']; ?>" method="POST" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Nom</label>
                                    <input type="text" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Prénom</label>
                                    <input type="text" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="text" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Mot de passe (laisser vide pour ne pas modifier)</label>
                                    <input type="password" name="mot_de_passe">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Genre</label>
                                    <select name="genre">
                                        <option value="">Sélectionner le genre</option>
                                        <option value="Homme" <?php echo $user['genre'] === 'Homme' ? 'selected' : ''; ?>>Homme</option>
                                        <option value="Femme" <?php echo $user['genre'] === 'Femme' ? 'selected' : ''; ?>>Femme</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Date de naissance</label>
                                    <input type="text" name="date_naissance" value="<?php echo htmlspecialchars($user['date_naissance']); ?>" placeholder="YYYY-MM-DD">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Téléphone</label>
                                    <input type="text" name="telephone" value="<?php echo htmlspecialchars($user['telephone']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                        <label>Rôle</label>
                                        <select name="role">
                                            <option value="">Sélectionner le rôle</option>
                                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Utilisateur</option>
                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                                        </select>
                                    <?php else: ?>
                                        <input type="hidden" name="role" value="<?php echo htmlspecialchars($user['role']); ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Photo de profil</label>
                                <input type="file" name="photo" accept="image/*">
                                <?php if (!empty($user['photo'])): ?>
                                    <p>Photo actuelle : <img src="<?php echo htmlspecialchars($user['photo']); ?>" alt="Photo de profil" style="width: 100px; height: 100px; object-fit: cover;"></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="newsletter" value="1" <?php echo $user['newsletter'] ? 'checked' : ''; ?>>
                                    Newsletter
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="accepte_conditions" value="1" <?php echo $user['accepte_conditions'] ? 'checked' : ''; ?>>
                                    Accepter les conditions
                                </label>
                            </div>
                            
                            <div class="form-actions">
                                <a href="<?php echo $_SESSION['user_role'] === 'admin' ? 'index.php?controller=user&action=index' : 'index.php?controller=user&action=profile'; ?>" class="cancel-btn">Annuler</a>
                                <button type="submit" class="submit-btn">Modifier</button>
                            </div>
                        </form>
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