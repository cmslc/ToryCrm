<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Helpers\Auth;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('users', 'view');
        $tid = $this->tenantId();
        $search = $this->input('search');
        $role = $this->input('role');
        $status = $this->input('status');
        $dept = $this->input('department');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $where = ["u.tenant_id = ?"];
        $params = [$tid];

        if ($search) {
            $where[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s]);
        }
        if ($role) { $where[] = "u.role = ?"; $params[] = $role; }
        if ($status !== null && $status !== '') { $where[] = "u.is_active = ?"; $params[] = (int)$status; }
        if ($dept) { $where[] = "u.department_id = ?"; $params[] = (int)$dept; }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch("SELECT COUNT(*) as count FROM users u WHERE {$whereClause}", $params)['count'];
        $users = Database::fetchAll(
            "SELECT u.*, d.name as dept_name, p.name as position_name FROM users u LEFT JOIN departments d ON u.department_id = d.id LEFT JOIN positions p ON u.position_id = p.id WHERE {$whereClause} ORDER BY u.created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        $totalPages = ceil($total / $perPage);

        $departments = Database::fetchAll("SELECT id, name FROM departments WHERE tenant_id = ? ORDER BY name", [$tid]);

        // User-group mappings
        $userGroupMap = [];
        $ugRows = Database::fetchAll(
            "SELECT upg.user_id, pg.name, pg.color FROM user_permission_groups upg JOIN permission_groups pg ON upg.group_id = pg.id WHERE pg.tenant_id = ?",
            [$tid]
        );
        foreach ($ugRows as $row) { $userGroupMap[$row['user_id']][] = $row; }

        return $this->view('users.index', [
            'users' => [
                'items' => $users,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'departments' => $departments,
            'userGroupMap' => $userGroupMap,
            'filters' => [
                'search' => $search,
                'role' => $role,
                'status' => $status,
                'department' => $dept,
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('users', 'create');
        $permGroups = Database::fetchAll("SELECT * FROM permission_groups WHERE tenant_id = ? ORDER BY sort_order, name", [Database::tenantId()]);
        $positions = Database::fetchAll("SELECT * FROM positions WHERE tenant_id = ? AND is_active = 1 ORDER BY sort_order", [Database::tenantId()]);
        $departments = Database::fetchAll("SELECT id, name FROM departments WHERE tenant_id = ? ORDER BY name", [Database::tenantId()]);
        $old = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);
        return $this->view('users.create', ['permGroups' => $permGroups, 'positions' => $positions, 'departments' => $departments, 'old' => $old]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('users');
        $this->authorize('users', 'create');

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        // Save old input for form repopulation
        $_SESSION['old_input'] = $data;

        if (empty($name) || empty($email) || empty($password)) {
            $this->setFlash('error', 'Vui lòng nhập đầy đủ thông tin.');
            return $this->back();
        }

        if (strlen($password) < 6) {
            $this->setFlash('error', 'Mật khẩu tối thiểu 6 ký tự.');
            return $this->back();
        }

        $exists = Database::fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($exists) {
            $this->setFlash('error', 'Email đã tồn tại.');
            return $this->back();
        }

        $userId = Database::insert('users', [
            'name' => $name,
            'email' => $email,
            'password' => Auth::hashPassword($password),
            'phone' => trim($data['phone'] ?? ''),
            'role' => $data['role'] ?? 'staff',
            'position_id' => !empty($data['position_id']) ? (int)$data['position_id'] : null,
            'department_id' => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'department' => trim($data['department'] ?? ''),
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ]);

        // Avatar upload
        $avatar = $_FILES['avatar'] ?? null;
        if ($avatar && $avatar['error'] === UPLOAD_ERR_OK && $avatar['size'] > 0) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($avatar['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $avatar['size'] <= 5 * 1024 * 1024) {
                $uploadDir = BASE_PATH . '/public/uploads/avatars';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $fileName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($avatar['tmp_name'], $uploadDir . '/' . $fileName)) {
                    Database::update('users', ['avatar' => 'uploads/avatars/' . $fileName], 'id = ?', [$userId]);
                }
            }
        }

        // Assign permission groups
        $groupIds = $data['permission_groups'] ?? [];
        if (is_array($groupIds)) {
            foreach ($groupIds as $gid) {
                Database::query("INSERT IGNORE INTO user_permission_groups (user_id, group_id) VALUES (?, ?)", [$userId, (int)$gid]);
            }
        }

        unset($_SESSION['old_input']);
        $this->setFlash('success', 'Tạo người dùng thành công.');
        return $this->redirect('users');
    }

    public function edit($id)
    {
        $this->authorize('users', 'edit');
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            $this->setFlash('error', 'Người dùng không tồn tại.');
            return $this->redirect('users');
        }

        $permGroups = Database::fetchAll("SELECT * FROM permission_groups WHERE tenant_id = ? ORDER BY sort_order, name", [Database::tenantId()]);
        $userGroupIds = array_column(Database::fetchAll("SELECT group_id FROM user_permission_groups WHERE user_id = ?", [$id]), 'group_id');
        $positions = Database::fetchAll("SELECT * FROM positions WHERE tenant_id = ? AND is_active = 1 ORDER BY sort_order", [Database::tenantId()]);
        $departments = Database::fetchAll("SELECT id, name FROM departments WHERE tenant_id = ? ORDER BY name", [Database::tenantId()]);

        return $this->view('users.edit', ['editUser' => $user, 'permGroups' => $permGroups, 'userGroupIds' => $userGroupIds, 'positions' => $positions, 'departments' => $departments]);
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('users');
        $this->authorize('users', 'edit');

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            $this->setFlash('error', 'Người dùng không tồn tại.');
            return $this->redirect('users');
        }

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');

        if (empty($name) || empty($email)) {
            $this->setFlash('error', 'Vui lòng nhập đầy đủ thông tin.');
            return $this->back();
        }

        $emailExists = Database::fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $id]);
        if ($emailExists) {
            $this->setFlash('error', 'Email đã được sử dụng.');
            return $this->back();
        }

        $updateData = [
            'name' => $name,
            'email' => $email,
            'phone' => trim($data['phone'] ?? ''),
            'address' => trim($data['address'] ?? '') ?: null,
            'date_of_birth' => $data['date_of_birth'] ?? null ?: null,
            'gender' => $data['gender'] ?? null ?: null,
            'id_number' => trim($data['id_number'] ?? '') ?: null,
            'id_issued_date' => $data['id_issued_date'] ?? null ?: null,
            'id_issued_place' => trim($data['id_issued_place'] ?? '') ?: null,
            'bank_name' => trim($data['bank_name'] ?? '') ?: null,
            'bank_account' => trim($data['bank_account'] ?? '') ?: null,
            'bank_branch' => trim($data['bank_branch'] ?? '') ?: null,
            'join_date' => $data['join_date'] ?? null ?: null,
            'emergency_contact' => trim($data['emergency_contact'] ?? '') ?: null,
            'emergency_phone' => trim($data['emergency_phone'] ?? '') ?: null,
            'role' => $data['role'] ?? $user['role'],
            'position_id' => !empty($data['position_id']) ? (int)$data['position_id'] : null,
            'department_id' => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'department' => trim($data['department'] ?? ''),
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ];

        // Update password if provided
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                $this->setFlash('error', 'Mật khẩu tối thiểu 6 ký tự.');
                return $this->back();
            }
            $updateData['password'] = Auth::hashPassword($data['password']);
        }

        // Avatar upload
        $avatar = $_FILES['avatar'] ?? null;
        if ($avatar && $avatar['error'] === UPLOAD_ERR_OK && $avatar['size'] > 0) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($avatar['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $avatar['size'] <= 5 * 1024 * 1024) {
                $uploadDir = BASE_PATH . '/public/uploads/avatars';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $fileName = 'avatar_' . $id . '_' . time() . '.' . $ext;
                if (move_uploaded_file($avatar['tmp_name'], $uploadDir . '/' . $fileName)) {
                    // Delete old avatar
                    if (!empty($user['avatar'])) {
                        $oldPath = BASE_PATH . '/public/' . $user['avatar'];
                        if (file_exists($oldPath)) unlink($oldPath);
                    }
                    $updateData['avatar'] = 'uploads/avatars/' . $fileName;
                }
            }
        }

        // Salary fields (if attendance-payroll plugin)
        if (isset($data['base_salary'])) {
            $updateData['base_salary'] = (float)($data['base_salary'] ?? 0);
            $updateData['allowance_lunch'] = (float)($data['allowance_lunch'] ?? 0);
            $updateData['allowance_transport'] = (float)($data['allowance_transport'] ?? 0);
            $updateData['allowance_phone'] = (float)($data['allowance_phone'] ?? 0);
            $updateData['allowance_other'] = (float)($data['allowance_other'] ?? 0);
            $updateData['dependents'] = (int)($data['dependents'] ?? 0);
            $updateData['leave_balance'] = (float)($data['leave_balance'] ?? 12);
        }

        Database::update('users', $updateData, 'id = ?', [$id]);

        // Update permission groups
        $groupIds = $data['permission_groups'] ?? [];
        if (is_array($groupIds)) {
            Database::query("DELETE FROM user_permission_groups WHERE user_id = ?", [$id]);
            foreach ($groupIds as $gid) {
                Database::query("INSERT IGNORE INTO user_permission_groups (user_id, group_id) VALUES (?, ?)", [$id, (int)$gid]);
            }
            \App\Services\PermissionService::clearCache();
        }

        $this->setFlash('success', 'Cập nhật người dùng thành công.');
        return $this->redirect('users');
    }

    public function toggleActive($id)
    {
        if (!$this->isPost()) return $this->redirect('users');
        $this->authorize('users', 'edit');

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            $this->setFlash('error', 'Người dùng không tồn tại.');
            return $this->redirect('users');
        }

        if ($user['id'] === $this->userId()) {
            $this->setFlash('error', 'Không thể khóa tài khoản của bạn.');
            return $this->redirect('users');
        }

        Database::update('users', [
            'is_active' => $user['is_active'] ? 0 : 1,
        ], 'id = ?', [$id]);

        $status = $user['is_active'] ? 'khóa' : 'kích hoạt';
        $this->setFlash('success', "Đã {$status} tài khoản {$user['name']}.");
        return $this->redirect('users');
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('users');
        $this->authorize('users', 'delete');

        if ((int)$id === $this->userId()) {
            $this->setFlash('error', 'Không thể xóa tài khoản của bạn.');
            return $this->redirect('users');
        }

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            $this->setFlash('error', 'Người dùng không tồn tại.');
            return $this->redirect('users');
        }

        // Remove from permission groups
        Database::query("DELETE FROM user_permission_groups WHERE user_id = ?", [$id]);
        // Delete user
        Database::query("DELETE FROM users WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);

        $this->setFlash('success', "Đã xóa người dùng {$user['name']}.");
        return $this->redirect('users');
    }

    public function quickView($id)
    {
        $user = Database::fetch(
            "SELECT u.*, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.id = ?", [$id]
        );
        if (!$user) { echo '<div class="text-center py-4 text-muted">Không tìm thấy</div>'; return; }

        // Render partial without layout
        $viewUser = $user;
        extract(['viewUser' => $user]);
        include BASE_PATH . '/resources/views/users/quick-view.php';
    }

    public function resetPassword($id)
    {
        if (!$this->isPost()) return $this->redirect('users');
        $this->authorize('users', 'edit');
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) { $this->setFlash('error', 'Không tìm thấy.'); return $this->redirect('users'); }

        Database::update('users', ['password' => Auth::hashPassword('123456')], 'id = ?', [$id]);
        $this->setFlash('success', 'Đã reset mật khẩu ' . $user['name'] . ' về 123456.');
        return $this->redirect('users');
    }

    public function exportUsers()
    {
        $this->authorize('users', 'view');
        $tid = $this->tenantId();
        $users = Database::fetchAll(
            "SELECT u.*, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.tenant_id = ? ORDER BY u.name", [$tid]
        );

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="nhan-vien-' . date('Y-m-d') . '.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Họ tên','Email','SĐT','Phòng ban','Vai trò','Trạng thái','Ngày sinh','CCCD','Ngân hàng','STK','Lương CB','Ngày vào làm']);
        $rl = ['admin'=>'Admin','manager'=>'Manager','staff'=>'Staff'];
        foreach ($users as $u) {
            fputcsv($out, [
                $u['name'], $u['email'], $u['phone'] ?? '', $u['dept_name'] ?? $u['department'] ?? '',
                $rl[$u['role']] ?? $u['role'], $u['is_active'] ? 'Hoạt động' : 'Bị khóa',
                $u['date_of_birth'] ?? '', $u['id_number'] ?? '', $u['bank_name'] ?? '', $u['bank_account'] ?? '',
                $u['base_salary'] ?? 0, $u['join_date'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    public function bulkAction()
    {
        if (!$this->isPost()) return $this->redirect('users');
        $this->authorize('users', 'edit');
        $ids = $this->input('user_ids') ?: [];
        $action = $this->input('action');
        if (empty($ids)) { $this->setFlash('error', 'Chưa chọn người dùng.'); return $this->redirect('users'); }

        $myId = $this->userId();
        $ids = array_filter($ids, function($id) use ($myId) { return (int)$id !== $myId; });
        if (empty($ids)) { $this->setFlash('error', 'Không thể thao tác trên tài khoản của bạn.'); return $this->redirect('users'); }

        $ph = implode(',', array_fill(0, count($ids), '?'));

        if ($action === 'activate') {
            Database::query("UPDATE users SET is_active = 1 WHERE id IN ($ph)", $ids);
            $this->setFlash('success', 'Đã mở khóa ' . count($ids) . ' tài khoản.');
        } elseif ($action === 'deactivate') {
            Database::query("UPDATE users SET is_active = 0 WHERE id IN ($ph)", $ids);
            $this->setFlash('success', 'Đã khóa ' . count($ids) . ' tài khoản.');
        } elseif ($action === 'move_dept') {
            $deptId = (int)$this->input('move_dept');
            if ($deptId) {
                Database::query("UPDATE users SET department_id = ? WHERE id IN ($ph)", array_merge([$deptId], $ids));
                $this->setFlash('success', 'Đã chuyển ' . count($ids) . ' nhân viên.');
            }
        }

        return $this->redirect('users');
    }
}
