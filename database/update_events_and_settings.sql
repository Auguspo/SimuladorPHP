-- Migración para añadir is_deleted y la tabla system_settings
SET @dbname = DATABASE();
SET @tablename = "session_events";
SET @columnname = "is_deleted";

-- Agregar is_deleted a session_events si no existe
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 1",
  "ALTER TABLE session_events ADD COLUMN is_deleted BOOLEAN NOT NULL DEFAULT FALSE AFTER time_ms"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Crear tabla system_settings si no existe
CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuración predeterminada de umbrales
INSERT INTO system_settings (setting_key, setting_value) VALUES
('fast_threshold_ms', '300'),
('slow_threshold_ms', '450'),
('max_timeout_ms', '8000')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
