<?php

namespace App\Filament\Pos\Pages;

use Filament\Pages\Page;
use App\Models\Product;
use App\Models\Category;
use App\Models\Table as TableModel;
use App\Models\Tab;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use BackedEnum;

class QuickOrder extends Page
{
    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-shopping-cart';
    }
    
    protected static ?string $navigationLabel = 'Quick Order';
    
    protected static ?string $title = 'Quick Order';
    
    protected static ?int $navigationSort = 1;
    
    protected string $view = 'filament.pos.pages.quick-order';
    
    public $selectedCategoryId = null;
    public $search = '';
    public $cart = [];
    public $orderType = 'dine_in'; // dine_in, takeaway
    public $selectedTableId = null;
    public $tabId = null;
    public $customerName = '';
    public $showPaymentModal = false;
    public $paymentMethod = 'cash';
    public $cashReceived = 0;
    public $splitPayments = [];
    
    // Split Bill Modal
    public $showSplitBillModal = false;
    public $splitBillTabId = null;
    public $splitBillItems = [];
    public $splitBillNewTabCustomerName = '';
    public $splitBillNewTabTableId = null;
    
    // Merge Tab Modal
    public $showMergeTabModal = false;
    public $mergeTabSourceId = null;
    public $mergeTabTargetId = null;
    
    // Transfer Tab Modal
    public $showTransferTabModal = false;
    public $transferTabId = null;
    public $transferTabNewTableId = null;
    public $transferTabNewCustomerName = '';
    
    public function mount(): void
    {
        $this->loadDefaultCategory();
    }
    
    public function loadDefaultCategory(): void
    {
        $firstCategory = Category::where('outlet_id', Auth::user()->outlet_id)
            ->where('is_active', true)
            ->ordered()
            ->first();
        
        if ($firstCategory) {
            $this->selectedCategoryId = $firstCategory->id;
        }
    }
    
    public function getCategoriesProperty()
    {
        return Category::where('outlet_id', Auth::user()->outlet_id)
            ->where('is_active', true)
            ->ordered()
            ->get();
    }
    
    public function getProductsProperty()
    {
        $query = Product::where('outlet_id', Auth::user()->outlet_id)
            ->where('is_active', true)
            ->where('type', 'finished_good')
            ->with(['category', 'variants' => fn($q) => $q->where('is_active', true)->ordered(), 'modifierGroups.modifiers' => fn($q) => $q->where('is_active', true)->ordered()])
            ->ordered();
        
        if ($this->selectedCategoryId) {
            $query->where('category_id', $this->selectedCategoryId);
        }
        
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('slug', 'like', "%{$this->search}%");
            });
        }
        
        return $query->get();
    }
    
    public function getTablesProperty()
    {
        return TableModel::where('outlet_id', Auth::user()->outlet_id)
            ->where('is_active', true)
            ->ordered()
            ->get();
    }
    
    public function getActiveTabsProperty()
    {
        return Tab::where('outlet_id', Auth::user()->outlet_id)
            ->where('status', 'open')
            ->with(['items.product', 'items.variant', 'items.modifiers', 'items.toppings'])
            ->latest()
            ->get();
    }
    
    public function selectCategory($categoryId): void
    {
        $this->selectedCategoryId = $categoryId;
    }
    
    public function addToCart($productId, $variantId = null): void
    {
        $product = Product::with(['variants', 'modifierGroups.modifiers', 'toppings'])->find($productId);
        
        if (!$product) {
            return;
        }
        
        $variant = null;
        if ($variantId) {
            $variant = $product->variants()->where('id', $variantId)->first();
        } elseif ($product->variants->where('is_default', true)->first()) {
            $variant = $product->variants()->where('is_default', true)->first();
        }
        
        $price = $product->base_price + ($variant?->price_adjustment ?? 0);
        
        $cartItem = [
            'id' => uniqid(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'variant_id' => $variant?->id,
            'variant_name' => $variant?->name,
            'base_price' => $price,
            'quantity' => 1,
            'modifiers' => [],
            'toppings' => [],
            'notes' => '',
            'subtotal' => $price,
        ];
        
        // If product has required modifiers, don't add to cart yet - open modifier modal
        $hasRequiredModifiers = $product->modifierGroups()->where('is_required', true)->exists();
        
        if ($hasRequiredModifiers || $product->modifierGroups->count() > 0 || $product->toppings->count() > 0) {
            $this->dispatch('open-modifier-modal', cartItem: $cartItem);
        } else {
            $this->addItemToCart($cartItem);
        }
    }
    
    #[On('add-item-to-cart')]
    public function addItemToCart(array $cartItem): void
    {
        // Check if same item with same modifiers/toppings exists
        $existingIndex = $this->findExistingCartItem($cartItem);
        
        if ($existingIndex !== false) {
            $this->cart[$existingIndex]['quantity'] += $cartItem['quantity'];
            $this->cart[$existingIndex]['subtotal'] = $this->cart[$existingIndex]['quantity'] * $this->cart[$existingIndex]['base_price'];
        } else {
            $this->cart[] = $cartItem;
        }
        
        $this->recalculateCart();
    }
    
    protected function findExistingCartItem(array $newItem): int|false
    {
        foreach ($this->cart as $index => $item) {
            if ($item['product_id'] === $newItem['product_id'] 
                && $item['variant_id'] === $newItem['variant_id']
                && $this->modifiersMatch($item['modifiers'], $newItem['modifiers'])
                && $this->toppingsMatch($item['toppings'], $newItem['toppings'])) {
                return $index;
            }
        }
        return false;
    }
    
    protected function modifiersMatch(array $modifiers1, array $modifiers2): bool
    {
        if (count($modifiers1) !== count($modifiers2)) return false;
        
        $ids1 = collect($modifiers1)->pluck('id')->sort()->values();
        $ids2 = collect($modifiers2)->pluck('id')->sort()->values();
        
        return $ids1->equals($ids2);
    }
    
    protected function toppingsMatch(array $toppings1, array $toppings2): bool
    {
        if (count($toppings1) !== count($toppings2)) return false;
        
        $ids1 = collect($toppings1)->pluck('id')->sort()->values();
        $ids2 = collect($toppings2)->pluck('id')->sort()->values();
        
        return $ids1->equals($ids2);
    }
    
    public function updateQuantity($cartItemId, $quantity): void
    {
        foreach ($this->cart as $index => $item) {
            if ($item['id'] === $cartItemId) {
                if ($quantity <= 0) {
                    unset($this->cart[$index]);
                    $this->cart = array_values($this->cart);
                } else {
                    $this->cart[$index]['quantity'] = $quantity;
                    $this->cart[$index]['subtotal'] = $quantity * $this->cart[$index]['base_price'];
                }
                break;
            }
        }
        $this->recalculateCart();
    }
    
    public function removeFromCart($cartItemId): void
    {
        $this->cart = array_values(array_filter($this->cart, fn($item) => $item['id'] !== $cartItemId));
        $this->recalculateCart();
    }
    
    public function clearCart(): void
    {
        $this->cart = [];
        $this->recalculateCart();
    }
    
    protected function recalculateCart(): void
    {
        $this->cart = array_values($this->cart);
    }
    
    public function getSubtotalProperty(): float
    {
        return collect($this->cart)->sum('subtotal');
    }
    
    public function getTaxAmountProperty(): float
    {
        $taxRate = Auth::user()->outlet->settings['tax_rate'] ?? 0.11;
        return $this->subtotal * $taxRate;
    }
    
    public function getServiceChargeProperty(): float
    {
        $serviceRate = Auth::user()->outlet->settings['service_charge'] ?? 0.05;
        return $this->subtotal * $serviceRate;
    }
    
    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->tax_amount + $this->service_charge;
    }
    
    public function getRoundedTotalProperty(): int
    {
        $rounding = Auth::user()->outlet->settings['rounding'] ?? 100;
        return ceil($this->total / $rounding) * $rounding;
    }
    
    public function getChangeProperty(): float
    {
        return max(0, $this->cashReceived - $this->rounded_total);
    }
    
    public function openPaymentModal(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', message: 'Keranjang kosong!', type: 'warning');
            return;
        }
        $this->showPaymentModal = true;
        $this->cashReceived = $this->rounded_total;
    }
    
    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->paymentMethod = 'cash';
        $this->cashReceived = 0;
        $this->splitPayments = [];
    }
    
    public function processPayment(): void
    {
        if ($this->paymentMethod === 'cash' && $this->cashReceived < $this->rounded_total) {
            $this->dispatch('notify', message: 'Uang tidak cukup!', type: 'danger');
            return;
        }
        
        // Create order
        $order = $this->createOrder();
        
        if ($order) {
            // Create payment
            $this->createPayment($order);
            
            // Print receipt
            $this->printReceipt($order);
            
            // Send to KDS
            $this->sendToKds($order);
            
            // Clear cart
            $this->clearCart();
            $this->selectedTableId = null;
            $this->tabId = null;
            $this->customerName = '';
            $this->orderType = 'dine_in';
            
            $this->closePaymentModal();
            
            $this->dispatch('notify', message: 'Pesanan berhasil diproses!', type: 'success');
        }
    }
    
    protected function createOrder(): \App\Models\Order
    {
        $order = \App\Models\Order::create([
            'outlet_id' => Auth::user()->outlet_id,
            'user_id' => Auth::id(),
            'table_id' => $this->orderType === 'dine_in' ? $this->selectedTableId : null,
            'tab_id' => $this->tabId,
            'order_type' => $this->orderType,
            'customer_name' => $this->customerName,
            'status' => 'paid',
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'service_charge' => $this->service_charge,
            'total' => $this->rounded_total,
            'rounding_amount' => $this->rounded_total - $this->total,
            'payment_method' => $this->paymentMethod,
            'paid_at' => now(),
        ]);
        
        foreach ($this->cart as $item) {
            $orderItem = \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'product_name' => $item['product_name'],
                'variant_name' => $item['variant_name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['base_price'],
                'subtotal' => $item['subtotal'],
                'notes' => $item['notes'],
            ]);
            
            foreach ($item['modifiers'] as $mod) {
                \App\Models\OrderItemModifier::create([
                    'order_item_id' => $orderItem->id,
                    'modifier_id' => $mod['id'],
                    'modifier_name' => $mod['name'],
                    'price_adjustment' => $mod['price_adjustment'],
                ]);
            }
            
            foreach ($item['toppings'] as $top) {
                \App\Models\OrderItemTopping::create([
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
        if ($this->selectedTableId) {
            TableModel::where('id', $this->selectedTableId)->update(['status' => 'occupied']);
        }
        
        // Update tab if exists
        if ($this->tabId) {
            Tab::where('id', $this->tabId)->update(['status' => 'active']);
        }
        
        // Deduct stock for ingredients
        $this->deductIngredientStock($order);
        
        return $order;
    }
    
    protected function deductIngredientStock(\App\Models\Order $order): void
    {
        foreach ($order->items as $item) {
            $product = Product::with('recipes.ingredient')->find($item->product_id);
            if ($product && $product->recipes->isNotEmpty()) {
                foreach ($product->recipes as $recipe) {
                    $qtyToDeduct = $recipe->quantity * $item->quantity;
                    $recipe->ingredient->decrement('current_stock', $qtyToDeduct);
                    
                    // Log stock movement
                    \App\Models\StockMovement::create([
                        'outlet_id' => Auth::user()->outlet_id,
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
    
    protected function createPayment(\App\Models\Order $order): void
    {
        $payment = \App\Models\Payment::create([
            'outlet_id' => Auth::user()->outlet_id,
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'method' => $this->paymentMethod,
            'amount' => $this->rounded_total,
            'cash_received' => $this->paymentMethod === 'cash' ? $this->cashReceived : null,
            'change_amount' => $this->paymentMethod === 'cash' ? $this->change : 0,
            'status' => 'completed',
            'paid_at' => now(),
        ]);
        
        if ($this->paymentMethod === 'split') {
            foreach ($this->splitPayments as $split) {
                \App\Models\PaymentDetail::create([
                    'payment_id' => $payment->id,
                    'method' => $split['method'],
                    'amount' => $split['amount'],
                    'reference' => $split['reference'] ?? null,
                ]);
            }
        }
    }
    
    protected function printReceipt(\App\Models\Order $order): void
    {
        // Dispatch print job event
        $this->dispatch('print-receipt', orderId: $order->id);
    }
    
    protected function sendToKds(\App\Models\Order $order): void
    {
        // Dispatch KDS event via Laravel Reverb
        $this->dispatch('send-to-kds', orderId: $order->id);
    }
    
    public function saveAsTab(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', message: 'Keranjang kosong!', type: 'warning');
            return;
        }
        
        if (!$this->customerName && !$this->selectedTableId) {
            $this->dispatch('notify', message: 'Isi nama pelanggan atau pilih meja!', type: 'warning');
            return;
        }
        
        $currentShift = \App\Models\Shift::where('outlet_id', Auth::user()->outlet_id)
            ->where('status', 'open')
            ->where('user_id', Auth::id())
            ->first();
        
        $tab = Tab::create([
            'outlet_id' => Auth::user()->outlet_id,
            'shift_id' => $currentShift?->id,
            'opened_by' => Auth::id(),
            'table_id' => $this->selectedTableId,
            'customer_name' => $this->customerName,
            'status' => 'open',
            'opened_at' => now(),
        ]);
        
        foreach ($this->cart as $item) {
            $tabItem = \App\Models\TabItem::create([
                'tab_id' => $tab->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'product_name' => $item['product_name'],
                'variant_name' => $item['variant_name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['base_price'],
                'subtotal' => $item['subtotal'],
                'notes' => $item['notes'],
            ]);
            
            foreach ($item['modifiers'] as $mod) {
                \App\Models\TabItemModifier::create([
                    'tab_item_id' => $tabItem->id,
                    'modifier_id' => $mod['id'],
                    'modifier_name' => $mod['name'],
                    'price_adjustment' => $mod['price_adjustment'],
                ]);
            }
            
            foreach ($item['toppings'] as $top) {
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
        
        if ($this->selectedTableId) {
            TableModel::where('id', $this->selectedTableId)->update(['status' => 'occupied']);
        }
        
        $this->clearCart();
        $this->selectedTableId = null;
        $this->customerName = '';
        
        $this->dispatch('notify', message: 'Tab berhasil disimpan!', type: 'success');
    }
    
    public function loadTab($tabId): void
    {
        $tab = Tab::with(['items.product', 'items.variant', 'items.modifiers', 'items.toppings'])->find($tabId);
        
        if (!$tab || $tab->status !== 'open') {
            $this->dispatch('notify', message: 'Tab tidak ditemukan atau sudah ditutup!', type: 'danger');
            return;
        }
        
        $this->tabId = $tab->id;
        $this->selectedTableId = $tab->table_id;
        $this->customerName = $tab->customer_name;
        $this->orderType = 'dine_in';
        $this->cart = [];
        
        foreach ($tab->items as $item) {
            $cartItem = [
                'id' => uniqid(),
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'variant_id' => $item->variant_id,
                'variant_name' => $item->variant_name,
                'base_price' => $item->unit_price,
                'quantity' => $item->quantity,
                'modifiers' => $item->modifiers->map(fn($m) => ['id' => $m->modifier_id, 'name' => $m->modifier_name, 'price_adjustment' => $m->price_adjustment])->toArray(),
                'toppings' => $item->toppings->map(fn($t) => ['id' => $t->topping_id, 'name' => $t->topping_name, 'quantity' => $t->quantity, 'price' => $t->unit_price])->toArray(),
                'notes' => $item->notes,
                'subtotal' => $item->subtotal,
            ];
            $this->cart[] = $cartItem;
        }
        
        $this->recalculateCart();
        
        // Delete the tab after loading
        $tab->delete();
        
        $this->dispatch('notify', message: 'Tab dimuat ke keranjang!', type: 'success');
    }
    
    // ==================== SPLIT BILL ====================
    
    public function openSplitBillModal($tabId): void
    {
        $tab = Tab::with(['items.product', 'items.variant', 'items.modifiers', 'items.toppings'])->find($tabId);
        
        if (!$tab || $tab->status !== 'open') {
            $this->dispatch('notify', message: 'Tab tidak ditemukan atau sudah ditutup!', type: 'danger');
            return;
        }
        
        $this->splitBillTabId = $tab->id;
        $this->splitBillItems = [];
        $this->splitBillNewTabCustomerName = '';
        $this->splitBillNewTabTableId = null;
        
        foreach ($tab->items as $item) {
            $this->splitBillItems[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'variant_id' => $item->variant_id,
                'variant_name' => $item->variant_name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
                'modifiers' => $item->modifiers->map(fn($m) => ['id' => $m->modifier_id, 'name' => $m->modifier_name, 'price_adjustment' => $m->price_adjustment])->toArray(),
                'toppings' => $item->toppings->map(fn($t) => ['id' => $t->topping_id, 'name' => $t->topping_name, 'quantity' => $t->quantity, 'price' => $t->unit_price])->toArray(),
                'notes' => $item->notes,
                'selected' => false,
                'split_quantity' => 0,
            ];
        }
        
        $this->showSplitBillModal = true;
    }
    
    public function closeSplitBillModal(): void
    {
        $this->showSplitBillModal = false;
        $this->splitBillTabId = null;
        $this->splitBillItems = [];
        $this->splitBillNewTabCustomerName = '';
        $this->splitBillNewTabTableId = null;
    }
    
    public function processSplitBill(): void
    {
        $selectedItems = collect($this->splitBillItems)->where('selected', true)->where('split_quantity', '>', 0)->values();
        
        if ($selectedItems->isEmpty()) {
            $this->dispatch('notify', message: 'Pilih minimal 1 item untuk dipecah!', type: 'warning');
            return;
        }
        
        // Validate quantities don't exceed available
        foreach ($selectedItems as $item) {
            if ($item['split_quantity'] > $item['quantity']) {
                $this->dispatch('notify', message: "Jumlah pecah untuk {$item['product_name']} melebihi jumlah tersedia!", type: 'danger');
                return;
            }
        }
        
        $sourceTab = Tab::find($this->splitBillTabId);
        
        // Create new tab for split items
        $newTab = Tab::create([
            'outlet_id' => Auth::user()->outlet_id,
            'shift_id' => $sourceTab->shift_id,
            'table_id' => $this->splitBillNewTabTableId,
            'customer_name' => $this->splitBillNewTabCustomerName ?: $sourceTab->customer_name . ' (Split)',
            'guest_count' => 1,
            'status' => 'open',
            'opened_by' => Auth::id(),
            'opened_at' => now(),
        ]);
        
        // Move selected items to new tab
        foreach ($selectedItems as $item) {
            // If splitting partial quantity, update source tab item
            if ($item['split_quantity'] < $item['quantity']) {
                $sourceTabItem = \App\Models\TabItem::find($item['id']);
                $remainingQty = $item['quantity'] - $item['split_quantity'];
                $sourceTabItem->update([
                    'quantity' => $remainingQty,
                    'subtotal' => $remainingQty * $item['unit_price'],
                ]);
            } else {
                // Move entire item - delete from source
                \App\Models\TabItem::find($item['id'])->delete();
            }
            
            // Create item in new tab
            $newTabItem = \App\Models\TabItem::create([
                'tab_id' => $newTab->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'product_name' => $item['product_name'],
                'variant_name' => $item['variant_name'],
                'quantity' => $item['split_quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['split_quantity'] * $item['unit_price'],
                'notes' => $item['notes'],
            ]);
            
            // Copy modifiers
            foreach ($item['modifiers'] as $mod) {
                \App\Models\TabItemModifier::create([
                    'tab_item_id' => $newTabItem->id,
                    'modifier_id' => $mod['id'],
                    'modifier_name' => $mod['name'],
                    'price_adjustment' => $mod['price_adjustment'],
                ]);
            }
            
            // Copy toppings
            foreach ($item['toppings'] as $top) {
                \App\Models\TabItemTopping::create([
                    'tab_item_id' => $newTabItem->id,
                    'topping_id' => $top['id'],
                    'topping_name' => $top['name'],
                    'quantity' => $top['quantity'],
                    'unit_price' => $top['price'],
                    'subtotal' => $top['price'] * $top['quantity'],
                ]);
            }
        }
        
        // Recalculate totals for both tabs
        $this->recalculateTabTotals($sourceTab);
        $this->recalculateTabTotals($newTab);
        
        // Update table status if new table assigned
        if ($this->splitBillNewTabTableId) {
            TableModel::where('id', $this->splitBillNewTabTableId)->update(['status' => 'occupied']);
        }
        
        // If source tab has no items left, close it
        if ($sourceTab->items()->count() === 0) {
            $sourceTab->update(['status' => 'void', 'closed_at' => now(), 'closed_by' => Auth::id()]);
            if ($sourceTab->table_id) {
                TableModel::where('id', $sourceTab->table_id)->update(['status' => 'available']);
            }
        }
        
        $this->closeSplitBillModal();
        $this->dispatch('notify', message: 'Bill berhasil dipecah! Tab baru: #' . $newTab->id, type: 'success');
    }
    
    protected function recalculateTabTotals(Tab $tab): void
    {
        $subtotal = $tab->items()->sum('subtotal');
        $taxRate = Auth::user()->outlet->settings['tax_rate'] ?? 0.11;
        $serviceRate = Auth::user()->outlet->settings['service_charge'] ?? 0.05;
        $taxAmount = $subtotal * $taxRate;
        $serviceCharge = $subtotal * $serviceRate;
        $total = $subtotal + $taxAmount + $serviceCharge;
        $rounding = Auth::user()->outlet->settings['rounding'] ?? 100;
        $roundedTotal = ceil($total / $rounding) * $rounding;
        
        $tab->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'service_charge_amount' => $serviceCharge,
            'total_amount' => $roundedTotal,
        ]);
    }
    
    // ==================== MERGE TAB ====================
    
    public function openMergeTabModal(): void
    {
        $this->mergeTabSourceId = null;
        $this->mergeTabTargetId = null;
        $this->showMergeTabModal = true;
    }
    
    public function closeMergeTabModal(): void
    {
        $this->showMergeTabModal = false;
        $this->mergeTabSourceId = null;
        $this->mergeTabTargetId = null;
    }
    
    public function processMergeTab(): void
    {
        if (!$this->mergeTabSourceId || !$this->mergeTabTargetId) {
            $this->dispatch('notify', message: 'Pilih tab sumber dan tab tujuan!', type: 'warning');
            return;
        }
        
        if ($this->mergeTabSourceId === $this->mergeTabTargetId) {
            $this->dispatch('notify', message: 'Tab sumber dan tujuan tidak boleh sama!', type: 'warning');
            return;
        }
        
        $sourceTab = Tab::with(['items.product', 'items.variant', 'items.modifiers', 'items.toppings'])->find($this->mergeTabSourceId);
        $targetTab = Tab::find($this->mergeTabTargetId);
        
        if (!$sourceTab || $sourceTab->status !== 'open') {
            $this->dispatch('notify', message: 'Tab sumber tidak ditemukan atau sudah ditutup!', type: 'danger');
            return;
        }
        
        if (!$targetTab || $targetTab->status !== 'open') {
            $this->dispatch('notify', message: 'Tab tujuan tidak ditemukan atau sudah ditutup!', type: 'danger');
            return;
        }
        
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
        
        // Recalculate target tab totals
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
        
        $this->closeMergeTabModal();
        $this->dispatch('notify', message: 'Tab berhasil digabung ke tab #' . $targetTab->id, type: 'success');
    }
    
    // ==================== TRANSFER TAB ====================
    
    public function openTransferTabModal($tabId): void
    {
        $tab = Tab::find($tabId);
        
        if (!$tab || $tab->status !== 'open') {
            $this->dispatch('notify', message: 'Tab tidak ditemukan atau sudah ditutup!', type: 'danger');
            return;
        }
        
        $this->transferTabId = $tab->id;
        $this->transferTabNewTableId = null;
        $this->transferTabNewCustomerName = $tab->customer_name;
        $this->showTransferTabModal = true;
    }
    
    public function closeTransferTabModal(): void
    {
        $this->showTransferTabModal = false;
        $this->transferTabId = null;
        $this->transferTabNewTableId = null;
        $this->transferTabNewCustomerName = '';
    }
    
    public function processTransferTab(): void
    {
        $tab = Tab::find($this->transferTabId);
        
        if (!$tab || $tab->status !== 'open') {
            $this->dispatch('notify', message: 'Tab tidak ditemukan atau sudah ditutup!', type: 'danger');
            return;
        }
        
        $oldTableId = $tab->table_id;
        
        $tab->update([
            'table_id' => $this->transferTabNewTableId,
            'customer_name' => $this->transferTabNewCustomerName,
        ]);
        
        // Free old table
        if ($oldTableId) {
            TableModel::where('id', $oldTableId)->update(['status' => 'available']);
        }
        
        // Occupy new table
        if ($this->transferTabNewTableId) {
            TableModel::where('id', $this->transferTabNewTableId)->update(['status' => 'occupied']);
        }
        
        $this->closeTransferTabModal();
        $this->dispatch('notify', message: 'Tab berhasil dipindah ke meja baru!', type: 'success');
    }
    
    public function render(): \Illuminate\Contracts\View\View
    {
        return view($this->view, [
            'categories' => $this->categories,
            'products' => $this->products,
            'tables' => $this->tables,
            'activeTabs' => $this->activeTabs,
        ]);
    }
}