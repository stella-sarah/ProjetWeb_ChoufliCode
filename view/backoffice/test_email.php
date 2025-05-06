<?php
require_once 'email_config.php';

try {
    $emailConfig = EmailConfig::getInstance();
    $result = $emailConfig->sendReservationStatusEmail(
        'azizlatrache2@gmail.com', // Test recipient email
        'Test User',
        'Test Event',
        date('d M Y H:i'),
        'accepted'
    );
    
    if ($result) {
        echo "Email sent successfully! Check your inbox and spam folder.";
    } else {
        echo "Email sending failed! Check the email_debug.log file for details.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    echo "<br>Check the email_debug.log file for more details.";
}
?> 