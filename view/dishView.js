function displayDishes(dishes) {
    const dishList = document.getElementById('dish-list');
    dishList.innerHTML = ''; // Clear existing dishes
    dishes.forEach(dish => {
        const dishDiv = document.createElement('div');
        dishDiv.className = 'item';
        dishDiv.innerHTML = `
            <h3>${dish.name}</h3>
            <p>Prix : ${dish.price} €</p>
            <button onclick="editDish(${dish.id})">Modifier</button>
        `;
        dishList.appendChild(dishDiv);
    });
}