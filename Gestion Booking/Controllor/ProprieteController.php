<?php
// Gestion Booking/Controllor/ProprieteController.php

// Inclure les modèles et le contrôleur Photo nécessaires
require_once __DIR__ . '/../Model/Villa.php';
require_once __DIR__ . '/../Model/MaisonHote.php';
require_once __DIR__ . '/../Model/Hotel.php';
require_once __DIR__ . '/Photo.php'; // Pour Photo::fileToBase64 et Photo controller instance

class ProprieteController {
    private $conn; // Objet PDO pour la connexion BD
    private $photoController; // Instance du contrôleur Photo

    /**
     * Constructeur
     * @param PDO $db Connexion à la base de données
     */
    public function __construct($db) {
        if (!$db instanceof PDO) {
             error_log("Erreur: Connexion BD invalide dans ProprieteController.");
             throw new InvalidArgumentException("Invalid database connection provided to ProprieteController.");
        }
        $this->conn = $db;
        // Initialiser le contrôleur Photo ici pour l'utiliser dans les méthodes
        $this->photoController = new Photo($db); // Assurez-vous que la classe Photo existe et prend PDO en argument
    }

    // --- Méthodes d'Ajout (inchangées) ---
    public function ajouterVilla(Villa $villa, $photoFile = null) {
        $photoNomAssocie = null;
        if ($photoFile && isset($photoFile['error']) && $photoFile['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($photoFile['type'], $allowed_types)) {
                $photoNomUnique = 'VILLA_' . preg_replace('/[^a-zA-Z0-9_]/', '', strtoupper($villa->getNomVilla())) . '_' . time();
                try {
                    $base64Image = Photo::fileToBase64($photoFile);
                    $this->photoController->setNom($photoNomUnique);
                    $this->photoController->setImageBase64($base64Image);
                    if ($this->photoController->ajouterPhoto()) {
                        $photoNomAssocie = $photoNomUnique;
                    } else {
                        error_log("ProprieteController: Échec de l'ajout de la photo à la BD pour " . $photoNomUnique);
                    }
                } catch (Exception $e) {
                    error_log("ProprieteController: Exception lors de l'ajout de photo pour villa: " . $e->getMessage());
                }
            } else {
                 error_log("ProprieteController: Type de fichier photo non autorisé pour villa: " . $photoFile['type']);
            }
        } elseif ($photoFile && isset($photoFile['error']) && $photoFile['error'] !== UPLOAD_ERR_NO_FILE) {
             error_log("ProprieteController: Erreur d'upload de fichier photo pour villa: " . $photoFile['error']);
        }
        $villa->setPhotoNomAssocie($photoNomAssocie);

        $sql = "INSERT INTO villas (nom_villa, type_villa, surface_m2, nb_chambres, nb_salles_bain, description, prix_indicatif, piscine, jardin_m2, parking, photo_nom_associe)
                VALUES (:nom_villa, :type_villa, :surface_m2, :nb_chambres, :nb_salles_bain, :description, :prix_indicatif, :piscine, :jardin_m2, :parking, :photo_nom_associe)";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':nom_villa', $villa->getNomVilla());
            $stmt->bindValue(':type_villa', $villa->getTypeVilla());
            $stmt->bindValue(':surface_m2', $villa->getSurfaceM2(), PDO::PARAM_INT);
            $stmt->bindValue(':nb_chambres', $villa->getNbChambres(), PDO::PARAM_INT);
            $stmt->bindValue(':nb_salles_bain', $villa->getNbSallesBain(), PDO::PARAM_INT);
            $stmt->bindValue(':description', $villa->getDescription());
            $stmt->bindValue(':prix_indicatif', $villa->getPrixIndicatif());
            $stmt->bindValue(':piscine', $villa->hasPiscine(), PDO::PARAM_BOOL);
            $stmt->bindValue(':jardin_m2', $villa->getJardinM2(), PDO::PARAM_INT);
            $stmt->bindValue(':parking', $villa->getParking(), PDO::PARAM_INT);
            $stmt->bindValue(':photo_nom_associe', $villa->getPhotoNomAssocie(), $villa->getPhotoNomAssocie() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("PDOException ajouterVilla: " . $e->getMessage() . " | SQL: " . $sql);
            if ($e->getCode() == 23000) { error_log("Tentative d'ajout d'une villa avec un nom déjà existant: " . $villa->getNomVilla()); }
            return false;
        }
    }
    public function ajouterMaisonHote(MaisonHote $maison, $photoFile = null) {
         $photoNomAssocie = null;
         if ($photoFile && isset($photoFile['error']) && $photoFile['error'] === UPLOAD_ERR_OK) {
             $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
             if (in_array($photoFile['type'], $allowed_types)) {
                 $photoNomUnique = 'MAISON_' . preg_replace('/[^a-zA-Z0-9_]/', '', strtoupper($maison->getNomMaison())) . '_' . time();
                 try {
                     $base64Image = Photo::fileToBase64($photoFile);
                     $this->photoController->setNom($photoNomUnique);
                     $this->photoController->setImageBase64($base64Image);
                     if ($this->photoController->ajouterPhoto()) { $photoNomAssocie = $photoNomUnique; }
                     else { error_log("ProprieteController: Échec ajout photo BD pour maison " . $photoNomUnique); }
                 } catch (Exception $e) { error_log("ProprieteController: Exception ajout photo maison hote: " . $e->getMessage()); }
             } else { error_log("ProprieteController: Type de fichier photo non autorisé pour maison: " . $photoFile['type']); }
         } elseif ($photoFile && isset($photoFile['error']) && $photoFile['error'] !== UPLOAD_ERR_NO_FILE) {
             error_log("ProprieteController: Erreur d'upload de fichier photo pour maison: " . $photoFile['error']);
         }
         $maison->setPhotoNomAssocie($photoNomAssocie);

         $sql = "INSERT INTO maisons_hotes (nom_maison, type_maison, surface_m2, nb_chambres, capacite_personnes, description, prix_nuit, petit_dejeuner_inclus, piscine, photo_nom_associe)
                 VALUES (:nom_maison, :type_maison, :surface_m2, :nb_chambres, :capacite, :description, :prix_nuit, :pdj, :piscine, :photo_nom)";
         try {
             $stmt = $this->conn->prepare($sql);
             $stmt->bindValue(':nom_maison', $maison->getNomMaison());
             $stmt->bindValue(':type_maison', $maison->getTypeMaison());
             $stmt->bindValue(':surface_m2', $maison->getSurfaceM2(), PDO::PARAM_INT);
             $stmt->bindValue(':nb_chambres', $maison->getNbChambres(), PDO::PARAM_INT);
             $stmt->bindValue(':capacite', $maison->getCapacitePersonnes(), PDO::PARAM_INT);
             $stmt->bindValue(':description', $maison->getDescription());
             $stmt->bindValue(':prix_nuit', $maison->getPrixNuit());
             $stmt->bindValue(':pdj', $maison->isPetitDejeunerInclus(), PDO::PARAM_BOOL);
             $stmt->bindValue(':piscine', $maison->hasPiscine(), PDO::PARAM_BOOL);
             $stmt->bindValue(':photo_nom', $maison->getPhotoNomAssocie(), $maison->getPhotoNomAssocie() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
             return $stmt->execute();
         } catch (PDOException $e) {
             error_log("PDOException ajouterMaisonHote: " . $e->getMessage() . " | SQL: " . $sql);
              if ($e->getCode() == 23000) { error_log("Tentative d'ajout d'une maison d'hôte avec un nom déjà existant: " . $maison->getNomMaison()); }
             return false;
        }
    }
    public function ajouterHotel(Hotel $hotel, $photoFile = null) {
        $photoNomAssocie = null;
        if ($photoFile && isset($photoFile['error']) && $photoFile['error'] === UPLOAD_ERR_OK) {
             $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
             if (in_array($photoFile['type'], $allowed_types)) {
                $photoNomUnique = 'HOTEL_' . preg_replace('/[^a-zA-Z0-9_]/', '', strtoupper($hotel->getNomHotel())) . '_' . time();
                try {
                    $base64Image = Photo::fileToBase64($photoFile);
                    $this->photoController->setNom($photoNomUnique);
                    $this->photoController->setImageBase64($base64Image);
                    if ($this->photoController->ajouterPhoto()) { $photoNomAssocie = $photoNomUnique; }
                    else { error_log("ProprieteController: Échec ajout photo BD pour hotel " . $photoNomUnique); }
                } catch (Exception $e) { error_log("ProprieteController: Exception ajout photo hotel: " . $e->getMessage()); }
            } else { error_log("ProprieteController: Type de fichier photo non autorisé pour hotel: " . $photoFile['type']); }
        } elseif ($photoFile && isset($photoFile['error']) && $photoFile['error'] !== UPLOAD_ERR_NO_FILE) {
             error_log("ProprieteController: Erreur d'upload de fichier photo pour hotel: " . $photoFile['error']);
        }
        $hotel->setPhotoNomAssocie($photoNomAssocie);

        $sql = "INSERT INTO hotels (nom_hotel, type_hotel, classement_etoiles, description, services_cles, adresse, prix_nuit_apd, photo_nom_associe)
                VALUES (:nom_hotel, :type_hotel, :etoiles, :description, :services, :adresse, :prix_apd, :photo_nom)";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':nom_hotel', $hotel->getNomHotel());
            $stmt->bindValue(':type_hotel', $hotel->getTypeHotel());
            $stmt->bindValue(':etoiles', $hotel->getClassementEtoiles(), PDO::PARAM_INT);
            $stmt->bindValue(':description', $hotel->getDescription());
            $stmt->bindValue(':services', $hotel->getServicesCles());
            $stmt->bindValue(':adresse', $hotel->getAdresse());
            $stmt->bindValue(':prix_apd', $hotel->getPrixNuitApd());
            $stmt->bindValue(':photo_nom', $hotel->getPhotoNomAssocie(), $hotel->getPhotoNomAssocie() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("PDOException ajouterHotel: " . $e->getMessage() . " | SQL: " . $sql);
            if ($e->getCode() == 23000) { error_log("Tentative d'ajout d'un hôtel avec un nom déjà existant: " . $hotel->getNomHotel()); }
            return false;
        }
    }

    // --- Méthodes de Récupération (inchangées) ---
    public function getAllVillas() {
        $sql = "SELECT * FROM villas ORDER BY nom_villa ASC";
        try { $stmt = $this->conn->prepare($sql); $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC); }
        catch (PDOException $e) { error_log("PDOException getAllVillas: " . $e->getMessage()); return []; }
    }
    public function getAllMaisonsHotes() {
        $sql = "SELECT * FROM maisons_hotes ORDER BY nom_maison ASC";
        try { $stmt = $this->conn->prepare($sql); $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC); }
        catch (PDOException $e) { error_log("PDOException getAllMaisonsHotes: " . $e->getMessage()); return []; }
    }
    public function getAllHotels() {
        $sql = "SELECT * FROM hotels ORDER BY nom_hotel ASC";
        try { $stmt = $this->conn->prepare($sql); $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC); }
        catch (PDOException $e) { error_log("PDOException getAllHotels: " . $e->getMessage()); return []; }
    }
    public function getVillaByNom($nomVilla) {
        $sql = "SELECT * FROM villas WHERE nom_villa = :nom LIMIT 1";
        try { $stmt = $this->conn->prepare($sql); $stmt->bindParam(':nom', $nomVilla, PDO::PARAM_STR); $stmt->execute(); return $stmt->fetch(PDO::FETCH_ASSOC); }
        catch (PDOException $e) { error_log("PDOException getVillaByNom: " . $e->getMessage()); return false; }
    }
    public function getMaisonHoteByNom($nomMaison) {
        $sql = "SELECT * FROM maisons_hotes WHERE nom_maison = :nom LIMIT 1";
        try { $stmt = $this->conn->prepare($sql); $stmt->bindParam(':nom', $nomMaison, PDO::PARAM_STR); $stmt->execute(); return $stmt->fetch(PDO::FETCH_ASSOC); }
        catch (PDOException $e) { error_log("PDOException getMaisonHoteByNom: " . $e->getMessage()); return false; }
    }
    public function getHotelByNom($nomHotel) {
        $sql = "SELECT * FROM hotels WHERE nom_hotel = :nom LIMIT 1";
        try { $stmt = $this->conn->prepare($sql); $stmt->bindParam(':nom', $nomHotel, PDO::PARAM_STR); $stmt->execute(); return $stmt->fetch(PDO::FETCH_ASSOC); }
        catch (PDOException $e) { error_log("PDOException getHotelByNom: " . $e->getMessage()); return false; }
    }

    // --- Méthodes de Modification (inchangées) ---
    public function modifierVilla($id_villa, Villa $villa, $photoFile = null) {
        $ancienPhotoNom = null;
        $nouveauPhotoNom = $villa->getPhotoNomAssocie();

        if ($photoFile && isset($photoFile['error']) && $photoFile['error'] === UPLOAD_ERR_OK) {
             $stmt_old_photo = $this->conn->prepare("SELECT photo_nom_associe FROM villas WHERE id_villa = :id");
             $stmt_old_photo->bindParam(':id', $id_villa, PDO::PARAM_INT);
             $stmt_old_photo->execute();
             $result = $stmt_old_photo->fetch(PDO::FETCH_ASSOC);
             if ($result && !empty($result['photo_nom_associe'])) {
                 $ancienPhotoNom = $result['photo_nom_associe'];
                 if ($nouveauPhotoNom === null) $nouveauPhotoNom = $ancienPhotoNom;
             }
        }

        if ($photoFile && isset($photoFile['error']) && $photoFile['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($photoFile['type'], $allowed_types)) {
                $photoNomUnique = 'VILLA_' . preg_replace('/[^a-zA-Z0-9_]/', '', strtoupper($villa->getNomVilla())) . '_' . time();
                try {
                    $base64Image = Photo::fileToBase64($photoFile);
                    $this->photoController->setNom($photoNomUnique);
                    $this->photoController->setImageBase64($base64Image);
                    if ($this->photoController->ajouterPhoto()) {
                        $nouveauPhotoNom = $photoNomUnique;
                    } else {
                        error_log("ProprieteController: Échec ajout NOUVELLE photo BD pour modif villa " . $id_villa);
                        $nouveauPhotoNom = $ancienPhotoNom;
                    }
                } catch (Exception $e) {
                    error_log("ProprieteController: Exception upload photo modif villa: " . $e->getMessage());
                    $nouveauPhotoNom = $ancienPhotoNom;
                }
            } else {
                 error_log("ProprieteController: Type fichier non autorisé pour modif villa: " . $photoFile['type']);
                 $nouveauPhotoNom = $ancienPhotoNom;
            }
        }
        $villa->setPhotoNomAssocie($nouveauPhotoNom);

        $sql = "UPDATE villas SET
                    nom_villa = :nom_villa, type_villa = :type_villa, surface_m2 = :surface_m2,
                    nb_chambres = :nb_chambres, nb_salles_bain = :nb_salles_bain, description = :description,
                    prix_indicatif = :prix_indicatif, piscine = :piscine, jardin_m2 = :jardin_m2,
                    parking = :parking, photo_nom_associe = :photo_nom_associe
                WHERE id_villa = :id_villa";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':nom_villa', $villa->getNomVilla());
            $stmt->bindValue(':type_villa', $villa->getTypeVilla());
            $stmt->bindValue(':surface_m2', $villa->getSurfaceM2(), PDO::PARAM_INT);
            $stmt->bindValue(':nb_chambres', $villa->getNbChambres(), PDO::PARAM_INT);
            $stmt->bindValue(':nb_salles_bain', $villa->getNbSallesBain(), PDO::PARAM_INT);
            $stmt->bindValue(':description', $villa->getDescription());
            $stmt->bindValue(':prix_indicatif', $villa->getPrixIndicatif());
            $stmt->bindValue(':piscine', $villa->hasPiscine(), PDO::PARAM_BOOL);
            $stmt->bindValue(':jardin_m2', $villa->getJardinM2(), PDO::PARAM_INT);
            $stmt->bindValue(':parking', $villa->getParking(), PDO::PARAM_INT);
            $stmt->bindValue(':photo_nom_associe', $villa->getPhotoNomAssocie(), $villa->getPhotoNomAssocie() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':id_villa', $id_villa, PDO::PARAM_INT);

            $updateReussi = $stmt->execute();

            if ($updateReussi && $ancienPhotoNom !== null && $nouveauPhotoNom !== $ancienPhotoNom) {
                if (!$this->photoController->deletePhotoByName($ancienPhotoNom)) {
                     error_log("ProprieteController: Échec suppression ancienne photo '" . $ancienPhotoNom . "' après modif villa " . $id_villa);
                }
            }
            return $updateReussi;

        } catch (PDOException $e) {
            error_log("PDOException modifierVilla: " . $e->getMessage() . " | SQL: " . $sql);
             if ($e->getCode() == 23000) { error_log("Violation contrainte unique modif villa: " . $villa->getNomVilla()); }
            return false;
        }
    }
    public function modifierMaisonHote($id_maison, MaisonHote $maison, $photoFile = null) {
        $ancienPhotoNom = null;
        $nouveauPhotoNom = $maison->getPhotoNomAssocie();

        if ($photoFile && isset($photoFile['error']) && $photoFile['error'] === UPLOAD_ERR_OK) {
             $stmt_old_photo = $this->conn->prepare("SELECT photo_nom_associe FROM maisons_hotes WHERE id_maison_hote = :id");
             $stmt_old_photo->bindParam(':id', $id_maison, PDO::PARAM_INT);
             $stmt_old_photo->execute();
             $result = $stmt_old_photo->fetch(PDO::FETCH_ASSOC);
             if ($result && !empty($result['photo_nom_associe'])) {
                 $ancienPhotoNom = $result['photo_nom_associe'];
                 if ($nouveauPhotoNom === null) $nouveauPhotoNom = $ancienPhotoNom;
             }
        }

        if ($photoFile && isset($photoFile['error']) && $photoFile['error'] === UPLOAD_ERR_OK) {
             $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
             if (in_array($photoFile['type'], $allowed_types)) {
                 $photoNomUnique = 'MAISON_' . preg_replace('/[^a-zA-Z0-9_]/', '', strtoupper($maison->getNomMaison())) . '_' . time();
                 try {
                     $base64Image = Photo::fileToBase64($photoFile);
                     $this->photoController->setNom($photoNomUnique);
                     $this->photoController->setImageBase64($base64Image);
                     if ($this->photoController->ajouterPhoto()) { $nouveauPhotoNom = $photoNomUnique; }
                     else { error_log("ProprieteController: Échec ajout NOUVELLE photo BD modif maison " . $id_maison); $nouveauPhotoNom = $ancienPhotoNom; }
                 } catch (Exception $e) { error_log("ProprieteController: Exception upload photo modif maison: " . $e->getMessage()); $nouveauPhotoNom = $ancienPhotoNom; }
             } else { error_log("ProprieteController: Type fichier non autorisé modif maison: " . $photoFile['type']); $nouveauPhotoNom = $ancienPhotoNom; }
        }
        $maison->setPhotoNomAssocie($nouveauPhotoNom);

        $sql = "UPDATE maisons_hotes SET
                    nom_maison = :nom, type_maison = :type, surface_m2 = :surface, nb_chambres = :chambres,
                    capacite_personnes = :capacite, description = :desc, prix_nuit = :prix,
                    petit_dejeuner_inclus = :pdj, piscine = :piscine, photo_nom_associe = :photo_nom
                WHERE id_maison_hote = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':nom', $maison->getNomMaison());
            $stmt->bindValue(':type', $maison->getTypeMaison());
            $stmt->bindValue(':surface', $maison->getSurfaceM2(), PDO::PARAM_INT);
            $stmt->bindValue(':chambres', $maison->getNbChambres(), PDO::PARAM_INT);
            $stmt->bindValue(':capacite', $maison->getCapacitePersonnes(), PDO::PARAM_INT);
            $stmt->bindValue(':desc', $maison->getDescription());
            $stmt->bindValue(':prix', $maison->getPrixNuit());
            $stmt->bindValue(':pdj', $maison->isPetitDejeunerInclus(), PDO::PARAM_BOOL);
            $stmt->bindValue(':piscine', $maison->hasPiscine(), PDO::PARAM_BOOL);
            $stmt->bindValue(':photo_nom', $maison->getPhotoNomAssocie(), $maison->getPhotoNomAssocie() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':id', $id_maison, PDO::PARAM_INT);

            $updateReussi = $stmt->execute();

            if ($updateReussi && $ancienPhotoNom !== null && $nouveauPhotoNom !== $ancienPhotoNom) {
                if (!$this->photoController->deletePhotoByName($ancienPhotoNom)) {
                     error_log("ProprieteController: Échec suppression ancienne photo '" . $ancienPhotoNom . "' après modif maison " . $id_maison);
                }
            }
            return $updateReussi;
        } catch (PDOException $e) {
             error_log("PDOException modifierMaisonHote: " . $e->getMessage() . " | SQL: " . $sql);
              if ($e->getCode() == 23000) { error_log("Violation contrainte unique modif maison: " . $maison->getNomMaison()); }
             return false;
        }
    }
    public function modifierHotel($id_hotel, Hotel $hotel, $photoFile = null) {
        $ancienPhotoNom = null;
        $nouveauPhotoNom = $hotel->getPhotoNomAssocie();

        if ($photoFile && isset($photoFile['error']) && $photoFile['error'] === UPLOAD_ERR_OK) {
             $stmt_old_photo = $this->conn->prepare("SELECT photo_nom_associe FROM hotels WHERE id_hotel = :id");
             $stmt_old_photo->bindParam(':id', $id_hotel, PDO::PARAM_INT);
             $stmt_old_photo->execute();
             $result = $stmt_old_photo->fetch(PDO::FETCH_ASSOC);
             if ($result && !empty($result['photo_nom_associe'])) {
                 $ancienPhotoNom = $result['photo_nom_associe'];
                  if ($nouveauPhotoNom === null) $nouveauPhotoNom = $ancienPhotoNom;
            }
        }

        if ($photoFile && isset($photoFile['error']) && $photoFile['error'] === UPLOAD_ERR_OK) {
             $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
             if (in_array($photoFile['type'], $allowed_types)) {
                 $photoNomUnique = 'HOTEL_' . preg_replace('/[^a-zA-Z0-9_]/', '', strtoupper($hotel->getNomHotel())) . '_' . time();
                 try {
                     $base64Image = Photo::fileToBase64($photoFile);
                     $this->photoController->setNom($photoNomUnique);
                     $this->photoController->setImageBase64($base64Image);
                     if ($this->photoController->ajouterPhoto()) { $nouveauPhotoNom = $photoNomUnique; }
                     else { error_log("ProprieteController: Échec ajout NOUVELLE photo BD modif hotel " . $id_hotel); $nouveauPhotoNom = $ancienPhotoNom; }
                 } catch (Exception $e) { error_log("ProprieteController: Exception upload photo modif hotel: " . $e->getMessage()); $nouveauPhotoNom = $ancienPhotoNom; }
             } else { error_log("ProprieteController: Type fichier non autorisé modif hotel: " . $photoFile['type']); $nouveauPhotoNom = $ancienPhotoNom; }
        }
        $hotel->setPhotoNomAssocie($nouveauPhotoNom);

        $sql = "UPDATE hotels SET
                    nom_hotel = :nom, type_hotel = :type, classement_etoiles = :etoiles, description = :desc,
                    services_cles = :services, adresse = :adresse, prix_nuit_apd = :prix, photo_nom_associe = :photo_nom
                WHERE id_hotel = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':nom', $hotel->getNomHotel());
            $stmt->bindValue(':type', $hotel->getTypeHotel());
            $stmt->bindValue(':etoiles', $hotel->getClassementEtoiles(), PDO::PARAM_INT);
            $stmt->bindValue(':desc', $hotel->getDescription());
            $stmt->bindValue(':services', $hotel->getServicesCles());
            $stmt->bindValue(':adresse', $hotel->getAdresse());
            $stmt->bindValue(':prix', $hotel->getPrixNuitApd());
            $stmt->bindValue(':photo_nom', $hotel->getPhotoNomAssocie(), $hotel->getPhotoNomAssocie() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':id', $id_hotel, PDO::PARAM_INT);

             $updateReussi = $stmt->execute();

            if ($updateReussi && $ancienPhotoNom !== null && $nouveauPhotoNom !== $ancienPhotoNom) {
                if (!$this->photoController->deletePhotoByName($ancienPhotoNom)) {
                     error_log("ProprieteController: Échec suppression ancienne photo '" . $ancienPhotoNom . "' après modif hotel " . $id_hotel);
                }
            }
            return $updateReussi;
        } catch (PDOException $e) {
            error_log("PDOException modifierHotel: " . $e->getMessage() . " | SQL: " . $sql);
            if ($e->getCode() == 23000) { error_log("Violation contrainte unique modif hotel: " . $hotel->getNomHotel()); }
            return false;
        }
    }


    // --- Méthodes de Suppression (CORRIGÉES) ---

    /**
     * Supprime une villa et sa photo associée.
     * @param int $id_villa ID de la villa à supprimer.
     * @return bool True si succès, False si échec.
     */
    public function supprimerVilla($id_villa) {
        $photoNomAssocie = null;
        $this->conn->beginTransaction(); // Start transaction

        try {
            // 1. Récupérer le nom de la photo associée AVANT de supprimer la villa
            $sqlSelectPhoto = "SELECT photo_nom_associe FROM villas WHERE id_villa = :id";
            $stmtSelect = $this->conn->prepare($sqlSelectPhoto);
            $stmtSelect->bindParam(':id', $id_villa, PDO::PARAM_INT);
            $stmtSelect->execute();
            $result = $stmtSelect->fetch(PDO::FETCH_ASSOC);
            if ($result && !empty($result['photo_nom_associe'])) {
                $photoNomAssocie = $result['photo_nom_associe'];
            }

            // 2. Supprimer la villa de la table 'villas'
            $sqlDeleteVilla = "DELETE FROM villas WHERE id_villa = :id";
            $stmtDelete = $this->conn->prepare($sqlDeleteVilla);
            $stmtDelete->bindParam(':id', $id_villa, PDO::PARAM_INT);
            $deleteVillaSuccess = $stmtDelete->execute();

            if (!$deleteVillaSuccess) {
                 throw new Exception("Échec de la suppression de la villa ID: " . $id_villa);
            }

            // 3. Si la suppression de la villa réussit ET qu'il y avait un nom de photo,
            //    appeler deletePhotoByName du contrôleur Photo
            if ($photoNomAssocie !== null) {
                // Assurez-vous que la méthode deletePhotoByName existe dans Photo.php et fonctionne
                if (!$this->photoController->deletePhotoByName($photoNomAssocie)) {
                    // Log l'erreur mais on peut choisir de continuer ou d'annuler la transaction
                    error_log("ProprieteController: Échec de la suppression de l'enregistrement photo '" . $photoNomAssocie . "' pour villa ID " . $id_villa . ". La villa a été supprimée.");
                    // Optionnel: throw new Exception("Échec suppression photo associée."); // Pour annuler la transaction
                }
            }

            // Si tout s'est bien passé, valider la transaction
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // En cas d'erreur, annuler la transaction
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Exception lors de la suppression de la villa ID " . $id_villa . ": " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une maison d'hôte et sa photo associée.
     * @param int $id_maison ID de la maison d'hôte à supprimer.
     * @return bool True si succès, False si échec.
     */
     public function supprimerMaisonHote($id_maison) {
        $photoNomAssocie = null;
        $this->conn->beginTransaction();

        try {
            // 1. Récupérer le nom de la photo
            // !! Vérifiez le nom exact de la colonne ID: id_maison_hote? !!
            $sqlSelectPhoto = "SELECT photo_nom_associe FROM maisons_hotes WHERE id_maison_hote = :id";
            $stmtSelect = $this->conn->prepare($sqlSelectPhoto);
            $stmtSelect->bindParam(':id', $id_maison, PDO::PARAM_INT);
            $stmtSelect->execute();
            $result = $stmtSelect->fetch(PDO::FETCH_ASSOC);
            if ($result && !empty($result['photo_nom_associe'])) {
                $photoNomAssocie = $result['photo_nom_associe'];
            }

            // 2. Supprimer la maison d'hôte
            $sqlDeleteMaison = "DELETE FROM maisons_hotes WHERE id_maison_hote = :id";
            $stmtDelete = $this->conn->prepare($sqlDeleteMaison);
            $stmtDelete->bindParam(':id', $id_maison, PDO::PARAM_INT);
            $deleteMaisonSuccess = $stmtDelete->execute();

            if (!$deleteMaisonSuccess) {
                 throw new Exception("Échec de la suppression de la maison d'hôte ID: " . $id_maison);
            }

            // 3. Supprimer la photo associée si elle existe
            if ($photoNomAssocie !== null) {
                if (!$this->photoController->deletePhotoByName($photoNomAssocie)) {
                    error_log("ProprieteController: Échec de la suppression photo '" . $photoNomAssocie . "' pour maison ID " . $id_maison);
                    // Optionnel: throw new Exception("Échec suppression photo associée.");
                }
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Exception lors de la suppression de la maison d'hôte ID " . $id_maison . ": " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un hôtel et sa photo associée.
     * @param int $id_hotel ID de l'hôtel à supprimer.
     * @return bool True si succès, False si échec.
     */
     public function supprimerHotel($id_hotel) {
        $photoNomAssocie = null;
        $this->conn->beginTransaction();

        try {
            // 1. Récupérer le nom de la photo
            // !! Vérifiez le nom exact de la colonne ID: id_hotel? !!
            $sqlSelectPhoto = "SELECT photo_nom_associe FROM hotels WHERE id_hotel = :id";
            $stmtSelect = $this->conn->prepare($sqlSelectPhoto);
            $stmtSelect->bindParam(':id', $id_hotel, PDO::PARAM_INT);
            $stmtSelect->execute();
            $result = $stmtSelect->fetch(PDO::FETCH_ASSOC);
            if ($result && !empty($result['photo_nom_associe'])) {
                $photoNomAssocie = $result['photo_nom_associe'];
            }

            // 2. Supprimer l'hôtel
            $sqlDeleteHotel = "DELETE FROM hotels WHERE id_hotel = :id";
            $stmtDelete = $this->conn->prepare($sqlDeleteHotel);
            $stmtDelete->bindParam(':id', $id_hotel, PDO::PARAM_INT);
            $deleteHotelSuccess = $stmtDelete->execute();

             if (!$deleteHotelSuccess) {
                 throw new Exception("Échec de la suppression de l'hôtel ID: " . $id_hotel);
            }

            // 3. Supprimer la photo associée si elle existe
            if ($photoNomAssocie !== null) {
                if (!$this->photoController->deletePhotoByName($photoNomAssocie)) {
                     error_log("ProprieteController: Échec de la suppression photo '" . $photoNomAssocie . "' pour hôtel ID " . $id_hotel);
                     // Optionnel: throw new Exception("Échec suppression photo associée.");
                }
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
             if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Exception lors de la suppression de l'hôtel ID " . $id_hotel . ": " . $e->getMessage());
            return false;
        }
    }


} // Fin classe ProprieteController
?>
