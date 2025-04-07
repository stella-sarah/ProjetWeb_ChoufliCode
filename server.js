const express = require('express');
const app = express();
const EventController = require('./Controllers/EventController');
const ReservationController = require('./Controllers/ReservationController');
const RegistrationController = require('./Controllers/RegistrationController');

const eventController = new EventController();
const reservationController = new ReservationController();
const registrationController = new RegistrationController();

app.set('view engine', 'ejs');
app.use(express.urlencoded({ extended: true }));
app.use(express.static('public'));

app.get('/events', (req, res) => {
    res.render('events', { events: eventController.getEvents() });
});

app.get('/reservations', (req, res) => {
    res.render('reservations', { reservations: reservationController.getReservations() });
});

app.get('/registrations', (req, res) => {
    res.render('registrations', { registrations: registrationController.getRegistrations() });
});

app.listen(3000, () => {
    console.log('Server is running on port 3000');
});