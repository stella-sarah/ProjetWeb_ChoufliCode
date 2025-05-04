<?php

class Reservation {
    private $id;
    private $client_name;
    private $client_email;
    private $client_phone;
    private $reservation_date;
    private $reservation_time;
    private $guest_count;
    private $special_requests;
    private $status;
    private $created_at;
    private $updated_at;

    public function __construct($id, $client_name, $client_email, $client_phone, $reservation_date, $reservation_time, $guest_count, $special_requests = null, $status = 'pending', $created_at = null, $updated_at = null) {
        $this->id = $id;
        $this->client_name = $client_name;
        $this->client_email = $client_email;
        $this->client_phone = $client_phone;
        $this->reservation_date = $reservation_date;
        $this->reservation_time = $reservation_time;
        $this->guest_count = $guest_count;
        $this->special_requests = $special_requests;
        $this->status = $status;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getClientName() {
        return $this->client_name;
    }

    public function getClientEmail() {
        return $this->client_email;
    }

    public function getClientPhone() {
        return $this->client_phone;
    }

    public function getReservationDate() {
        return $this->reservation_date;
    }

    public function getReservationTime() {
        return $this->reservation_time;
    }

    public function getGuestCount() {
        return $this->guest_count;
    }

    public function getSpecialRequests() {
        return $this->special_requests;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    // Setters
    public function setClientName($client_name) {
        $this->client_name = $client_name;
    }

    public function setClientEmail($client_email) {
        $this->client_email = $client_email;
    }

    public function setClientPhone($client_phone) {
        $this->client_phone = $client_phone;
    }

    public function setReservationDate($reservation_date) {
        $this->reservation_date = $reservation_date;
    }

    public function setReservationTime($reservation_time) {
        $this->reservation_time = $reservation_time;
    }

    public function setGuestCount($guest_count) {
        $this->guest_count = $guest_count;
    }

    public function setSpecialRequests($special_requests) {
        $this->special_requests = $special_requests;
    }

    public function setStatus($status) {
        $this->status = $status;
    }
} 