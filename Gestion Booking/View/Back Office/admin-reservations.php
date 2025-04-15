<?php
// admin-reservations.php - Gestion des réservations de visites
require_once 'C:/xampp/htdocs/Gestion Booking/config.php';
require_once 'C:/xampp/htdocs/Gestion Booking/Controllor/Visite.php';

// Initialiser la session
session_start();

// Vérifier si l'utilisateur est connecté en tant qu'admin
// Commenter cette section si vous n'avez pas encore de système d'authentification
/*
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Rediriger vers la page de connexion
    header('Location: login.php');
    exit;
}
*/

// Initialiser la connexion à la base de données
$db = config::getConnexion();

// Initialiser l'objet Visite
$visite = new Visite($db);

// Variables pour les messages
$message = '';
$error = '';

// Traitement de la suppression d'une visite
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id_visite = $_GET['id'];
    
    if ($visite->supprimerVisite($id_visite)) {
        $message = "La réservation de visite a été supprimée avec succès.";
    } else {
        $error = "Une erreur s'est produite lors de la suppression de la réservation.";
    }
}

// Traitement de la modification d'une visite
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier') {
    // Récupérer les données du formulaire
    $id_visite = $_POST['id_visite'];
    $id_cin = htmlspecialchars(strip_tags($_POST['id_cin']));
    $nom_complet = htmlspecialchars(strip_tags($_POST['nom_complet']));
    $date_visite = htmlspecialchars(strip_tags($_POST['date_visite']));
    $heure_visite = htmlspecialchars(strip_tags($_POST['heure_visite']));
    
    // Validation des données
    if (empty($id_cin) || empty($nom_complet) || empty($date_visite) || empty($heure_visite)) {
        $error = "Tous les champs sont obligatoires";
    } else {
        // Définir les valeurs de l'objet visite
        $visite->setIdVisite($id_visite);
        $visite->setCin($id_cin);
        $visite->setNomComplet($nom_complet);
        $visite->setDateVisite($date_visite);
        $visite->setHeureVisite($heure_visite);
        
        // Mettre à jour la visite dans la base de données
        if ($visite->modifierVisite()) {
            $message = "La réservation de visite a été modifiée avec succès.";
        } else {
            $error = "Une erreur s'est produite lors de la modification de la réservation.";
        }
    }
}

// Récupération des réservations de visite
$visites = $visite->afficherVisites();
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
        /* Style pour le tableau de réservations */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .data-table th, 
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(201, 168, 108, 0.2);
        }
        
        .data-table th {
            background-color: rgba(36, 36, 36, 0.8);
            color: var(--gold-primary);
            font-weight: 600;
        }
        
        .data-table tr:hover {
            background-color: rgba(201, 168, 108, 0.1);
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .btn-edit {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #3e8e41;
        }
        
        .btn-delete {
            background-color: #F44336;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #d32f2f;
        }
        
        /* Style pour les messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: rgba(76, 175, 80, 0.2);
            border: 1px solid #4CAF50;
            color: #4CAF50;
        }
        
        .alert-danger {
            background-color: rgba(244, 67, 54, 0.2);
            border: 1px solid #F44336;
            color: #F44336;
        }
        
        /* Style pour le modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
        }
        
        .modal-content {
            background-color: #242424;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #c9a86c;
            border-radius: 8px;
            width: 60%;
            max-width: 600px;
            position: relative;
            color: white;
        }
        
        .close {
            color: #c9a86c;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #fff;
        }
        
        .form-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .form-group {
            flex: 1;
            margin-right: 15px;
        }
        
        .form-group:last-child {
            margin-right: 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #c9a86c;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid rgba(201, 168, 108, 0.2);
            border-radius: 4px;
            background-color: rgba(36, 36, 36, 0.8);
            color: white;
        }
        
        .form-control:focus {
            border-color: #c9a86c;
            outline: none;
        }
        
        .action-btn {
            background-color: transparent;
            border: 1px solid;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 14px;
            margin-right: 10px;
        }
        
        .action-btn.edit {
            border-color: #4CAF50;
            color: #4CAF50;
        }
        
        .action-btn.edit:hover {
            background-color: #4CAF50;
            color: #fff;
        }
        
        .action-btn.delete {
            border-color: #F44336;
            color: #F44336;
        }
        
        .action-btn.delete:hover {
            background-color: #F44336;
            color: #fff;
        }
        
        /* Status styles */
        .status {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status.pending {
            background-color: rgba(255, 193, 7, 0.2);
            color: #FFC107;
        }
        
        .status.confirmed {
            background-color: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
        }
        
        .status.completed {
            background-color: rgba(33, 150, 243, 0.2);
            color: #2196F3;
        }
        
        .status.cancelled {
            background-color: rgba(244, 67, 54, 0.2);
            color: #F44336;
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
                    <a href="admin-hebergements.php">
                        <i class="fas fa-hotel"></i>
                        <span>Hébergements</span>
                    </a>
                </li>
                <li class="active">
                    <a href="admin-reservations.php">
                        <i class="fas fa-calendar-check"></i>
                        <span>Réservations</span>
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
                <h2>Gestion des Réservations de Visite</h2>
            </div>
            
            <div class="nav-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Rechercher un CIN ou un nom...">
                </div>
                
                <div class="user-profile">
                    <img src="/api/placeholder/100/100" alt="Admin Profile">
                    <span>Admin</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <?php if(!empty($message)): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <?php if(!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <!-- Liste des réservations -->
            <div class="card">
                <div class="card-header">
                    <h3>Liste des réservations de visite</h3>
                    <div class="card-actions">
                        <button class="refresh-btn" id="refreshBtn">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="data-table" id="reservationsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>CIN</th>
                                <th>Nom Complet</th>
                                <th>Date Visite</th>
                                <th>Heure Visite</th>
                                <th>Type Villa</th>
                                <th>Nom Villa</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($visites)): ?>
                                <?php foreach($visites as $v): ?>
                                    <tr>
                                        <td><?php echo $v['id_visite']; ?></td>
                                        <td><?php echo $v['id_cin']; ?></td>
                                        <td><?php echo $v['nom_complet']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($v['date_visite'])); ?></td>
                                        <td><?php echo $v['heure_visite']; ?></td>
                                        <td><?php echo $v['type_villa'] ?? 'Non spécifié'; ?></td>
                                        <td><?php echo $v['nom_villa'] ?? 'Non spécifié'; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-edit" onclick="openEditModal(<?php echo $v['id_visite']; ?>, '<?php echo $v['id_cin']; ?>', '<?php echo $v['nom_complet']; ?>', '<?php echo $v['date_visite']; ?>', '<?php echo $v['heure_visite']; ?>', '<?php echo $v['type_villa'] ?? ''; ?>', '<?php echo $v['nom_villa'] ?? ''; ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-delete" onclick="confirmDelete(<?php echo $v['id_visite']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">Aucune réservation de visite trouvée</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de modification de réservation -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Modifier la réservation de visite</h2>
            
            <form id="editForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <input type="hidden" name="action" value="modifier">
                <input type="hidden" id="edit_id_visite" name="id_visite" value="">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_id_cin">Numéro CIN</label>
                        <input type="text" id="edit_id_cin" name="id_cin" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_nom_complet">Nom Complet</label>
                        <input type="text" id="edit_nom_complet" name="nom_complet" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_date_visite">Date de Visite</label>
                        <input type="date" id="edit_date_visite" name="date_visite" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_heure_visite">Heure de Visite</label>
                        <select id="edit_heure_visite" name="heure_visite" class="form-control" required>
                            <option value="09:00">09:00</option>
                            <option value="10:00">10:00</option>
                            <option value="11:00">11:00</option>
                            <option value="14:00">14:00</option>
                            <option value="15:00">15:00</option>
                            <option value="16:00">16:00</option>
                            <option value="17:00">17:00</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_type_villa">Type de Villa</label>
                        <input type="text" id="edit_type_villa" class="form-control" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_nom_villa">Nom de Villa</label>
                        <input type="text" id="edit_nom_villa" class="form-control" disabled>
                    </div>
                </div>
                
                <div class="form-group" style="text-align: right; margin-top: 20px;">
                    <button type="button" class="action-btn delete" onclick="closeEditModal()">Annuler</button>
                    <button type="submit" class="action-btn edit">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fonction pour ouvrir le modal de modification
        function openEditModal(id, cin, nom, date, heure, type, nomVilla) {
            document.getElementById('edit_id_visite').value = id;
            document.getElementById('edit_id_cin').value = cin;
            document.getElementById('edit_nom_complet').value = nom;
            document.getElementById('edit_date_visite').value = date;
            document.getElementById('edit_heure_visite').value = heure;
            document.getElementById('edit_type_villa').value = type;
            document.getElementById('edit_nom_villa').value = nomVilla;
            
            document.getElementById('editModal').style.display = 'block';
        }
        
        // Fonction pour fermer le modal de modification
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
      // Fonction pour confirmer la suppression
      function confirmDelete(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette réservation de visite ?')) {
                window.location.href = 'admin-reservations.php?action=supprimer&id=' + id;
            }
        }
        
        // Fonction de recherche dans le tableau
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const input = this.value.toLowerCase();
            const table = document.getElementById('reservationsTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const cin = rows[i].getElementsByTagName('td')[1];
                const nom = rows[i].getElementsByTagName('td')[2];
                
                if (cin && nom) {
                    const cinText = cin.textContent || cin.innerText;
                    const nomText = nom.textContent || nom.innerText;
                    
                    if (cinText.toLowerCase().indexOf(input) > -1 || nomText.toLowerCase().indexOf(input) > -1) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
        });
        
        // Bouton de rafraîchissement
        document.getElementById('refreshBtn').addEventListener('click', function() {
            window.location.reload();
        });
        
        // Toggle du sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('expanded');
        });
        
        // Fermer le modal en cliquant à l'extérieur
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
        
        // Validation de formulaire
        document.getElementById('editForm').addEventListener('submit', function(e) {
            const cinInput = document.getElementById('edit_id_cin');
            
            // Valider le format du CIN (8 chiffres pour la Tunisie)
            if (!/^\d{8}$/.test(cinInput.value)) {
                e.preventDefault();
                alert('Le numéro CIN doit contenir exactement 8 chiffres.');
                cinInput.focus();
            }
        });
    </script>
</body>
</html>