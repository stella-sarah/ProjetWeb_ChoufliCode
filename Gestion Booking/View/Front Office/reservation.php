<?php
// Reservation.php - Page listing available properties

// Inclure les fichiers nécessaires
require_once 'C:/xampp/htdocs/Gestion Booking/config.php';
require_once 'C:/xampp/htdocs/Gestion Booking/Controllor/Photo.php';

// Initialiser la connexion à la base de données
$db = config::getConnexion();

// Initialiser l'objet Photo
$photo = new Photo($db);

// Récupérer les photos pour chaque villa
$photos_kmar = $photo->getPhotosByNom('KMAR');
$photos_yasser = $photo->getPhotosByNom('YASSER');
$photos_sami = $photo->getPhotosByNom('SAMI');
$photos_narjess = $photo->getPhotosByNom('NARJESS');
$photos_yasmine = $photo->getPhotosByNom('YASMINE');
$photos_amira = $photo->getPhotosByNom('AMIRA');

// Récupérer les photos pour les maisons d'hôtes
$photos_dar = $photo->getPhotosByNom('DAR');
$photos_riad = $photo->getPhotosByNom('RIAD');
$photos_villa_hote = $photo->getPhotosByNom('VILLA_HOTE');

// Récupérer les photos pour les hôtels
$photos_palace = $photo->getPhotosByNom('PALACE');
$photos_boutique = $photo->getPhotosByNom('BOUTIQUE');
$photos_resort = $photo->getPhotosByNom('RESORT');

// Fonction pour afficher le carrousel d'images
function displayPhotoCarousel($photos, $default_image = "/api/placeholder/600/350") {
    if (empty($photos)) {
        echo '<img src="' . $default_image . '" alt="Villa">';
    } else {
        echo '<div class="villa-carousel">';
        foreach ($photos as $index => $photo) {
            $active_class = ($index === 0) ? ' active' : '';
            echo '<div class="carousel-item' . $active_class . '">';
            echo '<img src="' . $photo['image_base64'] . '" alt="' . htmlspecialchars($photo['nom']) . '">';
            echo '</div>';
        }
        
        // Ajouter des contrôles de navigation si plus d'une photo
        if (count($photos) > 1) {
            echo '<a class="carousel-prev" onclick="changeSlide(-1)">&#10094;</a>';
            echo '<a class="carousel-next" onclick="changeSlide(1)">&#10095;</a>';
            
            // Indicateurs
            echo '<div class="carousel-indicators">';
            for ($i = 0; $i < count($photos); $i++) {
                $active_class = ($i === 0) ? ' active' : '';
                echo '<span class="carousel-dot' . $active_class . '" onclick="currentSlide(' . ($i+1) . ')"></span>';
            }
            echo '</div>';
        }
        
        echo '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Réservation</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles pour les boutons de réservation */
        .reservation-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .book-now-btn, .visit-btn {
            flex: 1;
            display: block;
            padding: 12px 20px;
            text-align: center;
            text-decoration: none;
            font-weight: 700;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .book-now-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .visit-btn {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }
        
        .book-now-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .visit-btn:hover {
            background-color: rgba(var(--primary-color-rgb), 0.1);
            transform: translateY(-3px);
        }
        
        .book-now-btn span, .visit-btn span {
            display: inline-block;
            transition: transform 0.3s ease;
        }
        
        .book-now-btn:hover span, .visit-btn:hover span {
            animation: bounce 0.5s ease infinite alternate;
        }
        
        @keyframes bounce {
            0% { transform: translateY(0); }
            100% { transform: translateY(-2px); }
        }
        
        @media (max-width: 768px) {
            .reservation-buttons {
                flex-direction: column;
            }
        }

        /* Styles pour le carrousel d'images */
        .villa-carousel {
            position: relative;
            width: 100%;
            max-height: 350px;
            overflow: hidden;
            border-radius: 5px;
        }
        
        .carousel-item {
            display: none;
            width: 100%;
        }
        
        .carousel-item.active {
            display: block;
        }
        
        .carousel-item img {
            width: 100%;
            height: 350px;
            object-fit: cover;
        }
        
        .carousel-prev, .carousel-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            padding: 10px;
            color: white;
            background: rgba(0, 0, 0, 0.5);
            cursor: pointer;
            z-index: 1;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }
        
        .carousel-prev {
            left: 10px;
        }
        
        .carousel-next {
            right: 10px;
        }
        
        .carousel-prev:hover, .carousel-next:hover {
            background: rgba(0, 0, 0, 0.8);
        }
        
        .carousel-indicators {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
        }
        
        .carousel-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .carousel-dot.active, .carousel-dot:hover {
            background: rgba(255, 255, 255, 0.9);
        }
    </style>
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
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="contact-btn">Déconnexion</a>
            <?php else: ?>
                <a href="login.php" class="contact-btn">Connexion</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Reservation Hero Section -->
    <section class="hero" id="home" style="height: 60vh;">
        <div class="hero-content">
            <div class="hero-subtitle">Propriétés Disponibles</div>
            <h1 class="hero-title">Nos <span>Hébergements</span></h1>
            <p class="hero-description">Découvrez notre sélection d'hébergements luxueux et choisissez celui qui correspond à vos attentes.</p>
        </div>
    </section>

    <!-- Villas Section -->
    <section class="gallery" style="padding-top: 60px;">
        <div class="container gallery-container">
            <div class="gallery-header">
                <p class="section-subtitle">Résidences de Prestige</p>
                <h2 class="section-title">Nos <span>Villas</span> Disponibles</h2>
            </div>
            <div class="gallery-grid">
                <div class="gallery-item" onclick="openTransportPopup('s3')">
                    <?php displayPhotoCarousel($photos_kmar); ?>
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+3 - KMAR</h3>
                        <p class="gallery-item-subtitle">215m² | 3 chambres</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openTransportPopup('s4')">
                    <?php displayPhotoCarousel($photos_yasser); ?>
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+4 - YASSER</h3>
                        <p class="gallery-item-subtitle">280m² | 4 chambres</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openTransportPopup('s3b')">
                    <?php displayPhotoCarousel($photos_sami); ?>
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+3 - SAMI</h3>
                        <p class="gallery-item-subtitle">220m² | 3 chambres</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openTransportPopup('s4b')">
                    <?php displayPhotoCarousel($photos_narjess); ?>
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+4 - NARJESS</h3>
                        <p class="gallery-item-subtitle">290m² | 4 chambres</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openTransportPopup('s5')">
                    <?php displayPhotoCarousel($photos_yasmine); ?>
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+5 - YASMINE</h3>
                        <p class="gallery-item-subtitle">350m² | 5 chambres</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openTransportPopup('s3c')">
                    <?php displayPhotoCarousel($photos_amira); ?>
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+3 - AMIRA</h3>
                        <p class="gallery-item-subtitle">210m² | 3 chambres</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Maisons d'hôtes Section -->
    <section class="gallery" style="padding-top: 60px;">
        <div class="container gallery-container">
            <div class="gallery-header">
                <p class="section-subtitle">Séjours Authentiques</p>
                <h2 class="section-title">Nos <span>Maisons d'hôtes</span> Disponibles</h2>
            </div>
            <div class="gallery-grid">
                <div class="gallery-item" onclick="openMaisonPopup('dar')">
                    <?php displayPhotoCarousel($photos_dar); ?>
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Dar Traditionnel</h3>
                        <p class="gallery-item-subtitle">150m² | 2 chambres</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openMaisonPopup('riad')">
                    <?php displayPhotoCarousel($photos_riad); ?>
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Riad Luxueux</h3>
                        <p class="gallery-item-subtitle">220m² | 3 chambres</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openMaisonPopup('villa')">
                    <?php displayPhotoCarousel($photos_villa_hote); ?>
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa d'Hôtes</h3>
                        <p class="gallery-item-subtitle">300m² | 4 chambres</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Hôtels Section -->
    <section class="gallery" style="padding-top: 60px; padding-bottom: 60px;">
        <div class="container gallery-container">
            <div class="gallery-header">
                <p class="section-subtitle">Séjours de Luxe</p>
                <h2 class="section-title">Nos <span>Hôtels</span> Partenaires</h2>
            </div>
            <div class="gallery-grid">
                <div class="gallery-item" onclick="openHotelPopup('palace')">
                    <?php displayPhotoCarousel($photos_palace); ?>
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Hôtel Palace</h3>
                        <p class="gallery-item-subtitle">Suites de luxe | 5*</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openHotelPopup('boutique')">
                    <?php displayPhotoCarousel($photos_boutique); ?>
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Hôtel Boutique</h3>
                        <p class="gallery-item-subtitle">Charme intime | 4*</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openHotelPopup('resort')">
                    <?php displayPhotoCarousel($photos_resort); ?>
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Résort 5*</h3>
                        <p class="gallery-item-subtitle">Plage privée | All inclusive</p>
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

                <div style="margin-top:15px; border-radius:5px; overflow:hidden;">
                    <?php displayPhotoCarousel($photos_kmar, "/api/placeholder/600/300"); ?>
                </div>
                
                <div class="reservation-buttons">
                    <a href="reservation-villa.php?type=s3&nom=KMAR" class="book-now-btn">
                        <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                    </a>
                    <a href="visitevilla.php?type=s3&nom=KMAR" class="visit-btn">
                        <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span> <span>M</span><span>A</span> <span>V</span><span>I</span><span>S</span><span>I</span><span>T</span><span>E</span>
                    </a>
                </div>
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

                <div style="margin-top:15px; border-radius:5px; overflow:hidden;">
                    <?php displayPhotoCarousel($photos_yasser, "/api/placeholder/600/300"); ?>
                </div>
                
                <div class="reservation-buttons">
                    <a href="reservation-villa.php?type=s4&nom=YASSER" class="book-now-btn">
                        <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                    </a>
                    <a href="visitevilla.php?type=s4&nom=YASSER" class="visit-btn">
                        <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span> <span>M</span><span>A</span> <span>V</span><span>I</span><span>S</span><span>I</span><span>T</span><span>E</span>
                    </a>
                </div>
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

                <div style="margin-top:15px; border-radius:5px; overflow:hidden;">
                    <?php displayPhotoCarousel($photos_sami, "/api/placeholder/600/300"); ?>
                </div>
                
                <div class="reservation-buttons">
                    <a href="reservation-villa.php?type=s3&nom=SAMI" class="book-now-btn">
                        <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                    </a>
                    <a href="visitevilla.php?type=s3&nom=SAMI" class="visit-btn">
                        <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span> <span>M</span><span>A</span> <span>V</span><span>I</span><span>S</span><span>I</span><span>T</span><span>E</span>
                    </a>
                </div>
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

                <div style="margin-top:15px; border-radius:5px; overflow:hidden;">
                    <?php displayPhotoCarousel($photos_narjess, "/api/placeholder/600/300"); ?>
                </div>
                
                <div class="reservation-buttons">
                    <a href="reservation-villa.php?type=s4&nom=NARJESS" class="book-now-btn">
                        <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                    </a>
                    <a href="visitevilla.php?type=s4&nom=NARJESS" class="visit-btn">
                        <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span> <span>M</span><span>A</span> <span>V</span><span>I</span><span>S</span><span>I</span><span>T</span><span>E</span>
                    </a>
                </div>
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

                <div style="margin-top:15px; border-radius:5px; overflow:hidden;">
                    <?php displayPhotoCarousel($photos_yasmine, "/api/placeholder/600/300"); ?>
                </div>
                
                <div class="reservation-buttons">
                    <a href="reservation-villa.php?type=s5&nom=YASMINE" class="book-now-btn">
                        <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                    </a>
                    <a href="visitevilla.php?type=s5&nom=YASMINE" class="visit-btn">
                        <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span> <span>M</span><span>A</span> <span>V</span><span>I</span><span>S</span><span>I</span><span>T</span><span>E</span>
                    </a>
                </div>
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

                <div style="margin-top:15px; border-radius:5px; overflow:hidden;">
                    <?php displayPhotoCarousel($photos_amira, "/api/placeholder/600/300"); ?>
                </div>
                
                <div class="reservation-buttons">
                    <a href="reservation-villa.php?type=s3&nom=AMIRA" class="book-now-btn">
                        <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                    </a>
                    <a href="visitevilla.php?type=s3&nom=AMIRA" class="visit-btn">
                        <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span> <span>M</span><span>A</span> <span>V</span><span>I</span><span>S</span><span>I</span><span>T</span><span>E</span>
                    </a>
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

                <div style="margin-top:15px; border-radius:5px; overflow:hidden;">
                    <?php displayPhotoCarousel($photos_dar, "/api/placeholder/600/300"); ?>
                </div>
                
                <a href="reservation-maisonhote.php?type=dar" class="book-now-btn">
                    <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                </a>
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

                <div style="margin-top:15px; border-radius:5px; overflow:hidden;">
                    <?php displayPhotoCarousel($photos_riad, "/api/placeholder/600/300"); ?>
                </div>
                
                <a href="reservation-maisonhote.php?type=riad" class="book-now-btn">
                    <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                </a>
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

                <div style="margin-top:15px; border-radius:5px; overflow:hidden;">
                    <?php displayPhotoCarousel($photos_villa_hote, "/api/placeholder/600/300"); ?>
                </div>
                
                <a href="reservation-maisonhote.php?type=villa" class="book-now-btn">
                    <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                </a>
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

                <div style="margin-top:15px; border-radius:5px; overflow:hidden;">
                    <?php displayPhotoCarousel($photos_palace, "/api/placeholder/600/300"); ?>
                </div>
                
                <a href="reservation-hotel.php?type=palace" class="book-now-btn">
                    <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                </a>
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

                <div style="margin-top:15px; border-radius:5px; overflow:hidden;">
                    <?php displayPhotoCarousel($photos_boutique, "/api/placeholder/600/300"); ?>
                </div>
                
                <a href="reservation-hotel.php?type=boutique" class="book-now-btn">
                    <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                </a>
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

                <div style="margin-top:15px; border-radius:5px; overflow:hidden;">
                    <?php displayPhotoCarousel($photos_resort, "/api/placeholder/600/300"); ?>
                </div>
                
                <a href="reservation-hotel.php?type=resort" class="book-now-btn">
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
        
        // Variables globales pour le carrousel
        let slideIndex = 1;
        let carouselIntervals = {};
        
        // Initialiser les carrousels automatiques
        document.addEventListener('DOMContentLoaded', function() {
            const carousels = document.querySelectorAll('.villa-carousel');
            
            carousels.forEach((carousel, index) => {
                if (carousel.querySelectorAll('.carousel-item').length > 1) {
                    // Démarrer un intervalle pour chaque carrousel
                    carouselIntervals[index] = setInterval(() => {
                        // Ne changer les diapositives que pour ce carrousel spécifique
                        changeSlideForCarousel(index, 1);
                    }, 5000); // Changer toutes les 5 secondes
                }
            });
        });
        
        // Changer la diapositive pour un carrousel spécifique
        function changeSlideForCarousel(carouselIndex, n) {
            const carousel = document.querySelectorAll('.villa-carousel')[carouselIndex];
            const slides = carousel.querySelectorAll('.carousel-item');
            const dots = carousel.querySelectorAll('.carousel-dot');
            
            // Rechercher l'index actuel
            let currentIndex = 0;
            for (let i = 0; i < slides.length; i++) {
                if (slides[i].classList.contains('active')) {
                    currentIndex = i;
                    break;
                }
            }
            
            // Calculer le nouvel index
            let newIndex = currentIndex + n;
            if (newIndex >= slides.length) newIndex = 0;
            if (newIndex < 0) newIndex = slides.length - 1;
            
            // Mettre à jour les classes
            slides[currentIndex].classList.remove('active');
            dots[currentIndex]?.classList.remove('active');
            
            slides[newIndex].classList.add('active');
            dots[newIndex]?.classList.add('active');
        }
        
        // Fonction pour tous les carrousels (utilisée par les boutons)
        function changeSlide(n) {
            // Trouver quel carrousel est actuellement visible
            const visiblePopups = document.querySelectorAll('.popup-content');
            visiblePopups.forEach((popup, index) => {
                if (popup.style.display === 'block') {
                    const carousel = popup.querySelector('.villa-carousel');
                    if (carousel) {
                        const carouselIndex = Array.from(document.querySelectorAll('.villa-carousel')).indexOf(carousel);
                        changeSlideForCarousel(carouselIndex, n);
                    }
                }
            });
        }
        
        // Fonction pour définir une diapositive spécifique
        function currentSlide(n) {
            // Trouver quel carrousel est actuellement visible
            const visiblePopups = document.querySelectorAll('.popup-content');
            visiblePopups.forEach((popup, index) => {
                if (popup.style.display === 'block') {
                    const carousel = popup.querySelector('.villa-carousel');
                    if (carousel) {
                        const slides = carousel.querySelectorAll('.carousel-item');
                        const dots = carousel.querySelectorAll('.carousel-dot');
                        
                        // Désactiver tous
                        for (let i = 0; i < slides.length; i++) {
                            slides[i].classList.remove('active');
                            dots[i].classList.remove('active');
                        }
                        
                        // Activer le sélectionné (n est 1-based)
                        slides[n-1].classList.add('active');
                        dots[n-1].classList.add('active');
                    }
                }
            });
        }
        
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