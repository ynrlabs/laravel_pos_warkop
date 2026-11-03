<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Table as TableModel;
use App\Models\Tab;
use App\Models\Shift;
use App\Models\ShiftDefinition;
use App\Models\ShiftCashDetail;
use App\Models\ShiftSummary;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemModifier;
use App\Models\OrderItemTopping;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\ProductVariant;
use App\Models\Modifier;
use App\Models\Topping;
use App\Services\PaymentGateway\PaymentGatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QuickOrderController extends Controller
{
    /**
     * Display the POS Quick Order page.
     */
    public function index(Request $request)
    {
        $outletId = Auth::user()->outlet_id;
        $userId = Auth::id();

        // Get current open shift
        $currentShift = Shift::where('outlet_id', $outletId)
            ->where('user_id', $userId)
            ->where('status', 'opened')
            ->latest('shift_date')
            ->first();

        // Shift definitions
        $shiftDefinitions = ShiftDefinition::where('outlet_id', $outletId)
            ->where('is_active', true)
            ->ordered()
            ->get();

        if ($shiftDefinitions->isEmpty()) {
            $defaultShift = ShiftDefinition::create([
                'outlet_id' => $outletId,
                'name' => 'Shift Reguler',
                'code' => 'REG',
                'start_time' => '07:00',
                'end_time' => '23:00',
                'is_active' => true,
                'sort_order' => 1,
            ]);
            $shiftDefinitions = collect([$defaultShift]);
        }

        // Categories
        $categories = Category::where('outlet_id', $outletId)
            ->where('is_active', true)
            ->ordered()
            ->get();

        // Products with filters
        $query = Product::where('outlet_id', $outletId)
            ->where('is_active', true)
            ->where('type', 'finished_good')
            ->with([
                'category',
                'variants' => fn($q) => $q->where('is_active', true)->ordered(),
                'modifierGroups.modifiers' => fn($q) => $q->where('is_active', true)->ordered(),
                'toppings' => fn($q) => $q->where('is_active', true)->ordered(),
            ])
            ->ordered();

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('slug', 'like', "%{$request->search}%");
            });
        }

        $products = $query->get()->map(function ($product) {
            // Calculate effective price (base + default variant adjustment)
            $defaultVariant = $product->variants->where('is_default', true)->first();
            $product->effective_price = $product->base_price + ($defaultVariant?->price_adjustment ?? 0);
            
            // Add product count for category badge
            $product->products_count = 1;
            
            return $product;
        });

        // Tables
        $tables = TableModel::where('outlet_id', $outletId)
            ->where('is_active', true)
            ->ordered()
            ->get();

        // Active tabs
        $activeTabs = Tab::where('outlet_id', $outletId)
            ->where('status', 'open')
            ->with(['items.product', 'items.variant', 'items.modifiers', 'items.toppings', 'table'])
            ->latest()
            ->get()
            ->map(function ($tab) {
                $tab->items->each(function ($item) {
                    $item->subtotal = $item->subtotal;
                });
                return $tab;
            });

        $outlet = Auth::user()->outlet;
        $outletSettings = [
            'tax_rate' => (float) ($outlet?->settings['tax_rate'] ?? 0.11),
            'service_charge' => (float) ($outlet?->settings['service_charge'] ?? 0.05),
            'rounding' => (int) ($outlet?->settings['rounding'] ?? 100),
        ];

        return Inertia::render('POS/QuickOrder', [
            'categories' => $categories,
            'products' => $products,
            'tables' => $tables,
            'activeTabs' => $activeTabs,
            'outletSettings' => $outletSettings,
            'shiftDefinitions' => $shiftDefinitions,
            'currentShift' => $currentShift ? [
                'id' => $currentShift->id,
                'status' => $currentShift->status,
                'shift_date' => $currentShift->shift_date,
                'opening_cash' => $currentShift->opening_cash,
                'total_cash_sales' => $currentShift->total_cash_sales,
                'total_non_cash_sales' => $currentShift->total_non_cash_sales,
                'transaction_count' => $currentShift->transaction_count,
            ] : null,
        ]);
    }

    /**
     * Process a new order.
     */
    public function processOrder(Request $request)
    {
        $request->validate([
            'cart' => 'required|array|min:1',
            'cart.*.product_id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.base_price' => 'required|numeric|min:0',
            'cart.*.modifiers' => 'array',
            'cart.*.toppings' => 'array',
            'order_type' => 'required|in:dine_in,takeaway',
            'table_id' => 'nullable|exists:tables,id',
            'customer_name' => 'nullable|string|max:255',
            'tab_id' => 'nullable|exists:tabs,id',
            'payment' => 'required|array',
            'payment.method' => 'required|in:cash,qris,ewallet,card,split',
            'payment.cash_received' => 'nullable|numeric|min:0',
            'payment.split_payments' => 'nullable|array',
        ]);

        $outletId = Auth::user()->outlet_id;
        $userId = Auth::id();

        $activeShift = Shift::where('outlet_id', $outletId)
            ->where('user_id', $userId)
            ->where('status', 'opened')
            ->first();

        if (! $activeShift) {
            return response()->json([
                'success' => false,
                'message' => 'Harap buka shift kasir terlebih dahulu sebelum memproses transaksi.',
            ], 422);
        }

        return DB::transaction(function () use ($request, $outletId, $userId, $activeShift) {
            $cart = $request->cart;
            $orderType = $request->order_type;
            $tableId = $orderType === 'dine_in' ? $request->table_id : null;
            $customerName = $request->customer_name;
            $tabId = $request->tab_id;
            $payment = $request->payment;

            // Calculate totals
            $subtotal = collect($cart)->sum('subtotal');
            $taxRate = Auth::user()->outlet->settings['tax_rate'] ?? 0.11;
            $serviceRate = Auth::user()->outlet->settings['service_charge'] ?? 0.05;
            $taxAmount = $subtotal * $taxRate;
            $serviceCharge = $subtotal * $serviceRate;
            $total = $subtotal + $taxAmount + $serviceCharge;
            $rounding = Auth::user()->outlet->settings['rounding'] ?? 100;
            $roundedTotal = ceil($total / $rounding) * $rounding;
            $roundingAmount = $roundedTotal - $total;

            // Determine order status based on payment method
            $isGatewayPayment = in_array($payment['method'], ['qris', 'ewallet']);
            $orderStatus = $isGatewayPayment ? 'pending' : 'paid';
            $paymentStatus = $isGatewayPayment ? 'pending' : 'paid';

            // Create order
            $order = Order::create([
                'outlet_id' => $outletId,
                'shift_id' => $activeShift->id,
                'user_id' => $userId,
                'table_id' => $tableId,
                'tab_id' => $tabId,
                'order_type' => $orderType,
                'customer_name' => $customerName,
                'status' => $orderStatus,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'service_charge' => $serviceCharge,
                'total' => $roundedTotal,
                'rounding_amount' => $roundingAmount,
                'payment_method' => $payment['method'],
                'paid_at' => $orderStatus === 'paid' ? now() : null,
            ]);

            // Create order items
            foreach ($cart as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'variant_name' => $item['variant_name'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['base_price'],
                    'subtotal' => $item['subtotal'],
                    'notes' => $item['notes'] ?? null,
                ]);

                // Modifiers
                foreach ($item['modifiers'] ?? [] as $mod) {
                    OrderItemModifier::create([
                        'order_item_id' => $orderItem->id,
                        'modifier_id' => $mod['id'],
                        'modifier_name' => $mod['name'],
                        'price_adjustment' => $mod['price_adjustment'],
                    ]);
                }

                // Toppings
                foreach ($item['toppings'] ?? [] as $top) {
                    OrderItemTopping::create([
                        'order_item_id' => $orderItem->id,
                        'topping_id' => $top['id'],
                        'topping_name' => $top['name'],
                        'quantity' => $top['quantity'],
                        'unit_price' => $top['price'],
                        'subtotal' => $top['price'] * $top['quantity'],
                    ]);
                }
            }

            // Update table status
            if ($tableId) {
                TableModel::where('id', $tableId)->update(['status' => 'occupied']);
            }

            // Update tab if exists
            if ($tabId) {
                Tab::where('id', $tabId)->update([
                    'status' => $orderStatus === 'paid' ? 'paid' : 'hold',
                    'closed_at' => $orderStatus === 'paid' ? now() : null,
                    'closed_by' => $orderStatus === 'paid' ? $userId : null,
                ]);
            }

            // Create payment record
            $methodMap = [
                'cash' => 'cash',
                'qris' => 'qris_dynamic',
                'ewallet' => 'ewallet_gopay',
                'card' => 'edc_debit',
                'split' => 'other',
            ];

            $paymentRecord = Payment::create([
                'outlet_id' => $outletId,
                'order_id' => $order->id,
                'payment_number' => 'PAY-' . time() . '-' . mt_rand(1000, 9999),
                'method' => $methodMap[$payment['method']] ?? 'other',
                'amount' => $roundedTotal,
                'cash_received' => $payment['method'] === 'cash' ? ($payment['cash_received'] ?? null) : null,
                'cash_change' => $payment['method'] === 'cash' ? max(0, ($payment['cash_received'] ?? 0) - $roundedTotal) : 0,
                'status' => $paymentStatus,
                'paid_at' => $paymentStatus === 'paid' ? now() : null,
                'processed_by' => $userId,
            ]);

            if ($payment['method'] === 'split') {
                foreach ($payment['split_payments'] as $split) {
                    PaymentDetail::create([
                        'payment_id' => $paymentRecord->id,
                        'method' => $split['method'],
                        'amount' => $split['amount'],
                        'reference' => $split['reference'] ?? null,
                    ]);
                }
            }

            // Handle gateway payments (QRIS/E-Wallet)
            if ($isGatewayPayment) {
                $gateway = PaymentGatewayFactory::getPrimaryGateway($outletId);
                
                if (!$gateway) {
                    $paymentRecord->update(['status' => 'failed', 'notes' => 'Gateway tidak dikonfigurasi']);
                    $order->update(['status' => 'void']);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Gateway pembayaran belum aktif/dikonfigurasi!',
                    ], 400);
                }

                try {
                    $result = $payment['method'] === 'qris' 
                        ? $gateway->createQrisTransaction($paymentRecord)
                        : $gateway->createEwalletTransaction($paymentRecord, 'ewallet_gopay');

                    if ($result['success']) {
                        $paymentRecord->update([
                            'qr_code_data' => $result['qr_string'] ?? null,
                            'qr_image_path' => $result['qr_image_url'] ?? null,
                            'provider_transaction_id' => $result['provider_transaction_id'] ?? null,
                            'expired_at' => $result['expired_at'] ?? now()->addMinutes(15),
                            'provider' => $gateway instanceof \App\Services\PaymentGateway\MidtransService ? 'midtrans' : 'xendit',
                        ]);

                        return response()->json([
                            'success' => true,
                            'qris_data' => [
                                'qr_string' => $result['qr_string'] ?? '',
                                'qr_image_url' => $result['qr_image_url'] ?? '',
                                'payment_number' => $paymentRecord->payment_number,
                                'amount' => $paymentRecord->amount,
                                'expires_at' => $result['expired_at'] instanceof \Carbon\Carbon 
                                    ? $result['expired_at'] 
                                    : \Carbon\Carbon::parse($result['expired_at']),
                            ],
                        ]);
                    } else {
                        $paymentRecord->update(['status' => 'failed', 'notes' => $result['error'] ?? 'API error']);
                        $order->update(['status' => 'void']);
                        
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal membuat transaksi: ' . ($result['error'] ?? 'Unknown Error'),
                        ], 400);
                    }
                } catch (\Exception $e) {
                    $paymentRecord->update(['status' => 'failed', 'notes' => $e->getMessage()]);
                    $order->update(['status' => 'void']);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
                    ], 500);
                }
            }

            // For cash/card payments - finalize immediately
            $this->finalizeOrder($order);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil diproses!',
            ]);
        });
    }

    /**
     * Finalize order: deduct stock, print receipt, send to KDS.
     */
    protected function finalizeOrder(Order $order)
    {
        if ($order->status === 'paid' && ! $order->wasRecentlyCreated) {
            return;
        }

        if ($order->status !== 'paid') {
            $order->update([
                'status' => 'paid',
                'paid_at' => $order->paid_at ?? now(),
            ]);
        }

        // Deduct ingredient stock
        $this->deductIngredientStock($order);

        // Ensure order is linked to active shift if not yet assigned
        if (! $order->shift_id) {
            $shift = Shift::where('outlet_id', $order->outlet_id)
                ->where('user_id', $order->user_id)
                ->where('status', 'opened')
                ->latest('shift_date')
                ->first();

            if ($shift) {
                $order->update(['shift_id' => $shift->id]);
            }
        }

        // Dispatch events for printing and KDS
        event(new \App\Events\OrderCreated($order));
        event(new \App\Events\PrintReceipt($order));
    }

    /**
     * Deduct ingredient stock based on recipes.
     */
    protected function deductIngredientStock(Order $order)
    {
        foreach ($order->items as $item) {
            $product = Product::with('recipes.ingredient')->find($item->product_id);
            
            if ($product && $product->recipes->isNotEmpty()) {
                foreach ($product->recipes as $recipe) {
                    $qtyToDeduct = $recipe->quantity * $item->quantity;
                    $recipe->ingredient->decrement('current_stock', $qtyToDeduct);

                    // Log stock movement
                    \App\Models\StockMovement::create([
                        'outlet_id' => $order->outlet_id,
                        'ingredient_id' => $recipe->ingredient_id,
                        'type' => 'out',
                        'quantity' => $qtyToDeduct,
                        'unit_id' => $recipe->ingredient->unit_id,
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'notes' => "Order #{$order->id} - {$item->product_name}",
                    ]);
                }
            }
        }
    }

    /**
     * Load a tab into cart.
     */
    public function loadTab(Tab $tab)
    {
        if ($tab->outlet_id !== Auth::user()->outlet_id || $tab->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'Tab tidak ditemukan atau sudah ditutup!',
            ], 404);
        }

        $cartItems = $tab->items->map(function ($item) {
            return [
                'id' => \Illuminate\Support\Str::uuid(),
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'variant_id' => $item->variant_id,
                'variant_name' => $item->variant_name,
                'base_price' => $item->unit_price,
                'quantity' => $item->quantity,
                'modifiers' => $item->modifiers->map(fn($m) => [
                    'id' => $m->modifier_id,
                    'name' => $m->modifier_name,
                    'price_adjustment' => $m->price_adjustment,
                ])->toArray(),
                'toppings' => $item->toppings->map(fn($t) => [
                    'id' => $t->topping_id,
                    'name' => $t->topping_name,
                    'price' => $t->unit_price,
                    'quantity' => $t->quantity,
                ])->toArray(),
                'notes' => $item->notes,
                'subtotal' => $item->subtotal,
            ];
        });

        // Mark tab as hold while loaded into POS
        $tab->update(['status' => 'hold']);

        return response()->json([
            'success' => true,
            'tab' => [
                'id' => $tab->id,
                'table_id' => $tab->table_id,
                'customer_name' => $tab->customer_name,
                'items' => $cartItems,
            ],
            'message' => 'Tab dimuat ke keranjang!',
        ]);
    }

    /**
     * Save current cart as a tab.
     */
    public function saveTab(Request $request)
    {
        $request->validate([
            'cart' => 'required|array|min:1',
            'order_type' => 'required|in:dine_in,takeaway',
            'table_id' => 'nullable|exists:tables,id',
            'customer_name' => 'nullable|string|max:255',
        ]);

        $outletId = Auth::user()->outlet_id;
        $userId = Auth::id();

        $currentShift = Shift::where('outlet_id', $outletId)
            ->where('user_id', $userId)
            ->where('status', 'opened')
            ->latest('shift_date')
            ->first();

        $tab = Tab::create([
            'outlet_id' => $outletId,
            'shift_id' => $currentShift?->id,
            'table_id' => $request->order_type === 'dine_in' ? $request->table_id : null,
            'customer_name' => $request->customer_name,
            'guest_count' => 1,
            'status' => 'open',
            'opened_by' => $userId,
            'opened_at' => now(),
        ]);

        foreach ($request->cart as $item) {
            $tabItem = \App\Models\TabItem::create([
                'tab_id' => $tab->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'product_name' => $item['product_name'],
                'variant_name' => $item['variant_name'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['base_price'],
                'subtotal' => $item['subtotal'],
                'notes' => $item['notes'] ?? null,
            ]);

            foreach ($item['modifiers'] ?? [] as $mod) {
                \App\Models\TabItemModifier::create([
                    'tab_item_id' => $tabItem->id,
                    'modifier_id' => $mod['id'],
                    'modifier_name' => $mod['name'],
                    'price_adjustment' => $mod['price_adjustment'],
                ]);
            }

            foreach ($item['toppings'] ?? [] as $top) {
                \App\Models\TabItemTopping::create([
                    'tab_item_id' => $tabItem->id,
                    'topping_id' => $top['id'],
                    'topping_name' => $top['name'],
                    'quantity' => $top['quantity'],
                    'unit_price' => $top['price'],
                    'subtotal' => $top['price'] * $top['quantity'],
                ]);
            }
        }

        if ($request->table_id) {
            TableModel::where('id', $request->table_id)->update(['status' => 'occupied']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tab berhasil disimpan!',
        ]);
    }

    /**
     * Split a tab.
     */
    public function splitTab(Request $request, Tab $tab)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:tab_items,id',
            'items.*.split_quantity' => 'required|integer|min:1',
            'new_tab_customer_name' => 'nullable|string|max:255',
            'new_tab_table_id' => 'nullable|exists:tables,id',
        ]);

        if ($tab->outlet_id !== Auth::user()->outlet_id || $tab->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'Tab tidak ditemukan atau sudah ditutup!',
            ], 404);
        }

        return DB::transaction(function () use ($request, $tab) {
            $selectedItems = collect($request->items)->where('split_quantity', '>', 0);

            if ($selectedItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pilih minimal 1 item untuk dipecah!',
                ], 400);
            }

            // Validate quantities
            foreach ($selectedItems as $item) {
                $tabItem = \App\Models\TabItem::find($item['id']);
                if ($item['split_quantity'] > $tabItem->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Jumlah pecah untuk {$tabItem->product_name} melebihi jumlah tersedia!",
                    ], 400);
                }
            }

            // Create new tab
            $newTab = Tab::create([
                'outlet_id' => $tab->outlet_id,
                'shift_id' => $tab->shift_id,
                'table_id' => $request->new_tab_table_id,
                'customer_name' => $request->new_tab_customer_name ?: $tab->customer_name . ' (Split)',
                'guest_count' => 1,
                'status' => 'open',
                'opened_by' => Auth::id(),
                'opened_at' => now(),
            ]);

            // Move selected items
            foreach ($selectedItems as $item) {
                $tabItem = \App\Models\TabItem::find($item['id']);
                
                if ($item['split_quantity'] < $tabItem->quantity) {
                    // Partial split - update source
                    $remainingQty = $tabItem->quantity - $item['split_quantity'];
                    $tabItem->update([
                        'quantity' => $remainingQty,
                        'subtotal' => $remainingQty * $tabItem->unit_price,
                    ]);
                } else {
                    // Full move - delete from source
                    $tabItem->delete();
                }

                // Create in new tab
                $newTabItem = \App\Models\TabItem::create([
                    'tab_id' => $newTab->id,
                    'product_id' => $tabItem->product_id,
                    'variant_id' => $tabItem->variant_id,
                    'product_name' => $tabItem->product_name,
                    'variant_name' => $tabItem->variant_name,
                    'quantity' => $item['split_quantity'],
                    'unit_price' => $tabItem->unit_price,
                    'subtotal' => $item['split_quantity'] * $tabItem->unit_price,
                    'notes' => $tabItem->notes,
                ]);

                foreach ($tabItem->modifiers as $mod) {
                    \App\Models\TabItemModifier::create([
                        'tab_item_id' => $newTabItem->id,
                        'modifier_id' => $mod->modifier_id,
                        'modifier_name' => $mod->modifier_name,
                        'price_adjustment' => $mod->price_adjustment,
                    ]);
                }

                foreach ($tabItem->toppings as $top) {
                    \App\Models\TabItemTopping::create([
                        'tab_item_id' => $newTabItem->id,
                        'topping_id' => $top->topping_id,
                        'topping_name' => $top->topping_name,
                        'quantity' => $top->quantity,
                        'unit_price' => $top->unit_price,
                        'subtotal' => $top->subtotal,
                    ]);
                }
            }

            // Recalculate totals
            $this->recalculateTabTotals($tab);
            $this->recalculateTabTotals($newTab);

            // Update table status
            if ($request->new_tab_table_id) {
                TableModel::where('id', $request->new_tab_table_id)->update(['status' => 'occupied']);
            }

            // Close source tab if empty
            if ($tab->items()->count() === 0) {
                $tab->update(['status' => 'void', 'closed_at' => now(), 'closed_by' => Auth::id()]);
                if ($tab->table_id) {
                    TableModel::where('id', $tab->table_id)->update(['status' => 'available']);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bill berhasil dipecah! Tab baru: #' . $newTab->id,
            ]);
        });
    }

    /**
     * Merge two tabs.
     */
    public function mergeTabs(Request $request)
    {
        $request->validate([
            'source_id' => 'required|exists:tabs,id',
            'target_id' => 'required|exists:tabs,id',
        ]);

        if ($request->source_id === $request->target_id) {
            return response()->json([
                'success' => false,
                'message' => 'Tab sumber dan tujuan tidak boleh sama!',
            ], 400);
        }

        $sourceTab = Tab::with(['items.product', 'items.variant', 'items.modifiers', 'items.toppings'])->find($request->source_id);
        $targetTab = Tab::find($request->target_id);

        if (!$sourceTab || $sourceTab->status !== 'open' || !$targetTab || $targetTab->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'Tab sumber atau tujuan tidak valid!',
            ], 400);
        }

        return DB::transaction(function () use ($sourceTab, $targetTab) {
            // Move all items from source to target
            foreach ($sourceTab->items as $item) {
                $newTabItem = \App\Models\TabItem::create([
                    'tab_id' => $targetTab->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'product_name' => $item->product_name,
                    'variant_name' => $item->variant_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                    'notes' => $item->notes,
                ]);

                foreach ($item->modifiers as $mod) {
                    \App\Models\TabItemModifier::create([
                        'tab_item_id' => $newTabItem->id,
                        'modifier_id' => $mod->modifier_id,
                        'modifier_name' => $mod->modifier_name,
                        'price_adjustment' => $mod->price_adjustment,
                    ]);
                }

                foreach ($item->toppings as $top) {
                    \App\Models\TabItemTopping::create([
                        'tab_item_id' => $newTabItem->id,
                        'topping_id' => $top->topping_id,
                        'topping_name' => $top->topping_name,
                        'quantity' => $top->quantity,
                        'unit_price' => $top->unit_price,
                        'subtotal' => $top->subtotal,
                    ]);
                }
            }

            // Recalculate target tab
            $this->recalculateTabTotals($targetTab);

            // Update merge tracking
            $sourceTab->update([
                'status' => 'merged',
                'merged_to_tab_id' => $targetTab->id,
                'closed_at' => now(),
                'closed_by' => Auth::id(),
            ]);

            $targetTab->update(['merged_from_tab_id' => $sourceTab->id]);

            // Free up source table
            if ($sourceTab->table_id) {
                TableModel::where('id', $sourceTab->table_id)->update(['status' => 'available']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tab berhasil digabung ke tab #' . $targetTab->id,
            ]);
        });
    }

    /**
     * Transfer tab to another table.
     */
    public function transferTab(Request $request, Tab $tab)
    {
        $request->validate([
            'table_id' => 'nullable|exists:tables,id',
            'customer_name' => 'nullable|string|max:255',
        ]);

        if ($tab->outlet_id !== Auth::user()->outlet_id || $tab->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'Tab tidak ditemukan atau sudah ditutup!',
            ], 404);
        }

        $oldTableId = $tab->table_id;

        $tab->update([
            'table_id' => $request->table_id,
            'customer_name' => $request->customer_name,
        ]);

        // Free old table
        if ($oldTableId) {
            TableModel::where('id', $oldTableId)->update(['status' => 'available']);
        }

        // Occupy new table
        if ($request->table_id) {
            TableModel::where('id', $request->table_id)->update(['status' => 'occupied']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tab berhasil dipindah ke meja baru!',
        ]);
    }

    /**
     * Check QRIS payment status.
     */
    public function checkQrisStatus($paymentNumber)
    {
        $payment = Payment::with(['order', 'outlet'])->where('payment_number', $paymentNumber)->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran tidak ditemukan',
            ], 404);
        }

        // Already paid via webhook or gateway query
        if ($payment->status === 'paid') {
            if ($payment->order && $payment->order->status !== 'paid') {
                $this->finalizeOrder($payment->order);
            }
            return response()->json([
                'success' => true,
                'payment_status' => 'paid',
            ]);
        }

        // Check expiration
        if ($payment->isExpired() || ($payment->expired_at && now()->gt($payment->expired_at))) {
            $payment->update(['status' => 'expired']);
            $payment->order->update(['status' => 'void']);
            
            return response()->json([
                'success' => false,
                'payment_status' => 'expired',
                'message' => 'Pembayaran QRIS kedaluwarsa!',
            ]);
        }

        // Query gateway
        $gateway = PaymentGatewayFactory::getGatewayByName($payment->outlet_id, $payment->provider);
        
        if ($gateway) {
            $result = $gateway->getTransactionStatus($paymentNumber);
            
            if ($result['success']) {
                $statusResult = $gateway->handleNotification($result['data']);
                
                if ($statusResult['success'] && $statusResult['status'] === 'paid') {
                    $this->finalizeOrder($payment->order);
                    return response()->json([
                        'success' => true,
                        'payment_status' => 'paid',
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'payment_status' => $payment->status,
        ]);
    }

    /**
     * Cancel QRIS payment.
     */
    public function cancelQrisPayment($paymentNumber)
    {
        $payment = Payment::where('payment_number', $paymentNumber)->first();

        if ($payment) {
            $payment->update(['status' => 'cancelled']);
            $payment->order->update(['status' => 'void']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran QRIS dibatalkan.',
        ]);
    }

    /**
     * Print receipt for order.
     */
    public function printReceipt(Order $order)
    {
        event(new \App\Events\PrintReceipt($order));
        
        return response()->json([
            'success' => true,
            'message' => 'Struk dikirim ke printer',
        ]);
    }

    /**
     * Recalculate tab totals.
     */
    protected function recalculateTabTotals(Tab $tab)
    {
        $subtotal = $tab->items()->sum('subtotal');
        $taxRate = $tab->outlet->settings['tax_rate'] ?? 0.11;
        $serviceRate = $tab->outlet->settings['service_charge'] ?? 0.05;
        $taxAmount = $subtotal * $taxRate;
        $serviceCharge = $subtotal * $serviceRate;
        $total = $subtotal + $taxAmount + $serviceCharge;
        $rounding = $tab->outlet->settings['rounding'] ?? 100;
        $roundedTotal = ceil($total / $rounding) * $rounding;

        $tab->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'service_charge_amount' => $serviceCharge,
            'total_amount' => $roundedTotal,
        ]);
    }

    public function openShift(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'shift_definition_id' => 'nullable|exists:shift_definitions,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $outletId = Auth::user()->outlet_id;
        $userId = Auth::id();

        $existing = Shift::where('outlet_id', $outletId)
            ->where('user_id', $userId)
            ->where('status', 'opened')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Anda masih memiliki shift yang sedang aktif.',
            ], 422);
        }

        $shiftDefId = $request->shift_definition_id;
        if (! $shiftDefId) {
            $shiftDef = ShiftDefinition::where('outlet_id', $outletId)->where('is_active', true)->first();
            if (! $shiftDef) {
                $shiftDef = ShiftDefinition::create([
                    'outlet_id' => $outletId,
                    'name' => 'Shift Reguler',
                    'code' => 'REG',
                    'start_time' => '07:00',
                    'end_time' => '23:00',
                    'is_active' => true,
                    'sort_order' => 1,
                ]);
            }
            $shiftDefId = $shiftDef->id;
        }

        $shift = DB::transaction(function () use ($outletId, $userId, $shiftDefId, $request) {
            $shift = Shift::create([
                'outlet_id' => $outletId,
                'shift_definition_id' => $shiftDefId,
                'user_id' => $userId,
                'shift_date' => today(),
                'status' => 'opened',
                'opened_at' => now(),
                'notes' => $request->notes,
            ]);

            ShiftCashDetail::create([
                'shift_id' => $shift->id,
                'type' => 'opening',
                'denomination' => (float) $request->opening_cash,
                'quantity' => 1,
                'notes' => 'Modal awal kasir',
            ]);

            return $shift;
        });

        return response()->json([
            'success' => true,
            'message' => 'Shift berhasil dibuka.',
            'shift' => [
                'id' => $shift->id,
                'status' => $shift->status,
                'opening_cash' => (float) $request->opening_cash,
                'total_cash_sales' => 0,
                'total_non_cash_sales' => 0,
                'transaction_count' => 0,
            ],
        ]);
    }

    public function closeShift(Request $request)
    {
        $request->validate([
            'actual_cash' => 'required|numeric|min:0',
            'variance_notes' => 'nullable|string|max:500',
        ]);

        $outletId = Auth::user()->outlet_id;
        $userId = Auth::id();

        $shift = Shift::where('outlet_id', $outletId)
            ->where('user_id', $userId)
            ->where('status', 'opened')
            ->latest('shift_date')
            ->first();

        if (! $shift) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada shift aktif yang dapat ditutup.',
            ], 422);
        }

        DB::transaction(function () use ($shift, $request) {
            ShiftCashDetail::create([
                'shift_id' => $shift->id,
                'type' => 'closing',
                'denomination' => (float) $request->actual_cash,
                'quantity' => 1,
                'notes' => 'Hitung fisik kasir akhir shift',
            ]);

            $openingCash = $shift->opening_cash;
            $cashSales = $shift->total_cash_sales;
            $nonCashSales = $shift->total_non_cash_sales;
            $refundAmount = $shift->refund_amount;
            $expectedCash = $openingCash + $cashSales - $refundAmount;
            $actualCash = (float) $request->actual_cash;
            $variance = $actualCash - $expectedCash;

            ShiftSummary::updateOrCreate(
                ['shift_id' => $shift->id],
                [
                    'opening_cash' => $openingCash,
                    'cash_sales' => $cashSales,
                    'non_cash_sales' => $nonCashSales,
                    'refund_amount' => $refundAmount,
                    'expected_cash' => $expectedCash,
                    'actual_cash' => $actualCash,
                    'variance_notes' => $variance != 0 ? ($request->variance_notes ?: 'Terdapat selisih kas fisik') : null,
                    'transaction_count' => $shift->transaction_count,
                    'void_count' => $shift->void_count,
                    'refund_count' => $shift->refund_count,
                ]
            );

            $shift->update([
                'status' => 'closed',
                'closed_at' => now(),
                'notes' => $request->variance_notes ?: $shift->notes,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Shift berhasil ditutup.',
        ]);
    }
}