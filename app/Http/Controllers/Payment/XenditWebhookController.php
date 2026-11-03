<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentGateway\PaymentGatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();
        
        Log::info('Xendit Webhook Received', $data);

        try {
            // Find the payment - Xendit uses external_id
            $externalId = $data['external_id'] ?? $data['id'] ?? null;
            
            if (!$externalId) {
                return response()->json(['error' => 'Missing external_id'], 400);
            }

            $payment = Payment::where('payment_number', $externalId)->first();
            
            if (!$payment) {
                Log::warning('Xendit Webhook: Payment not found', ['external_id' => $externalId]);
                return response()->json(['error' => 'Payment not found'], 404);
            }

            // Get the integration
            $integration = $payment->outlet->integrations()
                ->where('type', 'payment_gateway')
                ->where('name', 'xendit')
                ->where('is_active', true)
                ->first();

            if (!$integration) {
                Log::error('Xendit Webhook: No active Xendit integration', ['outlet_id' => $payment->outlet_id]);
                return response()->json(['error' => 'Integration not configured'], 500);
            }

            // Create gateway service and handle notification
            $gateway = PaymentGatewayFactory::create($integration);
            $result = $gateway->handleNotification($data);

            if ($result['success']) {
                Log::info('Xendit Webhook Processed Successfully', [
                    'payment_id' => $payment->id,
                    'status' => $result['status'],
                ]);
                return response()->json(['status' => 'ok']);
            } else {
                Log::error('Xendit Webhook Processing Failed', [
                    'payment_id' => $payment->id,
                    'error' => $result['error'],
                ]);
                return response()->json(['error' => $result['error']], 400);
            }

        } catch (\Exception $e) {
            Log::error('Xendit Webhook Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function callback(Request $request)
    {
        // Handle redirect callback from Xendit
        $paymentId = $request->query('payment');
        
        if ($paymentId) {
            $payment = Payment::find($paymentId);
            
            if ($payment) {
                // Check transaction status
                $integration = $payment->outlet->integrations()
                    ->where('type', 'payment_gateway')
                    ->where('name', 'xendit')
                    ->where('is_active', true)
                    ->first();

                if ($integration) {
                    $gateway = PaymentGatewayFactory::create($integration);
                    $result = $gateway->getTransactionStatus($payment->payment_number);
                    
                    if ($result['success']) {
                        $gateway->handleNotification($result['data']);
                    }
                }
            }
        }

        // Redirect to POS payment page
        return redirect()->route('pos.quick-order')
            ->with('payment_status', $payment?->status ?? 'unknown');
    }
}