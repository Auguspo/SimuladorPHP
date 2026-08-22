<?php
require_once __DIR__ . '/bootstrap.php';
require_once PROJECT_ROOT . '/private/db.php';

try {
    $pdo = db();
    
    $sql = "
    CREATE TABLE IF NOT EXISTS session_scorings (
        session_id INT NOT NULL,
        tiempoReaccionFrenadas TINYINT NOT NULL DEFAULT 0,
        usoSistemaActivoPasivo TINYINT NOT NULL DEFAULT 0,
        frenadoAceleracionProgresiva TINYINT NOT NULL DEFAULT 0,
        respetoSenalesViales TINYINT NOT NULL DEFAULT 0,
        usoSenalizacionLuminaria TINYINT NOT NULL DEFAULT 0,
        tomaDecisionesSeguras TINYINT NOT NULL DEFAULT 0,
        evitacionManiobrasPeligrosas TINYINT NOT NULL DEFAULT 0,
        velocidadAdecuadaContexto TINYINT NOT NULL DEFAULT 0,
        conduccionSuavePredecible TINYINT NOT NULL DEFAULT 0,
        maniobrasEvasivasSeguras TINYINT NOT NULL DEFAULT 0,
        evaluacionCorrectaSalidasRiesgo TINYINT NOT NULL DEFAULT 0,
        totalScore SMALLINT NOT NULL DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (session_id),
        CONSTRAINT fk_scoring_session FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Migration completed successfully. Table 'session_scorings' created.";

} catch (PDOException $e) {
    echo "Error executing migration: " . $e->getMessage();
}
