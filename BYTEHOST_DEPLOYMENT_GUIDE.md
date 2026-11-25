# Bytehost Deployment Guide

The Laravel system can be deployed to Bytehost/ByetHost shared hosting. Bytehost's current free-hosting page lists PHP 8.3, MySQL 8/MariaDB, SSL for free subdomains, FTP, a secure file manager, and phpMyAdmin. Those meet the base runtime requirements of this Laravel 10 application, which requires PHP 8.1 or later and the standard PHP extensions documented by Laravel. [1] [2]

Bytehost's feature page does not list SSH or Composer. This guide therefore uses an **upload-ready package with prebuilt PHP dependencies**. Do not rely on server-side `composer install` or Artisan commands unless your particular Bytehost plan confirms SSH access.

## 1. Create the Bytehost site and database

Create the Bytehost account and either use its free HTTPS subdomain or attach a custom domain. In VistaPanel, create a MySQL database and user, then note the database host, database name, username, and password. Open phpMyAdmin and import a database export from the current XAMPP VanService installation. That export preserves the existing users, vans, trips, bookings, and other current records.

If the existing system did not yet have the fuel module, run `fuel_records.sql` in the same phpMyAdmin database after importing. It creates the additional fuel-record table only.

## 2. Build the Bytehost upload folder on the computer that has the Laravel source

On Windows, open Command Prompt in the Laravel project folder and run:

```bat
composer install --no-dev --optimize-autoloader
bash scripts/build-bytehost-package.sh
```

If Git Bash is not installed, use the prepared upload package supplied with this update. The script produces a folder named `VanService_Bytehost_Upload/htdocs`. Its contents are ready for FTP upload. Do not upload the original project root directly to the website folder.

## 3. Upload with FTP

The official Bytehost free plan lists a 10 MB maximum upload size and FTP access. Use FTP/FileZilla rather than relying on a large browser upload. [1]

Connect with the FTP host, username, password, and port shown in VistaPanel. Open the Bytehost website root, usually the directory named `htdocs` or the directory VistaPanel identifies as the website root. Upload **the contents** of `VanService_Bytehost_Upload/htdocs` into that website root.

After upload, the visible website root must contain `index.php`, `.htaccess`, the `build` directory, and a hidden `_vanservice` directory. The `_vanservice` directory contains the Laravel source and `vendor` dependencies. Two supplied `.htaccess` files block web access to that directory; do not delete either one. Before going live, verify that opening `https://YOUR-DOMAIN/_vanservice/` returns a **403/404** response and never reveals a directory listing.

## 4. Configure the protected production settings

Edit this server file using the Bytehost file manager:

```text
htdocs/_vanservice/bytehost-settings.php
```

Replace every `YOUR-BYTEHOST-...` value with the real HTTPS domain and MySQL details from VistaPanel. This protected PHP settings file is loaded by the supplied Bytehost front controller before Laravel starts. Keep these production settings:

```php
'APP_ENV' => 'production',
'APP_DEBUG' => 'false',
'SESSION_SECURE_COOKIE' => 'true',
```

Generate an application key locally from the Laravel project using XAMPP PHP, then replace the `APP_KEY` value in `bytehost-settings.php`:

```bat
C:\xampp\php\php.exe artisan key:generate --show
```

Do not reuse the local XAMPP `APP_KEY` and never publish the `bytehost-settings.php` file.

## 5. Set file permissions and test

Using the file manager, make these directories writable by PHP. Start with permission `755`; use `775` only if Bytehost requires it:

```text
htdocs/_vanservice/storage
htdocs/_vanservice/bootstrap/cache
```

Open the HTTPS URL in a private browser window. Confirm that the login page loads, sign in as an administrator, and add a test fuel record. Then verify booking availability prompts and a confirmed printable trip ticket.

## Driver app for online tracking

The current Android driver app automatically discovers an HTTP Laravel server only on the same Wi-Fi network. It will not automatically switch to this Bytehost HTTPS address. After the Bytehost HTTPS domain is live, provide the final domain and a new production Driver APK can be prepared with that verified public URL. Do not expose an XAMPP computer directly to the Internet.

## What I need to perform the live upload

To carry out the final FTP deployment, provide or connect the Bytehost account through a secure browser takeover. The account must already have a website/subdomain and a database created. You will need to enter the FTP and database passwords yourself; never send them in chat.

**References**

[1]: https://byet.host/free-hosting "ByetHost free hosting features"
[2]: https://laravel.com/docs/10.x/deployment "Laravel 10 deployment requirements"
