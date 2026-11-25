<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

/**
 * One-time Bytehost installer.
 *
 * The package builder copies this file to htdocs/install.php. It is protected
 * by the random INSTALL_TOKEN generated for each package and deletes itself
 * after a successful installation.
 */

$applicationRoot = __DIR__ . '/_vanservice';
$settingsFile = $applicationRoot . '/bytehost-settings.php';
$lockFile = $applicationRoot . '/bytehost-install.lock';

function installerPage(string $title, string $message, int $status = 200): never
{
    http_response_code($status);
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    echo <<<HTML
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$safeTitle} · VanService</title>
  <style>
    body { margin: 0; padding: 2rem; background: #f3f7fb; color: #152033; font-family: system-ui, -apple-system, sans-serif; }
    main { max-width: 640px; margin: 8vh auto; padding: 2rem; background: #fff; border-radius: 14px; box-shadow: 0 12px 32px rgba(15, 35, 60, .12); }
    h1 { margin-top: 0; color: #0c5b8d; font-size: 1.55rem; }
    p { line-height: 1.6; white-space: pre-line; }
    a { display: inline-block; margin-top: .8rem; color: #fff; background: #0c5b8d; padding: .7rem 1rem; border-radius: 7px; text-decoration: none; }
  </style>
</head>
<body><main><h1>{$safeTitle}</h1><p>{$safeMessage}</p></main></body>
</html>
HTML;
    exit;
}

/**
 * Bytehost's file-manager upload can leave newly extracted Laravel folders
 * non-writable to PHP. Prepare only Laravel runtime folders before the app
 * bootstraps, trying the secure group mode first and a shared-host fallback
 * only when the server reports that it is still not writable.
 */
function prepareRuntimeDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        @mkdir($directory, 0775, true);
    }

    @chmod($directory, 0775);

    if (!is_writable($directory)) {
        @chmod($directory, 0777);
    }
}

if (!is_file($settingsFile)) {
    installerPage('Installation package is incomplete', 'The protected Bytehost settings file is missing. Upload the complete package contents again.', 500);
}

/** @var array<string, string> $settings */
$settings = require $settingsFile;
$installToken = (string) ($settings['INSTALL_TOKEN'] ?? '');

if ($installToken === '' || !hash_equals($installToken, (string) ($_GET['token'] ?? ''))) {
    installerPage('Installation link required', 'Open the exact one-time installation URL from INSTALL-URL.txt included in your uploaded package.', 403);
}

if (is_file($lockFile)) {
    installerPage('VanService is already installed', 'The installer has been locked. Open the site normally.', 410);
}

if (empty($settings['DB_PASSWORD']) || str_contains((string) $settings['DB_PASSWORD'], 'YOUR-BYTEHOST-')) {
    installerPage('Database password is required', 'Edit htdocs/_vanservice/bytehost-settings.php in the Bytehost File Manager. Replace the DB_PASSWORD placeholder with your MySQL database password, save it, and then reopen this installation URL.', 422);
}

foreach ([
    $applicationRoot . '/storage',
    $applicationRoot . '/storage/app',
    $applicationRoot . '/storage/framework',
    $applicationRoot . '/storage/framework/cache',
    $applicationRoot . '/storage/framework/sessions',
    $applicationRoot . '/storage/framework/views',
    $applicationRoot . '/storage/logs',
    $applicationRoot . '/bootstrap/cache',
] as $runtimeDirectory) {
    prepareRuntimeDirectory($runtimeDirectory);
}

foreach ($settings as $key => $value) {
    if (is_string($key) && (is_string($value) || is_numeric($value) || is_bool($value))) {
        $value = (string) $value;
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

// Keep one-time setup independent of shared-host filesystem permissions. The
// installed site returns to file-backed sessions/cache after setup, but the
// migration process itself does not need to create Laravel runtime files.
foreach ([
    'LOG_CHANNEL' => 'errorlog',
    'CACHE_DRIVER' => 'array',
    'SESSION_DRIVER' => 'array',
    'VIEW_COMPILED' => sys_get_temp_dir() . '/vanservice-views',
] as $key => $value) {
    putenv($key . '=' . $value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

if (file_exists($maintenance = $applicationRoot . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $applicationRoot . '/vendor/autoload.php';

$app = require_once $applicationRoot . '/bootstrap/app.php';

try {
    /** @var Kernel $kernel */
    $kernel = $app->make(Kernel::class);

    if ($kernel->call('migrate', ['--force' => true]) !== 0) {
        installerPage('Database installation could not finish', 'Check the MySQL host, database name, username, and password in bytehost-settings.php. If they are correct, wait one minute and try the same installation URL again.', 500);
    }

    if ($kernel->call('db:seed', ['--force' => true]) !== 0) {
        installerPage('Initial data setup could not finish', 'The tables were created, but the default administrator could not be added. Try the same installation URL again.', 500);
    }

    // Make a best-effort lock. If Bytehost refuses this write, migrations and
    // seeding remain safe to repeat because Laravel records applied migrations
    // and the supplied seed uses updateOrCreate.
    @file_put_contents($lockFile, 'Installed at ' . gmdate('c') . PHP_EOL, LOCK_EX);

    // If the hosting filesystem permits it, remove the public installer after success.
    @unlink(__FILE__);

    $url = (string) ($settings['APP_URL'] ?? '/');
    installerPage('VanService is installed', "The database tables and default accounts were created successfully.\n\nOpen your site: {$url}\n\nSign in with:\nAdmin: admin@vanservice.com / admin123\nDriver: driver@vanservice.com / driver123\n\nChange both passwords after your first test.");
} catch (Throwable $exception) {
    $message = 'The installer could not complete. Check the MySQL host, database name, user, and password in bytehost-settings.php, then try again.';

    if (str_contains($exception->getMessage(), 'Permission denied')) {
        $message = 'Bytehost did not allow Laravel to write a runtime file. Re-upload the current install.php fix and open the same installation URL again.';
    } elseif ($exception instanceof \PDOException || str_contains($exception->getMessage(), 'SQLSTATE')) {
        $message = 'Laravel could not connect to MySQL. Confirm that DB_HOST is sql310.byethost22.com and that DB_DATABASE, DB_USERNAME, and DB_PASSWORD exactly match your Bytehost account settings.';
    }

    installerPage('Installation could not finish', $message, 500);
}
