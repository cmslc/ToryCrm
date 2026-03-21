<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifModel = new Notification();
        $page = max(1, (int) $this->input('page') ?: 1);

        $notifications = $notifModel->getForUser($this->userId(), $page, 20);

        return $this->view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function unread()
    {
        $notifModel = new Notification();
        $notifications = $notifModel->getUnread($this->userId(), 20);
        $count = $notifModel->getUnreadCount($this->userId());

        return $this->json([
            'count' => $count,
            'notifications' => $notifications,
        ]);
    }

    public function markRead($id)
    {
        $notif = Database::fetch("SELECT * FROM notifications WHERE id = ? AND user_id = ?", [$id, $this->userId()]);

        if (!$notif) {
            $this->setFlash('error', 'Thông báo không tồn tại.');
            return $this->redirect('notifications');
        }

        $notifModel = new Notification();
        $notifModel->markAsRead($id);

        // If has link, redirect to that link
        if (!empty($notif['link'])) {
            return $this->redirect($notif['link']);
        }

        return $this->redirect('notifications');
    }

    public function markAllRead()
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $notifModel = new Notification();
        $notifModel->markAllRead($this->userId());

        if ($this->input('redirect')) {
            $this->setFlash('success', 'Đã đánh dấu tất cả là đã đọc.');
            return $this->redirect('notifications');
        }

        return $this->json(['success' => true]);
    }

    public function delete($id)
    {
        $notif = Database::fetch("SELECT * FROM notifications WHERE id = ? AND user_id = ?", [$id, $this->userId()]);

        if (!$notif) {
            $this->setFlash('error', 'Thông báo không tồn tại.');
            return $this->redirect('notifications');
        }

        Database::delete('notifications', 'id = ?', [$id]);

        $this->setFlash('success', 'Xóa thông báo thành công.');
        return $this->redirect('notifications');
    }
}
