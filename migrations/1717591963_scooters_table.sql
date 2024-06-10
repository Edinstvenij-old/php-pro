CREATE TABLE IF NOT EXISTS scooters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    station_id INT,
    model VARCHAR(255) NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (station_id) REFERENCES stations (id)
    );
