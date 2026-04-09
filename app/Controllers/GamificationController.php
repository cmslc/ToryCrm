<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class GamificationController extends Controller
{
    public function leaderboard()
    {
        $tid = $this->tenantId();
        $period = $this->input('period', date('Y-m'));

        // Get or generate leaderboard data
        $this->generateLeaderboard($tid, $period);

        $leaderboard = Database::fetchAll(
            "SELECT ls.*, u.name, u.email
             FROM leaderboard_snapshots ls
             JOIN users u ON ls.user_id = u.id
             WHERE ls.tenant_id = ? AND ls.period = ?
             ORDER BY ls.points DESC, ls.revenue DESC",
            [$tid, $period]
        );

        // Assign ranks
        foreach ($leaderboard as $i => &$entry) {
            $entry['rank'] = $i + 1;
        }

        return $this->view('gamification.leaderboard', [
            'pageTitle' => 'Bảng xếp hạng',
            'leaderboard' => $leaderboard,
            'period' => $period,
        ]);
    }

    public function achievements()
    {
        $uid = $this->userId();

        $achievements = Database::fetchAll(
            "SELECT a.*, ua.earned_at,
                    CASE WHEN ua.user_id IS NOT NULL THEN 1 ELSE 0 END as is_earned
             FROM achievements a
             LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
             ORDER BY a.points ASC",
            [$uid]
        );

        // Calculate progress for each achievement
        $tid = $this->tenantId();
        foreach ($achievements as &$ach) {
            $ach['progress'] = $this->calculateProgress($ach, $tid, $uid);
        }

        return $this->view('gamification.achievements', [
            'pageTitle' => 'Thành tựu',
            'achievements' => $achievements,
        ]);
    }

    public function myAchievements()
    {
        $uid = $this->userId();

        $achievements = Database::fetchAll(
            "SELECT a.*, ua.earned_at
             FROM user_achievements ua
             JOIN achievements a ON ua.achievement_id = a.id
             WHERE ua.user_id = ?
             ORDER BY ua.earned_at DESC",
            [$uid]
        );

        return $this->json(['achievements' => $achievements]);
    }

    private function generateLeaderboard(int $tid, string $period): void
    {
        // Check if already generated
        $exists = Database::fetch(
            "SELECT COUNT(*) as c FROM leaderboard_snapshots WHERE tenant_id = ? AND period = ?",
            [$tid, $period]
        );

        // Regenerate if empty or if current month
        if (((int)($exists['c'] ?? 0)) > 0 && $period !== date('Y-m')) {
            return;
        }

        // Delete old data for current period to refresh
        Database::query(
            "DELETE FROM leaderboard_snapshots WHERE tenant_id = ? AND period = ?",
            [$tid, $period]
        );

        $yearMonth = explode('-', $period);
        $year = (int)$yearMonth[0];
        $month = (int)$yearMonth[1];

        // Get all active users
        $users = Database::fetchAll(
            "SELECT id, name FROM users WHERE tenant_id = ? AND is_active = 1",
            [$tid]
        );

        foreach ($users as $user) {
            $uid = $user['id'];

            // Deals won this period
            $dealsWon = (int)(Database::fetch(
                "SELECT COUNT(*) as c FROM deals WHERE tenant_id = ? AND owner_id = ? AND status = 'won'
                 AND MONTH(updated_at) = ? AND YEAR(updated_at) = ?",
                [$tid, $uid, $month, $year]
            )['c'] ?? 0);

            // Revenue from won deals
            $revenue = (float)(Database::fetch(
                "SELECT COALESCE(SUM(value), 0) as total FROM deals WHERE tenant_id = ? AND owner_id = ? AND status = 'won'
                 AND MONTH(updated_at) = ? AND YEAR(updated_at) = ?",
                [$tid, $uid, $month, $year]
            )['total'] ?? 0);

            // Activities count
            $activities = (int)(Database::fetch(
                "SELECT COUNT(*) as c FROM activities WHERE tenant_id = ? AND user_id = ?
                 AND MONTH(created_at) = ? AND YEAR(created_at) = ?",
                [$tid, $uid, $month, $year]
            )['c'] ?? 0);

            // Points: deals * 20 + activities * 2
            $points = ($dealsWon * 20) + ($activities * 2);

            // Add achievement points
            $achievementPoints = (int)(Database::fetch(
                "SELECT COALESCE(SUM(a.points), 0) as p
                 FROM user_achievements ua
                 JOIN achievements a ON ua.achievement_id = a.id
                 WHERE ua.user_id = ?",
                [$uid]
            )['p'] ?? 0);
            $points += $achievementPoints;

            Database::query(
                "INSERT INTO leaderboard_snapshots (tenant_id, user_id, period, deals_won, revenue, activities_count, points, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE deals_won = VALUES(deals_won), revenue = VALUES(revenue),
                 activities_count = VALUES(activities_count), points = VALUES(points)",
                [$tid, $uid, $period, $dealsWon, $revenue, $activities, $points]
            );
        }

        // Update ranks
        $ranked = Database::fetchAll(
            "SELECT id FROM leaderboard_snapshots WHERE tenant_id = ? AND period = ? ORDER BY points DESC, revenue DESC",
            [$tid, $period]
        );
        foreach ($ranked as $i => $r) {
            Database::query("UPDATE leaderboard_snapshots SET rank_position = ? WHERE id = ?", [$i + 1, $r['id']]);
        }

        // Check and award achievements
        try { $this->checkAchievements($tid); } catch (\Exception $e) {}
    }

    private function checkAchievements(int $tid): void
    {
        $users = Database::fetchAll("SELECT id FROM users WHERE tenant_id = ? AND is_active = 1", [$tid]);
        $achievements = Database::fetchAll("SELECT * FROM achievements");

        foreach ($users as $user) {
            $uid = $user['id'];
            foreach ($achievements as $ach) {
                // Skip if already earned
                $earned = Database::fetch(
                    "SELECT 1 FROM user_achievements WHERE user_id = ? AND achievement_id = ?",
                    [$uid, $ach['id']]
                );
                if ($earned) continue;

                $progress = $this->calculateProgress($ach, $tid, $uid);
                if ($progress >= 100) {
                    Database::query(
                        "INSERT IGNORE INTO user_achievements (user_id, achievement_id, earned_at) VALUES (?, ?, NOW())",
                        [$uid, $ach['id']]
                    );
                }
            }
        }
    }

    private function calculateProgress(array $ach, int $tid, int $uid): int
    {
        $current = 0;
        $target = (int)$ach['criteria_value'];

        switch ($ach['criteria_type']) {
            case 'deals_won':
                $current = (int)(Database::fetch(
                    "SELECT COUNT(*) as c FROM deals WHERE tenant_id = ? AND owner_id = ? AND status = 'won'",
                    [$tid, $uid]
                )['c'] ?? 0);
                break;

            case 'monthly_activities':
                $current = (int)(Database::fetch(
                    "SELECT COUNT(*) as c FROM activities WHERE tenant_id = ? AND user_id = ?
                     AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())",
                    [$tid, $uid]
                )['c'] ?? 0);
                break;

            case 'contacts_created':
                $current = (int)(Database::fetch(
                    "SELECT COUNT(*) as c FROM contacts WHERE tenant_id = ? AND created_by = ? AND is_deleted = 0",
                    [$tid, $uid]
                )['c'] ?? 0);
                break;

            case 'tickets_resolved':
                $current = (int)(Database::fetch(
                    "SELECT COUNT(*) as c FROM tickets WHERE tenant_id = ? AND assigned_to = ? AND status = 'resolved'",
                    [$tid, $uid]
                )['c'] ?? 0);
                break;

            case 'top_revenue':
                // Special: check if user was rank 1 in any month
                $topCheck = Database::fetch(
                    "SELECT COUNT(*) as c FROM leaderboard_snapshots WHERE user_id = ? AND rank_position = 1",
                    [$uid]
                );
                $current = (int)($topCheck['c'] ?? 0);
                break;
        }

        if ($target <= 0) return 0;
        return min(100, (int)(($current / $target) * 100));
    }
}
