const orderModel = new OrderModel();

function addOrder(order) {
    orderModel.addOrder(order);
    displayOrders(orderModel.getAllOrders());
}