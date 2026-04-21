# Tổng hợp kịch bản hệ thống Khách hàng & cách xử lý

Bản ghi hoàn chỉnh các luồng nghiệp vụ + cơ chế bảo mật + cơ chế dedupe của module Khách hàng.

## 1. Cấu trúc dữ liệu

### Bảng chính
- **`contacts`** — Công ty / khách hàng (có `owner_id` là sale phụ trách)
- **`persons`** — Người cá nhân (tên, SĐT, email, avatar, `is_hidden`) — unique per tenant
- **`contact_persons`** — "Nơi làm việc" (1 person ↔ 1 công ty, có `is_active`, `start_date`, `end_date`, `is_primary`, `position`, `title`)

Một `person` có thể có nhiều `contact_persons` → giải quyết **nghỉ việc chuyển công ty** và **làm song song nhiều công ty**.

### Bảng phụ trợ
- **`contact_merge_requests`** — Yêu cầu thêm người LH vào KH của sale khác
- **`contact_followers`** — Followers theo dõi thêm
- **`tags` + `taggables`** — Nhãn dùng chung cho nhiều loại entity
- **`departments`** — Phòng ban với `manager_id` / `vice_manager_id` (dùng cho scope data)

---

## 2. Tạo KH mới — duplicate detection

### 2.1 Trùng MST / SĐT / Email công ty

| Trường hợp | Hiển thị | Hành động của Sale B |
|---|---|---|
| B có quyền xem KH đó | 🟡 Alert tên công ty + phụ trách + nút **"Mở KH"** | Click mở KH, huỷ ý định tạo mới |
| B KHÔNG có quyền xem | 🔴 Alert chỉ lộ tên sale phụ trách (ẩn tên công ty) | Form inline → **gửi yêu cầu thêm người liên hệ** cho owner duyệt |
| Admin / `force_create` | Cảnh báo, không chặn | Tiếp tục tạo mới |

### 2.2 Luồng phê duyệt merge request

```
B gửi yêu cầu  →  contact_merge_requests (pending)  →  Owner nhận notification
                                                          ↓
                                             Owner vào /approvals/pending
                                                          ↓
                           ┌───── Duyệt ─────┐    ┌── Từ chối ──┐
                           ↓                      ↓
        PersonService::findOrCreate tạo      merge_requests
        person + contact_person mới tại      status = rejected
        KH của Owner                              ↓
                           ↓                 B nhận notify + lý do
                   B nhận notify
```

---

## 3. Thêm "Người liên hệ" — person reuse

### 3.1 Người hoàn toàn mới
Gõ SĐT không có gợi ý → Lưu → `PersonService::findOrCreate` tạo `persons` + `contact_persons` cùng lúc.

### 3.2 Người đã có ở công ty KHÁC (B có quyền xem)
Gõ SĐT/tên ≥3 ký tự → dropdown gợi ý → click → form tự điền + link `person_id` → Lưu. Người đó có thêm nơi làm việc mới cùng `person_id`.

### 3.3 Người đã có ở công ty B KHÔNG có quyền xem
Dropdown gợi ý nhưng ẩn tên công ty → hiển thị **"+N nơi khác không có quyền xem"**. B vẫn link được `person_id`, không tạo trùng; không biết người đó đang ở công ty nào của sale khác.

### 3.4 Người làm nhiều công ty cùng lúc
Dùng cùng `person_id`, mỗi công ty 1 row `contact_persons` với `is_active = 1`.

### 3.5 Nghỉ việc chuyển công ty
1. KH cũ: form edit → bỏ tick **"Đang làm việc tại đây"** + điền `end_date`
2. KH mới: form create/edit → gõ SĐT → chọn gợi ý → điền `start_date`, tick active

Profile hiển thị đầy đủ timeline: rows "Đã nghỉ" bị gạch chân + opacity giảm; rows đang làm hiện ngày vào.

### 3.6 Race condition 2 sale cùng tạo person cùng SĐT
`PersonService::findOrCreate` dùng transaction + re-check dưới lock + retry read khi rollback → chỉ có 1 person được tạo.

---

## 4. Profile Person (`/persons/{id}`)

### 4.1 Hiển thị
- Info cá nhân (tên, SĐT, email, avatar, giới tính, sinh nhật, note)
- Badge "Ẩn khỏi search" nếu `is_hidden = 1`
- Lịch sử làm việc (employments) theo thời gian mới nhất trước:
  - Đang làm (xanh) — click mở công ty
  - Đã nghỉ (xám) — hiện end_date
  - Công ty không có quyền → tên hiển thị + 🔒 icon khoá, không click được

### 4.2 Hành động (3 nút)
- **Sửa** → `/persons/{id}/edit` — edit tên, SĐT, email, avatar, giới tính, sinh nhật, note
- **Ẩn / Bỏ ẩn** → toggle `is_hidden`. Ẩn rồi thì dropdown search không trả person này cho sale khác (chỉ person_id trực tiếp biết được)
- **Xoá** → chỉ cho phép nếu **không còn contact_persons** nào trỏ về. Nếu còn, báo lỗi + gợi ý gộp

---

## 5. Gộp duplicate — `/persons/duplicates`

- Liệt kê nhóm persons cùng SĐT
- Mỗi nhóm: radio **"Giữ"** + checkbox **"Gộp vào"** cho bản sao
- Bấm **Gộp**:
  1. Transaction bắt đầu
  2. Mọi `contact_persons` của bản sao → re-point `person_id` về target
  3. Field trống của target được fill từ bản sao (full_name, phone, email, gender, dob, avatar, note)
  4. Bản sao bị xoá khỏi `persons`
  5. Commit. Rollback nếu lỗi

Verify mọi person thuộc cùng tenant; không cho chọn target làm source; UI disable checkbox tự động.

---

## 6. Data scope (ai thấy gì)

### 6.1 Quyền xem KH
| Vai trò | Thấy KH nào |
|---|---|
| Admin (`is_system=1`) hoặc `view_all` | Toàn bộ tenant |
| Trưởng/phó phòng | KH thuộc phòng mình + phòng con (đệ quy hierarchy) |
| Nhân viên thường | KH mình là `owner_id` + KH mình là follower |

**Không cần tick quyền "Xem phòng ban"** — tự động dựa trên vai trò manager/vice_manager trong bảng `departments`.

### 6.2 Quyền với Person
- **Profile**: ai có `contacts.view` đều mở được `/persons/{id}` (toàn tenant); công ty trong timeline được lock theo quyền từng công ty
- **Edit / delete / toggle hidden**: cần `contacts.edit` (delete cần `contacts.delete`)
- **Search dropdown**: lọc `is_hidden = 0`, giấu tên công ty không có quyền
- **Merge**: verify tenant cho mọi person source + target

---

## 7. Permissions & bảo mật (endpoints)

| Endpoint | Check |
|---|---|
| `contacts/` (index) | `authorize('contacts', 'view')` + owner scope |
| `contacts/search-ajax` | `authorize('contacts', 'view')` + owner scope |
| `contacts/check-duplicate` | Nếu trùng & không có quyền xem → chỉ lộ tên sale phụ trách, không lộ tên công ty |
| `contacts/{id}` (show) | `canAccessEntity` |
| `contacts/{id}/edit` | `canAccessEntity` |
| `contacts/{id}/update` | `canAccessEntity` + sync tags (tenant-verified) |
| `contacts/{id}/delete` | `canAccessEntity` + `delete` perm |
| `contacts/{id}/persons` (AJAX) | `authorize('contacts', 'view')` + `canAccessEntity` |
| `contacts/{id}/followers` | `canAccessEntity` + `edit` perm |
| `contacts/{id}/change-owner` | `canAccessEntity` + `edit` perm |
| `contacts/{id}/quick-update` | `canAccessEntity` |
| `contacts/bulk` | `edit` perm + filter IDs qua `canAccessEntity` |
| `persons/search` | `authorize('contacts', 'view')` + filter tên công ty theo quyền |
| `persons/{id}` | `authorize('contacts', 'view')` |
| `persons/{id}/edit` | `authorize('contacts', 'edit')` + tenant check |
| `persons/{id}/update` | `authorize('contacts', 'edit')` + tenant check |
| `persons/{id}/delete` | `authorize('contacts', 'delete')` + không còn employment |
| `persons/{id}/toggle-hidden` | `authorize('contacts', 'edit')` + tenant check |
| `persons/duplicates` | `authorize('contacts', 'edit')` |
| `persons/merge` | `authorize('contacts', 'edit')` + verify tenant của mọi person |
| `merge-requests/store` | POST, verify tenant của existing_contact, chặn duplicate pending |
| `merge-requests/pending` | `authorize('contacts', 'edit')` |
| `merge-requests/{id}/approve` | Chỉ owner của existing_contact + `edit` perm |
| `merge-requests/{id}/reject` | Chỉ owner của existing_contact + `edit` perm |
| `TagService::syncTags` | Verify tag_id thuộc tenant hiện tại trước khi insert |

---

## 8. Integration

### 8.1 Getfly Sync
`GetflySyncController::sync` gọi `PersonService::findOrCreate` cho mỗi contact_person đến từ API. Sync lần N vẫn bảo toàn `person_id` via phone match.

### 8.2 Import Excel
`ImportService::importContacts` hiện chỉ import bảng `contacts` (công ty), chưa import contact_persons. Không ảnh hưởng persons.

### 8.3 Merge request → auto create person
Khi owner duyệt merge_request, dùng `PersonService::findOrCreate` để tạo/link person cho contact_person mới — không sinh duplicate.

---

## 9. Trạng thái các mục gap

| # | Mục | Trạng thái |
|---|---|---|
| 1 | Form sửa person profile (`/persons/{id}/edit`) | ✅ Đã làm |
| 2 | Toggle `is_hidden` | ✅ Đã làm |
| 3 | Xoá person (chặn nếu còn contact_persons) | ✅ Đã làm |
| 4 | UI `start_date` / `end_date` / `is_active` trong form contact_persons | ✅ Đã làm |
| 5 | Hiển thị `is_active` trong "Người liên hệ" card ở contacts/show | ✅ Đã làm |
| 6 | `GetflySyncController` dùng `PersonService::findOrCreate` | ✅ Đã làm |
| 7 | Race condition trong `PersonService::findOrCreate` | ✅ Đã fix (transaction + recheck) |
| 8 | MergeRequestController — auth + tenant check | ✅ Đã fix |
| 9 | Unique index `(tenant_id, phone)` trên persons | Bỏ qua (có case 2 người share SĐT + Phase 4 đủ dùng) |
| 10 | Import Excel dedupe | Chưa cần (`ImportService` không import contact_persons) |
| 11 | `checkDuplicate` có lộ tên sale phụ trách khi trùng | Không fix (by design — cần biết để gửi merge request) |
