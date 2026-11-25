<?php

/**
 * Bytehost shared-hosting front controller.
 *
 * Upload this file and the files from Laravel's public directory to your
 * Bytehost website root (usually htdocs). Upload the application code to the
 * protected _vanservice directory alongside this file.
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$applicationRoot = __DIR__ . '/_vanservice';
$settingsFile = $applicationRoot . '/bytehost-settings.php';

if (is_file($settingsFile)) {
    foreach (require $settingsFile as $key => $value) {
        $value = (string) $value;
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

if (file_exists($maintenance = $applicationRoot . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $applicationRoot . '/vendor/autoload.php';

$app = require_once $applicationRoot . '/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = tap($kernel->handle(
    $request = Request::capture()
))->send();

$kernel->terminate($request, $response);
