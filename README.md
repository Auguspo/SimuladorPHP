# Simulador de Manejo Defensivo - Backend y API (PHP)

Este repositorio contiene el backend, la base de datos y la interfaz de supervisión del **Simulador de Manejo Defensivo**. Está diseñado para recibir telemetría de una placa ESP32, guardar el registro histórico de los conductores, y presentar las estadísticas en un panel de control web.

## 🚀 Arquitectura

El proyecto está desarrollado en **PHP puro (Vanilla)** sin frameworks pesados, asegurando máxima velocidad de respuesta y fácil despliegue en cualquier hosting compartido o servidor local (Apache/Nginx).

La estructura de carpetas sigue el principio de seguridad donde la lógica crítica se encuentra fuera del alcance público:

*   **`public_html/`**: Carpeta raíz del servidor web (Document Root). Contiene los puntos de entrada accesibles públicamente, vistas, CSS/JS y el enrutador principal (`.htaccess`).
*   **`public_html/api/`**: Endpoints RESTful de uso exclusivo para la ESP32 (ingesta de datos) y consumo interno de Ajax (estadísticas).
*   **`private/`**: Archivos de configuración, conexión a la base de datos y control de sesión (rutas no expuestas).
*   **`database/`**: Scripts SQL con el esquema de la base de datos (tablas `users`, `participants`, `sessions`, `session_events`, `clutch_metrics`).

## ✨ Características Principales

-   **Ingesta Segura:** El endpoint `/api/telemetry` valida estrictamente el JSON entrante de la ESP32 (verificando tipos de datos, límites de rango y estímulos permitidos) antes de insertar los datos usando *Prepared Statements* (PDO) dentro de una transacción, evitando cualquier tipo de inyección SQL o corrupción de datos.
-   **Enrutamiento Limpio:** Uso de mod_rewrite en `.htaccess` para generar URLs amigables (ej. `/login`, `/estadisticas`) sin exponer extensiones `.php`.
-   **Minificación al Vuelo:** El archivo `bootstrap.php` implementa un *Output Buffer* que limpia y minifica el HTML antes de enviarlo al navegador del usuario, reduciendo el consumo de ancho de banda.
-   **Seguridad y Autenticación:** Control de acceso mediante variables de sesión. Protección de los endpoints de la API mediante un token `Bearer`.

## 🛠️ Instalación y Despliegue

1.  **Requisitos:** PHP 8.0+ y MySQL/MariaDB.
2.  **Base de Datos:** Importa el archivo `database/schema.sql` en tu gestor de base de datos MySQL.
3.  **Configuración:** Configura las credenciales de la base de datos en `private/config.php` o crea un archivo `.env` en la raíz del proyecto.
4.  **Servidor Web:** Configura tu Apache/Nginx para que el `DocumentRoot` apunte a la carpeta `public_html/`.

## 🔒 Seguridad
Se han eliminado los scripts de prueba públicos y se ha forzado un enrutamiento que protege los archivos base. Asegúrate de modificar el usuario maestro generado en la base de datos para utilizar una contraseña fuerte.
