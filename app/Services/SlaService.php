<?php

namespace App\Services;

use Core\Database;
use App\Models\Notification;

class SlaService
{
    /**
     * Assign SLA policy to a ticket based on its priority
     */
    public static function assignSla(int $ticketId): void
    {
        $ticket = Database::fetch("SELECT id, priority, created_at FROM tickets WHERE id = ?", [$ticketId]);
        if (!$ticket) return;

        $policy = Database::fetch(
            "SELECT * FROM sla_policies WHERE priority = ? AND is_active = 1 ORDER BY id ASC LIMIT 1",
            [$ticket['priority']]
        );

        if (!$policy) return;

        $createdAt = strtotime($ticket['created_at']);
        $firstResponseDue = date('Y-m-d H:i:s', $createdAt + ($policy['first_response_hours'] * 3600));
        $resolutionDue = date('Y-m-d H:i:s', $createdAt + ($policy['resolution_hours'] * 3600));

        Database::update('tickets', [
            'sla_policy_id' => $policy['id'],
            'sla_first_response_due' => $firstResponseDue,
            'sla_resolution_due' => $resolutionDue,
        ], 'id = ?', [$ticketId]);
    }

    /**
     * Record first response time when staff adds first comment
     */
    public static function recordFirstResponse(int $ticketId): void
    {
        $ticket = Database::fetch(
            "SELECT id, first_response_at, sla_first_response_due FROM tickets WHERE id = ?",
            [$ticketId]
        );

        if (!$ticket || !empty($ticket['first_response_at'])) return;

        $now = date('Y-m-d H:i:s');
        Database::update('tickets', [
            'first_response_at' => $now,
        ], 'id = ?', [$ticketId]);

        // Check if first response breached SLA
        if (!empty($ticket['sla_first_response_due']) && $now > $ticket['sla_first_response_due']) {
            Database::update('tickets', [
                'sla_breached' => 1,
            ], 'id = ?', [$ticketId]);
        }
    }

    /**
     * Check all open tickets for SLA breaches and auto-escalate
     */
    public static function checkBreaches(): void
    {
        $now = date('Y-m-d H:i:s');

        // First response breach: past due and no first response yet
        $firstResponseBreached = Database::fetchAll(
            "SELECT t.id, t.ticket_code, t.title, t.assigned_to, sp.escalate_to
             FROM tickets t
             JOIN sla_policies sp ON t.sla_policy_id = sp.id
             WHERE t.sla_breached = 0
               AND t.first_response_at IS NULL
               AND t.sla_first_response_due IS NOT NULL
               AND t.sla_first_response_due < ?
               AND t.status NOT IN ('resolved', 'closed')",
            [$now]
        );

        foreach ($firstResponseBreached as $ticket) {
            self::breachTicket($ticket);
        }

        // Resolution breach: past due and not resolved/closed
        $resolutionBreached = Database::fetchAll(
            "SELECT t.id, t.ticket_code, t.title, t.assigned_to, sp.escalate_to
             FROM tickets t
             JOIN sla_policies sp ON t.sla_policy_id = sp.id
             WHERE t.sla_breached = 0
               AND t.sla_resolution_due IS NOT NULL
               AND t.sla_resolution_due < ?
               AND t.status NOT IN ('resolved', 'closed')",
            [$now]
        );

        foreach ($resolutionBreached as $ticket) {
            self::breachTicket($ticket);
        }
    }

    /**
     * Mark ticket as breached, escalate and notify
     */
    private static function breachTicket(array $ticket): void
    {
        $updateData = ['sla_breached' => 1];

        if (!empty($ticket['escalate_to'])) {
            $updateData['assigned_to'] = $ticket['escalate_to'];
        }

        Database::update('tickets', $updateData, 'id = ?', [$ticket['id']]);

        // Notify the escalation target
        $notifyUserId = $ticket['escalate_to'] ?? $ticket['assigned_to'];
        if ($notifyUserId) {
            Notification::send(
                $notifyUserId,
                'sla_breach',
                "SLA vi phạm: {$ticket['ticket_code']}",
                "Ticket \"{$ticket['title']}\" đã vi phạm SLA và được chuyển tiếp.",
                "tickets/{$ticket['id']}",
                'ri-alarm-warning-line'
            );
        }
    }

    /**
     * Get SLA status for a ticket
     */
    public static function getSlaStatus(array $ticket): ?array
    {
        if (empty($ticket['sla_policy_id'])) return null;

        $now = time();
        $createdAt = strtotime($ticket['created_at']);

        $result = [
            'is_breached' => (bool) ($ticket['sla_breached'] ?? false),
            'first_response_remaining' => null,
            'resolution_remaining' => null,
            'first_response_status' => 'ok',
            'resolution_status' => 'ok',
        ];

        // First response
        if (!empty($ticket['sla_first_response_due'])) {
            $due = strtotime($ticket['sla_first_response_due']);
            $totalTime = $due - $createdAt;

            if (!empty($ticket['first_response_at'])) {
                // Already responded
                $respondedAt = strtotime($ticket['first_response_at']);
                if ($respondedAt > $due) {
                    $result['first_response_status'] = 'breached';
                    $result['first_response_remaining'] = 0;
                } else {
                    $result['first_response_status'] = 'ok';
                    $result['first_response_remaining'] = 0;
                }
            } else {
                $remaining = $due - $now;
                $result['first_response_remaining'] = round($remaining / 3600, 2);

                if ($remaining <= 0) {
                    $result['first_response_status'] = 'breached';
                    $result['first_response_remaining'] = 0;
                } elseif ($totalTime > 0 && $remaining < ($totalTime * 0.25)) {
                    $result['first_response_status'] = 'warning';
                }
            }
        }

        // Resolution
        if (!empty($ticket['sla_resolution_due'])) {
            $due = strtotime($ticket['sla_resolution_due']);
            $totalTime = $due - $createdAt;
            $status = $ticket['status'] ?? '';

            if (in_array($status, ['resolved', 'closed'])) {
                $resolvedAt = strtotime($ticket['resolved_at'] ?? $ticket['closed_at'] ?? date('Y-m-d H:i:s'));
                if ($resolvedAt > $due) {
                    $result['resolution_status'] = 'breached';
                    $result['resolution_remaining'] = 0;
                } else {
                    $result['resolution_status'] = 'ok';
                    $result['resolution_remaining'] = 0;
                }
            } else {
                $remaining = $due - $now;
                $result['resolution_remaining'] = round($remaining / 3600, 2);

                if ($remaining <= 0) {
                    $result['resolution_status'] = 'breached';
                    $result['resolution_remaining'] = 0;
                } elseif ($totalTime > 0 && $remaining < ($totalTime * 0.25)) {
                    $result['resolution_status'] = 'warning';
                }
            }
        }

        return $result;
    }
}
