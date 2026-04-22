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

        return $this->view('plugin:attendance-payroll.index', compact('users', 'map', 'month', 'year', 'daysInMonth'));
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
            // Check in — "late" if after configured work_start
            $workStart = tenant_setting('work_start', '08:00');
            $status = (date('H:i') > $workStart) ? 'late' : 'present';
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
        $leaveType = $this->input('leave_type');

        $where = ["lr.tenant_id = ?"];
        $params = [$tid];
        if ($status) { $where[] = "lr.status = ?"; $params[] = $status; }
        if ($leaveType) { $where[] = "lr.leave_type = ?"; $params[] = $leaveType; }

        $whereSql = implode(' AND ', $where);
        $page = max(1, (int)($this->input('page') ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $total = Database::fetch("SELECT COUNT(*) as cnt FROM leave_requests lr WHERE $whereSql", $params)['cnt'];
        $leaves = Database::fetchAll(
            "SELECT lr.*, u.name as user_name, u.leave_balance, a.name as approved_by_name
             FROM leave_requests lr
             LEFT JOIN users u ON lr.user_id = u.id
             LEFT JOIN users a ON lr.approved_by = a.id
             WHERE $whereSql ORDER BY lr.created_at DESC LIMIT $limit OFFSET $offset",
            $params
        );
        $totalPages = ceil($total / $limit);

        // Leave stats
        $leaveStats = Database::fetchAll(
            "SELECT u.id, u.name, u.leave_balance,
                    (SELECT COALESCE(SUM(days),0) FROM leave_requests WHERE user_id = u.id AND status = 'approved' AND YEAR(date_from) = YEAR(NOW())) as used
             FROM users u WHERE u.tenant_id = ? AND u.is_active = 1 ORDER BY u.name", [$tid]
        );

        $filters = compact('status', 'leaveType');
        return $this->view('plugin:attendance-payroll.leaves', compact('leaves', 'leaveStats', 'page', 'totalPages', 'total', 'filters'));
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

    // ---- Thuế TNCN lũy tiến ----
    private function calcPIT(float $taxableIncome): float
    {
        if ($taxableIncome <= 0) return 0;
        $brackets = [
            [5000000, 0.05], [10000000, 0.10], [18000000, 0.15],
            [32000000, 0.20], [52000000, 0.25], [80000000, 0.30], [PHP_INT_MAX, 0.35],
        ];
        $tax = 0; $prev = 0;
        foreach ($brackets as [$limit, $rate]) {
            if ($taxableIncome <= $prev) break;
            $taxable = min($taxableIncome, $limit) - $prev;
            $tax += $taxable * $rate;
            $prev = $limit;
        }
        return round($tax);
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

        return $this->view('plugin:attendance-payroll.payroll', compact('payrolls', 'month', 'year'));
    }

    public function payrollDetail($id)
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();
        $payroll = Database::fetch(
            "SELECT p.*, u.name as user_name, u.email as user_email
             FROM payrolls p LEFT JOIN users u ON p.user_id = u.id
             WHERE p.id = ? AND p.tenant_id = ?", [$id, $tid]
        );
        if (!$payroll) {
            $this->setFlash('error', 'Không tìm thấy.');
            return $this->redirect('attendance/payroll');
        }
        return $this->view('plugin:attendance-payroll.payroll-detail', compact('payroll'));
    }

    public function generatePayroll()
    {
        if (!$this->isPost()) return $this->redirect('attendance/payroll');
        $tid = Database::tenantId();
        $month = (int)$this->input('month');
        $year = (int)$this->input('year');
        $regenerate = $this->input('regenerate');

        // Tạo lại: xóa bảng lương draft cũ
        if ($regenerate) {
            Database::query("DELETE FROM payrolls WHERE tenant_id = ? AND month = ? AND year = ? AND status = 'draft'", [$tid, $month, $year]);
        }

        $users = Database::fetchAll(
            "SELECT id, base_salary, allowance_lunch, allowance_transport, allowance_phone, allowance_other, dependents
             FROM users WHERE tenant_id = ? AND is_active = 1", [$tid]
        );
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

            // Tạm ứng đã duyệt
            $advance = Database::fetch(
                "SELECT COALESCE(SUM(amount),0) as total FROM salary_advances WHERE tenant_id = ? AND user_id = ? AND month = ? AND year = ? AND status = 'approved'",
                [$tid, $u['id'], $month, $year]
            );

            $baseSalary = (float)($u['base_salary'] ?? 0);
            $workDays = (float)($stats['work_days'] ?? 0);
            $leaveDays = (float)($stats['leave_days'] ?? 0);
            $otHours = (float)($stats['overtime_hours'] ?? 0);
            $dependents = (int)($u['dependents'] ?? 0);
            $advanceAmount = (float)($advance['total'] ?? 0);

            // Phụ cấp
            $alLunch = (float)($u['allowance_lunch'] ?? 0);
            $alTransport = (float)($u['allowance_transport'] ?? 0);
            $alPhone = (float)($u['allowance_phone'] ?? 0);
            $alOther = (float)($u['allowance_other'] ?? 0);
            $totalAllowance = $alLunch + $alTransport + $alPhone + $alOther;

            // Tính lương
            $dailyRate = $standardDays > 0 ? $baseSalary / $standardDays : 0;
            $salaryByDay = round($dailyRate * $workDays);
            $overtimePay = round($dailyRate / 8 * 1.5 * $otHours);
            $grossSalary = $salaryByDay + $overtimePay + $totalAllowance;

            // BHXH 8%, BHYT 1.5%, BHTN 1%
            $bhxh = round($baseSalary * 0.08);
            $bhyt = round($baseSalary * 0.015);
            $bhtn = round($baseSalary * 0.01);
            $totalInsurance = $bhxh + $bhyt + $bhtn;

            // Thuế TNCN
            $deductSelf = 11000000;
            $deductDep = $dependents * 4400000;
            $taxIncome = $grossSalary - $totalInsurance - $deductSelf - $deductDep;
            $tax = $this->calcPIT($taxIncome);

            $netSalary = $grossSalary - $totalInsurance - $tax - $advanceAmount;

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
                'allowance_lunch' => $alLunch,
                'allowance_transport' => $alTransport,
                'allowance_phone' => $alPhone,
                'allowance_other' => $alOther,
                'total_allowance' => $totalAllowance,
                'gross_salary' => $grossSalary,
                'insurance' => $totalInsurance,
                'bhxh' => $bhxh,
                'bhyt' => $bhyt,
                'bhtn' => $bhtn,
                'tax_income' => max(0, $taxIncome),
                'tax' => $tax,
                'advance' => $advanceAmount,
                'dependents' => $dependents,
                'net_salary' => round($netSalary),
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
        $p = Database::fetch("SELECT * FROM payrolls WHERE id = ? AND tenant_id = ?", [$id, $tid]);
        if (!$p || $p['status'] !== 'draft') {
            $this->setFlash('error', 'Không thể sửa.');
            return $this->redirect('attendance/payroll');
        }

        $bonus = (float)$this->input('bonus');
        $deductions = (float)$this->input('deductions');
        $note = trim($this->input('note') ?? '');
        $net = $p['gross_salary'] + $bonus - $deductions - $p['insurance'] - $p['tax'] - $p['advance'];

        Database::update('payrolls', [
            'bonus' => $bonus,
            'deductions' => $deductions,
            'net_salary' => round($net),
            'note' => $note ?: null,
        ], 'id = ?', [$id]);

        $this->setFlash('success', 'Đã cập nhật.');
        return $this->redirect("attendance/payroll?month={$p['month']}&year={$p['year']}");
    }

    public function confirmPayroll($id)
    {
        if (!$this->isPost()) return $this->redirect('attendance/payroll');
        $tid = Database::tenantId();
        Database::update('payrolls', ['status' => 'confirmed'], 'id = ? AND tenant_id = ?', [$id, $tid]);
        $this->setFlash('success', 'Đã xác nhận.');
        return $this->back();
    }

    public function markPaid($id)
    {
        if (!$this->isPost()) return $this->redirect('attendance/payroll');
        $tid = Database::tenantId();
        Database::update('payrolls', ['status' => 'paid', 'paid_at' => date('Y-m-d H:i:s')], 'id = ? AND tenant_id = ? AND status = ?', [$id, $tid, 'confirmed']);
        $this->setFlash('success', 'Đã đánh dấu đã trả.');
        return $this->back();
    }

    public function bulkConfirmPayroll()
    {
        if (!$this->isPost()) return $this->redirect('attendance/payroll');
        $tid = Database::tenantId();
        $ids = $this->input('payroll_ids') ?: [];
        $action = $this->input('action');
        if (empty($ids)) { $this->setFlash('error', 'Chưa chọn.'); return $this->back(); }

        $ph = implode(',', array_fill(0, count($ids), '?'));
        if ($action === 'confirm') {
            Database::query("UPDATE payrolls SET status = 'confirmed' WHERE id IN ($ph) AND tenant_id = ? AND status = 'draft'", array_merge($ids, [$tid]));
            $this->setFlash('success', 'Đã xác nhận ' . count($ids) . ' phiếu lương.');
        } elseif ($action === 'paid') {
            Database::query("UPDATE payrolls SET status = 'paid', paid_at = NOW() WHERE id IN ($ph) AND tenant_id = ? AND status = 'confirmed'", array_merge($ids, [$tid]));
            $this->setFlash('success', 'Đã đánh dấu đã trả ' . count($ids) . ' phiếu.');
        }
        return $this->back();
    }

    public function payrollHistory($userId)
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();
        $user = Database::fetch("SELECT id, name, email, base_salary FROM users WHERE id = ? AND tenant_id = ?", [$userId, $tid]);
        if (!$user) { $this->setFlash('error', 'Không tìm thấy.'); return $this->redirect('attendance/payroll'); }

        $history = Database::fetchAll(
            "SELECT * FROM payrolls WHERE tenant_id = ? AND user_id = ? ORDER BY year DESC, month DESC",
            [$tid, $userId]
        );
        return $this->view('plugin:attendance-payroll.payroll-history', compact('user', 'history'));
    }

    public function exportPayroll()
    {
        $tid = Database::tenantId();
        $month = (int)($this->input('month') ?: date('m'));
        $year = (int)($this->input('year') ?: date('Y'));

        $payrolls = Database::fetchAll(
            "SELECT p.*, u.name as user_name FROM payrolls p LEFT JOIN users u ON p.user_id = u.id WHERE p.tenant_id = ? AND p.month = ? AND p.year = ? ORDER BY u.name",
            [$tid, $month, $year]
        );

        $fileName = "bang-luong-thang-{$month}-{$year}.csv";
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        echo "\xEF\xBB\xBF"; // BOM UTF-8

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Nhân viên','Lương CB','Ngày công','Nghỉ','OT (h)','OT Pay','PC Ăn','PC Xăng','PC ĐT','PC Khác','Tổng PC','Gross','BHXH','BHYT','BHTN','Thuế TNCN','Tạm ứng','Thưởng','Khấu trừ','Thực nhận','Trạng thái']);
        foreach ($payrolls as $p) {
            fputcsv($out, [
                $p['user_name'], $p['base_salary'], $p['work_days'], $p['leave_days'], $p['overtime_hours'], $p['overtime_pay'],
                $p['allowance_lunch'], $p['allowance_transport'], $p['allowance_phone'], $p['allowance_other'], $p['total_allowance'],
                $p['gross_salary'], $p['bhxh'], $p['bhyt'], $p['bhtn'], $p['tax'], $p['advance'],
                $p['bonus'], $p['deductions'], $p['net_salary'], $p['status'],
            ]);
        }
        fclose($out);
        exit;
    }

    // ---- Tạm ứng ----
    public function advances()
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();
        $status = $this->input('status');
        $fMonth = $this->input('month');
        $fYear = $this->input('year');

        $where = ["sa.tenant_id = ?"];
        $params = [$tid];
        if ($status) { $where[] = "sa.status = ?"; $params[] = $status; }
        if ($fMonth) { $where[] = "sa.month = ?"; $params[] = (int)$fMonth; }
        if ($fYear) { $where[] = "sa.year = ?"; $params[] = (int)$fYear; }

        $whereSql = implode(' AND ', $where);
        $page = max(1, (int)($this->input('page') ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $total = Database::fetch("SELECT COUNT(*) as cnt FROM salary_advances sa WHERE $whereSql", $params)['cnt'];
        $advances = Database::fetchAll(
            "SELECT sa.*, u.name as user_name, u.base_salary, a.name as approved_by_name
             FROM salary_advances sa LEFT JOIN users u ON sa.user_id = u.id LEFT JOIN users a ON sa.approved_by = a.id
             WHERE $whereSql ORDER BY sa.created_at DESC LIMIT $limit OFFSET $offset",
            $params
        );
        $totalPages = ceil($total / $limit);
        $filters = compact('status', 'fMonth', 'fYear');

        return $this->view('plugin:attendance-payroll.advances', compact('advances', 'page', 'totalPages', 'total', 'filters'));
    }

    public function createAdvance()
    {
        if (!$this->isPost()) return $this->redirect('attendance/advances');
        $tid = Database::tenantId();
        Database::insert('salary_advances', [
            'tenant_id' => $tid,
            'user_id' => $this->userId(),
            'amount' => (float)$this->input('amount'),
            'month' => (int)($this->input('month') ?: date('m')),
            'year' => (int)($this->input('year') ?: date('Y')),
            'reason' => trim($this->input('reason') ?? '') ?: null,
        ]);
        $this->setFlash('success', 'Đã gửi yêu cầu tạm ứng.');
        return $this->redirect('attendance/advances');
    }

    public function approveAdvance($id)
    {
        if (!$this->isPost()) return $this->redirect('attendance/advances');
        $tid = Database::tenantId();
        $adv = Database::fetch("SELECT * FROM salary_advances WHERE id = ? AND tenant_id = ?", [$id, $tid]);
        if (!$adv || $adv['status'] !== 'pending') {
            $this->setFlash('error', 'Không thể duyệt.');
            return $this->redirect('attendance/advances');
        }
        $action = $this->input('action');
        Database::update('salary_advances', [
            'status' => $action === 'approve' ? 'approved' : 'rejected',
            'approved_by' => $this->userId(),
            'approved_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
        $this->setFlash('success', $action === 'approve' ? 'Đã duyệt tạm ứng.' : 'Đã từ chối.');
        return $this->redirect('attendance/advances');
    }
}
