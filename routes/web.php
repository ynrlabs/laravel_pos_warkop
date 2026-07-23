<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payment\MidtransWebhookController;
use App\Http\Controllers\Payment\XenditWebhookController;

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

// POS Shift Summary Route
Route::prefix('pos')->group(function () {
    Route::get('shift-summary/{shift}', function (\App\Models\Shift $shift) {
        return view('filament.pos.pages.shift-summary', ['shift' => $shift]);
    })->name('pos.shift-summary');
});