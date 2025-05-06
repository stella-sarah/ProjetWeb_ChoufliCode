<?php
require_once 'config.php';

// Initialize PDO connection
try {
    $pdo = Config::getConnexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Handle sorting for events
$valid_sort_columns = ['id', 'title', 'event_date', 'venue', 'max_capacity'];
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING);
$order = filter_input(INPUT_GET, 'order', FILTER_SANITIZE_STRING);
$sort = in_array($sort, $valid_sort_columns) ? $sort : 'event_date';
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

// Handle search for events
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
$search_query = $search ? trim($search) : '';

// Build the SQL query for events
$sql = "SELECT id, title, event_date, venue, max_capacity FROM events";
$params = [];

if ($search_query) {
    $sql .= " WHERE id LIKE ? OR title LIKE ? OR venue LIKE ? OR description LIKE ?";
    $search_param = "%$search_query%";
    $params = [$search_param, $search_param, $search_param, $search_param];
}

$sql .= " ORDER BY $sort $order";

// Fetch events
$events = [];
$eventVisitors = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($events as $event) {
        $stmt = $pdo->prepare("SELECT client_name, client_email, reservation_date, status FROM reservations WHERE event_id = ?");
        $stmt->execute([$event['id']]);
        $eventVisitors[$event['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des événements : " . $e->getMessage();
}

// Fetch reservations
$reservations = [];
try {
    $sql = "SELECT r.id, r.client_name, r.client_email, r.event_id, r.reservation_date, r.status, e.title AS event_title 
            FROM reservations r 
            JOIN events e ON r.event_id = e.id 
            ORDER BY r.reservation_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des réservations : " . $e->getMessage();
}

// Handle form submission for adding an event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $event_date = filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_STRING);
    $venue = filter_input(INPUT_POST, 'venue', FILTER_SANITIZE_STRING);
    $max_capacity = filter_input(INPUT_POST, 'max_capacity', FILTER_VALIDATE_INT);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $image_url = filter_input(INPUT_POST, 'image_url', FILTER_SANITIZE_URL) ?: '';

    if ($title && $event_date && $venue && $max_capacity !== false) {
        try {
            $id = '#' . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4);
            $sql = "INSERT INTO events (id, title, event_date, venue, max_capacity, description, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id, $title, $event_date, $venue, $max_capacity, $description, $image_url]);
            header("Location: dashboard.php?success=Événement ajouté avec succès" . ($search ? "&search=" . urlencode($search) : "") . "&sort=$sort&order=$order");
            exit;
        } catch (PDOException $e) {
            header("Location: dashboard.php?error=Erreur lors de l'ajout de l'événement : " . urlencode($e->getMessage()) . "&sort=$sort&order=$order");
            exit;
        }
    } else {
        header("Location: dashboard.php?error=Erreur : Veuillez remplir tous les champs obligatoires correctement&sort=$sort&order=$order");
        exit;
    }
}

// Helper function to get sort link
function getSortLink($column, $current_sort, $current_order, $search) {
    $new_order = ($current_sort === $column && $current_order === 'ASC') ? 'DESC' : 'ASC';
    $params = ['sort' => $column, 'order' => $new_order];
    if ($search) {
        $params['search'] = $search;
    }
    return 'dashboard.php?' . http_build_query($params);
}

// Helper function to get sort icon
function getSortIcon($column, $current_sort, $current_order) {
    if ($current_sort === $column) {
        return $current_order === 'ASC' ? 'fa-sort-up' : 'fa-sort-down';
    }
    return 'fa-sort';
}

// Fetch stats for cards (example queries, adjust based on your database)
$stats = [
    'events' => 0,
    'reservations' => 0,
    'pending_reservations' => 0,
    'users' => 0
];
try {
    $stats['events'] = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $stats['reservations'] = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
    $stats['pending_reservations'] = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn();
    $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(); // Assuming a users table exists
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des statistiques : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Events - Backoffice</title>
    <link rel="stylesheet" href="../assets/css/styleback.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap');

:root {
    --gold-primary: #c9a86c;
    --gold-light: #e9d9b6;
    --gold-dark: #8b783d;
    --dark-bg: #1c1c1c;
    --darker-bg: #111111;
    --light-text: #f8f5eb;
    --sidebar-width: 280px;
    --sidebar-collapsed-width: 80px;
    --top-nav-height: 70px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Montserrat', sans-serif;

}

body {
    background-color: var(--darker-bg);
    color: var(--light-text);
    display: flex;
    min-height: 100vh;
}

/* Sidebar Styles */
.sidebar {
    width: var(--sidebar-width);
    background-color: var(--dark-bg);
    height: 100vh;
    position: fixed;
    transition: all 0.3s ease;
    z-index: 1000;
    border-right: 1px solid rgba(201, 168, 108, 0.1);
}

.sidebar.collapsed {
    width: var(--sidebar-collapsed-width);
}

.sidebar-header {
    padding: 25px;
    border-bottom: 1px solid rgba(201, 168, 108, 0.2);
}

.logo {
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

.sidebar.collapsed .logo {
    justify-content: center;
}

.logo img {
    height: 40px;
}

.logo-text {
    margin-left: 15px;
    transition: all 0.3s ease;
}

.sidebar.collapsed .logo-text {
    display: none;
}

.logo-text h1 {
    font-family: 'Cinzel', serif;
    font-weight: 700;
    font-size: 20px;
    color: var(--gold-primary);
    margin: 0;
    letter-spacing: 1px;
}

.logo-text p {
    font-size: 11px;
    font-weight: 300;
    color: var(--gold-light);
    letter-spacing: 2px;
    text-transform: uppercase;
}

.sidebar-nav {
    padding: 20px 0;
}

.sidebar-nav ul {
    list-style: none;
}

.sidebar-nav li {
    margin-bottom: 5px;
}

.sidebar-nav li a {
    display: flex;
    align-items: center;
    padding: 15px 25px;
    color: rgba(248, 245, 235, 0.7);
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 14px;
    letter-spacing: 0.5px;
}

.sidebar-nav li a:hover {
    background-color: rgba(201, 168, 108, 0.1);
    color: var(--gold-primary);
}

.sidebar-nav li.active a {
    background-color: rgba(201, 168, 108, 0.2);
    color: var(--gold-primary);
    border-left: 3px solid var(--gold-primary);
}

.sidebar-nav li i {
    font-size: 18px;
    margin-right: 15px;
    width: 20px;
    text-align: center;
}

.sidebar.collapsed .sidebar-nav li span {
    display: none;
}

.sidebar.collapsed .sidebar-nav li i {
    margin-right: 0;
    font-size: 20px;
}

/* Main Content Styles */
.main-content {
    margin-left: var(--sidebar-width);
    width: calc(100% - var(--sidebar-width));
    transition: all 0.3s ease;
    background-color: var(--darker-bg);
}

.main-content.expanded {
    margin-left: var(--sidebar-collapsed-width);
    width: calc(100% - var(--sidebar-collapsed-width));
}

/* Top Navigation */
.top-nav {
    height: var(--top-nav-height);
    background-color: var(--dark-bg);
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 30px;
    position: sticky;
    top: 0;
    z-index: 100;
    border-bottom: 1px solid rgba(201, 168, 108, 0.1);
}

.nav-left {
    display: flex;
    align-items: center;
}

.sidebar-toggle {
    background: none;
    border: none;
    font-size: 18px;
    color: var(--gold-light);
    cursor: pointer;
    margin-right: 25px;
    padding: 5px;
    transition: all 0.3s ease;
}

.sidebar-toggle:hover {
    color: var(--gold-primary);
}

.nav-left h2 {
    font-family: 'Playfair Display', serif;
    font-weight: 600;
    color: var(--gold-light);
    font-size: 22px;
}

.nav-right {
    display: flex;
    align-items: center;
}

.search-box {
    position: relative;
    margin-right: 25px;
}

.search-box input {
    padding: 10px 15px 10px 40px;
    border: 1px solid rgba(201, 168, 108, 0.3);
    border-radius: 4px;
    font-size: 14px;
    width: 220px;
    background-color: rgba(17, 17, 17, 0.5);
    color: var(--light-text);
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: var(--gold-primary);
    box-shadow: 0 0 0 2px rgba(201, 168, 108, 0.2);
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gold-light);
}

.add-btn {
    background-color: var(--gold-primary);
    color: var(--darker-bg);
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    font-weight: 500;
    cursor: pointer;
    margin-right: 20px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.add-btn:hover {
    background-color: var(--gold-light);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.user-profile {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.user-profile:hover {
    background-color: rgba(201, 168, 108, 0.1);
}

.user-profile img {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    margin-right: 10px;
    border: 2px solid var(--gold-primary);
}

.user-profile span {
    font-weight: 500;
    margin-right: 8px;
    color: var(--light-text);
}

.user-profile i {
    color: var(--gold-light);
    font-size: 12px;
}

/* Content Area */
.content-area {
    padding: 30px;
}

/* Tabs */
.tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.tab-btn {
    padding: 10px 20px;
    background-color: var(--dark-bg);
    border: 1px solid rgba(201, 168, 108, 0.3);
    color: var(--gold-light);
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
}

.tab-btn:hover {
    border-color: var(--gold-primary);
    color: var(--gold-primary);
}

.tab-btn.active {
    background-color: rgba(201, 168, 108, 0.2);
    border-color: var(--gold-primary);
    color: var(--gold-primary);
}

/* Cards */
.card {
    background-color: var(--dark-bg);
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    margin-bottom: 30px;
    border: 1px solid rgba(201, 168, 108, 0.1);
}

.card-header {
    padding: 20px 25px;
    border-bottom: 1px solid rgba(201, 168, 108, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--gold-primary);
    font-family: 'Cinzel', serif;
    letter-spacing: 1px;
}

.card-actions {
    display: flex;
    align-items: center;
    gap: 15px;
    color: #8b783d;
}

.filter-select, .period-select {
    padding: 8px 12px;
    background-color: var(--dark-bg);
    border: 1px solid rgba(201, 168, 108, 0.3);
    color: var(--light-text);
    border-radius: 4px;
    font-size: 14px;
}

.refresh-btn {
    background: none;
    border: none;
    color: var(--gold-light);
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s ease;
}

.refresh-btn:hover {
    color: var(--gold-primary);
    transform: rotate(180deg);
}

.card-body {
    padding: 25px;
}

/* Data Table */
.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    text-align: left;
    padding: 15px;
    background-color: rgba(17, 17, 17, 0.8);
    color: var(--gold-light);
    font-weight: 500;
    font-size: 14px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    border-bottom: 1px solid rgba(201, 168, 108, 0.2);
}

.data-table td {
    padding: 15px;
    border-bottom: 1px solid rgba(201, 168, 108, 0.1);
    font-size: 14px;
    color: rgba(248, 245, 235, 0.8);
}

.data-table tr:hover td {
    background-color: rgba(201, 168, 108, 0.05);
    
}

.transport-img {
    width: 50px;
    height: 30px;
    object-fit: cover;
    border-radius: 3px;
    border: 1px solid rgba(201, 168, 108, 0.3);
}

/* Status Badges */
.status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
}

.status.active {
    background-color: rgba(75, 192, 192, 0.1);
    color: #4bc0c0;
    border: 1px solid rgba(75, 192, 192, 0.3);
}

.status.maintenance {
    background-color: rgba(255, 206, 86, 0.1);
    color: #ffce56;
    border: 1px solid rgba(255, 206, 86, 0.3);
}

.status.completed {
    background-color: rgba(54, 162, 235, 0.1);
    color: #36a2eb;
    border: 1px solid rgba(54, 162, 235, 0.3);
}

.status.cancelled {
    background-color: rgba(255, 99, 132, 0.1);
    color: #ff6384;
    border: 1px solid rgba(255, 99, 132, 0.3);
}

/* Action Buttons */
.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background-color: transparent;
    color: #8b783d;
    cursor: pointer;
    margin-right: 5px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.action-btn:hover {
    background-color: rgba(201, 168, 108, 0.1);
}

.action-btn.view {
    color: #36a2eb;
}

.action-btn.edit {
    color: var(--gold-primary);
}

.action-btn.delete {
    color: #ff6384;
}

.action-btn.delete:hover {
    background-color: rgba(255, 99, 132, 0.1);
}

/* Stats Chart */
.stats-chart {
    height: 300px;
    background-color: rgba(17, 17, 17, 0.5);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px dashed rgba(201, 168, 108, 0.3);
}

.chart-placeholder {
    text-align: center;
    color: var(--gold-light);
    opacity: 0.7;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    z-index: 2000;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: var(--dark-bg);
    padding: 30px;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    position: relative;
    border: 1px solid var(--gold-primary);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.close-modal {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 28px;
    color: var(--gold-light);
    cursor: pointer;
    transition: all 0.3s ease;
}

.close-modal:hover {
    color: var(--gold-primary);
}

.modal-content h2 {
    font-family: 'Cinzel', serif;
    color: var(--gold-primary);
    margin-bottom: 25px;
    text-align: center;
}

/* Transport Form */
.transport-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    color: var(--gold-light);
    font-size: 14px;
}

.form-group select, 
.form-group input, 
.form-group textarea {
    padding: 10px 15px;
    background-color: rgba(17, 17, 17, 0.5);
    border: 1px solid rgba(201, 168, 108, 0.3);
    color: var(--light-text);
    border-radius: 4px;
    font-size: 14px;
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.form-row {
    display: flex;
    gap: 20px;
}

.form-row .form-group {
    flex: 1;
}

.file-upload {
    position: relative;
}

.file-upload input[type="file"] {
    display: none;
}

.file-upload label {
    padding: 10px 15px;
    background-color: rgba(17, 17, 17, 0.5);
    border: 1px solid rgba(201, 168, 108, 0.3);
    color: var(--light-text);
    border-radius: 4px;
    cursor: pointer;
    display: inline-block;
    transition: all 0.3s ease;
    text-align: center;
    width: 100%;
}

.file-upload label:hover {
    border-color: var(--gold-primary);
    color: var(--gold-primary);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 20px;
    columns: #8b783d;
}

.cancel-btn {
    padding: 10px 20px;
    background-color: transparent;
    border: 1px solid rgba(201, 168, 108, 0.3);
    color: var(--gold-light);
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.cancel-btn:hover {
    border-color: var(--gold-primary);
    color: var(--gold-primary);
}

.submit-btn {
    padding: 10px 20px;
    background-color: var(--gold-primary);
    border: none;
    color: var(--darker-bg);
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.submit-btn:hover {
    background-color: var(--gold-light);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Transport Detail Modal */
.detail-row {
    display: flex;
    gap: 30px;
    margin-bottom: 20px;
}

.detail-image {
    flex: 1;
}

.detail-image img {
    width: 100%;
    max-height: 250px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid rgba(201, 168, 108, 0.3);
}

.detail-info {
    flex: 2;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.detail-item {
    display: flex;
    align-items: center;
}

.detail-label {
    width: 120px;
    color: var(--gold-light);
    font-size: 14px;
}

.detail-value {
    color: var(--light-text);
    font-weight: 500;
}

.detail-item.full-width {
    flex-direction: column;
    align-items: flex-start;
}

.detail-description {
    color: rgba(248, 245, 235, 0.8);
    line-height: 1.6;
    margin-top: 5px;
}

.detail-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}

.edit-btn {
    padding: 10px 20px;
    background-color: var(--gold-primary);
    border: none;
    color: var(--darker-bg);
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.edit-btn:hover {
    background-color: var(--gold-light);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Responsive Styles */
@media screen and (max-width: 1200px) {
    .detail-row {
        flex-direction: column;
    }
}

@media screen and (max-width: 992px) {
    .sidebar {
        width: var(--sidebar-collapsed-width);
    }
    
    .sidebar .logo-text,
    .sidebar .sidebar-nav li span {
        display: none;
    }
    
    .sidebar .sidebar-nav li i {
        margin-right: 0;
        font-size: 20px;
    }
    
    .main-content {
        margin-left: var(--sidebar-collapsed-width);
        width: calc(100% - var(--sidebar-collapsed-width));
    }
    
    .nav-right {
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 10px;
    }
    
    .add-btn {
        margin-right: 0;
    }
    
    .search-box {
        order: -1;
        width: 100%;
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .search-box input {
        width: 100%;
    }
}

@media screen and (max-width: 768px) {
    .top-nav {
        padding: 0 15px;
    }
    
    .user-profile span {
        display: none;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .card-actions {
        width: 100%;
        justify-content: flex-end;
    }
    
    .data-table {
        display: block;
        overflow-x: auto;
    }
}

@media screen and (max-width: 576px) {
    .form-row {
        flex-direction: column;
        gap: 15px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .cancel-btn, .submit-btn {
        width: 100%;
    }
}

/* Alerts */
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert.success {
    background-color: rgba(75, 192, 192, 0.1);
    color: #4bc0c0;
    border: 1px solid rgba(75, 192, 192, 0.3);
}

.alert.error {
    background-color: rgba(255, 99, 132, 0.1);
    color: #ff6384;
    border: 1px solid rgba(255, 99, 132, 0.3);
}

.alert.debug {
    background-color: rgba(54, 162, 235, 0.1);
    color: #36a2eb;
    border: 1px solid rgba(54, 162, 235, 0.3);
}

.calendar-btn {
    background-color: var(--gold-primary);
    color: var(--darker-bg);
    border: none;
    padding: 10px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
}

.calendar-btn:hover {
    background-color: var(--gold-light);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

#calendar {
    height: 400px;
    margin-top: 20px;
}

#calendar .fc {
    font-size: 14px;
}

#calendar .fc-header-toolbar {
    margin-bottom: 1em;
}

#calendar .fc-toolbar-title {
    font-size: 1.2em;
}

#calendar .fc-button {
    padding: 0.4em 0.6em;
    font-size: 0.9em;
}

.date-picker {
    display: flex;
    gap: 10px;
}

.date-picker input {
    flex: 1;
    padding: 10px 15px;
    background-color: rgba(17, 17, 17, 0.5);
    border: 1px solid rgba(201, 168, 108, 0.3);
    color: var(--light-text);
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
}

.venue-selection {
    display: flex;
    gap: 10px;
    align-items: center;
}

.venue-selection input {
    flex: 1;
}

.map-btn {
    padding: 8px 15px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
}

.map-btn:hover {
    background-color: #0056b3;
}

.map-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    z-index: 1000;
}

.map-container {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 800px;
    height: 80%;
    background-color: white;
    border-radius: 10px;
    padding: 20px;
}

#map {
    width: 100%;
    height: calc(100% - 50px);
    border-radius: 5px;
}

.close-map {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 24px;
    cursor: pointer;
    color: #333;
    background: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1001;
}

.map-actions {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
}

.select-location-btn {
    padding: 10px 20px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.select-location-btn:hover {
    background-color: #218838;
}
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <img src="logo.png" alt="TuniFy Logo">
                <div class="logo-text">
                    <h1>TuniFy</h1>
                    <p>ADMINISTRATION</p>
                </div>
            </div>
        </div>
        <div class="sidebar-nav">
            <ul>
                <li class="active">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tableau de Bord</span>
                    </a>
                </li>
                <li>
                    <a href="transportback.php">
                        <i class="fas fa-car"></i>
                        <span>Transports</span>
                    </a>
                </li>
                
                <li>
                    <a href="admin-reservations.php">
                        <i class="fas fa-hotel"></i>
                        <span>Hébergements</span>
                    </a>
                </li>
                <li>
                    <a href="restauration.php">
                        <i class="fas fa-utensils"></i>
                        <span>Restauration</span>
                    </a>
                </li>
                <li>
                    <a href="forums.php">
                        <i class="fas fa-comments"></i>
                        <span>Forums</span>
                    </a>
                </li>
                <li>
                    <a href="users.php">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
                <li>
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Paramètres</span>
                    </a>
                </li>
                <li>
                    <a href="../frontend/front.php" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Voir le Site</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="nav-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h2>Tableau de Bord - Événements et Réservations</h2>
            </div>
            <div class="nav-right">
                <div class="search-box">
                    <form method="GET" id="searchForm">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Rechercher un événement..." value="<?php echo htmlspecialchars($search); ?>">
                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                        <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>">
                    </form>
                </div>
                <button class="add-btn" id="addEventBtn">
                    <i class="fas fa-plus"></i>
                    Nouvel Événement
                </button>
                <div class="user-profile">
                    <img src="assets/images/admin.jpg" alt="Admin Profile">
                    <span>Admin</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Alerts -->
            <?php
            if (isset($_GET['success'])) {
                echo '<div class="alert success">' . htmlspecialchars($_GET['success']) . '</div>';
            }
            if (isset($_GET['error'])) {
                echo '<div class="alert error">' . htmlspecialchars($_GET['error']) . '</div>';
            }
            ?>

            <!-- Tabs -->
            <div class="tabs">
                <button class="tab-btn active">Aperçu</button>
                <button class="tab-btn">Ventes</button>
                <button class="tab-btn">Visites</button>
                <button class="tab-btn">Rapports</button>
            </div>

            <!-- Stats Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="card" style="margin-bottom: 0;">
                    <div style="padding: 20px; text-align: center;">
                        <div style="font-size: 40px; color: var(--gold-primary); margin-bottom: 10px;">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h3 style="font-size: 20px; margin-bottom: 5px;">Événements</h3>
                        <p style="font-size: 28px; font-weight: 600; color: var(--gold-primary);"><?php echo $stats['events']; ?></p>
                        <p style="font-size: 14px; color: #4bc0c0; margin-top: 10px;">
                            <i class="fas fa-arrow-up"></i> +<?php echo rand(0, 5); ?> ce mois
                        </p>
                    </div>
                </div>
                <div class="card" style="margin-bottom: 0;">
                    <div style="padding: 20px; text-align: center;">
                        <div style="font-size: 40px; color: var(--gold-primary); margin-bottom: 10px;">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <h3 style="font-size: 20px; margin-bottom: 5px;">Réservations</h3>
                        <p style="font-size: 28px; font-weight: 600; color: var(--gold-primary);"><?php echo $stats['reservations']; ?></p>
                        <p style="font-size: 14px; color: #4bc0c0; margin-top: 10px;">
                            <i class="fas fa-arrow-up"></i> +<?php echo rand(0, 10); ?> cette semaine
                        </p>
                    </div>
                </div>
                <div class="card" style="margin-bottom: 0;">
                    <div style="padding: 20px; text-align: center;">
                        <div style="font-size: 40px; color: var(--gold-primary); margin-bottom: 10px;">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <h3 style="font-size: 20px; margin-bottom: 5px;">Réservations en attente</h3>
                        <p style="font-size: 28px; font-weight: 600; color: var(--gold-primary);"><?php echo $stats['pending_reservations']; ?></p>
                        <p style="font-size: 14px; color: #4bc0c0; margin-top: 10px;">
                            <i class="fas fa-arrow-up"></i> +<?php echo rand(0, 3); ?> aujourd'hui
                        </p>
                    </div>
                </div>
                <div class="card" style="margin-bottom: 0;">
                    <div style="padding: 20px; text-align: center;">
                        <div style="font-size: 40px; color: var(--gold-primary); margin-bottom: 10px;">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 style="font-size: 20px; margin-bottom: 5px;">Utilisateurs</h3>
                        <p style="font-size: 28px; font-weight: 600; color: var(--gold-primary);"><?php echo $stats['users']; ?></p>
                        <p style="font-size: 14px; color: #4bc0c0; margin-top: 10px;">
                            <i class="fas fa-arrow-up"></i> +<?php echo rand(0, 2); ?> ce mois
                        </p>
                    </div>
                </div>
            </div>

            <!-- Events List -->
            <div class="card">
                <div class="card-header">
                    <h3>Liste des Événements</h3>
                    <div class="card-actions">
                    <button id="btnpdf" class="tab-btn active" type="button">Export as PDF</button>
                        <select class="filter-select">
                            <option>Tous</option>
                            <option>Futurs</option>
                            <option>Passés</option>
                        </select>
                        <button class="refresh-btn" onclick="window.location.href='dashboard.php'">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><a href="<?php echo getSortLink('id', $sort, $order, $search); ?>">ID <i class="fas <?php echo getSortIcon('id', $sort, $order); ?>"></i></a></th>
                                <th><a href="<?php echo getSortLink('title', $sort, $order, $search); ?>">Titre <i class="fas <?php echo getSortIcon('title', $sort, $order); ?>"></i></a></th>
                                <th><a href="<?php echo getSortLink('event_date', $sort, $order, $search); ?>">Date <i class="fas <?php echo getSortIcon('event_date', $sort, $order); ?>"></i></a></th>
                                <th><a href="<?php echo getSortLink('venue', $sort, $order, $search); ?>">Lieu <i class="fas <?php echo getSortIcon('venue', $sort, $order); ?>"></i></a></th>
                                <th><a href="<?php echo getSortLink('max_capacity', $sort, $order, $search); ?>">Capacité Max <i class="fas <?php echo getSortIcon('max_capacity', $sort, $order); ?>"></i></a></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['id']); ?></td>
                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                <td><?php echo htmlspecialchars(date('d M Y', strtotime($event['event_date']))); ?></td>
                                <td><?php echo htmlspecialchars($event['venue']); ?></td>
                                <td><?php echo htmlspecialchars($event['max_capacity']); ?></td>
                                <td>
                                    <button class="action-btn view" onclick="window.location.href='event_details.php?id=<?php echo urlencode($event['id']); ?>'"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit" onclick="window.location.href='edit_event.php?id=<?php echo urlencode($event['id']); ?>'"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete" onclick="if(confirm('Voulez-vous vraiment supprimer l\'événement #<?php echo $event['id']; ?> ?')) window.location.href='delete_event.php?id=<?php echo urlencode($event['id']); ?>'"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reservations List -->
            <div class="card">
                <div class="card-header">
                    <h3>Réservations Récentes</h3>
                    <div class="card-actions">
                        <select class="filter-select">
                            <option>Toutes</option>
                            <option>Confirmées</option>
                            <option>En attente</option>
                            <option>Annulées</option>
                        </select>
                        <button class="refresh-btn" onclick="window.location.href='dashboard.php'">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Email</th>
                                <th>Événement</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation): ?>
                            <?php
                                $statusClasses = [
                                    'confirmed' => 'completed',
                                    'pending' => 'active',
                                    'cancelled' => 'cancelled'
                                ];
                                $statusClass = $statusClasses[$reservation['status']] ?? 'cancelled';
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['client_name']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['client_email']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['event_title']); ?></td>
                                <td><?php echo htmlspecialchars(date('d M Y H:i', strtotime($reservation['reservation_date']))); ?></td>
                                <td>
                                    <span class="status <?php echo htmlspecialchars($statusClass); ?>">
                                        <?php echo htmlspecialchars(ucfirst($reservation['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($reservation['status'] === 'pending'): ?>
                                        <button class="action-btn accept" 
                                                onclick="if(confirm('Voulez-vous accepter la réservation #<?php echo $reservation['id']; ?> ?')) window.location.href='accept_reservation.php?id=<?php echo urlencode($reservation['id']); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>'"
                                                title="Accepter">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="action-btn refuse" 
                                                onclick="if(confirm('Voulez-vous refuser la réservation #<?php echo $reservation['id']; ?> ?')) window.location.href='refuse_reservation.php?id=<?php echo urlencode($reservation['id']); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>'"
                                                title="Refuser">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div class="modal" id="addEventModal">
        <div class="modal-content" style="max-width: 800px;">
            <span class="close-modal" id="closeAddModal">×</span>
            <h2>Ajouter un Événement</h2>
            <form id="addEventForm" method="POST" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Titre</label>
                        <input type="text" id="title" name="title" required>
                        <span class="error-message" id="title-error"></span>
                    </div>
                    <div class="form-group">
                        <label>Date de l'Événement</label>
                        <div class="date-picker">
                            <input type="text" id="event_date" name="event_date" readonly required>
                            <button type="button" id="openCalendarBtn" class="calendar-btn">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                        </div>
                        <span class="error-message" id="event_date-error"></span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="venue">Lieu</label>
                        <div class="venue-selection">
                            <input type="text" id="venue" name="venue" required readonly>
                            <button type="button" id="selectLocationBtn" class="map-btn">
                                <i class="fas fa-map-marker-alt"></i> Sélectionner le lieu
                            </button>
                        </div>
                        <input type="hidden" id="latitude" name="latitude">
                        <input type="hidden" id="longitude" name="longitude">
                        <span class="error-message" id="venue-error"></span>
                    </div>
                    <div class="form-group">
                        <label for="max_capacity">Capacité Maximum</label>
                        <input type="number" id="max_capacity" name="max_capacity" min="1" required>
                        <span class="error-message" id="max_capacity-error"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Description de l'événement"></textarea>
                    <span class="error-message" id="description-error"></span>
                </div>
                <div class="form-group">
                    <label for="image_url">URL de l'Image</label>
                    <input type="url" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
                    <span class="error-message" id="image_url-error"></span>
                </div>
                <input type="hidden" name="add_event" value="1">
                <div class="form-actions">
                    <button type="button" class="cancel-btn" id="cancelAddEvent">Annuler</button>
                    <button type="submit" class="submit-btn">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Calendar Modal -->
    <div class="modal" id="calendarModal">
        <div class="modal-content" style="max-width: 600px; padding: 20px;">
            <span class="close-modal" id="closeCalendarModal">×</span>
            <h2>Sélectionner une Date</h2>
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Map Modal -->
    <div id="mapModal" class="map-modal">
        <div class="map-container">
            <span class="close-map" onclick="closeMap()">&times;</span>
            <div id="map"></div>
            <div class="map-actions">
                <button type="button" class="select-location-btn" onclick="selectLocation()">Sélectionner ce lieu</button>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script>
        var eventVisitors = <?php echo json_encode($eventVisitors); ?>;

        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');

        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

        // Modal Handling for Add Event
        const addEventModal = document.getElementById('addEventModal');
        const addEventBtn = document.getElementById('addEventBtn');
        const closeAddModal = document.getElementById('closeAddModal');
        const cancelAddEvent = document.getElementById('cancelAddEvent');
        const selectLocationBtn = document.getElementById('selectLocationBtn');

        addEventBtn.addEventListener('click', function() {
            addEventModal.style.display = 'flex';
            document.getElementById('addEventForm').reset();
            document.querySelectorAll('.error-message').forEach(span => {
                span.style.display = 'none';
                span.textContent = '';
            });
            document.querySelectorAll('.form-group').forEach(group => {
                group.classList.remove('invalid');
            });
        });

        closeAddModal.addEventListener('click', function() {
            addEventModal.style.display = 'none';
        });

        cancelAddEvent.addEventListener('click', function() {
            addEventModal.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target === addEventModal) {
                addEventModal.style.display = 'none';
            }
        });

        // Active menu item
        document.querySelectorAll('.sidebar-nav li').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.sidebar-nav li').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Search form submission on input
        const searchInput = document.querySelector('.search-box input[name="search"]');
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('searchForm').submit();
            }, 500);
        });

        // Form validation for Add Event
        const addEventForm = document.getElementById('addEventForm');
        const fields = [
            {
                id: 'title',
                validate: (value) => {
                    if (!value) return 'Le titre est requis.';
                    if (value.length < 3) return 'Le titre doit contenir au moins 3 caractères.';
                    if (value.length > 255) return 'Le titre ne peut pas dépasser 255 caractères.';
                    if (!/^[a-zA-Z0-9\s,.-]+$/.test(value)) return 'Le titre ne peut contenir que des lettres, chiffres, espaces, virgules, points ou tirets.';
                    return '';
                }
            },
            {
                id: 'event_date',
                validate: (value) => {
                    if (!value) return 'La date est requise.';
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const inputDate = new Date(value);
                    if (isNaN(inputDate.getTime())) return 'Date invalide.';
                    if (inputDate <= today) return 'La date doit être dans le futur.';
                    return '';
                }
            },
            {
                id: 'venue',
                validate: (value) => {
                    if (!value) return 'Le lieu est requis.';
                    if (value.length < 3) return 'Le lieu doit contenir au moins 3 caractères.';
                    if (value.length > 255) return 'Le lieu ne peut pas dépasser 255 caractères.';                    return '';
                }
            },
            {
                id: 'max_capacity',
                validate: (value) => {
                    if (!value) return 'La capacité est requise.';
                    const num = parseInt(value, 10);
                    if (isNaN(num) || num < 1) return 'La capacité doit être un nombre positif.';
                    if (num > 10000) return 'La capacité ne peut pas dépasser 10 000.';
                    return '';
                }
            },
            {
                id: 'description',
                validate: (value) => {
                    if (value.length > 1000) return 'La description ne peut pas dépasser 1000 caractères.';
                    if (/<[^>]+>/.test(value)) return 'La description ne peut pas contenir de balises HTML.';
                    return '';
                }
            },
            {
                id: 'image_url',
                validate: (value) => {
                    if (!value) return '';
                    if (value.length > 255) return 'L\'URL ne peut pas dépasser 255 caractères.';
                    if (!/^https?:\/\/[^\s/$.?#].[^\s]*$/.test(value)) return 'Veuillez entrer une URL valide (http:// ou https://).';
                    return '';
                }
            }
        ];

        function validateField(fieldId, value) {
            const fieldConfig = fields.find(f => f.id === fieldId);
            if (!fieldConfig) return '';
            return fieldConfig.validate(value);
        }

        function updateErrorMessage(fieldId, message) {
            const errorSpan = document.getElementById(`${fieldId}-error`);
            const formGroup = errorSpan.closest('.form-group');
            if (message) {
                errorSpan.textContent = message;
                errorSpan.style.display = 'block';
                formGroup.classList.add('invalid');
            } else {
                errorSpan.textContent = '';
                errorSpan.style.display = 'none';
                formGroup.classList.remove('invalid');
            }
        }

        fields.forEach(field => {
            const input = document.getElementById(field.id);
            input.addEventListener('input', () => {
                const message = validateField(field.id, input.value);
                updateErrorMessage(field.id, message);
            });
        });

        addEventForm.addEventListener('submit', (e) => {
            let hasErrors = false;
            fields.forEach(field => {
                const input = document.getElementById(field.id);
                const message = validateField(field.id, input.value);
                updateErrorMessage(field.id, message);
                if (message) hasErrors = true;
            });
            if (hasErrors) {
                e.preventDefault();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            var btnpdf = document.getElementById('btnpdf');
            if (!btnpdf) return;

            btnpdf.addEventListener('click', function () {
                if (!window.jspdf || !window.jspdf.jsPDF) {
                    alert('jsPDF library not loaded!');
                    return;
                }
                var table = document.querySelector('.card-body .data-table');
                if (!table) {
                    alert('Event table not found!');
                    return;
                }

                const { jsPDF } = window.jspdf;
                var doc = new jsPDF();

                // 1. Main Event List Table
                var headers = [];
                table.querySelectorAll('thead th').forEach(function (th, idx) {
                    if (idx < 5) headers.push(th.innerText.trim());
                });

                var data = [];
                table.querySelectorAll('tbody tr').forEach(function (tr) {
                    var row = [];
                    tr.querySelectorAll('td').forEach(function (td, idx) {
                        if (idx < 5) row.push(td.innerText.trim());
                    });
                    if (row.length) data.push(row);
                });

                doc.text("Liste des Événements", 14, 15);
                doc.autoTable({
                    head: [headers],
                    body: data,
                    startY: 20,
                    styles: { font: "helvetica", fontSize: 10 },
                    headStyles: { fillColor: [201, 168, 108] }
                });

                // 2. For each event, add a page with its visitors
                data.forEach(function (eventRow, i) {
                    var eventId = eventRow[0];
                    // DEBUG: Check eventId and eventVisitors keys
                    // console.log('Event ID:', eventId, 'Available keys:', Object.keys(window.eventVisitors));

                    // Try both with and without leading #
                    var visitors = window.eventVisitors[eventId] || window.eventVisitors[eventId.replace(/^#/, '')] || [];

                    doc.addPage();
                    doc.setFont(undefined, 'bold');
                    doc.text(`Événement: ${eventRow[1]} (ID: ${eventId})`, 14, 20);
                    doc.setFont(undefined, 'normal');
                    doc.text(`Date: ${eventRow[2]} | Lieu: ${eventRow[3]} | Capacité Max: ${eventRow[4]}`, 14, 28);

                    if (visitors.length > 0) {
                        doc.autoTable({
                            head: [['Nom', 'Email', 'Date de réservation', 'Statut']],
                            body: visitors.map(v => [
                                v.client_name,
                                v.client_email,
                                v.reservation_date,
                                v.status
                            ]),
                            startY: 35,
                            styles: { font: "helvetica", fontSize: 9 },
                            headStyles: { fillColor: [201, 168, 108] },
                            margin: { left: 14, right: 14 }
                        });
                    } else {
                        doc.text('Aucun visiteur pour cet événement.', 14, 40);
                    }
                });

                doc.save('evenements_avec_visiteurs.pdf');
            });
        });

        // Initialize calendar
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var openCalendarBtn = document.getElementById('openCalendarBtn');
            var calendarModal = document.getElementById('calendarModal');
            var closeCalendarModal = document.getElementById('closeCalendarModal');
            var eventDateInput = document.getElementById('event_date');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'fr',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: function(info, successCallback, failureCallback) {
                    fetch('get_events.php')
                        .then(response => response.json())
                        .then(data => {
                            var events = data.map(event => ({
                                title: event.title,
                                start: event.event_date,
                                backgroundColor: '#ff6384',
                                borderColor: '#ff6384',
                                display: 'background'
                            }));
                            successCallback(events);
                        })
                        .catch(error => {
                            console.error('Error fetching events:', error);
                            failureCallback(error);
                        });
                },
                selectable: true,
                select: function(info) {
                    var selectedDate = info.startStr.split('T')[0];
                    var isDateOccupied = calendar.getEvents().some(event => {
                        return event.startStr.split('T')[0] === selectedDate;
                    });

                    if (isDateOccupied) {
                        alert('Cette date est déjà occupée par un événement. Veuillez choisir une autre date.');
                        return;
                    }

                    eventDateInput.value = selectedDate;
                    calendarModal.style.display = 'none';
                },
                selectAllow: function(selectInfo) {
                    var selectedDate = selectInfo.startStr.split('T')[0];
                    return !calendar.getEvents().some(event => {
                        return event.startStr.split('T')[0] === selectedDate;
                    });
                },
                eventDidMount: function(info) {
                    info.el.title = info.event.title;
                }
            });
            calendar.render();

            // Open calendar modal
            openCalendarBtn.addEventListener('click', function() {
                calendarModal.style.display = 'flex';
                calendar.render();
            });

            // Close calendar modal
            closeCalendarModal.addEventListener('click', function() {
                calendarModal.style.display = 'none';
            });

            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === calendarModal) {
                    calendarModal.style.display = 'none';
                }
            });
        });

        // Map functionality
        let map;
        let marker;
        let selectedLocation = null;

        function openMap() {
            const mapModal = document.getElementById('mapModal');
            mapModal.style.display = 'block';
            mapModal.style.zIndex = '9999'; // Assure que la carte est au premier plan
            
            // Clear previous map if it exists
            const mapContainer = document.getElementById('map');
            mapContainer.innerHTML = '';
            
            // Attendre que le modal soit visible avant d'initialiser la carte
            setTimeout(() => {
                try {
                    // Initialize map with default center (Tunisia)
                    map = L.map('map').setView([36.8065, 10.1815], 15);

                    // Add OpenStreetMap tiles
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    // Create geocoder control
                    L.Control.geocoder({
                        defaultMarkGeocode: false,
                        position: 'topleft',
                        placeholder: 'Rechercher un lieu...',
                        errorMessage: 'Aucun résultat trouvé.',
                        showResultIcons: true,
                        collapsed: true
                    })
                    .on('markgeocode', function(e) {
                        const latlng = e.geocode.center;
                        map.setView(latlng, 15);
                        
                        // Remove previous marker if it exists
                        if (marker) {
                            map.removeLayer(marker);
                        }
                        
                        // Add marker
                        marker = L.marker(latlng).addTo(map)
                            .bindPopup(e.geocode.name)
                            .openPopup();
                        
                        selectedLocation = {
                            lat: latlng.lat,
                            lng: latlng.lng,
                            name: e.geocode.name
                        };
                    })
                    .addTo(map);

                    // Add click event to map
                    map.on('click', function(e) {
                        const latlng = e.latlng;
                        
                        // Remove previous marker if it exists
                        if (marker) {
                            map.removeLayer(marker);
                        }
                        
                        // Add marker
                        marker = L.marker(latlng).addTo(map);
                        
                        // Get location name using reverse geocoding
                        const geocoder = L.Control.Geocoder.nominatim();
                        geocoder.reverse(latlng, map.options.crs.scale(map.getZoom()), function(results) {
                            const locationName = results[0]?.name || 'Lieu sélectionné';
                            marker.bindPopup(locationName).openPopup();
                            
                            selectedLocation = {
                                lat: latlng.lat,
                                lng: latlng.lng,
                                name: locationName
                            };
                        });
                    });

                    // Redimensionner la carte pour s'assurer qu'elle s'affiche correctement
                    map.invalidateSize();

                } catch (error) {
                    console.error('Map initialization error:', error);
                    alert('Erreur lors de l\'initialisation de la carte: ' + error.message);
                }
            }, 100);
        }

        function closeMap() {
            const mapModal = document.getElementById('mapModal');
            mapModal.style.display = 'none';
            mapModal.style.zIndex = '1000';
            
            // Clean up map elements
            if (map) {
                map.remove();
            }
            if (marker) {
                map.removeLayer(marker);
            }
        }

        function selectLocation() {
            if (selectedLocation) {
                document.getElementById('venue').value = selectedLocation.name;
                document.getElementById('latitude').value = selectedLocation.lat;
                document.getElementById('longitude').value = selectedLocation.lng;
                closeMap();
            } else {
                alert('Veuillez sélectionner un lieu sur la carte.');
            }
        }

        // Add event listener for the location selection button
        selectLocationBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Empêche le comportement par défaut du bouton
            openMap();
        });

        // Close map when clicking outside
        document.getElementById('mapModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeMap();
            }
        });
    </script>
</body>
</html>