// server.js
const express = require('express');
const path = require('path');
const dishModel = require('./model/DishModel');
const reservationModel = require('./model/ReservationModel'); // Import ReservationModel
const orderModel = require('./model/OrderModel'); // Import OrderModel

const app = express();
app.use(express.json());
app.use(express.static('public'));

// Dishes Routes
app.get('/api/dishes', (req, res) => {
    res.json(dishModel.getAllDishes());
});

// Reservations Routes
app.get('/api/reservations', (req, res) => {
    res.json(reservationModel.getAllReservations());
});

app.post('/api/reservations', (req, res) => {
    const newReservation = req.body;
    reservationModel.addReservation(newReservation);
    res.status(201).json(newReservation);
});

app.put('/api/reservations/:id', (req, res) => {
    const id = parseInt(req.params.id);
    const updatedReservation = req.body;
    reservationModel.updateReservation(id, updatedReservation);
    res.json(updatedReservation);
});

app.delete('/api/reservations/:id', (req, res) => {
    const id = parseInt(req.params.id);
    reservationModel.deleteReservation(id);
    res.status(204).send();
});

// Orders Routes
app.get('/api/orders', (req, res) => {
    res.json(orderModel.getAllOrders());
});

app.post('/api/orders', (req, res) => {
    const newOrder = req.body;
    orderModel.addOrder(newOrder);
    res.status(201).json(newOrder);
});

app.put('/api/orders/:id', (req, res) => {
    const id = parseInt(req.params.id);
    const updatedOrder = req.body;
    orderModel.updateOrder(id, updatedOrder);
    res.json(updatedOrder);
});

app.delete('/api/orders/:id', (req, res) => {
    const id = parseInt(req.params.id);
    orderModel.deleteOrder(id);
    res.status(204).send();
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}!`);
});