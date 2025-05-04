<?php
if (!isset($page_title)) {
    $page_title = 'Restaurant';
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Restaurant'; ?></title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- AOS Animation -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

    <style>
        :root {
            --primary-dark: #0a0a0a;
            --secondary-dark: #1a1a1a;
            --accent-color: #d4af37;
            --accent-color-hover: #e6c875;
            --text-primary: #f0f0f0;
            --text-secondary: #cccccc;
            --border-color: #333333;
            --hover-dark: #252525;
            --card-bg: #141414;
            --error-color: #dc3545;
            --success-color: #28a745;
            --warning-color: #ffc107;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-color: var(--primary-dark);
            color: var(--text-primary);
            overflow-x: hidden;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            position: fixed;
            width: 100%;
            z-index: 1000;
            transition: all 0.3s ease;
            padding: 15px 0;
            background-color: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
        }

        header.scrolled {
            padding: 10px 0;
            background-color: rgba(10, 10, 10, 0.98);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
        }

        .logo img {
            height: 50px;
            transition: transform 0.3s ease;
        }

        .logo:hover img {
            transform: scale(1.05);
        }

        .logo-text {
            line-height: 1.2;
        }

        .logo-text h1 {
            font-family: 'Cinzel', serif;
            font-weight: 700;
            font-size: 24px;
            color: var(--accent-color);
            margin: 0;
            letter-spacing: 1px;
        }

        .logo-text p {
            font-size: 12px;
            font-weight: 300;
            color: var(--text-secondary);
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 40px;
        }

        nav ul li a {
            color: var(--text-primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            padding: 5px 0;
            position: relative;
        }

        nav ul li a:hover {
            color: var(--accent-color);
        }

        nav ul li a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--accent-color);
            transition: width 0.3s ease;
        }

        nav ul li a:hover::after {
            width: 100%;
        }

        .contact-btn {
            background-color: transparent;
            border: 1px solid var(--accent-color);
            color: var(--accent-color);
            padding: 12px 30px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 30px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .contact-btn:hover {
            background-color: var(--accent-color);
            color: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
        }

        /* Main Content */
        main {
            min-height: calc(100vh - 200px);
            padding-top: 90px;
        }

        /* Page Header */
        .page-header {
            padding: 80px 0;
            text-align: center;
            background-color: var(--secondary-dark);
            margin-bottom: 50px;
        }

        .section-subtitle {
            color: var(--accent-color);
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            color: var(--text-primary);
            margin-bottom: 20px;
        }

        .section-title span {
            color: var(--accent-color);
        }

        /* Forms */
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background-color: var(--secondary-dark);
            border: 1px solid var(--border-color);
            border-radius: 5px;
            color: var(--text-primary);
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.1);
        }

        .form-label {
            display: block;
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }

        .btn-primary {
            background-color: var(--accent-color);
            color: var(--primary-dark);
        }

        .btn-primary:hover {
            background-color: var(--accent-color-hover);
            transform: translateY(-2px);
        }

        .btn-outline-light {
            background-color: transparent;
            border: 1px solid var(--text-primary);
            color: var(--text-primary);
        }

        .btn-outline-light:hover {
            background-color: var(--text-primary);
            color: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Footer */
        footer {
            background-color: var(--secondary-dark);
            padding: 50px 0 20px;
            margin-top: 100px;
            border-top: 1px solid var(--border-color);
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            color: var(--accent-color);
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .footer-section p {
            color: var(--text-secondary);
            margin-bottom: 10px;
            font-size: 14px;
            line-height: 1.6;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--accent-color);
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-links a {
            color: var(--text-secondary);
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            color: var(--accent-color);
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .footer-bottom p {
            color: var(--text-secondary);
            font-size: 12px;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .container {
                padding: 0 30px;
            }

            nav ul {
                gap: 30px;
            }
        }

        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }

            nav ul {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background-color: var(--primary-dark);
                padding: 20px;
                flex-direction: column;
                gap: 15px;
                border-top: 1px solid var(--border-color);
            }

            nav ul.active {
                display: flex;
            }

            .logo-text h1 {
                font-size: 20px;
            }

            .section-title {
                font-size: 32px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 20px;
            }

            .logo img {
                height: 40px;
            }

            .logo-text h1 {
                font-size: 18px;
            }

            .logo-text p {
                font-size: 10px;
            }

            .contact-btn {
                padding: 8px 20px;
                font-size: 12px;
            }
        }
    </style>

    <?php if (isset($additional_css)) echo $additional_css; ?>
</head>

<body>
    <header>
        <div class="container">
            <div class="nav-container">
                <a href="index.php" class="logo">
                    <img src="assets/img/logo.png" alt="Restaurant Logo">
                    <div class="logo-text">
                        <h1>Tunify</h1>
                        <p>Cuisine Authentique</p>
                    </div>
                </a>

                <button class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>

                <nav>
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="menu.php">Menu</a></li>
                        <li><a href="commande.php">Commander</a></li>
                        <li><a href="reservation.php">Réservation</a></li>
                        <li><a href="suivi_commande.php">Suivi Commande</a></li>
                    </ul>
                </nav>

                <a href="tel:+21600000000" class="contact-btn">
                    <i class="fas fa-phone"></i>
                    Contactez-nous
                </a>
            </div>
        </div>
    </header>

    <main>
        <?php if (isset($page_header) && $page_header): ?>
        <section class="page-header">
            <div class="container">
                <h2><?php echo $page_title; ?></h2>
            </div>
        </section>
        <?php endif; ?>

        <?php echo $content; ?>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>À Propos</h3>
                    <p>Le Bistrot vous accueille dans une ambiance chaleureuse pour déguster une cuisine authentique et raffinée.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h3>Horaires</h3>
                    <p>Lundi - Vendredi: 11h - 23h</p>
                    <p>Samedi - Dimanche: 10h - 00h</p>
                </div>

                <div class="footer-section">
                    <h3>Contact</h3>
                    <p><i class="fas fa-phone"></i> +216 00 000 000</p>
                    <p><i class="fas fa-envelope"></i> contact@lebistrot.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Rue Example, Ville</p>
                </div>

                <div class="footer-section">
                    <h3>Liens Rapides</h3>
                    <ul class="footer-links">
                        <li><a href="menu.php">Notre Menu</a></li>
                        <li><a href="reservation.php">Réservation</a></li>
                        <li><a href="suivi_commande.php">Suivi Commande</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Le Bistrot. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            offset: 100,
            once: true
        });

        // Header Scroll Effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Mobile Menu Toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const nav = document.querySelector('nav ul');

        mobileMenuBtn.addEventListener('click', function() {
            nav.classList.toggle('active');
            this.innerHTML = nav.classList.contains('active') 
                ? '<i class="fas fa-times"></i>' 
                : '<i class="fas fa-bars"></i>';
        });
    </script>

    <?php if (isset($additional_js)) echo $additional_js; ?>
</body>

</html> 