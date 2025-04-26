<?php
// Inclure le modèle Report
include_once 'models/Report.php';

class ReportController {
    
    private $report;

    public function __construct($db) {
        $this->report = new Report($db);
    }

    // Créer un signalement
    public function createReport($content_type, $content_id, $reporter, $reason, $details) {
        $this->report->setContentType($content_type);
        $this->report->setContentId($content_id);
        $this->report->setReporter($reporter);
        $this->report->setReason($reason);
        $this->report->setDetails($details);
        
        if($this->report->create()) {
            return "Signalement créé avec succès.";
        } else {
            return "Erreur lors de la création du signalement.";
        }
    }

    // Lire tous les signalements
    public function getAllReports($status = null) {
        $reports = $this->report->readAll($status);
        return $reports;
    }

    // Lire un signalement par ID
    public function getReportById($id) {
        $this->report->setId($id);
        if($this->report->readOne()) {
            return $this->report;
        } else {
            return null;
        }
    }

    // Mettre à jour le statut d'un signalement
    public function updateReportStatus($id, $status) {
        $this->report->setId($id);
        $this->report->setStatus($status);
        
        if($this->report->updateStatus()) {
            return "Statut du signalement mis à jour avec succès.";
        } else {
            return "Erreur lors de la mise à jour du statut.";
        }
    }

    // Supprimer un signalement
    public function deleteReport($id) {
        $this->report->setId($id);
        if($this->report->delete()) {
            return "Signalement supprimé avec succès.";
        } else {
            return "Erreur lors de la suppression du signalement.";
        }
    }

    // Compter les signalements par statut
    public function countReportsByStatus($status) {
        return $this->report->countByStatus($status);
    }
}
?>
