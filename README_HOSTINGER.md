# Guía de Despliegue en Hostinger (Hosting Compartido)

Esta guía te explica cómo desplegar el **Simulador de Telemetría** en Hostinger en menos de 5 minutos.

---

## Opción Recomendada (Máxima Seguridad - Estructura Multi-carpeta)

En esta opción, las credenciales y el código interno (`private/` y `src/`) quedan almacenados fuera del directorio público web, haciendo imposible su acceso vía HTTP.

### Paso 1: Crear la Base de Datos en Hostinger
1. Inicia sesión en tu panel de Hostinger (**hPanel**).
2. Ve a **Bases de Datos MySQL**.
3. Crea una nueva base de datos (ej. `u123456789_simulador`) y un usuario con su contraseña.
4. Entra a **phpMyAdmin** desde Hostinger en tu nueva base de datos.
5. Haz clic en **Importar** y selecciona el archivo `database/schema.sql`.

### Paso 2: Subir Archivos por Administrador de Archivos o FTP
1. En el Administrador de Archivos de Hostinger, dirígete a la raíz de tu cuenta (`/home/u123456789/`).
2. Sube las carpetas `private/` y `src/` directamente a la raíz (al mismo nivel que la carpeta `public_html`).
3. Entra a la carpeta `public_html/` de Hostinger y sube allí el contenido de la carpeta `public_html/` del proyecto.

### Paso 3: Configurar Credenciales
1. Dentro de la carpeta `private/` en Hostinger, crea o edita un archivo `.env` (basándote en `.env.example`) con las credenciales creadas en el Paso 1:
   ```env
   DB_HOST=localhost
   DB_NAME=u123456789_simulador
   DB_USER=u123456789_admin
   DB_PASS=TuPasswordSegura123!
   API_TOKEN=123
   ```

### Paso 4: Crear Administrador Inicial
1. Abre tu navegador e ingresa a: `https://tudominio.com/crear_admin`
2. Verás la confirmación de creación del usuario `admin` / `admin123`.
3. ¡Listo! Ya puedes ingresar a `https://tudominio.com/login`.

---

## Opción 2: Todo en `public_html` (Carpeta Única)

Si prefieres subir todo dentro de `public_html/`:
1. Sube las carpetas `public_html/`, `private/` y `src/` dentro de `public_html/`.
2. Los archivos `.htaccess` incluidos dentro de `private/` y `src/` bloquearán automáticamente cualquier acceso web directo a esas carpetas.
3. El archivo `bootstrap.php` detectará automáticamente esta estructura sin necesidad de cambiar ninguna línea de código.
