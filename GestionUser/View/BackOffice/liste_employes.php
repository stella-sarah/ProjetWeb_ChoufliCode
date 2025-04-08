<!DOCTYPE html>
<html>
<head>
    <title>Backoffice</title>
    <link rel="stylesheet" href="/GestionUser/Assets/CSS/styleback.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <!-- Chemin corrigé pour l'image -->
                <img src="/GestionUser/Assets/logo.png" alt="Logo"> 
                <div class="logo-text">
                    <h1>Admin</h1>
                </div>
            </div>
        </div>
        <!-- Menu sidebar -->
    </div>

    <div class="main-content">
        <div class="top-nav">
            <!-- Ajoutez le contenu de la navbar -->
            <div class="nav-left">
                <button class="sidebar-toggle">☰</button>
                <h2>Tableau de bord</h2>
            </div>
        </div>
        
        <div class="content-area">
            <div class="card">
                <div class="card-header">
                    <h3>Gestion des employés</h3>
                </div>
                <div class="card-body">
                    <!-- Tableau corrigé -->
                    <table class="data-table">
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Rôle</th> <!-- Nouvelle colonne -->
                        </tr>
                        <?php 
// Fichier temporaire (à remplacer par un vrai contrôleur)
$employes = [
    new Utilisateur("John", "john@mail.com", "admin"),
    new Utilisateur("Alice", "alice@mail.com", "employe")
];
foreach ($employes as $e): 
?>
                        <tr>
                            <td><?= $e->getNom() ?></td>
                            <td><?= $e->getEmail() ?></td>
                            <td><?= $e->getTelephone() ?></td>
                            <!-- Badge de rôle ajouté -->
                            <td>
                                <span class="role-badge <?= ($e->getRole() === 'admin') ? 'role-admin' : 'role-employe' ?>">
                                    <?= $e->getRole() ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>