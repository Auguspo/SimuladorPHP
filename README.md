# Backend PHP/MySQL para simulador

Estructura pensada para hosting compartido tipo Hostinger:

```text
private/
  config.php
  db.php
public_html/
  .htaccess
  api/
    ingest.php  # handler interno; usar /api/telemetry
database/
  schema.sql
```

`private/` queda fuera del directorio publico y contiene credenciales. `public_html/` es lo unico expuesto por Apache.

## Modelo de datos

- `users`: usuarios del sistema administrativo (`master`, `instructor`, `visualizador`).
- `participants`: participante/conductor, con datos estables `name` y `dni`.
- `sessions`: una prueba concreta; guarda fecha y la foto variable del participante (`edad`, `peso`, `comentario`).
- `session_events`: eventos de reaccion asociados a una sesion.
- `clutch_metrics`: resumen de embrague asociado a una sesion.

## Instalacion

1. Crear la base MySQL en Hostinger.
2. Importar `database/schema.sql`.
3. Editar `private/config.php` con las credenciales reales y un `API_TOKEN` largo.
4. Subir `private/` al mismo nivel que `public_html/`.
5. Subir `public_html/` como raiz publica del sitio.

## Prueba de ingesta

PowerShell:

```powershell
$body = @{
  sesion = @{
    id = "68A1C2F03D4E"
    fecha = "2026-07-17T18:45:00"
    conductor = @{
      nombre = "Juan Perez"
      dni = "12345678"
      edad = "30"
      peso = "75"
      comentario = "Sin comentarios"
    }
    eventos = @{
      evento = @(
        @{ numero = 1; estimulo = "Freno (LED)"; resultado = "ACIERTO"; tiempo_ms = 850 },
        @{ numero = 2; estimulo = "Acelerador (Bocina)"; resultado = "ACIERTO"; tiempo_ms = 1200 },
        @{ numero = 3; estimulo = "Boton 3"; resultado = "ERROR"; tiempo_ms = 0 }
      )
    }
    embrague = @{
      conteo = 5
      tiempo_total_s = 12.4
    }
  }
} | ConvertTo-Json -Depth 10

Invoke-RestMethod `
  -Uri "http://localhost:8080/api/telemetry" `
  -Method Post `
  -Headers @{Authorization="123"} `
  -ContentType "application/json" `
  -Body $body
```
