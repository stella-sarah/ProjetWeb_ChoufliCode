<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require __DIR__ . '/../../vendor/autoload.php';

class EmailConfig {
    private static $instance = null;
    private $mailer;

    // Email configuration - Update these values with your email details
    private $config = [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 465, // Changed to port 465 for SSL
        'smtp_username' => 'azizlatrache2@gmail.com',
        'smtp_password' => 'sfia uehy aolb xbtb',
        'from_email' => 'azizlatrache2@gmail.com',
        'from_name' => 'TuniFy Events'
    ];

    private function __construct() {
        try {
            $this->mailer = new PHPMailer(true);
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['smtp_host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['smtp_username'];
            $this->mailer->Password = $this->config['smtp_password'];
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Changed to SMTPS
            $this->mailer->Port = $this->config['smtp_port'];
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            $this->mailer->CharSet = 'UTF-8';
            
            // Enable debug mode and log to file
            $this->mailer->SMTPDebug = 3; // More detailed debug output
            $this->mailer->Debugoutput = function($str, $level) {
                $logFile = __DIR__ . '/email_debug.log';
                $timestamp = date('Y-m-d H:i:s');
                file_put_contents($logFile, "[$timestamp] $str\n", FILE_APPEND);
            };

            // Additional settings for better reliability
            $this->mailer->Timeout = 60; // Increase timeout
            $this->mailer->SMTPKeepAlive = true; // Keep connection alive
            $this->mailer->Priority = 1; // High priority
        } catch (Exception $e) {
            error_log("EmailConfig initialization error: " . $e->getMessage());
            throw $e;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function sendReservationStatusEmail($toEmail, $toName, $eventTitle, $eventDate, $status) {
        try {
            $mailer = $this->mailer;
            $mailer->clearAddresses();
            $mailer->addAddress($toEmail, $toName);

            if ($status === 'accepted') {
                $mailer->Subject = "Confirmation de votre réservation - TuniFy Events";
                $mailer->Body = $this->getAcceptedEmailTemplate($toName, $eventTitle, $eventDate);
            } else {
                $mailer->Subject = "Mise à jour de votre réservation - TuniFy Events";
                $mailer->Body = $this->getRefusedEmailTemplate($toName, $eventTitle, $eventDate);
            }

            $mailer->AltBody = strip_tags($mailer->Body);
            
            // Log the attempt
            error_log("Attempting to send email to: $toEmail");
            
            if (!$mailer->send()) {
                throw new Exception("Email sending failed: " . $mailer->ErrorInfo);
            }
            
            error_log("Email sent successfully to: $toEmail");
            return true;
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            throw new Exception("Erreur lors de l'envoi de l'email : " . $e->getMessage());
        }
    }

    private function getAcceptedEmailTemplate($name, $eventTitle, $eventDate) {
        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Réservation Confirmée</h2>
                    </div>
                    <div class='content'>
                        <p>Bonjour $name,</p>
                        <p>Nous sommes ravis de vous informer que votre réservation pour l'événement <strong>$eventTitle</strong> prévu le $eventDate a été acceptée.</p>
                        <p>Vous recevrez prochainement un email avec les détails de votre réservation et les instructions pour le jour de l'événement.</p>
                        <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
                    </div>
                    <div class='footer'>
                        <p>Cordialement,<br>L'équipe TuniFy Events</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    private function getRefusedEmailTemplate($name, $eventTitle, $eventDate) {
        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #f44336; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Réservation Refusée</h2>
                    </div>
                    <div class='content'>
                        <p>Bonjour $name,</p>
                        <p>Nous regrettons de vous informer que votre réservation pour l'événement <strong>$eventTitle</strong> prévu le $eventDate n'a pas pu être acceptée.</p>
                        <p>Nous vous invitons à consulter notre calendrier d'événements pour découvrir d'autres opportunités.</p>
                        <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
                    </div>
                    <div class='footer'>
                        <p>Cordialement,<br>L'équipe TuniFy Events</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }
} 