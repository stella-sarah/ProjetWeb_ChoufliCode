<?php


header('Content-Type: application/json'); // Indiquer qu'on retourne du JSON

// Inclusion des fichiers nécessaires
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Controllor/DemandeAchatController.php';
require_once __DIR__ . '/Model/DemandeAchatVilla.php';

// Réponse par défaut en cas d'erreur non spécifique
$response = ['success' => false, 'error' => 'Erreur serveur inattendue.'];

// Vérifier si la requête est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['error'] = 'Méthode non autorisée.';
    http_response_code(405); // Method Not Allowed
    echo json_encode($response);
    exit;
}

// Récupérer les données JSON envoyées par fetch
$input_data = json_decode(file_get_contents('php://input'), true);

// Vérifier si les données JSON ont été correctement décodées
if ($input_data === null && json_last_error() !== JSON_ERROR_NONE) {
    $response['error'] = 'Données JSON invalides reçues.';
    http_response_code(400); // Bad Request
    echo json_encode($response);
    exit;
}

// Nettoyer et récupérer les données nécessaires
$id_villa            = filter_var($input_data['id_villa'] ?? null, FILTER_VALIDATE_INT);
$villa_nom           = htmlspecialchars($input_data['villa_nom'] ?? '', ENT_QUOTES, 'UTF-8');
$villa_type          = htmlspecialchars($input_data['villa_type'] ?? '', ENT_QUOTES, 'UTF-8');
$nom_demandeur       = htmlspecialchars($input_data['nom_demandeur'] ?? '', ENT_QUOTES, 'UTF-8');
$email_demandeur     = filter_var($input_data['email_demandeur'] ?? '', FILTER_SANITIZE_EMAIL);
$telephone_demandeur = htmlspecialchars($input_data['telephone_demandeur'] ?? '', ENT_QUOTES, 'UTF-8');
$message_text        = htmlspecialchars($input_data['message'] ?? '', ENT_QUOTES, 'UTF-8');

// Récupérer l'ID utilisateur de la session (si disponible)
session_start(); // Démarrer la session pour accéder à $_SESSION
$id_utilisateur_session = $_SESSION['user_id'] ?? null;

// --- Validation Côté Serveur ---
if (empty($nom_demandeur) || empty($email_demandeur)) {
    $response['error'] = 'Le nom et l\'email sont requis.';
    http_response_code(400);
    echo json_encode($response);
    exit;
}
if (!filter_var($email_demandeur, FILTER_VALIDATE_EMAIL)) {
    $response['error'] = 'Format de l\'email invalide.';
    http_response_code(400);
    echo json_encode($response);
    exit;
}
if (empty($id_villa)) {
     $response['error'] = 'ID de la villa manquant ou invalide.';
     http_response_code(400);
     echo json_encode($response);
     exit;
}
// Ajouter d'autres validations si nécessaire...

// --- Tentative d'enregistrement en BDD ---
try {
    $db = config::getConnexion();
    $demandeAchatController = new DemandeAchatController($db); // Utilise le contrôleur modifié

    $nouvelleDemande = new DemandeAchatVilla();
    $nouvelleDemande->setIdUtilisateur($id_utilisateur_session); // Peut être null
    $nouvelleDemande->setNomDemandeur($nom_demandeur);
    $nouvelleDemande->setEmailDemandeur($email_demandeur);
    $nouvelleDemande->setTelephoneDemandeur($telephone_demandeur ?: null); // Mettre null si vide
    $nouvelleDemande->setIdVilla($id_villa);
    $nouvelleDemande->setNomVilla($villa_nom);
    $nouvelleDemande->setTypeVilla($villa_type);
    $nouvelleDemande->setMessage($message_text ?: null); // Mettre null si vide
    $nouvelleDemande->setStatutDemande('Nouvelle');

    // Appeler la méthode qui NE FAIT QUE sauvegarder en BDD
    if ($demandeAchatController->ajouterDemandeAchat($nouvelleDemande)) {

        // --- *** NOUVEAU : Envoi de l'email de confirmation à l'utilisateur *** ---
        $to = $email_demandeur;
        $subject = "Confirmation de votre demande d'information - TuniFy Village";
        $from_email = "no-reply@tunifyvillage.com"; // **METTRE VOTRE ADRESSE EMAIL D'EXPÉDITION ICI**
        $from_name = "TuniFy Village";

        // Message HTML simple (vous pouvez le rendre plus complexe)
        $email_body = "
        <html>
        <head><title>$subject</title></head>
        <body style='font-family: sans-serif; background-color: #f4f4f4; padding: 20px;'>
            <div style='background-color: #ffffff; padding: 30px; border-radius: 5px; max-width: 600px; margin: auto; border: 1px solid #ddd;'>
                <h2 style='color: #c9a86c;'>Bonjour " . htmlspecialchars($nom_demandeur) . ",</h2>
                <p>Nous avons bien reçu votre demande d'information concernant la <strong>Villa " . htmlspecialchars($villa_nom) . " (" . htmlspecialchars($villa_type) . ")</strong>.</p>
                <p>Notre équipe commerciale vous contactera prochainement pour répondre à vos questions.</p>
                <p>Merci de votre intérêt pour TuniFy Village.</p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='font-size: 0.9em; color: #777;'>Ceci est un email automatique, merci de ne pas y répondre.</p>
            </div>
        </body>
        </html>";

        // Headers pour email HTML
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: $from_name <$from_email>" . "\r\n";
        // Optionnel: Reply-To, CC, BCC
        // $headers .= "Reply-To: contact@tunifyvillage.com" . "\r\n";
        // $headers .= "Cc: sales@tunifyvillage.com" . "\r\n";

        // Tentative d'envoi avec mail()
        // @ Supprime les erreurs PHP si mail() échoue, on vérifie le retour
        if (@mail($to, $subject, $email_body, $headers)) {
            // Email envoyé avec succès (selon la fonction mail(), pas de garantie de livraison)
            $response = ['success' => true, 'email_status' => 'sent'];
            http_response_code(200); // OK
        } else {
            // Échec de l'envoi de l'email via mail()
            error_log("ERREUR: Échec de l'envoi de l'email de confirmation à $to via mail(). Vérifier la configuration du serveur mail.");
            // La sauvegarde BDD a réussi, mais l'email a échoué. On retourne quand même succès globalement,
            // mais on peut ajouter une note sur l'échec de l'email si besoin.
            $response = ['success' => true, 'email_status' => 'failed', 'warning' => 'La demande a été enregistrée, mais l\'email de confirmation n\'a pas pu être envoyé.'];
            http_response_code(200); // Toujours OK car la BDD est OK
        }
        // --- *** FIN Envoi Email *** ---

    } else {
        // L'erreur spécifique est loggée dans le contrôleur
        $response['error'] = 'Erreur lors de l\'enregistrement dans la base de données.';
        http_response_code(500); // Internal Server Error
    }

} catch (PDOException $e) {
    error_log("PDOException dans enregistrer_demande_ajax.php: " . $e->getMessage());
    $response['error'] = 'Erreur base de données.';
    http_response_code(500);
} catch (Exception $e) {
    error_log("Exception dans enregistrer_demande_ajax.php: " . $e->getMessage());
    $response['error'] = 'Erreur serveur générale.';
    http_response_code(500);
}

// Retourner la réponse JSON
echo json_encode($response);
exit;
?>
