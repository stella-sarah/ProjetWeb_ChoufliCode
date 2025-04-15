<?php
// upload-photo.php - Page pour télécharger des photos
require_once 'C:/xampp/htdocs/Gestion Booking/config.php';
require_once 'C:/xampp/htdocs/Gestion Booking/Controllor/Photo.php';

// Initialiser la session
session_start();

// Vérifier si l'utilisateur est connecté en tant qu'administrateur
// Si vous avez un système d'authentification, utilisez-le ici
// if(!isset($_SESSION['admin_id'])) {
//     header("Location: login.php");
//     exit();
// }

// Initialiser la connexion à la base de données
$db = config::getConnexion();

// Initialiser l'objet Photo
$photo = new Photo($db);

// Variables pour les messages
$message = '';
$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier si un fichier a été téléchargé
    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Récupérer les données du formulaire
        $nom = htmlspecialchars(strip_tags($_POST['nom']));
        
        // Vérifier le type de fichier
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if(in_array($_FILES['image']['type'], $allowed_types)) {
            // Convertir l'image en base64
            $image_base64 = Photo::fileToBase64($_FILES['image']);
            
            // Définir les valeurs de l'objet photo
            $photo->setNom($nom);
            $photo->setImageBase64($image_base64);
            
            // Ajouter la photo dans la base de données
            if($photo->ajouterPhoto()) {
                $message = "La photo a été téléchargée avec succès.";
            } else {
                $error = "Une erreur s'est produite lors de l'enregistrement de la photo.";
            }
        } else {
            $error = "Le format du fichier n'est pas pris en charge. Veuillez télécharger une image JPEG, PNG ou GIF.";
        }
    } else {
        $error = "Veuillez sélectionner une image à télécharger.";
    }
}

// Récupérer toutes les photos
$photos = $photo->getAllPhotos();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Gestion des Photos</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .photo-item {
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            position: relative;
        }
        .photo-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .photo-info {
            padding: 10px;
            background: #f8f9fa;
        }
        .photo-actions {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .delete-btn {
            background: rgba(220, 53, 69, 0.8);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .delete-btn:hover {
            background: rgba(220, 53, 69, 1);
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
                    <li><a href="reservation.php">Réservation</a></li>
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

    <!-- Main Content -->
    <section class="contact-section" style="padding-top: 120px; padding-bottom: 80px;">
        <div class="container">
            <div class="form-container">
                <div class="form-header">
                    <h2>Gestion des Photos</h2>
                    <p>Téléchargez des photos pour les villas et propriétés</p>
                </div>
                
                <?php if(!empty($message)): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom de la Villa/Propriété</label>
                            <input type="text" id="nom" name="nom" class="form-control" placeholder="Ex: VILLA_S3_KMAR" required>
                            <small>Utilisez un nom descriptif qui identifie clairement la propriété</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Sélectionner une Image</label>
                            <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/gif" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-submit">Télécharger la Photo</button>
                    </div>
                </form>
            </div>
            
            <h2 style="margin-top: 40px;">Photos Téléchargées</h2>
            
            <?php if(empty($photos)): ?>
                <p>Aucune photo n'a été téléchargée pour le moment.</p>
            <?php else: ?>
                <div class="photos-grid">
                    <?php foreach($photos as $p): ?>
                        <div class="photo-item">
                            <div class="photo-actions">
                                <a href="delete-photo.php?id=<?php echo $p['id']; ?>" class="delete-btn" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette photo?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                            <img src="<?php echo $p['image_base64']; ?>" alt="<?php echo htmlspecialchars($p['nom']); ?>">
                            <div class="photo-info">
                                <p><strong><?php echo htmlspecialchars($p['nom']); ?></strong></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

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

</body>
</html>