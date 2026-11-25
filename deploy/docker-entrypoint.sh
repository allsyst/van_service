#!/usr/bin/env bash
set -euo pipefail

a2dismod mpm_event mpm_worker >/dev/null 2>&1 || true
a2enmod mpm_prefork >/dev/null 2>&1 || true

PORT_TO_USE="${PORT:-80}"
echo "Entrypoint: PORT=${PORT:-unset} -> PORT_TO_USE=${PORT_TO_USE}"
echo "Entrypoint: DB_HOST_SET=$([[ -n "${DB_HOST:-}" ]] && echo yes || echo no) APP_KEY_SET=$([[ -n "${APP_KEY:-}" ]] && echo yes || echo no)"

grep -q '^ServerName ' /etc/apache2/apache2.conf || echo 'ServerName localhost' >> /etc/apache2/apache2.conf
sed -ri "s/Listen [0-9]+/Listen ${PORT_TO_USE}/g" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT_TO_USE}>/g" /etc/apache2/sites-available/000-default.conf

if [[ -n "${DB_HOST:-}" && -n "${APP_KEY:-}" ]]; then
  php artisan migrate --force || true
  php artisan config:cache || true
  php artisan view:cache || true
fi

exec apache2-foreground
