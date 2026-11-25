<?php

/**
 * Bytehost production settings.
 *
 * This file is stored in the protected _vanservice folder. Replace every
 * placeholder using details from your Bytehost VistaPanel. Do not share this
 * file, upload it to a public repository, or place it in the htdocs root.
 */
return [
    'APP_NAME' => 'VanService',
    'APP_ENV' => 'production',
    'APP_KEY' => 'base64:REPLACE_WITH_A_NEW_APP_KEY',
    'APP_DEBUG' => 'false',
    'APP_URL' => 'https://YOUR-BYTEHOST-DOMAIN',
    'LOG_CHANNEL' => 'stack',
    'LOG_LEVEL' => 'error',

    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => 'YOUR-BYTEHOST-MYSQL-HOST',
    'DB_PORT' => '3306',
    'DB_DATABASE' => 'YOUR-BYTEHOST-DB-NAME',
    'DB_USERNAME' => 'YOUR-BYTEHOST-DB-USER',
    'DB_PASSWORD' => 'YOUR-BYTEHOST-DB-PASSWORD',

    'BROADCAST_DRIVER' => 'log',
    'CACHE_DRIVER' => 'file',
    'FILESYSTEM_DISK' => 'local',
    'QUEUE_CONNECTION' => 'sync',
    'SESSION_DRIVER' => 'file',
    'SESSION_LIFETIME' => '120',
    'SESSION_SECURE_COOKIE' => 'true',

    'MAIL_MAILER' => 'log',
    'MAIL_FROM_ADDRESS' => 'noreply@YOUR-BYTEHOST-DOMAIN',
    'MAIL_FROM_NAME' => 'VanService',

    // Populated with a random value by build-bytehost-install-package.sh.
    'INSTALL_TOKEN' => 'REPLACE_WITH_A_RANDOM_INSTALL_TOKEN',
];
