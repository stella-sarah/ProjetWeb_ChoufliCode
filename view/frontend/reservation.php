<?php
$page_title = 'Le Bistrot - Réservation';
$page_header = false;

require_once __DIR__ . '/../../controller/ReservationC.php';
$reservationC = new ReservationC();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['client_name'], $_POST['client_email'], $_POST['client_phone'], 
              $_POST['reservation_date'], $_POST['reservation_time'], $_POST['guest_count'])) {
        
        $client_name = $_POST['client_name'];
        $client_email = $_POST['client_email'];
        $client_phone = $_POST['client_phone'];
        $reservation_date = $_POST['reservation_date'];
        $reservation_time = $_POST['reservation_time'];
        $guest_count = (int)$_POST['guest_count'];
        $special_requests = $_POST['special_requests'] ?? '';

        // Check availability
        if ($reservationC->verifierDisponibilite($reservation_date, $reservation_time, $guest_count)) {
            $id_reservation = $reservationC->ajouterReservation(
                $client_name,
                $client_email,
                $client_phone,
                $reservation_date,
                $reservation_time,
                $guest_count,
                $special_requests
            );
            
            if ($id_reservation) {
                $message = "Votre réservation a été effectuée avec succès! Numéro de réservation: " . $id_reservation;
                $message_type = "success";
            } else {
                $message = "Une erreur est survenue lors de la réservation. Veuillez réessayer.";
                $message_type = "error";
            }
        } else {
            $message = "Désolé, il n'y a plus de place disponible pour cette date et heure.";
            $message_type = "error";
        }
    } else {
        $message = "Veuillez remplir tous les champs obligatoires.";
        $message_type = "error";
    }
}

ob_start();
?>

<!-- Reservation Header -->
<section class="reservation-header">
    <div class="container">
        <div class="reservation-header-content" data-aos="fade-up">
            <p class="section-subtitle">Réservation</p>
            <h2 class="section-title">Réservez Votre <span>Table</span></h2>
        </div>
    </div>
</section>

<!-- Reservation Form -->
<section class="reservation-form">
    <div class="container">
    <div class="reservation-container">
    <div class="form-content-container">
        <?php if (isset($message)): ?>
            <div class="alert-container full-width">
                <div class="alert alert-<?php echo $message_type; ?>" data-aos="fade-up">
                    <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            </div>
        <?php endif; ?>

            <form method="POST" class="form-grid" data-aos="fade-up">
                <div class="form-group">
                    <label class="form-label" for="client_name">
                        <i class="fas fa-user"></i>
                        Nom Complet
                    </label>
                    <input type="text" id="client_name" name="client_name" class="form-control" required
                           placeholder="Votre nom complet">
                </div>

                <div class="form-group">
                    <label class="form-label" for="client_email">
                        <i class="fas fa-envelope"></i>
                        Email
                    </label>
                    <input type="email" id="client_email" name="client_email" class="form-control" required
                           placeholder="votre@email.com">
                </div>

                <div class="form-group">
                    <label class="form-label" for="client_phone">
                        <i class="fas fa-phone"></i>
                        Téléphone
                    </label>
                    <input type="tel" id="client_phone" name="client_phone" class="form-control" required
                           placeholder="Votre numéro de téléphone">
                </div>

                <div class="form-group">
                    <label class="form-label" for="reservation_date">
                        <i class="fas fa-calendar"></i>
                        Date
                    </label>
                    <input type="date" id="reservation_date" name="reservation_date" class="form-control" required
                           min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="reservation_time">
                        <i class="fas fa-clock"></i>
                        Heure
                    </label>
                    <input type="time" id="reservation_time" name="reservation_time" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="guest_count">
                        <i class="fas fa-users"></i>
                        Nombre de Personnes
                    </label>
                    <div class="quantity-input">
                        <button type="button" class="quantity-btn minus">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" id="guest_count" name="guest_count" class="form-control" 
                               value="2" min="1" max="10" required>
                        <button type="button" class="quantity-btn plus">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="special_requests">
                        <i class="fas fa-comment"></i>
                        Notes Spéciales
                    </label>
                    <textarea id="special_requests" name="special_requests" class="form-control" rows="4" 
                              placeholder="Allergies, occasions spéciales, préférences de placement, etc."></textarea>
                </div>

                <div class="form-actions full-width">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i>
                        Confirmer la Réservation
                    </button>
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i>
                        Retour à l'Accueil
                    </a>
                </div>
            </form>

            <div class="info-cards" data-aos="fade-up" data-aos-delay="100">
                <div class="info-card">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Horaires d'Ouverture</h3>
                    <ul>
                        <li>
                            <span class="day">Lundi - Vendredi</span>
                            <span class="hours">11h - 23h</span>
                        </li>
                        <li>
                            <span class="day">Samedi - Dimanche</span>
                            <span class="hours">10h - 00h</span>
                        </li>
                    </ul>
                </div>

                <div class="info-card">
                    <div class="info-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <h3>Contact Direct</h3>
                    <p>Pour toute question ou réservation urgente</p>
                    <a href="tel:+21600000000" class="contact-link">
                        <i class="fas fa-phone"></i>
                        +216 00 000 000
                    </a>
                </div>

                <div class="info-card">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Notre Adresse</h3>
                    <p>123 Rue Example<br>Ville, Région<br>Code Postal</p>
                    <a href="#" class="contact-link">
                        <i class="fas fa-directions"></i>
                        Obtenir l'itinéraire
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();

$additional_css = '
<style>
    /* Reservation Header */
    .reservation-header {
        background-color: var(--secondary-dark);
        padding: 60px 0;
        margin-bottom: 50px;
        text-align: center;
    }

    /* Reservation Form */
    .reservation-form {
        padding: 0 0 100px;
    }

    .reservation-container {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 50px;
        align-items: start;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
        background-color: var(--secondary-dark);
        padding: 40px;
        border-radius: 10px;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .quantity-input {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .quantity-input .form-control {
        width: 80px;
        text-align: center;
    }

    .quantity-btn {
        width: 40px;
        height: 40px;
        border: 1px solid var(--border-color);
        background-color: var(--secondary-dark);
        color: var(--text-primary);
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .quantity-btn:hover {
        background-color: var(--hover-dark);
        border-color: var(--accent-color);
    }

    .form-actions {
        display: flex;
        gap: 20px;
        justify-content: flex-end;
        margin-top: 20px;
    }

    /* Info Cards */
    .info-cards {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .info-card {
        background-color: var(--secondary-dark);
        padding: 30px;
        border-radius: 10px;
        text-align: center;
    }

    .info-icon {
        width: 60px;
        height: 60px;
        background: rgba(212, 175, 55, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .info-icon i {
        font-size: 24px;
        color: var(--accent-color);
    }

    .info-card h3 {
        color: var(--text-primary);
        font-size: 20px;
        margin-bottom: 15px;
        font-family: "Playfair Display", serif;
    }

    .info-card p {
        color: var(--text-secondary);
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 15px;
    }

    .info-card ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .info-card ul li {
        display: flex;
        justify-content: space-between;
        color: var(--text-secondary);
        font-size: 14px;
        padding: 10px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .info-card ul li:last-child {
        border-bottom: none;
    }

    .contact-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--accent-color);
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .contact-link:hover {
        color: var(--accent-color-hover);
        transform: translateY(-2px);
    }

    @media (max-width: 1024px) {
        .reservation-container {
            grid-template-columns: 1fr;
        }

        .info-cards {
            flex-direction: row;
            flex-wrap: wrap;
        }

        .info-card {
            flex: 1;
            min-width: 300px;
        }
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions .btn {
            width: 100%;
        }

        .info-cards {
            flex-direction: column;
        }

        .info-card {
            width: 100%;
        }
    }

    /* Alert Styles */
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .alert i {
        font-size: 20px;
    }

    .alert-success {
        background-color: var(--success-color);
        color: var(--text-primary);
    }

    .alert-error {
        background-color: var(--error-color);
        color: var(--text-primary);
    }

    /* Form Icons */
    .form-label i {
        color: var(--accent-color);
        width: 20px;
    }
</style>';

$additional_js = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Guest count adjustment
    const guestInput = document.getElementById("guest_count");
    const minusBtn = guestInput.parentElement.querySelector(".minus");
    const plusBtn = guestInput.parentElement.querySelector(".plus");

    minusBtn.addEventListener("click", () => {
        const currentValue = parseInt(guestInput.value);
        if (currentValue > parseInt(guestInput.min)) {
            guestInput.value = currentValue - 1;
        }
    });

    plusBtn.addEventListener("click", () => {
        const currentValue = parseInt(guestInput.value);
        if (currentValue < parseInt(guestInput.max)) {
            guestInput.value = currentValue + 1;
        }
    });

    // Set minimum time based on current time
    const timeInput = document.getElementById("reservation_time");
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, "0");
    const minutes = String(now.getMinutes()).padStart(2, "0");
    
    const dateInput = document.getElementById("reservation_date");
    const today = now.toISOString().split("T")[0];
    
    if (dateInput.value === today) {
        timeInput.min = `${hours}:${minutes}`;
    }

    dateInput.addEventListener("change", function() {
        if (this.value === today) {
            timeInput.min = `${hours}:${minutes}`;
        } else {
            timeInput.min = "";
        }
    });
});
</script>';

require_once "layout.php";
?>