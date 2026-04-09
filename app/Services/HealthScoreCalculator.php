<?php

namespace App\Services;

use Core\Database;

class HealthScoreCalculator
{
    private int $tenantId;

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Calculate health scores for all contacts in the tenant
     */
    public function calculateAll(): int
    {
        $contacts = Database::fetchAll(
            "SELECT id FROM contacts WHERE tenant_id = ? AND is_deleted = 0",
            [$this->tenantId]
        );

        $count = 0;
        foreach ($contacts as $contact) {
            $this->calculate((int)$contact['id']);
            $count++;
        }

        return $count;
    }

    /**
     * Calculate health score for a single contact (0-100)
     */
    public function calculate(int $contactId): array
    {
        $engagementScore = $this->getEngagementScore($contactId);
        $paymentScore = $this->getPaymentScore($contactId);
        $supportScore = $this->getSupportScore($contactId);
        $activityScore = $this->getActivityScore($contactId);

        $overall = (int)round(($engagementScore + $paymentScore + $supportScore + $activityScore) / 4);

        if ($overall < 30) {
            $churnRisk = 'critical';
        } elseif ($overall < 50) {
            $churnRisk = 'high';
        } elseif ($overall < 70) {
            $churnRisk = 'medium';
        } else {
            $churnRisk = 'low';
        }

        // Get last interaction date
        $lastInteraction = Database::fetch(
            "SELECT MAX(created_at) as last_at FROM activities WHERE contact_id = ?",
            [$contactId]
        );
        $lastAt = $lastInteraction['last_at'] ?? null;
        $daysSince = $lastAt ? (int)((time() - strtotime($lastAt)) / 86400) : 999;

        $factors = json_encode([
            'engagement' => $engagementScore,
            'payment' => $paymentScore,
            'support' => $supportScore,
            'activity' => $activityScore,
        ]);

        // REPLACE INTO (upsert based on unique key uk_health_contact)
        Database::query(
            "REPLACE INTO health_scores
             (contact_id, overall_score, engagement_score, payment_score, support_score, activity_score,
              churn_risk, last_interaction_at, days_since_interaction, calculated_at, factors)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)",
            [
                $contactId, $overall, $engagementScore, $paymentScore, $supportScore, $activityScore,
                $churnRisk, $lastAt, $daysSince, $factors
            ]
        );

        return [
            'overall_score' => $overall,
            'engagement_score' => $engagementScore,
            'payment_score' => $paymentScore,
            'support_score' => $supportScore,
            'activity_score' => $activityScore,
            'churn_risk' => $churnRisk,
        ];
    }

    /**
     * Engagement: based on activities count in last 30 days
     */
    private function getEngagementScore(int $contactId): int
    {
        $result = Database::fetch(
            "SELECT COUNT(*) as cnt
             FROM activities
             WHERE contact_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            [$contactId]
        );

        $count = (int)($result['cnt'] ?? 0);

        if ($count === 0) return 0;
        if ($count <= 2) return 30;
        if ($count <= 5) return 60;
        return 90;
    }

    /**
     * Payment: paid orders / total orders ratio * 100
     */
    private function getPaymentScore(int $contactId): int
    {
        $result = Database::fetch(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as paid
             FROM orders
             WHERE contact_id = ? AND is_deleted = 0",
            [$contactId]
        );

        $total = (int)($result['total'] ?? 0);
        $paid = (int)($result['paid'] ?? 0);

        if ($total === 0) return 50; // No orders = neutral score
        return (int)round(($paid / $total) * 100);
    }

    /**
     * Support: 100 - (open tickets * 20), min 0
     */
    private function getSupportScore(int $contactId): int
    {
        $result = Database::fetch(
            "SELECT COUNT(*) as cnt
             FROM tickets
             WHERE contact_id = ? AND status NOT IN ('resolved', 'closed')",
            [$contactId]
        );

        $openTickets = (int)($result['cnt'] ?? 0);
        return max(0, 100 - ($openTickets * 20));
    }

    /**
     * Activity: based on days since last interaction
     */
    private function getActivityScore(int $contactId): int
    {
        $result = Database::fetch(
            "SELECT MAX(created_at) as last_at FROM activities WHERE contact_id = ?",
            [$contactId]
        );

        $lastAt = $result['last_at'] ?? null;

        if (!$lastAt) return 0;

        $days = (int)((time() - strtotime($lastAt)) / 86400);

        if ($days <= 7) return 100;
        if ($days <= 14) return 70;
        if ($days <= 30) return 40;
        if ($days <= 60) return 20;
        return 0;
    }
}
