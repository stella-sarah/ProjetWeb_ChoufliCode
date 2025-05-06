-- Update the reservations table structure
ALTER TABLE reservations
DROP COLUMN client,
DROP COLUMN service,
DROP COLUMN date,
DROP COLUMN notes;

ALTER TABLE reservations
ADD COLUMN event_id VARCHAR(50) NOT NULL AFTER id,
ADD COLUMN client_name VARCHAR(255) NOT NULL AFTER event_id,
ADD COLUMN client_email VARCHAR(255) NOT NULL AFTER client_name,
ADD COLUMN client_phone VARCHAR(20) NOT NULL AFTER client_email,
ADD COLUMN reservation_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER client_phone,
ADD COLUMN status ENUM('pending', 'accepted', 'refused') DEFAULT 'pending' AFTER reservation_date,
ADD FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE;

-- Create events table if it doesn't exist
CREATE TABLE IF NOT EXISTS events (
    id VARCHAR(50) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(255) NOT NULL,
    max_capacity INT NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add latitude and longitude columns if they don't exist
ALTER TABLE events
ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8) AFTER venue,
ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8) AFTER latitude; 