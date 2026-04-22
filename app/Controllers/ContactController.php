<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class ContactController extends Controller
{
    public function index()
    {
        $this->authorize('contacts', 'view');
        $search = $this->input('search');
        $status = $this->input('status');
        $sourceId = $this->input('source_id');
        $ownerId = $this->input('owner_id');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = in_array((int)$this->input('per_page'), [10,20,50,100]) ? (int)$this->input('per_page') : 20;
        $offset = ($page - 1) * $perPage;

        $where = ["c.is_deleted = 0", "c.tenant_id = ?"];
        $params = [Database::tenantId()];

        if ($search) {
            $where[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR c.company_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ? OR c.mobile LIKE ? OR c.account_code LIKE ? OR c.id IN (SELECT contact_id FROM contact_persons WHERE full_name LIKE ?))";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if ($status === 'today') {
            $where[] = "DATE(c.created_at) = CURDATE()";
        } elseif ($status) {
            $where[] = "c.status = ?";
            $params[] = $status;
        }

        if ($sourceId) {
            $where[] = "c.source_id = ?";
            $params[] = $sourceId;
        }

        if ($ownerId) {
            $where[] = "c.owner_id = ?";
            $params[] = $ownerId;
        }

        $customerGroup = $this->input('customer_group');
        if ($customerGroup) {
            $where[] = "c.customer_group = ?";
            $params[] = $customerGroup;
        }

        // Owner-based data scoping: staff only sees own records
        $ownerScope = $this->ownerScope('c', 'owner_id', 'contacts');
        if ($ownerScope['where']) {
            $where[] = $ownerScope['where'];
            $params = array_merge($params, $ownerScope['params']);
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count FROM contacts c WHERE {$whereClause}",
            $params
        )['count'];

        $contacts = Database::fetchAll(
            "SELECT c.*,
                    u.name as owner_name, u.avatar as owner_avatar,
                    cs.name as source_name, cs.color as source_color,
                    c.last_activity_at,
                    cp.full_name as primary_contact_name, cp.phone as primary_contact_phone, cp.title as primary_contact_title
             FROM contacts c
             LEFT JOIN users u ON c.owner_id = u.id
             LEFT JOIN contact_sources cs ON c.source_id = cs.id
             LEFT JOIN contact_persons cp ON cp.contact_id = c.id AND cp.is_primary = 1
             WHERE {$whereClause}
             ORDER BY c.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $sources = Database::fetchAll("SELECT * FROM contact_sources ORDER BY sort_order, name");
        $users = Database::fetchAll("SELECT u.id, u.name, u.avatar, u.role, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");
        $statusCountsWhere = "is_deleted = 0 AND tenant_id = ?";
        $statusCountsParams = [Database::tenantId()];
        $statusCounts = Database::fetchAll("SELECT status, COUNT(*) as count FROM contacts WHERE {$statusCountsWhere}" . $this->getOwnerScopeSql('owner_id', 'contacts') . " GROUP BY status", $statusCountsParams);

        $totalPages = ceil($total / $perPage);

        $contactStatuses = Database::fetchAll(
            "SELECT * FROM contact_statuses WHERE tenant_id = ? AND (is_active = 1 OR is_active IS NULL) ORDER BY sort_order",
            [Database::tenantId()]
        );

        $todayCount = Database::fetch(
            "SELECT COUNT(*) as count FROM contacts WHERE is_deleted = 0 AND tenant_id = ? AND DATE(created_at) = CURDATE()" . $this->getOwnerScopeSql('owner_id', 'contacts'),
            [Database::tenantId()]
        )['count'];

        $displayColumns = \App\Services\ColumnService::getColumns('contacts');

        return $this->view('contacts.index', [
            'contacts' => [
                'items' => $contacts,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'sources' => $sources,
            'users' => $users,
            'statusCounts' => $statusCounts,
            'contactStatuses' => $contactStatuses,
            'todayCount' => $todayCount,
            'displayColumns' => $displayColumns,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'source_id' => $sourceId,
                'owner_id' => $ownerId,
                'customer_group' => $customerGroup,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('contacts', 'create');
        $sources = Database::fetchAll("SELECT * FROM contact_sources ORDER BY sort_order, name");
        $users = Database::fetchAll("SELECT u.id, u.name, u.avatar, u.role, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");
        $contactStatuses = Database::fetchAll("SELECT * FROM contact_statuses WHERE tenant_id = ? AND (is_active = 1 OR is_active IS NULL) ORDER BY sort_order", [Database::tenantId()]);
        $industries = Database::fetchAll("SELECT DISTINCT industry FROM contacts WHERE industry IS NOT NULL AND industry != '' ORDER BY industry");

        return $this->view('contacts.create', [
            'sources' => $sources,
            'users' => $users,
            'contactStatuses' => $contactStatuses,
            'industries' => $industries,
        ]);
    }

    /**
     * Find existing company by MST or name, or create new one.
     */
    private function findOrCreateCompany(array $contactData): ?int
    {
        $tid = Database::tenantId();

        // Only create company if has valid MST (10 or 13 digits, not a phone number)
        $taxCode = $contactData['tax_code'] ?? '';
        if (empty($taxCode) || !preg_match('/^\d{10}(\d{3})?$/', $taxCode) || preg_match('/^0(9|3|7|8|5)\d{8}$/', $taxCode)) {
            return null;
        }

        // Try find by tax_code
        $existing = Database::fetch("SELECT id FROM companies WHERE tax_code = ? AND is_deleted = 0 AND tenant_id = ?", [$contactData['tax_code'], $tid]);
        if ($existing) return (int)$existing['id'];

        // Try find by exact company name
        if (!empty($contactData['company_name'])) {
            $existing = Database::fetch("SELECT id FROM companies WHERE name = ? AND is_deleted = 0 AND tenant_id = ?", [$contactData['company_name'], $tid]);
            if ($existing) return (int)$existing['id'];
        }

        // Create new company (only with MST)
        return Database::insert('companies', [
            'tenant_id' => $tid,
            'name' => $contactData['company_name'],
            'phone' => $contactData['company_phone'] ?? $contactData['phone'] ?? null,
            'email' => $contactData['company_email'] ?? $contactData['email'] ?? null,
            'address' => $contactData['address'] ?? null,
            'city' => $contactData['city'] ?? null,
            'province' => $contactData['province'] ?? null,
            'district' => $contactData['district'] ?? null,
            'country' => $contactData['country'] ?? null,
            'tax_code' => $contactData['tax_code'] ?? null,
            'website' => $contactData['website'] ?? null,
            'fax' => $contactData['fax'] ?? null,
            'industry' => $contactData['industry'] ?? null,
            'company_size' => $contactData['company_size'] ?? null,
            'owner_id' => $contactData['owner_id'] ?? $this->userId(),
            'created_by' => $this->userId(),
        ]);
    }

    private function buildContactData(array $data): array
    {
        // Derive first_name/last_name/full_name from input
        $companyName = trim($data['company_name'] ?? '');
        if (!empty($data['full_name'])) {
            $fullName = trim($data['full_name']);
            $parts = explode(' ', $fullName, 2);
            $firstName = $parts[0];
            $lastName = $parts[1] ?? '';
        } elseif (!empty($data['first_name'])) {
            $firstName = trim($data['first_name']);
            $lastName = trim($data['last_name'] ?? '');
            $fullName = trim($firstName . ' ' . $lastName);
        } else {
            // Use company_name as display name
            $parts = explode(' ', $companyName, 2);
            $firstName = $parts[0];
            $lastName = $parts[1] ?? '';
            $fullName = $companyName;
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => $fullName ?: null,
            'company_name' => trim($data['company_name'] ?? '') ?: null,
            'company_phone' => trim($data['company_phone'] ?? '') ?: null,
            'company_email' => trim($data['company_email'] ?? '') ?: null,
            'industry' => trim($data['industry'] ?? '') ?: null,
            'company_size' => trim($data['company_size'] ?? '') ?: null,
            'title' => trim($data['title'] ?? '') ?: null,
            'email' => trim($data['email'] ?? ''),
            'phone' => trim($data['phone'] ?? ''),
            'mobile' => trim($data['mobile'] ?? ''),
            'fax' => trim($data['fax'] ?? '') ?: null,
            'position' => trim($data['position'] ?? ''),
            'source_id' => (!empty($data['source_id']) ? $data['source_id'] : null),
            'account_code' => trim($data['account_code'] ?? '') ?: null,
            'tax_code' => trim($data['tax_code'] ?? '') ?: null,
            'website' => trim($data['website'] ?? '') ?: null,
            'address' => trim($data['address'] ?? ''),
            'city' => trim($data['city'] ?? ''),
            'province' => trim($data['province'] ?? '') ?: null,
            'district' => trim($data['district'] ?? '') ?: null,
            'ward' => trim($data['ward'] ?? '') ?: null,
            'country' => trim($data['country'] ?? '') ?: 'Việt Nam',
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?? 'new',
            'customer_group' => $data['customer_group'] ?? null ?: null,
            'referrer_code' => trim($data['referrer_code'] ?? '') ?: null,
            'gender' => $data['gender'] ?? null ?: null,
            'date_of_birth' => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
            'owner_id' => (!empty($data['owner_id']) ? $data['owner_id'] : null),
        ];
    }

    /**
     * @return array list of blocked entries (each: [name, phone, email, existing_person_id])
     *               that the user couldn't add because the phone/email belongs to a
     *               person they lack access to. Caller can surface these + offer merge request.
     */
    private function saveContactPersons(int $contactId, array $data): array
    {
        $names = $data['cp_name'] ?? [];
        if (empty($names)) return [];

        // Delete existing and re-insert
        Database::query("DELETE FROM contact_persons WHERE contact_id = ?", [$contactId]);

        $titles = $data['cp_title'] ?? [];
        $positions = $data['cp_position'] ?? [];
        $phones = $data['cp_phone'] ?? [];
        $emails = $data['cp_email'] ?? [];
        $genders = $data['cp_gender'] ?? [];
        $dobs = $data['cp_dob'] ?? [];
        $notes = $data['cp_note'] ?? [];
        $primaries = $data['cp_primary'] ?? [];
        $personIds = $data['cp_person_id'] ?? [];
        $startDates = $data['cp_start_date'] ?? [];
        $endDates = $data['cp_end_date'] ?? [];
        $activeMap = $data['cp_active'] ?? []; // keyed by $idx
        $tid = Database::tenantId();
        $blocked = [];

        foreach ($names as $i => $name) {
            $name = trim($name);
            if (empty($name)) continue;

            $phone = trim($phones[$i] ?? '') ?: null;
            $email = trim($emails[$i] ?? '') ?: null;
            $gender = ($genders[$i] ?? '') ?: null;
            $dob = !empty($dobs[$i]) ? $dobs[$i] : null;
            $note = trim($notes[$i] ?? '') ?: null;

            // Resolve person_id: use provided (user picked existing) or lookup/create
            $personId = (int)($personIds[$i] ?? 0);
            if ($personId > 0) {
                $exists = Database::fetch("SELECT id FROM persons WHERE id = ? AND tenant_id = ?", [$personId, $tid]);
                if (!$exists) $personId = 0;
            }

            // STRICT RULE: if phone/email matches existing person and user has no
            // access to any of that person's employments → BLOCK. Caller gets the
            // option to send a merge request through the approval workflow.
            if ($personId === 0 && ($phone || $email)) {
                $match = null;
                if ($phone) {
                    $match = Database::fetch("SELECT id FROM persons WHERE tenant_id = ? AND phone = ? LIMIT 1", [$tid, $phone]);
                }
                if (!$match && $email) {
                    $match = Database::fetch("SELECT id FROM persons WHERE tenant_id = ? AND email = ? LIMIT 1", [$tid, $email]);
                }
                if ($match) {
                    $matchedPid = (int)$match['id'];
                    $hasAccess = false;
                    $owners = Database::fetchAll(
                        "SELECT DISTINCT c.owner_id FROM contact_persons cp
                         JOIN contacts c ON c.id = cp.contact_id
                         WHERE cp.person_id = ? AND cp.tenant_id = ?",
                        [$matchedPid, $tid]
                    );
                    foreach ($owners as $o) {
                        if ($this->canAccessOwner((int)$o['owner_id'], 'contacts')) { $hasAccess = true; break; }
                    }
                    if (!$hasAccess) {
                        $blocked[] = [
                            'name' => $name,
                            'phone' => $phone,
                            'email' => $email,
                            'position' => trim($positions[$i] ?? '') ?: null,
                            'title' => trim($titles[$i] ?? '') ?: null,
                            'existing_person_id' => $matchedPid,
                        ];
                        continue; // skip this row
                    }
                    // Has access → reuse
                    $personId = $matchedPid;
                }
            }

            if ($personId === 0) {
                $personId = \App\Services\PersonService::findOrCreate($tid, $phone, $email, $name, [
                    'gender' => $gender,
                    'date_of_birth' => $dob,
                    'note' => $note,
                ]);
            }

            $isActive = array_key_exists($i, $activeMap) ? (int)$activeMap[$i] : 1;
            Database::insert('contact_persons', [
                'tenant_id' => $tid,
                'contact_id' => $contactId,
                'person_id' => $personId,
                'title' => trim($titles[$i] ?? '') ?: null,
                'full_name' => $name,
                'position' => trim($positions[$i] ?? '') ?: null,
                'phone' => $phone,
                'email' => $email,
                'gender' => $gender,
                'date_of_birth' => $dob,
                'note' => $note,
                'is_primary' => in_array($i, $primaries) ? 1 : 0,
                'is_active' => $isActive,
                'start_date' => !empty($startDates[$i]) ? $startDates[$i] : null,
                'end_date' => !empty($endDates[$i]) ? $endDates[$i] : null,
                'sort_order' => $i,
            ]);
        }
        return $blocked;
    }

    private function handleAvatarUpload(int $contactId, ?string $oldAvatar = null): void
    {
        $avatar = $_FILES['avatar'] ?? null;
        if (!$avatar || $avatar['error'] !== UPLOAD_ERR_OK || $avatar['size'] <= 0) return;

        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($avatar['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed) || $avatar['size'] > 5 * 1024 * 1024) return;

        $uploadDir = BASE_PATH . '/public/uploads/avatars';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        if ($oldAvatar) {
            $oldFile = $uploadDir . '/' . $oldAvatar;
            if (file_exists($oldFile)) unlink($oldFile);
        }

        $fileName = 'contact_' . $contactId . '_' . time() . '.' . $ext;
        if (move_uploaded_file($avatar['tmp_name'], $uploadDir . '/' . $fileName)) {
            Database::update('contacts', ['avatar' => $fileName], 'id = ?', [$contactId]);
        }
    }

    /**
     * AJAX check duplicate contact by MST, phone, email.
     */
    public function checkDuplicate()
    {
        $field = $this->input('field');
        $value = trim($this->input('value') ?? '');
        $excludeId = (int)($this->input('exclude_id') ?? 0);

        if (empty($value) || !in_array($field, ['tax_code', 'phone', 'email', 'company_name'])) {
            return $this->json(['found' => false]);
        }

        $tid = Database::tenantId();

        // For phone/email, also check persons table (Phase 3 person reuse).
        // If a person with matching phone/email exists, surface as "found" so UI
        // doesn't mislead user into thinking it's safe to create new.
        if (in_array($field, ['phone', 'email'])) {
            $person = Database::fetch(
                "SELECT id, full_name, phone, email FROM persons WHERE tenant_id = ? AND {$field} = ? LIMIT 1",
                [$tid, $value]
            );
            if ($person) {
                return $this->json([
                    'found' => true,
                    'can_see' => true,
                    'is_person' => true,
                    'id' => $person['id'],
                    'name' => $person['full_name'],
                    'phone' => $person['phone'],
                    'email' => $person['email'],
                    'message' => 'Người liên hệ này đã có trong hệ thống. Hãy chọn từ gợi ý ở ô phía trên thay vì tạo mới.',
                ]);
            }
        }

        $where = "c.tenant_id = ? AND c.is_deleted = 0 AND c.{$field} = ?";
        $params = [$tid, $value];

        if ($excludeId) {
            $where .= " AND c.id != ?";
            $params[] = $excludeId;
        }

        $existing = Database::fetch(
            "SELECT c.id, c.first_name, c.last_name, c.company_name, c.phone, c.tax_code, c.account_code, c.owner_id, u.name as owner_name, u.department_id as owner_dept_id
             FROM contacts c
             LEFT JOIN users u ON c.owner_id = u.id
             WHERE {$where} LIMIT 1",
            $params
        );

        if ($existing) {
            $name = $existing['company_name'] ?: trim($existing['first_name'] . ' ' . ($existing['last_name'] ?? ''));

            // Check if current user can see this contact's details
            $canSeeDetails = false;
            if ($this->isSystemAdmin()) {
                $canSeeDetails = true;
            } elseif ($existing['owner_id'] == $this->userId()) {
                $canSeeDetails = true;
            } else {
                $visibleIds = $this->getVisibleUserIds();
                if ($visibleIds && in_array($existing['owner_id'], $visibleIds)) {
                    $canSeeDetails = true;
                }
            }

            if ($canSeeDetails) {
                return $this->json([
                    'found' => true,
                    'can_see' => true,
                    'id' => $existing['id'],
                    'name' => $name,
                    'account_code' => $existing['account_code'],
                    'phone' => $existing['phone'],
                    'tax_code' => $existing['tax_code'],
                    'owner_name' => $existing['owner_name'],
                ]);
            } else {
                return $this->json([
                    'found' => true,
                    'can_see' => false,
                    'existing_id' => $existing['id'],
                    'owner_name' => $existing['owner_name'],
                ]);
            }
        }

        return $this->json(['found' => false]);
    }

    /**
     * Check if a phone exists in contact_persons of a specific contact.
     */
    public function checkPersonPhone()
    {
        $contactId = (int)($this->input('contact_id') ?? 0);
        $phone = trim($this->input('phone') ?? '');

        if (!$contactId || empty($phone)) return $this->json(['exists' => false]);

        $exists = Database::fetch(
            "SELECT id FROM contact_persons WHERE contact_id = ? AND phone = ?",
            [$contactId, $phone]
        );

        // Also check main contact phone
        if (!$exists) {
            $exists = Database::fetch(
                "SELECT id FROM contacts WHERE id = ? AND phone = ?",
                [$contactId, $phone]
            );
        }

        return $this->json(['exists' => (bool)$exists]);
    }

    public function store()
    {
        if (!$this->isPost()) {
            return $this->redirect('contacts');
        }
        $this->authorize('contacts', 'create');

        $data = $this->allInput();
        $contactData = $this->buildContactData($data);

        if (empty($contactData['company_name']) && empty($contactData['first_name'])) {
            $this->setFlash('error', 'Tên khách hàng không được để trống.');
            return $this->back();
        }

        // Server-side duplicate check
        $tid = Database::tenantId();
        if (!empty($contactData['tax_code'])) {
            $dup = Database::fetch("SELECT id, company_name FROM contacts WHERE tenant_id = ? AND is_deleted = 0 AND tax_code = ?", [$tid, $contactData['tax_code']]);
            if ($dup && empty($data['force_create'])) {
                $this->setFlash('error', 'MST "' . $contactData['tax_code'] . '" đã tồn tại: ' . ($dup['company_name'] ?? 'KH #' . $dup['id']) . '. Nếu vẫn muốn tạo, bấm lưu lại.');
                $_SESSION['force_create_contact'] = true;
                return $this->back();
            }
        }
        if (!empty($contactData['phone'])) {
            $dup = Database::fetch("SELECT id, first_name, company_name FROM contacts WHERE tenant_id = ? AND is_deleted = 0 AND phone = ?", [$tid, $contactData['phone']]);
            if ($dup && empty($data['force_create'])) {
                $name = $dup['company_name'] ?: $dup['first_name'];
                $this->setFlash('error', 'SĐT "' . $contactData['phone'] . '" đã tồn tại: ' . $name . '. Nếu vẫn muốn tạo, bấm lưu lại.');
                $_SESSION['force_create_contact'] = true;
                return $this->back();
            }
        }

        $contactData['owner_id'] = $contactData['owner_id'] ?: $this->userId();
        $contactData['created_by'] = $this->userId();

        // Auto-generate account_code if empty
        if (empty($contactData['account_code'])) {
            $maxCode = Database::fetch("SELECT MAX(CAST(SUBSTRING(account_code, 3) AS UNSIGNED)) as max_num FROM contacts WHERE account_code LIKE 'KH%'");
            $nextNum = ($maxCode['max_num'] ?? 0) + 1;
            $contactData['account_code'] = 'KH' . $nextNum;
        }

        // Auto-create or link company for business contacts
        if (!empty($contactData['company_name'])) {
            $companyId = $this->findOrCreateCompany($contactData);
            $contactData['company_id'] = $companyId;
        }

        $contactId = Database::insert('contacts', $contactData);

        $this->handleAvatarUpload($contactId);
        $blocked = $this->saveContactPersons($contactId, $data);
        if (!empty($blocked)) {
            $_SESSION['_cp_blocked'] = ['contact_id' => $contactId, 'items' => $blocked];
        }

        $displayName = $contactData['company_name'] ?: ($contactData['first_name'] . ' ' . $contactData['last_name']);

        // Log activity
        Database::insert('activities', [
            'type' => 'system',
            'title' => "Đã tạo khách hàng: {$displayName}",
            'user_id' => $this->userId(),
            'contact_id' => $contactId,
        ]);

        // Auto-follow: thêm admin + managers làm người theo dõi mặc định
        $defaultFollowers = Database::fetchAll(
            "SELECT id FROM users WHERE tenant_id = ? AND is_active = 1 AND role IN ('admin', 'manager')",
            [Database::tenantId()]
        );
        foreach ($defaultFollowers as $df) {
            Database::query(
                "INSERT IGNORE INTO contact_followers (contact_id, user_id) VALUES (?, ?)",
                [$contactId, $df['id']]
            );
        }

        // Sync tags
        $tagIds = $this->input('tag_ids') ?? [];
        if (is_array($tagIds) && !empty($tagIds)) {
            \App\Services\TagService::syncTags('contact', $contactId, $tagIds);
        }

        $this->setFlash('success', 'Đã tạo khách hàng thành công.');
        return $this->redirect('contacts/' . $contactId);
    }

    public function show($id)
    {
        $this->authorize('contacts', 'view');
        $contact = Database::fetch(
            "SELECT c.*, u.name as owner_name, u.avatar as owner_avatar, cs.name as source_name
             FROM contacts c
             LEFT JOIN users u ON c.owner_id = u.id
             LEFT JOIN contact_sources cs ON c.source_id = cs.id
             WHERE c.id = ?",
            [$id]
        );

        if (!$contact) {
            $this->setFlash('error', 'Contact not found.');
            return $this->redirect('contacts');
        }

        // Ownership check: staff can only view own records
        if (!$this->canAccessEntity('contact', (int)$contact['id'], $contact['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('contacts');
        }

        // Activities loaded by plugin (activity-exchange) in view

        // Split view partial (no layout)
        if ($this->input('partial')) {
            $contactStatuses = Database::fetchAll("SELECT * FROM contact_statuses WHERE tenant_id = ? AND (is_active = 1 OR is_active IS NULL) ORDER BY sort_order", [Database::tenantId()]);
            return $this->view('contacts.partial-show', [
                'contact' => $contact,
                'contactStatuses' => $contactStatuses,
                'noLayout' => true,
            ]);
        }

        $deals = Database::fetchAll(
            "SELECT d.*, ds.name as stage_name
             FROM deals d
             LEFT JOIN deal_stages ds ON d.stage_id = ds.id
             WHERE d.contact_id = ?
             ORDER BY d.created_at DESC",
            [$id]
        );

        $tasks = Database::fetchAll(
            "SELECT * FROM tasks WHERE contact_id = ? ORDER BY due_date ASC",
            [$id]
        );

        $contactStatuses = Database::fetchAll(
            "SELECT * FROM contact_statuses WHERE tenant_id = ? AND (is_active = 1 OR is_active IS NULL) ORDER BY sort_order",
            [Database::tenantId()]
        );

        $followers = Database::fetchAll(
            "SELECT cf.user_id, u.name FROM contact_followers cf JOIN users u ON cf.user_id = u.id WHERE cf.contact_id = ? ORDER BY cf.created_at",
            [$id]
        );

        $contactPersons = Database::fetchAll(
            "SELECT * FROM contact_persons WHERE contact_id = ? ORDER BY is_primary DESC, sort_order, id",
            [$id]
        );

        return $this->view('contacts.show', [
            'contact' => $contact,
            'deals' => $deals,
            'tasks' => $tasks,
            'contactStatuses' => $contactStatuses,
            'followers' => $followers,
            'contactPersons' => $contactPersons,
        ]);
    }

    public function edit($id)
    {
        $this->authorize('contacts', 'edit');
        $contact = Database::fetch("SELECT * FROM contacts WHERE id = ?", [$id]);

        if (!$contact) {
            $this->setFlash('error', 'Contact not found.');
            return $this->redirect('contacts');
        }

        // Ownership check: staff can only edit own records
        if (!$this->canAccessEntity('contact', (int)$contact['id'], $contact['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('contacts');
        }

        $sources = Database::fetchAll("SELECT * FROM contact_sources ORDER BY sort_order, name");
        $users = Database::fetchAll("SELECT u.id, u.name, u.avatar, u.role, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");
        $contactStatuses = Database::fetchAll("SELECT * FROM contact_statuses WHERE tenant_id = ? AND (is_active = 1 OR is_active IS NULL) ORDER BY sort_order", [Database::tenantId()]);
        $industries = Database::fetchAll("SELECT DISTINCT industry FROM contacts WHERE industry IS NOT NULL AND industry != '' ORDER BY industry");
        $contactPersons = Database::fetchAll(
            "SELECT * FROM contact_persons WHERE contact_id = ? ORDER BY is_primary DESC, sort_order, id",
            [$id]
        );

        return $this->view('contacts.edit', [
            'contact' => $contact,
            'sources' => $sources,
            'users' => $users,
            'contactStatuses' => $contactStatuses,
            'industries' => $industries,
            'contactPersons' => $contactPersons,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('contacts/' . $id);
        }
        $this->authorize('contacts', 'edit');

        $contact = Database::fetch("SELECT * FROM contacts WHERE id = ?", [$id]);

        if (!$contact) {
            $this->setFlash('error', 'Contact not found.');
            return $this->redirect('contacts');
        }

        // Ownership check: staff can only update own records
        if (!$this->canAccessEntity('contact', (int)$contact['id'], $contact['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('contacts');
        }

        $data = $this->allInput();
        $contactData = $this->buildContactData($data);

        if (empty($contactData['company_name']) && empty($contactData['first_name'])) {
            $this->setFlash('error', 'Tên khách hàng không được để trống.');
            return $this->back();
        }

        Database::update('contacts', $contactData, 'id = ?', [$id]);

        $this->handleAvatarUpload($id, $contact['avatar'] ?? null);
        $blocked = $this->saveContactPersons($id, $data);
        if (!empty($blocked)) {
            $_SESSION['_cp_blocked'] = ['contact_id' => $id, 'items' => $blocked];
        }

        // Log activity
        $name = trim($contactData['company_name'] ?? $contactData['full_name'] ?? '');
        Database::insert('activities', [
            'type' => 'system',
            'title' => "Đã cập nhật khách hàng: {$name}",
            'user_id' => $this->userId(),
            'contact_id' => $id,
        ]);

        // Sync tags (empty array = clear all)
        $tagIds = $this->input('tag_ids') ?? [];
        \App\Services\TagService::syncTags('contact', (int)$id, is_array($tagIds) ? $tagIds : []);

        $this->setFlash('success', 'Cập nhật khách hàng thành công.');
        return $this->redirect('contacts/' . $id);
    }

    public function searchAjax()
    {
        $this->authorize('contacts', 'view');
        $q = trim($this->input('q') ?? '');
        if (strlen($q) < 1) return $this->json([]);
        $tid = Database::tenantId();
        $like = '%' . $q . '%';
        $scope = $this->getOwnerScopeSql('owner_id', 'contacts');
        $results = Database::fetchAll(
            "SELECT id, first_name, last_name, full_name, company_name, account_code, company_phone, company_email, phone, email, address
             FROM contacts WHERE tenant_id = ? AND is_deleted = 0
             AND (company_name LIKE ? OR full_name LIKE ? OR first_name LIKE ? OR account_code LIKE ? OR phone LIKE ? OR company_phone LIKE ? OR tax_code LIKE ?)" . $scope . "
             ORDER BY company_name, full_name LIMIT 20",
            [$tid, $like, $like, $like, $like, $like, $like, $like]
        );
        return $this->json($results);
    }

    public function persons($id)
    {
        $this->authorize('contacts', 'view');
        $contact = Database::fetch("SELECT owner_id FROM contacts WHERE id = ? AND tenant_id = ?", [(int)$id, Database::tenantId()]);
        if (!$contact || !$this->canAccessEntity('contact', (int)$id, (int)($contact['owner_id'] ?? 0))) {
            return $this->json(['error' => 'Không có quyền'], 403);
        }
        $persons = Database::fetchAll(
            "SELECT id, title, full_name, phone, email, position FROM contact_persons WHERE contact_id = ? ORDER BY is_primary DESC, sort_order, id",
            [(int)$id]
        );
        return $this->json($persons);
    }

    public function followers($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        $this->authorize('contacts', 'edit');

        $contact = Database::fetch("SELECT owner_id FROM contacts WHERE id = ? AND tenant_id = ?", [(int)$id, Database::tenantId()]);
        if (!$contact || !$this->canAccessEntity('contact', (int)$id, (int)($contact['owner_id'] ?? 0))) {
            return $this->json(['error' => 'Không có quyền'], 403);
        }

        $userId = (int) $this->input('user_id');
        $action = $this->input('action');

        if (!$userId) return $this->json(['error' => 'User ID required'], 400);

        if ($action === 'add') {
            Database::query(
                "INSERT IGNORE INTO contact_followers (contact_id, user_id) VALUES (?, ?)",
                [(int)$id, $userId]
            );
            return $this->json(['success' => true]);
        } elseif ($action === 'remove') {
            Database::query(
                "DELETE FROM contact_followers WHERE contact_id = ? AND user_id = ?",
                [(int)$id, $userId]
            );
            return $this->json(['success' => true]);
        }

        return $this->json(['error' => 'Invalid action'], 400);
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('contacts');
        $this->authorize('contacts', 'delete');

        $contact = $this->findSecure('contacts', (int)$id);
        if (!$contact) {
            $this->setFlash('error', 'Khách hàng không tồn tại.');
            return $this->redirect('contacts');
        }

        // Ownership check: staff can only delete own records
        if (!$this->canAccessEntity('contact', (int)$contact['id'], $contact['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('contacts');
        }

        // Soft delete
        Database::softDelete('contacts', 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Xóa khách hàng: {$contact['first_name']} {$contact['last_name']}",
            'user_id' => $this->userId(),
            'contact_id' => (int)$id,
        ]);

        $this->setFlash('success', 'Đã xóa khách hàng.');
        return $this->redirect('contacts');
    }

    // ---- Khôi phục khách hàng đã xóa ----
    public function trash()
    {
        $this->authorize('contacts', 'delete');
        $tid = Database::tenantId();
        $contacts = Database::fetchAll(
            "SELECT c.*, comp.name as company_name
             FROM contacts c
             LEFT JOIN companies comp ON c.company_id = comp.id
             WHERE c.is_deleted = 1 AND c.tenant_id = ?
             ORDER BY c.deleted_at DESC",
            [$tid]
        );

        return $this->view('contacts.trash', ['contacts' => $contacts]);
    }

    public function restore($id)
    {
        if (!$this->isPost()) return $this->redirect('contacts/trash');
        $this->authorize('contacts', 'delete');

        Database::restore('contacts', 'id = ?', [$id]);

        $this->setFlash('success', 'Đã khôi phục khách hàng.');
        return $this->redirect('contacts/trash');
    }

    // ---- Quick Update (inline edit) ----
    public function quickUpdate($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }
        $this->authorize('contacts', 'edit');

        $contact = Database::fetch("SELECT * FROM contacts WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$contact) {
            return $this->json(['error' => 'Khách hàng không tồn tại'], 404);
        }
        if (!$this->canAccessEntity('contact', (int)$id, (int)($contact['owner_id'] ?? 0))) {
            return $this->json(['error' => 'Không có quyền'], 403);
        }

        $field = $this->input('field');
        $value = $this->input('value');
        $allowed = ['status', 'owner_id', 'source_id'];

        if (!in_array($field, $allowed)) {
            return $this->json(['error' => 'Trường không được phép cập nhật'], 422);
        }

        Database::update('contacts', [$field => $value ?: null], 'id = ?', [$id]);

        $display = $value;
        if ($field === 'status') {
            $statusLabels = ['new' => 'Mới', 'contacted' => 'Đã liên hệ', 'qualified' => 'Tiềm năng', 'converted' => 'Chuyển đổi', 'lost' => 'Mất'];
            $statusColors = ['new' => 'info', 'contacted' => 'primary', 'qualified' => 'warning', 'converted' => 'success', 'lost' => 'danger'];
            $label = $statusLabels[$value] ?? $value;
            $color = $statusColors[$value] ?? 'secondary';
            $display = '<span class="badge bg-' . $color . '-subtle text-' . $color . '">' . $label . '</span>';
        } elseif ($field === 'owner_id') {
            $user = Database::fetch("SELECT name FROM users WHERE id = ?", [$value]);
            $display = $user ? htmlspecialchars($user['name']) : '-';
        } elseif ($field === 'source_id') {
            $source = Database::fetch("SELECT name FROM contact_sources WHERE id = ?", [$value]);
            $display = $source ? '<span class="badge bg-secondary-subtle text-secondary">' . htmlspecialchars($source['name']) . '</span>' : '-';
        }

        return $this->json(['success' => true, 'value' => $value, 'display' => $display]);
    }

    // ---- Bulk Actions ----
    public function bulk()
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }
        $this->authorize('contacts', 'edit');

        $ids = $_POST['ids'] ?? [];
        $action = $this->input('action');
        $value = $this->input('value');

        if (empty($ids) || !is_array($ids)) {
            return $this->json(['error' => 'Chưa chọn mục nào'], 422);
        }

        // Sanitize IDs
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $tenantId = Database::tenantId();

        // Chỉ giữ IDs mà user có quyền truy cập
        $accessibleIds = [];
        $ownerRows = Database::fetchAll(
            "SELECT id, owner_id FROM contacts WHERE id IN ({$placeholders}) AND tenant_id = ?",
            array_merge($ids, [$tenantId])
        );
        foreach ($ownerRows as $row) {
            if ($this->canAccessEntity('contact', (int)$row['id'], (int)($row['owner_id'] ?? 0))) {
                $accessibleIds[] = (int)$row['id'];
            }
        }
        if (empty($accessibleIds)) {
            return $this->json(['error' => 'Không có quyền thực hiện trên các khách hàng đã chọn'], 403);
        }
        $ids = $accessibleIds;

        $count = 0;

        switch ($action) {
            case 'assign':
                if (empty($value)) {
                    return $this->json(['error' => 'Vui lòng chọn người phụ trách'], 422);
                }
                foreach ($ids as $id) {
                    Database::update('contacts', ['owner_id' => (int)$value], 'id = ? AND tenant_id = ?', [$id, $tenantId]);
                    $count++;
                }
                break;

            case 'status':
                $allStatuses = Database::fetchAll("SELECT slug FROM contact_statuses WHERE tenant_id = ?", [$tenantId]);
                $validStatuses = array_column($allStatuses, 'slug');
                if (!in_array($value, $validStatuses)) {
                    return $this->json(['error' => 'Mối quan hệ không hợp lệ'], 422);
                }
                foreach ($ids as $id) {
                    Database::update('contacts', ['status' => $value], 'id = ? AND tenant_id = ?', [$id, $tenantId]);
                    $count++;
                }
                break;

            case 'delete':
                foreach ($ids as $id) {
                    Database::softDelete('contacts', 'id = ? AND tenant_id = ?', [$id, $tenantId]);
                    $count++;
                }
                break;

            default:
                return $this->json(['error' => 'Hành động không hợp lệ'], 422);
        }

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Thao tác hàng loạt ({$action}) trên {$count} khách hàng",
            'user_id' => $this->userId(),
        ]);

        return $this->json(['success' => true, 'count' => $count]);
    }

    // ---- Đổi người phụ trách ----
    public function updateAvatar($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);

        $contact = Database::fetch("SELECT avatar FROM contacts WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$contact) return $this->json(['error' => 'Không tồn tại'], 404);

        $filename = upload_avatar('avatar', 'avatars', $contact['avatar'] ?? null);
        if ($filename) {
            Database::update('contacts', ['avatar' => $filename], 'id = ?', [$id]);
            return $this->json(['success' => true, 'url' => url('uploads/avatars/' . $filename)]);
        }
        return $this->json(['error' => 'Không thể tải ảnh'], 422);
    }

    public function changeOwner($id)
    {
        if (!$this->isPost()) return $this->redirect('contacts/' . $id);
        $this->authorize('contacts', 'edit');

        $contact = $this->findSecure('contacts', (int)$id);
        if (!$contact) {
            $this->setFlash('error', 'Khách hàng không tồn tại.');
            return $this->redirect('contacts');
        }
        if (!$this->canAccessEntity('contact', (int)$id, (int)($contact['owner_id'] ?? 0))) {
            $this->setFlash('error', 'Không có quyền đổi người phụ trách khách hàng này.');
            return $this->redirect('contacts/' . $id);
        }

        $newOwnerId = $this->input('owner_id');
        if (empty($newOwnerId)) {
            $this->setFlash('error', 'Vui lòng chọn người phụ trách.');
            return $this->back();
        }

        $oldOwner = Database::fetch("SELECT name FROM users WHERE id = ?", [$contact['owner_id'] ?? 0]);
        $newOwner = Database::fetch("SELECT name FROM users WHERE id = ?", [$newOwnerId]);

        Database::update('contacts', ['owner_id' => $newOwnerId], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Đổi người phụ trách: {$contact['first_name']} {$contact['last_name']}",
            'description' => ($oldOwner['name'] ?? 'Chưa gán') . ' → ' . ($newOwner['name'] ?? ''),
            'user_id' => $this->userId(),
            'contact_id' => (int)$id,
        ]);

        $this->setFlash('success', 'Đã đổi người phụ trách.');
        return $this->redirect('contacts/' . $id);
    }

}
