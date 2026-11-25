#!/usr/bin/env bash
# Build an FTP-ready layout for Bytehost shared hosting.
# Run after: composer install --no-dev --optimize-autoloader
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUT="${1:-$ROOT/../VanService_Bytehost_Upload}"
PUBLIC_ROOT="$OUT/htdocs"
APP_ROOT="$PUBLIC_ROOT/_vanservice"

if [[ ! -f "$ROOT/vendor/autoload.php" ]]; then
  echo "Missing vendor/autoload.php. Run: composer install --no-dev --optimize-autoloader" >&2
  exit 1
fi

rm -rf "$OUT"
mkdir -p "$APP_ROOT"

# The private Laravel application is deliberately stored below a protected
# folder. Files are copied explicitly so no local .env or runtime logs move.
for item in app bootstrap config database resources routes storage vendor artisan composer.json composer.lock; do
  cp -a "$ROOT/$item" "$APP_ROOT/"
done

# Laravel resolves Vite's build manifest through public_path(), while the
# browser serves assets from htdocs. Keep a private public copy for Laravel's
# manifest lookup and copy the same assets to the real website root below.
cp -a "$ROOT/public" "$APP_ROOT/public"

find "$APP_ROOT/storage/logs" -type f ! -name '.gitignore' -delete 2>/dev/null || true
find "$APP_ROOT/storage/framework/cache" -type f ! -name '.gitignore' -delete 2>/dev/null || true
find "$APP_ROOT/storage/framework/sessions" -type f ! -name '.gitignore' -delete 2>/dev/null || true
find "$APP_ROOT/storage/framework/views" -type f ! -name '.gitignore' -delete 2>/dev/null || true
rm -f "$APP_ROOT/bootstrap/cache/"*.php

# Upload these public assets directly to Bytehost htdocs, then copy the provided
# Bytehost controller and rewrite rules over Laravel's original public files.
cp -a "$ROOT/public/." "$PUBLIC_ROOT/"
cp "$ROOT/deploy/bytehost/public_html/index.php" "$PUBLIC_ROOT/index.php"
cp "$ROOT/deploy/bytehost/public_html/.htaccess" "$PUBLIC_ROOT/.htaccess"
cp "$ROOT/deploy/bytehost/bytehost-settings.php" "$APP_ROOT/bytehost-settings.php"
cp "$ROOT/deploy/bytehost/private/.htaccess" "$APP_ROOT/.htaccess"
cp "$ROOT/deploy/bytehost/fuel_records.sql" "$OUT/fuel_records.sql"
cp "$ROOT/BYTEHOST_DEPLOYMENT_GUIDE.md" "$OUT/README-BYTEHOST-DEPLOYMENT.md"

printf 'Bytehost FTP layout created at: %s\n' "$OUT"
printf 'Upload the CONTENTS of %s to the Bytehost website root.\n' "$PUBLIC_ROOT"
