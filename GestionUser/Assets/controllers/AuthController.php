<?php
require_once 'models/User.php';

class AuthController {
    private $db;
    private $table = "users";

    public function __construct($db) {
        $this->db = $db;
    }

    public function showLoginForm() {
        if (isset($_SESSION['user_id'])) {
            if ($_SESSION['user_role'] === 'admin') {
                header("Location: index.php?controller=user&action=index");
            } else {
                header("Location: index.php?controller=user&action=profile");
            }
            exit;
        }

        // Generate CAPTCHA
        $captchaText = $this->generateCaptchaText(6);
        $_SESSION['captcha'] = $captchaText;

        require_once 'views/auth/login.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = trim($_POST['mot_de_passe']);
            $captchaInput = trim($_POST['captcha']);
            $errors = [];

            // Server-side validation
            if (empty($email)) {
                $errors[] = "L'email est requis.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'email n'est pas valide.";
            }
            if (empty($password)) {
                $errors[] = "Le mot de passe est requis.";
            }
            if (empty($captchaInput)) {
                $errors[] = "Le CAPTCHA est requis.";
            } elseif ($captchaInput !== $_SESSION['captcha']) {
                $errors[] = "Le CAPTCHA est incorrect.";
            }

            if (empty($errors)) {
                // Fetch user from database
                $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':email' => $email]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($userData && password_verify($password, $userData['mot_de_passe'])) {
                    $user = new User();
                    $user->setId($userData['id']);
                    $user->setNom($userData['nom']);
                    $user->setPrenom($userData['prenom']);
                    $user->setEmail($userData['email']);
                    $user->setMotDePasse($userData['mot_de_passe']);
                    $user->setGenre($userData['genre']);
                    $user->setDateNaissance($userData['date_naissance']);
                    $user->setTelephone($userData['telephone']);
                    $user->setRole($userData['role']);
                    $user->setPhoto($userData['photo']);
                    $user->setNewsletter($userData['newsletter']);
                    $user->setAccepteConditions($userData['accepte_conditions']);
                    $user->setCreatedAt($userData['created_at']);

                    $_SESSION['user_id'] = $user->getId();
                    $_SESSION['user_role'] = $user->getRole();
                    $_SESSION['user_nom'] = $user->getNom();
                    $_SESSION['user_prenom'] = $user->getPrenom();
                    $_SESSION['user'] = [
                        'id' => $user->getId(),
                        'nom' => $user->getNom(),
                        'prenom' => $user->getPrenom(),
                        'email' => $user->getEmail(),
                        'genre' => $user->getGenre(),
                        'date_naissance' => $user->getDateNaissance(),
                        'telephone' => $user->getTelephone(),
                        'role' => $user->getRole(),
                        'photo' => $user->getPhoto(),
                        'newsletter' => $user->getNewsletter(),
                        'accepte_conditions' => $user->getAccepteConditions(),
                        'created_at' => $user->getCreatedAt()
                    ];

                    if ($user->getRole() === 'admin') {
                        header("Location: index.php?controller=user&action=index");
                    } else {
                        header("Location: index.php?controller=user&action=profile");
                    }
                    exit;
                } else {
                    $_SESSION['error'] = "Email ou mot de passe incorrect.";
                    header("Location: index.php?controller=auth&action=showLoginForm");
                    exit;
                }
            } else {
                $_SESSION['errors'] = $errors;
                header("Location: index.php?controller=auth&action=showLoginForm");
                exit;
            }
        }
    }

    public function showSignupForm() {
        if (isset($_SESSION['user_id'])) {
            if ($_SESSION['user_role'] === 'admin') {
                header("Location: index.php?controller=user&action=index");
            } else {
                header("Location: index.php?controller=user&action=profile");
            }
            exit;
        }
        require_once 'views/auth/signup.php';
    }

    public function signup() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom']);
            $prenom = trim($_POST['prenom']);
            $email = trim($_POST['email']);
            $mot_de_passe = trim($_POST['mot_de_passe']);
            $genre = trim($_POST['genre']);
            $date_naissance = trim($_POST['date_naissance']);
            $telephone = trim($_POST['telephone']);
            $newsletter = isset($_POST['newsletter']) ? 1 : 0;
            $accepte_conditions = isset($_POST['accepte_conditions']) ? 1 : 0;
            $errors = [];

            // Server-side validation
            if (empty($nom)) {
                $errors[] = "Le nom est requis.";
            }
            if (empty($prenom)) {
                $errors[] = "Le prénom est requis.";
            }
            if (empty($email)) {
                $errors[] = "L'email est requis.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'email n'est pas valide.";
            } else {
                $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':email' => $email]);
                if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                    $errors[] = "Cet email est déjà utilisé.";
                }
            }
            if (empty($mot_de_passe)) {
                $errors[] = "Le mot de passe est requis.";
            } elseif (strlen($mot_de_passe) < 6) {
                $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
            }
            if (empty($genre)) {
                $errors[] = "Le genre est requis.";
            }
            if (empty($date_naissance)) {
                $errors[] = "La date de naissance est requise.";
            } elseif (!$this->validateDate($date_naissance)) {
                $errors[] = "La date de naissance doit être au format YYYY-MM-DD et valide.";
            }
            if (empty($telephone)) {
                $errors[] = "Le téléphone est requis.";
            } elseif (!preg_match("/^[0-9]{8,15}$/", $telephone)) {
                $errors[] = "Le numéro de téléphone n'est pas valide.";
            }
            if (!$accepte_conditions) {
                $errors[] = "Vous devez accepter les conditions d'utilisation.";
            }

            // Handle file upload
            $photo = '';
            if (!empty($_FILES['photo']['name'])) {
                $target_dir = "assets/images/users/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $photo = $target_dir . uniqid() . "_" . basename($_FILES['photo']['name']);
                $imageFileType = strtolower(pathinfo($photo, PATHINFO_EXTENSION));
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array($imageFileType, $allowedTypes)) {
                    $errors[] = "Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
                } elseif ($_FILES['photo']['size'] > 5000000) {
                    $errors[] = "La photo ne doit pas dépasser 5MB.";
                } elseif (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo)) {
                    $errors[] = "Erreur lors du téléchargement de la photo.";
                }
            }

            if (empty($errors)) {
                $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                $query = "INSERT INTO " . $this->table . " (
                    nom, prenom, email, mot_de_passe, genre, date_naissance, telephone, role, photo, newsletter, accepte_conditions, created_at
                ) VALUES (
                    :nom, :prenom, :email, :mot_de_passe, :genre, :date_naissance, :telephone, :role, :photo, :newsletter, :accepte_conditions, :created_at
                )";
                
                $stmt = $this->db->prepare($query);
                $success = $stmt->execute([
                    ':nom' => $nom,
                    ':prenom' => $prenom,
                    ':email' => $email,
                    ':mot_de_passe' => $hashed_password,
                    ':genre' => $genre,
                    ':date_naissance' => $date_naissance,
                    ':telephone' => $telephone,
                    ':role' => 'user',
                    ':photo' => $photo,
                    ':newsletter' => $newsletter,
                    ':accepte_conditions' => $accepte_conditions,
                    ':created_at' => date('Y-m-d H:i:s')
                ]);

                if ($success) {
                    $_SESSION['success'] = "Inscription réussie. Veuillez vous connecter.";
                    header("Location: index.php?controller=auth&action=showLoginForm");
                    exit;
                } else {
                    $_SESSION['error'] = "Une erreur s'est produite lors de l'inscription.";
                    header("Location: index.php?controller=auth&action=showSignupForm");
                    exit;
                }
            } else {
                $_SESSION['errors'] = $errors;
                header("Location: index.php?controller=auth&action=showSignupForm");
                exit;
            }
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: index.php?controller=auth&action=showLoginForm");
        exit;
    }

    public function refreshCaptcha() {
        $captchaText = $this->generateCaptchaText(6);
        $_SESSION['captcha'] = $captchaText;
        echo $captchaText;
    }

    private function generateCaptchaText($length) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $captchaText = '';
        for ($i = 0; $i < $length; $i++) {
            $captchaText .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $captchaText;
    }

    private function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
?>