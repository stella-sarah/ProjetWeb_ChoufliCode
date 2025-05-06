// Fonction pour charger les réservations
function loadReservations() {
    fetch('get_reservations.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tbody = document.querySelector('#reservationsTable tbody');
                tbody.innerHTML = '';
                
                data.reservations.forEach(reservation => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${reservation.client}</td>
                        <td>${reservation.service}</td>
                        <td>${reservation.date}</td>
                        <td>${reservation.status}</td>
                        <td>${reservation.notes || ''}</td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="editReservation(${reservation.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteReservation(${reservation.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showAlert('Erreur lors du chargement des réservations', 'danger');
        });
}

// Fonction pour éditer une réservation
function editReservation(id) {
    fetch(`get_reservation.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const reservation = data.reservation;
                document.getElementById('reservationId').value = reservation.id;
                document.getElementById('client').value = reservation.client;
                document.getElementById('service').value = reservation.service;
                document.getElementById('date').value = reservation.date;
                document.getElementById('status').value = reservation.status;
                document.getElementById('notes').value = reservation.notes || '';
                
                // Afficher le modal
                const modal = new bootstrap.Modal(document.getElementById('reservationModal'));
                modal.show();
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showAlert('Erreur lors du chargement de la réservation', 'danger');
        });
}

// Fonction pour supprimer une réservation
function deleteReservation(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        
        fetch('process_reservation.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                loadReservations();
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showAlert('Erreur lors de la suppression de la réservation', 'danger');
        });
    }
}

// Fonction pour sauvegarder une réservation
function saveReservation() {
    const formData = new FormData(document.getElementById('reservationForm'));
    const id = document.getElementById('reservationId').value;
    
    if (id) {
        formData.append('id', id);
    }
    
    fetch('process_reservation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            loadReservations();
            const modal = bootstrap.Modal.getInstance(document.getElementById('reservationModal'));
            modal.hide();
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showAlert('Erreur lors de la sauvegarde de la réservation', 'danger');
    });
}

// Fonction pour afficher les alertes
function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Charger les réservations au chargement de la page
document.addEventListener('DOMContentLoaded', loadReservations); 