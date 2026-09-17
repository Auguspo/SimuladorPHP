<?php

namespace App\Models;

use PDO;

class StatsModel extends BaseModel
{
    public function getSettings(): array
    {
        return [
            'fast_threshold_ms' => 300,
            'slow_threshold_ms' => 450,
            'max_timeout_ms' => 8000
        ];
    }

    private function buildWhereClause(?int $participantId, ?string $fromDate, ?string $toDate, string $deletedFilter, array &$params): string
    {
        $whereConditions = [];

        if ($participantId !== null) {
            $whereConditions[] = 's.participant_id = :participant_id';
            $params[':participant_id'] = $participantId;
        }
        if ($fromDate !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
            $whereConditions[] = 's.tested_at >= :from_date';
            $params[':from_date'] = $fromDate . ' 00:00:00';
        }
        if ($toDate !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
            $whereConditions[] = 's.tested_at <= :to_date';
            $params[':to_date'] = $toDate . ' 23:59:59';
        }
        if ($deletedFilter === 'N') {
            $whereConditions[] = 'se.is_deleted = 0';
        } elseif ($deletedFilter === 'Y') {
            $whereConditions[] = 'se.is_deleted = 1';
        }

        return !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    }

    public function getStats(?int $participantId, ?string $fromDate, ?string $toDate, string $deletedFilter): array
    {
        $params = [];
        $whereClause = $this->buildWhereClause($participantId, $fromDate, $toDate, $deletedFilter, $params);

        $settings = $this->getSettings();
        $fastThreshold = $settings['fast_threshold_ms'];
        $slowThreshold = $settings['slow_threshold_ms'];

        // KPIs
        $stmtSessions = $this->pdo->prepare("SELECT COUNT(DISTINCT s.id) FROM sessions s JOIN session_events se ON se.session_id = s.id {$whereClause}");
        $stmtSessions->execute($params);
        $totalSessions = (int)$stmtSessions->fetchColumn();

        $stmtParticipants = $this->pdo->prepare("SELECT COUNT(DISTINCT s.participant_id) FROM sessions s JOIN session_events se ON se.session_id = s.id {$whereClause}");
        $stmtParticipants->execute($params);
        $totalParticipants = (int)$stmtParticipants->fetchColumn();

        $sqlEvents = "SELECT 
                COUNT(*) AS total_events,
                SUM(CASE WHEN se.result = 'ACIERTO' THEN 1 ELSE 0 END) AS total_aciertos,
                AVG(se.time_ms) AS avg_reaction_ms,
                MIN(se.time_ms) AS min_reaction_ms
             FROM session_events se
             JOIN sessions s ON s.id = se.session_id
             {$whereClause}";
        $stmtEvents = $this->pdo->prepare($sqlEvents);
        $stmtEvents->execute($params);
        $eventsStats = $stmtEvents->fetch(PDO::FETCH_ASSOC) ?: [];

        $sqlClutch = "SELECT 
                AVG(cm.count) AS avg_clutch_count,
                AVG(cm.total_time_s) AS avg_clutch_time_s
             FROM clutch_metrics cm
             JOIN sessions s ON s.id = cm.session_id
             JOIN session_events se ON se.session_id = s.id
             {$whereClause}";
        $stmtClutch = $this->pdo->prepare($sqlClutch);
        $stmtClutch->execute($params);
        $clutchStats = $stmtClutch->fetch(PDO::FETCH_ASSOC) ?: [];

        // Stimulus Breakdown
        $sqlStimulus = "SELECT 
                se.stimulus,
                COUNT(*) AS count,
                SUM(CASE WHEN se.result = 'ACIERTO' THEN 1 ELSE 0 END) AS aciertos,
                AVG(se.time_ms) AS avg_ms,
                MIN(se.time_ms) AS min_ms
             FROM session_events se
             JOIN sessions s ON s.id = se.session_id
             {$whereClause}
             GROUP BY se.stimulus
             ORDER BY count DESC";
        $stmtStimulus = $this->pdo->prepare($sqlStimulus);
        $stmtStimulus->execute($params);
        $stimulusBreakdown = $stmtStimulus->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Leaderboard
        $sqlDrivers = "SELECT 
                p.id, p.name, p.dni,
                COUNT(DISTINCT s.id) AS sessions_count,
                AVG(se.time_ms) AS avg_reaction_ms,
                MIN(se.time_ms) AS best_reaction_ms,
                SUM(CASE WHEN se.result = 'ACIERTO' THEN 1 ELSE 0 END) * 100.0 / COUNT(se.id) AS accuracy_pct
             FROM participants p
             JOIN sessions s ON s.participant_id = p.id
             JOIN session_events se ON se.session_id = s.id
             {$whereClause}
             GROUP BY p.id, p.name, p.dni
             ORDER BY avg_reaction_ms ASC";
        $stmtDrivers = $this->pdo->prepare($sqlDrivers);
        $stmtDrivers->execute($params);
        $driverLeaderboard = $stmtDrivers->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Ranges
        $sqlRanges = "SELECT
                SUM(CASE WHEN se.time_ms < {$fastThreshold} THEN 1 ELSE 0 END) AS fast_count,
                SUM(CASE WHEN se.time_ms >= {$fastThreshold} AND se.time_ms <= {$slowThreshold} THEN 1 ELSE 0 END) AS normal_count,
                SUM(CASE WHEN se.time_ms > {$slowThreshold} THEN 1 ELSE 0 END) AS slow_count
             FROM session_events se
             JOIN sessions s ON s.id = se.session_id
             {$whereClause}";
        $stmtRanges = $this->pdo->prepare($sqlRanges);
        $stmtRanges->execute($params);
        $speedRanges = $stmtRanges->fetch(PDO::FETCH_ASSOC) ?: [];

        $totalEvts = (int)($eventsStats['total_events'] ?? 0);
        $totalAciertos = (int)($eventsStats['total_aciertos'] ?? 0);

        return [
            'thresholds' => $settings,
            'kpis' => [
                'total_sessions' => $totalSessions,
                'total_participants' => $totalParticipants,
                'total_events' => $totalEvts,
                'accuracy_rate' => $totalEvts > 0 ? round(($totalAciertos / $totalEvts) * 100, 1) : 0,
                'avg_reaction_ms' => round((float)($eventsStats['avg_reaction_ms'] ?? 0), 1),
                'min_reaction_ms' => (int)($eventsStats['min_reaction_ms'] ?? 0),
                'avg_clutch_count' => round((float)($clutchStats['avg_clutch_count'] ?? 0), 1),
                'avg_clutch_time_s' => round((float)($clutchStats['avg_clutch_time_s'] ?? 0), 2)
            ],
            'stimulus_breakdown' => $stimulusBreakdown,
            'driver_leaderboard' => $driverLeaderboard,
            'speed_ranges' => [
                'fast' => (int)($speedRanges['fast_count'] ?? 0),
                'normal' => (int)($speedRanges['normal_count'] ?? 0),
                'slow' => (int)($speedRanges['slow_count'] ?? 0)
            ]
        ];
    }
}
