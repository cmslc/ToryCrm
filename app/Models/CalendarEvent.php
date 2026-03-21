<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class CalendarEvent extends Model
{
    protected string $table = 'calendar_events';

    public function getByDateRange(string $start, string $end, ?int $userId = null): array
    {
        $where = "start_at >= ? AND start_at <= ?";
        $params = [$start, $end];

        if ($userId) {
            $where .= " AND (user_id = ? OR created_by = ?)";
            $params[] = $userId;
            $params[] = $userId;
        }

        return Database::fetchAll(
            "SELECT ce.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name,
                    u.name as user_name
             FROM calendar_events ce
             LEFT JOIN contacts c ON ce.contact_id = c.id
             LEFT JOIN companies comp ON ce.company_id = comp.id
             LEFT JOIN users u ON ce.user_id = u.id
             WHERE {$where}
             ORDER BY ce.start_at ASC",
            $params
        );
    }

    public function getUpcoming(int $userId, int $limit = 10): array
    {
        return Database::fetchAll(
            "SELECT ce.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name
             FROM calendar_events ce
             LEFT JOIN contacts c ON ce.contact_id = c.id
             LEFT JOIN companies comp ON ce.company_id = comp.id
             WHERE (ce.user_id = ? OR ce.created_by = ?) AND ce.start_at >= NOW() AND ce.is_completed = 0
             ORDER BY ce.start_at ASC
             LIMIT {$limit}",
            [$userId, $userId]
        );
    }

    public function getToday(int $userId): array
    {
        return Database::fetchAll(
            "SELECT ce.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name
             FROM calendar_events ce
             LEFT JOIN contacts c ON ce.contact_id = c.id
             WHERE (ce.user_id = ? OR ce.created_by = ?) AND DATE(ce.start_at) = CURDATE()
             ORDER BY ce.start_at ASC",
            [$userId, $userId]
        );
    }

    public function getOverdue(int $userId): array
    {
        return Database::fetchAll(
            "SELECT * FROM calendar_events
             WHERE (user_id = ? OR created_by = ?) AND end_at < NOW() AND is_completed = 0
             ORDER BY end_at DESC",
            [$userId, $userId]
        );
    }

    public function markComplete(int $id): void
    {
        $this->update($id, [
            'is_completed' => 1,
            'completed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getForCalendarJson(string $start, string $end, ?int $userId = null): array
    {
        $events = $this->getByDateRange($start, $end, $userId);
        $result = [];

        foreach ($events as $event) {
            $result[] = [
                'id' => $event['id'],
                'title' => $event['title'],
                'start' => $event['start_at'],
                'end' => $event['end_at'] ?: $event['start_at'],
                'color' => $event['color'] ?? '#405189',
                'allDay' => (bool)$event['all_day'],
                'extendedProps' => [
                    'type' => $event['type'],
                    'location' => $event['location'],
                    'description' => $event['description'],
                ],
            ];
        }

        return $result;
    }
}
