<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\CallLog;

class CallLogController extends Controller
{
    public function index()
    {
        $model = new CallLog();
        $page = max(1, (int) $this->input('page') ?: 1);

        $callLogs = $model->getWithRelations($page, 10, [
            'search' => $this->input('search'),
            'call_type' => $this->input('call_type'),
            'status' => $this->input('status'),
            'user_id' => $this->input('user_id'),
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
        ]);

        $stats = $model->getStats(
            $this->input('date_from') ?: date('Y-m-01'),
            $this->input('date_to') ?: date('Y-m-t')
        );

        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

        return $this->view('call-logs.index', [
            'callLogs' => $callLogs,
            'stats' => $stats,
            'users' => $users,
            'filters' => [
                'search' => $this->input('search'),
                'call_type' => $this->input('call_type'),
                'status' => $this->input('status'),
                'user_id' => $this->input('user_id'),
                'date_from' => $this->input('date_from'),
                'date_to' => $this->input('date_to'),
            ],
        ]);
    }

    public function create()
    {
        $contacts = Database::fetchAll("SELECT id, first_name, last_name, phone FROM contacts ORDER BY first_name");
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

        return $this->view('call-logs.create', [
            'contacts' => $contacts,
            'users' => $users,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('call-logs');

        $data = $this->allInput();

        Database::insert('call_logs', [
            'call_type' => $data['call_type'] ?? 'outbound',
            'caller_number' => trim($data['caller_number'] ?? ''),
            'callee_number' => trim($data['callee_number'] ?? ''),
            'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
            'user_id' => !empty($data['user_id']) ? $data['user_id'] : $this->userId(),
            'duration' => (int)($data['duration'] ?? 0),
            'status' => $data['status'] ?? 'answered',
            'notes' => trim($data['notes'] ?? ''),
            'started_at' => !empty($data['started_at']) ? $data['started_at'] : date('Y-m-d H:i:s'),
        ]);

        Database::insert('activities', [
            'type' => 'call',
            'title' => ($data['call_type'] === 'inbound' ? 'Cuộc gọi đến' : 'Cuộc gọi đi') . ': ' . ($data['caller_number'] ?? ''),
            'user_id' => $this->userId(),
            'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
        ]);

        $this->setFlash('success', 'Đã ghi nhận cuộc gọi.');
        return $this->redirect('call-logs');
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('call-logs');

        Database::delete('call_logs', 'id = ?', [$id]);
        $this->setFlash('success', 'Đã xóa log cuộc gọi.');
        return $this->redirect('call-logs');
    }
}
