<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/private/db.php';

header('Content-Type: application/json; charset=utf-8');

function json_response(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function authorization_header(): string
{
    return $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? '';
}

function authorized(string $header): bool
{
    if ($header === '') {
        return false;
    }

    if (preg_match('/^Bearer\s+(.+)$/i', $header, $matches) === 1) {
        return hash_equals(API_TOKEN, trim($matches[1]));
    }

    return hash_equals(API_TOKEN, trim($header));
}

function required_array(array $data, string $key): array
{
    if (!isset($data[$key]) || !is_array($data[$key])) {
        json_response(400, ['ok' => false, 'error' => "$key es requerido"]);
    }

    return $data[$key];
}

function required_string(array $data, string $key, int $maxLength): string
{
    $value = $data[$key] ?? null;
    if (!is_string($value)) {
        json_response(400, ['ok' => false, 'error' => "$key debe ser texto"]);
    }

    $value = trim($value);
    if ($value === '' || strlen($value) > $maxLength) {
        json_response(400, ['ok' => false, 'error' => "$key es invalido"]);
    }

    return $value;
}

function optional_string(array $data, string $key, int $maxLength): ?string
{
    if (!array_key_exists($key, $data) || $data[$key] === null) {
        return null;
    }

    if (!is_string($data[$key])) {
        json_response(400, ['ok' => false, 'error' => "$key debe ser texto o null"]);
    }

    $value = trim($data[$key]);
    return $value === '' ? null : substr($value, 0, $maxLength);
}

function optional_uint(array $data, string $key, int $max): ?int
{
    if (!array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
        return null;
    }

    $value = filter_var($data[$key], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0, 'max_range' => $max],
    ]);

    if ($value === false) {
        json_response(400, ['ok' => false, 'error' => "$key debe ser un entero entre 0 y $max"]);
    }

    return (int) $value;
}

function required_uint(array $data, string $key, int $max): int
{
    $value = optional_uint($data, $key, $max);
    if ($value === null) {
        json_response(400, ['ok' => false, 'error' => "$key es requerido"]);
    }

    return $value;
}

function optional_decimal(array $data, string $key, float $max): ?string
{
    if (!array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
        return null;
    }

    if (!is_numeric($data[$key])) {
        json_response(400, ['ok' => false, 'error' => "$key debe ser numerico"]);
    }

    $value = (float) $data[$key];
    if ($value < 0 || $value > $max) {
        json_response(400, ['ok' => false, 'error' => "$key esta fuera de rango"]);
    }

    return number_format($value, 3, '.', '');
}

function generate_external_session_id(int $length = 16): string
{
    $bytes = random_bytes((int) ceil($length / 2));
    return strtoupper(substr(bin2hex($bytes), 0, $length));
}

function parse_datetime(string $value): string
{
    $dateTime = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', $value);
    $errors = DateTimeImmutable::getLastErrors();
    if (!$dateTime instanceof DateTimeImmutable || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
        json_response(400, ['ok' => false, 'error' => 'sesion.fecha debe usar formato YYYY-MM-DDTHH:MM:SS']);
    }

    return $dateTime->format('Y-m-d H:i:s');
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(405, ['ok' => false, 'error' => 'Metodo no permitido']);
    }

    if (!authorized(authorization_header())) {
        json_response(401, ['ok' => false, 'error' => 'Unauthorized']);
    }

    $rawBody = file_get_contents('php://input');
    if ($rawBody === false || trim($rawBody) === '') {
        json_response(400, ['ok' => false, 'error' => 'JSON requerido']);
    }

    $data = json_decode($rawBody, true);
    if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
        json_response(400, ['ok' => false, 'error' => 'JSON invalido']);
    }

    $session = required_array($data, 'sesion');
    $participant = required_array($session, 'conductor');
    $eventsWrapper = required_array($session, 'eventos');
    $events = $eventsWrapper['evento'] ?? null;
    if (!is_array($events)) {
        json_response(400, ['ok' => false, 'error' => 'eventos.evento debe ser un array']);
    }

    $externalSessionId = generate_external_session_id(20);
    $testedAt = parse_datetime(required_string($session, 'fecha', 19));
    $participantName = required_string($participant, 'nombre', 120);
    $participantDni = required_string($participant, 'dni', 30);
    $participantAge = optional_uint($participant, 'edad', 120);
    $participantWeight = optional_decimal($participant, 'peso', 300);
    $participantComment = optional_string($participant, 'comentario', 65535);

    $clutch = required_array($session, 'embrague');
    $clutchCount = required_uint($clutch, 'conteo', 1000000);
    $clutchTotalTime = optional_decimal($clutch, 'tiempo_total_s', 86400);
    if ($clutchTotalTime === null) {
        json_response(400, ['ok' => false, 'error' => 'embrague.tiempo_total_s es requerido']);
    }

    $normalizedEvents = [];
    foreach ($events as $event) {
        if (!is_array($event)) {
            json_response(400, ['ok' => false, 'error' => 'Cada evento debe ser un objeto']);
        }

        $result = required_string($event, 'resultado', 20);
        if (!in_array($result, ['ACIERTO', 'ERROR'], true)) {
            json_response(400, ['ok' => false, 'error' => 'resultado debe ser ACIERTO o ERROR']);
        }

        $normalizedEvents[] = [
            'number' => required_uint($event, 'numero', 1000000),
            'stimulus' => required_string($event, 'estimulo', 80),
            'result' => $result,
            'time_ms' => required_uint($event, 'tiempo_ms', 600000),
        ];
    }

    $pdo = db();
    $pdo->beginTransaction();

    $statement = $pdo->prepare(
        'INSERT INTO participants (name, dni)
         VALUES (:name, :dni)
         ON DUPLICATE KEY UPDATE name = VALUES(name), id = LAST_INSERT_ID(id)'
    );
    $statement->execute([
        ':name' => $participantName,
        ':dni' => $participantDni,
    ]);
    $participantId = (int) $pdo->lastInsertId();

    $statement = $pdo->prepare(
        'INSERT INTO sessions (
             external_id, participant_id, tested_at, participant_age, participant_weight_kg, participant_comment
         ) VALUES (
             :external_id, :participant_id, :tested_at, :participant_age, :participant_weight_kg, :participant_comment
         )'
    );
    $statement->execute([
        ':external_id' => $externalSessionId,
        ':participant_id' => $participantId,
        ':tested_at' => $testedAt,
        ':participant_age' => $participantAge,
        ':participant_weight_kg' => $participantWeight,
        ':participant_comment' => $participantComment,
    ]);
    $sessionId = (int) $pdo->lastInsertId();

    $eventStatement = $pdo->prepare(
        'INSERT INTO session_events (session_id, event_number, stimulus, result, time_ms)
         VALUES (:session_id, :event_number, :stimulus, :result, :time_ms)'
    );

    foreach ($normalizedEvents as $event) {
        $eventStatement->execute([
            ':session_id' => $sessionId,
            ':event_number' => $event['number'],
            ':stimulus' => $event['stimulus'],
            ':result' => $event['result'],
            ':time_ms' => $event['time_ms'],
        ]);
    }

    $statement = $pdo->prepare(
        'INSERT INTO clutch_metrics (session_id, count, total_time_s)
         VALUES (:session_id, :count, :total_time_s)'
    );
    $statement->execute([
        ':session_id' => $sessionId,
        ':count' => $clutchCount,
        ':total_time_s' => $clutchTotalTime,
    ]);

    $pdo->commit();

    json_response(200, [
        'ok' => true,
        'session_id' => $sessionId,
        'external_session_id' => $externalSessionId,
        'participant_id' => $participantId,
        'events_count' => count($normalizedEvents),
    ]);
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($exception->getMessage());
    json_response(500, ['ok' => false, 'error' => 'Internal Server Error']);
}
