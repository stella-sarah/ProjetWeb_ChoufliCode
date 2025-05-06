function openModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = 'flex';
    modal.classList.add('modal-open');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('modal-close');
    setTimeout(() => {
        modal.style.display = 'none';
        modal.classList.remove('modal-open', 'modal-close');
        document.body.style.overflow = 'auto';
        const form = document.getElementById(modalId === 'addTransportModal' ? 'addTransportForm' : modalId === 'editTransportModal' ? 'editTransportForm' : 'editReservationForm');
        if (form) {
            form.reset();
            clearErrors(modalId === 'addTransportModal' ? 'add' : modalId === 'editTransportModal' ? 'edit' : 'editReservation');
            if (modalId === 'addTransportModal') {
                const imagePreview = document.getElementById('addTransportImagePreview');
                const imageText = document.getElementById('addTransportImageText');
                const imageHidden = document.getElementById('addTransportImageHidden');
                imagePreview.style.display = 'none';
                imagePreview.src = '';
                imageText.style.display = 'block';
                imageHidden.value = '';
            }
            if (modalId === 'editTransportModal') {
                const imagePreview = document.getElementById('editTransportImagePreview');
                const imageText = document.getElementById('editTransportImageText');
                const imageHidden = document.getElementById('editTransportImageHidden');
                imagePreview.style.display = 'none';
                imagePreview.src = '';
                imageText.style.display = 'block';
                imageHidden.value = '';
            }
        }
    }, 400);
}

function updateTransportImage(formPrefix) {
    const typeSelect = document.getElementById(`${formPrefix}TransportType`);
    const imagePreview = document.getElementById(`${formPrefix}TransportImagePreview`);
    const imageText = document.getElementById(`${formPrefix}TransportImageText`);
    const imageHidden = document.getElementById(`${formPrefix}TransportImageHidden`);

    const type = typeSelect.value;
    if (type && validTypes.includes(type)) {
        let imageFileName;
        switch (type.toLowerCase()) {
            case 'métro':
                imageFileName = 'metro.jpg';
                break;
            default:
                imageFileName = `${type.toLowerCase()}.jpg`;
        }
        const imagePath = `../../image/${imageFileName}`;
        imagePreview.src = imagePath;
        imagePreview.style.display = 'block';
        imageText.style.display = 'none';
        imageHidden.value = imageFileName;
    } else {
        imagePreview.src = '';
        imagePreview.style.display = 'none';
        imageText.style.display = 'block';
        imageHidden.value = '';
    }
}

function viewTransport(id) {
    fetch(`getTransport.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .then(transport => {
            document.getElementById('viewTransportTitle').textContent = transport.nom;
            const detailContent = document.getElementById('transportDetailContent');
            detailContent.innerHTML = `
                <div class="detail-row">
                    <div class="detail-image">
                        <img src="../../image/${transport.image}" alt="${transport.nom}" style="max-width: 200px;" onerror="this.src='../../image/default.jpg';">
                    </div>
                    <div class="detail-info">
                        <div class="detail-item">
                            <span class="detail-label">Type:</span>
                            <span class="detail-value">${transport.type}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Prix/jour:</span>
                            <span class="detail-value">${transport.prix} €</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Stock:</span>
                            <span class="detail-value">${transport.stock}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Vitesse:</span>
                            <span class="detail-value">${transport.vitesse} km/h</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Nombre de places:</span>
                            <span class="detail-value">${transport.nb_places}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Batterie:</span>
                            <span class="detail-value">${transport.batterie} kWh</span>
                        </div>
                    </div>
                </div>
                <div class="detail-actions">
                    <button class="edit-btn" onclick="editTransport(${transport.id})">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                </div>
            `;
            openModal('viewTransportModal');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du chargement des détails du transport');
        });
}

function editTransport(id) {
    fetch(`getTransport.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .then(transport => {
            document.getElementById('editTransportId').value = transport.id;
            document.getElementById('editTransportType').value = transport.type;
            document.getElementById('editTransportName').value = transport.nom;
            document.getElementById('editTransportPrice').value = transport.prix;
            document.getElementById('editTransportStock').value = transport.stock;
            document.getElementById('editTransportVitesse').value = transport.vitesse;
            document.getElementById('editTransportNbPlaces').value = transport.nb_places;
            document.getElementById('editTransportBatterie').value = transport.batterie;
            updateTransportImage('edit');
            openModal('editTransportModal');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du chargement des données du transport');
        });
}

// Helper: Geocode a city name to [lat, lon] using Nominatim
async function geocodeCity(city) {
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(city + ', Tunisia')}`;
    const response = await fetch(url);
    const data = await response.json();
    if (data && data.length > 0) {
        return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
    }
    throw new Error('Ville non trouvée: ' + city);
}

// Helper: Get route info from OSRM
async function getRouteInfo(from, to) {
    const url = `https://router.project-osrm.org/route/v1/driving/${from[1]},${from[0]};${to[1]},${to[0]}?overview=full&geometries=geojson`;
    const response = await fetch(url);
    const data = await response.json();
    if (data.routes && data.routes.length > 0) {
        return data.routes[0];
    }
    throw new Error('Route non trouvée');
}

// Enhanced: Show map in reservation modal with custom styling and features
async function showReservationMap(depart, destination) {
    const mapDiv = document.getElementById('reservationMap');
    const infoDiv = document.getElementById('routeInfo');
    // Clean up previous map if any
    if (mapDiv._leaflet_id) {
        mapDiv._leaflet_id = null;
        mapDiv.innerHTML = "";
    }
    mapDiv.style.display = 'block';
    infoDiv.style.display = 'block';

    try {
        const from = await geocodeCity(depart);
        const to = await geocodeCity(destination);
        const route = await getRouteInfo(from, to);

        // Custom icons
        const departIcon = L.icon({
            iconUrl: 'https://cdn.jsdelivr.net/gh/pointhi/leaflet-color-markers@master/img/marker-icon-green.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });
        const destIcon = L.icon({
            iconUrl: 'https://cdn.jsdelivr.net/gh/pointhi/leaflet-color-markers@master/img/marker-icon-red.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        // Init map
        let map = L.map('reservationMap', {
            zoomControl: true,
            attributionControl: false
        }).setView(from, 7);

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Draw route with custom style
        const coords = route.geometry.coordinates.map(c => [c[1], c[0]]);
        const polyline = L.polyline(coords, {
            color: '#0074D9',
            weight: 6,
            opacity: 0.8,
            dashArray: '10, 10'
        }).addTo(map);

        // Add markers with popups
        const departMarker = L.marker(from, {icon: departIcon}).addTo(map)
            .bindPopup(`<b>Départ</b><br>${depart}`).openPopup();
        const destMarker = L.marker(to, {icon: destIcon}).addTo(map)
            .bindPopup(`<b>Destination</b><br>${destination}`);

        // Fit bounds with padding
        map.fitBounds([from, to], {padding: [60, 60]});

        // Add scale
        L.control.scale().addTo(map);

        // Add attribution
        L.control.attribution({prefix: false}).addAttribution('© OpenStreetMap contributors').addTo(map);

        // Show info
        document.getElementById('routeDistance').textContent = (route.distance / 1000).toFixed(2) + ' km';
        document.getElementById('routeDuration').textContent = (route.duration / 60).toFixed(0) + ' min';

        // Optional: Animate the route line (simple fade-in)
        polyline.setStyle({opacity: 0});
        setTimeout(() => polyline.setStyle({opacity: 0.8}), 300);

    } catch (e) {
        mapDiv.style.display = 'none';
        infoDiv.style.display = 'none';
        console.error(e);
    }
}

function viewReservation(id) {
    fetch(`getReservation.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .then(reservation => {
            document.getElementById('viewReservationTitle').textContent = `Réservation de ${reservation.nom} ${reservation.prenom}`;
            const detailContent = document.getElementById('reservationDetailContent');
            detailContent.innerHTML = `
                <div class="detail-row">
                    <div class="detail-info">
                        <div class="detail-item">
                            <span class="detail-label">Nom:</span>
                            <span class="detail-value">${reservation.nom}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Prénom:</span>
                            <span class="detail-value">${reservation.prenom}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">CIN:</span>
                            <span class="detail-value">${reservation.cin}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value">${reservation.email}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Départ:</span>
                            <span class="detail-value">${reservation.depart}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Destination:</span>
                            <span class="detail-value">${reservation.destination}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date Début:</span>
                            <span class="detail-value">${reservation.datedebut}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date Fin:</span>
                            <span class="detail-value">${reservation.datefin}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Paiement:</span>
                            <span class="detail-value">${reservation.paiement.charAt(0).toUpperCase() + reservation.paiement.slice(1)}</span>
                        </div>
                    </div>
                </div>
                <div class="detail-actions">
                    <button class="edit-btn" onclick="editReservation(${reservation.id})">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                </div>
            `;
            openModal('viewReservationModal');
            // Show the map with distance/duration
            showReservationMap(reservation.depart, reservation.destination);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du chargement des détails de la réservation');
        });
}

function editReservation(id) {
    fetch(`getReservation.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .then(reservation => {
            document.getElementById('editReservationId').value = reservation.id;
            document.getElementById('editReservationNom').value = reservation.nom;
            document.getElementById('editReservationPrenom').value = reservation.prenom;
            document.getElementById('editReservationCin').value = reservation.cin;
            document.getElementById('editReservationEmail').value = reservation.email;
            document.getElementById('editReservationDepart').value = reservation.depart;
            document.getElementById('editReservationDestination').value = reservation.destination;
            document.getElementById('editReservationDateDebut').value = reservation.datedebut;
            document.getElementById('editReservationDateFin').value = reservation.datefin;
            document.getElementById('editReservationPaiement').value = reservation.paiement;
            openModal('editReservationModal');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du chargement des données de la réservation');
        });
}

function confirmDelete(type, id, name) {
    if (!id || id <= 0 || isNaN(id)) {
        alert('ID invalide pour la suppression.');
        return;
    }
    const messageElement = document.getElementById('deleteConfirmationMessage');
    messageElement.innerHTML = `Voulez-vous vraiment supprimer ${type === 'transport' ? 'le transport' : 'la réservation'} <strong>${name}</strong> ? Cette action est irréversible.`;
    openModal('deleteConfirmationModal');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    confirmBtn.onclick = function() {
        const url = type === 'transport' ? `transportback.php?action=delete&id=${id}` : `transportback.php?action=delete_reservation&id=${id}`;
        window.location.href = url;
    };
}

let currentTypeFilter = 'all';

function filterTransports(type) {
    currentTypeFilter = type;
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    event.currentTarget.classList.add('active');
    applyFiltersAndSearch();
}

function searchTransports() {
    applyFiltersAndSearch();
}

function applyFiltersAndSearch() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#transportTableBody tr');
    
    rows.forEach(row => {
        const name = row.dataset.name.toLowerCase();
        const type = row.dataset.type.toLowerCase();
        
        const matchesSearch = name.includes(input) || type.includes(input);
        const matchesType = currentTypeFilter === 'all' || type === currentTypeFilter.toLowerCase();
        
        row.style.display = matchesSearch && matchesType ? '' : 'none';
    });
}

function sortTransports(criteria) {
    const tableBody = document.getElementById('transportTableBody');
    const rows = Array.from(tableBody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        if (criteria === 'name-asc') {
            return a.dataset.name.localeCompare(b.dataset.name);
        } else if (criteria === 'name-desc') {
            return b.dataset.name.localeCompare(a.dataset.name);
        } else if (criteria === 'price-asc') {
            return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
        } else if (criteria === 'price-desc') {
            return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
        }
        return 0;
    });
    
    tableBody.innerHTML = '';
    rows.forEach(row => tableBody.appendChild(row));
    
    applyFiltersAndSearch();
}

function sortReservations(criteria) {
    const tableBody = document.getElementById('reservationTableBody');
    const rows = Array.from(tableBody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        if (criteria === 'nom-asc') {
            return a.dataset.nom.localeCompare(b.dataset.nom);
        } else if (criteria === 'nom-desc') {
            return b.dataset.nom.localeCompare(a.dataset.nom);
        } else if (criteria === 'datedebut-asc') {
            return new Date(a.dataset.datedebut) - new Date(b.dataset.datedebut);
        } else if (criteria === 'datedebut-desc') {
            return new Date(b.dataset.datedebut) - new Date(a.dataset.datedebut);
        }
        return 0;
    });
    
    tableBody.innerHTML = '';
    rows.forEach(row => tableBody.appendChild(row));
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            closeModal(modal.id);
        });
    }
}

setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (alert) alert.style.display = 'none';
    });
}, 3000);

const validTypes = ['Voiture', 'Moto', 'Trottinette', 'Bus', 'Métro', 'Bicyclette'];
const validGouvernorats = [
    "Ariana", "Béja", "Ben Arous", "Bizerte", "Gabès", "Gafsa", "Jendouba", "Kairouan",
    "Kasserine", "Kébili", "Le Kef", "Mahdia", "Manouba", "Médenine", "Monastir", "Nabeul",
    "Sfax", "Sidi Bouzid", "Siliana", "Sousse", "Tataouine", "Tozeur", "Tunis", "Zaghouan"
];
const validPaiements = ['carte', 'especes', 'virement'];

function clearErrors(formPrefix) {
    const errorElements = document.querySelectorAll(`#${formPrefix}TransportForm .error-message, #editReservationForm .error-message`);
    errorElements.forEach(el => el.textContent = '');
}

function setError(elementId, message) {
    document.getElementById(elementId).textContent = message;
}

function validateForm(formId) {
    const form = document.getElementById(formId);
    const prefix = formId === 'addTransportForm' ? 'add' : formId === 'editTransportForm' ? 'edit' : 'editReservation';
    let isValid = true;
    clearErrors(prefix);

    if (formId === 'addTransportForm' || formId === 'editTransportForm') {
        const type = form.querySelector(`#${prefix}TransportType`).value;
        if (!validTypes.includes(type)) {
            setError(`${prefix}TransportTypeError`, 'Veuillez sélectionner un type valide.');
            isValid = false;
        }

        const name = form.querySelector(`#${prefix}TransportName`).value.trim();
        if (!name || name.length > 100) {
            setError(`${prefix}TransportNameError`, 'Le nom est requis et doit être inférieur à 100 caractères.');
            isValid = false;
        }

        const priceInput = form.querySelector(`#${prefix}TransportPrice`);
        const priceValue = priceInput.value.trim();
        if (priceValue === '' || isNaN(parseFloat(priceValue))) {
            setError(`${prefix}TransportPriceError`, 'Veuillez entrer un nombre positif valide.');
            isValid = false;
        } else {
            const price = parseFloat(priceValue);
            if (price <= 0 || price > 10000) {
                setError(`${prefix}TransportPriceError`, 'Le prix doit être un nombre positif inférieur à 10 000 €.');
                isValid = false;
            }
        }

        const stockInput = form.querySelector(`#${prefix}TransportStock`);
        const stockValue = stockInput.value.trim();
        if (stockValue === '' || isNaN(parseInt(stockValue)) || !/^\d+$/.test(stockValue)) {
            setError(`${prefix}TransportStockError`, 'Veuillez entrer un nombre entier non négatif.');
            isValid = false;
        } else {
            const stock = parseInt(stockValue);
            if (stock < 0 || stock > 1000) {
                setError(`${prefix}TransportStockError`, 'Le stock doit être un nombre entre 0 et 1000.');
                isValid = false;
            }
        }

        const vitesseInput = form.querySelector(`#${prefix}TransportVitesse`);
        const vitesseValue = vitesseInput.value.trim();
        if (vitesseValue === '' || isNaN(parseFloat(vitesseValue))) {
            setError(`${prefix}TransportVitesseError`, 'Veuillez entrer une vitesse valide.');
            isValid = false;
        } else {
            const vitesse = parseFloat(vitesseValue);
            if (vitesse <= 0 || vitesse > 500) {
                setError(`${prefix}TransportVitesseError`, 'La vitesse doit être entre 0 et 500 km/h.');
                isValid = false;
            }
        }

        const nbPlacesInput = form.querySelector(`#${prefix}TransportNbPlaces`);
        const nbPlacesValue = nbPlacesInput.value.trim();
        if (nbPlacesValue === '' || isNaN(parseInt(nbPlacesValue)) || !/^\d+$/.test(nbPlacesValue)) {
            setError(`${prefix}TransportNbPlacesError`, 'Veuillez entrer un nombre entier de places.');
            isValid = false;
        } else {
            const nbPlaces = parseInt(nbPlacesValue);
            if (nbPlaces <= 0 || nbPlaces > 100) {
                setError(`${prefix}TransportNbPlacesError`, 'Le nombre de places doit être entre 1 et 100.');
                isValid = false;
            }
        }

        const batterieInput = form.querySelector(`#${prefix}TransportBatterie`);
        const batterieValue = batterieInput.value.trim();
        if (batterieValue === '' || isNaN(parseFloat(batterieValue))) {
            setError(`${prefix}TransportBatterieError`, 'Veuillez entrer une capacité de batterie valide.');
            isValid = false;
        } else {
            const batterie = parseFloat(batterieValue);
            if (batterie <= 0 || batterie > 1000) {
                setError(`${prefix}TransportBatterieError`, 'La batterie doit être entre 0 et 1000 kWh.');
                isValid = false;
            }
        }
    } else if (formId === 'editReservationForm') {
        const nom = form.querySelector('#editReservationNom').value.trim();
        if (!nom || nom.length > 100 || !/^[a-zA-Z\s\-']+$/.test(nom)) {
            setError('editReservationNomError', 'Le nom est requis, doit être inférieur à 100 caractères et ne contenir que des lettres, espaces, tirets ou apostrophes.');
            isValid = false;
        }

        const prenom = form.querySelector('#editReservationPrenom').value.trim();
        if (!prenom || prenom.length > 100 || !/^[a-zA-Z\s\-']+$/.test(prenom)) {
            setError('editReservationPrenomError', 'Le prénom est requis, doit être inférieur à 100 caractères et ne contenir que des lettres, espaces, tirets ou apostrophes.');
            isValid = false;
        }

        const cin = form.querySelector('#editReservationCin').value.trim();
        if (!cin || !/^\d{8}$/.test(cin)) {
            setError('editReservationCinError', 'Le CIN doit être un numéro de 8 chiffres.');
            isValid = false;
        }

        const email = form.querySelector('#editReservationEmail').value.trim();
        if (!email || !/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/.test(email)) {
            setError('editReservationEmailError', 'L\'email est invalide.');
            isValid = false;
        }

        const depart = form.querySelector('#editReservationDepart').value;
        if (!depart || !validGouvernorats.includes(depart)) {
            setError('editReservationDepartError', 'Le gouvernorat de départ est invalide.');
            isValid = false;
        }

        const destination = form.querySelector('#editReservationDestination').value;
        if (!destination || !validGouvernorats.includes(destination)) {
            setError('editReservationDestinationError', 'Le gouvernorat de destination est invalide.');
            isValid = false;
        } else if (depart === destination) {
            setError('editReservationDestinationError', 'Le départ et la destination ne peuvent pas être identiques.');
            isValid = false;
        }

        const datedebut = form.querySelector('#editReservationDateDebut').value;
        const today = new Date().toISOString().split('T')[0];
        if (!datedebut || datedebut < today) {
            setError('editReservationDateDebutError', 'La date de début est requise et ne peut pas être dans le passé.');
            isValid = false;
        }

        const datefin = form.querySelector('#editReservationDateFin').value;
        if (!datefin || datefin <= datedebut) {
            setError('editReservationDateFinError', 'La date de fin doit être postérieure à la date de début.');
            isValid = false;
        }

        const paiement = form.querySelector('#editReservationPaiement').value;
        if (!paiement || !validPaiements.includes(paiement)) {
            setError('editReservationPaiementError', 'Le mode de paiement est invalide.');
            isValid = false;
        }
    }

    return isValid;
}

document.getElementById('addTransportForm').addEventListener('submit', function(e) {
    if (!validateForm('addTransportForm')) {
        e.preventDefault();
    }
});

document.getElementById('editTransportForm').addEventListener('submit', function(e) {
    if (!validateForm('editTransportForm')) {
        e.preventDefault();
    }
});

document.getElementById('editReservationForm').addEventListener('submit', function(e) {
    if (!validateForm('editReservationForm')) {
        e.preventDefault();
    }
});