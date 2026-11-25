#!/usr/bin/env bash
# Build a one-time, domain-configured Bytehost package without storing a DB password.
# Usage:
#   bash scripts/build-bytehost-install-package.sh <domain> <db-host> <db-name> <db-user> [output-directory]
set -euo pipefail

if [[ $# -lt 4 || $# -gt 5 ]]; then
  echo 'Usage: bash scripts/build-bytehost-install-package.sh <domain> <db-host> <db-name> <db-user> [output-directory]' >&2
  exit 1
fi

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DOMAIN="$1"
DB_HOST="$2"
DB_NAME="$3"
DB_USER="$4"
OUT="${5:-$ROOT/../VanService_Bytehost_${DOMAIN//[^A-Za-z0-9]/_}}"
ARCHIVE_NAME="$(basename "$OUT").zip"

if [[ ! "$DOMAIN" =~ ^[A-Za-z0-9.-]+$ || ! "$DB_HOST" =~ ^[A-Za-z0-9.-]+$ || ! "$DB_NAME" =~ ^[A-Za-z0-9_]+$ || ! "$DB_USER" =~ ^[A-Za-z0-9_]+$ ]]; then
  echo 'The domain, MySQL host, database name, or database user contains unsupported characters.' >&2
  exit 1
fi

bash "$ROOT/scripts/build-bytehost-package.sh" "$OUT"

APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
INSTALL_TOKEN="$(php -r 'echo bin2hex(random_bytes(24));')"
SETTINGS="$OUT/htdocs/_vanservice/bytehost-settings.php"

DOMAIN="$DOMAIN" DB_HOST="$DB_HOST" DB_NAME="$DB_NAME" DB_USER="$DB_USER" APP_KEY="$APP_KEY" INSTALL_TOKEN="$INSTALL_TOKEN" SETTINGS="$SETTINGS" python3 - <<'PY'
import os
from pathlib import Path

path = Path(os.environ['SETTINGS'])
text = path.read_text()
replacements = {
    'base64:REPLACE_WITH_A_NEW_APP_KEY': os.environ['APP_KEY'],
    'https://YOUR-BYTEHOST-DOMAIN': 'https://' + os.environ['DOMAIN'],
    'YOUR-BYTEHOST-MYSQL-HOST': os.environ['DB_HOST'],
    'YOUR-BYTEHOST-DB-NAME': os.environ['DB_NAME'],
    'YOUR-BYTEHOST-DB-USER': os.environ['DB_USER'],
    'noreply@YOUR-BYTEHOST-DOMAIN': 'noreply@' + os.environ['DOMAIN'],
    'REPLACE_WITH_A_RANDOM_INSTALL_TOKEN': os.environ['INSTALL_TOKEN'],
}
for old, new in replacements.items():
    text = text.replace(old, new)
path.write_text(text)
PY

cp "$ROOT/deploy/bytehost/public_html/install.php" "$OUT/htdocs/install.php"
cat > "$OUT/INSTALL-URL.txt" <<EOF
VanService Bytehost one-time installation URL

1. Upload the CONTENTS of the htdocs folder to your Bytehost htdocs folder.
2. In htdocs/_vanservice/bytehost-settings.php, replace only the DB_PASSWORD placeholder with your MySQL database password.
3. Open this link once in a browser:

https://${DOMAIN}/install.php?token=${INSTALL_TOKEN}

The installer creates all database tables and the default accounts, then locks itself.
Do not share this URL. Delete this file after installation.
EOF
cat > "$OUT/README-QUICK-INSTALL.txt" <<EOF
BYTEHOST QUICK INSTALL

1. In VistaPanel, open Online File Manager and enter htdocs.
2. Upload ${ARCHIVE_NAME} to htdocs, then extract it.
3. Extraction creates a folder with an htdocs folder inside it. Open that inner htdocs folder, select everything inside it, and move those items into the real htdocs directory. The real htdocs directory must contain index.php, install.php, build, and _vanservice.
4. Edit htdocs/_vanservice/bytehost-settings.php. Replace only DB_PASSWORD with the MySQL database password you chose. Do not edit the other values.
5. Open the one-time link in INSTALL-URL.txt.
6. When the completion page appears, open the main domain and sign in.

Default accounts after installation:
Admin: admin@vanservice.com / admin123
Driver: driver@vanservice.com / driver123

Change both passwords after first use. Do not share bytehost-settings.php or INSTALL-URL.txt.
EOF

printf 'Configured Bytehost installer created at: %s\n' "$OUT"
printf 'One-time install URL stored in: %s/INSTALL-URL.txt\n' "$OUT"
