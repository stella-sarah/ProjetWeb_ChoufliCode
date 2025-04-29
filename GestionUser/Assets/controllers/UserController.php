<?php
require_once 'models/User.php';

class UserController {
    private $db;
    private $table = "users";

    public function __construct($db) {
        $this->db = $db;
    }

    public function dashboard() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = "Accès non autorisé.";
            header("Location: index.php?controller=auth&action=showLoginForm");
            exit;
        }

        // Fetch user statistics
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $query = "SELECT COUNT(*) as active FROM " . $this->table . " WHERE last_login IS NOT NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $activeUsers = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

        $query = "SELECT COUNT(*) as admins FROM " . $this->table . " WHERE role = 'admin'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $adminUsers = $stmt->fetch(PDO::FETCH_ASSOC)['admins'];

        $inactiveUsers = $totalUsers - $activeUsers;

        // Fetch recent users
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC LIMIT 4";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $recentUsersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $recentUsers = [];
        foreach ($recentUsersData as $userData) {
            $user = new User();
            $user->setId($userData['id']);
            $user->setNom($userData['nom']);
            $user->setPrenom($userData['prenom']);
            $user->setEmail($userData['email']);
            $user->setRole($userData['role']);
            $user->setCreatedAt($userData['created_at']);
            $user->setLastLogin($userData['last_login'] ?? null);

            $recentUsers[] = [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'created_at' => $user->getCreatedAt(),
                'status' => $user->getLastLogin() ? 'user' : 'inactive'
            ];
        }

        require_once 'views/users/admin_users.php';
    }

    public function index() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = "Accès non autorisé.";
            header("Location: index.php?controller=auth&action=showLoginForm");
            exit;
        }

        // Fetch all users
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($usersData as $userData) {
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
            $users[] = [
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
        }

        $totalUsers = count($users);
        $activeUsers = count(array_filter($users, fn($user) => $user['active'] ?? true));
        $adminUsers = count(array_filter($users, fn($user) => $user['role'] === 'admin'));
        $inactiveUsers = $totalUsers - $activeUsers;

        require_once 'views/users/index.php';
    }

    public function store() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = "Accès non autorisé.";
            header("Location: index.php?controller=auth&action=showLoginForm");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom']);
            $prenom = trim($_POST['prenom']);
            $email = trim($_POST['email']);
            $mot_de_passe = trim($_POST['mot_de_passe']);
            $genre = trim($_POST['genre']);
            $date_naissance = trim($_POST['date_naissance']);
            $telephone = trim($_POST['telephone']);
            $role = trim($_POST['role']);
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
            if (empty($role)) {
                $errors[] = "Le rôle est requis.";
            }
            if (!$accepte_conditions) {
                $errors[] = "Les conditions d'utilisation doivent être acceptées.";
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
                    ':role' => $role,
                    ':photo' => $photo,
                    ':newsletter' => $newsletter,
                    ':accepte_conditions' => $accepte_conditions,
                    ':created_at' => date('Y-m-d H:i:s')
                ]);

                if ($success) {
                    $_SESSION['success'] = "Utilisateur ajouté avec succès.";
                    header("Location: index.php?controller=user&action=dashboard");
                    exit;
                } else {
                    $_SESSION['error'] = "Une erreur s'est produite lors de l'ajout de l'utilisateur.";
                    header("Location: index.php?controller=user&action=dashboard");
                    exit;
                }
            } else {
                $_SESSION['errors'] = $errors;
                header("Location: index.php?controller=user&action=dashboard");
                exit;
            }
        }
    }

    public function edit() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Veuillez vous connecter.";
            header("Location: index.php?controller=auth&action=showLoginForm");
            exit;
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_id'] !== $id) {
            $_SESSION['error'] = "Accès non autorisé.";
            header("Location: index.php?controller=user&action=profile");
            exit;
        }

        // Fetch user from database
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header("Location: index.php?controller=user&action=dashboard");
            exit;
        }

        $user = [
            'id' => $userData['id'],
            'nom' => $userData['nom'],
            'prenom' => $userData['prenom'],
            'email' => $userData['email'],
            'genre' => $userData['genre'],
            'date_naissance' => $userData['date_naissance'],
            'telephone' => $userData['telephone'],
            'role' => $userData['role'],
            'photo' => $userData['photo'],
            'newsletter' => $userData['newsletter'],
            'accepte_conditions' => $userData['accepte_conditions'],
            'created_at' => $userData['created_at']
        ];

        require_once 'views/users/edit.php';
    }

    public function update() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Veuillez vous connecter.";
            header("Location: index.php?controller=auth&action=showLoginForm");
            exit;
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_id'] !== $id) {
            $_SESSION['error'] = "Accès non autorisé.";
            header("Location: index.php?controller=user&action=profile");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom']);
            $prenom = trim($_POST['prenom']);
            $email = trim($_POST['email']);
            $mot_de_passe = trim($_POST['mot_de_passe']);
            $genre = trim($_POST['genre']);
            $date_naissance = trim($_POST['date_naissance']);
            $telephone = trim($_POST['telephone']);
            $role = trim($_POST['role']);
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
                $query = "SELECT * FROM " . $this->table . " WHERE email = :email AND id != :id LIMIT 1";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':email' => $email, ':id' => $id]);
                if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                    $errors[] = "Cet email est déjà utilisé.";
                }
            }
            if (!empty($mot_de_passe) && strlen($mot_de_passe) < 6) {
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
            if ($_SESSION['user_role'] === 'admin' && empty($role)) {
                $errors[] = "Le rôle est requis.";
            }
            if (!$accepte_conditions) {
                $errors[] = "Les conditions d'utilisation doivent être acceptées.";
            }

            // Handle file upload
            $query = "SELECT photo FROM " . $this->table . " WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);
            $currentPhoto = $stmt->fetch(PDO::FETCH_ASSOC)['photo'];

            $photo = $currentPhoto;
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
                $query = "UPDATE " . $this->table . " SET 
                    nom = :nom,
                    prenom = :prenom,
                    email = :email,
                    genre = :genre,
                    date_naissance = :date_naissance,
                    telephone = :telephone,
                    role = :role,
                    photo = :photo,
                    newsletter = :newsletter,
                    accepte_conditions = :accepte_conditions";
                    
                if (!empty($mot_de_passe)) {
                    $query .= ", mot_de_passe = :mot_de_passe";
                }

                $query .= " WHERE id = :id";

                $params = [
                    ':id' => $id,
                    ':nom' => $nom,
                    ':prenom' => $prenom,
                    ':email' => $email,
                    ':genre' => $genre,
                    ':date_naissance' => $date_naissance,
                    ':telephone' => $telephone,
                    ':role' => $_SESSION['user_role'] === 'admin' ? $role : 'user',
                    ':photo' => $photo,
                    ':newsletter' => $newsletter,
                    ':accepte_conditions' => $accepte_conditions
                ];

                if (!empty($mot_de_passe)) {
                    $params[':mot_de_passe'] = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                }

                $stmt = $this->db->prepare($query);
                $success = $stmt->execute($params);

                if ($success) {
                    $_SESSION['success'] = "Utilisateur mis à jour avec succès.";
                    if ($_SESSION['user_id'] == $id) {
                        // Fetch updated user data
                        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
                        $stmt = $this->db->prepare($query);
                        $stmt->execute([':id' => $id]);
                        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

                        $_SESSION['user'] = $userData;
                        $_SESSION['user_nom'] = $nom;
                        $_SESSION['user_prenom'] = $prenom;
                    }
                    if ($_SESSION['user_role'] === 'admin') {
                        header("Location: index.php?controller=user&action=dashboard");
                    } else {
                        header("Location: index.php?controller=user&action=profile");
                    }
                    exit;
                } else {
                    $_SESSION['error'] = "Une erreur s'est produite lors de la mise à jour.";
                    header("Location: index.php?controller=user&action=edit&id=$id");
                    exit;
                }
            } else {
                $_SESSION['errors'] = $errors;
                header("Location: index.php?controller=user&action=edit&id=$id");
                exit;
            }
        }
    }

    public function delete() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = "Accès non autorisé.";
            header("Location: index.php?controller=auth&action=showLoginForm");
            exit;
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $success = $stmt->execute([':id' => $id]);

        if ($success) {
            $_SESSION['success'] = "Utilisateur supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur s'est produite lors de la suppression.";
        }
        header("Location: index.php?controller=user&action=dashboard");
        exit;
    }

    public function profile() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Veuillez vous connecter.";
            header("Location: index.php?controller=auth&action=showLoginForm");
            exit;
        }

        // Fetch user from database
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        $user = new User();
        $user->setId($userData['id']);
        $user->setNom($userData['nom']);
        $user->setPrenom($userData['prenom']);
        $user->setEmail($userData['email']);
        $user->setGenre($userData['genre']);
        $user->setDateNaissance($userData['date_naissance']);
        $user->setTelephone($userData['telephone']);
        $user->setRole($userData['role']);
        $user->setPhoto($userData['photo']);
        $user->setNewsletter($userData['newsletter']);
        $user->setAccepteConditions($userData['accepte_conditions']);
        $user->setCreatedAt($userData['created_at']);

        $user = [
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

        require_once 'views/users/profile.php';
    }

    private function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
?>