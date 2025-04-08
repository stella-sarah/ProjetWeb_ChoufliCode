function displayOrders(orders) {
    const orderList = document.getElementById('order-list');
    orderList.innerHTML = ''; // Clear existing orders
    orders.forEach(order => {
        const orderDiv = document.createElement('div');
        orderDiv.className = 'item';
        orderDiv.innerHTML = `
            <h3>${order.customerName}</h3>
            <p>Statut : ${order.status}</p>
            <button onclick="editOrder(${order.id})">Modifier</button>
        `;
        orderList.appendChild(orderDiv);
    });
}