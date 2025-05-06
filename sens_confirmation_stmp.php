<?php

require_once '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Récupérer les données du formulaire
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $reservation_details = htmlspecialchars(trim($_POST['reservation_details']));

    // Vérifier si l'e-mail est valide
    if (!$email) {
        die("Adresse e-mail invalide.");
    }

    // Initialiser PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Remplacez par votre serveur SMTP (ex. smtp.gmail.com, smtp.sendgrid.net)
        $mail->SMTPAuth = true;
        $mail->Username = 'louatimal3k@gmail.com'; // Votre adresse e-mail SMTP
        $mail->Password = 'ykuxnbwmoesfdyax'; // Mot de passe d'application ou mot de passe SMTP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587; // Port SMTP (587 pour TLS, 465 pour SSL)

        // Expéditeur et destinataire
        $mail->setFrom('no-reply@votre-site.com', 'Réservations');
        $mail->addAddress($email, $name);
        $mail->addReplyTo('support@votre-site.com', 'Support');

        // Contenu de l'e-mail
        $mail->isHTML(false); // Utiliser du texte brut (true pour HTML)
        $mail->Subject = "Confirmation de votre réservation";
        $mail->Body = "Bonjour $name,\n\nMerci pour votre réservation !\n\nDétails de votre réservation :\n$reservation_details\n\nNous vous confirmerons bientôt les prochaines étapes. Si vous avez des questions, n'hésitez pas à nous contacter.\n\nCordialement,\nL'équipe de réservation";

        // Envoyer l'e-mail
        $mail->send();
        header("Location: confirmation.html");
        exit();
    } catch (Exception $e) {
        echo "Erreur lors de l'envoi de l'e-mail : {$mail->ErrorInfo}";
    }
}

if (!function_exists('sendConfirmationEmail')) {
    function sendConfirmationEmail($to, $name, $reservation_details) {
        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        $subject = "Confirmation de votre réservation";
        
        // Email headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: TuniFy Village <louatimal3k@gmail.com>' . "\r\n";
        $headers .= 'Reply-To: louatimal3k@gmail.com' . "\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
        
        // Email body
        $message = "
        <html>
        <head>
            <title>Confirmation de Réservation</title>
        </head>
        <body style='background-color: #1c1c1c; color: #f8f5eb; font-family: Arial, sans-serif; margin: 0; padding: 0;'>
            <table style='width: 100%; max-width: 600px; margin: 20px auto; background-color: #2a2a2a; border-radius: 8px; border: 1px solid #c9a86c;'>
                <tr>
                    <td style='padding: 30px; text-align: center;'>
                        <h2 style='color: #c9a86c; font-family: \"Cinzel\", serif; font-size: 28px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px;'>Confirmation de Réservation</h2>
                        <p style='font-size: 16px; line-height: 1.6; margin-bottom: 15px;'>Bonjour <span style='color: #c9a86c; font-weight: bold;'>$name</span>,</p>
                        <p style='font-size: 16px; line-height: 1.6; margin-bottom: 15px;'>Merci pour votre réservation chez <span style='color: #c9a86c;'>TuniFy Village</span> !</p>
                        <p style='font-size: 16px; line-height: 1.6; margin-bottom: 15px; border-left: 3px solid #c9a86c; padding-left: 15px; text-align: left;'>Détails de votre réservation :<br>$reservation_details</p>
                        <p style='font-size: 16px; line-height: 1.6; margin-bottom: 15px;'>Nous vous confirmerons bientôt les prochaines étapes. Si vous avez des questions, n'hésitez pas à nous contacter à <a href='mailto:support@tunifyvillage.com' style='color: #c9a86c; text-decoration: none; font-weight: bold;'>support@tunifyvillage.com</a>.</p>
                        <p style='font-size: 16px; line-height: 1.6; margin-top: 30px; text-align: center;'>Cordialement,<br><span style='color: #c9a86c; font-weight: bold; font-size: 18px;'>L'équipe TuniFy Village</span></p>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
        
        // Log attempt
        error_log("Attempting to send email to: $to");
        
        // Try to send email
        $mail_sent = mail($to, $subject, $message, $headers);
        
        if (!$mail_sent) {
            error_log("Failed to send email to: $to");
            error_log("Last error: " . error_get_last()['message']);
            return false;
        }
        
        error_log("Email sent successfully to: $to");
        return true;
    }
}
?>