<!DOCTYPE html>
<html lang="fr">
<head>
<style>
.profile-photo { max-width: 100px; height: auto; border-radius: 50%; object-fit: cover; }
</style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Backoffice</title>
    <link rel="stylesheet" href="../../styleback.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <img src="logo.png" alt="TuniFy Logo">
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
                    <a href="transportback.php">
                        <i class="fas fa-car"></i>
                        <span>Transports</span>
                    </a>
                </li>
                <li>
                    <a href="admin-reservations.php">
                        <i class="fas fa-hotel"></i>
                        <span>Hébergements</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-utensils"></i>
                        <span>Restauration</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-comments"></i>
                        <span>Forums</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-cog"></i>
                        <span>Paramètres</span>
                    </a>
                </li>
                <li>
                    <a href="index.php" target="_blank">
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
                <h2>Tableau de Bord</h2>
            </div>
            
            <div class="nav-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher...">
                </div>
                
                <button class="add-btn">
                    <i class="fas fa-plus"></i>
                    Nouveau
                </button>
                
                <div class="user-profile">
                    <img src="/api/placeholder/100/100" alt="Admin Profile">
                    <span>Admin</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Tabs -->
            <div class="tabs">
                <button class="tab-btn active">Aperçu</button>
                <button class="tab-btn">Ventes</button>
                <button class="tab-btn">Visites</button>
                <button class="tab-btn">Rapports</button>
            </div>

            <!-- Stats Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="card" style="margin-bottom: 0;">
                    <div style="padding: 20px; text-align: center;">
                        <div style="font-size: 40px; color: var(--gold-primary); margin-bottom: 10px;">
                            <i class="fas fa-car"></i>
                        </div>
                        <h3 style="font-size: 20px; margin-bottom: 5px;">Transports</h3>
                        <p style="font-size: 28px; font-weight: 600; color: var(--gold-primary);">24</p>
                        <p style="font-size: 14px; color: #4bc0c0; margin-top: 10px;">
                            <i class="fas fa-arrow-up"></i> +2 ce mois
                        </p>
                    </div>
                </div>
                
                <div class="card" style="margin-bottom: 0;">
                    <div style="padding: 20px; text-align: center;">
                        <div style="font-size: 40px; color: var(--gold-primary); margin-bottom: 10px;">
                            <i class="fas fa-hotel"></i>
                        </div>
                        <h3 style="font-size: 20px; margin-bottom: 5px;">Hébergements</h3>
                        <p style="font-size: 28px; font-weight: 600; color: var(--gold-primary);">15</p>
                        <p style="font-size: 14px; color: #4bc0c0; margin-top: 10px;">
                            <i class="fas fa-arrow-up"></i> +3 cette semaine
                        </p>
                    </div>
                </div>
                
                <div class="card" style="margin-bottom: 0;">
                    <div style="padding: 20px; text-align: center;">
                        <div style="font-size: 40px; color: var(--gold-primary); margin-bottom: 10px;">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h3 style="font-size: 20px; margin-bottom: 5px;">Restauration</h3>
                        <p style="font-size: 28px; font-weight: 600; color: var(--gold-primary);">32</p>
                        <p style="font-size: 14px; color: #4bc0c0; margin-top: 10px;">
                            <i class="fas fa-arrow-up"></i> +5 cette semaine
                        </p>
                    </div>
                </div>
                
                <div class="card" style="margin-bottom: 0;">
                    <div style="padding: 20px; text-align: center;">
                        <div style="font-size: 40px; color: var(--gold-primary); margin-bottom: 10px;">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3 style="font-size: 20px; margin-bottom: 5px;">Forums</h3>
                        <p style="font-size: 28px; font-weight: 600; color: var(--gold-primary);">5</p>
                        <p style="font-size: 14px; color: #4bc0c0; margin-top: 10px;">
                            <i class="fas fa-arrow-up"></i> +1 aujourd'hui
                        </p>
                    </div>
                </div>
            </div>

       
            <!-- Recent Reservations -->
            <div class="card">
                <div class="card-header">
                    <h3>Réservations Récentes</h3>
                    <div class="card-actions">
                        <select class="filter-select">
                            <option>Toutes</option>
                            <option>Confirmées</option>
                            <option>En attente</option>
                            <option>Annulées</option>
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
                                <th>Client</th>
                                <th>Service</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#1254</td>
                                <td>John Doe</td>
                                <td>Voiture de luxe</td>
                                <td>15 Juin 2023</td>
                                <td><span class="status confirmed">Confirmée</span></td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#1253</td>
                                <td>Jane Smith</td>
                                <td>Suite Deluxe</td>
                                <td>14 Juin 2023</td>
                                <td><span class="status active">En attente</span></td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#1252</td>
                                <td>Robert Johnson</td>
                                <td>Dîner gastronomique</td>
                                <td>14 Juin 2023</td>
                                <td><span class="status confirmed">Complétée</span></td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#1251</td>
                                <td>Sarah Williams</td>
                                <td>Trottinette électrique</td>
                                <td>13 Juin 2023</td>
                                <td><span class="status cancelled">Annulée</span></td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Reservation Modal -->
    <div class="modal" id="addReservationModal">
        <div class="modal-content">
            <span class="close-modal" id="closeModal">&times;</span>
            <h2>Ajouter une Réservation</h2>
            
            <form class="transport-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Client</label>
                        <select>
                            <option value="" disabled selected>Sélectionner un client</option>
                            <option>John Doe</option>
                            <option>Jane Smith</option>
                            <option>Robert Johnson</option>
                            <option>Sarah Williams</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Service</label>
                        <select>
                            <option value="" disabled selected>Sélectionner un service</option>
                            <option>Voiture de luxe</option>
                            <option>Suite Deluxe</option>
                            <option>Dîner gastronomique</option>
                            <option>Trottinette électrique</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Date de Réservation</label>
                        <input type="date">
                    </div>
                    
                    <div class="form-group">
                        <label>Heure</label>
                        <select>
                            <option value="" disabled selected>Sélectionner l'heure</option>
                            <option>09:00</option>
                            <option>10:00</option>
                            <option>11:00</option>
                            <option>14:00</option>
                            <option>15:00</option>
                            <option>16:00</option>
                            <option>17:00</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Statut</label>
                    <select>
                        <option value="" disabled selected>Sélectionner le statut</option>
                        <option>Confirmée</option>
                        <option>En attente</option>
                        <option>Annulée</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Notes</label>
                    <textarea placeholder="Ajouter des notes ou commentaires"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn" id="cancelAddReservation">Annuler</button>
                    <button type="submit" class="submit-btn">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Sidebar Toggle Function
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
        
        // Modals
        const addReservationModal = document.getElementById('addReservationModal');
        const addBtn = document.querySelector('.add-btn');
        const viewBtns = document.querySelectorAll('.action-btn.view');
        const closeModal = document.getElementById('closeModal');
        const cancelAddReservation = document.getElementById('cancelAddReservation');
        
        if (addBtn) {
            addBtn.addEventListener('click', function() {
                addReservationModal.style.display = 'flex';
            });
        }
        
        if (viewBtns) {
            viewBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Handle view action
                });
            });
        }
        
        if (closeModal) {
            closeModal.addEventListener('click', function() {
                addReservationModal.style.display = 'none';
            });
        }
        
        if (cancelAddReservation) {
            cancelAddReservation.addEventListener('click', function() {
                addReservationModal.style.display = 'none';
            });
        }
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === addReservationModal) {
                addReservationModal.style.display = 'none';
            }
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