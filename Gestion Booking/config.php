<?php
// config.php - Configuration de la base de données

// !!! VÉRIFIEZ ICI : Y a-t-il une ligne comme celle-ci SANS _once ?
// require 'Controllor/ReservationSejourController.php'; // Exemple incorrect
// require_once __DIR__ . '/Controllor/ReservationSejourController.php'; // Exemple correct si besoin (mais peu probable ici)

class config {
    private static $pdo = null;

    public static function getConnexion() {
        if (!isset(self::$pdo)) {
            // --- Configuration de la base de données ---
            $host = 'localhost';
            $dbname = 'tunify';
            $username = 'root';
            $password = '';
            // --- Fin Configuration ---

            try {
                self::$pdo = new PDO(
                    "mysql:host=$host;dbname=$dbname",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
                self::$pdo->exec("SET NAMES 'utf8mb4'");

            } catch (PDOException $e) {
                error_log("Erreur de connexion à la base de données: " . $e->getMessage());
                die('Erreur de connexion à la base de données.');
            }
        }
        return self::$pdo;
    }
}
?>
