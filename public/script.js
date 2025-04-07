document.addEventListener('DOMContentLoaded', function() {
    console.log("Le document est prêt!");

    // Exemple d'une alerte lors du chargement de la page
    alert("Bienvenue sur TuniFy Village!");

    // Fonction pour gérer le défilement de la page et ajouter une classe à l'en-tête
    window.onscroll = function() {
        var header = document.querySelector('.header');
        if (window.pageYOffset > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    };

    // Ajout d'un gestionnaire d'événements pour les boutons de réservation (exemple)
    const reservationButtons = document.querySelectorAll('.reservation-button');
    reservationButtons.forEach(button => {
        button.addEventListener('click', function() {
            alert("Réservation effectuée !");
        });
    });


});