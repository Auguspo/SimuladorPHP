DROP TABLE IF EXISTS clutch_metrics;
DROP TABLE IF EXISTS session_events;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS participants;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role ENUM('master', 'instructor', 'visualizador') NOT NULL,
    name VARCHAR(120) NOT NULL,
    first_name VARCHAR(120) NOT NULL DEFAULT '',
    last_name VARCHAR(120) NOT NULL DEFAULT '',
    dni VARCHAR(30) NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_dni (dni),
    CHECK (name <> ''),
    CHECK (password_hash <> '')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE participants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    dni VARCHAR(30) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_participants_dni (dni),
    CHECK (name <> ''),
    CHECK (dni <> '')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    external_id VARCHAR(64) NOT NULL,
    participant_id BIGINT UNSIGNED NOT NULL,
    tested_at DATETIME NOT NULL,
    participant_age TINYINT UNSIGNED NULL,
    participant_weight_kg DECIMAL(5,2) NULL,
    participant_comment TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_sessions_external_id (external_id),
    KEY idx_sessions_participant_id (participant_id),
    CONSTRAINT fk_sessions_participant
        FOREIGN KEY (participant_id) REFERENCES participants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CHECK (external_id <> ''),
    CHECK (participant_age IS NULL OR participant_age <= 120),
    CHECK (participant_weight_kg IS NULL OR participant_weight_kg <= 300)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE session_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id BIGINT UNSIGNED NOT NULL,
    event_number INT UNSIGNED NOT NULL,
    stimulus VARCHAR(80) NOT NULL,
    result ENUM('ACIERTO', 'ERROR') NOT NULL,
    time_ms INT UNSIGNED NOT NULL,
    is_deleted BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_session_events_number (session_id, event_number),
    CONSTRAINT fk_session_events_session
        FOREIGN KEY (session_id) REFERENCES sessions (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CHECK (stimulus <> ''),
    CHECK (time_ms <= 600000)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clutch_metrics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id BIGINT UNSIGNED NOT NULL,
    count INT UNSIGNED NOT NULL,
    total_time_s DECIMAL(8,3) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_clutch_metrics_session (session_id),
    CONSTRAINT fk_clutch_metrics_session
        FOREIGN KEY (session_id) REFERENCES sessions (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CHECK (count <= 1000000),
    CHECK (total_time_s >= 0 AND total_time_s <= 86400)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE system_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO system_settings (setting_key, setting_value) VALUES
('fast_threshold_ms', '300'),
('slow_threshold_ms', '450'),
('max_timeout_ms', '8000')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

