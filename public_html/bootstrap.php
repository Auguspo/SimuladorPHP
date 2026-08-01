<?php

declare(strict_types=1);

if (!defined('PROJECT_ROOT')) {
    // 1. Si private/ está un nivel arriba de public_html (ej. /home/usuario/private)
    if (file_exists(dirname(__DIR__) . '/private/config.php')) {
        define('PROJECT_ROOT', dirname(__DIR__));
    }
    // 2. Si private/ está dentro de public_html (ej. /home/usuario/public_html/private)
    elseif (file_exists(__DIR__ . '/private/config.php')) {
        define('PROJECT_ROOT', __DIR__);
    }
    // 3. Fallback predeterminado (un nivel arriba)
    else {
        define('PROJECT_ROOT', dirname(__DIR__));
    }
}

// Cargar configuración si aún no se ha cargado
if (file_exists(PROJECT_ROOT . '/private/config.php')) {
    require_once PROJECT_ROOT . '/private/config.php';
}

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
