function displayReservations(reservations) {
    const reservationList = document.getElementById('reservation-list');
    reservationList.innerHTML = ''; // Clear existing reservations
    reservations.forEach(reservation => {
        const reservationDiv = document.createElement('div');
        reservationDiv.className = 'item';
        reservationDiv.innerHTML = `
            <h3>${reservation.customerName}</h3>
            <p>Date : ${reservation.date}</p>
            <button onclick="editReservation(${reservation.id})">Modifier</button>
        `;
        reservationList.appendChild(reservationDiv);
    });
}