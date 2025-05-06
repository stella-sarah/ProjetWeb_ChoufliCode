<?php
require_once 'config.php';
require_once 'email_config.php';

// Initialize PDO connection
try {
    $pdo = Config::getConnexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Get parameters
$reservation_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING);
$order = filter_input(INPUT_GET, 'order', FILTER_SANITIZE_STRING);
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);

if (!$reservation_id) {
    header("Location: dashboard.php?error=ID de réservation invalide&sort=$sort&order=$order" . ($search ? "&search=" . urlencode($search) : ""));
    exit;
}

try {
    // Fetch reservation and event details
    $stmt = $pdo->prepare("
        SELECT r.client_name, r.client_email, r.status, e.title, e.event_date 
        FROM reservations r 
        JOIN events e ON r.event_id = e.id 
        WHERE r.id = ?
    ");
    $stmt->execute([$reservation_id]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        header("Location: dashboard.php?error=Réservation introuvable&sort=$sort&order=$order" . ($search ? "&search=" . urlencode($search) : ""));
        exit;
    }

    if ($reservation['status'] !== 'pending') {
        header("Location: dashboard.php?error=La réservation n'est pas en attente&sort=$sort&order=$order" . ($search ? "&search=" . urlencode($search) : ""));
        exit;
    }

    // Update reservation status
    $stmt = $pdo->prepare("UPDATE reservations SET status = 'accepted' WHERE id = ?");
    $stmt->execute([$reservation_id]);

    // Send email notification
    $emailConfig = EmailConfig::getInstance();
    $emailConfig->sendReservationStatusEmail(
        $reservation['client_email'],
        $reservation['client_name'],
        $reservation['title'],
        date('d M Y H:i', strtotime($reservation['event_date'])),
        'accepted'
    );

    header("Location: dashboard.php?success=Réservation acceptée et email envoyé&sort=$sort&order=$order" . ($search ? "&search=" . urlencode($search) : ""));
    exit;
} catch (Exception $e) {
    header("Location: dashboard.php?error=" . urlencode($e->getMessage()) . "&sort=$sort&order=$order" . ($search ? "&search=" . urlencode($search) : ""));
    exit;
}
?>