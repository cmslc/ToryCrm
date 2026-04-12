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
        $search = $this->input('search');
        $role = $this->input('role');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $where = ["1=1"];
        $params = [];

        if ($search) {
            $where[] = "(name LIKE ? OR email LIKE ?)";
            $s = "%{$search}%";
            $params[] = $s;
            $params[] = $s;
        }

        if ($role) {
            $where[] = "role = ?";
            $params[] = $role;
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch("SELECT COUNT(*) as count FROM users WHERE {$whereClause}", $params)['count'];
        $users = Database::fetchAll(
            "SELECT * FROM users WHERE {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        $totalPages = ceil($total / $perPage);

        return $this->view('users.index', [
            'users' => [
                'items' => $users,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'filters' => [
                'search' => $search,
                'role' => $role,
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('users', 'create');
        return $this->view('users.create');
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('users');
        $this->authorize('users', 'create');

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

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

        Database::insert('users', [
            'name' => $name,
            'email' => $email,
            'password' => Auth::hashPassword($password),
            'phone' => trim($data['phone'] ?? ''),
            'role' => $data['role'] ?? 'staff',
            'department' => trim($data['department'] ?? ''),
            'is_active' => 1,
        ]);

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

        return $this->view('users.edit', ['editUser' => $user]);
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
}
