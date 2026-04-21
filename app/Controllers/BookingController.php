<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class BookingController extends Controller
{
    public function index()
    {
        $tid = $this->tenantId();
        $uid = $this->userId();

        $links = Database::fetchAll(
            "SELECT bl.*, (SELECT COUNT(*) FROM bookings b WHERE b.link_id = bl.id) as booking_count
             FROM booking_links bl
             WHERE bl.tenant_id = ? AND bl.user_id = ?
             ORDER BY bl.created_at DESC",
            [$tid, $uid]
        );

        return $this->view('bookings.index', [
            'pageTitle' => 'Đặt lịch hẹn',
            'links' => $links,
        ]);
    }

    public function create()
    {
        return $this->view('bookings.create', [
            'pageTitle' => 'Tạo liên kết đặt lịch',
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('bookings');

        $data = $this->allInput();
        $title = trim($data['title'] ?? '');
        if (empty($title)) {
            $this->setFlash('error', 'Tiêu đề không được để trống.');
            return $this->back();
        }

        $slug = $this->generateSlug($data['slug'] ?? $title);

        // Check unique slug
        $exists = Database::fetch("SELECT id FROM booking_links WHERE slug = ?", [$slug]);
        if ($exists) {
            $slug = $slug . '-' . substr(uniqid(), -4);
        }

        $days = implode(',', $data['available_days'] ?? [1,2,3,4,5]);

        Database::query(
            "INSERT INTO booking_links (tenant_id, user_id, title, description, slug, duration, available_days, TIME(start_at), TIME(end_at), buffer_minutes, max_advance_days, is_active, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())",
            [
                $this->tenantId(),
                $this->userId(),
                $title,
                trim($data['description'] ?? ''),
                $slug,
                (int)($data['duration'] ?? 30),
                $days,
                $data['TIME(start_at)'] ?? '08:00',
                $data['TIME(end_at)'] ?? '17:00',
                (int)($data['buffer_minutes'] ?? 15),
                (int)($data['max_advance_days'] ?? 30),
            ]
        );

        $this->setFlash('success', 'Đã tạo liên kết đặt lịch thành công.');
        return $this->redirect('bookings');
    }

    public function edit($id)
    {
        $link = $this->findSecure('booking_links', (int)$id);
        if (!$link) {
            $this->setFlash('error', 'Không tìm thấy liên kết.');
            return $this->redirect('bookings');
        }

        return $this->view('bookings.create', [
            'pageTitle' => 'Sửa liên kết đặt lịch',
            'link' => $link,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('bookings');

        $link = $this->findSecure('booking_links', (int)$id);
        if (!$link) {
            $this->setFlash('error', 'Không tìm thấy liên kết.');
            return $this->redirect('bookings');
        }

        $data = $this->allInput();
        $title = trim($data['title'] ?? '');
        if (empty($title)) {
            $this->setFlash('error', 'Tiêu đề không được để trống.');
            return $this->back();
        }

        $days = implode(',', $data['available_days'] ?? [1,2,3,4,5]);

        Database::query(
            "UPDATE booking_links SET title=?, description=?, duration=?, available_days=?, TIME(start_at)=?, TIME(end_at)=?, buffer_minutes=?, max_advance_days=?, is_active=?, updated_at=NOW()
             WHERE id=? AND tenant_id=?",
            [
                $title,
                trim($data['description'] ?? ''),
                (int)($data['duration'] ?? 30),
                $days,
                $data['TIME(start_at)'] ?? '08:00',
                $data['TIME(end_at)'] ?? '17:00',
                (int)($data['buffer_minutes'] ?? 15),
                (int)($data['max_advance_days'] ?? 30),
                isset($data['is_active']) ? 1 : 0,
                (int)$id,
                $this->tenantId(),
            ]
        );

        $this->setFlash('success', 'Đã cập nhật liên kết đặt lịch.');
        return $this->redirect('bookings');
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('bookings');

        Database::query(
            "DELETE FROM booking_links WHERE id = ? AND tenant_id = ?",
            [(int)$id, $this->tenantId()]
        );

        $this->setFlash('success', 'Đã xóa liên kết đặt lịch.');
        return $this->redirect('bookings');
    }

    public function bookings($id)
    {
        $link = $this->findSecure('booking_links', (int)$id);
        if (!$link) {
            $this->setFlash('error', 'Không tìm thấy liên kết.');
            return $this->redirect('bookings');
        }

        $bookings = Database::fetchAll(
            "SELECT * FROM bookings WHERE link_id = ? ORDER BY DATE(start_at) DESC, TIME(start_at) DESC",
            [(int)$id]
        );

        return $this->view('bookings.bookings_list', [
            'pageTitle' => 'Lịch hẹn - ' . $link['title'],
            'link' => $link,
            'bookings' => $bookings,
        ]);
    }

    // ==================== PUBLIC ROUTES ====================

    public function publicPage($slug)
    {
        $link = Database::fetch(
            "SELECT bl.*, u.name as user_name, u.email as user_email
             FROM booking_links bl
             JOIN users u ON bl.user_id = u.id
             WHERE bl.slug = ? AND bl.is_active = 1",
            [$slug]
        );

        if (!$link) {
            http_response_code(404);
            echo "Liên kết không tồn tại hoặc đã bị vô hiệu hóa.";
            return;
        }

        $noLayout = true;
        return $this->view('bookings.public', [
            'link' => $link,
            'noLayout' => true,
        ]);
    }

    public function getAvailableSlots($slug)
    {
        $link = Database::fetch(
            "SELECT * FROM booking_links WHERE slug = ? AND is_active = 1",
            [$slug]
        );

        if (!$link) {
            return $this->json(['error' => 'Liên kết không tồn tại'], 404);
        }

        $date = $this->input('date', date('Y-m-d'));
        $dayOfWeek = (int)date('N', strtotime($date)); // 1=Mon, 7=Sun

        // Map day names to numbers
        $dayMap = ['mon'=>1,'tue'=>2,'wed'=>3,'thu'=>4,'fri'=>5,'sat'=>6,'sun'=>7];
        $rawDays = json_decode($link['available_days'] ?? '[]', true) ?: [];
        $availableDays = array_map(function($d) use ($dayMap) {
            return is_numeric($d) ? (int)$d : ($dayMap[strtolower($d)] ?? 0);
        }, $rawDays);

        if (!in_array($dayOfWeek, $availableDays)) {
            return $this->json(['slots' => [], 'message' => 'Ngày này không khả dụng']);
        }

        // Check if date is within max advance days
        $maxDate = date('Y-m-d', strtotime("+{$link['max_advance_days']} days"));
        if ($date > $maxDate || $date < date('Y-m-d')) {
            return $this->json(['slots' => [], 'message' => 'Ngày không hợp lệ']);
        }

        // Get existing bookings for this date
        $existingBookings = Database::fetchAll(
            "SELECT TIME(start_at) as start_time, TIME(end_at) as end_time FROM bookings
             WHERE link_id = ? AND DATE(start_at) = ? AND status = 'confirmed'",
            [$link['id'], $date]
        );

        // Parse available hours
        $hours = json_decode($link['available_hours'] ?? '{}', true) ?: [];
        $startHour = $hours['start'] ?? '08:00';
        $endHour = $hours['end'] ?? '17:00';

        $duration = (int)($link['duration_minutes'] ?? 30);
        $buffer = (int)($link['buffer_minutes'] ?? 15);
        $startTime = strtotime($date . ' ' . $startHour);
        $endTime = strtotime($date . ' ' . $endHour);

        $slots = [];
        $current = $startTime;

        while ($current + ($duration * 60) <= $endTime) {
            $slotStart = date('H:i', $current);
            $slotEnd = date('H:i', $current + ($duration * 60));

            // Check if slot overlaps with existing bookings
            $isAvailable = true;
            foreach ($existingBookings as $booking) {
                $bStart = substr($booking['start_time'], 0, 5);
                $bEnd = substr($booking['end_time'], 0, 5);
                if ($slotStart < $bEnd && $slotEnd > $bStart) {
                    $isAvailable = false;
                    break;
                }
            }

            // Don't show past slots for today
            if ($date === date('Y-m-d') && $current < time()) {
                $isAvailable = false;
            }

            if ($isAvailable) {
                $slots[] = [
                    'start' => $slotStart,
                    'end' => $slotEnd,
                    'label' => $slotStart . ' - ' . $slotEnd,
                ];
            }

            $current += ($duration + $buffer) * 60;
        }

        return $this->json(['slots' => $slots]);
    }

    public function bookSlot($slug)
    {
        // Rate limit public booking: max 5 attempts / IP / minute
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        try {
            $recent = (int)(Database::fetch(
                "SELECT COUNT(*) as c FROM booking_appointments WHERE guest_ip = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
                [$ip]
            )['c'] ?? 0);
            if ($recent >= 5) {
                http_response_code(429);
                return $this->json(['error' => 'Quá nhiều yêu cầu. Vui lòng thử lại sau.'], 429);
            }
        } catch (\Exception $e) {}

        $link = Database::fetch(
            "SELECT * FROM booking_links WHERE slug = ? AND is_active = 1",
            [$slug]
        );

        if (!$link) {
            return $this->json(['error' => 'Liên kết không tồn tại'], 404);
        }

        $data = $this->allInput();
        $name = trim($data['contact_name'] ?? '');
        $email = trim($data['contact_email'] ?? '');
        $date = $data['DATE(start_at)'] ?? '';
        $startTime = $data['TIME(start_at)'] ?? '';

        if (empty($name) || empty($email) || empty($date) || empty($startTime)) {
            return $this->json(['error' => 'Vui lòng điền đầy đủ thông tin'], 400);
        }

        $endTime = date('H:i:s', strtotime($startTime) + ($link['duration'] * 60));

        // Check if slot is still available
        $conflict = Database::fetch(
            "SELECT id FROM bookings
             WHERE link_id = ? AND DATE(start_at) = ? AND TIME(start_at) = ? AND status = 'confirmed'",
            [$link['id'], $date, $startTime]
        );

        if ($conflict) {
            return $this->json(['error' => 'Khung giờ này đã được đặt. Vui lòng chọn giờ khác.'], 409);
        }

        Database::query(
            "INSERT INTO bookings (tenant_id, link_id, contact_name, contact_email, contact_phone, note, DATE(start_at), TIME(start_at), TIME(end_at), status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', NOW())",
            [
                $link['tenant_id'],
                $link['id'],
                $name,
                $email,
                trim($data['contact_phone'] ?? ''),
                trim($data['note'] ?? ''),
                $date,
                $startTime,
                $endTime,
            ]
        );

        // Create calendar event for the owner
        try {
            Database::query(
                "INSERT INTO calendar_events (tenant_id, user_id, title, description, start_at, end_at, type, color, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, 'meeting', '#405189', NOW())",
                [
                    $link['tenant_id'],
                    $link['user_id'],
                    'Lịch hẹn: ' . $name,
                    "Khách: $name\nEmail: $email\nSĐT: " . ($data['contact_phone'] ?? '') . "\nGhi chú: " . ($data['note'] ?? ''),
                    $date . ' ' . $startTime,
                    $date . ' ' . $endTime,
                ]
            );
        } catch (\Exception $e) {
            // Calendar table might not have exact columns
        }

        return $this->json([
            'success' => true,
            'message' => 'Đặt lịch thành công!',
            'booking' => [
                'date' => date('d/m/Y', strtotime($date)),
                'time' => substr($startTime, 0, 5) . ' - ' . substr($endTime, 0, 5),
                'duration' => $link['duration'] . ' phút',
            ],
        ]);
    }

    private function generateSlug(string $title): string
    {
        $slug = mb_strtolower($title);
        $slug = preg_replace('/[àáạảãâầấậẩẫăằắặẳẵ]/u', 'a', $slug);
        $slug = preg_replace('/[èéẹẻẽêềếệểễ]/u', 'e', $slug);
        $slug = preg_replace('/[ìíịỉĩ]/u', 'i', $slug);
        $slug = preg_replace('/[òóọỏõôồốộổỗơờớợởỡ]/u', 'o', $slug);
        $slug = preg_replace('/[ùúụủũưừứựửữ]/u', 'u', $slug);
        $slug = preg_replace('/[ỳýỵỷỹ]/u', 'y', $slug);
        $slug = preg_replace('/đ/u', 'd', $slug);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }
}
