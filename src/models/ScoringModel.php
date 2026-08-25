<?php

namespace App\Models;

use PDO;

class ScoringModel extends BaseModel
{
    public function getBySessionId(int $sessionId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM session_scorings WHERE session_id = :session_id');
        $stmt->execute([':session_id' => $sessionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ?: null;
    }

    public function saveScoring(int $sessionId, array $data): void
    {
        $sql = "INSERT INTO session_scorings (
            session_id, tiempoReaccionFrenadas, usoSistemaActivoPasivo, frenadoAceleracionProgresiva,
            respetoSenalesViales, usoSenalizacionLuminaria, tomaDecisionesSeguras, evitacionManiobrasPeligrosas,
            velocidadAdecuadaContexto, conduccionSuavePredecible, maniobrasEvasivasSeguras, evaluacionCorrectaSalidasRiesgo,
            totalScore
        ) VALUES (
            :session_id, :v1, :v2, :v3, :v4, :v5, :v6, :v7, :v8, :v9, :v10, :v11, :total
        ) ON DUPLICATE KEY UPDATE
            tiempoReaccionFrenadas = VALUES(tiempoReaccionFrenadas),
            usoSistemaActivoPasivo = VALUES(usoSistemaActivoPasivo),
            frenadoAceleracionProgresiva = VALUES(frenadoAceleracionProgresiva),
            respetoSenalesViales = VALUES(respetoSenalesViales),
            usoSenalizacionLuminaria = VALUES(usoSenalizacionLuminaria),
            tomaDecisionesSeguras = VALUES(tomaDecisionesSeguras),
            evitacionManiobrasPeligrosas = VALUES(evitacionManiobrasPeligrosas),
            velocidadAdecuadaContexto = VALUES(velocidadAdecuadaContexto),
            conduccionSuavePredecible = VALUES(conduccionSuavePredecible),
            maniobrasEvasivasSeguras = VALUES(maniobrasEvasivasSeguras),
            evaluacionCorrectaSalidasRiesgo = VALUES(evaluacionCorrectaSalidasRiesgo),
            totalScore = VALUES(totalScore)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':session_id' => $sessionId,
            ':v1' => (int)($data['tiempoReaccionFrenadas'] ?? 0),
            ':v2' => (int)($data['usoSistemaActivoPasivo'] ?? 0),
            ':v3' => (int)($data['frenadoAceleracionProgresiva'] ?? 0),
            ':v4' => (int)($data['respetoSenalesViales'] ?? 0),
            ':v5' => (int)($data['usoSenalizacionLuminaria'] ?? 0),
            ':v6' => (int)($data['tomaDecisionesSeguras'] ?? 0),
            ':v7' => (int)($data['evitacionManiobrasPeligrosas'] ?? 0),
            ':v8' => (int)($data['velocidadAdecuadaContexto'] ?? 0),
            ':v9' => (int)($data['conduccionSuavePredecible'] ?? 0),
            ':v10' => (int)($data['maniobrasEvasivasSeguras'] ?? 0),
            ':v11' => (int)($data['evaluacionCorrectaSalidasRiesgo'] ?? 0),
            ':total' => (int)($data['totalScore'] ?? 0),
        ]);
    }
}
