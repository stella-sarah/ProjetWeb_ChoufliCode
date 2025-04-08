<?php
/**
 * Singleton de connexion à la base de données
 */
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $config = require 'config/db.php';
        $this->connection = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']}", 
            $config['user'], 
            $config['pass']
        );
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance() {
        if(!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }
}
?>