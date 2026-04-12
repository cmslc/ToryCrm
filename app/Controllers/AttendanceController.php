<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class AttendanceController extends Controller
{
    private function checkPlugin(): bool
    {
        try {
            $installed = \App\Services\PluginManager::getInstalled($this->tenantId());
            foreach ($installed as $p) {
                if ($p['slug'] === 'attendance-payroll' && $p['tenant_active']) return true;
            }
        } catch (\Exception $e) {}
        $this->setFlash('error', 'Plugin Chấm công & Lương chưa được cài đặt.');
        $this->redirect('plugins/marketplace');
        return false;
    }

    // ---- Chấm công ----
    public function index()
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();
        $month = (int)($this->input('month') ?: date('m'));
        $year = (int)($this->input('year') ?: date('Y'));

        $users = Database::fetchAll("SELECT id, name, email, base_salary FROM users WHERE tenant_id = ? AND is_active = 1 ORDER BY name", [$tid]);
        $attendances = Database::fetchAll(
            "SELECT * FROM attendances WHERE tenant_id = ? AND MONTH(date) = ? AND YEAR(date) = ?",
            [$tid, $month, $year]
        );

        // Group by user_id => date => record
        $map = [];
        foreach ($attendances as $a) {
            $map[$a['user_id']][$a['date']] = $a;
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        return $this->view('attendance.index', compact('users', 'map', 'month', 'year', 'daysInMonth'));
    }

    public function checkIn()
    {
        if (!$this->isPost()) return $this->redirect('attendance');
        $tid = Database::tenantId();
        $uid = $this->userId();
        $today = date('Y-m-d');
        $now = date('H:i:s');

        $existing = Database::fetch("SELECT * FROM attendances WHERE tenant_id = ? AND user_id = ? AND date = ?", [$tid, $uid, $today]);

        if ($existing) {
            if (!$existing['check_out']) {
                // Check out
                $checkIn = $existing['check_in'];
                $hours = round((strtotime($now) - strtotime($checkIn)) / 3600, 2);
                Database::update('attendances', [
                    'check_out' => $now,
                    'work_hours' => $hours,
                    'overtime_hours' => max(0, $hours - 8),
                ], 'id = ?', [$existing['id']]);
                $this->setFlash('success', 'Check-out lúc ' . date('H:i'));
            } else {
                $this->setFlash('warning', 'Đã check-out hôm nay rồi.');
            }
        } else {
            // Check in
            $status = (date('H:i') > '08:30') ? 'late' : 'present';
            Database::insert('attendances', [
                'tenant_id' => $tid,
                'user_id' => $uid,
                'date' => $today,
                'check_in' => $now,
                'status' => $status,
            ]);
            $this->setFlash('success', 'Check-in lúc ' . date('H:i') . ($status === 'late' ? ' (Đi muộn)' : ''));
        }
        return $this->redirect('attendance');
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('attendance');
        $tid = Database::tenantId();

        $userId = (int)$this->input('user_id');
        $date = $this->input('date');
        $checkIn = $this->input('check_in') ?: null;
        $checkOut = $this->input('check_out') ?: null;
        $status = $this->input('status') ?: 'present';
        $note = trim($this->input('note') ?? '');

        $hours = null;
        if ($checkIn && $checkOut) {
            $hours = round((strtotime($checkOut) - strtotime($checkIn)) / 3600, 2);
        }

        $existing = Database::fetch("SELECT * FROM attendances WHERE tenant_id = ? AND user_id = ? AND date = ?", [$tid, $userId, $date]);

        if ($existing) {
            Database::update('attendances', [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'status' => $status,
                'work_hours' => $hours,
                'overtime_hours' => $hours ? max(0, $hours - 8) : 0,
                'note' => $note ?: null,
            ], 'id = ?', [$existing['id']]);
        } else {
            Database::insert('attendances', [
                'tenant_id' => $tid,
                'user_id' => $userId,
                'date' => $date,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'status' => $status,
                'work_hours' => $hours,
                'overtime_hours' => $hours ? max(0, $hours - 8) : 0,
                'note' => $note ?: null,
            ]);
        }

        $this->setFlash('success', 'Đã cập nhật chấm công.');
        return $this->redirect('attendance?month=' . date('m', strtotime($date)) . '&year=' . date('Y', strtotime($date)));
    }

    // ---- Nghỉ phép ----
    public function leaves()
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();
        $status = $this->input('status');

        $where = ["lr.tenant_id = ?"];
        $params = [$tid];
        if ($status) { $where[] = "lr.status = ?"; $params[] = $status; }

        $leaves = Database::fetchAll(
            "SELECT lr.*, u.name as user_name, a.name as approved_by_name
             FROM leave_requests lr
             LEFT JOIN users u ON lr.user_id = u.id
             LEFT JOIN users a ON lr.approved_by = a.id
             WHERE " . implode(' AND ', $where) . " ORDER BY lr.created_at DESC",
            $params
        );

        return $this->view('attendance.leaves', compact('leaves'));
    }

    public function createLeave()
    {
        if (!$this->isPost()) return $this->redirect('attendance/leaves');
        $tid = Database::tenantId();

        $dateFrom = $this->input('date_from');
        $dateTo = $this->input('date_to') ?: $dateFrom;
        $days = (strtotime($dateTo) - strtotime($dateFrom)) / 86400 + 1;

        Database::insert('leave_requests', [
            'tenant_id' => $tid,
            'user_id' => $this->userId(),
            'leave_type' => $this->input('leave_type') ?: 'annual',
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'days' => $days,
            'reason' => trim($this->input('reason') ?? '') ?: null,
        ]);

        $this->setFlash('success', 'Đã gửi đơn xin nghỉ.');
        return $this->redirect('attendance/leaves');
    }

    public function approveLeave($id)
    {
        if (!$this->isPost()) return $this->redirect('attendance/leaves');
        $tid = Database::tenantId();
        $leave = Database::fetch("SELECT * FROM leave_requests WHERE id = ? AND tenant_id = ?", [$id, $tid]);
        if (!$leave || $leave['status'] !== 'pending') {
            $this->setFlash('error', 'Không thể duyệt đơn này.');
            return $this->redirect('attendance/leaves');
        }

        $action = $this->input('action');
        $newStatus = $action === 'approve' ? 'approved' : 'rejected';

        Database::update('leave_requests', [
            'status' => $newStatus,
            'approved_by' => $this->userId(),
            'approved_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        // Update attendance records for approved leave
        if ($newStatus === 'approved') {
            $start = strtotime($leave['date_from']);
            $end = strtotime($leave['date_to']);
            for ($d = $start; $d <= $end; $d += 86400) {
                $date = date('Y-m-d', $d);
                $existing = Database::fetch("SELECT id FROM attendances WHERE tenant_id = ? AND user_id = ? AND date = ?", [$tid, $leave['user_id'], $date]);
                if ($existing) {
                    Database::update('attendances', ['status' => 'leave'], 'id = ?', [$existing['id']]);
                } else {
                    Database::insert('attendances', [
                        'tenant_id' => $tid,
                        'user_id' => $leave['user_id'],
                        'date' => $date,
                        'status' => 'leave',
                    ]);
                }
            }
            // Deduct leave balance
            Database::query("UPDATE users SET leave_balance = leave_balance - ? WHERE id = ?", [$leave['days'], $leave['user_id']]);
        }

        $this->setFlash('success', $newStatus === 'approved' ? 'Đã duyệt đơn nghỉ.' : 'Đã từ chối đơn nghỉ.');
        return $this->redirect('attendance/leaves');
    }

    // ---- Bảng lương ----
    public function payroll()
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();
        $month = (int)($this->input('month') ?: date('m'));
        $year = (int)($this->input('year') ?: date('Y'));

        $payrolls = Database::fetchAll(
            "SELECT p.*, u.name as user_name, u.email as user_email
             FROM payrolls p LEFT JOIN users u ON p.user_id = u.id
             WHERE p.tenant_id = ? AND p.month = ? AND p.year = ?
             ORDER BY u.name",
            [$tid, $month, $year]
        );

        return $this->view('attendance.payroll', compact('payrolls', 'month', 'year'));
    }

    public function generatePayroll()
    {
        if (!$this->isPost()) return $this->redirect('attendance/payroll');
        $tid = Database::tenantId();
        $month = (int)$this->input('month');
        $year = (int)$this->input('year');

        $users = Database::fetchAll("SELECT id, base_salary FROM users WHERE tenant_id = ? AND is_active = 1", [$tid]);
        $standardDays = 22;
        $generated = 0;

        foreach ($users as $u) {
            $existing = Database::fetch("SELECT id FROM payrolls WHERE tenant_id = ? AND user_id = ? AND month = ? AND year = ?", [$tid, $u['id'], $month, $year]);
            if ($existing) continue;

            $stats = Database::fetch(
                "SELECT COUNT(CASE WHEN status IN ('present','late') THEN 1 END) as work_days,
                        COUNT(CASE WHEN status = 'leave' THEN 1 END) as leave_days,
                        COALESCE(SUM(overtime_hours),0) as overtime_hours
                 FROM attendances WHERE tenant_id = ? AND user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?",
                [$tid, $u['id'], $month, $year]
            );

            $baseSalary = (float)$u['base_salary'];
            $workDays = (float)($stats['work_days'] ?? 0);
            $leaveDays = (float)($stats['leave_days'] ?? 0);
            $otHours = (float)($stats['overtime_hours'] ?? 0);
            $dailyRate = $standardDays > 0 ? $baseSalary / $standardDays : 0;
            $overtimePay = round($dailyRate / 8 * 1.5 * $otHours);
            $insurance = round($baseSalary * 0.105);
            $netSalary = round($dailyRate * $workDays + $overtimePay - $insurance);

            Database::insert('payrolls', [
                'tenant_id' => $tid,
                'user_id' => $u['id'],
                'month' => $month,
                'year' => $year,
                'work_days' => $workDays,
                'leave_days' => $leaveDays,
                'overtime_hours' => $otHours,
                'base_salary' => $baseSalary,
                'overtime_pay' => $overtimePay,
                'insurance' => $insurance,
                'net_salary' => $netSalary,
                'created_by' => $this->userId(),
            ]);
            $generated++;
        }

        $this->setFlash('success', "Đã tạo bảng lương cho $generated nhân viên.");
        return $this->redirect("attendance/payroll?month=$month&year=$year");
    }

    public function updatePayroll($id)
    {
        if (!$this->isPost()) return $this->redirect('attendance/payroll');
        $tid = Database::tenantId();
        $payroll = Database::fetch("SELECT * FROM payrolls WHERE id = ? AND tenant_id = ?", [$id, $tid]);
        if (!$payroll) {
            $this->setFlash('error', 'Không tìm thấy.');
            return $this->redirect('attendance/payroll');
        }

        $bonus = (float)$this->input('bonus');
        $deductions = (float)$this->input('deductions');
        $note = trim($this->input('note') ?? '');
        $net = $payroll['base_salary'] / 22 * $payroll['work_days'] + $payroll['overtime_pay'] + $bonus - $deductions - $payroll['insurance'] - $payroll['tax'];

        Database::update('payrolls', [
            'bonus' => $bonus,
            'deductions' => $deductions,
            'net_salary' => round($net),
            'note' => $note ?: null,
        ], 'id = ?', [$id]);

        $this->setFlash('success', 'Đã cập nhật.');
        return $this->redirect("attendance/payroll?month={$payroll['month']}&year={$payroll['year']}");
    }

    public function confirmPayroll($id)
    {
        if (!$this->isPost()) return $this->redirect('attendance/payroll');
        $tid = Database::tenantId();
        Database::update('payrolls', ['status' => 'confirmed'], 'id = ? AND tenant_id = ?', [$id, $tid]);
        $this->setFlash('success', 'Đã xác nhận.');
        return $this->back();
    }
}
