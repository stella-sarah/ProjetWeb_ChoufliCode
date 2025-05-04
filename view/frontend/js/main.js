document.getElementById('reservationForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Empêche le rechargement de la page
    
    // Récupération des valeurs
    const reservationData = {
        clientName: document.getElementById('clientName').value,
        date: document.getElementById('reservationDate').value,
        time: document.getElementById('reservationTime').value,
        guests: document.getElementById('guestCount').value,
        requests: document.getElementById('specialRequests').value
    };
    
    // Validation supplémentaire
    if (!reservationData.clientName || !reservationData.date || !reservationData.time || !reservationData.guests) {
        alert('Veuillez remplir tous les champs obligatoires');
        return;
    }
    
    // Envoi des données (simulation)
    console.log('Données de réservation:', reservationData);
    alert('Réservation créée avec succès!\n\n' + 
          `Client: ${reservationData.clientName}\n` +
          `Date: ${reservationData.date}\n` +
          `Heure: ${reservationData.time}\n` +
          `Personnes: ${reservationData.guests}`);
    
    // Réinitialisation du formulaire
    this.reset();
});