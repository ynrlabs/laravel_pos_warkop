<?php

namespace App\Services\PaymentGateway;

use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create QRIS Dynamic transaction
     */
    public function createQrisTransaction(Payment $payment): array;

    /**
     * Create E-Wallet transaction (GoPay, ShopeePay, DANA, OVO)
     */
    public function createEwalletTransaction(Payment $payment, string $ewalletType): array;

    /**
     * Get transaction status
     */
    public function getTransactionStatus(string $orderId): array;

    /**
     * Handle notification callback
     */
    public function handleNotification(array $data): array;

    /**
     * Refund transaction
     */
    public function refund(string $transactionId, float $amount, string $reason): array;
}