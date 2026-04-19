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
        $contactId = $this->input('contact_id');
        $dateFrom = $this->input('date_from');
        $dateTo = $this->input('date_to');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = in_array((int)$this->input('per_page'), [10,20,50,100]) ? (int)$this->input('per_page') : 20;
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

        if ($contactId) {
            $where[] = "a.contact_id = ?";
            $params[] = $contactId;
        }

        if ($dateFrom) {
            $where[] = "a.created_at >= ?";
            $params[] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo) {
            $where[] = "a.created_at <= ?";
            $params[] = $dateTo . ' 23:59:59';
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

        // Get users for filter
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

        return $this->view('activities.index', [
            'activities' => $activities,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'users' => $users,
            'filters' => [
                'type' => $type,
                'user_id' => $userId,
                'contact_id' => $contactId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
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

        $contactId = $data['contact_id'] ?? null;

        // Handle file uploads (single or multiple)
        $attachPaths = [];
        $attachNames = [];
        $totalSize = 0;
        $allowed = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','xls','xlsx','ppt','pptx','txt','csv','zip','rar','dwg','dxf','cad','dwf','skp','3ds','obj','stl','step','stp','iges','igs'];
        $files = $_FILES['attachments'] ?? $_FILES['attachment'] ?? null;
        if ($files && !empty($files['name'])) {
            $names = is_array($files['name']) ? $files['name'] : [$files['name']];
            $tmps = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
            $sizes = is_array($files['size']) ? $files['size'] : [$files['size']];
            $errors = is_array($files['error']) ? $files['error'] : [$files['error']];
            $dir = 'uploads/activities/' . date('Y/m');
            if (!is_dir(BASE_PATH . '/public/' . $dir)) mkdir(BASE_PATH . '/public/' . $dir, 0755, true);
            for ($i = 0; $i < count($names); $i++) {
                if ($errors[$i] !== UPLOAD_ERR_OK || empty($names[$i])) continue;
                $ext = strtolower(pathinfo($names[$i], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed) || $sizes[$i] > 10 * 1024 * 1024) continue;
                $fileName = uniqid() . '_' . $i . '.' . $ext;
                move_uploaded_file($tmps[$i], BASE_PATH . '/public/' . $dir . '/' . $fileName);
                $attachPaths[] = $dir . '/' . $fileName;
                $attachNames[] = $names[$i];
                $totalSize += $sizes[$i];
            }
        }
        $attachPath = !empty($attachPaths) ? implode('|', $attachPaths) : null;
        $attachName = !empty($attachNames) ? implode('|', $attachNames) : null;

        $activityId = Database::insert('activities', [
            'type' => $type,
            'title' => $title,
            'description' => trim($data['description'] ?? ''),
            'attachment' => $attachPath,
            'attachment_name' => $attachName,
            'attachment_size' => $totalSize ?: null,
            'user_id' => $this->userId(),
            'contact_id' => $contactId,
            'deal_id' => $data['deal_id'] ?? null,
            'company_id' => $data['company_id'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);

        // Update last_activity_at on contact
        if ($contactId && $type !== 'system') {
            Database::update('contacts', ['last_activity_at' => date('Y-m-d H:i:s')], 'id = ?', [$contactId]);
        }

        // Notify tagged users
        $taggedUsers = trim($data['tagged_users'] ?? '');
        if ($taggedUsers) {
            $taggedIds = array_filter(array_map('intval', explode(',', $taggedUsers)));
            $currentUserName = $_SESSION['user']['name'] ?? 'Ai đó';
            $contactName = '';
            if ($contactId) {
                $c = Database::fetch("SELECT first_name, last_name, company_name FROM contacts WHERE id = ?", [$contactId]);
                $contactName = $c ? trim(($c['company_name'] ?: '') ?: ($c['first_name'] . ' ' . ($c['last_name'] ?? ''))) : '';
            }
            foreach ($taggedIds as $uid) {
                if ($uid == $this->userId()) continue;
                Database::insert('notifications', [
                    'tenant_id' => Database::tenantId(),
                    'user_id' => $uid,
                    'type' => 'info',
                    'title' => $currentUserName . ' đã nhắc đến bạn',
                    'message' => mb_substr($title, 0, 200),
                    'link' => $contactId ? 'contacts/' . $contactId : null,
                    'icon' => 'ri-at-line',
                ]);
            }
        }

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

    public function react($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Invalid'], 400);

        $type = $this->input('type') === 'dislike' ? 'dislike' : 'like';
        $uid = $this->userId();

        $existing = Database::fetch("SELECT id, type FROM activity_reactions WHERE activity_id = ? AND user_id = ?", [$id, $uid]);

        if ($existing) {
            if ($existing['type'] === $type) {
                Database::delete('activity_reactions', 'id = ?', [$existing['id']]);
            } else {
                Database::update('activity_reactions', ['type' => $type], 'id = ?', [$existing['id']]);
            }
        } else {
            Database::insert('activity_reactions', [
                'activity_id' => $id,
                'user_id' => $uid,
                'type' => $type,
            ]);
        }

        $likes = (int)Database::fetch("SELECT COUNT(*) as c FROM activity_reactions WHERE activity_id = ? AND type = 'like'", [$id])['c'];
        $dislikes = (int)Database::fetch("SELECT COUNT(*) as c FROM activity_reactions WHERE activity_id = ? AND type = 'dislike'", [$id])['c'];
        $myReaction = Database::fetch("SELECT type FROM activity_reactions WHERE activity_id = ? AND user_id = ?", [$id, $uid]);

        return $this->json(['likes' => $likes, 'dislikes' => $dislikes, 'my' => $myReaction['type'] ?? null]);
    }

    public function reply($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Invalid'], 400);

        $content = trim($this->input('content') ?? '');
        if (empty($content) && empty($_FILES['attachment']['name'])) return $this->json(['error' => 'Nội dung trống'], 422);

        $parent = Database::fetch("SELECT id, contact_id FROM activities WHERE id = ?", [$id]);
        if (!$parent) return $this->json(['error' => 'Không tồn tại'], 404);

        // Handle file upload
        $attachPath = null;
        $attachName = null;
        $attachSize = null;
        if (!empty($_FILES['attachment']['name']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['attachment'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','xls','xlsx','ppt','pptx','txt','csv','zip','rar','dwg','dxf','cad','dwf','skp','3ds','obj','stl','step','stp','iges','igs'];
            if (in_array($ext, $allowed) && $file['size'] <= 10 * 1024 * 1024) {
                $dir = 'uploads/activities/' . date('Y/m');
                if (!is_dir(BASE_PATH . '/public/' . $dir)) mkdir(BASE_PATH . '/public/' . $dir, 0755, true);
                $fileName = uniqid() . '.' . $ext;
                move_uploaded_file($file['tmp_name'], BASE_PATH . '/public/' . $dir . '/' . $fileName);
                $attachPath = $dir . '/' . $fileName;
                $attachName = $file['name'];
                $attachSize = $file['size'];
            }
        }

        $replyId = Database::insert('activities', [
            'type' => 'note',
            'title' => $content ?: ($attachName ? '📎 ' . $attachName : ''),
            'user_id' => $this->userId(),
            'contact_id' => $parent['contact_id'],
            'parent_id' => $id,
            'attachment' => $attachPath,
            'attachment_name' => $attachName,
            'attachment_size' => $attachSize,
        ]);

        $reply = Database::fetch("SELECT a.*, u.name as user_name, u.avatar as user_avatar FROM activities a LEFT JOIN users u ON a.user_id = u.id WHERE a.id = ?", [$replyId]);

        return $this->json(['success' => true, 'reply' => $reply]);
    }

    public function edit($id)
    {
        $activity = Database::fetch(
            "SELECT a.*, u.name as user_name
             FROM activities a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE a.id = ?",
            [$id]
        );

        if (!$activity) {
            $this->setFlash('error', 'Không tìm thấy hoạt động.');
            return $this->redirect('activities');
        }

        // Return JSON for modal edit
        return $this->json(['activity' => $activity]);
    }

    public function update($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $activity = Database::fetch("SELECT * FROM activities WHERE id = ?", [$id]);
        if (!$activity) {
            return $this->json(['error' => 'Không tìm thấy hoạt động'], 404);
        }

        $data = $this->allInput();
        $title = trim($data['title'] ?? '');
        $type = trim($data['type'] ?? $activity['type']);

        if (empty($title)) {
            return $this->json(['error' => 'Tiêu đề không được để trống'], 422);
        }

        Database::query(
            "UPDATE activities SET type = ?, title = ?, description = ?, scheduled_at = ? WHERE id = ?",
            [
                $type,
                $title,
                trim($data['description'] ?? ''),
                $data['scheduled_at'] ?? null,
                $id,
            ]
        );

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return $this->json(['success' => true]);
        }

        $this->setFlash('success', 'Đã cập nhật hoạt động.');
        return $this->redirect('activities');
    }

    public function delete($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        Database::query("DELETE FROM activities WHERE id = ?", [$id]);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return $this->json(['success' => true]);
        }

        $this->setFlash('success', 'Đã xóa hoạt động.');
        return $this->redirect('activities');
    }

    public function calendar()
    {
        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t'));

        $activities = Database::fetchAll(
            "SELECT a.id, a.type, a.title, a.description, a.scheduled_at, a.created_at, u.name as user_name
             FROM activities a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE (a.scheduled_at BETWEEN ? AND ?) OR (a.scheduled_at IS NULL AND a.created_at BETWEEN ? AND ?)
             ORDER BY COALESCE(a.scheduled_at, a.created_at) ASC",
            [$start, $end . ' 23:59:59', $start, $end . ' 23:59:59']
        );

        $typeColors = [
            'note' => '#405189',
            'call' => '#0ab39c',
            'email' => '#299cdb',
            'meeting' => '#f7b84b',
            'task' => '#f06548',
            'deal' => '#0ab39c',
            'system' => '#878a99',
        ];

        $events = [];
        foreach ($activities as $act) {
            $events[] = [
                'id' => $act['id'],
                'title' => $act['title'],
                'start' => $act['scheduled_at'] ?? $act['created_at'],
                'backgroundColor' => $typeColors[$act['type']] ?? '#405189',
                'borderColor' => $typeColors[$act['type']] ?? '#405189',
                'extendedProps' => [
                    'type' => $act['type'],
                    'description' => $act['description'] ?? '',
                    'user_name' => $act['user_name'] ?? '',
                ],
            ];
        }

        return $this->json($events);
    }
}
