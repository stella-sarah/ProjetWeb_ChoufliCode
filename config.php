<?php
class Config
{
    private static $pdo = null;

    public static function getConnexion()
    {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=localhost;dbname=tunify;charset=utf8mb4',
                    'root',
                    '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                    ]
                );
                
                // Vérification supplémentaire de l'encodage
                self::$pdo->exec("SET CHARACTER SET utf8mb4");
                
            } catch (PDOException $e) {
                error_log("Connection failed: " . $e->getMessage(), 3, __DIR__ . '/error.log');
                http_response_code(500);
                die(json_encode([
                    'success' => false,
                    'message' => 'Database connection failed: ' . $e->getMessage()
                ]));
            }
        }
        return self::$pdo;
    }
}
?>