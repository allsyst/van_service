# VanService Online Deployment Guide

This package is prepared for an online Laravel deployment so administrators, drivers, and passengers can access the same database from different locations over HTTPS.

## Before deploying

Use a production host that supports PHP 8.1 or newer, MySQL 8/MariaDB, HTTPS, and persistent writable storage. The host must allow the website document root to point to Laravel's `public` folder. Do not upload the application `.env` file to a public repository or share it with users.

Set a real domain, for example `https://vanservice.example.com`, and configure the following production settings in the host's `.env` file:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://vanservice.example.com
LOG_CHANNEL=stack
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
DB_CONNECTION=mysql
DB_HOST=YOUR_DATABASE_HOST
DB_PORT=3306
DB_DATABASE=YOUR_DATABASE_NAME
DB_USERNAME=YOUR_DATABASE_USERNAME
DB_PASSWORD=YOUR_DATABASE_PASSWORD
```

Generate a unique production application key after the `.env` is present:

```bash
php artisan key:generate --force
```

## Option 1: Standard PHP hosting or VPS

This option works on cPanel hosting, an Apache/Nginx VPS, or a Laravel-compatible managed PHP host.

1. Create an empty MySQL database and database user through the host panel.
2. Upload this Laravel project **without** `vendor`, `node_modules`, `.env`, or runtime cache files.
3. Run `composer install --no-dev --optimize-autoloader` on the server, or use the host's Laravel deployment tool.
4. Point the web domain document root to `YOUR_PROJECT/public`, never the project root.
5. Create the production `.env` using the values above and run `php artisan key:generate --force`.
6. Make `storage` and `bootstrap/cache` writable by the web server account.
7. Run only the needed targeted migrations on installations with the known historical migration-record issue. For a clean deployment, run `php artisan migrate --force`.
8. Run the optimization commands:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

9. Verify the public login page and then sign in with an administrator account over the HTTPS domain.

For the new fuel table on an existing installation, use this targeted command rather than a full migration if the old migration history is inconsistent:

```bash
php artisan migrate --path=database/migrations/2026_09_29_000000_create_fuel_records_table.php --force
```

## Option 2: Docker hosting

The project includes `Dockerfile`, `docker-compose.yml`, and `deploy/apache/000-default.conf` for hosts that run Docker.

1. Copy `.env.example` to `.env` on the server and set the production values. With the included Compose database service, set `DB_HOST=database`, set `DB_DATABASE` and `DB_USERNAME`, and provide strong values for `DB_PASSWORD` and `DB_ROOT_PASSWORD`.
2. Build and launch:

```bash
docker compose up -d --build
```

3. Generate the Laravel key and apply migrations:

```bash
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

4. Put the deployment behind a managed HTTPS reverse proxy or load balancer and route the domain to the `app` container. The sample Compose file exposes port `8080` for the first test.

## Production security checklist

| Requirement | Why it matters |
|---|---|
| HTTPS only | Protects credentials, bookings, and driver location data in transit. |
| `APP_DEBUG=false` | Prevents detailed error pages from exposing sensitive application details. |
| Strong unique DB passwords | Protects bookings, fuel records, and user information. |
| Document root is `/public` | Prevents direct browser access to Laravel configuration and source files. |
| Writable `storage` and `bootstrap/cache` only | Keeps runtime writes out of source directories. |
| Automated database backups | Preserves user, trip, fuel, and location data. |
| Firewall allow only 80/443 (and SSH admin access) | Reduces public attack surface. |

## Native driver app after deployment

The Android driver app currently discovers a Laravel server on the same Wi-Fi network. For an Internet-hosted system, it must use a verified HTTPS public API URL instead of local `/24` discovery. Keep the existing same-Wi-Fi mode for local XAMPP use, and make a separate production app configuration with the public HTTPS host when ready. Do not expose the XAMPP computer directly to the Internet.

## Go-live test

From a phone using mobile data—not the office Wi-Fi—open the HTTPS site, sign in, create a test trip request, approve it as administrator, confirm it in the driver app configuration intended for production, and verify that the live map, booking ticket, and fuel report work against the production database.
