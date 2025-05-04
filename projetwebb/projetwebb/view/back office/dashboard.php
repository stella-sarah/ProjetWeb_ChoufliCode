<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Backoffice</title>
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
                <li class="active">
                    <a href="#">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
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
                <h2>Dashboard</h2>
            </div>
            <div class="nav-right">
                <div class="search-box">
                    <input type="text" placeholder="Rechercher...">
                    <i class="fas fa-search"></i>
                </div>
                <div class="user-profile">
                    <img src="https://via.placeholder.com/40" alt="Profile">
                    <span>Admin</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Stats Cards -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(75, 192, 192, 0.1);">
                        <i class="fas fa-car" style="color: #4bc0c0;"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Transports</h3>
                        <p>24 Réservations</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(54, 162, 235, 0.1);">
                        <i class="fas fa-hotel" style="color: #36a2eb;"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Hébergements</h3>
                        <p>15 Réservations</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(255, 99, 132, 0.1);">
                        <i class="fas fa-utensils" style="color: #ff6384;"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Restauration</h3>
                        <p>32 Réservations</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(255, 159, 64, 0.1);">
                        <i class="fas fa-comment-alt" style="color: #ff9f40;"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Réclamations</h3>
                        <p>5 Non lues</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="card">
                <div class="card-header">
                    <h3>Activités Récentes</h3>
                    <a href="#" class="view-all">Voir tout</a>
                </div>
                <div class="card-body">
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="activity-content">
                                <p><strong>Nouvelle réservation</strong> de voiture par <strong>John Doe</strong></p>
                                <small>Il y a 10 minutes</small>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-hotel"></i>
                            </div>
                            <div class="activity-content">
                                <p><strong>Chambre réservée</strong> par <strong>Jane Smith</strong> pour 3 nuits</p>
                                <small>Il y a 45 minutes</small>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-comment-alt"></i>
                            </div>
                            <div class="activity-content">
                                <p><strong>Nouvelle réclamation</strong> de la part de <strong>Robert Johnson</strong></p>
                                <small>Il y a 2 heures</small>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-content">
                                <p><strong>Nouvel utilisateur</strong> enregistré: <strong>Sarah Williams</strong></p>
                                <small>Il y a 5 heures</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Reservations -->
            <div class="card">
                <div class="card-header">
                    <h3>Réservations Récentes</h3>
                    <a href="#" class="view-all">Voir tout</a>
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
                                <td><span class="status confirmed">Confirmé</span></td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#1253</td>
                                <td>Jane Smith</td>
                                <td>Suite Deluxe</td>
                                <td>14 Juin 2023</td>
                                <td><span class="status pending">En attente</span></td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#1252</td>
                                <td>Robert Johnson</td>
                                <td>Dîner gastronomique</td>
                                <td>14 Juin 2023</td>
                                <td><span class="status completed">Complété</span></td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#1251</td>
                                <td>Sarah Williams</td>
                                <td>Trottinette électrique</td>
                                <td>13 Juin 2023</td>
                                <td><span class="status cancelled">Annulé</span></td>
                                <td>
                                    <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar
        document.querySelector('.sidebar-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
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