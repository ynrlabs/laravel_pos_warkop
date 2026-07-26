<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payment\MidtransWebhookController;
use App\Http\Controllers\Payment\XenditWebhookController;
use App\Http\Controllers\POS\QuickOrderController;

Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;
        if (in_array($role, ['super_admin', 'manager'])) {
            return redirect('/admin');
        }
        return redirect('/pos');
    }
    return redirect('/pos');
});

// Payment Gateway Webhooks
Route::prefix('payment')->group(function () {
    // Midtrans Webhooks
    Route::post('midtrans/webhook', [MidtransWebhookController::class, 'handle'])
        ->name('payment.midtrans.webhook');
    Route::get('midtrans/callback', [MidtransWebhookController::class, 'callback'])
        ->name('payment.midtrans.callback');

    // Xendit Webhooks
    Route::post('xendit/webhook', [XenditWebhookController::class, 'handle'])
        ->name('payment.xendit.webhook');
    Route::get('xendit/callback', [XenditWebhookController::class, 'callback'])
        ->name('payment.xendit.callback');
    Route::get('xendit/return', [XenditWebhookController::class, 'callback'])
        ->name('payment.xendit.return');
});

// POS Routes (Inertia.js)
Route::prefix('pos')->group(function () {
    // Quick Order (Inertia POS)
    Route::get('inertia-quick-order', [QuickOrderController::class, 'index'])->name('pos.quick-order');
    Route::post('order/process', [QuickOrderController::class, 'processOrder'])->name('pos.order.process');
    
    // Tab Management
    Route::post('tab/save', [QuickOrderController::class, 'saveTab'])->name('pos.tab.save');
    Route::post('tab/load/{tab}', [QuickOrderController::class, 'loadTab'])->name('pos.tab.load');
    Route::post('tab/split/{tab}', [QuickOrderController::class, 'splitTab'])->name('pos.tab.split');
    Route::post('tab/merge', [QuickOrderController::class, 'mergeTabs'])->name('pos.tab.merge');
    Route::post('tab/transfer/{tab}', [QuickOrderController::class, 'transferTab'])->name('pos.tab.transfer');
    
    // Payment
    Route::post('payment/check-qris/{paymentNumber}', [QuickOrderController::class, 'checkQrisStatus'])->name('pos.payment.check-qris');
    Route::post('payment/cancel-qris/{paymentNumber}', [QuickOrderController::class, 'cancelQrisPayment'])->name('pos.payment.cancel-qris');
    
    // Print
    Route::post('order/print/{order}', [QuickOrderController::class, 'printReceipt'])->name('pos.order.print');

    // Shift Summary (Filament)
    Route::get('shift-summary/{shift}', function (\App\Models\Shift $shift) {
        return view('filament.pos.pages.shift-summary', ['shift' => $shift]);
    })->name('pos.shift-summary');
});
