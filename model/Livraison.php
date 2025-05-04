<?php

class Livraison {
    private $id;
    private $customer_name;
    private $email;
    private $city;
    private $address;
    private $plats;
    private $delivery_time;
    private $status;
    private $created_at;
    private $updated_at;

    public function __construct($id, $customer_name, $email, $city, $address, $plats, $delivery_time, $status = 'en attente', $created_at = null, $updated_at = null) {
        $this->id = $id;
        $this->customer_name = $customer_name;
        $this->email = $email;
        $this->city = $city;
        $this->address = $address;
        $this->plats = $plats;
        $this->delivery_time = $delivery_time;
        $this->status = $status;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getCustomerName() {
        return $this->customer_name;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getCity() {
        return $this->city;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getPlats() {
        return $this->plats;
    }

    public function getDeliveryTime() {
        return $this->delivery_time;
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
    public function setCustomerName($customer_name) {
        $this->customer_name = $customer_name;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setCity($city) {
        $this->city = $city;
    }

    public function setAddress($address) {
        $this->address = $address;
    }

    public function setPlats($plats) {
        $this->plats = $plats;
    }

    public function setDeliveryTime($delivery_time) {
        $this->delivery_time = $delivery_time;
    }

    public function setStatus($status) {
        $this->status = $status;
    }
} 