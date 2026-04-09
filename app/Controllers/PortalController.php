<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class PortalController extends Controller
{
    private function portalContact(): ?array
    {
        return $_SESSION['portal_contact'] ?? null;
    }

    private function requirePortalAuth(): ?array
    {
        $contact = $this->portalContact();
        if (!$contact) {
            $this->redirect('portal/login');
            return null;
        }
        return $contact;
    }

    public function login()
    {
        if ($this->portalContact()) {
            return $this->redirect('portal');
        }

        return $this->view('portal.login');
    }

    public function authenticate()
    {
        if (!$this->isPost()) {
            return $this->redirect('portal/login');
        }

        $token = trim($this->input('token') ?? '');
        $password = trim($this->input('password') ?? '');

        if (empty($token) || empty($password)) {
            $this->setFlash('error', 'Vui lòng nhập mã khách hàng và mật khẩu.');
            return $this->redirect('portal/login');
        }

        $contact = Database::fetch(
            "SELECT * FROM contacts WHERE portal_token = ? AND portal_password = ? AND portal_active = 1",
            [$token, md5($password)]
        );

        if (!$contact) {
            $this->setFlash('error', 'Thông tin đăng nhập không đúng hoặc tài khoản chưa được kích hoạt.');
            return $this->redirect('portal/login');
        }

        $_SESSION['portal_contact'] = [
            'id' => $contact['id'],
            'first_name' => $contact['first_name'],
            'last_name' => $contact['last_name'],
            'email' => $contact['email'],
            'phone' => $contact['phone'] ?? '',
        ];

        return $this->redirect('portal');
    }

    public function dashboard()
    {
        $contact = $this->requirePortalAuth();
        if (!$contact) return;

        $contactId = $contact['id'];

        $contactInfo = Database::fetch("SELECT * FROM contacts WHERE id = ?", [$contactId]);

        $orderCount = Database::fetch(
            "SELECT COUNT(*) as count FROM orders WHERE contact_id = ?",
            [$contactId]
        )['count'] ?? 0;

        $ticketCount = Database::fetch(
            "SELECT COUNT(*) as count FROM tickets WHERE contact_id = ?",
            [$contactId]
        )['count'] ?? 0;

        $openTickets = Database::fetch(
            "SELECT COUNT(*) as count FROM tickets WHERE contact_id = ? AND status IN ('open','in_progress','waiting')",
            [$contactId]
        )['count'] ?? 0;

        $recentOrders = Database::fetchAll(
            "SELECT o.*, u.name as owner_name
             FROM orders o
             LEFT JOIN users u ON o.created_by = u.id
             WHERE o.contact_id = ?
             ORDER BY o.created_at DESC
             LIMIT 5",
            [$contactId]
        );

        $recentTickets = Database::fetchAll(
            "SELECT t.*
             FROM tickets t
             WHERE t.contact_id = ?
             ORDER BY t.created_at DESC
             LIMIT 5",
            [$contactId]
        );

        return $this->view('portal.dashboard', [
            'contact' => $contactInfo,
            'portalContact' => $contact,
            'orderCount' => $orderCount,
            'ticketCount' => $ticketCount,
            'openTickets' => $openTickets,
            'recentOrders' => $recentOrders,
            'recentTickets' => $recentTickets,
        ]);
    }

    public function orders()
    {
        $contact = $this->requirePortalAuth();
        if (!$contact) return;

        $orders = Database::fetchAll(
            "SELECT o.*, u.name as owner_name
             FROM orders o
             LEFT JOIN users u ON o.created_by = u.id
             WHERE o.contact_id = ?
             ORDER BY o.created_at DESC",
            [$contact['id']]
        );

        return $this->view('portal.orders', [
            'orders' => $orders,
            'portalContact' => $contact,
        ]);
    }

    public function tickets()
    {
        $contact = $this->requirePortalAuth();
        if (!$contact) return;

        $tickets = Database::fetchAll(
            "SELECT t.*, tc.name as category_name, tc.color as category_color
             FROM tickets t
             LEFT JOIN ticket_categories tc ON t.category_id = tc.id
             WHERE t.contact_id = ?
             ORDER BY t.created_at DESC",
            [$contact['id']]
        );

        return $this->view('portal.tickets', [
            'tickets' => $tickets,
            'portalContact' => $contact,
        ]);
    }

    public function createTicket()
    {
        $contact = $this->requirePortalAuth();
        if (!$contact) return;

        $categories = Database::fetchAll("SELECT * FROM ticket_categories ORDER BY name");

        return $this->view('portal.create-ticket', [
            'categories' => $categories,
            'portalContact' => $contact,
        ]);
    }

    public function storeTicket()
    {
        if (!$this->isPost()) {
            return $this->redirect('portal/tickets');
        }

        $contact = $this->requirePortalAuth();
        if (!$contact) return;

        $data = $this->allInput();
        $title = trim($data['title'] ?? '');

        if (empty($title)) {
            $this->setFlash('error', 'Tiêu đề ticket không được để trống.');
            return $this->redirect('portal/tickets/create');
        }

        $ticketCode = 'TK-' . strtoupper(substr(md5(uniqid()), 0, 8));

        Database::insert('tickets', [
            'ticket_code' => $ticketCode,
            'title' => $title,
            'content' => trim($data['content'] ?? ''),
            'category_id' => !empty($data['category_id']) ? $data['category_id'] : null,
            'contact_id' => $contact['id'],
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'open',
            'contact_email' => $contact['email'] ?? '',
        ]);

        $this->setFlash('success', "Ticket {$ticketCode} đã được tạo thành công.");
        return $this->redirect('portal/tickets');
    }

    public function logout()
    {
        unset($_SESSION['portal_contact']);
        $this->setFlash('success', 'Đã đăng xuất thành công.');
        return $this->redirect('portal/login');
    }
}
