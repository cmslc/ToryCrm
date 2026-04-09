<?php

namespace App\Services;

use Core\Database;

class MoMoService
{
    private const MOMO_SANDBOX_URL = 'https://test-payment.momo.vn/v2/gateway/api/create';
    private const MOMO_PRODUCTION_URL = 'https://payment.momo.vn/v2/gateway/api/create';

    /**
     * Get MoMo config from integrations table
     */
    private function getConfig(): ?array
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $row = Database::fetch(
            "SELECT config FROM integrations WHERE tenant_id = ? AND provider = 'momo'",
            [$tenantId]
        );
        if (!$row || empty($row['config'])) {
            return null;
        }
        return json_decode($row['config'], true);
    }

    /**
     * Build MoMo payment URL with HMAC SHA256 signature
     */
    public function createPaymentUrl(int $invoiceId, float $amount, string $orderInfo): ?string
    {
        $config = $this->getConfig();
        if (!$config || empty($config['partner_code']) || empty($config['access_key']) || empty($config['secret_key'])) {
            return null;
        }

        $isProduction = ($config['environment'] ?? 'sandbox') === 'production';
        $endpoint = $isProduction ? self::MOMO_PRODUCTION_URL : self::MOMO_SANDBOX_URL;

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $returnUrl = $scheme . '://' . $host . '/payments/momo-return';
        $ipnUrl = $scheme . '://' . $host . '/webhooks/momo';

        $orderId = 'INV' . $invoiceId . '_' . time();
        $requestId = $orderId . '_req';
        $requestType = 'payWithMethod';
        $extraData = base64_encode(json_encode(['invoiceId' => $invoiceId]));
        $intAmount = (int) $amount;

        // Create pending transaction record
        Database::insert('payment_transactions', [
            'invoice_id' => $invoiceId,
            'gateway' => 'momo',
            'transaction_id' => $orderId,
            'amount' => $amount,
            'currency' => 'VND',
            'status' => 'pending',
        ]);

        // Build signature string per MoMo docs
        $rawSignature = 'accessKey=' . $config['access_key']
            . '&amount=' . $intAmount
            . '&extraData=' . $extraData
            . '&ipnUrl=' . $ipnUrl
            . '&orderId=' . $orderId
            . '&orderInfo=' . $orderInfo
            . '&partnerCode=' . $config['partner_code']
            . '&redirectUrl=' . $returnUrl
            . '&requestId=' . $requestId
            . '&requestType=' . $requestType;

        $signature = hash_hmac('sha256', $rawSignature, $config['secret_key']);

        $requestData = [
            'partnerCode' => $config['partner_code'],
            'partnerName' => 'ToryCRM',
            'storeId' => $config['partner_code'],
            'requestId' => $requestId,
            'amount' => $intAmount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $returnUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature,
        ];

        // Call MoMo API to get payment URL
        $response = $this->httpPostJson($endpoint, $requestData);

        if ($response && isset($response['payUrl']) && $response['resultCode'] == 0) {
            return $response['payUrl'];
        }

        return null;
    }

    /**
     * Verify MoMo return and update transaction status
     */
    public function handleReturn(array $params): array
    {
        $config = $this->getConfig();
        if (!$config) {
            return ['success' => false, 'message' => 'Cấu hình MoMo không hợp lệ.'];
        }

        $resultCode = (int)($params['resultCode'] ?? -1);
        $orderId = $params['orderId'] ?? '';

        // Verify signature
        if (!$this->verifySignature($params, $config['access_key'], $config['secret_key'])) {
            return ['success' => false, 'message' => 'Chữ ký không hợp lệ.'];
        }

        $transaction = Database::fetch(
            "SELECT * FROM payment_transactions WHERE transaction_id = ? AND gateway = 'momo'",
            [$orderId]
        );

        if (!$transaction) {
            return ['success' => false, 'message' => 'Giao dịch không tồn tại.'];
        }

        if ($resultCode === 0) {
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

        return ['success' => false, 'message' => 'Thanh toán thất bại. Mã lỗi: ' . $resultCode];
    }

    /**
     * Handle MoMo IPN (webhook notification)
     */
    public function handleIPN(array $params): array
    {
        $config = $this->getConfig();
        if (!$config) {
            return ['resultCode' => 99, 'message' => 'Config not found'];
        }

        if (!$this->verifySignature($params, $config['access_key'], $config['secret_key'])) {
            return ['resultCode' => 97, 'message' => 'Invalid signature'];
        }

        $orderId = $params['orderId'] ?? '';
        $resultCode = (int)($params['resultCode'] ?? -1);

        $transaction = Database::fetch(
            "SELECT * FROM payment_transactions WHERE transaction_id = ? AND gateway = 'momo'",
            [$orderId]
        );

        if (!$transaction) {
            return ['resultCode' => 1, 'message' => 'Order not found'];
        }

        if ($transaction['status'] === 'success') {
            return ['resultCode' => 0, 'message' => 'Already confirmed'];
        }

        if ($resultCode === 0) {
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

            return ['resultCode' => 0, 'message' => 'Confirm Success'];
        }

        Database::update('payment_transactions', [
            'status' => 'failed',
            'gateway_response' => json_encode($params),
        ], 'id = ?', [$transaction['id']]);

        return ['resultCode' => 0, 'message' => 'Confirm Success'];
    }

    /**
     * Verify MoMo HMAC SHA256 signature
     */
    private function verifySignature(array $params, string $accessKey, string $secretKey): bool
    {
        $signature = $params['signature'] ?? '';

        $rawSignature = 'accessKey=' . $accessKey
            . '&amount=' . ($params['amount'] ?? '')
            . '&extraData=' . ($params['extraData'] ?? '')
            . '&message=' . ($params['message'] ?? '')
            . '&orderId=' . ($params['orderId'] ?? '')
            . '&orderInfo=' . ($params['orderInfo'] ?? '')
            . '&orderType=' . ($params['orderType'] ?? '')
            . '&partnerCode=' . ($params['partnerCode'] ?? '')
            . '&payType=' . ($params['payType'] ?? '')
            . '&requestId=' . ($params['requestId'] ?? '')
            . '&responseTime=' . ($params['responseTime'] ?? '')
            . '&resultCode=' . ($params['resultCode'] ?? '')
            . '&transId=' . ($params['transId'] ?? '');

        $checkSignature = hash_hmac('sha256', $rawSignature, $secretKey);

        return hash_equals($checkSignature, $signature);
    }

    /**
     * HTTP POST JSON
     */
    private function httpPostJson(string $url, array $data): ?array
    {
        $jsonData = json_encode($data);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nContent-Length: " . strlen($jsonData) . "\r\n",
                'content' => $jsonData,
                'ignore_errors' => true,
                'timeout' => 30,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return null;
        }

        return json_decode($response, true);
    }
}
