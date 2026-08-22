<?php

declare(strict_types=1);

ini_set('error_log', __DIR__ . '/error_debug.log');

if (!defined('PROJECT_ROOT')) {
    if (@file_exists(dirname(__DIR__) . '/private/config.php')) {
        define('PROJECT_ROOT', dirname(__DIR__));
    }
    elseif (@file_exists(__DIR__ . '/private/config.php')) {
        define('PROJECT_ROOT', __DIR__);
    }
    else {
        define('PROJECT_ROOT', dirname(__DIR__));
    }
}

// Cargar configuración si aún no se ha cargado
if (file_exists(PROJECT_ROOT . '/private/config.php')) {
    require_once PROJECT_ROOT . '/private/config.php';
}
if (file_exists(PROJECT_ROOT . '/private/db.php')) {
    require_once PROJECT_ROOT . '/private/db.php';
}

// Autoloader PSR-4 simple para el namespace App\
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $base_dir = PROJECT_ROOT . '/src/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relative_class = substr($class, strlen($prefix));
    
    // Las carpetas son en minúsculas pero el namespace usa mayúsculas
    $path = explode('\\', $relative_class);
    $path[0] = strtolower($path[0]); // controllers o models
    $file = $base_dir . implode('/', $path) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Minificador al vuelo para las respuestas HTML.
 * Los archivos fuente permanecen 100% limpios y legibles para desarrollo/depuración.
 */
if (!function_exists('minify_html_output')) {
    function minify_html_output(string $buffer): string
    {
        if (trim($buffer) === '') {
            return $buffer;
        }

        // Si la respuesta es JSON, omitir minificación HTML
        foreach (headers_list() as $header) {
            if (stripos($header, 'Content-Type:') !== false && stripos($header, 'application/json') !== false) {
                return $buffer;
            }
        }

        // Eliminar comentarios HTML (conservando condicionales)
        $buffer = preg_replace('/<!--(?!\[if\s)[^>]*-->/s', '', $buffer);

        // Minificar espacios redundantes y saltos de línea entre etiquetas
        $search = [
            '/\>[^\S ]+/s',     // Espacios después de etiquetas
            '/[^\S ]+\</s',     // Espacios antes de etiquetas
            '/(\s)+/s',         // Espacios múltiples
        ];

        $replace = [
            '>',
            '<',
            '\\1',
        ];

        return preg_replace($search, $replace, $buffer) ?? $buffer;
    }
}

// Iniciar Output Buffering con minificación automática si no se han enviado encabezados
if (php_sapi_name() !== 'cli' && !headers_sent()) {
    ob_start('minify_html_output');
}
