<?php

declare(strict_types=1);

if (!function_exists('loadEnvFile')) {
    function loadEnvFile(string $path): void
    {
        if (!file_exists($path) || !is_readable($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if (
                    (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))
                ) {
                    $value = substr($value, 1, -1);
                }
                if (getenv($key) === false) {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }
}

// Cargar .env si existe en private/ o en la raíz del proyecto
loadEnvFile(__DIR__ . '/.env');
loadEnvFile(dirname(__DIR__) . '/.env');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'simulador_db');
define('DB_USER', getenv('DB_USER') ?: 'admin_user');
define('DB_PASS', getenv('DB_PASS') ?: 'admin_password');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

// Token compartido simple para ESP32 u otros clientes
define('API_TOKEN', getenv('API_TOKEN') ?: '123');

