<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class SearchController extends Controller
{
    public function index()
    {
        $q = trim($this->input('q') ?? '');

        if (empty($q)) {
            return $this->view('search.index', [
                'q' => '',
                'contacts' => [],
                'companies' => [],
                'deals' => [],
                'tickets' => [],
                'orders' => [],
            ]);
        }

        $search = "%{$q}%";

        // Search contacts
        $contacts = Database::fetchAll(
            "SELECT c.*, comp.name as company_name
             FROM contacts c
             LEFT JOIN companies comp ON c.company_id = comp.id
             WHERE c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?
             ORDER BY c.created_at DESC
             LIMIT 5",
            [$search, $search, $search, $search]
        );

        // Search companies
        $companies = Database::fetchAll(
            "SELECT *
             FROM companies
             WHERE name LIKE ? OR email LIKE ?
             ORDER BY created_at DESC
             LIMIT 5",
            [$search, $search]
        );

        // Search deals
        $deals = Database::fetchAll(
            "SELECT d.*, ds.name as stage_name, c.first_name as contact_first_name, c.last_name as contact_last_name
             FROM deals d
             LEFT JOIN deal_stages ds ON d.stage_id = ds.id
             LEFT JOIN contacts c ON d.contact_id = c.id
             WHERE d.title LIKE ?
             ORDER BY d.created_at DESC
             LIMIT 5",
            [$search]
        );

        // Search tickets
        $tickets = Database::fetchAll(
            "SELECT t.*, c.first_name as contact_first_name, c.last_name as contact_last_name
             FROM tickets t
             LEFT JOIN contacts c ON t.contact_id = c.id
             WHERE t.ticket_code LIKE ? OR t.title LIKE ?
             ORDER BY t.created_at DESC
             LIMIT 5",
            [$search, $search]
        );

        // Search orders
        $orders = Database::fetchAll(
            "SELECT o.*, c.first_name as contact_first_name, c.last_name as contact_last_name
             FROM orders o
             LEFT JOIN contacts c ON o.contact_id = c.id
             WHERE o.order_number LIKE ?
             ORDER BY o.created_at DESC
             LIMIT 5",
            [$search]
        );

        return $this->view('search.index', [
            'q' => $q,
            'contacts' => $contacts,
            'companies' => $companies,
            'deals' => $deals,
            'tickets' => $tickets,
            'orders' => $orders,
        ]);
    }
}
