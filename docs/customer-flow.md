# Kịch bản hệ thống Khách hàng & Xử lý

Bản ghi hoàn chỉnh các luồng nghiệp vụ + cơ chế bảo mật của module Khách hàng, sau khi áp dụng **Strict Privacy (Option 1)** + merge request flow cho person.

## 1. Cấu trúc dữ liệu

| Bảng | Vai trò |
|---|---|
| `contacts` | Công ty / KH doanh nghiệp (có `owner_id` = sale phụ trách) |
| `persons` | Người cá nhân — 1 SĐT = 1 person trong tenant |
| `contact_persons` | "Nơi làm việc" — 1 person ↔ 1 contact (có `is_active`, `start_date`, `end_date`, `is_primary`, `position`) |
| `contact_merge_requests` | Yêu cầu phê duyệt (2 loại: công ty trùng hoặc person trùng) |

---

## 2. Tạo KH mới (công ty)

| Tình huống | Xử lý |
|---|---|
| MST/SĐT/Email công ty chưa có | Tạo mới bình thường ✅ |
| Trùng — user có quyền xem KH đó | Alert vàng + nút "Mở KH" → huỷ tạo mới |
| Trùng — user KHÔNG có quyền xem | Alert đỏ ẩn tên công ty, lộ tên sale phụ trách → form inline gửi merge request |
| Admin / `force_create` | Cảnh báo nhưng cho phép tạo |

### Merge request (công ty trùng)
```
B gửi → contact_merge_requests (pending) → Owner nhận notify
                                              ↓
                                   /approvals/pending
                                              ↓
              ┌───── Duyệt ─────┐      ┌── Từ chối ──┐
              ↓                         ↓
      Tạo contact mới cho B       status = rejected
      (copy thông tin công ty)         ↓
              ↓                  B nhận lý do
      B nhận notification
```

---

## 3. Thêm người liên hệ (contact_persons) — **STRICT PRIVACY**

### 3.1 Gõ SĐT/tên — 4 trường hợp

| Tình huống | Dropdown | Xử lý khi submit |
|---|---|---|
| SĐT **chưa có** trong `persons` | Trống | ✅ Tạo person mới + contact_person |
| SĐT **đã có** + B có quyền xem ít nhất 1 employment | ✅ Hiện gợi ý → click reuse | Link `person_id` cũ, thêm employment mới |
| SĐT **đã có** + B **KHÔNG** có quyền | **Trống** (không lộ) | ❌ Chặn → banner vàng + nút "Gửi yêu cầu" |
| Admin / view_all | Thấy hết | Dùng bình thường |

### 3.2 Luồng "Trùng SĐT, không có quyền"

```
B nhập SĐT Quý (0349…) → dropdown trống → B điền tiếp → Lưu
                                                            ↓
                    Backend saveContactPersons check:
                    - Phone/email tồn tại trong persons? → YES
                    - B có quyền xem employment nào? → NO
                    → Skip row, append vào $blocked[]
                                                            ↓
                    Các người khác trong form vẫn được lưu ✅
                                                            ↓
                    Redirect → banner vàng hiện tại contact:
                    ┌──────────────────────────────────────────┐
                    │ ⚠ 1 người liên hệ không thể thêm         │
                    │ Quý · 0349…         [📤 Gửi yêu cầu]      │
                    └──────────────────────────────────────────┘
                                                            ↓
                    B click → POST /merge-requests/person
                                                            ↓
                    Notification tới owner của Quý
                                                            ↓
                    Owner duyệt → contact_persons row insert
                    (cùng person_id, khác contact_id)
                                                            ↓
                    B nhận notify "Đã duyệt"
```

### 3.3 Các kịch bản khác

| Kịch bản | Xử lý |
|---|---|
| Người làm **nhiều công ty cùng lúc** | Cùng `person_id`, nhiều `contact_persons` row, `is_active = 1` |
| **Nghỉ việc** chuyển công ty | KH cũ: bỏ tick "Đang làm việc" + điền `end_date`. KH mới: flow như 3.1 |
| **2 người share SĐT** (vợ/chồng) | Nếu B không có quyền → chặn. Admin duyệt → chọn "tạo person mới" thay vì link |
| **B biết SĐT qua nguồn ngoài** → nhập CRM | Bị chặn → gửi request → owner quyết định share hay không |

---

## 4. Profile Person `/persons/{id}`

### Quyền xem
- Ai có `contacts.view` → mở được profile
- Hiển thị tên công ty trong lịch sử làm việc
- Công ty không có quyền → hiện tên + 🔒 icon khoá, không click được
- Data kinh doanh tại mỗi công ty → scope theo owner công ty đó

### Hành động (2 nút)
- **Sửa** → `/persons/{id}/edit` (tên, SĐT, email, avatar, giới tính, sinh nhật, note)
- **Xoá** → chặn nếu còn `contact_persons` trỏ về (buộc merge/chuyển trước)

---

## 5. Gộp duplicate `/persons/duplicates`

**Dành cho admin** — khi có duplicate do:
- Sale B nhập SĐT không trùng chính xác nhưng cùng người
- Data legacy trước Phase 3
- Đặc biệt: **2 người khác nhau share SĐT** khi Option 1 strict không chặn được

### Flow gộp
- Liệt kê nhóm persons cùng SĐT
- Admin chọn "Giữ" (target) + "Gộp vào" (source)
- Bấm Gộp:
  1. Transaction begin
  2. Mọi `contact_persons` của source → re-point về target `person_id`
  3. Field trống của target được fill từ source
  4. Source bị xoá
  5. Commit (rollback nếu lỗi)

---

## 6. Data scope

### KH (contacts)
| Vai trò | Thấy |
|---|---|
| Admin / `view_all` | Toàn tenant |
| Trưởng/phó phòng | KH phòng mình + phòng con (đệ quy) |
| Nhân viên thường | KH mình `owner_id` + follower |

### Person
| Vai trò | Thấy trong search dropdown |
|---|---|
| Admin / `view_all` | Mọi person |
| Nhân viên thường | Chỉ person có ít nhất 1 employment ở KH user có quyền |

### Profile person
- Tên công ty: hiển thị cả công ty không có quyền (để biết người đó làm đâu)
- Data công ty: lock theo owner

---

## 7. Permissions endpoints (cheat sheet)

| Endpoint | Check |
|---|---|
| `contacts/` index | `authorize('contacts', 'view')` + owner scope |
| `contacts/search-ajax` | Authorize + owner scope |
| `contacts/check-duplicate` | Lộ tên sale nếu không quyền; cả `persons` check |
| `contacts/{id}` (show/edit/update/delete) | `canAccessEntity` |
| `contacts/bulk` | Filter IDs qua `canAccessEntity` |
| `persons/search` | **STRICT** — hide person nếu 0 employment truy cập được |
| `persons/{id}` (show/edit/delete) | `authorize` + tenant check |
| `persons/duplicates` | Admin / `edit` perm |
| `persons/merge` | Admin + verify tenant mọi person |
| `merge-requests/store` | Company duplicate request |
| `merge-requests/person` | Person access request |
| `merge-requests/{id}/approve` | Auto-branch company / person flow |
| `TagService::syncTags` | Verify tag tenant |

---

## 8. Integration

| Module | Hỗ trợ flow mới |
|---|---|
| **Getfly Sync** | Dùng `PersonService::findOrCreate` — không tạo trùng person qua sync |
| **Import Excel** | Chưa import contact_persons → không ảnh hưởng |
| **Merge request approve** | Tạo `contact_persons` row cùng `person_id` → không sinh duplicate |

---

## 9. Trạng thái implementation

| Mục | Trạng thái |
|---|---|
| Bảng `persons` + migrate 29,499 rows | ✅ |
| Form thêm người LH với dropdown reuse | ✅ |
| Profile person + timeline | ✅ |
| Edit/Xoá person | ✅ |
| Employment lifecycle (is_active/start/end_date) | ✅ |
| Gộp duplicate `/persons/duplicates` | ✅ |
| GetflySync dùng PersonService | ✅ |
| **Strict privacy — hide person không có quyền** | ✅ (Option 1) |
| **Merge request flow cho person** | ✅ |
| Activity/audit log | ✅ |

---

## 10. Tóm tắt 1 câu

> **Chặn trùng → Sale B nhập người đã có → bị chặn → gửi request → Owner duyệt → thêm employment cùng person_id. Không duplicate, không lộ thông tin sale khác.**
