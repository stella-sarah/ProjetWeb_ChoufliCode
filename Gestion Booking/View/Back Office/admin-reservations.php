<?php
// Admin-reservations.php - Back Office Reservations Management
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Gestion des Réservations</title>
    <link rel="stylesheet" href="../../styleback.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Correction pour les modals trop grands */
        .modal-content {
            max-height: 85vh;
            overflow-y: auto;
            padding-right: 15px;
        }
        
        /* Style pour la barre de défilement */
        .modal-content::-webkit-scrollbar {
            width: 8px;
        }
        
        .modal-content::-webkit-scrollbar-track {
            background: rgba(17, 17, 17, 0.5);
            border-radius: 4px;
        }
        
        .modal-content::-webkit-scrollbar-thumb {
            background: var(--gold-primary);
            border-radius: 4px;
        }
        
        .modal-content::-webkit-scrollbar-thumb:hover {
            background: var(--gold-dark);
        }
        
        /* Fixation de la position du bouton fermer */
        .close-modal {
            position: sticky;
            top: 0;
            right: 5px;
            float: right;
            z-index: 10;
            background-color: var(--dark-bg);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
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
                <li>
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
                <li class="active">
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
                            <option>+ Nouveau client</option>
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
                            <option>S+3 AMIRA</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Date de Visite</label>
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
                    <label>Email du Client</label>
                    <input type="email" placeholder="exemple@email.com">
                </div>
                
                <div class="form-group">
                    <label>Téléphone du Client</label>
                    <input type="tel" placeholder="+216 XX XXX XXX">
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

    <!-- Add Villa Modal -->
    <div class="modal" id="addVillaModal">
        <div class="modal-content">
            <span class="close-modal" id="closeVillaModal">&times;</span>
            <h2>Ajouter une Villa</h2>
            
            <form class="transport-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom de la Villa</label>
                        <input type="text" placeholder="Ex: KMAR, YASMINE, etc.">
                    </div>
                    
                    <div class="form-group">
                        <label>Type</label>
                        <select>
                            <option value="" disabled selected>Sélectionner le type</option>
                            <option>S+2</option>
                            <option>S+3</option>
                            <option>S+4</option>
                            <option>S+5</option>
                            <option>S+6</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Surface Totale (m²)</label>
                        <input type="number" placeholder="Ex: 215">
                    </div>
                    
                    <div class="form-group">
                        <label>Sous-sol (m²)</label>
                        <input type="number" placeholder="Ex: 52">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Jardin (m²)</label>
                        <input type="number" placeholder="Ex: 73">
                    </div>
                    
                    <div class="form-group">
                        <label>Nombre de Chambres</label>
                        <input type="number" placeholder="Ex: 3">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre de Salles de Bain</label>
                        <input type="number" placeholder="Ex: 2">
                    </div>
                    
                    <div class="form-group">
                        <label>Places de Parking</label>
                        <input type="number" placeholder="Ex: 2">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Étages</label>
                        <select>
                            <option value="" disabled selected>Sélectionner le nombre d'étages</option>
                            <option>Rez-de-chaussée</option>
                            <option>Rez-de-chaussée + 1</option>
                            <option>Rez-de-chaussée + 2</option>
                            <option>Rez-de-chaussée + 3</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Piscine</label>
                        <select>
                            <option value="" disabled selected>Sélectionner</option>
                            <option>Oui</option>
                            <option>Non</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Prix (DT)</label>
                    <input type="text" placeholder="Ex: 650,000">
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea placeholder="Description détaillée de la villa..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Plan de la Villa</label>
                    <div class="file-upload">
                        <input type="file" id="villaPlan" accept="image/*">
                        <label for="villaPlan">Choisir un fichier</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Images de la Villa</label>
                    <div class="file-upload">
                        <input type="file" id="villaImages" accept="image/*" multiple>
                        <label for="villaImages">Choisir des fichiers</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn" id="cancelAddVilla">Annuler</button>
                    <button type="submit" class="submit-btn">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View/Edit Reservation Modal -->
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
                        <span class="detail-label">Date de Visite:</span>
                        <span class="detail-value">05/04/2025 à 10:00</span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Date de Réservation:</span>
                        <span class="detail-value">01/04/2025</span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Statut:</span>
                        <span class="detail-value"><span class="status confirmed">Confirmée</span></span>
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
                <button class="edit-btn" id="editReservationBtn">
                    <i class="fas fa-edit"></i>
                    Modifier
                </button>
            </div>
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
                <h2>Gestion des Réservations</h2>
            </div>
            
            <div class="nav-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher...">
                </div>
                
                <button class="add-btn" id="openAddReservationModal" style="margin-right: 10px;">
                    <i class="fas fa-plus"></i>
                    Nouvelle Réservation
                </button>
                
                <button class="add-btn" id="openAddVillaModal" style="background-color: var(--gold-dark);">
                    <i class="fas fa-home"></i>
                    Nouvelle Villa
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
            <!-- Filter Options -->
            <div class="card" style="margin-bottom: 30px;">
                <div class="card-body" style="padding: 20px;">
                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <label style="display: block; margin-bottom: 8px; color: var(--gold-light);">Statut</label>
                            <select class="filter-select" style="width: 100%;">
                                <option>Tous les statuts</option>
                                <option>Confirmée</option>
                                <option>En attente</option>
                                <option>Annulée</option>
                            </select>
                        </div>
                        
                        <div style="flex: 1; min-width: 200px;">
                            <label style="display: block; margin-bottom: 8px; color: var(--gold-light);">Villa</label>
                            <select class="filter-select" style="width: 100%;">
                                <option>Toutes les villas</option>
                                <option>S+3 KMAR</option>
                                <option>S+4 YASSER</option>
                                <option>S+3 SAMI</option>
                                <option>S+4 NARJESS</option>
                                <option>S+5 YASMINE</option>
                            </select>
                        </div>
                        
                        <div style="flex: 1; min-width: 200px;">
                            <label style="display: block; margin-bottom: 8px; color: var(--gold-light);">Période</label>
                            <select class="filter-select" style="width: 100%;">
                                <option>Toutes les dates</option>
                                <option>Aujourd'hui</option>
                                <option>Cette semaine</option>
                                <option>Ce mois</option>
                                <option>Ce trimestre</option>
                            </select>
                        </div>
                        
                        <div style="display: flex; align-items: flex-end;">
                            <button style="background-color: var(--gold-primary); color: var(--darker-bg); border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                                <i class="fas fa-filter"></i> Filtrer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reservations Table -->
            <div class="card">
                <div class="card-header">
                    <h3>Liste des Réservations</h3>
                    <div class="card-actions">
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
                                <th>Contact</th>
                                <th>Villa</th>
                                <th>Date de Visite</th>
                                <th>Date de Réservation</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#RES-001</td>
                                <td>Ahmed Benali</td>
                                <td>+216 12 345 678</td>
                                <td>S+3 KMAR</td>
                                <td>05/04/2025 10:00</td>
                                <td>01/04/2025</td>
                                <td><span class="status confirmed">Confirmée</span></td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#RES-002</td>
                                <td>Sonia Mansour</td>
                                <td>+216 23 456 789</td>
                                <td>S+4 YASSER</td>
                                <td>03/04/2025 14:00</td>
                                <td>30/03/2025</td>
                                <td><span class="status active">En attente</span></td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#RES-003</td>
                                <td>Mohamed Kamoun</td>
                                <td>+216 34 567 890</td>
                                <td>S+5 YASMINE</td>
                                <td>01/04/2025 16:00</td>
                                <td>28/03/2025</td>
                                <td><span class="status cancelled">Annulée</span></td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#RES-004</td>
                                <td>Leila Trabelsi</td>
                                <td>+216 45 678 901</td>
                                <td>S+3 SAMI</td>
                                <td>30/03/2025 11:00</td>
                                <td>25/03/2025</td>
                                <td><span class="status confirmed">Confirmée</span></td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#RES-005</td>
                                <td>Karim Mejri</td>
                                <td>+216 56 789 012</td>
                                <td>S+4 NARJESS</td>
                                <td>28/03/2025 15:00</td>
                                <td>23/03/2025</td>
                                <td><span class="status active">En attente</span></td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#RES-006</td>
                                <td>Sarra Bouslama</td>
                                <td>+216 67 890 123</td>
                                <td>S+3 KMAR</td>
                                <td>10/04/2025 09:00</td>
                                <td>05/04/2025</td>
                                <td><span class="status active">En attente</span></td>
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
            
            <!-- Pagination -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px;">
                <div>
                    <span style="color: var(--gold-light);">Affichage de 1-6 sur 24 réservations</span>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button style="background-color: rgba(201, 168, 108, 0.1); color: var(--gold-light); border: 1px solid rgba(201, 168, 108, 0.3); padding: 8px 15px; border-radius: 4px; cursor: pointer; transition: all 0.3s ease;">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    
                    <button style="background-color: rgba(201, 168, 108, 0.2); color: var(--gold-primary); border: 1px solid var(--gold-primary); padding: 8px 15px; border-radius: 4px; cursor: pointer; transition: all 0.3s ease;">
                        1
                    </button>
                    
                    <button style="background-color: rgba(201, 168, 108, 0.1); color: var(--gold-light); border: 1px solid rgba(201, 168, 108, 0.3); padding: 8px 15px; border-radius: 4px; cursor: pointer; transition: all 0.3s ease;">
                        2
                    </button>
                    
                    <button style="background-color: rgba(201, 168, 108, 0.1); color: var(--gold-light); border: 1px solid rgba(201, 168, 108, 0.3); padding: 8px 15px; border-radius: 4px; cursor: pointer; transition: all 0.3s ease;">
                        3
                    </button>
                    
                    <button style="background-color: rgba(201, 168, 108, 0.1); color: var(--gold-light); border: 1px solid rgba(201, 168, 108, 0.3); padding: 8px 15px; border-radius: 4px; cursor: pointer; transition: all 0.3s ease;">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
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
        const addVillaModal = document.getElementById('addVillaModal');
        const viewReservationModal = document.getElementById('viewReservationModal');
        const openAddReservationModal = document.getElementById('openAddReservationModal');
        const openAddVillaModal = document.getElementById('openAddVillaModal');
        const viewBtns = document.querySelectorAll('.action-btn.view');
        const closeModal = document.getElementById('closeModal');
        const closeVillaModal = document.getElementById('closeVillaModal');
        const closeViewModal = document.getElementById('closeViewModal');
        const cancelAddReservation = document.getElementById('cancelAddReservation');
        const cancelAddVilla = document.getElementById('cancelAddVilla');
        
        openAddReservationModal.addEventListener('click', function() {
            addReservationModal.style.display = 'flex';
        });
        
        openAddVillaModal.addEventListener('click', function() {
            addVillaModal.style.display = 'flex';
        });
        
        viewBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                viewReservationModal.style.display = 'flex';
            });
        });
        
        closeModal.addEventListener('click', function() {
            addReservationModal.style.display = 'none';
        });
        
        closeVillaModal.addEventListener('click', function() {
            addVillaModal.style.display = 'none';
        });
        
        closeViewModal.addEventListener('click', function() {
            viewReservationModal.style.display = 'none';
        });
        
        cancelAddReservation.addEventListener('click', function() {
            addReservationModal.style.display = 'none';
        });
        
        cancelAddVilla.addEventListener('click', function() {
            addVillaModal.style.display = 'none';
        });
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === addReservationModal) {
                addReservationModal.style.display = 'none';
            }
            if (event.target === addVillaModal) {
                addVillaModal.style.display = 'none';
            }
            if (event.target === viewReservationModal) {
                viewReservationModal.style.display = 'none';
            }
        });
        
        // Confirm delete
        const deleteBtns = document.querySelectorAll('.action-btn.delete');
        
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')) {
                    // Delete action would happen here
                    alert('Réservation supprimée avec succès.');
                }
            });
        });
        
        // Edit buttons
        const editBtns = document.querySelectorAll('.action-btn.edit');
        const editReservationBtn = document.getElementById('editReservationBtn');
        
        editBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // In a real app, you'd populate the form with reservation data
                addReservationModal.style.display = 'flex';
            });
        });
        
        if (editReservationBtn) {
            editReservationBtn.addEventListener('click', function() {
                viewReservationModal.style.display = 'none';
                setTimeout(() => {
                    addReservationModal.style.display = 'flex';
                }, 300);
            });
        }
        
        // File upload name display
        const villaPlanInput = document.getElementById('villaPlan');
        const villaImagesInput = document.getElementById('villaImages');
        
        if (villaPlanInput) {
            villaPlanInput.addEventListener('change', function() {
                const fileName = this.files[0]?.name || 'Choisir un fichier';
                this.nextElementSibling.textContent = fileName;
            });
        }
        
        if (villaImagesInput) {
            villaImagesInput.addEventListener('change', function() {
                const fileCount = this.files.length;
                this.nextElementSibling.textContent = fileCount > 0 ? `${fileCount} fichier(s) sélectionné(s)` : 'Choisir des fichiers';
            });
        }
        
        // Add animation when opening modals
        function animateModalOpen(modal) {
            modal.style.display = 'flex';
            const modalContent = modal.querySelector('.modal-content');
            
            // Reset any previous transformation
            modalContent.style.transform = 'scale(0.8)';
            modalContent.style.opacity = '0';
            
            // Force reflow
            modalContent.offsetHeight;
            
            // Apply animation
            modalContent.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
            modalContent.style.transform = 'scale(1)';
            modalContent.style.opacity = '1';
        }
        
        // Update the event listeners to use the animation
        openAddReservationModal.addEventListener('click', function() {
            animateModalOpen(addReservationModal);
        });
        
        openAddVillaModal.addEventListener('click', function() {
            animateModalOpen(addVillaModal);
        });
        
        viewBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                animateModalOpen(viewReservationModal);
            });
        });
        
        // Add smooth scroll to modal content
        const modalContents = document.querySelectorAll('.modal-content');
        modalContents.forEach(content => {
            content.addEventListener('scroll', function() {
                if (this.scrollTop > 20) {
                    this.classList.add('scrolled');
                } else {
                    this.classList.remove('scrolled');
                }
            });
        });
    </script>
</body>
</html>
        