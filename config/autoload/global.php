<?php

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return [
    'db' => [
        'driver' => 'Pdo_Pgsql',
        'hostname' => '127.0.0.1',
        'port' => 5432,
        'database' => 'goldai',
        'username' => 'postgres',
        'password' => 'postgres',
        'charset' => 'utf8',
    ],
    'session_config' => [
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'gc_maxlifetime' => 60 * 60 * 8,
    ],
];
