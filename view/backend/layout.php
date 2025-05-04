<?php
$page_title = isset($page_title) ? $page_title : 'Dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Tunify - Administration'; ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        :root {
            --gold-primary: #c9a86c;
            --gold-light: #e9d9b6;
            --gold-dark: #8b783d;
            --dark-bg: #1c1c1c;
            --darker-bg: #111111;
            --light-text: #f8f5eb;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --top-nav-height: 70px;
            --success-color: #28a745;
            --error-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --transition-speed: 0.3s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-color: var(--darker-bg);
            color: var(--light-text);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--dark-bg);
            height: 100vh;
            position: fixed;
            transition: all var(--transition-speed) ease;
            z-index: 1000;
            border-right: 1px solid rgba(201, 168, 108, 0.1);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 25px;
            border-bottom: 1px solid rgba(201, 168, 108, 0.2);
            background-color: rgba(201, 168, 108, 0.05);
        }

        .logo {
            display: flex;
            align-items: center;
            transition: all var(--transition-speed) ease;
        }

        .logo-text {
            margin-left: 15px;
        }

        .logo-text h1 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 24px;
            color: var(--gold-primary);
            margin: 0;
            transition: all var(--transition-speed) ease;
        }

        .logo-text p {
            font-size: 12px;
            color: var(--gold-light);
            transition: all var(--transition-speed) ease;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .sidebar-nav ul {
            list-style: none;
        }

        .sidebar-nav li {
            margin-bottom: 5px;
        }

        .sidebar-nav li a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: rgba(248, 245, 235, 0.7);
            text-decoration: none;
            transition: all var(--transition-speed) ease;
            font-size: 14px;
            border-radius: 5px;
            margin: 0 10px;
        }

        .sidebar-nav li a:hover {
            background-color: rgba(201, 168, 108, 0.1);
            color: var(--gold-primary);
            transform: translateX(5px);
        }

        .sidebar-nav li.active a {
            background-color: rgba(201, 168, 108, 0.2);
            color: var(--gold-primary);
            border-left: 3px solid var(--gold-primary);
        }

        .sidebar-nav li i {
            font-size: 18px;
            margin-right: 15px;
            width: 20px;
            text-align: center;
            transition: all var(--transition-speed) ease;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            transition: all var(--transition-speed) ease;
        }

        /* Top Navigation */
        .top-nav {
            height: var(--top-nav-height);
            background-color: var(--dark-bg);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(201, 168, 108, 0.1);
        }

        .nav-left h2 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            color: var(--gold-light);
            font-size: 22px;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all var(--transition-speed) ease;
        }

        .user-profile:hover {
            background-color: rgba(201, 168, 108, 0.1);
        }

        .user-profile img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--gold-primary);
        }

        /* Content Area */
        .content-area {
            padding: 30px;
        }

        /* Card Styles */
        .card {
            background-color: var(--dark-bg);
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(201, 168, 108, 0.1);
            transition: all var(--transition-speed) ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(201, 168, 108, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            font-family: 'Playfair Display', serif;
            color: var(--gold-light);
            font-size: 20px;
            font-weight: 600;
        }

        .card-body {
            padding: 25px;
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .data-table th {
            background-color: rgba(201, 168, 108, 0.1);
            color: var(--gold-primary);
            font-weight: 600;
            padding: 15px;
            text-align: left;
            font-size: 14px;
            border-bottom: 2px solid rgba(201, 168, 108, 0.2);
        }

        .data-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(201, 168, 108, 0.1);
            color: var(--light-text);
            font-size: 14px;
        }

        .data-table tr:hover td {
            background-color: rgba(201, 168, 108, 0.05);
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 5px;
            font-weight: 500;
            transition: all var(--transition-speed) ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background-color: var(--gold-primary);
            color: var(--darker-bg);
        }

        .btn-primary:hover {
            background-color: var(--gold-dark);
            transform: translateY(-1px);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--gold-primary);
            color: var(--gold-primary);
        }

        .btn-outline:hover {
            background-color: rgba(201, 168, 108, 0.1);
            transform: translateY(-1px);
        }

        /* Form Styles */
        .form-control {
            background-color: var(--darker-bg);
            border: 1px solid rgba(201, 168, 108, 0.2);
            color: var(--light-text);
            padding: 10px 15px;
            border-radius: 5px;
            transition: all var(--transition-speed) ease;
        }

        .form-control:focus {
            background-color: var(--darker-bg);
            border-color: var(--gold-primary);
            color: var(--light-text);
            box-shadow: 0 0 0 0.2rem rgba(201, 168, 108, 0.25);
        }

        /* Alert Styles */
        .alert {
            border-radius: 5px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            border: 1px solid var(--success-color);
            color: var(--success-color);
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid var(--error-color);
            color: var(--error-color);
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .sidebar {
                width: var(--sidebar-collapsed-width);
            }

            .main-content {
                margin-left: var(--sidebar-collapsed-width);
                width: calc(100% - var(--sidebar-collapsed-width));
            }

            .logo-text {
                display: none;
            }

            .sidebar-nav li a span {
                display: none;
            }

            .sidebar-nav li a {
                justify-content: center;
                padding: 15px;
            }

            .sidebar-nav li i {
                margin-right: 0;
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-utensils" style="font-size: 30px; color: var(--gold-primary);"></i>
                <div class="logo-text">
                    <h1>Tunify</h1>
                    <p>Administration</p>
                </div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <a href="index.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'gestion_plats.php' ? 'active' : ''; ?>">
                    <a href="gestion_plats.php">
                        <i class="fas fa-utensils"></i>
                        <span>Gestion des Plats</span>
                    </a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'gestion_commandes.php' ? 'active' : ''; ?>">
                    <a href="gestion_commandes.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Gestion des Commandes</span>
                    </a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'gestion_livraisons.php' ? 'active' : ''; ?>">
                    <a href="gestion_livraisons.php">
                        <i class="fas fa-truck"></i>
                        <span>Gestion des Livraisons</span>
                    </a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'gestion_reservations.php' ? 'active' : ''; ?>">
                    <a href="gestion_reservations.php">
                        <i class="fas fa-calendar-check"></i>
                        <span>Gestion des Réservations</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="nav-left">
                <h2><?php echo $page_title; ?></h2>
            </div>
            <div class="nav-right">
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=c9a86c&color=fff" alt="Admin">
                    <span>Admin</span>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <?php echo $content; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('.data-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
                },
                responsive: true,
                pageLength: 10,
                order: [[0, 'desc']]
            });
        });

        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            }
        }
    </script>
</body>
</html> 