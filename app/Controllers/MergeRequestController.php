<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class MergeRequestController extends Controller
{
    /**
     * List pending merge requests for current user (as approver).
     */
    public function pending()
    {
        $this->authorize('contacts', 'edit');
        $uid = $this->userId();
        $tid = Database::tenantId();

        // Get requests where current user owns the existing contact
        $requests = Database::fetchAll(
            "SELECT mr.*,
                    c.company_name, c.first_name, c.last_name, c.account_code,
                    u.name as requester_name, u.avatar as requester_avatar
             FROM contact_merge_requests mr
             JOIN contacts c ON mr.existing_contact_id = c.id
             JOIN users u ON mr.requested_by = u.id
             WHERE mr.tenant_id = ? AND mr.status = 'pending' AND c.owner_id = ?
             ORDER BY mr.created_at DESC",
            [$tid, $uid]
        );

        // Also get requests I sent
        $myRequests = Database::fetchAll(
            "SELECT mr.*,
                    c.company_name, c.first_name, c.last_name, c.account_code,
                    u.name as approver_name
             FROM contact_merge_requests mr
             JOIN contacts c ON mr.existing_contact_id = c.id
             LEFT JOIN users u ON c.owner_id = u.id
             WHERE mr.tenant_id = ? AND mr.requested_by = ?
             ORDER BY mr.created_at DESC
             LIMIT 50",
            [$tid, $uid]
        );

        return $this->view('approvals.pending', [
            'requests' => $requests,
            'myRequests' => $myRequests,
        ]);
    }

    /**
     * Create a merge request (Sale 2 submits).
     */
    public function store()
    {
        if (!$this->isPost()) return $this->json(['error' => 'Invalid'], 400);

        $data = $this->allInput();
        $existingContactId = (int)($data['existing_contact_id'] ?? 0);
        $cpName = trim($data['cp_name'] ?? '');
        $cpPhone = trim($data['cp_phone'] ?? '');

        if (!$existingContactId || empty($cpName)) {
            return $this->json(['error' => 'Thiếu thông tin'], 422);
        }

        // Check existing contact exists (and belongs to current tenant)
        $contact = Database::fetch(
            "SELECT id, owner_id, company_name FROM contacts WHERE id = ? AND tenant_id = ? AND is_deleted = 0",
            [$existingContactId, Database::tenantId()]
        );
        if (!$contact) return $this->json(['error' => 'KH không tồn tại'], 404);

        // Check if phone already exists in contact_persons of this contact
        if ($cpPhone) {
            $existingPerson = Database::fetch(
                "SELECT id FROM contact_persons WHERE contact_id = ? AND phone = ?",
                [$existingContactId, $cpPhone]
            );
            if ($existingPerson) {
                return $this->json(['error' => 'Số điện thoại này đã có trong danh sách người liên hệ của doanh nghiệp. Không thể tạo trùng.', 'phone_exists' => true]);
            }
        }

        // Check if already has pending request with same phone
        if ($cpPhone) {
            $pendingReq = Database::fetch(
                "SELECT id FROM contact_merge_requests WHERE existing_contact_id = ? AND cp_phone = ? AND status = 'pending'",
                [$existingContactId, $cpPhone]
            );
            if ($pendingReq) {
                return $this->json(['error' => 'Đã có yêu cầu đang chờ duyệt với SĐT này.']);
            }
        }

        // Mask phone: show last 4 digits
        $phoneMasked = $cpPhone ? '***' . substr($cpPhone, -4) : null;

        $id = Database::insert('contact_merge_requests', [
            'tenant_id' => Database::tenantId(),
            'existing_contact_id' => $existingContactId,
            'requested_by' => $this->userId(),
            'cp_title' => trim($data['cp_title'] ?? '') ?: null,
            'cp_name' => $cpName,
            'cp_phone' => $cpPhone,
            'cp_phone_masked' => $phoneMasked,
            'cp_email' => trim($data['cp_email'] ?? '') ?: null,
            'cp_position' => trim($data['cp_position'] ?? '') ?: null,
            'note' => trim($data['note'] ?? '') ?: null,
        ]);

        // Notify contact owner (Sale 1)
        $requesterName = $_SESSION['user']['name'] ?? 'Ai đó';
        Database::insert('notifications', [
            'tenant_id' => Database::tenantId(),
            'user_id' => $contact['owner_id'],
            'type' => 'info',
            'title' => $requesterName . ' yêu cầu thêm người LH vào ' . ($contact['company_name'] ?? 'KH'),
            'message' => 'Người LH: ' . $cpName . ' (' . ($phoneMasked ?? 'N/A') . ')',
            'link' => 'approvals/pending',
            'icon' => 'ri-user-add-line',
        ]);

        return $this->json(['success' => true, 'message' => 'Đã gửi yêu cầu phê duyệt. Người phụ trách sẽ xem xét.']);
    }

    /**
     * Approve a merge request (Sale 1 approves).
     */
    public function approve($id)
    {
        if (!$this->isPost()) return $this->redirect('approvals/pending');

        $req = Database::fetch("SELECT mr.*, c.owner_id FROM contact_merge_requests mr JOIN contacts c ON mr.existing_contact_id = c.id WHERE mr.id = ?", [$id]);
        if (!$req) {
            $this->setFlash('error', 'Yêu cầu không tồn tại.');
            return $this->redirect('approvals/pending');
        }

        // Only contact owner or admin can approve
        if ($req['owner_id'] != $this->userId() && !$this->isSystemAdmin()) {
            $this->setFlash('error', 'Bạn không có quyền duyệt.');
            return $this->redirect('approvals/pending');
        }

        // Get existing contact full data
        $existingContact = Database::fetch("SELECT * FROM contacts WHERE id = ?", [$req['existing_contact_id']]);

        // Auto-generate account_code
        $maxCode = Database::fetch("SELECT MAX(CAST(SUBSTRING(account_code, 3) AS UNSIGNED)) as max_num FROM contacts WHERE account_code LIKE 'KH%'");
        $accountCode = 'KH' . (($maxCode['max_num'] ?? 0) + 1);

        // Create new contact for Sale 2 (copy DN info, link same company)
        $newContactId = Database::insert('contacts', [
            'tenant_id' => $existingContact['tenant_id'],
            'account_code' => $accountCode,
            'company_id' => $existingContact['company_id'],
            'company_name' => $existingContact['company_name'],
            'tax_code' => $existingContact['tax_code'],
            'company_phone' => $existingContact['company_phone'],
            'company_email' => $existingContact['company_email'],
            'address' => $existingContact['address'],
            'province' => $existingContact['province'],
            'district' => $existingContact['district'],
            'ward' => $existingContact['ward'],
            'city' => $existingContact['city'],
            'country' => $existingContact['country'],
            'website' => $existingContact['website'],
            'fax' => $existingContact['fax'],
            'industry' => $existingContact['industry'],
            'company_size' => $existingContact['company_size'],
            // Người LH mới làm thông tin chính
            'first_name' => explode(' ', $req['cp_name'], 2)[0],
            'last_name' => explode(' ', $req['cp_name'], 2)[1] ?? '',
            'full_name' => $req['cp_name'],
            'title' => $req['cp_title'],
            'phone' => $req['cp_phone'],
            'email' => $req['cp_email'],
            'position' => $req['cp_position'],
            // Assign to Sale 2
            'owner_id' => $req['requested_by'],
            'created_by' => $req['requested_by'],
            'status' => $existingContact['status'] ?? 'new',
            'customer_group' => $existingContact['customer_group'],
            'source_id' => $existingContact['source_id'],
        ]);

        // Add as contact person too
        Database::insert('contact_persons', [
            'contact_id' => $newContactId,
            'title' => $req['cp_title'],
            'full_name' => $req['cp_name'],
            'phone' => $req['cp_phone'],
            'email' => $req['cp_email'],
            'position' => $req['cp_position'] ?? null,
            'is_primary' => 1,
        ]);

        // Auto-follow: owner + người duyệt + admin/managers
        $tid = Database::tenantId();
        $followerIds = [$req['requested_by'], $this->userId()];
        $defaultFollowers = Database::fetchAll(
            "SELECT id FROM users WHERE tenant_id = ? AND is_active = 1 AND role IN ('admin', 'manager')",
            [$tid]
        );
        foreach ($defaultFollowers as $df) { $followerIds[] = $df['id']; }
        foreach (array_unique($followerIds) as $fid) {
            Database::query("INSERT IGNORE INTO contact_followers (contact_id, user_id) VALUES (?, ?)", [$newContactId, $fid]);
        }

        Database::update('contact_merge_requests', [
            'status' => 'approved',
            'approved_by' => $this->userId(),
        ], 'id = ?', [$id]);

        // Notify requester with link to new contact
        Database::insert('notifications', [
            'tenant_id' => Database::tenantId(),
            'user_id' => $req['requested_by'],
            'type' => 'success',
            'title' => 'Yêu cầu đã được duyệt - KH mới đã tạo',
            'message' => $req['cp_name'] . ' tại ' . ($existingContact['company_name'] ?? 'DN') . ' (' . $accountCode . ')',
            'link' => 'contacts/' . $newContactId,
            'icon' => 'ri-check-line',
        ]);

        $this->setFlash('success', 'Đã duyệt. KH mới ' . $accountCode . ' đã tạo cho người yêu cầu.');
        return $this->redirect('approvals/pending');
    }

    /**
     * Reject a merge request.
     */
    public function reject($id)
    {
        if (!$this->isPost()) return $this->redirect('approvals/pending');

        $req = Database::fetch("SELECT mr.*, c.owner_id FROM contact_merge_requests mr JOIN contacts c ON mr.existing_contact_id = c.id WHERE mr.id = ?", [$id]);
        if (!$req || ($req['owner_id'] != $this->userId() && !$this->isSystemAdmin())) {
            $this->setFlash('error', 'Không có quyền.');
            return $this->redirect('approvals/pending');
        }

        Database::update('contact_merge_requests', [
            'status' => 'rejected',
            'approved_by' => $this->userId(),
            'rejected_reason' => trim($this->input('reason') ?? '') ?: null,
        ], 'id = ?', [$id]);

        // Notify requester
        Database::insert('notifications', [
            'tenant_id' => Database::tenantId(),
            'user_id' => $req['requested_by'],
            'type' => 'danger',
            'title' => 'Yêu cầu thêm người LH bị từ chối',
            'message' => $req['cp_name'] . '. Lý do: ' . (trim($this->input('reason') ?? '') ?: 'Không nêu'),
            'link' => 'approvals/pending',
            'icon' => 'ri-close-line',
        ]);

        $this->setFlash('success', 'Đã từ chối yêu cầu.');
        return $this->redirect('approvals/pending');
    }
}
