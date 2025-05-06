<?php
require_once __DIR__ . '/../models/Inscription.php';

class InscriptionController {
    private $model;

    public function __construct() {
        $this->model = new Inscription();
    }

    public function index() {
        $filter = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';
        $inscriptions = $this->model->getAll();

        if ($filter) {
            $inscriptions = array_filter($inscriptions, fn($ins) => $ins['status'] === $filter);
        }
        if ($search) {
            $inscriptions = array_filter($inscriptions, fn($ins) => 
                stripos($ins['client'], $search) !== false ||
                stripos($ins['service'], $search) !== false ||
                stripos($ins['id'], $search) !== false
            );
        }

        include __DIR__ . '/../views/inscriptions/index.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'client' => $_POST['client'],
                'service' => $_POST['service'],
                'date' => $_POST['date'],
                'time' => $_POST['time'],
                'status' => $_POST['status'],
                'notes' => $_POST['notes'] ?? ''
            ];
            $this->model->create($data);
            header('Location: ' . BASE_URL . 'inscriptions');
            exit;
        }
    }

    public function edit($id) {
        $inscription = $this->model->getById($id);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'client' => $_POST['client'],
                'date' => $_POST['date'],
                'endDate' => $_POST['endDate'] ?? '',
                'time' => $_POST['time'],
                'status' => $_POST['status'],
                'notes' => $_POST['notes'] ?? ''
            ];
            $this->model->update($id, $data);
            header('Location: ' . BASE_URL . 'inscriptions');
            exit;
        }
        include __DIR__ . '/../views/inscriptions/index.php';
    }

    public function delete($id) {
        $this->model->delete($id);
        header('Location: ' . BASE_URL . 'inscriptions');
        exit;
    }
}