<?php
require_once __DIR__ . '/bootstrap.php';
require_once PROJECT_ROOT . '/private/auth.php';

use App\Models\SessionModel;
use App\Models\ScoringModel;

$sessionId = $_GET['id'] ?? null;
if (!$sessionId || !ctype_digit($sessionId)) {
    die("ID de sesión inválido");
}

$sessionModel = new SessionModel();
$session = $sessionModel->getByIdOrExternal($sessionId);

if (!$session) {
    die("Sesión no encontrada");
}

$scoringModel = new ScoringModel();
$scoring = $scoringModel->getBySessionId((int)$sessionId);

if (!$scoring) {
    die("No hay scoring guardado para esta sesión");
}

$filename = "Scoring_" . preg_replace('/[^a-zA-Z0-9_-]/', '_', $session['participant_name']) . "_" . date('Y-m-d', strtotime($session['tested_at'])) . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

$questions = [
    'tiempoReaccionFrenadas' => 'Tiempo de reacción ante frenadas',
    'usoSistemaActivoPasivo' => 'Uso de sistema activo y pasivo',
    'frenadoAceleracionProgresiva' => 'Frenado y aceleración progresiva',
    'respetoSenalesViales' => 'Respeto de señales viales',
    'usoSenalizacionLuminaria' => 'Uso de señalización luminaria',
    'tomaDecisionesSeguras' => 'Toma de decisiones seguras',
    'evitacionManiobrasPeligrosas' => 'Evitación de maniobras peligrosas o temerarias',
    'velocidadAdecuadaContexto' => 'Velocidad adecuada al contexto',
    'conduccionSuavePredecible' => 'Conducción suave y predecible',
    'maniobrasEvasivasSeguras' => 'Maniobras evasivas seguras',
    'evaluacionCorrectaSalidasRiesgo' => 'Evaluación correcta de salidas de riesgo'
];

echo "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">";
echo "<head><meta charset=\"UTF-8\"></head>";
echo "<body>";
echo "<table border='1' style='font-family: Arial, sans-serif; border-collapse: collapse;'>";

// Header Info
echo "<tr>";
echo "<th colspan='2' style='background-color: #f3f4f6; text-align: left; padding: 10px;'>DATOS DEL PARTICIPANTE</th>";
echo "<th colspan='4' style='background-color: #f3f4f6; text-align: left; padding: 10px;'>FECHA DE LA PRUEBA</th>";
echo "<th style='background-color: #f3f4f6; text-align: left; padding: 10px;'>DNI</th>";
echo "</tr>";

echo "<tr>";
echo "<td colspan='2' style='padding: 5px; font-weight: bold;'>" . htmlspecialchars($session['participant_name']) . "</td>";
echo "<td colspan='4' style='padding: 5px; font-weight: bold;'>" . date('d/m/Y H:i', strtotime($session['tested_at'])) . "</td>";
echo "<td style='padding: 5px; font-weight: bold;'>" . htmlspecialchars($session['participant_dni'] ?? '-') . "</td>";
echo "</tr>";

// Empty row separator
echo "<tr><td colspan='7'></td></tr>";

// Table Headers
echo "<tr>";
echo "<th style='background-color: #e5e7eb; padding: 10px; text-align: left;'>MÉTRICA / EVALUACIÓN</th>";
echo "<th style='background-color: #e5e7eb; padding: 10px; width: 40px; text-align: center;'>1</th>";
echo "<th style='background-color: #e5e7eb; padding: 10px; width: 40px; text-align: center;'>2</th>";
echo "<th style='background-color: #e5e7eb; padding: 10px; width: 40px; text-align: center;'>3</th>";
echo "<th style='background-color: #e5e7eb; padding: 10px; width: 40px; text-align: center;'>4</th>";
echo "<th style='background-color: #e5e7eb; padding: 10px; width: 40px; text-align: center;'>5</th>";
echo "<th style='background-color: #d1d5db; padding: 10px; width: 80px; text-align: center;'>VALORES</th>";
echo "</tr>";

// Questions
foreach ($questions as $key => $label) {
    $val = $scoring[$key] ?? 0;
    echo "<tr>";
    echo "<td style='padding: 8px; border: 1px solid #d1d5db;'>" . htmlspecialchars($label) . "</td>";
    
    // Checkmarks for 1 to 5
    for ($i = 1; $i <= 5; $i++) {
        $mark = ($val == $i) ? 'X' : '';
        echo "<td style='padding: 8px; border: 1px solid #d1d5db; text-align: center; font-weight: bold;'>" . $mark . "</td>";
    }
    
    // Final Value
    echo "<td style='padding: 8px; border: 1px solid #d1d5db; background-color: #f9fafb; font-weight: bold; text-align: center;'>" . $val . "</td>";
    echo "</tr>";
}

// Total
$totalScore = $scoring['totalScore'] ?? 0;
echo "<tr>";
echo "<td colspan='6' style='padding: 10px; text-align: right; font-weight: bold; background-color: #f3f4f6;'>TOTAL SCORING:</td>";
echo "<td style='padding: 10px; text-align: center; font-weight: bold; background-color: #e5e7eb; font-size: 14px;'>" . $totalScore . "</td>";
echo "</tr>";

echo "</table>";
echo "</body>";
echo "</html>";
