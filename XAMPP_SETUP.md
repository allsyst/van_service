# VanService — XAMPP Setup

This package contains the latest VanService Laravel system, including the fuel-consumption reports and graphs, vehicle booking availability prompts, printable trip tickets, notifications, and the Android-driver API.

## Requirements

Install **XAMPP with PHP 8.1 or later** and start **Apache** and **MySQL** from the XAMPP Control Panel. The package already includes the PHP `vendor` folder, so Composer is not required for normal startup.

## 1. Place the project in XAMPP

1. Extract the ZIP file.
2. Rename the extracted folder to `van_service_laravel`.
3. Move it to:

   ```text
   C:\xampp\htdocs\van_service_laravel
   ```

## 2. Create the configuration file

1. In `C:\xampp\htdocs\van_service_laravel`, locate `XAMPP_ENV_TEMPLATE.txt`.
2. Make a copy of that file and rename the copy to `.env`.
3. Open `.env` with a text editor. Keep the database settings shown for a fresh XAMPP MySQL installation unless your MySQL account has a password.

Do not place `.env` inside `public`.

## 3. Create a fresh database

1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
2. Create a database named:

   ```text
   van_service
   ```

3. Select collation `utf8mb4_unicode_ci` if phpMyAdmin asks.

For a clean installation, leave the database empty. The next command creates all tables.

## 4. Initialize Laravel

Open **Command Prompt** and run:

```bat
cd C:\xampp\htdocs\van_service_laravel
C:\xampp\php\php.exe artisan key:generate
C:\xampp\php\php.exe artisan migrate --seed
C:\xampp\php\php.exe artisan optimize:clear
```

The seed command creates these testing accounts:

```text
Admin:  admin@vanservice.com
Password: admin123

Driver: driver@vanservice.com
Password: driver123
```

Change both passwords after first login.

## 5. Open the system

Open this address in a browser:

```text
http://localhost/van_service_laravel/public
```

Sign in as the administrator and add vans, trips, fuel records, or test bookings.

## 6. Connect the Android Driver App on the same Wi-Fi

The native driver app discovers a Laravel server exposed on port 8000. On the Windows laptop, open a second Command Prompt in the project folder and run:

```bat
C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000
```

Find the laptop IPv4 address with:

```bat
ipconfig
```

On the Android tablet connected to the same non-guest Wi-Fi, open this URL in Chrome first:

```text
http://YOUR-LAPTOP-IP:8000/api/driver/mobile/health
```

It must return a small JSON response. If it does not open, allow `php.exe` or TCP port 8000 through Windows Defender Firewall on the **Private** network. The tablet and laptop must not use guest Wi-Fi or an access-point-isolated network.

## Existing database warning

If you already have an older VanService database, back it up in phpMyAdmin before running migrations. Do not run `migrate:fresh` unless you intentionally want to erase all existing data. The older XAMPP setup may have incomplete migration history; apply the latest migration files carefully after a backup.

## Quick checks

Run these from the project folder if something does not load:

```bat
C:\xampp\php\php.exe artisan optimize:clear
C:\xampp\php\php.exe artisan route:list
```

Check `storage\logs\laravel.log` for Laravel errors after setting up the project.
