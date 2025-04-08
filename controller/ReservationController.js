const reservationModel = new ReservationModel();

function addReservation(reservation) {
    reservationModel.addReservation(reservation);
    displayReservations(reservationModel.getAllReservations());
}