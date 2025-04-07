const Reservation = require('../Models/ReservationModel');

class ReservationController {
    constructor() {
        this.reservations = [];
    }

    createReservation(data) {
        const newReservation = new Reservation(this.reservations.length + 1, ...data);
        this.reservations.push(newReservation);
        return newReservation;
    }

    getReservations() {
        return this.reservations;
    }
}

module.exports = ReservationController;