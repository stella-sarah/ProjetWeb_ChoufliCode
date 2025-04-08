<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="C:/xampp/htdocs/GestionUser/Assets/CSS/styleback.css">
</head>
<body>
    <div class="sidebar">
        <!-- Sidebar content -->
    </div>
    
    <div class="main-content">
        <div class="top-nav">
            <h2>Gestion des Rôles</h2>
        </div>

        <div class="content-area">
            <div class="card">
                <div class="card-header">
                    <h3>Attribution des rôles</h3>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <tr>
                            <th>Nom</th>
                            <th>Rôle actuel</th>
                            <th>Nouveau rôle</th>
                        </tr>
                        <?php foreach ($utilisateurs as $u): ?>
                        <tr>
                            <td><?= $u->getNomComplet() ?></td>
                            <td><?= $u->getRole() ?></td>
                            <td>
                                <select class="filter-select" onchange="updateRole(<?= $u->getId() ?>, this.value)">
                                    <option value="agent">Agent</option>
                                    <option value="responsable">Responsable</option>
                                </select>
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