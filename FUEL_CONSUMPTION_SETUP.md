# Fuel Consumption Analytics Setup

This update adds an administrator-only **Fuel Consumption** module to VanService.

## What the module records

Each fuel record stores:

- Van
- Optional linked trip/travel
- Date of fuel use
- Actual gasoline consumed in litres
- Optional total fuel cost
- Optional odometer reading
- Optional fuel station and notes

> The module stores actual fuel entries. It deliberately does **not** generate made-up historical gasoline values.

## Three-month dashboard

Open **Admin Panel → Fuel Consumption** to view a rolling three-month analysis. The line chart compares:

| Measure | Data source |
|---|---|
| Gasoline consumption (L) | Fuel records entered by the administrator |
| Completed travels | Trips whose status is `completed` |
| Distance travelled (km) | `distance_km` from completed trips |

The page also displays total litres, total completed travels, total completed distance, and average consumption in litres per 100 km.

## Install the database table

### Preferred: Laravel command

After copying this update into the Laravel application directory, open **Command Prompt** in that directory and run:

```bat
php artisan migrate --path=database/migrations/2026_09_29_000000_create_fuel_records_table.php
```

Then start the application as usual:

```bat
php artisan serve --host=0.0.0.0 --port=8000
```

Do **not** run a full `php artisan migrate` on an installation whose historical migration records are inconsistent; use the targeted command above.

### Alternative: phpMyAdmin SQL

If the targeted migration cannot run, select the VanService database in phpMyAdmin and run this SQL once:

```sql
CREATE TABLE fuel_records (
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
```

## Entering the initial three months of data

1. Sign in as an administrator.
2. Select **Fuel Consumption** from the sidebar.
3. Click **Add Fuel Record**.
4. Select the linked travel when the fuel entry belongs to one. The system selects that trip's van automatically.
5. Enter the actual litres and fuel date. Add cost, odometer, fuel station, and notes if available.
6. Repeat for every real fuel entry in the last three months.

The line chart updates as records are entered. Travel count and distance come from completed trips automatically; only gasoline data needs manual entry.

## Updated source files

- `app/Models/FuelRecord.php`
- `app/Models/Van.php`
- `app/Models/Trip.php`
- `app/Http/Controllers/Admin/FuelRecordController.php`
- `database/migrations/2026_09_29_000000_create_fuel_records_table.php`
- `resources/views/admin/fuel-records/index.blade.php`
- `resources/views/admin/fuel-records/form.blade.php`
- `resources/views/layouts/admin.blade.php`
- `routes/web.php`
