CREATE TABLE IF NOT EXISTS rentals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    scooter_id INT,
    rental_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rental_end TIMESTAMP,
    price_per_hour DECIMAL(10, 2),
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (scooter_id) REFERENCES scooters (id)
    );
