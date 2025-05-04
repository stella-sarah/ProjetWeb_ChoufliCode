<?php

class Commande {
    private $id;
    private $customer_name;
    private $email;
    private $city;
    private $delivery_time;
    private $status;
    private $created_at;
    private $updated_at;
    private $depart_place;
    private $arrive_place;


    public function __construct($id, $customer_name, $email, $city, $delivery_time, $status = 'en préparation', $created_at = null, $updated_at = null, $depart_place = null, $arrive_place = null) {
        $this->id = $id;
        $this->customer_name = $customer_name;
        $this->email = $email;
        $this->city = $city;
        $this->delivery_time = $delivery_time;
        $this->status = $status;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
        $this->depart_place = $depart_place;
        $this->arrive_place = $arrive_place;

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

    public function getDepartPlace() {
        return $this->depart_place;
    }

    public function getArrivePlace() {
        return $this->arrive_place;
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

    public function setDeliveryTime($delivery_time) {
        $this->delivery_time = $delivery_time;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setDepartPlace($depart_place) {
        $this->depart_place = $depart_place;
    }

    public function setArrivePlace($arrive_place) {
        $this->arrive_place = $arrive_place;
    }
} 