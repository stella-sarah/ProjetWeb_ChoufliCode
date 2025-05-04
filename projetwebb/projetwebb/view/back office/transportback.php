<?php
session_start();
require_once '../../controller/transportcontroller.php';

$transportController = new TransportController();

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajout d'un transport
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        // Gestion de l'upload de l'image
        $imageName = 'default.jpg';
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../Uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileType = $_FILES['image']['type'];
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
            
            if (array_key_exists($fileType, $allowedTypes)) {
                $extension = $allowedTypes[$fileType];
                $imageName = uniqid('transport_') . '.' . $extension;
                $uploadFile = $uploadDir . $imageName;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                    $imageName = 'default.jpg';
                }
            }
        }
        
        // Récupération des données
        $nom = htmlspecialchars($_POST['name'] ?? '');
        $type = htmlspecialchars($_POST['type'] ?? '');
        $prix = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $description = htmlspecialchars($_POST['description'] ?? '');
        
        // Validation des données
        if (empty($nom) || empty($type) || $prix <= 0 || $stock < 0) {
            $_SESSION['error_message'] = "Veuillez remplir tous les champs correctement.";
            header("Location: transportback.php");
            exit();
        }

        // Ajout ou incrémentation dans la base
        $result = $transportController->ajouterTransport($imageName, $nom, $type, $prix, $stock, $description);
        
        if ($result) {
            $_SESSION['success_message'] = "Transport ajouté ou stock mis à jour avec succès!";
        } else {
            $_SESSION['error_message'] = "Erreur lors de l'ajout ou de la mise à jour du transport.";
        }
        header("Location: transportback.php");
        exit();
    }
    
    // Modification d'un transport
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $imageName = null;
        
        // Validation de l'ID
        if ($id <= 0) {
            $_SESSION['error_message'] = "ID de transport invalide.";
            header("Location: transportback.php");
            exit();
        }

        // Gestion de l'upload de l'image (si une nouvelle image est fournie)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../Uploads/';
            $fileType = $_FILES['image']['type'];
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
            
            if (array_key_exists($fileType, $allowedTypes)) {
                $extension = $allowedTypes[$fileType];
                $imageName = uniqid('transport_') . '.' . $extension;
                $uploadFile = $uploadDir . $imageName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                    $oldTransport = $transportController->getTransportById($id);
                    if ($oldTransport && $oldTransport['image'] !== 'default.jpg') {
                        $oldImagePath = $uploadDir . $oldTransport['image'];
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                } else {
                    $imageName = null;
                }
            }
        }
        
        // Récupération des données
        $nom = htmlspecialchars($_POST['name'] ?? '');
        $type = htmlspecialchars($_POST['type'] ?? '');
        $prix = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $description = htmlspecialchars($_POST['description'] ?? '');
        
        // Validation des données
        if (empty($nom) || empty($type) || $prix <= 0 || $stock < 0) {
            $_SESSION['error_message'] = "Veuillez remplir tous les champs correctement.";
            header("Location: transportback.php");
            exit();
        }

        // Mise à jour dans la base
        $result = $transportController->modifierTransport($id, $imageName, $nom, $type, $prix, $stock, $description);
        
        if ($result) {
            $_SESSION['success_message'] = "Transport modifié avec succès!";
        } else {
            $_SESSION['error_message'] = "Erreur lors de la modification du transport. Vérifiez les données ou les contraintes de la base de données.";
        }
        header("Location: transportback.php");
        exit();
    }
}

// Suppression d'un transport
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Validation de l'ID
    if ($id <= 0) {
        $_SESSION['error_message'] = "ID de transport invalide.";
        header("Location: transportback.php");
        exit();
    }

    $result = $transportController->supprimerTransport($id);
    
    if ($result) {
        $_SESSION['success_message'] = "Transport supprimé avec succès!";
    } else {
        $_SESSION['error_message'] = "Erreur lors de la suppression du transport. Vérifiez si le transport existe ou s'il est lié à des réservations.";
    }
    header("Location: transportback.php");
    exit();
}

// Récupération des transports
$transports = $transportController->getAllTransports();

// Gestion des messages de session
$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Gestion Transport</title>
    <link rel="stylesheet" href="../../styleback.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <img src="../../image/tunify.png" alt="tunify logo">
                <div class="logo-text">
                    <h1>TuniFy</h1>
                    <p>Admin Panel</p>
                </div>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="active">
                    <a href="transportback.php">
                        <i class="fas fa-car"></i>
                        <span>Transports</span>
                    </a>
                </li>
                <li>
                    <a href="#">
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
                        <i class="fas fa-comment-alt"></i>
                        <span>Réclamations</span>
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
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <header class="top-nav">
            <div class="nav-left">
                <button class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h2>Gestion des Transports</h2>
            </div>
            <div class="nav-right">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Rechercher un transport..." oninput="searchTransports()">
                    <i class="fas fa-search"></i>
                </div>
                <button class="add-btn" onclick="openModal('addTransportModal')">
                    <i class="fas fa-plus"></i> Ajouter Transport
                </button>
                <div class="user-profile">
                    <img src="../../image/tunify.png" alt="tunify logo">
                    <span>Admin</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Transport Types Tabs -->
            <div class="tabs">
                <button class="tab-btn active" onclick="filterTransports('all')">Tous</button>
                <button class="tab-btn" onclick="filterTransports('voiture')">Voitures</button>
                <button class="tab-btn" onclick="filterTransports('moto')">Motos</button>
                <button class="tab-btn" onclick="filterTransports('trottinette')">Trottinettes</button>
                <button class="tab-btn" onclick="filterTransports('bus')">Bus</button>
                <button class="tab-btn" onclick="filterTransports('metro')">Métro</button>
                <button class="tab-btn" onclick="filterTransports('bicyclette')">Bicyclettes</button>
            </div>

            <!-- Transport List -->
            <div class="card">
                <div class="card-header">
                    <h3>Liste des Transports</h3>
                    <div class="card-actions">
                        <select class="filter-select" onchange="sortTransports(this.value)">
                            <option value="">Trier par</option>
                            <option value="name-asc">Nom (A-Z)</option>
                            <option value="name-desc">Nom (Z-A)</option>
                            <option value="price-asc">Prix croissant</option>
                            <option value="price-desc">Prix décroissant</option>
                        </select>
                        <button class="refresh-btn" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Prix/jour</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="transportTableBody">
                            <?php foreach ($transports as $transport): ?>
                            <tr data-type="<?= htmlspecialchars(strtolower($transport['type'])) ?>" data-name="<?= htmlspecialchars(strtolower($transport['nom'])) ?>" data-price="<?= $transport['prix'] ?>">
                                <td>#<?= htmlspecialchars($transport['id']) ?></td>
                                <td><img src="../../Uploads/<?= htmlspecialchars($transport['image']) ?>" alt="<?= htmlspecialchars($transport['nom']) ?>" class="transport-img"></td>
                                <td><?= htmlspecialchars($transport['nom']) ?></td>
                                <td><?= htmlspecialchars($transport['type']) ?></td>
                                <td><?= htmlspecialchars($transport['prix']) ?> €</td>
                                <td>
                                    <span class="status <?= $transport['stock'] > 0 ? 'active' : 'reserved' ?>">
                                        <?= htmlspecialchars($transport['stock']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn view" onclick="viewTransport(<?= $transport['id'] ?>)"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit" onclick="editTransport(<?= $transport['id'] ?>)"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete" onclick="confirmDelete(<?= $transport['id'] ?>)"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Transport Modal -->
    <div class="modal" id="addTransportModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('addTransportModal')">×</span>
            <h2>Ajouter un Nouveau Transport</h2>
            <form class="transport-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>Type de Transport</label>
                    <select name="type" required>
                        <option value="">Sélectionner un type</option>
                        <option value="Voiture">Voiture</option>
                        <option value="Moto">Moto</option>
                        <option value="Trottinette">Trottinette</option>
                        <option value="Bus">Bus</option>
                        <option value="Métro">Métro</option>
                        <option value="Bicyclette">Bicyclette</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Nom du Transport</label>
                    <input type="text" name="name" placeholder="Ex: Voiture" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Description détaillée..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Prix par jour (€)</label>
                    <input type="number" name="price" step="0.01" placeholder="Ex: 2250" required>
                </div>
                
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" min="0" placeholder="Ex: 5" required>
                </div>
                
                <div class="form-group">
                    <label>Image du Transport</label>
                    <div class="file-upload">
                        <input type="file" id="transportImage" name="image" accept="image/*">
                        <label for="transportImage">Choisir une image</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closeModal('addTransportModal')">Annuler</button>
                    <button type="submit" class="submit-btn">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Transport Modal -->
    <div class="modal" id="viewTransportModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('viewTransportModal')">×</span>
            <h2 id="viewTransportTitle">Détails du Transport</h2>
            <div class="transport-detail-content" id="transportDetailContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>

    <!-- Edit Transport Modal -->
    <div class="modal" id="editTransportModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('editTransportModal')">×</span>
            <h2>Modifier le Transport</h2>
            <form class="transport-form" method="POST" enctype="multipart/form-data" id="editTransportForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editTransportId">
                
                <div class="form-group">
                    <label>Type de Transport</label>
                    <select name="type" id="editTransportType" required>
                        <option value="Voiture">Voiture</option>
                        <option value="Moto">Moto</option>
                        <option value="Trottinette">Trottinette</option>
                        <option value="Bus">Bus</option>
                        <option value="Métro">Métro</option>
                        <option value="Bicyclette">Bicyclette</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Nom du Transport</label>
                    <input type="text" name="name" id="editTransportName" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="editTransportDescription" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Prix par jour (€)</label>
                    <input type="number" name="price" id="editTransportPrice" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" id="editTransportStock" min="0" required>
                </div>
                
                <div class="form-group">
                    <label>Image du Transport</label>
                    <div class="file-upload">
                        <input type="file" id="editTransportImage" name="image" accept="image/*">
                        <label for="editTransportImage">Changer l'image</label>
                    </div>
                    <div class="current-image">
                        <small>Image actuelle:</small>
                        <img id="currentTransportImage" src="" width="100">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closeModal('editTransportModal')">Annuler</button>
                    <button type="submit" class="submit-btn">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages d'alerte -->
    <?php if (isset($successMessage)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= $successMessage ?>
    </div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?= $errorMessage ?>
    </div>
    <?php endif; ?>

    <script>
        // Fonctions pour les modals
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Afficher les détails d'un transport
        function viewTransport(id) {
            fetch(`getTransport.php?id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau');
                    }
                    return response.json();
                })
                .then(transport => {
                    document.getElementById('viewTransportTitle').textContent = transport.nom;
                    
                    const detailContent = document.getElementById('transportDetailContent');
                    detailContent.innerHTML = `
                        <div class="detail-row">
                            <div class="detail-image">
                                <img src="../../Uploads/${transport.image}" alt="${transport.nom}" style="max-width: 200px;">
                            </div>
                            <div class="detail-info">
                                <div class="detail-item">
                                    <span class="detail-label">Type:</span>
                                    <span class="detail-value">${transport.type}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Prix/jour:</span>
                                    <span class="detail-value">${transport.prix} €</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Stock:</span>
                                    <span class="detail-value">${transport.stock}</span>
                                </div>
                                <div class="detail-item full-width">
                                    <span class="detail-label">Description:</span>
                                    <p class="detail-description">${transport.description}</p>
                                </div>
                            </div>
                        </div>
                        <div class="detail-actions">
                            <button class="edit-btn" onclick="editTransport(${transport.id})">
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                        </div>
                    `;
                    
                    openModal('viewTransportModal');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur lors du chargement des détails du transport');
                });
        }
        
        // Modifier un transport
        function editTransport(id) {
            fetch(`getTransport.php?id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau');
                    }
                    return response.json();
                })
                .then(transport => {
                    document.getElementById('editTransportId').value = transport.id;
                    document.getElementById('editTransportType').value = transport.type;
                    document.getElementById('editTransportName').value = transport.nom;
                    document.getElementById('editTransportDescription').value = transport.description;
                    document.getElementById('editTransportPrice').value = transport.prix;
                    document.getElementById('editTransportStock').value = transport.stock;
                    document.getElementById('currentTransportImage').src = `../../Uploads/${transport.image}`;
                    
                    openModal('editTransportModal');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur lors du chargement des données du transport');
                });
        }
        
        // Confirmer la suppression
        function confirmDelete(id) {
            if (confirm(`Voulez-vous vraiment supprimer ce transport ? Cette action est irréversible.`)) {
                window.location.href = `transportback.php?action=delete&id=${id}`;
            }
        }
        
        // Filtrer les transports par type
        let currentTypeFilter = 'all';
        
        function filterTransports(type) {
            currentTypeFilter = type;
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            event.currentTarget.classList.add('active');
            
            applyFiltersAndSearch();
        }
        
        // Recherche des transports
        function searchTransports() {
            applyFiltersAndSearch();
        }
        
        // Appliquer les filtres et la recherche
        function applyFiltersAndSearch() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#transportTableBody tr');
            
            rows.forEach(row => {
                const name = row.dataset.name.toLowerCase();
                const type = row.dataset.type.toLowerCase();
                
                // Vérifier si la ligne correspond à la recherche
                const matchesSearch = name.includes(input) || type.includes(input);
                
                // Vérifier si la ligne correspond au filtre de type
                const matchesType = currentTypeFilter === 'all' || type === currentTypeFilter.toLowerCase();
                
                // Afficher la ligne si elle correspond à la recherche et au filtre
                row.style.display = matchesSearch && matchesType ? '' : 'none';
            });
        }
        
        // Trier les transports
        function sortTransports(criteria) {
            const tableBody = document.getElementById('transportTableBody');
            const rows = Array.from(tableBody.querySelectorAll('tr'));
            
            rows.sort((a, b) => {
                if (criteria === 'name-asc') {
                    return a.dataset.name.localeCompare(b.dataset.name);
                } else if (criteria === 'name-desc') {
                    return b.dataset.name.localeCompare(a.dataset.name);
                } else if (criteria === 'price-asc') {
                    return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                } else if (criteria === 'price-desc') {
                    return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                }
                return 0;
            });
            
            // Vider le tableau et réinsérer les lignes triées
            tableBody.innerHTML = '';
            rows.forEach(row => tableBody.appendChild(row));
            
            // Réappliquer la recherche et le filtrage après le tri
            applyFiltersAndSearch();
        }
        
        // Fermer les modals en cliquant à l'extérieur
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    modal.style.display = 'none';
                });
                document.body.style.overflow = 'auto';
            }
        }
        
        // Cacher les alertes après 3 secondes
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert) alert.style.display = 'none';
            });
        }, 3000);
    </script>
</body>
</html>