<?php
// Session start and role check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php?controller=auth&action=login");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Gestion des Utilisateurs</title>
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
                    <p>ADMINISTRATION</p>
                </div>
            </div>
        </div>
        
        <div class="sidebar-nav">
            <ul>
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
                <li class="active">
                    <a href="index.php?controller=user&action=index">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
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
                <h2>Gestion des Utilisateurs</h2>
            </div>
            
            <div class="nav-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher un utilisateur...">
                </div>
                
                <button class="add-btn" id="addBtn">
                    <i class="fas fa-plus"></i> Nouvel Utilisateur
                </button>
                
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
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Tabs -->
            <div class="tabs">
                <button class="tab-btn active">Aperçu</button>
                <button class="tab-btn">Rôles</button>
                <button class="tab-btn">Activités</button>
                <button class="tab-btn">Rapports</button>
            </div>

            <!-- Stats Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="card" style="margin-bottom: 0;">
                    <div style="padding: 20px; text-align: center;">
                        <div style="font-size: 40px; color: var(--gold-primary); margin-bottom: 10px;">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 style="font-size: 20px; margin-bottom: 5px;">Total Utilisateurs</h3>
                        <p style="font-size: 28px; font-weight: 600; color: var(--gold-primary);"><?php echo $totalUsers; ?></p>
                        <p style="font-size: 14px; color: #4bc0c0; margin-top: 10px;">
                            <i class="fas fa-arrow-up"></i> +10 ce mois
                        </p>
                    </div>
                </div>
                
                <div class="card" style="margin-bottom: 0;">
                    <div style="padding: 20px; text-align: center;">
                        <div style="font-size: 40px; color: var(--gold-primary); margin-bottom: 10px;">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h3 style="font-size: 20px; margin-bottom: 5px;">Utilisateurs Actifs</h3>
                        <p style="font-size: 28px; font-weight: 600; color: var(--gold-primary);"><?php echo $activeUsers; ?></p>
                        <p style="font-size: 14px; color: #4bc0c0; margin-top: 10px;">
                            <i class="fas fa-arrow-up"></i> +5 cette semaine
                        </p>
                    </div>
                </div>
                
                <div class="card" style="margin-bottom: 0;">
                    <div style="padding: 20px; text-align: center;">
                        <div style="font-size: 40px; color: var(--gold-primary); margin-bottom: 10px;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h3 style="font-size: 20px; margin-bottom: 5px;">Administrateurs</h3>
                        <p style="font-size: 28px; font-weight: 600; color: var(--gold-primary);"><?php echo $adminUsers; ?></p>
                        <p style="font-size: 14px; color: #4bc0c0; margin-top: 10px;">
                            <i class="fas fa-arrow-up"></i> +1 ce mois
                        </p>
                    </div>
                </div>
                
                <div class="card" style="margin-bottom: 0;">
                    <div style="padding: 20px; text-align: center;">
                        <div style="font-size: 40px; color: var(--gold-primary); margin-bottom: 10px;">
                            <i class="fas fa-user-times"></i>
                        </div>
                        <h3 style="font-size: 20px; margin-bottom: 5px;">Utilisateurs Inactifs</h3>
                        <p style="font-size: 28px; font-weight: 600; color: var(--gold-primary);"><?php echo $inactiveUsers; ?></p>
                        <p style="font-size: 14px; color: #4bc0c0; margin-top: 10px;">
                            <i class="fas fa-arrow-down"></i> -2 cette semaine
                        </p>
                    </div>
                </div>
            </div>

            <!-- User Grid -->
            <div class="card">
                <div class="card-header">
                    <h3>Gestion des Utilisateurs</h3>
                    <div class="card-actions">
                        <select id="roleFilter" onchange="filterUsers()">
                            <option value="all">Tous les utilisateurs</option>
                            <option value="admin">Administrateurs</option>
                            <option value="user">Utilisateurs</option>
                        </select>
                        <button class="refresh-btn">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
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
                        <div class="alert error">
                            <ul>
                                <?php foreach($_SESSION['errors'] as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['errors']); ?>
                    <?php endif; ?>
                    
                    <div class="users-grid">
                        <?php foreach ($users as $user): ?>
                            <div class="user-card" data-role="<?php echo htmlspecialchars($user['role']); ?>">
                                <div class="user-card-header">
                                    <div class="user-avatar <?php echo empty($user['photo']) ? 'no-image' : ''; ?>">
                                        <?php if (!empty($user['photo'])): ?>
                                            <img src="<?php echo htmlspecialchars($user['photo']); ?>" alt="Photo de profil">
                                        <?php else: ?>
                                            <i class="fas fa-user"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="user-info">
                                        <h4><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h4>
                                        <span class="role-badge <?php echo htmlspecialchars($user['role']); ?>">
                                            <?php echo $user['role'] === 'admin' ? 'Administrateur' : 'Utilisateur'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="user-card-body">
                                    <div class="user-details">
                                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                                        <p><i class="fas fa-calendar"></i> Inscrit le <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                                    </div>
                                    <div class="user-actions">
                                        <a href="index.php?controller=user&action=edit&id=<?php echo $user['id']; ?>" class="action-btn edit" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="action-btn view" title="Voir" onclick="viewUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="index.php?controller=user&action=delete&id=<?php echo $user['id']; ?>" 
                                           class="action-btn delete" 
                                           title="Supprimer" 
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- User View Modal -->
            <div class="modal" id="userViewModal">
                <div class="modal-content">
                    <span class="close-modal" onclick="closeUserModal()">×</span>
                    <h2>Détails de l'Utilisateur</h2>
                    <div class="user-view-body">
                        <div class="user-info-grid">
                            <div class="info-group">
                                <h4><i class="fas fa-user"></i> Informations personnelles</h4>
                                <div class="info-item">
                                    <span class="info-label">Nom complet</span>
                                    <span id="modalFullName" class="info-value"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Email</span>
                                    <span id="modalEmail" class="info-value"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Téléphone</span>
                                    <span id="modalPhone" class="info-value"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Genre</span>
                                    <span id="modalGender" class="info-value"></span>
                                </div>
                            </div>
                            <div class="info-group">
                                <h4><i class="fas fa-clock"></i> Informations du compte</h4>
                                <div class="info-item">
                                    <span class="info-label">Date d'inscription</span>
                                    <span id="modalCreatedAt" class="info-value"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Newsletter</span>
                                    <span id="modalNewsletter" class="info-value"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add User Modal -->
            <div class="modal" id="addUserModal">
                <div class="modal-content">
                    <span class="close-modal" id="closeAddModal">×</span>
                    <h2>Ajouter un Utilisateur</h2>
                    
                    <form action="index.php?controller=user&action=store" method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nom</label>
                                <input type="text" name="nom">
                            </div>
                            
                            <div class="form-group">
                                <label>Prénom</label>
                                <input type="text" name="prenom">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" name="email">
                            </div>
                            
                            <div class="form-group">
                                <label>Mot de passe</label>
                                <input type="password" name="mot_de_passe">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Genre</label>
                                <select name="genre">
                                    <option value="">Sélectionner le genre</option>
                                    <option value="Homme">Homme</option>
                                    <option value="Femme">Femme</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Date de naissance</label>
                                <input type="text" name="date_naissance" placeholder="YYYY-MM-DD">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Téléphone</label>
                                <input type="text" name="telephone">
                            </div>
                            
                            <div class="form-group">
                                <label>Rôle</label>
                                <select name="role">
                                    <option value="">Sélectionner le rôle</option>
                                    <option value="user">Utilisateur</option>
                                    <option value="admin">Administrateur</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Photo de profil</label>
                            <input type="file" name="photo" accept="image/*">
                        </div>
                        
                        <div class="form-group">
                            <label><input type="checkbox" name="newsletter" value="1"> Newsletter</label>
                        </div>
                        
                        <div class="form-group">
                            <label><input type="checkbox" name="accepte_conditions" value="1"> Accepter les conditions</label>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="cancel-btn" id="cancelAddUser">Annuler</button>
                            <button type="submit" class="submit-btn">Ajouter</button>
                        </div>
                    </form>
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

        // Modal Handling
        const addUserModal = document.getElementById('addUserModal');
        const addBtn = document.getElementById('addBtn');
        const closeAddModal = document.getElementById('closeAddModal');
        const cancelAddUser = document.getElementById('cancelAddUser');

        addBtn.addEventListener('click', function() {
            addUserModal.style.display = 'flex';
        });

        closeAddModal.addEventListener('click', function() {
            addUserModal.style.display = 'none';
        });

        cancelAddUser.addEventListener('click', function() {
            addUserModal.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target === addUserModal) {
                addUserModal.style.display = 'none';
            }
            if (event.target === document.getElementById('userViewModal')) {
                closeUserModal();
            }
        });

        // Filter Users
        function filterUsers() {
            const selectedRole = document.getElementById('roleFilter').value;
            const userCards = document.querySelectorAll('.user-card');
            
            userCards.forEach(card => {
                if (selectedRole === 'all' || card.dataset.role === selectedRole) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // View User Modal
        function viewUser(user) {
            document.getElementById('modalFullName').textContent = `${user.prenom} ${user.nom}`;
            document.getElementById('modalEmail').textContent = user.email;
            document.getElementById('modalPhone').textContent = user.telephone || 'Non renseigné';
            document.getElementById('modalGender').textContent = user.genre || 'Non spécifié';
            document.getElementById('modalCreatedAt').textContent = new Date(user.created_at).toLocaleDateString('fr-FR', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
            document.getElementById('modalNewsletter').textContent = user.newsletter ? 'Inscrit' : 'Non inscrit';
            
            document.getElementById('userViewModal').style.display = 'flex';
        }

        function closeUserModal() {
            document.getElementById('userViewModal').style.display = 'none';
        }

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