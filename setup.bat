@echo off
title VanService - Automated Setup
color 0A

echo ============================================
echo   VanService Laravel - Automated Setup
echo ============================================
echo.

:: -----------------------------------------------
:: STEP 1: Check if XAMPP PHP is available
:: -----------------------------------------------
echo [1/6] Checking PHP installation...
where php >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo  ERROR: PHP not found in PATH.
    echo  Please install XAMPP first from https://www.apachefriends.org
    echo  Then add C:\xampp\php to your system PATH and re-run this script.
    echo.
    pause
    exit /b 1
)
php -v | findstr /i "php"
echo  PHP found.
echo.

:: -----------------------------------------------
:: STEP 2: Check if Composer is available
:: -----------------------------------------------
echo [2/6] Checking Composer installation...
where composer >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo  ERROR: Composer not found.
    echo  Please install Composer from https://getcomposer.org/download
    echo  Then re-run this script.
    echo.
    pause
    exit /b 1
)
composer --version
echo  Composer found.
echo.

:: -----------------------------------------------
:: STEP 3: Install PHP dependencies
:: -----------------------------------------------
echo [3/6] Installing PHP dependencies (composer install)...
echo  This may take a few minutes...
call composer install --no-interaction --prefer-dist --optimize-autoloader
if %errorlevel% neq 0 (
    echo.
    echo  ERROR: composer install failed. Check your internet connection.
    pause
    exit /b 1
)
echo  Dependencies installed successfully.
echo.

:: -----------------------------------------------
:: STEP 4: Set up .env file
:: -----------------------------------------------
echo [4/6] Setting up environment file...
if not exist ".env" (
    copy .env.example .env >nul
    echo  .env file created from .env.example
) else (
    echo  .env file already exists, skipping.
)

:: Update .env database settings
echo  Configuring database settings in .env...
powershell -Command "(Get-Content .env) -replace 'DB_DATABASE=.*', 'DB_DATABASE=van_service' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'DB_USERNAME=.*', 'DB_USERNAME=root' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'DB_HOST=.*', 'DB_HOST=127.0.0.1' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'DB_PORT=.*', 'DB_PORT=3306' | Set-Content .env"
echo  Database configured: van_service (root, no password)
echo.

:: -----------------------------------------------
:: STEP 5: Generate app key
:: -----------------------------------------------
echo [5/6] Generating application key...
php artisan key:generate --force
echo  Application key generated.
echo.

:: -----------------------------------------------
:: STEP 6: Database setup
:: -----------------------------------------------
echo [6/6] Setting up database...
echo.
echo  IMPORTANT: Make sure XAMPP MySQL is running before continuing!
echo  Open XAMPP Control Panel and click START next to MySQL.
echo  Also create a database named: van_service
echo  (Go to http://localhost/phpmyadmin ^> New ^> van_service ^> Create)
echo.
pause

echo  Running database migrations...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo.
    echo  ERROR: Migration failed. Make sure MySQL is running and
    echo  the database 'van_service' exists in phpMyAdmin.
    pause
    exit /b 1
)
echo  Migrations completed.
echo.

echo  Seeding admin account...
php artisan db:seed --class=AdminSeeder --force
if %errorlevel% neq 0 (
    echo  WARNING: Seeder failed or already seeded. Continuing...
) else (
    echo  Admin account created.
)
echo.

:: -----------------------------------------------
:: Clear all caches
:: -----------------------------------------------
echo  Clearing caches...
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear
composer dump-autoload --quiet
echo  Caches cleared.
echo.

:: -----------------------------------------------
:: Done!
:: -----------------------------------------------
echo ============================================
echo   SETUP COMPLETE!
echo ============================================
echo.
echo  Admin Login:
echo    Email   : admin@vanservice.com
echo    Password: admin123
echo.
echo  To start the app, run:
echo    php artisan serve
echo.
echo  Then open: http://localhost:8000
echo.
echo  Or use XAMPP and open: http://localhost/van_service_laravel/public
echo.
pause

:: Ask user if they want to start the server now
set /p startserver="Start the development server now? (y/n): "
if /i "%startserver%"=="y" (
    echo.
    echo  Starting server at http://localhost:8000 ...
    echo  Press Ctrl+C to stop the server.
    echo.
    php artisan serve
)
