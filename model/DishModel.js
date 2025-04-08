class DishModel {
    constructor() {
        this.dishes = [
            // Initial dishes data
        ];
    }

    getAllDishes() {
        return this.dishes;
    }

    addDish(dish) {
        this.dishes.push(dish);
    }

    // Additional methods for editing and deleting dishes
}