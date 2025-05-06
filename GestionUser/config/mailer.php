<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function configureMailer($mailer) {
    // SMTP settings (update these with your actual SMTP details)
    $mailer->isSMTP();
    $mailer->Host = 'smtp.gmail.com'; // e.g., 'smtp.gmail.com'
    $mailer->SMTPAuth = true;
    $mailer->Username = 'sarahbenaziza03@gmail.com'; // Your SMTP username
    $mailer->Password = 'gyag rxrw qufj ccke'; // Your SMTP password
    $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mailer->Port = 587; // TCP port (587 for TLS, 465 for SSL)

    // Character set
    $mailer->CharSet = 'UTF-8';
}
?>