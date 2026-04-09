<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Services\VNPayService;
use App\Services\MoMoService;

class PaymentController extends Controller
{
    /**
     * GET - Show payment method selection for an invoice
     */
    public function checkout($invoiceId)
    {
        $invoice = Database::fetch(
            "SELECT i.*, t.name as tenant_name
             FROM invoices i
             LEFT JOIN tenants t ON i.tenant_id = t.id
             WHERE i.id = ?",
            [$invoiceId]
        );

        if (!$invoice) {
            $this->setFlash('error', 'Hóa đơn không tồn tại.');
            return $this->redirect('billing/invoices');
        }

        // Check VNPay and MoMo configs
        $tenantId = $this->tenantId();
        $vnpayConfig = Database::fetch(
            "SELECT config FROM integrations WHERE tenant_id = ? AND provider = 'vnpay' AND is_active = 1",
            [$tenantId]
        );
        $momoConfig = Database::fetch(
            "SELECT config FROM integrations WHERE tenant_id = ? AND provider = 'momo' AND is_active = 1",
            [$tenantId]
        );

        return $this->view('payments.checkout', [
            'invoice' => $invoice,
            'hasVNPay' => $vnpayConfig !== null,
            'hasMoMo' => $momoConfig !== null,
        ]);
    }

    /**
     * POST - Create VNPay payment URL and redirect
     */
    public function processVNPay($invoiceId)
    {
        if (!$this->isPost()) {
            return $this->redirect("payments/{$invoiceId}/checkout");
        }

        $invoice = Database::fetch("SELECT * FROM invoices WHERE id = ?", [$invoiceId]);
        if (!$invoice) {
            $this->setFlash('error', 'Hóa đơn không tồn tại.');
            return $this->redirect('billing/invoices');
        }

        $vnpay = new VNPayService();
        $orderInfo = 'Thanh toan hoa don #' . ($invoice['invoice_number'] ?? $invoiceId);
        $paymentUrl = $vnpay->createPaymentUrl($invoiceId, (float)$invoice['total_amount'], $orderInfo);

        if (!$paymentUrl) {
            $this->setFlash('error', 'Không thể tạo liên kết thanh toán VNPay. Vui lòng kiểm tra cấu hình.');
            return $this->redirect("payments/{$invoiceId}/checkout");
        }

        header("Location: {$paymentUrl}");
        exit;
    }

    /**
     * POST - Create MoMo payment URL and redirect
     */
    public function processMoMo($invoiceId)
    {
        if (!$this->isPost()) {
            return $this->redirect("payments/{$invoiceId}/checkout");
        }

        $invoice = Database::fetch("SELECT * FROM invoices WHERE id = ?", [$invoiceId]);
        if (!$invoice) {
            $this->setFlash('error', 'Hóa đơn không tồn tại.');
            return $this->redirect('billing/invoices');
        }

        $momo = new MoMoService();
        $orderInfo = 'Thanh toan hoa don #' . ($invoice['invoice_number'] ?? $invoiceId);
        $paymentUrl = $momo->createPaymentUrl($invoiceId, (float)$invoice['total_amount'], $orderInfo);

        if (!$paymentUrl) {
            $this->setFlash('error', 'Không thể tạo liên kết thanh toán MoMo. Vui lòng kiểm tra cấu hình.');
            return $this->redirect("payments/{$invoiceId}/checkout");
        }

        header("Location: {$paymentUrl}");
        exit;
    }

    /**
     * GET - Handle VNPay return (public route)
     */
    public function vnpayReturn()
    {
        $vnpay = new VNPayService();
        $result = $vnpay->handleReturn($_GET);

        if ($result['success']) {
            $noLayout = true;
            return $this->view('payments.success', [
                'message' => $result['message'],
                'transaction' => $result['transaction'] ?? null,
                'gateway' => 'VNPay',
                'noLayout' => true,
            ]);
        }

        $noLayout = true;
        return $this->view('payments.failed', [
            'message' => $result['message'],
            'gateway' => 'VNPay',
            'noLayout' => true,
        ]);
    }

    /**
     * GET - Handle MoMo return (public route)
     */
    public function momoReturn()
    {
        $momo = new MoMoService();
        $result = $momo->handleReturn($_GET);

        if ($result['success']) {
            $noLayout = true;
            return $this->view('payments.success', [
                'message' => $result['message'],
                'transaction' => $result['transaction'] ?? null,
                'gateway' => 'MoMo',
                'noLayout' => true,
            ]);
        }

        $noLayout = true;
        return $this->view('payments.failed', [
            'message' => $result['message'],
            'gateway' => 'MoMo',
            'noLayout' => true,
        ]);
    }

    /**
     * POST - Handle VNPay IPN webhook (public route)
     */
    public function vnpayIPN()
    {
        $vnpay = new VNPayService();
        $result = $vnpay->handleIPN($_GET);
        return $this->json($result);
    }

    /**
     * POST - Handle MoMo IPN webhook (public route)
     */
    public function momoIPN()
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $momo = new MoMoService();
        $result = $momo->handleIPN($input);
        return $this->json($result);
    }

    /**
     * GET - VNPay settings page
     */
    public function vnpaySettings()
    {
        $tenantId = $this->tenantId();
        $integration = Database::fetch(
            "SELECT * FROM integrations WHERE tenant_id = ? AND provider = 'vnpay'",
            [$tenantId]
        );
        $config = $integration ? json_decode($integration['config'] ?? '{}', true) : [];

        return $this->view('integrations.vnpay', [
            'config' => $config,
            'integration' => $integration,
        ]);
    }

    /**
     * POST - Save VNPay settings
     */
    public function saveVNPaySettings()
    {
        if (!$this->isPost()) {
            return $this->redirect('integrations/vnpay');
        }

        $tenantId = $this->tenantId();
        $data = $this->allInput();

        $config = json_encode([
            'tmn_code' => trim($data['tmn_code'] ?? ''),
            'hash_secret' => trim($data['hash_secret'] ?? ''),
            'environment' => ($data['environment'] ?? 'sandbox') === 'production' ? 'production' : 'sandbox',
        ]);

        $existing = Database::fetch(
            "SELECT id FROM integrations WHERE tenant_id = ? AND provider = 'vnpay'",
            [$tenantId]
        );

        if ($existing) {
            Database::update('integrations', [
                'config' => $config,
                'name' => 'VNPay',
                'is_active' => 1,
            ], 'id = ?', [$existing['id']]);
        } else {
            Database::insert('integrations', [
                'tenant_id' => $tenantId,
                'provider' => 'vnpay',
                'name' => 'VNPay',
                'config' => $config,
                'is_active' => 1,
                'created_by' => $this->userId(),
            ]);
        }

        $this->setFlash('success', 'Đã lưu cấu hình VNPay.');
        return $this->redirect('integrations/vnpay');
    }

    /**
     * GET - MoMo settings page
     */
    public function momoSettings()
    {
        $tenantId = $this->tenantId();
        $integration = Database::fetch(
            "SELECT * FROM integrations WHERE tenant_id = ? AND provider = 'momo'",
            [$tenantId]
        );
        $config = $integration ? json_decode($integration['config'] ?? '{}', true) : [];

        return $this->view('integrations.momo', [
            'config' => $config,
            'integration' => $integration,
        ]);
    }

    /**
     * POST - Save MoMo settings
     */
    public function saveMoMoSettings()
    {
        if (!$this->isPost()) {
            return $this->redirect('integrations/momo');
        }

        $tenantId = $this->tenantId();
        $data = $this->allInput();

        $config = json_encode([
            'partner_code' => trim($data['partner_code'] ?? ''),
            'access_key' => trim($data['access_key'] ?? ''),
            'secret_key' => trim($data['secret_key'] ?? ''),
            'environment' => ($data['environment'] ?? 'sandbox') === 'production' ? 'production' : 'sandbox',
        ]);

        $existing = Database::fetch(
            "SELECT id FROM integrations WHERE tenant_id = ? AND provider = 'momo'",
            [$tenantId]
        );

        if ($existing) {
            Database::update('integrations', [
                'config' => $config,
                'name' => 'MoMo',
                'is_active' => 1,
            ], 'id = ?', [$existing['id']]);
        } else {
            Database::insert('integrations', [
                'tenant_id' => $tenantId,
                'provider' => 'momo',
                'name' => 'MoMo',
                'config' => $config,
                'is_active' => 1,
                'created_by' => $this->userId(),
            ]);
        }

        $this->setFlash('success', 'Đã lưu cấu hình MoMo.');
        return $this->redirect('integrations/momo');
    }
}
