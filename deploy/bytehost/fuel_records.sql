-- Run this once in the Bytehost phpMyAdmin database only if the
-- fuel_records table does not already exist.
CREATE TABLE IF NOT EXISTS fuel_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    van_id BIGINT UNSIGNED NOT NULL,
    trip_id BIGINT UNSIGNED NULL,
    fuel_date DATE NOT NULL,
    liters DECIMAL(10,2) NOT NULL,
    total_cost DECIMAL(12,2) NULL,
    odometer_km DECIMAL(12,2) NULL,
    fuel_station VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX fuel_records_van_date_index (van_id, fuel_date),
    INDEX fuel_records_fuel_date_index (fuel_date),
    CONSTRAINT fuel_records_van_id_foreign
        FOREIGN KEY (van_id) REFERENCES vans(id) ON DELETE CASCADE,
    CONSTRAINT fuel_records_trip_id_foreign
        FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
