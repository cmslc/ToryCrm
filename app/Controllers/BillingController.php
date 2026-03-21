<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class BillingController extends Controller
{
    public function index()
    {
        $tenantId = $this->tenantId();

        // Current subscription with plan info
        $subscription = Database::fetch(
            "SELECT s.*, p.name as plan_name, p.slug as plan_slug, p.price_monthly, p.price_yearly,
                    p.max_users, p.max_contacts, p.max_deals, p.max_storage_mb, p.features
             FROM subscriptions s
             JOIN plans p ON s.plan_id = p.id
             WHERE s.tenant_id = ? AND s.status IN ('active', 'trialing')
             ORDER BY s.created_at DESC LIMIT 1",
            [$tenantId]
        );

        // Usage stats
        $usersCount = Database::fetch("SELECT COUNT(*) as count FROM users WHERE tenant_id = ?", [$tenantId])['count'] ?? 0;
        $contactsCount = Database::fetch("SELECT COUNT(*) as count FROM contacts WHERE tenant_id = ?", [$tenantId])['count'] ?? 0;
        $dealsCount = Database::fetch("SELECT COUNT(*) as count FROM deals WHERE tenant_id = ?", [$tenantId])['count'] ?? 0;

        $usage = [
            'users' => (int) $usersCount,
            'contacts' => (int) $contactsCount,
            'deals' => (int) $dealsCount,
        ];

        // Recent invoices
        $invoices = Database::fetchAll(
            "SELECT * FROM invoices WHERE tenant_id = ? ORDER BY created_at DESC LIMIT 5",
            [$tenantId]
        );

        return $this->view('billing.index', [
            'subscription' => $subscription,
            'usage' => $usage,
            'invoices' => $invoices,
        ]);
    }

    public function plans()
    {
        $plans = Database::fetchAll(
            "SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC, price_monthly ASC"
        );

        $currentSubscription = Database::fetch(
            "SELECT * FROM subscriptions WHERE tenant_id = ? AND status IN ('active', 'trialing') ORDER BY created_at DESC LIMIT 1",
            [$this->tenantId()]
        );

        return $this->view('billing.plans', [
            'plans' => $plans,
            'currentSubscription' => $currentSubscription,
        ]);
    }

    public function subscribe()
    {
        if (!$this->isPost()) {
            return $this->redirect('billing/plans');
        }

        $planId = (int) $this->input('plan_id');
        $billingCycle = $this->input('billing_cycle') ?: 'monthly';
        $tenantId = $this->tenantId();

        $plan = Database::fetch("SELECT * FROM plans WHERE id = ? AND is_active = 1", [$planId]);
        if (!$plan) {
            $this->setFlash('error', 'Goi dich vu khong hop le.');
            return $this->back();
        }

        $amount = $billingCycle === 'yearly' ? $plan['price_yearly'] : $plan['price_monthly'];
        $startDate = date('Y-m-d');
        $endDate = $billingCycle === 'yearly'
            ? date('Y-m-d', strtotime('+1 year'))
            : date('Y-m-d', strtotime('+1 month'));

        // Cancel existing active subscriptions
        Database::query(
            "UPDATE subscriptions SET status = 'cancelled', cancelled_at = NOW() WHERE tenant_id = ? AND status IN ('active', 'trialing')",
            [$tenantId]
        );

        // Create new subscription
        Database::insert('subscriptions', [
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'billing_cycle' => $billingCycle,
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'amount' => $amount,
            'created_by' => $this->userId(),
        ]);

        // Create invoice
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        Database::insert('invoices', [
            'tenant_id' => $tenantId,
            'invoice_number' => $invoiceNumber,
            'amount' => $amount,
            'status' => 'sent',
            'due_date' => date('Y-m-d', strtotime('+7 days')),
            'description' => 'Goi ' . $plan['name'] . ' - ' . ($billingCycle === 'yearly' ? 'Hang nam' : 'Hang thang'),
            'created_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Dang ky goi ' . $plan['name'] . ' thanh cong!');
        return $this->redirect('billing');
    }

    public function invoices()
    {
        $invoices = Database::fetchAll(
            "SELECT * FROM invoices WHERE tenant_id = ? ORDER BY created_at DESC",
            [$this->tenantId()]
        );

        return $this->view('billing.invoices', [
            'invoices' => $invoices,
        ]);
    }

    public function cancelSubscription()
    {
        if (!$this->isPost()) {
            return $this->redirect('billing');
        }

        Database::query(
            "UPDATE subscriptions SET status = 'cancelled', cancelled_at = NOW() WHERE tenant_id = ? AND status IN ('active', 'trialing')",
            [$this->tenantId()]
        );

        $this->setFlash('success', 'Da huy goi dich vu thanh cong.');
        return $this->redirect('billing');
    }
}
