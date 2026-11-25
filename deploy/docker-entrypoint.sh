#!/usr/bin/env bash
set -euo pipefail

a2dismod mpm_event mpm_worker >/dev/null 2>&1 || true
a2enmod mpm_prefork >/dev/null 2>&1 || true

PORT_TO_USE="${PORT:-80}"
sed -ri "s/Listen 80/Listen ${PORT_TO_USE}/g" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT_TO_USE}>/g" /etc/apache2/sites-available/000-default.conf

if [[ -n "${DB_HOST:-}" && -n "${APP_KEY:-}" ]]; then
  php artisan migrate --force || true
  php artisan config:cache || true
  php artisan view:cache || true
fi

exec apache2-foreground
