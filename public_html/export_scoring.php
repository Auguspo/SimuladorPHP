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
    header("Location: /sesion/$sessionId?error=true&msj=" . urlencode("Faltan completar datos para exportar"));
    exit;
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

echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head><meta charset="UTF-8"></head>';
echo '<body style="font-family: Calibri, sans-serif;">';

echo '<table style="border-collapse: collapse; width: 100%;">';

// HEADER ROWS
echo '<tr>';
echo '<td colspan="5" style="font-size: 24px; font-weight: bold; color: #1e3a5f; border-bottom: none;">SCORING</td>';
echo '<td colspan="3" rowspan="2" style="text-align: right; vertical-align: top;"><div style="background-color: #1e3a5f; color: white; border-radius: 50%; width: 80px; height: 80px; display: inline-block; text-align: center; line-height: 80px; font-size: 28px; font-weight: bold;">GSE</div></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="5" style="font-size: 16px; color: #1e3a5f; border-bottom: 2px solid #1e3a5f;">PR-CA-04-F10 V.00   06/03/2026</td>';
echo '</tr>';

// Empty Rows
echo '<tr><td colspan="8"></td></tr>';
echo '<tr><td colspan="8"></td></tr>';
echo '<tr><td colspan="8"></td></tr>';

// USER INFO ROW
echo '<tr>';
echo '<td colspan="2" style="background-color: #4b5563; color: white; font-weight: bold; padding: 5px; text-align: right;">Nombre y Apellido :</td>';
echo '<td colspan="2" style="border: 1px solid black; padding: 5px;">' . htmlspecialchars($session['participant_name']) . '</td>';
echo '<td style="background-color: #4b5563; color: white; font-weight: bold; padding: 5px; text-align: right;">DNI:</td>';
echo '<td style="border: 1px solid black; padding: 5px;">' . htmlspecialchars($session['participant_dni'] ?? '-') . '</td>';
echo '<td style="background-color: #4b5563; color: white; font-weight: bold; padding: 5px; text-align: right;">Fecha:</td>';
echo '<td style="border: 1px solid black; padding: 5px;">' . date('d/m/Y', strtotime($session['tested_at'])) . '</td>';
echo '</tr>';

echo '<tr><td colspan="8"></td></tr>';

// TABLE HEADERS
echo '<tr>';
echo '<th colspan="2" style="background-color: #374151; color: white; font-weight: bold; padding: 5px; border: 1px solid black; text-align: center;">Preguntas</th>';
echo '<th style="background-color: #dc2626; color: white; font-weight: bold; padding: 5px; border: 1px solid black; text-align: center;">Muy malo</th>';
echo '<th style="background-color: #ef4444; color: white; font-weight: bold; padding: 5px; border: 1px solid black; text-align: center;">Malo</th>';
echo '<th style="background-color: #f97316; color: white; font-weight: bold; padding: 5px; border: 1px solid black; text-align: center;">Regular</th>';
echo '<th style="background-color: #84cc16; color: white; font-weight: bold; padding: 5px; border: 1px solid black; text-align: center;">Bueno</th>';
echo '<th style="background-color: #65a30d; color: white; font-weight: bold; padding: 5px; border: 1px solid black; text-align: center;">Muy bueno</th>';
echo '<th style="background-color: #374151; color: white; font-weight: bold; padding: 5px; border: 1px solid black; text-align: center;">VALORES</th>';
echo '</tr>';

// TABLE ROWS
foreach ($questions as $key => $label) {
    $val = $scoring[$key] ?? 0;
    echo '<tr>';
    echo '<td colspan="2" style="padding: 10px; font-weight: bold; text-align: center; border-bottom: none;">' . htmlspecialchars($label) . '</td>';
    
    // Checkmarks colored borders based on image (Muy Malo / Malo -> red, Regular -> orange, Bueno / Muy bueno -> green)
    echo '<td style="border-left: 1px solid red; border-right: 1px solid red; text-align: center; vertical-align: middle;">' . (($val == 1) ? 'X' : '') . '</td>';
    echo '<td style="border-left: 1px solid red; border-right: 1px solid red; text-align: center; vertical-align: middle;">' . (($val == 2) ? 'X' : '') . '</td>';
    echo '<td style="border-left: 1px solid orange; border-right: 1px solid orange; text-align: center; vertical-align: middle;">' . (($val == 3) ? 'X' : '') . '</td>';
    echo '<td style="border-left: 1px solid green; border-right: 1px solid green; text-align: center; vertical-align: middle;">' . (($val == 4) ? 'X' : '') . '</td>';
    echo '<td style="border-left: 1px solid green; border-right: 1px solid black; text-align: center; vertical-align: middle;">' . (($val == 5) ? 'X' : '') . '</td>';
    
    // Value Column
    echo '<td style="border-left: 1px solid black; border-right: 1px solid black; text-align: center; vertical-align: middle;">' . $val . '</td>';
    echo '</tr>';
}

// Bottom border for the table
echo '<tr>';
echo '<td colspan="2" style="border-top: 1px solid black;"></td>';
echo '<td style="border-top: 1px solid red;"></td>';
echo '<td style="border-top: 1px solid red;"></td>';
echo '<td style="border-top: 1px solid orange;"></td>';
echo '<td style="border-top: 1px solid green;"></td>';
echo '<td style="border-top: 1px solid green;"></td>';
echo '<td style="border-top: 1px solid black;"></td>';
echo '</tr>';

// Total row
$totalScore = $scoring['totalScore'] ?? 0;
echo '<tr><td colspan="8"></td></tr>';
echo '<tr><td colspan="8"></td></tr>';
echo '<tr>';
echo '<td colspan="8"></td>';
echo '<td style="background-color: #374151; color: white; font-weight: bold; text-align: center; padding: 5px; border: 1px solid black;">' . $totalScore . '</td>';
echo '</tr>';

echo '</table>';
echo '</body>';
echo '</html>';
