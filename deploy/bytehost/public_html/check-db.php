<?php

declare(strict_types=1);

/**
 * Temporary Bytehost database diagnostic.
 * Upload to htdocs/check-db.php and open it with the existing one-time token.
 * It never displays the configured password. Delete it once the connection is
 * confirmed.
 */

$settingsFile = __DIR__ . '/_vanservice/bytehost-settings.php';

function databaseDiagnosticPage(string $title, string $body, int $status = 200): never
{
    http_response_code($status);
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeBody = htmlspecialchars($body, ENT_QUOTES, 'UTF-8');

    echo <<<HTML
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{$safeTitle} · VanService</title>
<style>body{margin:0;padding:2rem;background:#f3f7fb;color:#152033;font-family:system-ui,-apple-system,sans-serif}main{max-width:720px;margin:8vh auto;padding:2rem;background:#fff;border-radius:14px;box-shadow:0 12px 32px rgba(15,35,60,.12)}h1{margin-top:0;color:#0c5b8d;font-size:1.55rem}pre{padding:1rem;background:#f7f9fc;border-radius:8px;overflow:auto;white-space:pre-wrap;word-break:break-word}p{line-height:1.6}</style>
</head><body><main><h1>{$safeTitle}</h1><pre>{$safeBody}</pre></main></body></html>
HTML;
    exit;
}

if (!is_file($settingsFile)) {
    databaseDiagnosticPage('Settings file is missing', 'Upload the full VanService package first.', 500);
}

/** @var array<string, string> $settings */
$settings = require $settingsFile;
$installToken = (string) ($settings['INSTALL_TOKEN'] ?? '');

if ($installToken === '' || !hash_equals($installToken, (string) ($_GET['token'] ?? ''))) {
    databaseDiagnosticPage('Diagnostic link required', 'Open this page with the same ?token= value used for install.php.', 403);
}

$host = (string) ($settings['DB_HOST'] ?? '');
$port = (string) ($settings['DB_PORT'] ?? '3306');
$database = (string) ($settings['DB_DATABASE'] ?? '');
$username = (string) ($settings['DB_USERNAME'] ?? '');
$password = (string) ($settings['DB_PASSWORD'] ?? '');

$summary = "Host: {$host}\nPort: {$port}\nDatabase: {$database}\nUsername: {$username}\nPassword saved: " . (($password !== '' && !str_contains($password, 'YOUR-BYTEHOST-')) ? 'YES' : 'NO') . "\n\n";

if ($password === '' || str_contains($password, 'YOUR-BYTEHOST-')) {
    databaseDiagnosticPage('Database password is not saved', $summary . 'Edit htdocs/_vanservice/bytehost-settings.php and set DB_PASSWORD to your current Bytehost VistaPanel password. Do not share it in chat.', 422);
}

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 10]
    );
    $pdo->query('SELECT 1');
    databaseDiagnosticPage('MySQL connection successful', $summary . 'Laravel can connect to your Bytehost database. Return to the install.php link and run the installer again. Delete check-db.php afterward.');
} catch (PDOException $exception) {
    $error = preg_replace('/(password\s*[=:]\s*)[^\s;]*/i', '$1[hidden]', $exception->getMessage()) ?: 'Unknown database error';
    $guidance = 'Open a screenshot of this page in your reply. The error code below identifies the remaining issue.';

    if (str_contains($error, 'using password: NO')) {
        $guidance = 'The password was not saved in bytehost-settings.php. Set DB_PASSWORD again, save, and reload this page.';
    } elseif (str_contains($error, 'Access denied')) {
        $guidance = 'Bytehost rejected the username/password. Reset your VistaPanel password, wait two minutes, then update DB_PASSWORD to that same exact password.';
    } elseif (str_contains($error, 'getaddrinfo') || str_contains($error, 'Connection refused')) {
        $guidance = 'The MySQL host is not reachable. Confirm DB_HOST from the MySQL Databases page in VistaPanel.';
    } elseif (str_contains($error, 'could not find driver')) {
        $guidance = 'Bytehost PHP is missing the MySQL PDO driver. This free account cannot run the Laravel system until Bytehost enables pdo_mysql.';
    }

    databaseDiagnosticPage('MySQL connection test failed', $summary . "Error: {$error}\n\nNext step: {$guidance}", 500);
}
