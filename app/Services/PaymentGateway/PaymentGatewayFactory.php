<?php

namespace App\Services\PaymentGateway;

use App\Models\Integration;
use App\Models\Payment;

class PaymentGatewayFactory
{
    /**
     * Create payment gateway service instance
     */
    public static function create(Integration $integration): PaymentGatewayInterface
    {
        return match ($integration->name) {
            'midtrans' => new MidtransService($integration),
            'xendit' => new XenditService($integration),
            default => throw new \Exception("Unsupported payment gateway: {$integration->name}"),
        };
    }

    /**
     * Get primary payment gateway for outlet
     */
    public static function getPrimaryGateway(int $outletId): ?PaymentGatewayInterface
    {
        $integration = Integration::where('outlet_id', $outletId)
            ->where('type', 'payment_gateway')
            ->where('is_active', true)
            ->where('is_primary', true)
            ->first();

        if (!$integration) {
            // Fallback to any active payment gateway
            $integration = Integration::where('outlet_id', $outletId)
                ->where('type', 'payment_gateway')
                ->where('is_active', true)
                ->first();
        }

        if (!$integration) {
            return null;
        }

        return self::create($integration);
    }

    /**
     * Get specific gateway by name
     */
    public static function getGatewayByName(int $outletId, string $name): ?PaymentGatewayInterface
    {
        $integration = Integration::where('outlet_id', $outletId)
            ->where('type', 'payment_gateway')
            ->where('name', $name)
            ->where('is_active', true)
            ->first();

        if (!$integration) {
            return null;
        }

        return self::create($integration);
    }

    /**
     * Get all active gateways for outlet
     */
    public static function getAllGateways(int $outletId): array
    {
        $integrations = Integration::where('outlet_id', $outletId)
            ->where('type', 'payment_gateway')
            ->where('is_active', true)
            ->get();

        return $integrations->map(fn($i) => self::create($i))->toArray();
    }
}