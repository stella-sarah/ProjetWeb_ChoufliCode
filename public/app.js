document.addEventListener('DOMContentLoaded', function() {
    // Afficher les plats, réservations et commandes
    displayDishes();
    displayReservations();
    displayOrders();
});

// Simuler des données
let dishes = [
    { id: 1, name: "Couscous", price: 15.00 },
    { id: 2, name: "Brik", price: 3.50 }
];

let reservations = [
    { id: 1, customerName: "Jean Dupont", date: "2025-04-10" }
];

let orders = [
    { id: 1, customerName: "Marie Leclerc", status: "En préparation" }
];

// Fonction pour afficher les plats
function displayDishes() {
    const dishList = document.getElementById('dish-list');
    dishList.innerHTML = dishes.map(dish => <div>${dish.name} - ${dish.price} €</div>).join('');
}

// Fonction pour afficher les réservations
function displayReservations() {
    const reservationList = document.getElementById('reservation-list');
    reservationList.innerHTML = reservations.map(res => <div>${res.customerName} - ${res.date}</div>).join('');
}

// Fonction pour afficher les commandes
function displayOrders() {
    const orderList = document.getElementById('order-list');
    orderList.innerHTML = orders.map(order => <div>${order.customerName} - ${order.status}</div>).join('');
}