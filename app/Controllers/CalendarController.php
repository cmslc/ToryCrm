<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\CalendarEvent;

class CalendarController extends Controller
{
    public function index()
    {
        $this->authorize('calendar', 'view');
        $calendarModel = new CalendarEvent();
        $userId = $this->userId();

        $today = $calendarModel->getToday($userId);
        $upcoming = $calendarModel->getUpcoming($userId, 10);

        return $this->view('calendar.index', [
            'today' => $today,
            'upcoming' => $upcoming,
        ]);
    }

    public function events()
    {
        $this->authorize('calendar', 'view');
        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t 23:59:59'));

        $calendarModel = new CalendarEvent();
        $events = $calendarModel->getForCalendarJson($start, $end, $this->userId());

        return $this->json($events);
    }

    public function create()
    {
        $this->authorize('calendar', 'create');
        $tid = Database::tenantId();
        $contacts = Database::fetchAll("SELECT id, first_name, last_name, company_name FROM contacts WHERE is_deleted = 0 AND tenant_id = ? ORDER BY first_name LIMIT 500", [$tid]);
        $companies = Database::fetchAll("SELECT id, name FROM companies WHERE tenant_id = ? ORDER BY name", [$tid]);
        $deals = Database::fetchAll("SELECT id, title FROM deals WHERE status = 'open' AND tenant_id = ? ORDER BY title", [$tid]);
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 AND u.tenant_id = ? ORDER BY d.name, u.name", [$tid]);

        return $this->view('calendar.create', [
            'contacts' => $contacts,
            'companies' => $companies,
            'deals' => $deals,
            'users' => $users,
            'defaultDate' => $this->input('date', date('Y-m-d')),
            'selectedContactId' => (int) $this->input('contact_id'),
            'selectedCompanyId' => (int) $this->input('company_id'),
            'selectedDealId' => (int) $this->input('deal_id'),
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) {
            return $this->redirect('calendar');
        }
        $this->authorize('calendar', 'create');
        $tid = Database::tenantId();

        $data = $this->allInput();
        $title = trim($data['title'] ?? '');

        if (empty($title)) {
            $this->setFlash('error', 'Tiêu đề không được để trống.');
            return $this->back();
        }

        if (empty($data['start_at'])) {
            $this->setFlash('error', 'Thời gian bắt đầu không được để trống.');
            return $this->back();
        }

        // Verify referenced entities belong to current tenant
        if (!empty($data['contact_id'])) {
            $ok = Database::fetch("SELECT id FROM contacts WHERE id = ? AND tenant_id = ?", [$data['contact_id'], $tid]);
            if (!$ok) { $this->setFlash('error', 'Khách hàng không hợp lệ.'); return $this->back(); }
        }
        if (!empty($data['deal_id'])) {
            $ok = Database::fetch("SELECT id FROM deals WHERE id = ? AND tenant_id = ?", [$data['deal_id'], $tid]);
            if (!$ok) { $this->setFlash('error', 'Cơ hội không hợp lệ.'); return $this->back(); }
        }

        $typeColors = [
            'meeting' => '#405189',
            'call' => '#0ab39c',
            'visit' => '#f06548',
            'reminder' => '#ffbe0b',
            'other' => '#299cdb',
        ];

        $type = $data['type'] ?? 'meeting';

        $eventId = Database::insert('calendar_events', [
            'tenant_id' => $tid,
            'title' => $title,
            'description' => trim($data['description'] ?? ''),
            'type' => $type,
            'color' => $typeColors[$type] ?? '#405189',
            'start_at' => $data['start_at'],
            'end_at' => !empty($data['end_at']) ? $data['end_at'] : null,
            'all_day' => isset($data['all_day']) ? 1 : 0,
            'location' => trim($data['location'] ?? ''),
            'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
            'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
            'deal_id' => !empty($data['deal_id']) ? $data['deal_id'] : null,
            'user_id' => !empty($data['user_id']) ? $data['user_id'] : $this->userId(),
            'created_by' => $this->userId(),
        ]);

        Database::insert('activities', [
            'tenant_id' => $tid,
            'type' => 'meeting',
            'title' => "Lịch hẹn mới: {$title}",
            'description' => "Lịch hẹn {$title} vào " . $data['start_at'],
            'user_id' => $this->userId(),
            'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
            'deal_id' => !empty($data['deal_id']) ? $data['deal_id'] : null,
        ]);

        $this->setFlash('success', 'Tạo lịch hẹn thành công.');
        return $this->redirect('calendar');
    }

    private function fetchEventForAccess($id)
    {
        return Database::fetch(
            "SELECT * FROM calendar_events WHERE id = ? AND tenant_id = ?",
            [$id, Database::tenantId()]
        );
    }

    private function canAccessEvent(array $event): bool
    {
        $ownerId = (int)($event['user_id'] ?? $event['created_by'] ?? 0);
        return $this->canAccessOwner($ownerId, 'calendar');
    }

    public function show($id)
    {
        $this->authorize('calendar', 'view');
        $event = $this->fetchEventForAccess($id);
        if (!$event) { $this->setFlash('error', 'Lịch hẹn không tồn tại.'); return $this->redirect('calendar'); }
        if (!$this->canAccessEvent($event)) { $this->setFlash('error', 'Không có quyền.'); return $this->redirect('calendar'); }

        $event = Database::fetch(
            "SELECT ce.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name,
                    d.title as deal_title,
                    u.name as user_name,
                    uc.name as created_by_name
             FROM calendar_events ce
             LEFT JOIN contacts c ON ce.contact_id = c.id
             LEFT JOIN companies comp ON ce.company_id = comp.id
             LEFT JOIN deals d ON ce.deal_id = d.id
             LEFT JOIN users u ON ce.user_id = u.id
             LEFT JOIN users uc ON ce.created_by = uc.id
             WHERE ce.id = ? AND ce.tenant_id = ?",
            [$id, Database::tenantId()]
        );

        return $this->view('calendar.show', ['event' => $event]);
    }

    public function edit($id)
    {
        $this->authorize('calendar', 'edit');
        $event = $this->fetchEventForAccess($id);
        if (!$event) { $this->setFlash('error', 'Lịch hẹn không tồn tại.'); return $this->redirect('calendar'); }
        if (!$this->canAccessEvent($event)) { $this->setFlash('error', 'Không có quyền.'); return $this->redirect('calendar'); }

        $tid = Database::tenantId();
        $contacts = Database::fetchAll("SELECT id, first_name, last_name, company_name FROM contacts WHERE is_deleted = 0 AND tenant_id = ? ORDER BY first_name LIMIT 500", [$tid]);
        $companies = Database::fetchAll("SELECT id, name FROM companies WHERE tenant_id = ? ORDER BY name", [$tid]);
        $deals = Database::fetchAll("SELECT id, title FROM deals WHERE status = 'open' AND tenant_id = ? ORDER BY title", [$tid]);
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 AND u.tenant_id = ? ORDER BY d.name, u.name", [$tid]);

        return $this->view('calendar.edit', [
            'event' => $event,
            'contacts' => $contacts,
            'companies' => $companies,
            'deals' => $deals,
            'users' => $users,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('calendar/' . $id);
        $this->authorize('calendar', 'edit');

        $event = $this->fetchEventForAccess($id);
        if (!$event) { $this->setFlash('error', 'Lịch hẹn không tồn tại.'); return $this->redirect('calendar'); }
        if (!$this->canAccessEvent($event)) { $this->setFlash('error', 'Không có quyền.'); return $this->redirect('calendar'); }

        $data = $this->allInput();
        $title = trim($data['title'] ?? '');

        if (empty($title)) {
            $this->setFlash('error', 'Tiêu đề không được để trống.');
            return $this->back();
        }

        $typeColors = [
            'meeting' => '#405189',
            'call' => '#0ab39c',
            'visit' => '#f06548',
            'reminder' => '#ffbe0b',
            'other' => '#299cdb',
        ];

        $type = $data['type'] ?? 'meeting';

        Database::update('calendar_events', [
            'title' => $title,
            'description' => trim($data['description'] ?? ''),
            'type' => $type,
            'color' => $typeColors[$type] ?? '#405189',
            'start_at' => $data['start_at'],
            'end_at' => !empty($data['end_at']) ? $data['end_at'] : null,
            'all_day' => isset($data['all_day']) ? 1 : 0,
            'location' => trim($data['location'] ?? ''),
            'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
            'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
            'deal_id' => !empty($data['deal_id']) ? $data['deal_id'] : null,
            'user_id' => !empty($data['user_id']) ? $data['user_id'] : null,
        ], 'id = ? AND tenant_id = ?', [$id, Database::tenantId()]);

        $this->setFlash('success', 'Cập nhật lịch hẹn thành công.');
        return $this->redirect('calendar/' . $id);
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('calendar/' . $id);
        $this->authorize('calendar', 'delete');

        $event = $this->fetchEventForAccess($id);
        if (!$event) { $this->setFlash('error', 'Lịch hẹn không tồn tại.'); return $this->redirect('calendar'); }
        if (!$this->canAccessEvent($event)) { $this->setFlash('error', 'Không có quyền.'); return $this->redirect('calendar'); }

        Database::delete('calendar_events', 'id = ? AND tenant_id = ?', [$id, Database::tenantId()]);

        $this->setFlash('success', 'Xóa lịch hẹn thành công.');
        return $this->redirect('calendar');
    }

    public function complete($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        $this->authorize('calendar', 'edit');

        $event = $this->fetchEventForAccess($id);
        if (!$event) return $this->json(['error' => 'Event not found'], 404);
        if (!$this->canAccessEvent($event)) return $this->json(['error' => 'Không có quyền'], 403);

        Database::update('calendar_events', [
            'is_completed' => 1,
            'completed_at' => date('Y-m-d H:i:s'),
        ], 'id = ? AND tenant_id = ?', [$id, Database::tenantId()]);

        return $this->json(['success' => true]);
    }
}
