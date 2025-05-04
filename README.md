# Restaurant Management System Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Database Structure](#database-structure)
3. [Controllers and Models](#controllers-and-models)
4. [Frontend Views](#frontend-views)
5. [Backend Views](#backend-views)
6. [Data Flow](#data-flow)
7. [Features](#features)

## System Overview

This is a comprehensive restaurant management system that handles:
- Online food ordering
- Table reservations
- Delivery management
- Menu management
- Order tracking

The system is built using PHP with a MySQL database and follows an MVC (Model-View-Controller) architecture.

## Database Structure

### Tables

1. **dishes**
   - `id` (Primary Key)
   - `name` (VARCHAR)
   - `price` (DECIMAL)
   - `created_at` (TIMESTAMP)
   - `image_url` (VARCHAR) - For dish images

2. **commandes** (Orders)
   - `id` (Primary Key)
   - `customer_name` (VARCHAR)
   - `email` (VARCHAR)
   - `city` (VARCHAR)
   - `delivery_time` (DATETIME)
   - `status` (VARCHAR)
   - `created_at` (TIMESTAMP)
   - `updated_at` (TIMESTAMP)

3. **order_dishes** (Order Items)
   - `id` (Primary Key)
   - `order_id` (Foreign Key -> commandes.id)
   - `dish_id` (Foreign Key -> dishes.id)
   - `quantity` (INT)

4. **reservations**
   - `id` (Primary Key)
   - `client_name` (VARCHAR)
   - `client_email` (VARCHAR)
   - `client_phone` (VARCHAR)
   - `reservation_date` (DATE)
   - `reservation_time` (TIME)
   - `guest_count` (INT)
   - `special_requests` (TEXT)
   - `status` (ENUM: 'pending', 'confirmed', 'cancelled')
   - `created_at` (TIMESTAMP)
   - `updated_at` (TIMESTAMP)

5. **livraisons** (Deliveries)
   - `id` (Primary Key)
   - `customer_name` (VARCHAR)
   - `email` (VARCHAR)
   - `city` (VARCHAR)
   - `address` (VARCHAR)
   - `plats` (JSON)
   - `delivery_time` (DATETIME)
   - `status` (ENUM: 'en attente', 'en préparation', 'livré')
   - `created_at` (TIMESTAMP)
   - `updated_at` (TIMESTAMP)

## Controllers and Models

### Models

1. **Plat.php** (Dish Model)
   - Handles dish-related operations
   - Properties: id, name, price, image_url, created_at
   - Methods: Getters and setters for all properties

2. **Commande.php** (Order Model)
   - Manages order operations
   - Properties: id, customer_name, email, city, delivery_time, status
   - Methods: Getters and setters for all properties

3. **Reservation.php** (Reservation Model)
   - Handles reservation operations
   - Properties: id, client_name, client_email, client_phone, reservation_date, reservation_time, guest_count, special_requests, status
   - Methods: Getters and setters for all properties

### Controllers

1. **PlatC.php** (Dish Controller)
   - `ajouterPlat()`: Adds new dishes
   - `afficherPlats()`: Displays all dishes
   - `modifierPlat()`: Updates dish information
   - `supprimerPlat()`: Removes dishes
   - `getCategories()`: Retrieves dish categories
   - `toggleDisponibilite()`: Toggles dish availability

2. **CommandeC.php** (Order Controller)
   - `ajouterCommande()`: Creates new orders
   - `afficherCommandes()`: Lists all orders
   - `modifierCommande()`: Updates order status
   - `supprimerCommande()`: Removes orders
   - `getCommandesByEmail()`: Finds orders by email
   - `ajouterPlatCommande()`: Adds dishes to orders

3. **ReservationC.php** (Reservation Controller)
   - `ajouterReservation()`: Creates new reservations
   - `afficherReservations()`: Lists all reservations
   - `modifierReservation()`: Updates reservation status
   - `supprimerReservation()`: Removes reservations

## Frontend Views

1. **index.php** (Homepage)
   - Displays restaurant information
   - Shows featured dishes
   - Provides navigation to menu and reservations

2. **menu.php** (Menu Page)
   - Lists all available dishes
   - Allows dish filtering by category
   - Provides ordering functionality

3. **commande.php** (Order Page)
   - Order form for customers
   - Collects customer information
   - Handles dish selection and quantities

4. **suivi_commande.php** (Order Tracking)
   - Allows customers to track their orders
   - Shows order status and details
   - Provides order history

5. **reservation.php** (Reservation Page)
   - Table reservation form
   - Date and time selection
   - Guest count and special requests

## Backend Views

1. **index.php** (Admin Dashboard)
   - Overview of all system activities
   - Quick access to management sections
   - Statistics and summaries

2. **gestion_plats.php** (Dish Management)
   - Add/edit/delete dishes
   - Manage dish categories
   - Update dish availability

3. **gestion_commandes.php** (Order Management)
   - View and manage orders
   - Update order status
   - Process order details

4. **gestion_livraisons.php** (Delivery Management)
   - Track delivery status
   - Update delivery information
   - Manage delivery schedules

5. **gestion_reservations.php** (Reservation Management)
   - View and manage reservations
   - Update reservation status
   - Handle special requests

## Data Flow

### Order Process
1. Customer views menu (menu.php)
2. Selects dishes and quantities
3. Fills order form (commande.php)
4. System creates order in `commandes` table
5. Order items stored in `order_dishes` table
6. Customer can track order (suivi_commande.php)
7. Admin manages order status (gestion_commandes.php)

### Reservation Process
1. Customer views reservation page (reservation.php)
2. Fills reservation form with details
3. System creates reservation in `reservations` table
4. Admin manages reservations (gestion_reservations.php)
5. Status updates sent to customer

### Dish Management
1. Admin adds dishes (gestion_plats.php)
2. Dish stored in `dishes` table
3. Dishes displayed on menu (menu.php)
4. Admin can update dish details
5. Changes reflected in real-time

## Features

### Customer Features
- Online menu browsing
- Dish ordering system
- Order tracking
- Table reservations
- Delivery status updates

### Admin Features
- Complete order management
- Reservation handling
- Menu management
- Delivery tracking
- System statistics
- User management

### Technical Features
- Responsive design
- Real-time updates
- Secure data handling
- Efficient database structure
- User-friendly interface
- Comprehensive error handling 