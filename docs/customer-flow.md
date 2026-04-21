# Tổng hợp kịch bản hệ thống Khách hàng & cách xử lý

## 1. Tạo KH mới — duplicate detection

### 1.1 Trùng MST / SĐT / Email công ty

| Trường hợp | Hiển thị | Hành động của B |
|---|---|---|
| B có quyền xem KH đó (cùng phòng/hierarchy) | 🟡 Alert có tên KH + phụ trách + nút **"Mở KH"** | Click mở KH, bỏ ý định tạo mới |
| B KHÔNG có quyền xem | 🔴 Alert chỉ lộ tên sale phụ trách (ẩn tên công ty) | Hiện form inline → gửi **yêu cầu thêm người liên hệ** cho owner duyệt |
| Admin hoặc `force_create` | Cho tạo mới (cảnh báo nhưng không chặn) | Tiếp tục tạo |

### 1.2 Luồng phê duyệt merge request

```
B gửi yêu cầu  →  merge_requests (pending)  →  A nhận notification
                                                      ↓
                                         A vào /approvals/pending
                                                      ↓
                         ┌───── Duyệt ─────┐   ┌── Từ chối ──┐
                         ↓                     ↓
           Person + contact_person        merge_requests
           thêm vào KH của A              status = rejected
                         ↓                     ↓
               B nhận notify          B nhận notify + lý do
```

---

## 2. Thêm "Người liên hệ" — person reuse (Phase 3)

### 2.1 Người hoàn toàn mới
Gõ SĐT không có gợi ý → Lưu → Tạo `persons` + `contact_persons` cùng lúc.

### 2.2 Người đã có ở công ty KHÁC (B có quyền xem)
Gõ SĐT/tên ≥3 ký tự → dropdown hiện gợi ý → click → form tự điền + link `person_id` → Lưu → Người đó có thêm nơi làm việc mới.

### 2.3 Người đã có ở công ty B KHÔNG có quyền xem
Dropdown hiện gợi ý nhưng ẩn tên công ty, thay bằng "+N nơi khác không có quyền xem". B vẫn link được `person_id`, không bị tạo trùng; nhưng không biết người đó làm ở công ty nào của sale khác.

### 2.4 Người làm nhiều công ty cùng lúc
Dùng cùng `person_id`, mỗi công ty 1 row `contact_persons` với `is_active = 1`.

### 2.5 Nghỉ việc chuyển công ty
1. Ở KH cũ: sửa người liên hệ → `is_active = 0`, `end_date = <ngày nghỉ>`
2. Ở KH mới: thêm người liên hệ → gõ SĐT → gợi ý hiện → chọn → `is_active = 1`, `start_date = <ngày vào>`

Profile người đó hiển thị lịch sử làm việc đầy đủ (cả "Đang làm" và "Đã nghỉ").

---

## 3. Data scope (ai thấy gì)

### 3.1 Quyền xem KH
| Vai trò | Thấy KH nào |
|---|---|
| Admin (`is_system=1`) hoặc `view_all` | Toàn bộ tenant |
| Trưởng/phó phòng | KH thuộc phòng mình + phòng con (đệ quy) |
| Nhân viên thường | KH mình là `owner_id` + KH mình là follower |

**Không cần tick quyền "Xem phòng ban"** — tự động dựa trên vai trò manager/vice_manager trong bảng `departments`.

### 3.2 Profile Person
- Thấy info cá nhân (tên, SĐT, email)
- Thấy tên công ty trong lịch sử làm việc (kể cả công ty không có quyền — để biết người đó tồn tại)
- Không click vào được công ty không có quyền (🔒 icon khóa)
- Data kinh doanh (deal, báo giá, đơn) tại từng công ty vẫn scope theo owner

### 3.3 Search dropdown (Phase 3)
- Chỉ trả person `is_hidden = 0`
- Tên công ty B không có quyền → hiển thị "+N nơi khác không có quyền xem"

---

## 4. Gộp duplicate (Phase 4)

Sau import hoặc nhập tay, có thể 2 `persons` khác nhau nhưng cùng SĐT.

**Admin UI `/persons/duplicates`**:
- Liệt kê theo nhóm SĐT trùng
- Mỗi nhóm: radio **"Giữ"** cho person chính + checkbox **"Gộp vào"** cho bản sao
- Bấm Gộp:
  1. Mọi `contact_persons` của bản sao → re-point về person chính
  2. Info trống của person chính được fill từ bản sao
  3. Bản sao bị xoá khỏi `persons`

**Transaction wrap + verify tenant** → an toàn, rollback nếu lỗi.

---

## 5. Permissions & bảo mật

| Endpoint | Check |
|---|---|
| `contacts/search-ajax` | `authorize('contacts', 'view')` + scope theo owner |
| `contacts/{id}` (show) | `canAccessEntity` |
| `contacts/{id}/persons` | `authorize` + `canAccessEntity` |
| `contacts/{id}/followers` | `canAccessEntity` |
| `contacts/{id}/quick-update` | `canAccessEntity` |
| `contacts/bulk` | Filter IDs qua `canAccessEntity` trước khi mass action |
| `contacts/{id}/change-owner` | `canAccessEntity` |
| `TagService::syncTags` | Verify tag_id thuộc tenant hiện tại |
| `persons/search` | `authorize('contacts', 'view')` + filter tên công ty theo quyền |
| `persons/merge` | `authorize('contacts', 'edit')` + verify tenant của mọi person |

---

## 6. Vấn đề còn tồn tại / việc nên làm tiếp

| # | Gap | Ưu tiên |
|---|---|---|
| 1 | Form sửa person profile — hiện chỉ edit qua từng employment | Cao |
| 2 | Toggle `is_hidden` (person VIP ẩn khỏi search) | Trung |
| 3 | Xoá person | Trung |
| 4 | Unique index `(tenant_id, phone)` trên persons — chặn race condition ở tầng DB | Thấp |
| 5 | Import Excel — dedupe theo SĐT person (hiện `ImportService` không import contact_persons, chưa cần) | Thấp |
| 6 | GetflySyncController đã cập nhật để gọi `PersonService::findOrCreate` | ✅ Đã làm |
