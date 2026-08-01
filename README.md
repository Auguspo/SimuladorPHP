# Simulador de Telemetría PHP

Plataforma web para la gestión, análisis y monitoreo en tiempo real de pruebas de telemetría de conductores y tiempo de reacción.

---

## 🌟 Características Principales

1. **Gestión y Jerarquía de Usuarios (Roles & Seguridad)**
   - **`master`**: Administrador del sistema con acceso total. No se puede asignar desde la interfaz.
   - **`instructor`**: Puede crear usuarios, restablecer contraseñas, editar nombres/apellidos, alternar roles (`instructor` <-> `visualizador`), activar/desactivar accesos (`is_active`), ajustar umbrales de reacción y eliminar/restaurar eventos de prueba.
   - **`visualizador`**: Rol de solo lectura para consulta de estadísticas y métricas sin permisos de modificación ni borrado de eventos.
   - **Bloqueo Inteligente de Usuarios (`is_active`)**: Permite deshabilitar el acceso a un usuario sin destruir su hash de contraseña original.

2. **Configuración Dinámica de Umbrales de Reacción (0 a 8.000 ms)**
   - Umbrales configurables desde la pestaña de **Configuración / Menú**:
     - **Límite de Acierto / Rápido (`< ms`)**: Ej. 300 ms.
     - **Límite de Fallo / Lento (`> ms`)**: Ej. 450 ms.
     - **Timeout del Sistema**: Fijado en 8.000 ms (8 seg).
     - Validación estricta: Los límites de acierto y fallo no pueden ser iguales.

3. **Borrado Lógico y Restauración de Eventos (`is_deleted`)**
   - Los instructores y masters pueden descartar (`is_deleted = true`) o revivir/restaurar (`is_deleted = false`) eventos individuales por sesión.
   - Filtro de eventos desplegable:
     - **N (Vigentes - Predeterminado)**: Computa solo eventos válidos.
     - **Y (Solo Borrados)**: Muestra eventos marcados como eliminados.
     - **ALL (Todos)**: Incluye la totalidad de los eventos destacando visualmente los tachados.

4. **Formatos Regionales de Argentina (Fechas y Decimales)**
   - Fechas formateadas como **`DD/MM/YYYY`** (ej. `01/08/2026`).
   - Formato numérico con **coma decimal `,`** (ej. `14,7`, `14,42 s`, `83,3%`).

5. **Navegación e Interfaz de Usuario (UX/UI)**
   - **Ordenamiento Interactivo de Columnas (3 Estados)**: Clic en cabeceras para alternar entre `Ascendente (▲)`, `Descendente (▼)` y `Por Defecto (↕)`.
   - **Paginación Dinámica (50 ítems por página por defecto)**: Selector desplegable (`10`, `25`, `50`, `100` ítems) con navegación de páginas.
   - **Minificación al Vuelo de HTML**: Compresión automática en servidor via `ob_start` manteniendo el código fuente 100% limpio y legible para desarrollo/depuración.
   - **Diseño Mobile Responsive**: Adaptable a smartphones, tablets y pantallas de escritorio sin franjas laterales desbordadas.

---

## 🛠️ Requisitos e Instalación Local (Docker)

```bash
# 1. Clonar el repositorio
git clone <URL_DEL_REPOSITORIO>
cd SimuladorPHP

# 2. Levantar el entorno en Docker (PHP 8.2 + MySQL 8.0 + Nginx)
docker compose up -d --build

# 3. Acceder en el navegador
http://localhost:8080/
```

---

## 🗄️ Estructura de la Base de Datos y Migraciones

La base de datos incluye las tablas: `users`, `participants`, `sessions`, `session_events`, `clutch_metrics` y `system_settings`.

### Scripts de Migración en `/database`:
- `database/schema.sql`: Estructura inicial completa del sistema.
- `database/update_users_table.sql`: Migración de usuarios (`first_name`, `last_name`, `is_active`).
- `database/update_events_and_settings.sql`: Migración de borrado lógico (`is_deleted`) y tabla `system_settings`.

---

## 🚀 Despliegue en Hostinger

Consulta la guía detallada de despliegue en [README_HOSTINGER.md](README_HOSTINGER.md).
