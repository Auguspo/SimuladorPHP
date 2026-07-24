# Simulador de Telemetría (Backend PHP/MySQL)

Este proyecto provee una arquitectura MVC para recibir, almacenar y visualizar métricas generadas por un simulador físico (ej. ESP32, Arduino) usando PHP y MySQL.

## Arquitectura del Proyecto

El sistema está dividido para mantener alta seguridad y separación de responsabilidades:
- `public_html/`: Archivos expuestos al público (CSS, JS, Index).
- `src/`: Lógica de la aplicación (Controladores y Vistas en HTML/CSS).
- `private/`: Configuraciones críticas y conexión a base de datos (oculto al público).
- `database/`: Scripts SQL de creación de base de datos.

## Instalación en una PC Limpia (Modo Local)

Gracias a Docker, puedes levantar el proyecto completo en cualquier PC sin necesidad de instalar PHP ni MySQL manualmente.

1. **Requisitos:** Tener instalado **Docker Desktop** (y tenerlo abierto/corriendo).
2. Abre una terminal en la carpeta principal del proyecto.
3. Ejecuta el comando para encender el servidor y la base de datos:
   ```bash
   docker compose up -d
   ```
4. **Crear usuario administrador:**
   La primera vez que levantas el proyecto, la base de datos estará vacía. Para crear el usuario por defecto, abre tu navegador y entra a:
   `http://localhost:8080/crear_admin`
   *(Esto creará un usuario `admin` con contraseña `admin123` y luego el archivo se autodestruirá por seguridad).*

5. **Acceder a la Base de Datos (phpMyAdmin):**
   Tu entorno local ya incluye phpMyAdmin para visualizar y editar las tablas gráficamente.
   - **URL:** `http://localhost:8081`
   - **Usuario:** `root`
   - **Contraseña:** `root_password`

## Cómo probar el Endpoint de Telemetría

Una vez que el proyecto esté corriendo localmente, el servidor **no bloqueará las peticiones de tu simulador** (a diferencia de hostings gratuitos como InfinityFree que inyectan validaciones JavaScript).

### 1. Enviar datos desde tu Simulador (Placa Física / ESP32)
Si tu ESP32 está conectado al mismo WiFi que la computadora donde corre Docker, no puedes usar `localhost`. Debes apuntarlo a la IP local de tu PC:
- **Endpoint URL:** `http://192.168.1.X:8080/api/telemetry` *(Reemplaza la X por tu IP real)*.
- **Método:** `POST`
- **Header:** `Authorization: 123`
- **Body:** JSON con los datos.

### 2. Prueba Rápida desde PowerShell (Misma PC)
Para confirmar rápidamente que el sistema inserta bien en la base de datos, puedes correr este script en PowerShell:

```powershell
$body = @{
    external_id = "sesion_test_001"
    tested_at = "2026-07-24 10:00:00"
    conductor = "Piloto Prueba"
    events = @(
        @{ timestamp = "2026-07-24 10:00:01"; input_type = "throttle"; value = 1.0 },
        @{ timestamp = "2026-07-24 10:00:03"; input_type = "brake"; value = 1.0 }
    )
} | ConvertTo-Json -Depth 10

Invoke-RestMethod `
    -Uri "http://localhost:8080/api/telemetry" `
    -Method Post `
    -Headers @{"Authorization" = "123"} `
    -ContentType "application/json" `
    -Body $body
```

Si todo va bien, te responderá confirmando la subida y podrás ver la sesión en tu panel web entrando a `http://localhost:8080`.
