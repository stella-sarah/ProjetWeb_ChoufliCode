<?php
    // Connexion à la base de données
    require_once '../../config.php';
    $pdo = config::getConnexion();

    // Récupérer les transports depuis la base de données
    try {
        $query = $pdo->query("SELECT * FROM transport");
        $transports = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erreur de base de données: " . $e->getMessage());
    }

    // Préparer les données pour le JavaScript
    $transportData = [];
    foreach ($transports as $transport) {
        $availability = $transport['stock'] > 0 ? 'Disponible' : 'Non disponible';
        $availabilityClass = $transport['stock'] > 0 ? 'available' : 'unavailable';

        $transportData[strtolower($transport['type'])] = [
            'id' => $transport['id'],
            'title' => strtoupper($transport['nom']),
            'info' => [
                ['label' => 'Type:', 'value' => $transport['type']],
                ['label' => 'Disponibilité:', 'value' => $availability],
                ['label' => 'Prix/jour:', 'value' => $transport['prix'] . ' €'],
                ['label' => 'Statut:', 'value' => $availability, 'class' => $availabilityClass],
                ['label' => 'Description:', 'value' => $transport['description'], 'isDescription' => true]
            ],
            'image' => $transport['image'],
            'availability' => $availability,
            'availabilityClass' => $availabilityClass
        ];
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Luxury Living</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Style pour le badge de disponibilité */
        .availability-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            z-index: 10;
        }
        .availability-badge.available {
            background-color: #28a745;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .availability-badge.unavailable {
            background-color: #dc3545;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .gallery-item {
            position: relative;
        }
    </style>
</head>
<body>

    <!-- Header / Navigation -->
    <header>
        <div class="container nav-container">
            <div class="logo">
                <img src="../../image/tunify.png" alt="tunify logo">
                <div class="logo-text">
                    <h1>TuniFy</h1>
                    <p>Village</p>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="#about">Transport</a></li>
                    <li><a href="#gallery">Hebergement</a></li>
                    <li><a href="#features">Restauration</a></li>
                    <li><a href="#contact">Reclamations</a></li>
                </ul>
            </nav>
            <button class="contact-btn">Book a Tour</button>
        </div>
    </header>

    <!-- Gallery Section -->
    <section class="gallery" id="gallery">
        <div class="container gallery-container">
            <div class="gallery-header">
                <p class="section-subtitle">Immersive Experience</p>
                <h2 class="section-title">Discover Our <span class="animated-word">Stunning</span> Transports</h2>
            </div>
            <div class="gallery-grid">
                <?php foreach ($transports as $transport): ?>
                <div class="gallery-item" onclick="showTransportDetails('<?= strtolower($transport['type']) ?>')">
                    <span class="availability-badge <?= htmlspecialchars($transportData[strtolower($transport['type'])]['availabilityClass']) ?>">
                        <?= htmlspecialchars($transportData[strtolower($transport['type'])]['availability']) ?>
                    </span>
                    <img src="../../uploads/<?= htmlspecialchars($transport['image']) ?>" alt="<?= htmlspecialchars($transport['nom']) ?>">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title"><?= htmlspecialchars($transport['nom']) ?></h3>
                        <p class="gallery-item-subtitle"><?= htmlspecialchars($transport['type']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Transport Popup -->
    <div class="transport-popup" id="transportPopup">
        <div class="popup-overlay" onclick="hideTransportDetails()"></div>
        <div class="transport-popup-content">
            <div class="popup-header">
                <h2 id="popupTransportTitle">Transport Title</h2>
                <button class="close-popup" onclick="hideTransportDetails()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="popup-body">
                <div class="detail-row">
                    <div class="detail-image">
                        <img id="popupTransportImage" src="" alt="Transport Image">
                    </div>
                    <div class="detail-info">
                        <ul class="transport-info-list" id="transportInfoList">
                            <!-- Info will be inserted here by JavaScript -->
                        </ul>
                    </div>
                </div>
                <div class="detail-actions">
                    <button class="book-now-btn" onclick="bookNow()">
                        <i class="fas fa-calendar-check"></i> Book Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action Section -->
    <section class="cta">
        <div class="container cta-container">
            <h2 class="cta-title">Ready to Experience Luxury Living?</h2>
            <p class="cta-text">Schedule a private tour of TuniFy Village and discover your dream home in our exclusive community. Our knowledgeable consultants are ready to assist you in finding the perfect residence.</p>
            <div class="cta-buttons">
                <a href="#contact" class="cta-primary">Schedule a Tour</a>
                <a href="#" class="cta-secondary">Download Brochure</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-about">
                    <div class="logo">
                        <img src="../../image/tunify.png" alt="Jullanar Logo">
                        <div class="logo-text">
                            <h1>TuniFy</h1>
                            <p>Village</p>
                        </div>
                    </div>
                    <p>TuniFy Village is the epitome of luxury living, offering an exclusive residential experience with premium amenities and exceptional service in a stunning setting.</p>
                </div>
                
                <div class="footer-links">
                    <h3 class="footer-heading">Quick Links</h3>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#gallery">Properties</a></li>
                        <li><a href="#features">Amenities</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h3 class="footer-heading">Our Services</h3>
                    <ul>
                        <li><a href="#">Property Sales</a></li>
                        <li><a href="#">Property Management</a></li>
                        <li><a href="#">Interior Design</a></li>
                        <li><a href="#">Landscaping</a></li>
                        <li><a href="#">Concierge Services</a></li>
                    </ul>
                </div>
                
                <div class="footer-contact">
                    <h3 class="footer-heading">Contact Info</h3>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Luxury Boulevard, Golden District, City</p>
                    <p><i class="fas fa-phone"></i> +123 456 7890</p>
                    <p><i class="fas fa-envelope"></i> info@TuniFyvillage.com</p>
                    <p><i class="fas fa-clock"></i> Mon-Sat: 9:00 AM - 6:00 PM</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-copyright">
                    © 2025 TuniFy Village. All Rights Reserved. Designed by <a href="#">Kaptin</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Scroll Header Effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Transport data from PHP
        const transportData = <?= json_encode($transportData) ?>;
        let selectedTransportId = null;

        // Transport Details Popup
        function showTransportDetails(transportType) {
            const transport = transportData[transportType];
            selectedTransportId = transport.id;
            const titleElement = document.getElementById('popupTransportTitle');
            const imageElement = document.getElementById('popupTransportImage');
            const infoList = document.getElementById('transportInfoList');
            
            // Set title and image
            titleElement.textContent = transport.title;
            imageElement.src = `../../uploads/${transport.image}`;
            imageElement.alt = transport.title;
            
            // Clear previous info
            infoList.innerHTML = '';
            
            // Add info with animation
            transport.info.forEach((item, index) => {
                setTimeout(() => {
                    const li = document.createElement('li');
                    if (item.label === 'Statut:') {
                        li.innerHTML = `<span class="info-label">${item.label}</span> <span class="status-badge ${item.class}">${item.value}</span>`;
                    } else if (item.isDescription) {
                        li.innerHTML = `<span class="info-label">${item.label}</span> <span class="info-description">${item.value}</span>`;
                    } else {
                        li.innerHTML = `<span class="info-label">${item.label}</span> <span>${item.value}</span>`;
                    }
                    li.classList.add('fade-in-item');
                    infoList.appendChild(li);
                }, index * 100);
            });
            
            // Show popup with animation
            const popup = document.getElementById('transportPopup');
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function hideTransportDetails() {
            const popup = document.getElementById('transportPopup');
            popup.style.display = 'none';
            document.body.style.overflow = 'auto';
            selectedTransportId = null;
        }

        function bookNow() {
            if (selectedTransportId) {
                window.location.href = `reservation_transport.php?id=${selectedTransportId}`;
            } else {
                alert('Erreur : Aucun transport sélectionné.');
            }
        }

        document.querySelectorAll('.gallery-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
                this.style.boxShadow = '0 15px 30px rgba(0,0,0,0.2)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
            });
        });
    </script>
</body>
</html>