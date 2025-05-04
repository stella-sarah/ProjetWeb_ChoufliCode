// Gestion de la connexion admin
document.getElementById('loginForm').addEventListener('submit', (e) => {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    // Validation simple (à remplacer par une vraie authentification)
    if(username === 'admin' && password === 'admin123') {
        window.location.href = 'dashboard.html';
    } else {
        alert('Identifiants incorrects');
    }
});

// Fonctionnalités du dashboard
console.log('Admin dashboard chargé');