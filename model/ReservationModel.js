class ReservationModel {
    constructor() {
        this.reservations = [
            // Initial reservations data
        ];
    }

    getAllReservations() {
        return this.reservations;
    }

    addReservation(reservation) {
        this.reservations.push(reservation);
    }

    // Additional methods for editing and deleting reservations
}