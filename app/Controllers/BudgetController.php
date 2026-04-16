<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class BudgetController extends Controller
{
    /**
     * List budgets with stats
     */
    public function index()
    {
        $tid = Database::tenantId();
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = in_array((int)$this->input('per_page'), [10,20,50,100]) ? (int)$this->input('per_page') : 20;
        $offset = ($page - 1) * $perPage;

        $status = $this->input('status');
        $type = $this->input('type');
        $search = $this->input('search');

        $where = ["b.tenant_id = ?"];
        $params = [$tid];

        if ($search) {
            $where[] = "b.name LIKE ?";
            $params[] = "%{$search}%";
        }

        if ($status) {
            $where[] = "b.status = ?";
            $params[] = $status;
        }

        if ($type) {
            $where[] = "b.type = ?";
            $params[] = $type;
        }

        $ownerScope = $this->ownerScope('b', 'created_by', 'fund');
        if ($ownerScope['where']) { $where[] = $ownerScope['where']; $params = array_merge($params, $ownerScope['params']); }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch("SELECT COUNT(*) as count FROM budgets b WHERE {$whereClause}", $params)['count'];

        $budgets = Database::fetchAll(
            "SELECT b.*, u.name as created_by_name
             FROM budgets b
             LEFT JOIN users u ON b.created_by = u.id
             WHERE {$whereClause}
             ORDER BY b.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // Enrich budgets with actual spent from budget_items
        foreach ($budgets as &$budget) {
            $spent = Database::fetch(
                "SELECT COALESCE(SUM(actual_amount), 0) as total_spent FROM budget_items WHERE budget_id = ?",
                [$budget['id']]
            );
            $budget['total_spent'] = (float)($spent['total_spent'] ?? 0);
            $budget['remaining'] = (float)$budget['total_budget'] - $budget['total_spent'];
            $budget['utilization'] = $budget['total_budget'] > 0 ? round($budget['total_spent'] / $budget['total_budget'] * 100, 1) : 0;
        }
        unset($budget);

        $totalPages = ceil($total / $perPage);

        // Summary stats
        $summary = Database::fetch(
            "SELECT
                COALESCE(SUM(total_budget), 0) as total_planned,
                COALESCE(SUM((SELECT COALESCE(SUM(actual_amount), 0) FROM budget_items WHERE budget_id = b.id)), 0) as total_spent
             FROM budgets b WHERE b.tenant_id = ?" . (!$this->isAdminOrManager() ? " AND b.created_by = " . (int)$this->userId() : ''),
            [$tid]
        );
        $summary['remaining'] = (float)$summary['total_planned'] - (float)$summary['total_spent'];
        $summary['utilization'] = $summary['total_planned'] > 0 ? round($summary['total_spent'] / $summary['total_planned'] * 100, 1) : 0;

        return $this->view('budgets.index', [
            'budgets' => [
                'items' => $budgets,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'type' => $type,
            ],
        ]);
    }

    /**
     * Create budget form
     */
    public function create()
    {
        $tid = Database::tenantId();
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.tenant_id = ? AND u.is_active = 1 ORDER BY d.name, u.name", [$tid]);

        return $this->view('budgets.create', [
            'users' => $users,
        ]);
    }

    /**
     * Store budget
     */
    public function store()
    {
        if (!$this->isPost()) return $this->redirect('budgets');

        $data = $this->allInput();
        $tid = Database::tenantId();

        Database::beginTransaction();
        try {
            $totalBudget = 0;

            $budgetId = Database::insert('budgets', [
                'name' => trim($data['name'] ?? ''),
                'type' => $data['type'] ?? 'general',
                'department' => trim($data['department'] ?? ''),
                'status' => 'draft',
                'period_start' => !empty($data['period_start']) ? $data['period_start'] : null,
                'period_end' => !empty($data['period_end']) ? $data['period_end'] : null,
                'notes' => trim($data['notes'] ?? ''),
                'total_budget' => 0,
                'created_by' => $this->userId(),
                'tenant_id' => $tid,
            ]);

            if (!empty($data['items']) && is_array($data['items'])) {
                $sort = 0;
                foreach ($data['items'] as $item) {
                    if (empty($item['category'])) continue;

                    $planned = (float)($item['planned_amount'] ?? 0);
                    $totalBudget += $planned;

                    Database::insert('budget_items', [
                        'budget_id' => $budgetId,
                        'category' => trim($item['category']),
                        'planned_amount' => $planned,
                        'actual_amount' => 0,
                        'sort_order' => $sort++,
                    ]);
                }
            }

            Database::update('budgets', [
                'total_budget' => $totalBudget,
            ], 'id = ?', [$budgetId]);

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi tạo ngân sách: ' . $e->getMessage());
            return $this->back();
        }

        Database::insert('activities', [
            'type' => 'finance',
            'title' => "Tạo ngân sách: " . trim($data['name'] ?? ''),
            'user_id' => $this->userId(),
            'tenant_id' => $tid,
        ]);

        $this->setFlash('success', 'Ngân sách đã được tạo.');
        return $this->redirect('budgets/' . $budgetId);
    }

    /**
     * Show budget detail
     */
    public function show($id)
    {
        $tid = Database::tenantId();
        $budget = Database::fetch(
            "SELECT b.*, u.name as created_by_name, ua.name as approved_by_name
             FROM budgets b
             LEFT JOIN users u ON b.created_by = u.id
             LEFT JOIN users ua ON b.approved_by = ua.id
             WHERE b.id = ? AND b.tenant_id = ?",
            [$id, $tid]
        );

        if (!$budget) {
            $this->setFlash('error', 'Ngân sách không tồn tại.');
            return $this->redirect('budgets');
        }

        $items = Database::fetchAll(
            "SELECT * FROM budget_items WHERE budget_id = ? ORDER BY sort_order",
            [$id]
        );

        // Calculate totals
        $totalSpent = 0;
        foreach ($items as &$item) {
            $item['variance'] = (float)$item['planned_amount'] - (float)$item['actual_amount'];
            $item['percent_used'] = $item['planned_amount'] > 0 ? round($item['actual_amount'] / $item['planned_amount'] * 100, 1) : 0;
            $totalSpent += (float)$item['actual_amount'];
        }
        unset($item);

        $budget['total_spent'] = $totalSpent;
        $budget['remaining'] = (float)$budget['total_budget'] - $totalSpent;
        $budget['utilization'] = $budget['total_budget'] > 0 ? round($totalSpent / $budget['total_budget'] * 100, 1) : 0;

        return $this->view('budgets.show', [
            'budget' => $budget,
            'items' => $items,
        ]);
    }

    /**
     * Edit budget form
     */
    public function edit($id)
    {
        $tid = Database::tenantId();
        $budget = Database::fetch("SELECT * FROM budgets WHERE id = ? AND tenant_id = ?", [$id, $tid]);

        if (!$budget) {
            $this->setFlash('error', 'Ngân sách không tồn tại.');
            return $this->redirect('budgets');
        }

        $items = Database::fetchAll("SELECT * FROM budget_items WHERE budget_id = ? ORDER BY sort_order", [$id]);
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.tenant_id = ? AND u.is_active = 1 ORDER BY d.name, u.name", [$tid]);

        return $this->view('budgets.edit', [
            'budget' => $budget,
            'items' => $items,
            'users' => $users,
        ]);
    }

    /**
     * Update budget
     */
    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('budgets/' . $id);

        $tid = Database::tenantId();
        $budget = Database::fetch("SELECT * FROM budgets WHERE id = ? AND tenant_id = ?", [$id, $tid]);

        if (!$budget) {
            $this->setFlash('error', 'Ngân sách không tồn tại.');
            return $this->redirect('budgets');
        }

        $data = $this->allInput();

        Database::beginTransaction();
        try {
            Database::update('budgets', [
                'name' => trim($data['name'] ?? ''),
                'type' => $data['type'] ?? 'general',
                'department' => trim($data['department'] ?? ''),
                'period_start' => !empty($data['period_start']) ? $data['period_start'] : null,
                'period_end' => !empty($data['period_end']) ? $data['period_end'] : null,
                'notes' => trim($data['notes'] ?? ''),
            ], 'id = ?', [$id]);

            // Re-insert items (preserve actual_amount from existing)
            $existingItems = Database::fetchAll("SELECT category, actual_amount FROM budget_items WHERE budget_id = ?", [$id]);
            $existingMap = [];
            foreach ($existingItems as $ei) {
                $existingMap[strtolower(trim($ei['category']))] = (float)$ei['actual_amount'];
            }

            Database::delete('budget_items', 'budget_id = ?', [$id]);

            $totalBudget = 0;
            if (!empty($data['items']) && is_array($data['items'])) {
                $sort = 0;
                foreach ($data['items'] as $item) {
                    if (empty($item['category'])) continue;

                    $planned = (float)($item['planned_amount'] ?? 0);
                    $catKey = strtolower(trim($item['category']));
                    $actual = $existingMap[$catKey] ?? (float)($item['actual_amount'] ?? 0);
                    $totalBudget += $planned;

                    Database::insert('budget_items', [
                        'budget_id' => $id,
                        'category' => trim($item['category']),
                        'planned_amount' => $planned,
                        'actual_amount' => $actual,
                        'sort_order' => $sort++,
                    ]);
                }
            }

            Database::update('budgets', [
                'total_budget' => $totalBudget,
            ], 'id = ?', [$id]);

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi cập nhật ngân sách: ' . $e->getMessage());
            return $this->back();
        }

        $this->setFlash('success', 'Ngân sách đã được cập nhật.');
        return $this->redirect('budgets/' . $id);
    }

    /**
     * Approve budget (draft -> active)
     */
    public function approve($id)
    {
        if (!$this->isPost()) return $this->redirect('budgets/' . $id);

        $budget = Database::fetch("SELECT * FROM budgets WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$budget) {
            $this->setFlash('error', 'Ngân sách không tồn tại.');
            return $this->redirect('budgets');
        }

        if (!in_array($budget['status'], ['draft'])) {
            $this->setFlash('error', 'Chỉ có thể duyệt ngân sách ở trạng thái Nháp.');
            return $this->redirect('budgets/' . $id);
        }

        Database::update('budgets', [
            'status' => 'active',
            'approved_by' => $this->userId(),
            'approved_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'finance',
            'title' => "Duyệt ngân sách: {$budget['name']}",
            'user_id' => $this->userId(),
            'tenant_id' => Database::tenantId(),
        ]);

        $this->setFlash('success', 'Ngân sách đã được duyệt và kích hoạt.');
        return $this->redirect('budgets/' . $id);
    }

    /**
     * Close budget
     */
    public function close($id)
    {
        if (!$this->isPost()) return $this->redirect('budgets/' . $id);

        $budget = Database::fetch("SELECT * FROM budgets WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$budget) {
            $this->setFlash('error', 'Ngân sách không tồn tại.');
            return $this->redirect('budgets');
        }

        if ($budget['status'] === 'closed') {
            $this->setFlash('error', 'Ngân sách đã đóng.');
            return $this->redirect('budgets/' . $id);
        }

        Database::update('budgets', [
            'status' => 'closed',
            'closed_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'finance',
            'title' => "Đóng ngân sách: {$budget['name']}",
            'user_id' => $this->userId(),
            'tenant_id' => Database::tenantId(),
        ]);

        $this->setFlash('success', 'Ngân sách đã được đóng.');
        return $this->redirect('budgets/' . $id);
    }
}
