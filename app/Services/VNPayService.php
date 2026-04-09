<?php

namespace App\Services;

use Core\Database;

class VNPayService
{
    private const VNPAY_URL = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
    private const VNPAY_PRODUCTION_URL = 'https://pay.vnpay.vn/vpcpay.html';

    /**
     * Get VNPay config from integrations table
     */
    private function getConfig(): ?array
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $row = Database::fetch(
            "SELECT config FROM integrations WHERE tenant_id = ? AND provider = 'vnpay'",
            [$tenantId]
        );
        if (!$row || empty($row['config'])) {
            return null;
        }
        return json_decode($row['config'], true);
    }

    /**
     * Build VNPay payment URL with secure hash
     */
    public function createPaymentUrl(int $invoiceId, float $amount, string $orderInfo): ?string
    {
        $config = $this->getConfig();
        if (!$config || empty($config['tmn_code']) || empty($config['hash_secret'])) {
            return null;
        }

        $isProduction = ($config['environment'] ?? 'sandbox') === 'production';
        $baseUrl = $isProduction ? self::VNPAY_PRODUCTION_URL : self::VNPAY_URL;

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $returnUrl = $scheme . '://' . $host . '/payments/vnpay-return';

        $txnRef = 'INV' . $invoiceId . '_' . time();

        // Create pending transaction record
        Database::insert('payment_transactions', [
            'invoice_id' => $invoiceId,
            'gateway' => 'vnpay',
            'transaction_id' => $txnRef,
            'amount' => $amount,
            'currency' => 'VND',
            'status' => 'pending',
        ]);

        $inputData = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $config['tmn_code'],
            'vnp_Amount' => (int)($amount * 100), // VNPay uses amount * 100
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'vnp_Locale' => 'vn',
            'vnp_OrderInfo' => $orderInfo,
            'vnp_OrderType' => 'billpayment',
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_TxnRef' => $txnRef,
        ];

        // Sort by key
        ksort($inputData);

        $hashData = '';
        $query = '';
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i === 1) {
                $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashData .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . '=' . urlencode($value) . '&';
        }

        $secureHash = hash_hmac('sha512', $hashData, $config['hash_secret']);
        $query .= 'vnp_SecureHash=' . $secureHash;

        return $baseUrl . '?' . $query;
    }

    /**
     * Verify return URL hash and update transaction status
     */
    public function handleReturn(array $params): array
    {
        $config = $this->getConfig();
        if (!$config) {
            return ['success' => false, 'message' => 'Cấu hình VNPay không hợp lệ.'];
        }

        $secureHash = $params['vnp_SecureHash'] ?? '';
        unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);

        ksort($params);
        $hashData = '';
        $i = 0;
        foreach ($params as $key => $value) {
            if (str_starts_with($key, 'vnp_')) {
                if ($i === 1) {
                    $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
                } else {
                    $hashData .= urlencode($key) . '=' . urlencode($value);
                    $i = 1;
                }
            }
        }

        $checkHash = hash_hmac('sha512', $hashData, $config['hash_secret']);

        if ($secureHash !== $checkHash) {
            return ['success' => false, 'message' => 'Chữ ký không hợp lệ.'];
        }

        $responseCode = $params['vnp_ResponseCode'] ?? '99';
        $txnRef = $params['vnp_TxnRef'] ?? '';

        $transaction = Database::fetch(
            "SELECT * FROM payment_transactions WHERE transaction_id = ? AND gateway = 'vnpay'",
            [$txnRef]
        );

        if (!$transaction) {
            return ['success' => false, 'message' => 'Giao dịch không tồn tại.'];
        }

        if ($responseCode === '00') {
            Database::update('payment_transactions', [
                'status' => 'success',
                'paid_at' => date('Y-m-d H:i:s'),
                'gateway_response' => json_encode($params),
            ], 'id = ?', [$transaction['id']]);

            return [
                'success' => true,
                'message' => 'Thanh toán thành công.',
                'transaction' => $transaction,
            ];
        }

        Database::update('payment_transactions', [
            'status' => 'failed',
            'gateway_response' => json_encode($params),
        ], 'id = ?', [$transaction['id']]);

        return ['success' => false, 'message' => 'Thanh toán thất bại. Mã lỗi: ' . $responseCode];
    }

    /**
     * Handle VNPay IPN (Instant Payment Notification) webhook
     */
    public function handleIPN(array $params): array
    {
        $config = $this->getConfig();
        if (!$config) {
            return ['RspCode' => '99', 'Message' => 'Config not found'];
        }

        $secureHash = $params['vnp_SecureHash'] ?? '';
        unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);

        ksort($params);
        $hashData = '';
        $i = 0;
        foreach ($params as $key => $value) {
            if (str_starts_with($key, 'vnp_')) {
                if ($i === 1) {
                    $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
                } else {
                    $hashData .= urlencode($key) . '=' . urlencode($value);
                    $i = 1;
                }
            }
        }

        $checkHash = hash_hmac('sha512', $hashData, $config['hash_secret']);

        if ($secureHash !== $checkHash) {
            return ['RspCode' => '97', 'Message' => 'Invalid Checksum'];
        }

        $txnRef = $params['vnp_TxnRef'] ?? '';
        $transaction = Database::fetch(
            "SELECT * FROM payment_transactions WHERE transaction_id = ? AND gateway = 'vnpay'",
            [$txnRef]
        );

        if (!$transaction) {
            return ['RspCode' => '01', 'Message' => 'Order not found'];
        }

        if ($transaction['status'] === 'success') {
            return ['RspCode' => '02', 'Message' => 'Order already confirmed'];
        }

        $responseCode = $params['vnp_ResponseCode'] ?? '99';
        $vnpAmount = (int)($params['vnp_Amount'] ?? 0) / 100;

        if ((int)$vnpAmount !== (int)$transaction['amount']) {
            return ['RspCode' => '04', 'Message' => 'Invalid amount'];
        }

        if ($responseCode === '00') {
            Database::update('payment_transactions', [
                'status' => 'success',
                'paid_at' => date('Y-m-d H:i:s'),
                'gateway_response' => json_encode($params),
            ], 'id = ?', [$transaction['id']]);

            // Update invoice status to paid
            if ($transaction['invoice_id']) {
                Database::update('invoices', [
                    'status' => 'paid',
                    'paid_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$transaction['invoice_id']]);
            }

            return ['RspCode' => '00', 'Message' => 'Confirm Success'];
        }

        Database::update('payment_transactions', [
            'status' => 'failed',
            'gateway_response' => json_encode($params),
        ], 'id = ?', [$transaction['id']]);

        return ['RspCode' => '00', 'Message' => 'Confirm Success'];
    }
}
