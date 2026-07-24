<?php

declare(strict_types=1);

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'u123456789_simulador');
define('DB_USER', getenv('DB_USER') ?: 'u123456789_backend');
define('DB_PASS', getenv('DB_PASS') ?: 'cambiar_esta_password');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

// ponytail: token compartido simple para ESP32; si hay varios dispositivos, migrar a tokens por dispositivo.
define('API_TOKEN', getenv('API_TOKEN') ?: '123');
