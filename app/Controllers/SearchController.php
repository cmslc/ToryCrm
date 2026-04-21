<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class SearchController extends Controller
{
    public function index()
    {
        // Rate limit to prevent scraping: max 60 searches / user / minute
        $uid = $_SESSION['user']['id'] ?? 0;
        if ($uid && !\App\Services\RateLimiter::attempt('search:' . $uid, 60, 1)) {
            if ($this->input('format') === 'json') {
                return $this->json(['error' => 'Quá nhiều yêu cầu. Chờ 1 phút.'], 429);
            }
            $this->setFlash('error', 'Quá nhiều yêu cầu tìm kiếm. Chờ 1 phút.');
            return $this->redirect('dashboard');
        }
        $q = trim($this->input('q') ?? '');

        if (empty($q)) {
            if ($this->input('format') === 'json') {
                return $this->json([
                    'q' => '',
                    'contacts' => [],
                    'companies' => [],
                    'deals' => [],
                    'tickets' => [],
                    'orders' => [],
                ]);
            }

            return $this->view('search.index', [
                'q' => '',
                'contacts' => [],
                'companies' => [],
                'deals' => [],
                'tickets' => [],
                'orders' => [],
            ]);
        }

        $tid = Database::tenantId();
        $search = "%{$q}%";

        // Scope by user's visible owners (unless admin/view_all for the respective module)
        $contactScope = $this->getOwnerScopeSql('c.owner_id', 'contacts');
        $dealScope    = $this->getOwnerScopeSql('d.owner_id', 'deals');
        $ticketScope  = $this->getOwnerScopeSql('t.assigned_to', 'tickets');
        $orderScope   = $this->getOwnerScopeSql('o.owner_id', 'orders');

        // Search contacts
        $contacts = Database::fetchAll(
            "SELECT c.*, comp.name as company_name
             FROM contacts c
             LEFT JOIN companies comp ON c.company_id = comp.id
             WHERE c.tenant_id = ? AND c.is_deleted = 0
               AND (c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ? OR c.company_name LIKE ?)
               {$contactScope}
             ORDER BY c.created_at DESC
             LIMIT 5",
            [$tid, $search, $search, $search, $search, $search]
        );

        // Search companies
        $companies = Database::fetchAll(
            "SELECT *
             FROM companies
             WHERE tenant_id = ?
               AND (name LIKE ? OR email LIKE ?)
             ORDER BY created_at DESC
             LIMIT 5",
            [$tid, $search, $search]
        );

        // Search deals
        $deals = Database::fetchAll(
            "SELECT d.*, ds.name as stage_name, c.first_name as contact_first_name, c.last_name as contact_last_name
             FROM deals d
             LEFT JOIN deal_stages ds ON d.stage_id = ds.id
             LEFT JOIN contacts c ON d.contact_id = c.id
             WHERE d.tenant_id = ? AND d.title LIKE ?
               {$dealScope}
             ORDER BY d.created_at DESC
             LIMIT 5",
            [$tid, $search]
        );

        // Search tickets
        $tickets = Database::fetchAll(
            "SELECT t.*, c.first_name as contact_first_name, c.last_name as contact_last_name
             FROM tickets t
             LEFT JOIN contacts c ON t.contact_id = c.id
             WHERE t.tenant_id = ?
               AND (t.ticket_code LIKE ? OR t.title LIKE ?)
               {$ticketScope}
             ORDER BY t.created_at DESC
             LIMIT 5",
            [$tid, $search, $search]
        );

        // Search orders
        $orders = Database::fetchAll(
            "SELECT o.*, c.first_name as contact_first_name, c.last_name as contact_last_name
             FROM orders o
             LEFT JOIN contacts c ON o.contact_id = c.id
             WHERE o.tenant_id = ? AND o.is_deleted = 0
               AND o.order_number LIKE ?
               {$orderScope}
             ORDER BY o.created_at DESC
             LIMIT 5",
            [$tid, $search]
        );

        $results = [
            'q' => $q,
            'contacts' => $contacts,
            'companies' => $companies,
            'deals' => $deals,
            'tickets' => $tickets,
            'orders' => $orders,
        ];

        if ($this->input('format') === 'json') {
            return $this->json($results);
        }

        return $this->view('search.index', $results);
    }
}
