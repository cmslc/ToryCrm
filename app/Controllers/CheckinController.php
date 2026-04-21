<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class CheckinController extends Controller
{
    public function index()
    {
        $this->authorize('checkins', 'view');
        $tid = Database::tenantId();
        $userId = $this->input('user_id');
        $dateFrom = $this->input('date_from');
        $dateTo = $this->input('date_to');
        $contactSearch = $this->input('contact');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = in_array((int)$this->input('per_page'), [10,20,50,100]) ? (int)$this->input('per_page') : 20;
        $offset = ($page - 1) * $perPage;

        $where = ["ch.tenant_id = ?"];
        $params = [$tid];
        $scopeSql = $this->getOwnerScopeSql('ch.user_id', 'checkins');
        if ($scopeSql) $where[] = ltrim($scopeSql, ' AND ');

        if ($userId) {
            $where[] = "ch.user_id = ?";
            $params[] = $userId;
        }

        if ($dateFrom) {
            $where[] = "ch.created_at >= ?";
            $params[] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo) {
            $where[] = "ch.created_at <= ?";
            $params[] = $dateTo . ' 23:59:59';
        }

        if ($contactSearch) {
            $where[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR comp.name LIKE ?)";
            $params[] = "%{$contactSearch}%";
            $params[] = "%{$contactSearch}%";
            $params[] = "%{$contactSearch}%";
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count FROM checkins ch
             LEFT JOIN contacts c ON ch.contact_id = c.id
             LEFT JOIN companies comp ON ch.company_id = comp.id
             WHERE {$whereClause}",
            $params
        )['count'];

        $checkins = Database::fetchAll(
            "SELECT ch.*, u.name as user_name,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name
             FROM checkins ch
             LEFT JOIN users u ON ch.user_id = u.id
             LEFT JOIN contacts c ON ch.contact_id = c.id
             LEFT JOIN companies comp ON ch.company_id = comp.id
             WHERE {$whereClause}
             ORDER BY ch.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = ceil($total / $perPage);

        // Stats (tenant-scoped + owner-scoped)
        $statScopeSql = $this->getOwnerScopeSql('user_id', 'checkins');
        $statsToday = Database::fetch(
            "SELECT COUNT(*) as count FROM checkins WHERE tenant_id = ? AND DATE(created_at) = CURDATE(){$statScopeSql}",
            [$tid]
        )['count'];

        $statsWeek = Database::fetch(
            "SELECT COUNT(*) as count FROM checkins WHERE tenant_id = ? AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1){$statScopeSql}",
            [$tid]
        )['count'];

        $statsMonth = Database::fetch(
            "SELECT COUNT(*) as count FROM checkins WHERE tenant_id = ? AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()){$statScopeSql}",
            [$tid]
        )['count'];

        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 AND u.tenant_id = ? ORDER BY d.name, u.name", [$tid]);

        return $this->view('checkins.index', [
            'checkins' => $checkins,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'users' => $users,
            'statsToday' => $statsToday,
            'statsWeek' => $statsWeek,
            'statsMonth' => $statsMonth,
            'filters' => [
                'user_id' => $userId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'contact' => $contactSearch,
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('checkins', 'create');
        $tid = Database::tenantId();
        $contacts = Database::fetchAll(
            "SELECT id, first_name, last_name FROM contacts WHERE tenant_id = ? AND is_deleted = 0 ORDER BY first_name",
            [$tid]
        );

        $companies = Database::fetchAll(
            "SELECT id, name FROM companies WHERE tenant_id = ? ORDER BY name",
            [$tid]
        );

        return $this->view('checkins.create', [
            'contacts' => $contacts,
            'companies' => $companies,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('checkins');
        $this->authorize('checkins', 'create');

        $data = $this->allInput();

        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;

        if (empty($latitude) || empty($longitude)) {
            $this->setFlash('error', 'Không thể xác định vị trí. Vui lòng bật GPS.');
            return $this->redirect('checkins/create');
        }

        // Handle photo upload
        $photoPath = null;
        if (!empty($_FILES['photo']['tmp_name'])) {
            $uploadDir = BASE_PATH . '/public/uploads/checkins/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = 'checkin_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                $photoPath = '/uploads/checkins/' . $filename;
            }
        }

        $checkinId = Database::insert('checkins', [
            'tenant_id' => $this->tenantId(),
            'user_id' => $this->userId(),
            'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
            'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => trim($data['address'] ?? ''),
            'note' => trim($data['note'] ?? ''),
            'photo' => $photoPath,
            'check_type' => $data['check_type'] ?? 'visit',
        ]);

        // Create activity log entry
        $checkTypeLabels = [
            'visit' => 'Thăm KH',
            'meeting' => 'Họp',
            'delivery' => 'Giao hàng',
            'other' => 'Khác',
        ];
        $typeLabel = $checkTypeLabels[$data['check_type'] ?? 'visit'] ?? 'Check-in';

        Database::insert('activities', [
            'type' => 'meeting',
            'title' => "Check-in: {$typeLabel}" . (!empty($data['address']) ? ' - ' . $data['address'] : ''),
            'user_id' => $this->userId(),
            'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
            'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
        ]);

        $this->setFlash('success', 'Check-in thành công!');
        return $this->redirect('checkins');
    }

    public function show($id)
    {
        $this->authorize('checkins', 'view');
        $checkin = Database::fetch(
            "SELECT ch.*, u.name as user_name,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name
             FROM checkins ch
             LEFT JOIN users u ON ch.user_id = u.id
             LEFT JOIN contacts c ON ch.contact_id = c.id
             LEFT JOIN companies comp ON ch.company_id = comp.id
             WHERE ch.id = ? AND ch.tenant_id = ?",
            [$id, Database::tenantId()]
        );

        if (!$checkin) {
            $this->setFlash('error', 'Không tìm thấy check-in.');
            return $this->redirect('checkins');
        }
        if (!$this->canAccessOwner((int)($checkin['user_id'] ?? 0), 'checkins')) {
            $this->setFlash('error', 'Không có quyền.');
            return $this->redirect('checkins');
        }

        return $this->view('checkins.show', [
            'checkin' => $checkin,
        ]);
    }

    public function myCheckins()
    {
        $checkins = Database::fetchAll(
            "SELECT ch.*, c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name
             FROM checkins ch
             LEFT JOIN contacts c ON ch.contact_id = c.id
             LEFT JOIN companies comp ON ch.company_id = comp.id
             WHERE ch.user_id = ? AND DATE(ch.created_at) = CURDATE()
             ORDER BY ch.created_at DESC",
            [$this->userId()]
        );

        return $this->json(['checkins' => $checkins]);
    }

    public function map()
    {
        $dateFrom = $this->input('date_from') ?: date('Y-m-d');
        $dateTo = $this->input('date_to') ?: date('Y-m-d');

        $checkins = Database::fetchAll(
            "SELECT ch.*, u.name as user_name,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name
             FROM checkins ch
             LEFT JOIN users u ON ch.user_id = u.id
             LEFT JOIN contacts c ON ch.contact_id = c.id
             LEFT JOIN companies comp ON ch.company_id = comp.id
             WHERE ch.created_at >= ? AND ch.created_at <= ?
             ORDER BY ch.created_at DESC",
            [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']
        );

        return $this->view('checkins.map', [
            'checkins' => $checkins,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}
