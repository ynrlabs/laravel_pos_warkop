<?php

namespace App\Services\PaymentGateway;

use App\Models\Integration;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Xendit\Xendit;
use Xendit\QRCode;

class XenditService
{
    protected Integration $integration;
    protected bool $isProduction;

    public function __construct(Integration $integration)
    {
        $this->integration = $integration;
        $this->isProduction = $integration->isProduction();
        
        Xendit::setApiKey($integration->getApiKey());
        Xendit::setIsProduction($this->isProduction);
    }

    /**
     * Create QRIS Dynamic transaction
     */
    public function createQrisTransaction(Payment $payment): array
    {
        try {
            $params = [
                'external_id' => $payment->payment_number,
                'type' => 'DYNAMIC',
                'amount' => (int) $payment->amount,
                'callback_url' => route('payment.xendit.callback', ['payment' => $payment->id]),
            ];

            $qrCode = QRCode::create($params);

            return [
                'success' => true,
                'qr_string' => $qrCode['qr_string'] ?? null,
                'qr_image_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrCode['qr_string'] ?? ''),
                'provider_transaction_id' => $qrCode['id'] ?? null,
                'expired_at' => isset($qrCode['expiration_date']) 
                    ? \Carbon\Carbon::parse($qrCode['expiration_date']) 
                    : now()->addMinutes(5),
            ];

        } catch (\Exception $e) {
            Log::error('Xendit QRIS Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create E-Wallet transaction (DANA, OVO, ShopeePay, LinkAja)
     */
    public function createEwalletTransaction(Payment $payment, string $ewalletType): array
    {
        $channelCodeMap = [
            'ewallet_dana' => 'DANA',
            'ewallet_ovo' => 'OVO',
            'ewallet_shopeepay' => 'SHOPEEPAY',
            'ewallet_linkaja' => 'LINKAJA',
        ];

        $channelCode = $channelCodeMap[$ewalletType] ?? 'DANA';

        try {
            $params = [
                'external_id' => $payment->payment_number,
                'amount' => (int) $payment->amount,
                'phone' => $payment->ewallet_phone ?? $payment->order->customer_phone ?? '',
                'ewallet_type' => $channelCode,
                'callback_url' => route('payment.xendit.callback', ['payment' => $payment->id]),
                'redirect_url' => route('payment.xendit.return', ['payment' => $payment->id]),
            ];

            // Use Xendit EWallet API
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->integration->getApiKey() . ':'),
            ])->post($this->getApiUrl() . '/ewallets/charge', $params);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'redirect_url' => $data['checkout_url'] ?? null,
                    'deeplink_redirect_url' => $data['deeplink_url'] ?? null,
                    'provider_transaction_id' => $data['id'] ?? null,
                    'expired_at' => isset($data['expired_at']) 
                        ? \Carbon\Carbon::parse($data['expired_at']) 
                        : now()->addMinutes(15),
                ];
            }

            throw new \Exception('Failed to create e-wallet transaction: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Xendit E-Wallet Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus(string $externalId): array
    {
        try {
            // For QRIS
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->integration->getApiKey() . ':'),
            ])->get($this->getApiUrl() . '/qr-codes/' . $externalId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            // Try e-wallet
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->integration->getApiKey() . ':'),
            ])->get($this->getApiUrl() . '/ewallets/charges/' . $externalId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get status: ' . $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('Xendit Status Check Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle notification callback
     */
    public function handleNotification(array $data): array
    {
        $event = $data['event'] ?? null;
        $externalId = $data['id'] ?? $data['external_id'] ?? null;

        if (!$externalId) {
            return [
                'success' => false,
                'error' => 'Missing external_id',
            ];
        }

        $payment = Payment::where('payment_number', $externalId)->first();
        
        if (!$payment) {
            return [
                'success' => false,
                'error' => 'Payment not found',
            ];
        }

        // Map Xendit status to our status
        $statusMap = [
            'qr_code.payment' => 'paid',
            'qr_code.expired' => 'expired',
            'ewallet.charge_success' => 'paid',
            'ewallet.charge_failed' => 'failed',
            'ewallet.charge_expired' => 'expired',
            'ewallet.refund_success' => 'refunded',
        ];

        $newStatus = $statusMap[$event] ?? 'pending';

        $payment->update([
            'status' => $newStatus,
            'provider_transaction_id' => $data['id'] ?? $payment->provider_transaction_id,
            'paid_at' => in_array($newStatus, ['paid']) ? now() : $payment->paid_at,
        ]);

        // Update order status if payment is completed
        if ($newStatus === 'paid') {
            $payment->order->update(['status' => 'paid']);
        }

        return [
            'success' => true,
            'payment' => $payment,
            'status' => $newStatus,
        ];
    }

    /**
     * Get API URL based on environment
     */
    protected function getApiUrl(): string
    {
        return $this->isProduction 
            ? 'https://api.xendit.co' 
            : 'https://api.xendit.co';
    }

    /**
     * Refund transaction
     */
    public function refund(string $chargeId, float $amount, string $reason): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->integration->getApiKey() . ':'),
            ])->post($this->getApiUrl() . '/ewallets/charges/' . $chargeId . '/refund', [
                'amount' => (int) $amount,
                'reason' => $reason,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Refund failed: ' . $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('Xendit Refund Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}