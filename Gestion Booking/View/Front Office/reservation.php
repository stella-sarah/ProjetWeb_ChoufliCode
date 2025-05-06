<?php
// Reservation.php - Page listant les propriétés DYNAMIQUEMENT (avec vidéo pour villas)

// --- Inclusion des fichiers ---
// Ensure these paths are correct for your server setup
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controllor/Photo.php';
require_once __DIR__ . '/../../Controllor/ProprieteController.php';

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
$villas = $maisons_hotes = $hotels = [];
$page_error = '';
try {
    $villas = $proprieteController->getAllVillas();
    $maisons_hotes = $proprieteController->getAllMaisonsHotes();
    $hotels = $proprieteController->getAllHotels();
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des propriétés: " . $e->getMessage());
    $page_error = "Erreur lors du chargement des propriétés.";
}
// --- Fin Récupération ---

// Function to display the main image in the gallery
function displayPhotoCarousel($photoController, $photo_nom_associe, $alt_text = "Hébergement", $default_image_placeholder = "https://placehold.co/600x350/1c1c1c/c9a86c?text=Image+Indisponible") {
    $photos = [];
    if (!empty($photo_nom_associe)) {
        try {
            $photos = $photoController->getPhotosByNom($photo_nom_associe);
        } catch (Exception $e) {
             error_log("Error fetching photos by name '$photo_nom_associe': " . $e->getMessage());
             $photos = [];
        }
    }
    $error_placeholder = "https://placehold.co/600x350/cccccc/ffffff?text=Error+Loading";
    if (empty($photos)) {
        echo '<img src="' . htmlspecialchars($default_image_placeholder) . '" alt="' . htmlspecialchars($alt_text) . '" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src=\'' . $error_placeholder . '\';">';
    } else {
        $photo = $photos[0];
        if (is_array($photo) && isset($photo['image_base64']) && !empty($photo['image_base64'])) {
            $imageData = $photo['image_base64'];
            if (strpos($imageData, 'data:image') !== 0) {
                 if (strpos(substr($imageData, 0, 20), 'iVBORw0KGgo') === 0) $mime = 'png';
                 elseif (strpos(substr($imageData, 0, 20), '/9j/') === 0) $mime = 'jpeg';
                 elseif (strpos(substr($imageData, 0, 20), 'R0lGOD') === 0) $mime = 'gif';
                 else $mime = 'jpeg';
                 $imageData = 'data:image/' . $mime . ';base64,' . $imageData;
            }
            echo '<img src="' . htmlspecialchars($imageData) . '" alt="' . htmlspecialchars($alt_text) . '" style="width: 100%; height: 100%; object-fit: cover;">';
        } else {
             echo '<img src="' . htmlspecialchars($default_image_placeholder) . '" alt="' . htmlspecialchars($alt_text) . '" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src=\'' . $error_placeholder . '\';">';
        }
    }
}

// Function to display the full photo carousel in the popup
function displayFullPhotoCarouselInPopup($photoController, $photo_nom_associe, $alt_text = "Hébergement", $default_image_placeholder = "https://placehold.co/600x350/1c1c1c/c9a86c?text=Image+Indisponible") {
     $photos = [];
     if (!empty($photo_nom_associe)) {
        try {
            $photos = $photoController->getPhotosByNom($photo_nom_associe);
        } catch (Exception $e) {
             error_log("Error fetching photos for popup '$photo_nom_associe': " . $e->getMessage());
             $photos = [];
        }
     }
     $error_placeholder = "https://placehold.co/600x350/cccccc/ffffff?text=Error+Loading";
     if (empty($photos)) {
        echo '<img src="' . htmlspecialchars($default_image_placeholder) . '" alt="' . htmlspecialchars($alt_text) . '" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src=\'' . $error_placeholder . '\';">';
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
             echo '<img src="' . htmlspecialchars($imageData) . '" alt="' . htmlspecialchars($alt_text) . ' ' . ($validPhotoCount + 1) . '" onerror="this.onerror=null; this.src=\'' . $error_placeholder . '\';">';
             echo '</div>';
             $validPhotoCount++;
         }
    }
    if ($validPhotoCount === 0) {
         echo '<img src="' . htmlspecialchars($default_image_placeholder) . '" alt="' . htmlspecialchars($alt_text) . '" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src=\'' . $error_placeholder . '\';">';
    }
    elseif ($validPhotoCount > 1) {
        echo '<button type="button" class="carousel-prev" aria-label="Previous slide" onclick="changeSlide(\'' . htmlspecialchars($carousel_id) . '\', -1)">❮</button>';
        echo '<button type="button" class="carousel-next" aria-label="Next slide" onclick="changeSlide(\'' . htmlspecialchars($carousel_id) . '\', 1)">❯</button>';
        echo '<div class="carousel-indicators">';
        for ($i = 0; $i < $validPhotoCount; $i++) {
            $active_class = ($i === 0) ? ' active' : '';
            echo '<button type="button" class="carousel-dot' . $active_class . '" aria-label="Go to slide ' . ($i + 1) . '" onclick="currentSlide(\'' . htmlspecialchars($carousel_id) . '\', ' . ($i + 1) . ')"></button>';
        }
        echo '</div>';
    }
    echo '</div>';
}

// --- Gestion Session ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
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
        /* Styles from style.css will be inherited */
/* In your style.css file */

/* Navigation */
header {
    position: fixed;
    width: 100%;
    z-index: 100;
    transition: background-color 0.3s ease;
    padding: 15px 0; /* Adjust overall header padding as needed */
}

header.scrolled {
    background-color: rgba(28, 28, 28, 0.95);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
}

.container { /* Ensure your main container settings are appropriate */
    width: 100%;
    max-width: 1400px; /* Or your preferred max-width */
    margin: 0 auto;
    padding: 0 20px; /* Horizontal padding for content within the container */
}

.nav-container {
    display: flex;         /* Enables flexbox layout */
    align-items: center;   /* Vertically aligns all children (logo, nav, button) to the center */
    width: 100%;           /* Ensures it uses the full width available from .container */
}

.logo {
    display: flex;
    align-items: center; /* Vertically aligns image and text within the logo div */
    flex-shrink: 0;      /* Prevents the logo from shrinking if space is tight */
}

.logo img {
    height: 50px;        /* Adjust as needed */
    margin-right: 10px;  /* Space between logo image and logo text */
}

.logo-text {
    display: flex;
    flex-direction: column; /* Stacks h1 and p vertically */
    justify-content: center; /* Centers the text block if it has extra height */
}

.logo-text h1 {
    font-family: 'Cinzel', serif;
    font-weight: 700;
    font-size: 22px;
    color: var(--gold-primary);
    margin: 0;
    line-height: 1.1; /* Adjust for tight vertical spacing */
    letter-spacing: 1px;
}

.logo-text p {
    font-size: 11px;
    font-weight: 300;
    color: var(--gold-light);
    letter-spacing: 3px;
    text-transform: uppercase;
    margin: 0;
    line-height: 1.1; /* Adjust for tight vertical spacing */
}

nav {
    margin-left: 30px;   /* Space between logo and navigation links */
    /* flex-grow: 1; /* Alternative: if you want nav to fill space and center its items */
    /* display: flex; */
    /* justify-content: center; */
}

nav ul {
    display: flex;
    list-style: none;
    gap: 30px;           /* Space between navigation items */
    align-items: center; /* Ensures nav items themselves are aligned if they have different heights */
    margin: 0;
    padding: 0;
}

nav ul li a {
    color: var(--light-text);
    text-decoration: none;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 2px;
    transition: color 0.3s ease;
    font-weight: 400;
    padding: 10px 0; /* Adds some vertical padding to help with visual text alignment */
}

nav ul li a:hover,
nav ul li a.active {
    color: var(--gold-primary);
}

.contact-btn {
    background-color: transparent;
    border: 1px solid var(--gold-primary);
    color: var(--gold-primary);
    padding: 10px 25px; /* Adjust padding for visual text alignment with nav links */
    font-family: 'Montserrat', sans-serif;
    font-weight: 500;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    flex-shrink: 0;          /* Prevents the button from shrinking */
    margin-left: auto;       /* THIS IS THE KEY: Pushes the button to the far right */
    display: inline-flex;    /* Allows align-items to center text within the button's padding */
    align-items: center;     /* Vertically centers text within the button's padding */
    height: fit-content;     /* Ensures button height is determined by its content + padding */
}

.contact-btn:hover {
    background-color: var(--gold-primary);
    color: var(--darker-bg);
}

/* Responsive styles for smaller screens (from your existing CSS) */
@media screen and (max-width: 768px) {
    .nav-container {
        flex-direction: column; /* Stack items vertically on small screens */
        gap: 20px;
        align-items: flex-start; /* Or 'center' depending on desired mobile look */
    }

    nav {
        margin-left: 0; /* Reset margin for column layout */
        width: 100%; /* Allow nav to take full width on mobile */
    }

    nav ul {
        flex-direction: column; /* Stack nav links vertically */
        gap: 15px;
        text-align: center; /* Center nav links text */
        width: 100%;
    }

    .contact-btn {
        margin-left: 0; /* Reset margin for column layout */
        width: auto; /* Or width: 100%; if you want full-width button on mobile */
        align-self: center; /* Center button on mobile if nav-container is align-items:flex-start */
    }
}
        /* --- >>> ADD PADDING TO BODY FOR FIXED HEADER <<< --- */
        body {
            /* Adjust this value based on your actual fixed header height */
            /* Start with ~100px and adjust */
            padding-top: 10px; /* <<< ADJUST THIS VALUE AS NEEDED */
        }
        /* --- >>> --- */
        /* --- Styles for Image Carousel INSIDE POPUP --- */
        .villa-carousel {
            position: relative;
            width: 100%;
            height: 100%; /* Fill the media container */
            overflow: hidden;
            border-radius: 5px; /* Match container */
            background-color: #000; /* Black background */
            display: flex; /* Center image if needed */
            align-items: center; /* Center image vertically */
            justify-content: center; /* Center image horizontally */
        }
        .carousel-item {
            display: none; /* Hide inactive slides */
            width: 100%;
            height: 100%;
            text-align: center; /* Center image horizontally */
            animation: fadeEffect 1s; /* Keep fade effect */
        }
        .carousel-item.active {
            display: flex; /* Use flex to help center */
            align-items: center;
            justify-content: center;
        }
        .carousel-item img {
            display: block;    /* Remove extra space */
            width: 100%;       /* Make image fill width */
            height: 100%;      /* Make image fill height */
            object-fit: cover; /* Fill container, crop if necessary */
          
        }
        @keyframes fadeEffect { from {opacity: .4} to {opacity: 1} } /* Keep fade effect */

        /* Keep existing styles for arrows/dots inside popup */
        .carousel-prev, .carousel-next { position: absolute; top: 50%; transform: translateY(-50%); padding: 10px; color: white; background: rgba(0, 0, 0, 0.4); cursor: pointer; z-index: 1; border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; transition: background 0.3s; user-select: none; font-size: 18px; border: none; }
        .carousel-prev { left: 10px; }
        .carousel-next { right: 10px; }
        .carousel-prev:hover, .carousel-next:hover { background: rgba(0, 0, 0, 0.7); }
        .carousel-indicators { position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); display: flex; gap: 6px; z-index: 1; }
        .carousel-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255, 255, 255, 0.5); cursor: pointer; transition: background 0.3s; border: none; padding: 0;}
        .carousel-dot.active, .carousel-dot:hover { background: rgba(255, 255, 255, 0.9); }
        /* --- End Popup Carousel Styles --- */

        /* Keep other styles like .popup-media-container, .transport-popup etc. */
        .popup-media-container {
            margin-top: 20px;
            margin-bottom: 25px;
            border-radius: 5px;
            overflow: hidden;
            height: 300px; /* Keep fixed height for the container */
            background-color: #222;
            display: flex;
            justify-content: center;
            align-items: center;
         }
         /* ... rest of your styles ... */

        /* --- YouTube Video Carousel Styles --- */
        .youtube-carousel-container {
            position: relative;
            max-width: 800px;
            width: 90%;
            /* Reduced top margin as body padding handles header offset */
            /* Keep bottom margin for spacing below */
            margin: 200px auto 60px auto; /* Top=40px, R/L=auto, Bottom=60px */ /* <<< ADJUST 40px FOR SPACE BETWEEN HEADER AREA AND CAROUSEL */
            overflow: hidden; /* IMPORTANT */
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(201, 168, 108, 0.1);
            background-color: var(--dark-bg); /* Fallback */
        }

        .youtube-carousel-slides {
            display: flex; /* IMPORTANT */
            transition: transform 0.5s ease-in-out;
            width: 100%;
        }

        .youtube-slide {
            flex: 0 0 100%; /* IMPORTANT */
            box-sizing: border-box;
            background-color: #000; /* Black bg for iframes */
        }

        .youtube-slide iframe {
            display: block;
            width: 100%;
            aspect-ratio: 16 / 9;
            height: auto;
            border: none;
        }

        /* Navigation Arrows Styles */
        .yt-carousel-prev,
        .yt-carousel-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            padding: 0;
            color: white;
            background: rgba(0, 0, 0, 0.4);
            cursor: pointer;
            z-index: 1;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
            user-select: none;
            font-size: 20px;
            border: none;
            line-height: 1;
        }
        .yt-carousel-prev:focus,
        .yt-carousel-next:focus {
            outline: 2px solid var(--gold-light);
            outline-offset: 2px;
        }
        .yt-carousel-prev { left: 15px; }
        .yt-carousel-next { right: 15px; }
        .yt-carousel-prev:hover,
        .yt-carousel-next:hover {
            background: rgba(201, 168, 108, 0.7);
            color: var(--dark-bg);
        }

        /* Indicator Dots Styles */
        .yt-carousel-dots {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 1;
        }
        .yt-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
            border: none;
            padding: 0;
        }
        .yt-dot:focus {
            outline: 2px solid var(--gold-light);
            outline-offset: 2px;
        }
        .yt-dot.active {
            background: var(--gold-primary);
            transform: scale(1.2);
        }
        .yt-dot:hover {
             background: rgba(255, 255, 255, 0.9);
             transform: scale(1.2);
        }

        /* Responsive adjustment */
        @media (max-width: 768px) {
            body {
                /* Adjust body padding for smaller screens if header height changes */
                padding-top: 80px; /* Example smaller padding */
            }
            .yt-carousel-prev,
            .yt-carousel-next { width: 35px; height: 35px; font-size: 18px; }
            .yt-dot { width: 8px; height: 8px; }
            .yt-carousel-dots { bottom: 10px; }
            .youtube-carousel-container {
                 /* Adjust carousel margin for smaller screens if needed */
                 margin-top: 30px;
                 margin-bottom: 40px;
            }
        }

        /* Other styles from style.css (like .gallery-item, popups etc.) */

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

    <div class="youtube-carousel-container">
        <div class="youtube-carousel-slides">
            <div class="youtube-slide">
                <iframe src="https://www.youtube.com/embed/Jisnk4wMLqo" title="YouTube video player 1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
            <div class="youtube-slide">
                 <iframe src="https://www.youtube.com/embed/MtRvNwWacEU" title="YouTube video player 2" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
            <div class="youtube-slide">
                 <iframe src="https://www.youtube.com/embed/lOC8mp6Ujbo" title="YouTube video player 3" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
            <div class="youtube-slide">
                 <iframe src="https://www.youtube.com/embed/P59BATNWaHs" title="YouTube video player 4" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
        </div>
        <button type="button" class="yt-carousel-prev" aria-label="Previous video">❮</button>
        <button type="button" class="yt-carousel-next" aria-label="Next video">❯</button>
        <div class="yt-carousel-dots"></div>
    </div>
    <?php if (!empty($page_error)): ?>
        <div style="color: #FFA07A; text-align: center; padding: 20px; background: rgba(244, 67, 54, 0.1); border-bottom: 1px solid rgba(244, 67, 54, 0.4); max-width: 1100px; margin: 20px auto;"><?php echo htmlspecialchars($page_error); ?></div>
     <?php endif; ?>

    <section class="gallery" style="padding-top: 60px;">
        <div class="container gallery-container">
            <div class="gallery-header"> <p class="section-subtitle">Résidences de Prestige</p> <h2 class="section-title">Nos <span>Villas</span> Disponibles</h2> </div>
            <div class="gallery-grid">
                <?php if (!empty($villas)): ?>
                    <?php foreach ($villas as $villa): ?>
                        <?php if (isset($villa['id_villa'])): ?>
                            <div class="gallery-item" onclick="openPopup('villaPopup_<?php echo htmlspecialchars($villa['id_villa']); ?>')">
                                <?php displayPhotoCarousel($photoController, $villa['photo_nom_associe'] ?? null, 'Villa ' . htmlspecialchars($villa['nom_villa'] ?? '')); ?>
                                <div class="gallery-item-content">
                                    <h3 class="gallery-item-title">Villa <?php echo htmlspecialchars($villa['nom_villa'] ?? 'N/A'); ?></h3>
                                    <p class="gallery-item-subtitle"><?php echo htmlspecialchars($villa['type_villa'] ?? ''); ?> | <?php echo $villa['nb_chambres'] ?? '?'; ?> chambres</p>
                                </div>
                            </div>
                        <?php endif; ?>
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
                         <?php if (isset($maison['id_maison_hote'])): ?>
                            <div class="gallery-item" onclick="openPopup('maisonPopup_<?php echo htmlspecialchars($maison['id_maison_hote']); ?>')">
                                 <?php displayPhotoCarousel($photoController, $maison['photo_nom_associe'] ?? null, htmlspecialchars($maison['nom_maison'] ?? '')); ?>
                                <div class="gallery-item-content">
                                    <h3 class="gallery-item-title"><?php echo htmlspecialchars($maison['nom_maison'] ?? 'N/A'); ?></h3>
                                    <p class="gallery-item-subtitle"><?php echo $maison['nb_chambres'] ?? '?'; ?> chambres | Capacité: <?php echo $maison['capacite_personnes'] ?? '?'; ?> pers.</p>
                                </div>
                            </div>
                        <?php endif; ?>
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
                         <?php if (isset($hotel['id_hotel'])): ?>
                            <div class="gallery-item" onclick="openPopup('hotelPopup_<?php echo htmlspecialchars($hotel['id_hotel']); ?>')">
                                 <?php displayPhotoCarousel($photoController, $hotel['photo_nom_associe'] ?? null, htmlspecialchars($hotel['nom_hotel'] ?? '')); ?>
                                <div class="gallery-item-content">
                                    <h3 class="gallery-item-title"><?php echo htmlspecialchars($hotel['nom_hotel'] ?? 'N/A'); ?></h3>
                                    <p class="gallery-item-subtitle"><?php echo isset($hotel['classement_etoiles']) ? $hotel['classement_etoiles'] . '*' : ''; ?> | <?php echo htmlspecialchars($hotel['type_hotel'] ?? ''); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-properties">Aucun hôtel disponible pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>


    <?php if (!empty($villas)): ?>
        <?php foreach ($villas as $villa): ?>
            <?php if (isset($villa['id_villa'])): ?>
                <div class="transport-popup" id="villaPopup_<?php echo htmlspecialchars($villa['id_villa']); ?>">
                    <div class="transport-popup-content">
                        <button type="button" class="close-popup" aria-label="Close popup" onclick="closePopup('villaPopup_<?php echo htmlspecialchars($villa['id_villa']); ?>')">×</button>
                        <h2>Villa <?php echo htmlspecialchars($villa['nom_villa'] ?? 'N/A'); ?></h2>
                        <div class="popup-media-container">
                            <?php if (!empty($villa['video_url'])): ?>
                                <video width="100%" style="max-height: 300px; display: block;" controls>
                                    <source src="<?php echo htmlspecialchars($villa['video_url']); ?>" type="video/mp4">
                                    Votre navigateur ne supporte pas la lecture de vidéos HTML5.
                                    <a href="<?php echo htmlspecialchars($villa['video_url']); ?>">Lien vers la vidéo</a>
                                </video>
                            <?php else: ?>
                                <?php displayFullPhotoCarouselInPopup($photoController, $villa['photo_nom_associe'] ?? null, 'Villa ' . htmlspecialchars($villa['nom_villa'] ?? '')); ?>
                            <?php endif; ?>
                        </div>
                        <div class="transport-details-section"><h3>Caractéristiques</h3>
                            <ul class="transport-details-list">
                                <li><span>Type</span><span><?php echo htmlspecialchars($villa['type_villa'] ?? 'N/A'); ?></span></li>
                                <li><span>Nom</span><span><?php echo htmlspecialchars($villa['nom_villa'] ?? 'N/A'); ?></span></li>
                                <?php if(isset($villa['surface_m2']) && $villa['surface_m2']): ?><li><span>Surface</span><span><?php echo htmlspecialchars($villa['surface_m2']); ?> m²</span></li><?php endif; ?>
                                <?php if(isset($villa['nb_chambres']) && $villa['nb_chambres']): ?><li><span>Chambres</span><span><?php echo htmlspecialchars($villa['nb_chambres']); ?></span></li><?php endif; ?>
                                <?php if(isset($villa['nb_salles_bain']) && $villa['nb_salles_bain']): ?><li><span>Salles de bain</span><span><?php echo htmlspecialchars($villa['nb_salles_bain']); ?></span></li><?php endif; ?>
                                <?php if(isset($villa['jardin_m2']) && $villa['jardin_m2']): ?><li><span>Jardin</span><span><?php echo htmlspecialchars($villa['jardin_m2']); ?> m²</span></li><?php endif; ?>
                                <li><span>Piscine</span><span><?php echo isset($villa['piscine']) && $villa['piscine'] ? 'Oui' : 'Non'; ?></span></li>
                                <?php if(isset($villa['parking']) && $villa['parking']): ?><li><span>Parking</span><span><?php echo htmlspecialchars($villa['parking']); ?> places</span></li><?php endif; ?>
                            </ul>
                        </div>
                        <?php if(isset($villa['prix_indicatif']) && $villa['prix_indicatif']): ?>
                        <div class="transport-prices-section"><h3>Prix</h3>
                            <ul class="transport-prices-list">
                                <li><span>Prix Indicatif</span><span><?php echo number_format($villa['prix_indicatif'], 0, ',', ' ').' DT'; ?></span></li>
                            </ul>
                        </div>
                        <?php endif; ?>
                        <?php if(isset($villa['description']) && !empty($villa['description'])): ?>
                            <div class="popup-description">
                                <h3>Description</h3>
                                <p><?php echo nl2br(htmlspecialchars($villa['description'])); ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="reservation-buttons">
                            <a href="reservation-villa.php?nom=<?php echo urlencode($villa['nom_villa'] ?? ''); ?>" class="book-now-btn">Demande d'Achat</a>
                            <a href="visitevilla.php?type=<?php echo urlencode($villa['type_villa'] ?? ''); ?>&nom=<?php echo urlencode($villa['nom_villa'] ?? ''); ?>" class="book-now-btn">Réserver Visite</a>
                            <a href="recherchevisitecin.php" class="book-now-btn" style="background-color: var(--gold-dark); margin-top: 50px;">Rechercher Mes Visites</a>
                            </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($maisons_hotes)): ?>
        <?php foreach ($maisons_hotes as $maison): ?>
             <?php if (isset($maison['id_maison_hote'])): ?>
                <div class="transport-popup" id="maisonPopup_<?php echo htmlspecialchars($maison['id_maison_hote']); ?>">
                     <div class="transport-popup-content">
                        <button type="button" class="close-popup" aria-label="Close popup" onclick="closePopup('maisonPopup_<?php echo htmlspecialchars($maison['id_maison_hote']); ?>')">×</button>
                        <h2><?php echo htmlspecialchars($maison['nom_maison'] ?? 'N/A'); ?></h2>
                         <div class="popup-media-container">
                            <?php displayFullPhotoCarouselInPopup($photoController, $maison['photo_nom_associe'] ?? null, htmlspecialchars($maison['nom_maison'] ?? '')); ?>
                        </div>
                        <div class="transport-details-section"><h3>Caractéristiques</h3>
                            <ul class="transport-details-list">
                                <li><span>Type</span><span><?php echo htmlspecialchars($maison['type_maison'] ?? 'N/A'); ?></span></li>
                                 <?php if(isset($maison['surface_m2']) && $maison['surface_m2']): ?><li><span>Surface</span><span><?php echo htmlspecialchars($maison['surface_m2']); ?> m²</span></li><?php endif; ?>
                                <?php if(isset($maison['nb_chambres']) && $maison['nb_chambres']): ?><li><span>Chambres</span><span><?php echo htmlspecialchars($maison['nb_chambres']); ?></span></li><?php endif; ?>
                                <?php if(isset($maison['capacite_personnes']) && $maison['capacite_personnes']): ?><li><span>Capacité</span><span><?php echo htmlspecialchars($maison['capacite_personnes']); ?> personnes</span></li><?php endif; ?>
                                <li><span>Piscine</span><span><?php echo isset($maison['piscine']) && $maison['piscine'] ? 'Oui' : 'Non'; ?></span></li>
                                <li><span>Petit Déj. Inclus</span><span><?php echo isset($maison['petit_dejeuner_inclus']) && $maison['petit_dejeuner_inclus'] ? 'Oui' : 'Non'; ?></span></li>
                            </ul>
                        </div>
                        <?php if(isset($maison['prix_nuit']) && $maison['prix_nuit']): ?>
                        <div class="transport-prices-section"><h3>Prix</h3>
                            <ul class="transport-prices-list">
                                <li><span>Prix / Nuit</span><span><?php echo number_format($maison['prix_nuit'], 0, ',', ' ').' DT'; ?></span></li>
                            </ul>
                        </div>
                         <?php endif; ?>
                         <?php if(isset($maison['description']) && !empty($maison['description'])): ?>
                            <div class="popup-description">
                                <h3>Description</h3>
                                <p><?php echo nl2br(htmlspecialchars($maison['description'])); ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="reservation-buttons">
                             <a href="reservation-maisonhote.php?nom=<?php echo urlencode($maison['nom_maison'] ?? ''); ?>" class="book-now-btn">Réserver Séjour</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($hotels)): ?>
        <?php foreach ($hotels as $hotel): ?>
             <?php if (isset($hotel['id_hotel'])): ?>
                <div class="transport-popup" id="hotelPopup_<?php echo htmlspecialchars($hotel['id_hotel']); ?>">
                     <div class="transport-popup-content">
                        <button type="button" class="close-popup" aria-label="Close popup" onclick="closePopup('hotelPopup_<?php echo htmlspecialchars($hotel['id_hotel']); ?>')">×</button>
                        <h2><?php echo htmlspecialchars($hotel['nom_hotel'] ?? 'N/A'); ?></h2>
                         <div class="popup-media-container">
                            <?php displayFullPhotoCarouselInPopup($photoController, $hotel['photo_nom_associe'] ?? null, htmlspecialchars($hotel['nom_hotel'] ?? '')); ?>
                        </div>
                        <div class="transport-details-section"><h3>Caractéristiques</h3>
                            <ul class="transport-details-list">
                                <li><span>Type</span><span><?php echo htmlspecialchars($hotel['type_hotel'] ?? 'N/A'); ?></span></li>
                                <?php if(isset($hotel['classement_etoiles']) && $hotel['classement_etoiles']): ?><li><span>Classement</span><span><?php echo htmlspecialchars($hotel['classement_etoiles']); ?> *</span></li><?php endif; ?>
                                <?php if(isset($hotel['services_cles']) && !empty($hotel['services_cles'])): ?><li><span>Services Clés</span><span><?php echo htmlspecialchars($hotel['services_cles']); ?></span></li><?php endif; ?>
                                 <?php if(isset($hotel['adresse']) && !empty($hotel['adresse'])): ?><li><span>Adresse</span><span><?php echo htmlspecialchars($hotel['adresse']); ?></span></li><?php endif; ?>
                            </ul>
                        </div>
                         <?php if(isset($hotel['prix_nuit_apd']) && $hotel['prix_nuit_apd']): ?>
                        <div class="transport-prices-section"><h3>Prix</h3>
                            <ul class="transport-prices-list">
                                 <li><span>Prix / Nuit (àpd)</span><span><?php echo number_format($hotel['prix_nuit_apd'], 0, ',', ' ').' DT'; ?></span></li>
                            </ul>
                        </div>
                         <?php endif; ?>
                         <?php if(isset($hotel['description']) && !empty($hotel['description'])): ?>
                            <div class="popup-description">
                                <h3>Description</h3>
                                <p><?php echo nl2br(htmlspecialchars($hotel['description'])); ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="reservation-buttons">
                             <a href="reservation-hotel.php?nom=<?php echo urlencode($hotel['nom_hotel'] ?? ''); ?>" class="book-now-btn">Réserver Séjour</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>


    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                 <div class="footer-about"> <div class="logo"> <img src="logo.png" alt="TuniFy Logo"> <div class="logo-text"> <h1>TuniFy</h1> <p>Village</p> </div> </div> <p>TuniFy Village est l'incarnation du luxe...</p> </div>
                 <div class="footer-links"> <h3 class="footer-heading">Liens Rapides</h3> <ul> <li><a href="index.php#home">Accueil</a></li> <li><a href="index.php#about">À Propos</a></li> <li><a href="index.php#gallery">Propriétés</a></li> <li><a href="index.php#features">Services</a></li> <li><a href="reservation.php">Réservation</a></li> <li><a href="index.php#contact">Contact</a></li> </ul> </div>
                 <div class="footer-links"> <h3 class="footer-heading">Nos Services</h3> <ul> <li><a href="#">Vente Immobilière</a></li> <li><a href="#">Gestion de Propriété</a></li> <li><a href="#">Design d'Intérieur</a></li> <li><a href="#">Aménagement Paysager</a></li> <li><a href="#">Services de Conciergerie</a></li> </ul> </div>
                 <div class="footer-contact"> <h3 class="footer-heading">Informations de Contact</h3> <p><i class="fas fa-map-marker-alt"></i> 123 Boulevard du Luxe, Quartier Doré, Ville</p> <p><i class="fas fa-phone"></i> +216 12 345 678</p> <p><i class="fas fa-envelope"></i> info@tunifyvillage.com</p> <p><i class="fas fa-clock"></i> Lun-Sam: 9:00 - 18:00</p> </div>
             </div>
            <div class="footer-bottom"> <div class="footer-copyright"> © <?php echo date("Y"); ?> TuniFy Village. Tous Droits Réservés. Conçu par <a href="#">Kaptin</a> </div> </div>
        </div>
    </footer>

    <script>
        // --- Carrousel Logic for Popups ---
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
              if (dots.length > 0) { for (i = 0; i < dots.length; i++) { dots[i].classList.remove("active"); } }
             slides[slideIndexes[carouselId] - 1].style.display = "block";
             slides[slideIndexes[carouselId] - 1].classList.add("active");
              if (dots.length > 0) { dots[slideIndexes[carouselId] - 1].classList.add("active"); }
         }

        // --- Popup Logic ---
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

        // --- YouTube Carousel & Other Logic ---
        document.addEventListener('DOMContentLoaded', function() {

            // Scroll Header Effect
            const header = document.querySelector('header');
            if (header) {
                 const handleScroll = () => { header.classList.toggle('scrolled', window.scrollY > 50); };
                 window.addEventListener('scroll', handleScroll);
                 handleScroll();
            }

            // YouTube Carousel Setup
            const carouselContainer = document.querySelector('.youtube-carousel-container');
            if (carouselContainer) {
                const slidesContainer = carouselContainer.querySelector('.youtube-carousel-slides');
                const slides = carouselContainer.querySelectorAll('.youtube-slide');
                const prevButton = carouselContainer.querySelector('.yt-carousel-prev');
                const nextButton = carouselContainer.querySelector('.yt-carousel-next');
                const dotsContainer = carouselContainer.querySelector('.yt-carousel-dots');
                const totalSlides = slides.length;
                let currentSlideIndex = 0;

                if (!slidesContainer || !slides || totalSlides === 0) {
                    console.warn("YouTube carousel essential elements not found. Carousel disabled.");
                    if (prevButton) prevButton.style.display = 'none';
                    if (nextButton) nextButton.style.display = 'none';
                    if (dotsContainer) dotsContainer.style.display = 'none';
                    return;
                }
                if (totalSlides <= 1) {
                    if (prevButton) prevButton.style.display = 'none';
                    if (nextButton) nextButton.style.display = 'none';
                    if (dotsContainer) dotsContainer.style.display = 'none';
                    return;
                }

                function createDots() {
                    if (!dotsContainer) return;
                    dotsContainer.innerHTML = '';
                    for (let i = 0; i < totalSlides; i++) {
                        const dot = document.createElement('button');
                        dot.classList.add('yt-dot');
                        dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
                        dot.addEventListener('click', () => goToSlide(i));
                        dotsContainer.appendChild(dot);
                    }
                }

                function updateCarousel() {
                    if (slidesContainer) {
                        slidesContainer.style.transform = `translateX(-${currentSlideIndex * 100}%)`;
                    } else {
                        console.error("Slides container not found during update.");
                        return;
                    }
                    const dots = dotsContainer?.querySelectorAll('.yt-dot');
                    if (dots) {
                        dots.forEach((dot, index) => {
                            dot.classList.toggle('active', index === currentSlideIndex);
                        });
                    }
                    slides.forEach((slide, index) => {
                        const iframe = slide.querySelector('iframe');
                        if (iframe && index !== currentSlideIndex) {
                            const currentSrc = iframe.getAttribute('src');
                            if (currentSrc) {
                               iframe.setAttribute('src', '');
                               requestAnimationFrame(() => { // Use requestAnimationFrame for better timing
                                   iframe.setAttribute('src', currentSrc);
                               });
                            }
                        }
                    });
                }

                function goToSlide(index) {
                    if (index >= 0 && index < totalSlides) {
                       currentSlideIndex = index;
                       updateCarousel();
                    }
                }
                function showNextSlide() {
                    currentSlideIndex = (currentSlideIndex + 1) % totalSlides;
                    updateCarousel();
                }
                function showPrevSlide() {
                    currentSlideIndex = (currentSlideIndex - 1 + totalSlides) % totalSlides;
                    updateCarousel();
                }

                if (nextButton) { nextButton.addEventListener('click', showNextSlide); }
                else { console.warn("YouTube carousel 'next' button not found."); }
                if (prevButton) { prevButton.addEventListener('click', showPrevSlide); }
                else { console.warn("YouTube carousel 'previous' button not found."); }

                createDots();
                updateCarousel();
            } // End if (carouselContainer)

            // Close popup when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target.classList.contains('transport-popup')) {
                    closePopup(event.target.id);
                }
            });

        }); // End DOMContentLoaded
    </script>

</body>
</html>
