
<?php
// Front office main page
session_start(); // Pour gérer l'utilisateur connecté
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Habitat de Luxe</title>
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
                    <li><a href="#home">Accueil</a></li>
                    <li><a href="#about">À Propos</a></li>
                    <li><a href="#gallery">Galerie</a></li>
                    <li><a href="#features">Services</a></li>
                    <li class="dropdown">
                        <a href="javascript:void(0)" class="dropbtn">Réservation</a>
                        <div class="dropdown-content">
                            <a href="reservation-villa.php">Villas</a>
                            <a href="reservation-maisonhote.php">Maisons d'hôtes</a>
                            <a href="reservation-hotel.php">Hôtels</a>
                        </div>
                    </li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </nav>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="contact-btn">Déconnexion</a>
            <?php else: ?>
                <a href="login.php" class="contact-btn">Connexion</a>
            <?php endif; ?>
        </div>
    </header>


    <!-- Gallery Section -->
    <section class="gallery" id="gallery">
        <div class="container gallery-container">
            <div class="gallery-header">
                <p class="section-subtitle">Voyage Visuel</p>
                <h2 class="section-title">Explorez Nos <span>Spectaculaires</span> Propriétés</h2>
            </div>
            <div class="gallery-grid">
                <!-- Villas -->
                <div class="gallery-item" onclick="openTransportPopup('s3')">
                    <img src="/api/placeholder/600/350" alt="Villa S+3">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+3</h3>
                        <p class="gallery-item-subtitle">Résidence Executive</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openTransportPopup('s4')">
                    <img src="/api/placeholder/600/350" alt="Villa S+4">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+4</h3>
                        <p class="gallery-item-subtitle">Confort Moderne</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openTransportPopup('s5')">
                    <img src="/api/placeholder/600/350" alt="Villa S+5">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+5</h3>
                        <p class="gallery-item-subtitle">Luxe Ultime</p>
                    </div>
                </div>
                
                <!-- Maisons d'hôtes -->
                <div class="gallery-item" onclick="openMaisonPopup('dar')">
                    <img src="/api/placeholder/600/350" alt="Dar Traditionnel">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Dar Traditionnel</h3>
                        <p class="gallery-item-subtitle">Charme Authentique</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openMaisonPopup('riad')">
                    <img src="/api/placeholder/600/350" alt="Riad Luxueux">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Riad Luxueux</h3>
                        <p class="gallery-item-subtitle">Élégance Marocaine</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openMaisonPopup('villa')">
                    <img src="/api/placeholder/600/350" alt="Villa d'Hôtes">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa d'Hôtes</h3>
                        <p class="gallery-item-subtitle">Confort Privé</p>
                    </div>
                </div>
                
                <!-- Hôtels -->
                <div class="gallery-item" onclick="openHotelPopup('palace')">
                    <img src="/api/placeholder/600/350" alt="Hôtel Palace">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Hôtel Palace</h3>
                        <p class="gallery-item-subtitle">Luxe Royal</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openHotelPopup('boutique')">
                    <img src="/api/placeholder/600/350" alt="Hôtel Boutique">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Hôtel Boutique</h3>
                        <p class="gallery-item-subtitle">Charme Intime</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openHotelPopup('resort')">
                    <img src="/api/placeholder/600/350" alt="Résort 5*">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Résort 5*</h3>
                        <p class="gallery-item-subtitle">Détente Totale</p>
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
                
                <div class="popup-buttons">
                    <button onclick="window.location.href='reservation-villa.php?type=s3&nom=KMAR'" class="contact-btn">Réserver</button>
                    <button onclick="closeTransportPopup()" class="contact-btn">Annuler</button>
                </div>
            </div>

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
                
                <div class="popup-buttons">
                    <button onclick="window.location.href='reservation-villa.php?type=s4&nom=KMAR'" class="contact-btn">Réserver</button>
                    <button onclick="closeTransportPopup()" class="contact-btn">Annuler</button>
                </div>
            </div>

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
                
                <div class="popup-buttons">
                    <button onclick="window.location.href='reservation-villa.php?type=s5&nom=KMAR'" class="contact-btn">Réserver</button>
                    <button onclick="closeTransportPopup()" class="contact-btn">Annuler</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Maison d'hôte Popup -->
    <div class="transport-popup" id="maisonPopup">
        <div class="transport-popup-content">
            <span class="close-popup" onclick="closeMaisonPopup()">&times;</span>
            <h2 id="maisonPopupTitle">Détails de la Maison d'Hôtes</h2>
            
            <div id="darPopup" class="popup-content">
                <div class="transport-details-section">
                    <h3>Caractéristiques</h3>
                    <ul class="transport-details-list">
                        <li><span>Type</span><span>Dar Traditionnel</span></li>
                        <li><span>Surface</span><span>150m²</span></li>
                        <li><span>Chambres</span><span>2</span></li>
                        <li><span>Salles de Bain</span><span>1</span></li>
                        <li><span>Terrasse</span><span>Oui</span></li>
                        <li><span>Jardin</span><span>50m²</span></li>
                    </ul>
                </div>
                
                <div class="transport-prices-section">
                    <h3>Détails Supplémentaires</h3>
                    <ul class="transport-prices-list">
                        <li><span>Capacité</span><span>4 personnes</span></li>
                        <li><span>Cuisine</span><span>Équipée</span></li>
                        <li><span>Climatisation</span><span>Oui</span></li>
                        <li><span>WiFi</span><span>Inclus</span></li>
                        <li><span>Prix/nuit</span><span>350 DT</span></li>
                    </ul>
                </div>

                <img src="/api/placeholder/600/300" alt="Dar Traditionnel" style="width:100%; margin-top:15px; border-radius:5px;">
                
                <div class="popup-buttons">
                    <button onclick="window.location.href='reservation-maisonhote.php?type=dar'" class="contact-btn">Réserver</button>
                    <button onclick="closeMaisonPopup()" class="contact-btn">Annuler</button>
                </div>
            </div>

            <div id="riadPopup" class="popup-content">
                <div class="transport-details-section">
                    <h3>Caractéristiques</h3>
                    <ul class="transport-details-list">
                        <li><span>Type</span><span>Riad Luxueux</span></li>
                        <li><span>Surface</span><span>220m²</span></li>
                        <li><span>Chambres</span><span>3</span></li>
                        <li><span>Salles de Bain</span><span>2</span></li>
                        <li><span>Patio</span><span>Oui</span></li>
                        <li><span>Piscine</span><span>Petite piscine</span></li>
                    </ul>
                </div>
                
                <div class="transport-prices-section">
                    <h3>Détails Supplémentaires</h3>
                    <ul class="transport-prices-list">
                        <li><span>Capacité</span><span>6 personnes</span></li>
                        <li><span>Petit-déjeuner</span><span>Inclus</span></li>
                        <li><span>Service ménage</span><span>Inclus</span></li>
                        <li><span>Spa</span><span>Sur demande</span></li>
                        <li><span>Prix/nuit</span><span>550 DT</span></li>
                    </ul>
                </div>

                <img src="/api/placeholder/600/300" alt="Riad Luxueux" style="width:100%; margin-top:15px; border-radius:5px;">
                
                <div class="popup-buttons">
                    <button onclick="window.location.href='reservation-maisonhote.php?type=riad'" class="contact-btn">Réserver</button>
                    <button onclick="closeMaisonPopup()" class="contact-btn">Annuler</button>
                </div>
            </div>

            <div id="villaPopup" class="popup-content">
                <div class="transport-details-section">
                    <h3>Caractéristiques</h3>
                    <ul class="transport-details-list">
                        <li><span>Type</span><span>Villa d'Hôtes</span></li>
                        <li><span>Surface</span><span>300m²</span></li>
                        <li><span>Chambres</span><span>4</span></li>
                        <li><span>Salles de Bain</span><span>3</span></li>
                        <li><span>Jardin</span><span>100m²</span></li>
                        <li><span>Piscine</span><span>Privée</span></li>
                    </ul>
                </div>
                
                <div class="transport-prices-section">
                    <h3>Détails Supplémentaires</h3>
                    <ul class="transport-prices-list">
                        <li><span>Capacité</span><span>8 personnes</span></li>
                        <li><span>Cuisine extérieure</span><span>Oui</span></li>
                        <li><span>Parking</span><span>3 voitures</span></li>
                        <li><span>Service conciergerie</span><span>24/7</span></li>
                        <li><span>Prix/nuit</span><span>750 DT</span></li>
                    </ul>
                </div>

                <img src="/api/placeholder/600/300" alt="Villa d'Hôtes" style="width:100%; margin-top:15px; border-radius:5px;">
                
                <div class="popup-buttons">
                    <button onclick="window.location.href='reservation-maisonhote.php?type=villa'" class="contact-btn">Réserver</button>
                    <button onclick="closeMaisonPopup()" class="contact-btn">Annuler</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hotel Popup -->
    <div class="transport-popup" id="hotelPopup">
        <div class="transport-popup-content">
            <span class="close-popup" onclick="closeHotelPopup()">&times;</span>
            <h2 id="hotelPopupTitle">Détails de l'Hôtel</h2>
            
            <div id="palacePopup" class="popup-content">
                <div class="transport-details-section">
                    <h3>Caractéristiques</h3>
                    <ul class="transport-details-list">
                        <li><span>Type</span><span>Hôtel Palace 5*</span></li>
                        <li><span>Chambres</span><span>Suites Deluxe/Executive</span></li>
                        <li><span>Restaurants</span><span>3</span></li>
                        <li><span>Piscine</span><span>Intérieure & extérieure</span></li>
                        <li><span>Spa</span><span>Complet</span></li>
                        <li><span>Service</span><span>24/7</span></li>
                    </ul>
                </div>
                
                <div class="transport-prices-section">
                    <h3>Détails Supplémentaires</h3>
                    <ul class="transport-prices-list">
                        <li><span>Petit-déjeuner</span><span>Buffet inclus</span></li>
                        <li><span>Room service</span><span>Disponible</span></li>
                        <li><span>Navette</span><span>Aéroport gratuit</span></li>
                        <li><span>Vue</span><span>Panoramique</span></li>
                        <li><span>Prix/nuit</span><span>À partir de 800 DT</span></li>
                    </ul>
                </div>

                <img src="/api/placeholder/600/300" alt="Hôtel Palace" style="width:100%; margin-top:15px; border-radius:5px;">
                
                <div class="popup-buttons">
                    <button onclick="window.location.href='reservation-hotel.php?type=palace'" class="contact-btn">Réserver</button>
                    <button onclick="closeHotelPopup()" class="contact-btn">Annuler</button>
                </div>
            </div>

            <div id="boutiquePopup" class="popup-content">
                <div class="transport-details-section">
                    <h3>Caractéristiques</h3>
                    <ul class="transport-details-list">
                        <li><span>Type</span><span>Hôtel Boutique 4*</span></li>
                        <li><span>Chambres</span><span>Standards/Supérieures</span></li>
                        <li><span>Restaurant</span><span>1</span></li>
                        <li><span>Bar</span><span>Lounge</span></li>
                        <li><span>Terrasse</span><span>Panoramique</span></li>
                        <li><span>Service</span><span>Conciergerie</span></li>
                    </ul>
                </div>
                
                <div class="transport-prices-section">
                    <h3>Détails Supplémentaires</h3>
                    <ul class="transport-prices-list">
                        <li><span>Petit-déjeuner</span><span>Buffet inclus</span></li>
                        <li><span>Navette</span><span>Sur demande</span></li>
                        <li><span>Design</span><span>Unique</span></li>
                        <li><span>Ambiance</span><span>Intime</span></li>
                        <li><span>Prix/nuit</span><span>À partir de 450 DT</span></li>
                    </ul>
                </div>

                <img src="/api/placeholder/600/300" alt="Hôtel Boutique" style="width:100%; margin-top:15px; border-radius:5px;">
                
                <div class="popup-buttons">
                    <button onclick="window.location.href='reservation-hotel.php?type=boutique'" class="contact-btn">Réserver</button>
                    <button onclick="closeHotelPopup()" class="contact-btn">Annuler</button>
                </div>
            </div>

            <div id="resortPopup" class="popup-content">
                <div class="transport-details-section">
                    <h3>Caractéristiques</h3>
                    <ul class="transport-details-list">
                        <li><span>Type</span><span>Résort 5*</span></li>
                        <li><span>Hébergement</span><span>Bungalows/Villas</span></li>
                        <li><span>Restaurants</span><span>4</span></li>
                        <li><span>Piscines</span><span>2</span></li>
                        <li><span>Plage</span><span>Privée</span></li>
                        <li><span>Activités</span><span>Nautiques</span></li>
                    </ul>
                </div>
                
                <div class="transport-prices-section">
                    <h3>Détails Supplémentaires</h3>
                    <ul class="transport-prices-list">
                        <li><span>All-inclusive</span><span>Option disponible</span></li>
                        <li><span>Club enfants</span><span>Oui</span></li>
                        <li><span>Spa</span><span>Complet</span></li>
                        <li><span>Sports</span><span>Équipements</span></li>
                        <li><span>Prix/nuit</span><span>À partir de 650 DT</span></li>
                    </ul>
                </div>

                <img src="/api/placeholder/600/300" alt="Résort 5*" style="width:100%; margin-top:15px; border-radius:5px;">
                
                <div class="popup-buttons">
                    <button onclick="window.location.href='reservation-hotel.php?type=resort'" class="contact-btn">Réserver</button>
                    <button onclick="closeHotelPopup()" class="contact-btn">Annuler</button>
                </div>
            </div>
        </div>
    </div>


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
            document.querySelectorAll('#transportPopup .popup-content').forEach(content => {
                content.style.display = 'none';
            });

            // Show the specific popup content
            if (type === 's3') {
                document.getElementById('s3Popup').style.display = 'block';
                document.getElementById('popupTitle').textContent = 'Villa S+3 - Détails';
            } else if (type === 's4') {
                document.getElementById('s4Popup').style.display = 'block';
                document.getElementById('popupTitle').textContent = 'Villa S+4 - Détails';
            } else if (type === 's5') {
                document.getElementById('s5Popup').style.display = 'block';
                document.getElementById('popupTitle').textContent = 'Villa S+5 - Détails';
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

        // Maison d'hôte Popup Functions
        function openMaisonPopup(type) {
            const popup = document.getElementById('maisonPopup');
            popup.style.display = 'flex';

            // Hide all popup contents first
            document.querySelectorAll('#maisonPopup .popup-content').forEach(content => {
                content.style.display = 'none';
            });

            // Show the specific popup content
            if (type === 'dar') {
                document.getElementById('darPopup').style.display = 'block';
                document.getElementById('maisonPopupTitle').textContent = 'Dar Traditionnel - Détails';
            } else if (type === 'riad') {
                document.getElementById('riadPopup').style.display = 'block';
                document.getElementById('maisonPopupTitle').textContent = 'Riad Luxueux - Détails';
            } else if (type === 'villa') {
                document.getElementById('villaPopup').style.display = 'block';
                document.getElementById('maisonPopupTitle').textContent = 'Villa d\'Hôtes - Détails';
            }
        }

        function closeMaisonPopup() {
            const popup = document.getElementById('maisonPopup');
            popup.classList.add('hiding');
            
            setTimeout(() => {
                popup.style.display = 'none';
                popup.classList.remove('hiding');
            }, 200);
        }

        // Hotel Popup Functions
        function openHotelPopup(type) {
            const popup = document.getElementById('hotelPopup');
            popup.style.display = 'flex';

            // Hide all popup contents first
            document.querySelectorAll('#hotelPopup .popup-content').forEach(content => {
                content.style.display = 'none';
            });

            // Show the specific popup content
            if (type === 'palace') {
                document.getElementById('palacePopup').style.display = 'block';
                document.getElementById('hotelPopupTitle').textContent = 'Hôtel Palace - Détails';
            } else if (type === 'boutique') {
                document.getElementById('boutiquePopup').style.display = 'block';
                document.getElementById('hotelPopupTitle').textContent = 'Hôtel Boutique - Détails';
            } else if (type === 'resort') {
                document.getElementById('resortPopup').style.display = 'block';
                document.getElementById('hotelPopupTitle').textContent = 'Résort 5* - Détails';
            }
        }

        function closeHotelPopup() {
            const popup = document.getElementById('hotelPopup');
            popup.classList.add('hiding');
            
            setTimeout(() => {
                popup.style.display = 'none';
                popup.classList.remove('hiding');
            }, 200);
        }

        // Close popup when clicking outside of it
        window.addEventListener('click', function(event) {
            const popups = ['transportPopup', 'maisonPopup', 'hotelPopup'];
            popups.forEach(popupId => {
                const popup = document.getElementById(popupId);
                if (event.target === popup) {
                    if (popupId === 'transportPopup') closeTransportPopup();
                    if (popupId === 'maisonPopup') closeMaisonPopup();
                    if (popupId === 'hotelPopup') closeHotelPopup();
                }
            });
        });
    </script>
</body>
</html>