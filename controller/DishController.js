const dishModel = new DishModel();

function addDish(dish) {
    dishModel.addDish(dish);
    displayDishes(dishModel.getAllDishes());
}