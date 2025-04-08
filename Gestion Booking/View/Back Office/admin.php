<?php
// Admin.php - Back Office Dashboard
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Administration</title>
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
                    <a href="admin.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tableau de Bord</span>
                    </a>
                </li>
                <li>
                    <a href="admin-villas.php">
                        <i class="fas fa-home"></i>
                        <span>Gestion des Villas</span>
                    </a>
                </li>
                <li>
                    <a href="admin-reservations.php">
                        <i class="fas fa-calendar-check"></i>
                        <span>Réservations</span>
                    </a>
                </li>
                <li>
                    <a href="admin-clients.php">
                        <i class="fas fa-users"></i>
                        <span>Clients</span>
                    </a>
                </li>
                <li>
                    <a href="admin-transports.php">
                        <i class="fas fa-car"></i>
                        <span>Transports</span>
                    </a>
                </li>
                <li>
                    <a href="admin-settings.php">
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
                            <i class="fas fa-home"></i>
                        </div>
                        <h3 style="font-size: 20px; margin-bottom: 5px;">Total Villas</h3>
                        <p style="font-size: 28px; font-weight: 600; color: var(--gold-primary);">24</p>
                        <p style="font-size: 14px; color: #4bc0c0; margin-top: 10px;">
                            <i class="fas fa-arrow-up"></i> +2 ce mois
                        </p>
                    </div>
                </div>
                
                <div class="card" style="margin-bottom: 0;">
                    <div style="padding: 20px; text-align: center;">
                        <div style="font-size: 40px; color: var(--gold-primary); margin-bottom: 10px;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 style="font-size: 20px; margin-bottom: 5px;">Réservations</h3>
                        <p style="font-size: 28px; font-weight: 600; color: var(--gold-primary);">18</p>
                        <p style="font-size: 14px; color: #4bc0c0; margin-top: 10px;">
                            <i class="fas fa-arrow-up"></i> +5 cette semaine
                        </p>
                    </div>
                </div>
                
                <div class="card" style="margin-bottom: 0;">
                    <div style="padding: 20px; text-align: center;">
                        <div style="font-size: 40px; color: var(--gold-primary); margin-bottom: 10px;">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 style="font-size: 20px; margin-bottom: 5px;">Clients</h3>
                        <p style="font-size: 28px; font-weight: 600; color: var(--gold-primary);">42</p>
                        <p style="font-size: 14px; color: #4bc0c0; margin-top: 10px;">
                            <i class="fas fa-arrow-up"></i> +8 ce mois
                        </p>
                    </div>
                </div>
                
                <div class="card" style="margin-bottom: 0;">
                    <div style="padding: 20px; text-align: center;">
                        <div style="font-size: 40px; color: var(--gold-primary); margin-bottom: 10px;">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h3 style="font-size: 20px; margin-bottom: 5px;">Revenus</h3>
                        <p style="font-size: 28px; font-weight: 600; color: var(--gold-primary);">2.4M DT</p>
                        <p style="font-size: 14px; color: #4bc0c0; margin-top: 10px;">
                            <i class="fas fa-arrow-up"></i> +12% ce trimestre
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stats Chart -->
            <div class="card">
                <div class="card-header">
                    <h3>Statistiques de Vente</h3>
                    <div class="card-actions">
                        <select class="period-select">
                            <option>Cette Année</option>
                            <option>Ce Trimestre</option>
                            <option>Ce Mois</option>
                            <option>Cette Semaine</option>
                        </select>
                        <button class="refresh-btn">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="stats-chart">
                        <div class="chart-placeholder">
                            <i class="fas fa-chart-line" style="font-size: 48px; margin-bottom: 15px;"></i>
                            <p>Le graphique des ventes apparaîtra ici.</p>
                        </div>
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
                                <th>Villa</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Prix</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#RES-001</td>
                                <td>Ahmed Benali</td>
                                <td>S+3 KMAR</td>
                                <td>05/04/2025</td>
                                <td><span class="status confirmed">Confirmée</span></td>
                                <td>650,000 DT</td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#RES-002</td>
                                <td>Sonia Mansour</td>
                                <td>S+4 YASSER</td>
                                <td>03/04/2025</td>
                                <td><span class="status active">En attente</span></td>
                                <td>850,000 DT</td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#RES-003</td>
                                <td>Mohamed Kamoun</td>
                                <td>S+5 YASMINE</td>
                                <td>01/04/2025</td>
                                <td><span class="status cancelled">Annulée</span></td>
                                <td>1,050,000 DT</td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#RES-004</td>
                                <td>Leila Trabelsi</td>
                                <td>S+3 SAMI</td>
                                <td>30/03/2025</td>
                                <td><span class="status confirmed">Confirmée</span></td>
                                <td>670,000 DT</td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#RES-005</td>
                                <td>Karim Mejri</td>
                                <td>S+4 NARJESS</td>
                                <td>28/03/2025</td>
                                <td><span class="status active">En attente</span></td>
                                <td>870,000 DT</td>
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
                            <option>Ahmed Benali</option>
                            <option>Sonia Mansour</option>
                            <option>Mohamed Kamoun</option>
                            <option>Leila Trabelsi</option>
                            <option>Karim Mejri</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Villa</label>
                        <select>
                            <option value="" disabled selected>Sélectionner une villa</option>
                            <option>S+3 KMAR</option>
                            <option>S+4 YASSER</option>
                            <option>S+3 SAMI</option>
                            <option>S+4 NARJESS</option>
                            <option>S+5 YASMINE</option>
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

    <!-- View Reservation Modal -->
    <div class="modal" id="viewReservationModal">
        <div class="modal-content">
            <span class="close-modal" id="closeViewModal">&times;</span>
            <h2>Détails de la Réservation</h2>
            
            <div class="detail-row">
                <div class="detail-image">
                    <img src="/api/placeholder/600/350" alt="Villa Image">
                </div>
                
                <div class="detail-info">
                    <div class="detail-item">
                        <span class="detail-label">ID Réservation:</span>
                        <span class="detail-value">#RES-001</span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Client:</span>
                        <span class="detail-value">Ahmed Benali</span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Contact:</span>
                        <span class="detail-value">+216 12 345 678</span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">ahmed.benali@email.com</span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Villa:</span>
                        <span class="detail-value">S+3 KMAR</span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Date:</span>
                        <span class="detail-value">05/04/2025 à 10:00</span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Statut:</span>
                        <span class="detail-value"><span class="status confirmed">Confirmée</span></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Prix:</span>
                        <span class="detail-value" style="color: var(--gold-primary); font-weight: 600;">650,000 DT</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-item full-width" style="margin-top: 20px;">
                <span class="detail-label">Notes:</span>
                <p class="detail-description">
                    Client très intéressé par cette villa. A demandé des informations supplémentaires sur les options de financement. Prévoir documentation complète pour la visite.
                </p>
            </div>
            
            <div class="detail-actions">
                <button class="edit-btn">
                    <i class="fas fa-edit"></i>
                    Modifier
                </button>
            </div>
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
        const viewReservationModal = document.getElementById('viewReservationModal');
        const addBtn = document.querySelector('.add-btn');
        const viewBtns = document.querySelectorAll('.action-btn.view');
        const closeModal = document.getElementById('closeModal');
        const closeViewModal = document.getElementById('closeViewModal');
        const cancelAddReservation = document.getElementById('cancelAddReservation');
        
        addBtn.addEventListener('click', function() {
            addReservationModal.style.display = 'flex';
        });
        
        viewBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                viewReservationModal.style.display = 'flex';
            });
        });
        
        closeModal.addEventListener('click', function() {
            addReservationModal.style.display = 'none';
        });
        
        closeViewModal.addEventListener('click', function() {
            viewReservationModal.style.display = 'none';
        });
        
        cancelAddReservation.addEventListener('click', function() {
            addReservationModal.style.display = 'none';
        });
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === addReservationModal) {
                addReservationModal.style.display = 'none';
            }
            if (event.target === viewReservationModal) {
                viewReservationModal.style.display = 'none';
            }
        });
    </script>
</body>
</html>