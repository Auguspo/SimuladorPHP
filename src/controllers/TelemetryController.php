<?php

namespace App\Controllers;

use App\Models\ParticipantModel;
use App\Models\SessionModel;
use App\Models\EventModel;
use App\Models\ClutchModel;
use PDO;
use DateTimeImmutable;
use Throwable;

class TelemetryController extends ApiController
{
    private function authorized(string $header): bool
    {
        if ($header === '') return false;
        if (preg_match('/^Bearer\s+(.+)$/i', $header, $matches) === 1) {
            return hash_equals(API_TOKEN, trim($matches[1]));
        }
        return hash_equals(API_TOKEN, trim($header));
    }

    private function required_array(array $data, string $key): array
    {
        if (!isset($data[$key]) || !is_array($data[$key])) {
            $this->jsonResponse(400, ['ok' => false, 'error' => "$key es requerido"]);
        }
        return $data[$key];
    }

    private function required_string(array $data, string $key, int $maxLength): string
    {
        $value = $data[$key] ?? null;
        if (!is_string($value)) $this->jsonResponse(400, ['ok' => false, 'error' => "$key debe ser texto"]);
        $value = trim($value);
        if ($value === '' || strlen($value) > $maxLength) $this->jsonResponse(400, ['ok' => false, 'error' => "$key es invalido"]);
        return $value;
    }

    private function optional_string(array $data, string $key, int $maxLength): ?string
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) return null;
        if (!is_string($data[$key])) $this->jsonResponse(400, ['ok' => false, 'error' => "$key debe ser texto o null"]);
        $value = trim($data[$key]);
        return $value === '' ? null : substr($value, 0, $maxLength);
    }

    private function optional_uint(array $data, string $key, int $max): ?int
    {
        if (!array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') return null;
        $value = filter_var($data[$key], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => $max]]);
        if ($value === false) $this->jsonResponse(400, ['ok' => false, 'error' => "$key debe ser un entero entre 0 y $max"]);
        return (int) $value;
    }

    private function required_uint(array $data, string $key, int $max): int
    {
        $value = $this->optional_uint($data, $key, $max);
        if ($value === null) $this->jsonResponse(400, ['ok' => false, 'error' => "$key es requerido"]);
        return $value;
    }

    private function optional_decimal(array $data, string $key, float $max): ?string
    {
        if (!array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') return null;
        if (!is_numeric($data[$key])) $this->jsonResponse(400, ['ok' => false, 'error' => "$key debe ser numerico"]);
        $value = (float) $data[$key];
        if ($value < 0 || $value > $max) $this->jsonResponse(400, ['ok' => false, 'error' => "$key esta fuera de rango"]);
        return number_format($value, 3, '.', '');
    }

    private function generate_external_session_id(int $length = 16): string
    {
        $bytes = random_bytes((int) ceil($length / 2));
        return strtoupper(substr(bin2hex($bytes), 0, $length));
    }

    private function parse_datetime(string $value): string
    {
        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if (!$dateTime instanceof DateTimeImmutable || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            $this->jsonResponse(400, ['ok' => false, 'error' => 'sesion.fecha debe usar formato YYYY-MM-DDTHH:MM:SS']);
        }
        return $dateTime->format('Y-m-d H:i:s');
    }

    public function ingest(): void
    {
        $this->requireMethod('POST');

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (!$this->authorized($authHeader)) {
            $this->jsonResponse(401, ['ok' => false, 'error' => 'Unauthorized']);
        }

        $rawBody = file_get_contents('php://input');
        if ($rawBody === false || trim($rawBody) === '') {
            $this->jsonResponse(400, ['ok' => false, 'error' => 'JSON requerido']);
        }

        $data = json_decode($rawBody, true);
        if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
            $this->jsonResponse(400, ['ok' => false, 'error' => 'JSON invalido']);
        }

        $session = $this->required_array($data, 'sesion');
        $participant = $this->required_array($session, 'conductor');
        $eventsWrapper = $this->required_array($session, 'eventos');
        $events = $eventsWrapper['evento'] ?? null;
        if (!is_array($events)) {
            $this->jsonResponse(400, ['ok' => false, 'error' => 'eventos.evento debe ser un array']);
        }

        $externalSessionId = $this->generate_external_session_id(20);
        $testedAt = $this->parse_datetime($this->required_string($session, 'fecha', 19));
        $participantName = $this->required_string($participant, 'nombre', 120);
        $participantDni = $this->required_string($participant, 'dni', 30);
        $participantAge = $this->optional_uint($participant, 'edad', 120);
        $participantWeight = $this->optional_decimal($participant, 'peso', 300);
        $participantComment = $this->optional_string($participant, 'comentario', 65535);
        $tractionModeSession = $this->optional_string($participant, 'modo_traccion', 20) ?? 'Indefinido';
        if (!in_array($tractionModeSession, ['2H', '4H', '4L', 'Indefinido'], true)) {
            $tractionModeSession = 'Indefinido';
        }

        $clutch = $this->required_array($session, 'embrague');
        $clutchCount = $this->required_uint($clutch, 'conteo', 1000000);
        $clutchTotalTime = $this->optional_decimal($clutch, 'tiempo_total_s', 86400);
        if ($clutchTotalTime === null) {
            $this->jsonResponse(400, ['ok' => false, 'error' => 'embrague.tiempo_total_s es requerido']);
        }

        $normalizedEvents = [];
        foreach ($events as $event) {
            if (!is_array($event)) $this->jsonResponse(400, ['ok' => false, 'error' => 'Cada evento debe ser un objeto']);
            $result = $this->required_string($event, 'resultado', 20);
            if (!in_array($result, ['ACIERTO', 'ERROR'], true)) $this->jsonResponse(400, ['ok' => false, 'error' => 'resultado debe ser ACIERTO o ERROR']);
            $stimulus = $this->required_string($event, 'estimulo', 80);
            $validStimuli = ['Freno (LED)', 'Acelerador (LED)', 'Freno (Bocina)', 'Acelerador (Bocina)', 'Boton 1', 'Boton 2', 'Boton 3', 'Boton 4', '-'];
            if (!in_array($stimulus, $validStimuli, true)) $this->jsonResponse(400, ['ok' => false, 'error' => "estimulo invalido: $stimulus"]);
            
            $eventTraction = $this->optional_string($event, 'modo_traccion', 20);
            if (!in_array($eventTraction, ['2H', '4H', '4L', 'Indefinido'], true)) $eventTraction = $tractionModeSession;

            $normalizedEvents[] = [
                'number' => $this->required_uint($event, 'numero', 1000000),
                'stimulus' => $stimulus,
                'result' => $result,
                'time_ms' => $this->required_uint($event, 'tiempo_ms', 600000),
                'traction_mode' => $eventTraction
            ];
        }

        $pdo = db(); // Use raw PDO for transaction control across multiple models
        $pdo->beginTransaction();

        try {
            $participantModel = new ParticipantModel();
            $participantId = $participantModel->getOrCreate($participantName, $participantDni);

            $sessionModel = new SessionModel();
            $sessionId = $sessionModel->create($externalSessionId, $participantId, $testedAt, $participantAge, $participantWeight !== null ? (float)$participantWeight : null, $participantComment);

            $eventModel = new EventModel();
            foreach ($normalizedEvents as $ev) {
                $eventModel->create($sessionId, $ev['number'], $ev['stimulus'], $ev['result'], $ev['time_ms'], $ev['traction_mode']);
            }

            $clutchModel = new ClutchModel();
            $clutchModel->create($sessionId, $clutchCount, (float)$clutchTotalTime);

            $pdo->commit();

            $this->jsonResponse(200, [
                'ok' => true,
                'session_id' => $sessionId,
                'external_session_id' => $externalSessionId,
                'participant_id' => $participantId,
                'events_count' => count($normalizedEvents),
            ]);
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log($e->getMessage());
            $this->jsonResponse(500, ['ok' => false, 'error' => 'Internal Server Error']);
        }
    }
}
