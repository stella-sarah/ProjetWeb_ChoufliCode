<?php
// Reservation.php - Page listant les propriétés DYNAMIQUEMENT (avec vidéo pour villas)

// --- Inclusion des fichiers ---
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controllor/Photo.php'; // Pour les images
require_once __DIR__ . '/../../Controllor/ProprieteController.php'; // Pour récupérer les propriétés

// Initialiser la connexion et les contrôleurs
try {
    $db = config::getConnexion();
    $photoController = new Photo($db);
    $proprieteController = new ProprieteController($db);
} catch (Exception $e) {
    error_log("Erreur de connexion BD/Contrôleur dans reservation.php: " . $e->getMessage());
    die('<div style="color: red; padding: 20px; border: 1px solid red; margin: 20px;">Erreur critique lors du chargement des données. Veuillez réessayer plus tard ou contacter le support.</div>');
}

// --- Récupération dynamique des propriétés ---
try {
    // Assurez-vous que getAllVillas() récupère la nouvelle colonne 'video_url' si vous l'avez ajoutée
    $villas = $proprieteController->getAllVillas();
    $maisons_hotes = $proprieteController->getAllMaisonsHotes();
    $hotels = $proprieteController->getAllHotels();
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des propriétés: " . $e->getMessage());
    $villas = $maisons_hotes = $hotels = [];
    $page_error = "Erreur lors du chargement des propriétés.";
}
// --- Fin Récupération ---

// Fonction pour afficher l'image principale dans la galerie (INCHANGÉE)
function displayPhotoCarousel($photoController, $photo_nom_associe, $alt_text = "Hébergement", $default_image = "/api/placeholder/600/350/1c1c1c/c9a86c?text=Image+Indisponible") {
    $photos = [];
    if (!empty($photo_nom_associe)) {
        $photos = $photoController->getPhotosByNom($photo_nom_associe);
    }

    if (empty($photos)) {
        echo '<img src="' . htmlspecialchars($default_image) . '" alt="' . htmlspecialchars($alt_text) . '" style="width: 100%; height: 100%; object-fit: cover;">';
    } else {
        $photo = $photos[0];
        if (is_array($photo) && isset($photo['image_base64']) && !empty($photo['image_base64'])) {
            $imageData = $photo['image_base64'];
            if (strpos($imageData, 'data:image') !== 0) {
                // Basic check for image type based on base64 start
                 if (strpos(substr($imageData, 0, 20), 'iVBORw0KGgo') === 0) $mime = 'png';
                 elseif (strpos(substr($imageData, 0, 20), '/9j/') === 0) $mime = 'jpeg';
                 elseif (strpos(substr($imageData, 0, 20), 'R0lGOD') === 0) $mime = 'gif';
                 else $mime = 'jpeg'; // Default
                 $imageData = 'data:image/' . $mime . ';base64,' . $imageData;
            }
            echo '<img src="' . htmlspecialchars($imageData) . '" alt="' . htmlspecialchars($alt_text) . '" style="width: 100%; height: 100%; object-fit: cover;">';
        } else {
             echo '<img src="' . htmlspecialchars($default_image) . '" alt="' . htmlspecialchars($alt_text) . '" style="width: 100%; height: 100%; object-fit: cover;">';
        }
    }
}

// Fonction pour afficher le carrousel photo COMPLET dans le popup (INCHANGÉE - utilisée pour maisons/hôtels)
function displayFullPhotoCarouselInPopup($photoController, $photo_nom_associe, $alt_text = "Hébergement", $default_image = "/api/placeholder/600/350/1c1c1c/c9a86c?text=Image+Indisponible") {
     $photos = [];
     if (!empty($photo_nom_associe)) {
         $photos = $photoController->getPhotosByNom($photo_nom_associe);
     }

     if (empty($photos)) {
        echo '<img src="' . $default_image . '" alt="' . htmlspecialchars($alt_text) . '" style="width: 100%; height: 100%; object-fit: cover;">';
        return;
    }

    $carousel_id = 'carousel-popup-' . preg_replace('/[^a-zA-Z0-9_\-]/', '-', strtolower($alt_text)) . '-' . rand();
    echo '<div class="villa-carousel" id="' . htmlspecialchars($carousel_id) . '">';
    $validPhotoCount = 0;
    foreach ($photos as $index => $photo) {
         if (is_array($photo) && isset($photo['image_base64']) && !empty($photo['image_base64'])) {
             $imageData = $photo['image_base64'];
             if (strpos($imageData, 'data:image') !== 0) {
                 if (strpos(substr($imageData, 0, 20), 'iVBORw0KGgo') === 0) $mime = 'png';
                 elseif (strpos(substr($imageData, 0, 20), '/9j/') === 0) $mime = 'jpeg';
                 elseif (strpos(substr($imageData, 0, 20), 'R0lGOD') === 0) $mime = 'gif';
                 else $mime = 'jpeg';
                 $imageData = 'data:image/' . $mime . ';base64,' . $imageData;
             }
             $active_class = ($validPhotoCount === 0) ? ' active' : '';
             echo '<div class="carousel-item' . $active_class . '">';
             echo '<img src="' . htmlspecialchars($imageData) . '" alt="' . htmlspecialchars($alt_text) . '">';
             echo '</div>';
             $validPhotoCount++;
         }
    }
    if ($validPhotoCount === 0) {
         echo '<img src="' . $default_image . '" alt="' . htmlspecialchars($alt_text) . '" style="width: 100%; height: 100%; object-fit: cover;">';
    }
    elseif ($validPhotoCount > 1) {
        echo '<a class="carousel-prev" onclick="changeSlide(\'' . htmlspecialchars($carousel_id) . '\', -1)">&#10094;</a>';
        echo '<a class="carousel-next" onclick="changeSlide(\'' . htmlspecialchars($carousel_id) . '\', 1)">&#10095;</a>';
        echo '<div class="carousel-indicators">';
        for ($i = 0; $i < $validPhotoCount; $i++) {
            $active_class = ($i === 0) ? ' active' : '';
            echo '<span class="carousel-dot' . $active_class . '" onclick="currentSlide(\'' . htmlspecialchars($carousel_id) . '\', ' . ($i + 1) . ')"></span>';
        }
        echo '</div>';
    }
    echo '</div>';
}


// --- Gestion Session ---
session_start();
$utilisateur_connecte = isset($_SESSION['user_id']);
// --- Fin Gestion Session ---

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Réservation</title>
    <link rel="stylesheet" href="../../style.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles CSS existants */
        .reservation-buttons { display: flex; gap: 15px; margin-top: 20px; }
        .book-now-btn, .visit-btn { flex: 1; display: block; padding: 8px 20px; text-align: center; text-decoration: none; font-weight: 600; border-radius: 5px; transition: all 0.3s ease; border: 1px solid transparent; font-size: 14px; }
        .book-now-btn { background: var(--gold-primary); color: var(--darker-bg); }
        .visit-btn { background: transparent; color: var(--gold-primary); border-color: var(--gold-primary); }
        .book-now-btn:hover { background: var(--gold-light); color: var(--darker-bg); transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .visit-btn:hover { background: rgba(201, 168, 108, 0.1); transform: translateY(-2px); }
        @media (max-width: 768px) { .reservation-buttons { flex-direction: column; } }

        .villa-carousel { position: relative; width: 100%; height: 100%; overflow: hidden; border-radius: 5px; background-color: #333; }
        .carousel-item { display: none; width: 100%; height: 100%; animation: fadeEffect 1s; }
        @keyframes fadeEffect { from {opacity: .4} to {opacity: 1} }
        .carousel-item.active { display: block; }
        .carousel-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .carousel-prev, .carousel-next { position: absolute; top: 50%; transform: translateY(-50%); padding: 10px; color: white; background: rgba(0, 0, 0, 0.4); cursor: pointer; z-index: 1; border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; transition: background 0.3s; user-select: none; font-size: 18px; }
        .carousel-prev { left: 10px; }
        .carousel-next { right: 10px; }
        .carousel-prev:hover, .carousel-next:hover { background: rgba(0, 0, 0, 0.7); }
        .carousel-indicators { position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); display: flex; gap: 6px; z-index: 1; }
        .carousel-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255, 255, 255, 0.5); cursor: pointer; transition: background 0.3s; }
        .carousel-dot.active, .carousel-dot:hover { background: rgba(255, 255, 255, 0.9); }

        .gallery-item { position: relative; height: 350px; overflow: hidden; border-radius: 5px; cursor: pointer; transition: transform 0.5s ease; background-color: var(--dark-bg); }
        .gallery-item::after { content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 60%; background: linear-gradient(to top, rgba(0, 0, 0, 0.85), transparent); transition: opacity 0.3s ease; z-index: 1; pointer-events: none; }
        .gallery-item:hover::after { opacity: 0.95; }
        .gallery-item-content { position: absolute; bottom: 25px; left: 25px; z-index: 2; transition: transform 0.5s ease; color: var(--light-text); }
        .gallery-item:hover .gallery-item-content { transform: translateY(-10px); }
        .gallery-item-title { font-family: 'Cinzel', serif; font-size: 22px; font-weight: 600; margin-bottom: 8px; text-shadow: 1px 1px 3px rgba(0,0,0,0.7); }
        .gallery-item-subtitle { font-family: 'Montserrat', sans-serif; font-size: 13px; font-weight: 400; color: var(--gold-primary); text-transform: uppercase; letter-spacing: 1px; text-shadow: 1px 1px 2px rgba(0,0,0,0.5); }

         .transport-popup { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.85); z-index: 2000; justify-content: center; align-items: center; backdrop-filter: blur(5px); animation: fadeInPopup 0.4s ease-out; }
         @keyframes fadeInPopup { from { opacity: 0; } to { opacity: 1; } }
         .transport-popup.hiding { animation: fadeOutPopup 0.3s ease-in forwards; }
         @keyframes fadeOutPopup { from { opacity: 1; } to { opacity: 0; } }
         .transport-popup-content { background-color: var(--dark-bg); padding: 30px 35px; border-radius: 10px; width: 90%; max-width: 650px; position: relative; border: 1px solid var(--gold-primary); box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6); max-height: 90vh; overflow-y: auto; animation: scaleInPopup 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
         @keyframes scaleInPopup { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
         .close-popup { position: absolute; top: 15px; right: 20px; font-size: 28px; color: var(--gold-light); cursor: pointer; transition: color 0.3s ease, transform 0.3s ease; z-index: 10; }
         .close-popup:hover { color: #fff; transform: rotate(90deg); }
         .transport-popup-content h2 { font-family: 'Cinzel', serif; font-size: 24px; color: var(--gold-primary); margin-bottom: 25px; text-align: center; text-transform: uppercase; letter-spacing: 1px; }
         .transport-details-section, .transport-prices-section { margin-bottom: 25px; }
         .transport-details-section h3, .transport-prices-section h3 { font-family: 'Montserrat', sans-serif; font-size: 16px; color: var(--gold-primary); margin-bottom: 15px; padding-bottom: 8px; border-bottom: 1px solid rgba(201, 168, 108, 0.3); }
         .transport-details-list, .transport-prices-list { list-style: none; padding: 0; }
         .transport-details-list li, .transport-prices-list li { display: flex; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid rgba(248, 245, 235, 0.1); font-size: 14px; }
         .transport-details-list li:last-child, .transport-prices-list li:last-child { border-bottom: none; }
         .transport-details-list li span:first-child, .transport-prices-list li span:first-child { color: rgba(248, 245, 235, 0.8); padding-right: 10px; min-width: 120px; /* Pour alignement */ }
         .transport-details-list li span:last-child, .transport-prices-list li span:last-child { color: var(--light-text); font-weight: 500; text-align: right; }
         .popup-media-container {
            margin-top: 20px;
            margin-bottom: 25px;
            border-radius: 5px;
            overflow: hidden;
            height: 300px; /* Hauteur fixe pour média popup */
            background-color: #222; /* Fond sombre */
            display: flex; /* Pour centrer si besoin */
            justify-content: center;
            align-items: center;
         }
         .popup-media-container video { /* Style spécifique pour la vidéo */
             max-width: 100%;
             max-height: 100%;
             display: block; /* Empêche espace blanc sous la vidéo */
         }
         .popup-description { font-size: 14px; color: rgba(248, 245, 235, 0.8); line-height: 1.6; margin-top: 15px; }
         .no-properties { text-align: center; color: rgba(248, 245, 235, 0.7); padding: 40px 20px; font-style: italic; }

         /* Style pour la vidéo dans la section hero */
        .hero-video-container {
            margin-top: 30px; /* Espace au-dessus de la vidéo */
            max-width: 800px; /* Limite la largeur */
            margin-left: auto;
            margin-right: auto;
            border-radius: 8px;
            overflow: hidden; /* Pour les coins arrondis */
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(201, 168, 108, 0.2); /* Bordure subtile */
        }
        .hero-video-container video {
            width: 100%;
            display: block; /* Enlève l'espace en dessous */
            height: auto; /* Garde le ratio */
        }

    </style>
</head>
<body>
    <header>
        <div class="container nav-container">
             <div class="logo"> <img src="logo.png" alt="TuniFy Logo"> <div class="logo-text"> <h1>TuniFy</h1> <p>Village</p> </div> </div>
             <nav> <ul> <li><a href="index.php#home">Accueil</a></li> <li><a href="index.php#about">À Propos</a></li> <li><a href="index.php#gallery">Galerie</a></li> <li><a href="index.php#features">Services</a></li> <li><a href="reservation.php" class="active">Réservation</a></li> <li><a href="index.php#contact">Contact</a></li> </ul> </nav>
             <?php if($utilisateur_connecte): ?> <a href="logout.php" class="contact-btn">Déconnexion</a> <?php else: ?> <a href="login.php" class="contact-btn">Connexion</a> <?php endif; ?>
        </div>
    </header>

    <section class="hero" id="home" style="min-height: 70vh; height: auto; padding-bottom: 50px; background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.8)), url('/api/placeholder/1600/900/111111/c9a86c?text=TuniFy+Village') center/cover no-repeat;">
        <div class="hero-content" style="padding-top: 100px;"> <div class="hero-subtitle"></div>
            <h1 class="hero-title">Nos <span>Hébergements</span></h1>
            <p class="hero-description">Découvrez notre sélection d'hébergements luxueux et réservez votre séjour ou planifiez une visite.</p>

            <div class="hero-video-container">
                <video id="heroVideo" autoplay muted loop playsinline>
                    <source src="test.mp4" type="video/mp4">
                    Votre navigateur ne supporte pas la lecture de vidéos HTML5.
                </video>
                 </div>
            </div>
    </section>

     <?php if (isset($page_error)): ?>
        <div style="color: #FFA07A; text-align: center; padding: 20px; background: rgba(244, 67, 54, 0.1); border-bottom: 1px solid rgba(244, 67, 54, 0.4);"><?php echo htmlspecialchars($page_error); ?></div>
     <?php endif; ?>

    <section class="gallery" style="padding-top: 60px;">
        <div class="container gallery-container">
            <div class="gallery-header"> <p class="section-subtitle">Résidences de Prestige</p> <h2 class="section-title">Nos <span>Villas</span> Disponibles</h2> </div>
            <div class="gallery-grid">
                <?php if (!empty($villas)): ?>
                    <?php foreach ($villas as $villa): ?>
                        <div class="gallery-item" onclick="openPopup('villaPopup_<?php echo $villa['id_villa']; ?>')">
                            <?php displayPhotoCarousel($photoController, $villa['photo_nom_associe'] ?? null, 'Villa ' . htmlspecialchars($villa['nom_villa'])); ?>
                            <div class="gallery-item-content">
                                <h3 class="gallery-item-title">Villa <?php echo htmlspecialchars($villa['nom_villa']); ?></h3>
                                <p class="gallery-item-subtitle"><?php echo htmlspecialchars($villa['type_villa']); ?> | <?php echo $villa['nb_chambres'] ?? '?'; ?> chambres</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-properties">Aucune villa disponible pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="gallery" style="padding-top: 60px;">
        <div class="container gallery-container">
            <div class="gallery-header"> <p class="section-subtitle">Séjours Authentiques</p> <h2 class="section-title">Nos <span>Maisons d'hôtes</span></h2> </div>
            <div class="gallery-grid">
                 <?php if (!empty($maisons_hotes)): ?>
                    <?php foreach ($maisons_hotes as $maison): ?>
                        <div class="gallery-item" onclick="openPopup('maisonPopup_<?php echo $maison['id_maison_hote']; ?>')">
                             <?php displayPhotoCarousel($photoController, $maison['photo_nom_associe'] ?? null, htmlspecialchars($maison['nom_maison'])); ?>
                            <div class="gallery-item-content">
                                <h3 class="gallery-item-title"><?php echo htmlspecialchars($maison['nom_maison']); ?></h3>
                                <p class="gallery-item-subtitle"><?php echo $maison['nb_chambres'] ?? '?'; ?> chambres | Capacité: <?php echo $maison['capacite_personnes'] ?? '?'; ?> pers.</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-properties">Aucune maison d'hôte disponible pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="gallery" style="padding-top: 60px; padding-bottom: 60px;">
        <div class="container gallery-container">
            <div class="gallery-header"> <p class="section-subtitle">Séjours de Luxe</p> <h2 class="section-title">Nos <span>Hôtels</span> Partenaires</h2> </div>
            <div class="gallery-grid">
                 <?php if (!empty($hotels)): ?>
                    <?php foreach ($hotels as $hotel): ?>
                        <div class="gallery-item" onclick="openPopup('hotelPopup_<?php echo $hotel['id_hotel']; ?>')">
                             <?php displayPhotoCarousel($photoController, $hotel['photo_nom_associe'] ?? null, htmlspecialchars($hotel['nom_hotel'])); ?>
                            <div class="gallery-item-content">
                                <h3 class="gallery-item-title"><?php echo htmlspecialchars($hotel['nom_hotel']); ?></h3>
                                <p class="gallery-item-subtitle"><?php echo $hotel['classement_etoiles'] ? $hotel['classement_etoiles'] . '*' : ''; ?> | <?php echo htmlspecialchars($hotel['type_hotel']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-properties">Aucun hôtel disponible pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>


    <?php foreach ($villas as $villa): ?>
    <div class="transport-popup" id="villaPopup_<?php echo $villa['id_villa']; ?>">
        <div class="transport-popup-content">
            <span class="close-popup" onclick="closePopup('villaPopup_<?php echo $villa['id_villa']; ?>')">&times;</span>
            <h2>Villa <?php echo htmlspecialchars($villa['nom_villa']); ?></h2>

            <div class="popup-media-container">
                <?php if (!empty($villa['video_url'])): // Vérifie si l'URL de la vidéo existe ?>
                    <video width="100%" style="max-height: 300px; display: block;" controls>
                        <source src="<?php echo htmlspecialchars($villa['video_url']); ?>" type="video/mp4">
                        Votre navigateur ne supporte pas la lecture de vidéos HTML5.
                        <a href="<?php echo htmlspecialchars($villa['video_url']); ?>">Lien vers la vidéo</a>
                    </video>
                <?php else: // Sinon, affiche le carrousel photo comme avant ?>
                    <?php displayFullPhotoCarouselInPopup($photoController, $villa['photo_nom_associe'] ?? null, 'Villa ' . htmlspecialchars($villa['nom_villa'])); ?>
                <?php endif; ?>
            </div>

            <div class="transport-details-section"><h3>Caractéristiques</h3>
                <ul class="transport-details-list">
                    <li><span>Type</span><span><?php echo htmlspecialchars($villa['type_villa']); ?></span></li>
                    <li><span>Nom</span><span><?php echo htmlspecialchars($villa['nom_villa']); ?></span></li>
                    <?php if($villa['surface_m2']): ?><li><span>Surface</span><span><?php echo $villa['surface_m2']; ?> m²</span></li><?php endif; ?>
                    <?php if($villa['nb_chambres']): ?><li><span>Chambres</span><span><?php echo $villa['nb_chambres']; ?></span></li><?php endif; ?>
                    <?php if($villa['nb_salles_bain']): ?><li><span>Salles de bain</span><span><?php echo $villa['nb_salles_bain']; ?></span></li><?php endif; ?>
                    <?php if($villa['jardin_m2']): ?><li><span>Jardin</span><span><?php echo $villa['jardin_m2']; ?> m²</span></li><?php endif; ?>
                    <li><span>Piscine</span><span><?php echo $villa['piscine'] ? 'Oui' : 'Non'; ?></span></li>
                    <?php if($villa['parking']): ?><li><span>Parking</span><span><?php echo $villa['parking']; ?> places</span></li><?php endif; ?>
                </ul>
            </div>
            <?php if($villa['prix_indicatif']): ?>
            <div class="transport-prices-section"><h3>Prix</h3>
                <ul class="transport-prices-list">
                    <li><span>Prix Indicatif</span><span><?php echo number_format($villa['prix_indicatif'], 0, ',', ' ').' DT'; ?></span></li>
                </ul>
            </div>
            <?php endif; ?>
            <?php if($villa['description']): ?>
                <div class="popup-description">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($villa['description'])); ?></p>
                </div>
            <?php endif; ?>
            <div class="reservation-buttons">
                <a href="reservation-villa.php?nom=<?php echo urlencode($villa['nom_villa']); ?>" class="book-now-btn">Demande d'Achat</a>
                <a href="visitevilla.php?type=<?php echo urlencode($villa['type_villa']); ?>&nom=<?php echo urlencode($villa['nom_villa']); ?>" class="visit-btn">Réserver Visite</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php foreach ($maisons_hotes as $maison): ?>
    <div class="transport-popup" id="maisonPopup_<?php echo $maison['id_maison_hote']; ?>">
         <div class="transport-popup-content">
            <span class="close-popup" onclick="closePopup('maisonPopup_<?php echo $maison['id_maison_hote']; ?>')">&times;</span>
            <h2><?php echo htmlspecialchars($maison['nom_maison']); ?></h2>
             <div class="popup-media-container">
                <?php displayFullPhotoCarouselInPopup($photoController, $maison['photo_nom_associe'] ?? null, htmlspecialchars($maison['nom_maison'])); ?>
            </div>
            <div class="transport-details-section"><h3>Caractéristiques</h3>
                <ul class="transport-details-list">
                    <li><span>Type</span><span><?php echo htmlspecialchars($maison['type_maison']); ?></span></li>
                     <?php if($maison['surface_m2']): ?><li><span>Surface</span><span><?php echo $maison['surface_m2']; ?> m²</span></li><?php endif; ?>
                    <?php if($maison['nb_chambres']): ?><li><span>Chambres</span><span><?php echo $maison['nb_chambres']; ?></span></li><?php endif; ?>
                    <?php if($maison['capacite_personnes']): ?><li><span>Capacité</span><span><?php echo $maison['capacite_personnes']; ?> personnes</span></li><?php endif; ?>
                    <li><span>Piscine</span><span><?php echo $maison['piscine'] ? 'Oui' : 'Non'; ?></span></li>
                    <li><span>Petit Déj. Inclus</span><span><?php echo $maison['petit_dejeuner_inclus'] ? 'Oui' : 'Non'; ?></span></li>
                </ul>
            </div>
            <?php if($maison['prix_nuit']): ?>
            <div class="transport-prices-section"><h3>Prix</h3>
                <ul class="transport-prices-list">
                    <li><span>Prix / Nuit</span><span><?php echo number_format($maison['prix_nuit'], 0, ',', ' ').' DT'; ?></span></li>
                </ul>
            </div>
             <?php endif; ?>
             <?php if($maison['description']): ?>
                <div class="popup-description">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($maison['description'])); ?></p>
                </div>
            <?php endif; ?>
            <div class="reservation-buttons">
                 <a href="reservation-maisonhote.php?nom=<?php echo urlencode($maison['nom_maison']); ?>" class="book-now-btn">Réserver Séjour</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php foreach ($hotels as $hotel): ?>
    <div class="transport-popup" id="hotelPopup_<?php echo $hotel['id_hotel']; ?>">
         <div class="transport-popup-content">
            <span class="close-popup" onclick="closePopup('hotelPopup_<?php echo $hotel['id_hotel']; ?>')">&times;</span>
            <h2><?php echo htmlspecialchars($hotel['nom_hotel']); ?></h2>
             <div class="popup-media-container">
                <?php displayFullPhotoCarouselInPopup($photoController, $hotel['photo_nom_associe'] ?? null, htmlspecialchars($hotel['nom_hotel'])); ?>
            </div>
            <div class="transport-details-section"><h3>Caractéristiques</h3>
                <ul class="transport-details-list">
                    <li><span>Type</span><span><?php echo htmlspecialchars($hotel['type_hotel']); ?></span></li>
                    <?php if($hotel['classement_etoiles']): ?><li><span>Classement</span><span><?php echo $hotel['classement_etoiles']; ?> *</span></li><?php endif; ?>
                    <?php if($hotel['services_cles']): ?><li><span>Services Clés</span><span><?php echo htmlspecialchars($hotel['services_cles']); ?></span></li><?php endif; ?>
                     <?php if($hotel['adresse']): ?><li><span>Adresse</span><span><?php echo htmlspecialchars($hotel['adresse']); ?></span></li><?php endif; ?>
                </ul>
            </div>
             <?php if($hotel['prix_nuit_apd']): ?>
            <div class="transport-prices-section"><h3>Prix</h3>
                <ul class="transport-prices-list">
                     <li><span>Prix / Nuit (àpd)</span><span><?php echo number_format($hotel['prix_nuit_apd'], 0, ',', ' ').' DT'; ?></span></li>
                </ul>
            </div>
             <?php endif; ?>
             <?php if($hotel['description']): ?>
                <div class="popup-description">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($hotel['description'])); ?></p>
                </div>
            <?php endif; ?>
            <div class="reservation-buttons">
                 <a href="reservation-hotel.php?nom=<?php echo urlencode($hotel['nom_hotel']); ?>" class="book-now-btn">Réserver Séjour</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>


    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                 <div class="footer-about"> <div class="logo"> <img src="logo.png" alt="TuniFy Logo"> <div class="logo-text"> <h1>TuniFy</h1> <p>Village</p> </div> </div> <p>TuniFy Village est l'incarnation du luxe...</p> </div>
                 <div class="footer-links"> <h3 class="footer-heading">Liens Rapides</h3> <ul> <li><a href="index.php#home">Accueil</a></li> <li><a href="index.php#about">À Propos</a></li> <li><a href="index.php#gallery">Propriétés</a></li> <li><a href="index.php#features">Services</a></li> <li><a href="reservation.php">Réservation</a></li> <li><a href="index.php#contact">Contact</a></li> </ul> </div>
                 <div class="footer-links"> <h3 class="footer-heading">Nos Services</h3> <ul> <li><a href="#">Vente Immobilière</a></li> <li><a href="#">Gestion de Propriété</a></li> <li><a href="#">Design d'Intérieur</a></li> <li><a href="#">Aménagement Paysager</a></li> <li><a href="#">Services de Conciergerie</a></li> </ul> </div>
                 <div class="footer-contact"> <h3 class="footer-heading">Informations de Contact</h3> <p><i class="fas fa-map-marker-alt"></i> 123 Boulevard du Luxe, Quartier Doré, Ville</p> <p><i class="fas fa-phone"></i> +216 12 345 678</p> <p><i class="fas fa-envelope"></i> info@tunifyvillage.com</p> <p><i class="fas fa-clock"></i> Lun-Sam: 9:00 - 18:00</p> </div>
             </div>
            <div class="footer-bottom"> <div class="footer-copyright"> &copy; <?php echo date("Y"); ?> TuniFy Village. Tous Droits Réservés. Conçu par <a href="#">Kaptin</a> </div> </div>
        </div>
    </footer>

    <script>
        // --- Carrousel Logic (INCHANGÉ) ---
        let slideIndexes = {};
        function initCarousel(carouselId) {
             if (slideIndexes[carouselId] === undefined) {
                 slideIndexes[carouselId] = 1;
                 showSlides(carouselId, 1);
             }
         }
        function changeSlide(carouselId, n) {
             if (slideIndexes[carouselId] === undefined) initCarousel(carouselId);
             showSlides(carouselId, slideIndexes[carouselId] += n);
         }
        function currentSlide(carouselId, n) {
              if (slideIndexes[carouselId] === undefined) initCarousel(carouselId);
             showSlides(carouselId, slideIndexes[carouselId] = n);
         }
        function showSlides(carouselId, n) {
             let i;
             let carouselElement = document.getElementById(carouselId);
             if (!carouselElement) { console.error("Carousel element not found:", carouselId); return; }
             let slides = carouselElement.getElementsByClassName("carousel-item");
             let dots = carouselElement.getElementsByClassName("carousel-dot");
             if (slides.length === 0) return;
             if (n > slides.length) { slideIndexes[carouselId] = 1 }
             else if (n < 1) { slideIndexes[carouselId] = slides.length }
             else { slideIndexes[carouselId] = n; }
             for (i = 0; i < slides.length; i++) { slides[i].style.display = "none"; slides[i].classList.remove("active"); }
              if (dots.length > 0) { for (i = 0; i < dots.length; i++) { dots[i].className = dots[i].className.replace(" active", ""); } }
             slides[slideIndexes[carouselId] - 1].style.display = "block";
             slides[slideIndexes[carouselId] - 1].classList.add("active");
              if (dots.length > 0) { dots[slideIndexes[carouselId] - 1].className += " active"; }
         }

        // --- Popup Logic (MODIFIÉ pour arrêter la vidéo) ---
        function openPopup(popupId) {
            const popup = document.getElementById(popupId);
            if (popup) {
                popup.style.display = 'flex';
                const carouselInPopup = popup.querySelector('.villa-carousel');
                if (carouselInPopup && carouselInPopup.id) { initCarousel(carouselInPopup.id); }
                const videoInPopup = popup.querySelector('video');
                if (videoInPopup) { videoInPopup.currentTime = 0; }
            } else {
                console.error("Popup with ID '" + popupId + "' not found.");
            }
        }

        function closePopup(popupId) {
            const popup = document.getElementById(popupId);
            if (popup) {
                const video = popup.querySelector('video');
                if (video) { video.pause(); video.currentTime = 0; }
                popup.classList.add('hiding');
                setTimeout(() => {
                    popup.style.display = 'none';
                    popup.classList.remove('hiding');
                }, 300);
            }
        }

        // --- Event Listeners (DOM Ready) ---
        document.addEventListener('DOMContentLoaded', function() {
            // Fermer popup en cliquant à l'extérieur
            window.addEventListener('click', function(event) {
                if (event.target.classList.contains('transport-popup')) {
                    closePopup(event.target.id);
                }
            });

            // Scroll Header Effect
            const header = document.querySelector('header');
            if (header) {
                 const handleScroll = () => { header.classList.toggle('scrolled', window.scrollY > 50); };
                 window.addEventListener('scroll', handleScroll);
                 handleScroll();
            }

            // --- *** NOUVEAU : Intersection Observer pour la vidéo Hero *** ---
            const heroVideo = document.getElementById('heroVideo');

            if (heroVideo) {
                const observerOptions = {
                    root: null, // Observe par rapport au viewport
                    rootMargin: '0px',
                    threshold: 0.5 // Déclenche quand 50% de la vidéo est visible/invisible
                };

                const observerCallback = (entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            // La vidéo est visible
                            // Tenter de jouer la vidéo (peut échouer si l'utilisateur n'a pas interagi)
                            heroVideo.play().catch(error => {
                                // Gérer l'erreur si autoplay est bloqué (optionnel)
                                console.log("Autoplay de la vidéo hero bloqué, interaction utilisateur requise.");
                            });
                        } else {
                            // La vidéo n'est plus visible
                            heroVideo.pause();
                        }
                    });
                };

                const videoObserver = new IntersectionObserver(observerCallback, observerOptions);
                videoObserver.observe(heroVideo); // Commence à observer la vidéo
            }
            // --- *** FIN Intersection Observer *** ---

        });

    </script>
</body>
</html>
