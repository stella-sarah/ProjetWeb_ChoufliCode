-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS tunify CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE tunify;

-- Drop the table if it exists to recreate it with the correct structure
DROP TABLE IF EXISTS reservations;

-- Create reservations table with the correct structure
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client VARCHAR(255) NOT NULL,
    service VARCHAR(255) NOT NULL,
    date DATETIME NOT NULL,
    status ENUM('Confirmée', 'En attente', 'Annulée') DEFAULT 'En attente',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some sample data
INSERT INTO reservations (client, service, date, status, notes) VALUES
('John Doe', 'Voiture de luxe', '2024-03-20 10:00:00', 'Confirmée', 'Client VIP'),
('Jane Smith', 'Suite Deluxe', '2024-03-21 14:00:00', 'En attente', 'Préférence chambre vue mer'),
('Bob Johnson', 'Dîner gastronomique', '2024-03-22 19:00:00', 'Annulée', 'Annulation pour raison personnelle'); 