<?php
require_once 'Utilisateur.php';

/**
 * Classe Administrateur - Gestion des fonctionnalités admin
 */
class Administrateur extends Utilisateur {
    /**
     * Constructeur
     */
    public function __construct($data = []) {
        parent::__construct($data);
        $this->role = 'admin';
    }

    /**
     * Gestion des utilisateurs
     * @param int $userId - ID utilisateur
     * @param string $action - Action à réaliser
     */
    public function gererUtilisateurs($userId, $action) {
        $db = Database::getInstance();
        // Exemple : Changement de statut
        $stmt = $db->prepare("UPDATE utilisateurs SET statut = ? WHERE id = ?");
        $stmt->execute([$action, $userId]);
    }

    /**
     * Gestion des données système
     * @param string $table - Table cible
     */
    public function gererDonnees($table) {
        // Exemple : Export de données
        $db = Database::getInstance();
        return $db->query("SELECT * FROM $table")->fetchAll();
    }
}
?>