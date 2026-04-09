<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class ActivityController extends Controller
{
    public function index()
    {
        $type = $this->input('type');
        $userId = $this->input('user_id');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        $where = ["1=1"];
        $params = [];

        if ($type) {
            $where[] = "a.type = ?";
            $params[] = $type;
        }

        if ($userId) {
            $where[] = "a.user_id = ?";
            $params[] = $userId;
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count FROM activities a WHERE {$whereClause}",
            $params
        )['count'];

        $activities = Database::fetchAll(
            "SELECT a.*, u.name as user_name,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    d.title as deal_title, comp.name as company_name
             FROM activities a
             LEFT JOIN users u ON a.user_id = u.id
             LEFT JOIN contacts c ON a.contact_id = c.id
             LEFT JOIN deals d ON a.deal_id = d.id
             LEFT JOIN companies comp ON a.company_id = comp.id
             WHERE {$whereClause}
             ORDER BY a.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = ceil($total / $perPage);

        return $this->view('activities.index', [
            'activities' => $activities,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'filters' => [
                'type' => $type,
                'user_id' => $userId,
            ],
        ]);
    }

    public function feed()
    {
        $activities = Database::fetchAll(
            "SELECT a.*, u.name as user_name,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    d.title as deal_title, comp.name as company_name
             FROM activities a
             LEFT JOIN users u ON a.user_id = u.id
             LEFT JOIN contacts c ON a.contact_id = c.id
             LEFT JOIN deals d ON a.deal_id = d.id
             LEFT JOIN companies comp ON a.company_id = comp.id
             ORDER BY a.created_at DESC
             LIMIT 20"
        );

        $now = time();
        foreach ($activities as &$act) {
            $created = strtotime($act['created_at'] ?? '');
            if ($created) {
                $diff = $now - $created;
                if ($diff < 60) {
                    $act['time_ago'] = 'Vừa xong';
                } elseif ($diff < 3600) {
                    $act['time_ago'] = floor($diff / 60) . ' phút trước';
                } elseif ($diff < 86400) {
                    $act['time_ago'] = floor($diff / 3600) . ' giờ trước';
                } elseif ($diff < 604800) {
                    $act['time_ago'] = floor($diff / 86400) . ' ngày trước';
                } else {
                    $act['time_ago'] = date('d/m/Y', $created);
                }
            } else {
                $act['time_ago'] = '';
            }
        }
        unset($act);

        // Count activities in last 24h as "new"
        $newCount = 0;
        foreach ($activities as $a) {
            $created = strtotime($a['created_at'] ?? '');
            if ($created && ($now - $created) < 86400) {
                $newCount++;
            }
        }

        return $this->json([
            'activities' => $activities,
            'new_count' => $newCount,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $data = $this->allInput();

        $type = trim($data['type'] ?? '');
        $title = trim($data['title'] ?? '');

        if (empty($type) || empty($title)) {
            return $this->json(['error' => 'Type and title are required'], 422);
        }

        $activityId = Database::insert('activities', [
            'type' => $type,
            'title' => $title,
            'description' => trim($data['description'] ?? ''),
            'user_id' => $this->userId(),
            'contact_id' => $data['contact_id'] ?? null,
            'deal_id' => $data['deal_id'] ?? null,
            'company_id' => $data['company_id'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);

        // AJAX request → return JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $activity = Database::fetch(
                "SELECT a.*, u.name as user_name
                 FROM activities a
                 LEFT JOIN users u ON a.user_id = u.id
                 WHERE a.id = ?",
                [$activityId]
            );
            return $this->json(['success' => true, 'activity' => $activity]);
        }

        // Normal form submit → redirect back
        $this->setFlash('success', 'Đã thêm hoạt động.');
        $referer = $_SERVER['HTTP_REFERER'] ?? url('activities');
        header('Location: ' . $referer);
        exit;
    }
}
