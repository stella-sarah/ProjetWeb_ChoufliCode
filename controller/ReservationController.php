<?php
require_once __DIR__ . '/../models/Reservation.php';

class ReservationController {
    private $model;

    public function __construct() {
        $this->model = new Reservation();
    }

    public function index() {
        $filter = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';
        $reservations = $this->model->getAll();

        if ($filter) {
            $reservations = array_filter($reservations, fn($res) => $res['status'] === $filter);
        }
        if ($search) {
            $reservations = array_filter($reservations, fn($res) => 
                stripos($res['client'], $search) !== false ||
                stripos($res['service'], $search) !== false ||
                stripos($res['id'], $search) !== false
            );
        }

        include __DIR__ . '/../views/reservations/index.php';
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
            header('Location: ' . BASE_URL . 'reservations');
            exit;
        }
    }

    public function edit($id) {
        $reservation = $this->model->getById($id);
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
            header('Location: ' . BASE_URL . 'reservations');
            exit;
        }
        include __DIR__ . '/../views/reservations/index.php';
    }

    public function delete($id) {
        $this->model->delete($id);
        header('Location: ' . BASE_URL . 'reservations');
        exit;
    }
}