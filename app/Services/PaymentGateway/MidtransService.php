<?php

namespace App\Services\PaymentGateway;

use App\Models\Integration;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    protected Integration $integration;
    protected bool $isProduction;

    public function __construct(Integration $integration)
    {
        $this->integration = $integration;
        $this->isProduction = $integration->isProduction();
        
        Config::$serverKey = $integration->getServerKey();
        Config::$clientKey = $integration->getClientKey();
        Config::$isProduction = $this->isProduction;
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create QRIS Dynamic transaction
     */
    public function createQrisTransaction(Payment $payment): array
    {
        $params = [
            'transaction_details' => [
                'order_id' => $payment->payment_number,
                'gross_amount' => (int) $payment->amount,
            ],
            'customer_details' => [
                'first_name' => $payment->order->customer_name ?? 'Customer',
                'phone' => $payment->ewallet_phone ?? $payment->order->customer_phone ?? '',
            ],
            'item_details' => $this->buildItemDetails($payment->order),
            'enabled_payments' => ['qris'],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'minute',
                'duration' => 5,
            ],
            'callbacks' => [
                'finish' => route('payment.midtrans.callback', ['payment' => $payment->id]),
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            
            // Get QR code from Midtrans
            $response = Http::withBasicAuth($this->integration->getServerKey(), '')
                ->post($this->getApiUrl() . '/v2/qris/charge', [
                    'payment_type' => 'qris',
                    'transaction_details' => $params['transaction_details'],
                    'customer_details' => $params['customer_details'],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'snap_token' => $snapToken,
                    'redirect_url' => $data['actions'][0]['url'] ?? null,
                    'qr_string' => $data['qr_string'] ?? null,
                    'qr_image_url' => $data['qr_image_url'] ?? null,
                    'provider_transaction_id' => $data['transaction_id'] ?? null,
                    'expired_at' => $data['expiry_time'] ?? now()->addMinutes(5),
                ];
            }

            throw new \Exception('Failed to create QRIS transaction: ' . $response->body());
            
        } catch (\Exception $e) {
            Log::error('Midtrans QRIS Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create E-Wallet transaction (GoPay, ShopeePay, DANA, OVO)
     */
    public function createEwalletTransaction(Payment $payment, string $ewalletType): array
    {
        $paymentTypeMap = [
            'ewallet_gopay' => 'gopay',
            'ewallet_shopeepay' => 'shopeepay',
            'ewallet_dana' => 'dana',
            'ewallet_ovo' => 'ovo',
        ];

        $paymentType = $paymentTypeMap[$ewalletType] ?? 'gopay';

        $params = [
            'transaction_details' => [
                'order_id' => $payment->payment_number,
                'gross_amount' => (int) $payment->amount,
            ],
            'customer_details' => [
                'first_name' => $payment->order->customer_name ?? 'Customer',
                'phone' => $payment->ewallet_phone ?? $payment->order->customer_phone ?? '',
                'email' => $payment->order->customer_email ?? '',
            ],
            'item_details' => $this->buildItemDetails($payment->order),
            'enabled_payments' => [$paymentType],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'minute',
                'duration' => 15,
            ],
            'callbacks' => [
                'finish' => route('payment.midtrans.callback', ['payment' => $payment->id]),
            ],
        ];

        // Add e-wallet specific parameters
        if ($paymentType === 'gopay') {
            $params['gopay'] = [
                'enable_callback' => true,
                'callback_url' => route('payment.midtrans.callback', ['payment' => $payment->id]),
            ];
        }

        try {
            $snapToken = Snap::getSnapToken($params);
            
            $response = Http::withBasicAuth($this->integration->getServerKey(), '')
                ->post($this->getApiUrl() . '/v2/' . $paymentType . '/charge', array_merge(
                    $params['transaction_details'],
                    ['payment_type' => $paymentType],
                    $params['customer_details']
                ));

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'snap_token' => $snapToken,
                    'redirect_url' => $data['actions'][0]['url'] ?? null,
                    'deeplink_redirect_url' => $data['deeplink_redirect_url'] ?? null,
                    'provider_transaction_id' => $data['transaction_id'] ?? null,
                    'expired_at' => $data['expiry_time'] ?? now()->addMinutes(15),
                ];
            }

            throw new \Exception('Failed to create e-wallet transaction: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Midtrans E-Wallet Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus(string $orderId): array
    {
        try {
            $status = Http::withBasicAuth($this->integration->getServerKey(), '')
                ->get($this->getApiUrl() . '/v2/' . $orderId . '/status');

            if ($status->successful()) {
                return [
                    'success' => true,
                    'data' => $status->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get status: ' . $status->body(),
            ];

        } catch (\Exception $e) {
            Log::error('Midtrans Status Check Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get API URL based on environment
     */
    protected function getApiUrl(): string
    {
        return $this->isProduction 
            ? 'https://api.midtrans.com' 
            : 'https://api.sandbox.midtrans.com';
    }

    /**
     * Handle notification callback
     */
    public function handleNotification(array $data): array
    {
        $orderId = $data['order_id'] ?? null;
        $transactionStatus = $data['transaction_status'] ?? null;
        $fraudStatus = $data['fraud_status'] ?? null;
        $paymentType = $data['payment_type'] ?? null;
        $transactionId = $data['transaction_id'] ?? null;
        $signatureKey = $data['signature_key'] ?? null;

        // Verify signature
        if (!$this->verifySignature($data)) {
            return [
                'success' => false,
                'error' => 'Invalid signature',
            ];
        }

        $payment = Payment::where('payment_number', $orderId)->first();
        
        if (!$payment) {
            return [
                'success' => false,
                'error' => 'Payment not found',
            ];
        }

        // Map Midtrans status to our status
        $statusMap = [
            'capture' => 'paid',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny' => 'failed',
            'cancel' => 'cancelled',
            'expire' => 'expired',
            'refund' => 'refunded',
            'partial_refund' => 'refunded',
        ];

        $newStatus = $statusMap[$transactionStatus] ?? 'pending';

        // For credit card, check fraud status
        if (in_array($paymentType, ['credit_card', 'ecard'])) {
            if ($fraudStatus === 'challenge') {
                $newStatus = 'pending'; // Manual review needed
            } elseif ($fraudStatus === 'deny') {
                $newStatus = 'failed';
            }
        }

        $payment->update([
            'status' => $newStatus,
            'provider_transaction_id' => $transactionId,
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
     * Verify Midtrans signature
     */
    protected function verifySignature(array $data): bool
    {
        $orderId = $data['order_id'] ?? '';
        $statusCode = $data['status_code'] ?? '';
        $grossAmount = $data['gross_amount'] ?? '';
        $signatureKey = $data['signature_key'] ?? '';
        $serverKey = $this->integration->getServerKey();

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }

    /**
     * Build item details for Midtrans
     */
    protected function buildItemDetails($order): array
    {
        $items = [];
        
        foreach ($order->items as $item) {
            $items[] = [
                'id' => $item->product_id,
                'price' => (int) $item->unit_price,
                'quantity' => $item->quantity,
                'name' => $item->product_name . ($item->variant_name ? ' - ' . $item->variant_name : ''),
                'category' => $item->product->category->name ?? 'Menu',
            ];

            // Add modifiers as separate items
            foreach ($item->modifiers as $modifier) {
                if ($modifier->price_adjustment > 0) {
                    $items[] = [
                        'id' => 'mod_' . $modifier->modifier_id,
                        'price' => (int) $modifier->price_adjustment,
                        'quantity' => $item->quantity,
                        'name' => 'Modifier: ' . $modifier->modifier_name,
                        'category' => 'Modifier',
                    ];
                }
            }

            // Add toppings
            foreach ($item->toppings as $topping) {
                $items[] = [
                    'id' => 'top_' . $topping->topping_id,
                    'price' => (int) $topping->unit_price,
                    'quantity' => $topping->quantity,
                    'name' => 'Topping: ' . $topping->topping_name,
                    'category' => 'Topping',
                ];
            }
        }

        return $items;
    }

    /**
     * Refund transaction
     */
    public function refund(string $transactionId, float $amount, string $reason): array
    {
        try {
            $response = Http::withBasicAuth($this->integration->getServerKey(), '')
                ->post($this->getApiUrl() . '/v2/' . $transactionId . '/refund', [
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
            Log::error('Midtrans Refund Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}