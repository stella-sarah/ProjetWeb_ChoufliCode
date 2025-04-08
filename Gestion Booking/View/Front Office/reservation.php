<?php
// Reservation.php - Page listing available properties
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Réservation</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header / Navigation -->
    <header>
        <div class="container nav-container">
            <div class="logo">
                <img src="logo.png" alt="TuniFy Logo">
                <div class="logo-text">
                    <h1>TuniFy</h1>
                    <p>Village</p>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php#home">Accueil</a></li>
                    <li><a href="index.php#about">À Propos</a></li>
                    <li><a href="index.php#gallery">Galerie</a></li>
                    <li><a href="index.php#features">Services</a></li>
                    <li><a href="reservation.php" class="active">Réservation</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                </ul>
            </nav>
            <button class="contact-btn">Réserver une Visite</button>
        </div>
    </header>

    <!-- Reservation Hero Section -->
    <section class="hero" id="home" style="height: 60vh;">
        <div class="hero-content">
            <div class="hero-subtitle">Propriétés Disponibles</div>
            <h1 class="hero-title">Nos <span>Villas</span></h1>
            <p class="hero-description">Découvrez notre sélection de villas luxueuses et choisissez celle qui correspond à vos attentes.</p>
        </div>
    </section>

    <!-- Villas Section -->
    <section class="gallery" style="padding-top: 60px;">
        <div class="container gallery-container">
            <div class="gallery-header">
                <p class="section-subtitle">Résidences de Prestige</p>
                <h2 class="section-title">Nos <span>Propriétés</span> Disponibles</h2>
            </div>
            <div class="gallery-grid">
                <div class="gallery-item" onclick="openTransportPopup('s3')">
                    <img src="/api/placeholder/600/350" alt="Villa S+3">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+3 - KMAR</h3>
                        <p class="gallery-item-subtitle">215m² | 3 chambres</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openTransportPopup('s4')">
                    <img src="/api/placeholder/600/350" alt="Villa S+4">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+4 - YASSER</h3>
                        <p class="gallery-item-subtitle">280m² | 4 chambres</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openTransportPopup('s3b')">
                    <img src="/api/placeholder/600/350" alt="Villa S+3">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+3 - SAMI</h3>
                        <p class="gallery-item-subtitle">220m² | 3 chambres</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openTransportPopup('s4b')">
                    <img src="/api/placeholder/600/350" alt="Villa S+4">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+4 - NARJESS</h3>
                        <p class="gallery-item-subtitle">290m² | 4 chambres</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openTransportPopup('s5')">
                    <img src="/api/placeholder/600/350" alt="Villa S+5">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+5 - YASMINE</h3>
                        <p class="gallery-item-subtitle">350m² | 5 chambres</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openTransportPopup('s3c')">
                    <img src="/api/placeholder/600/350" alt="Villa S+3">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+3 - AMIRA</h3>
                        <p class="gallery-item-subtitle">210m² | 3 chambres</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Villa Popup -->
    <div class="transport-popup" id="transportPopup">
        <div class="transport-popup-content">
            <span class="close-popup" onclick="closeTransportPopup()">&times;</span>
            <h2 id="popupTitle">Détails de la Villa</h2>
            
            <!-- S+3 KMAR -->
            <div id="s3Popup" class="popup-content">
                <div class="transport-details-section">
                    <h3>Caractéristiques</h3>
                    <ul class="transport-details-list">
                        <li><span>Type</span><span>S+3</span></li>
                        <li><span>Surface Totale</span><span>215m²</span></li>
                        <li><span>Sous-sol</span><span>52m²</span></li>
                        <li><span>Jardin</span><span>73m²</span></li>
                        <li><span>Piscine Extérieure</span><span>Oui</span></li>
                        <li><span>Places de Parking</span><span>2</span></li>
                    </ul>
                </div>
                
                <div class="transport-prices-section">
                    <h3>Détails Supplémentaires</h3>
                    <ul class="transport-prices-list">
                        <li><span>Étage</span><span>Rez-de-chaussée + 1</span></li>
                        <li><span>Chambres</span><span>3</span></li>
                        <li><span>Salles de Bain</span><span>2</span></li>
                        <li><span>Cuisine</span><span>Équipée</span></li>
                        <li><span>Prix</span><span>650,000 DT</span></li>
                    </ul>
                </div>

                <img src="/api/placeholder/600/300" alt="Plan Villa S+3" style="width:100%; margin-top:15px; border-radius:5px;">
                
                <a href="reservation-form.php?type=s3&nom=KMAR" class="book-now-btn">
                    <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                </a>
            </div>

            <!-- S+4 YASSER -->
            <div id="s4Popup" class="popup-content">
                <div class="transport-details-section">
                    <h3>Caractéristiques</h3>
                    <ul class="transport-details-list">
                        <li><span>Type</span><span>S+4</span></li>
                        <li><span>Surface Totale</span><span>280m²</span></li>
                        <li><span>Sous-sol</span><span>65m²</span></li>
                        <li><span>Jardin</span><span>90m²</span></li>
                        <li><span>Piscine Extérieure</span><span>Oui</span></li>
                        <li><span>Places de Parking</span><span>3</span></li>
                    </ul>
                </div>
                
                <div class="transport-prices-section">
                    <h3>Détails Supplémentaires</h3>
                    <ul class="transport-prices-list">
                        <li><span>Étage</span><span>Rez-de-chaussée + 2</span></li>
                        <li><span>Chambres</span><span>4</span></li>
                        <li><span>Salles de Bain</span><span>3</span></li>
                        <li><span>Cuisine</span><span>Équipée</span></li>
                        <li><span>Prix</span><span>850,000 DT</span></li>
                    </ul>
                </div>

                <img src="/api/placeholder/600/300" alt="Plan Villa S+4" style="width:100%; margin-top:15px; border-radius:5px;">
                
                <a href="reservation-form.php?type=s4&nom=YASSER" class="book-now-btn">
                    <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                </a>
            </div>

            <!-- S+3 SAMI -->
            <div id="s3bPopup" class="popup-content">
                <div class="transport-details-section">
                    <h3>Caractéristiques</h3>
                    <ul class="transport-details-list">
                        <li><span>Type</span><span>S+3</span></li>
                        <li><span>Surface Totale</span><span>220m²</span></li>
                        <li><span>Sous-sol</span><span>55m²</span></li>
                        <li><span>Jardin</span><span>75m²</span></li>
                        <li><span>Piscine Extérieure</span><span>Oui</span></li>
                        <li><span>Places de Parking</span><span>2</span></li>
                    </ul>
                </div>
                
                <div class="transport-prices-section">
                    <h3>Détails Supplémentaires</h3>
                    <ul class="transport-prices-list">
                        <li><span>Étage</span><span>Rez-de-chaussée + 1</span></li>
                        <li><span>Chambres</span><span>3</span></li>
                        <li><span>Salles de Bain</span><span>2</span></li>
                        <li><span>Cuisine</span><span>Équipée</span></li>
                        <li><span>Prix</span><span>670,000 DT</span></li>
                    </ul>
                </div>

                <img src="/api/placeholder/600/300" alt="Plan Villa S+3" style="width:100%; margin-top:15px; border-radius:5px;">
                
                <a href="reservation-form.php?type=s3&nom=SAMI" class="book-now-btn">
                    <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                </a>
            </div>

            <!-- S+4 NARJESS -->
            <div id="s4bPopup" class="popup-content">
                <div class="transport-details-section">
                    <h3>Caractéristiques</h3>
                    <ul class="transport-details-list">
                        <li><span>Type</span><span>S+4</span></li>
                        <li><span>Surface Totale</span><span>290m²</span></li>
                        <li><span>Sous-sol</span><span>68m²</span></li>
                        <li><span>Jardin</span><span>95m²</span></li>
                        <li><span>Piscine Extérieure</span><span>Oui</span></li>
                        <li><span>Places de Parking</span><span>3</span></li>
                    </ul>
                </div>
                
                <div class="transport-prices-section">
                    <h3>Détails Supplémentaires</h3>
                    <ul class="transport-prices-list">
                        <li><span>Étage</span><span>Rez-de-chaussée + 2</span></li>
                        <li><span>Chambres</span><span>4</span></li>
                        <li><span>Salles de Bain</span><span>3</span></li>
                        <li><span>Cuisine</span><span>Équipée</span></li>
                        <li><span>Prix</span><span>870,000 DT</span></li>
                    </ul>
                </div>

                <img src="/api/placeholder/600/300" alt="Plan Villa S+4" style="width:100%; margin-top:15px; border-radius:5px;">
                
                <a href="reservation-form.php?type=s4&nom=NARJESS" class="book-now-btn">
                    <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                </a>
            </div>

            <!-- S+5 YASMINE -->
            <div id="s5Popup" class="popup-content">
                <div class="transport-details-section">
                    <h3>Caractéristiques</h3>
                    <ul class="transport-details-list">
                        <li><span>Type</span><span>S+5</span></li>
                        <li><span>Surface Totale</span><span>350m²</span></li>
                        <li><span>Sous-sol</span><span>80m²</span></li>
                        <li><span>Jardin</span><span>120m²</span></li>
                        <li><span>Piscine Extérieure</span><span>Oui</span></li>
                        <li><span>Places de Parking</span><span>4</span></li>
                    </ul>
                </div>
                
                <div class="transport-prices-section">
                    <h3>Détails Supplémentaires</h3>
                    <ul class="transport-prices-list">
                        <li><span>Étage</span><span>Rez-de-chaussée + 2</span></li>
                        <li><span>Chambres</span><span>5</span></li>
                        <li><span>Salles de Bain</span><span>4</span></li>
                        <li><span>Cuisine</span><span>Équipée</span></li>
                        <li><span>Prix</span><span>1,050,000 DT</span></li>
                    </ul>
                </div>

                <img src="/api/placeholder/600/300" alt="Plan Villa S+5" style="width:100%; margin-top:15px; border-radius:5px;">
                
                <a href="reservation-form.php?type=s5&nom=YASMINE" class="book-now-btn">
                    <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                </a>
            </div>

            <!-- S+3 AMIRA -->
            <div id="s3cPopup" class="popup-content">
                <div class="transport-details-section">
                    <h3>Caractéristiques</h3>
                    <ul class="transport-details-list">
                        <li><span>Type</span><span>S+3</span></li>
                        <li><span>Surface Totale</span><span>210m²</span></li>
                        <li><span>Sous-sol</span><span>50m²</span></li>
                        <li><span>Jardin</span><span>70m²</span></li>
                        <li><span>Piscine Extérieure</span><span>Oui</span></li>
                        <li><span>Places de Parking</span><span>2</span></li>
                    </ul>
                </div>
                
                <div class="transport-prices-section">
                    <h3>Détails Supplémentaires</h3>
                    <ul class="transport-prices-list">
                        <li><span>Étage</span><span>Rez-de-chaussée + 1</span></li>
                        <li><span>Chambres</span><span>3</span></li>
                        <li><span>Salles de Bain</span><span>2</span></li>
                        <li><span>Cuisine</span><span>Équipée</span></li>
                        <li><span>Prix</span><span>640,000 DT</span></li>
                    </ul>
                </div>

                <img src="/api/placeholder/600/300" alt="Plan Villa S+3" style="width:100%; margin-top:15px; border-radius:5px;">
                
                <a href="reservation-form.php?type=s3&nom=AMIRA" class="book-now-btn">
                    <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-about">
                    <div class="logo">
                        <img src="logo.png" alt="TuniFy Logo">
                        <div class="logo-text">
                            <h1>TuniFy</h1>
                            <p>Village</p>
                        </div>
                    </div>
                    <p>TuniFy Village est l'incarnation du luxe, offrant une expérience résidentielle exclusive avec des équipements haut de gamme et un service exceptionnel dans un cadre magnifique.</p>
                </div>
                
                <div class="footer-links">
                    <h3 class="footer-heading">Liens Rapides</h3>
                    <ul>
                        <li><a href="index.php#home">Accueil</a></li>
                        <li><a href="index.php#about">À Propos</a></li>
                        <li><a href="index.php#gallery">Propriétés</a></li>
                        <li><a href="index.php#features">Services</a></li>
                        <li><a href="reservation.php">Réservation</a></li>
                        <li><a href="index.php#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h3 class="footer-heading">Nos Services</h3>
                    <ul>
                        <li><a href="#">Vente Immobilière</a></li>
                        <li><a href="#">Gestion de Propriété</a></li>
                        <li><a href="#">Design d'Intérieur</a></li>
                        <li><a href="#">Aménagement Paysager</a></li>
                        <li><a href="#">Services de Conciergerie</a></li>
                    </ul>
                </div>
                
                <div class="footer-contact">
                    <h3 class="footer-heading">Informations de Contact</h3>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Boulevard du Luxe, Quartier Doré, Ville</p>
                    <p><i class="fas fa-phone"></i> +216 12 345 678</p>
                    <p><i class="fas fa-envelope"></i> info@tunifyvillage.com</p>
                    <p><i class="fas fa-clock"></i> Lun-Sam: 9:00 - 18:00</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-copyright">
                    &copy; 2025 TuniFy Village. Tous Droits Réservés. Conçu par <a href="#">Kaptin</a>
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
        
        // Villa Popup Functions
        function openTransportPopup(type) {
            const popup = document.getElementById('transportPopup');
            popup.style.display = 'flex';

            // Hide all popup contents first
            document.querySelectorAll('.popup-content').forEach(content => {
                content.style.display = 'none';
            });

            // Show the specific popup content
            if (type === 's3') {
                document.getElementById('s3Popup').style.display = 'block';
                document.getElementById('popupTitle').textContent = 'Villa S+3 - KMAR';
            } else if (type === 's4') {
                document.getElementById('s4Popup').style.display = 'block';
                document.getElementById('popupTitle').textContent = 'Villa S+4 - YASSER';
            } else if (type === 's3b') {
                document.getElementById('s3bPopup').style.display = 'block';
                document.getElementById('popupTitle').textContent = 'Villa S+3 - SAMI';
            } else if (type === 's4b') {
                document.getElementById('s4bPopup').style.display = 'block';
                document.getElementById('popupTitle').textContent = 'Villa S+4 - NARJESS';
            } else if (type === 's5') {
                document.getElementById('s5Popup').style.display = 'block';
                document.getElementById('popupTitle').textContent = 'Villa S+5 - YASMINE';
            } else if (type === 's3c') {
                document.getElementById('s3cPopup').style.display = 'block';
                document.getElementById('popupTitle').textContent = 'Villa S+3 - AMIRA';
            }
        }

        function closeTransportPopup() {
            const popup = document.getElementById('transportPopup');
            popup.classList.add('hiding');
            
            setTimeout(() => {
                popup.style.display = 'none';
                popup.classList.remove('hiding');
            }, 200);
        }

        // Close popup when clicking outside of it
        window.addEventListener('click', function(event) {
            const popup = document.getElementById('transportPopup');
            if (event.target === popup) {
                closeTransportPopup();
            }
        });
    </script>
</body>
</html>