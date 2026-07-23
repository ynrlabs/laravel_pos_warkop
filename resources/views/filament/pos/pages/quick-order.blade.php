<style>
    /* Dark Mode styling for Warkop POS */
    .dark .bg-white {
        background-color: #1a1615 !important; /* dark chocolate base */
        color: #f7f3f0 !important;
    }
    .dark .bg-gray-50 {
        background-color: #120e0d !important; /* deeper brown/black */
        color: #e8e2de !important;
    }
    .dark .bg-gray-100 {
        background-color: #2b2422 !important; /* medium chocolate */
        color: #f7f3f0 !important;
    }
    .dark .bg-gray-200 {
        background-color: #3b302c !important;
    }
    .dark .text-gray-900 {
        color: #f7f3f0 !important;
    }
    .dark .text-gray-700 {
        color: #dcd3cb !important;
    }
    .dark .text-gray-600 {
        color: #c4b9b0 !important;
    }
    .dark .text-gray-500 {
        color: #ab9f96 !important;
    }
    .dark .border-gray-200, 
    .dark .border-gray-300,
    .dark .border-gray-100 {
        border-color: #2b2422 !important;
    }
    .dark input, 
    .dark select, 
    .dark textarea {
        background-color: #221c1a !important;
        color: #f7f3f0 !important;
        border-color: #3d322f !important;
    }
    .dark input:focus, 
    .dark select:focus, 
    .dark textarea:focus {
        border-color: #d4a574 !important;
        --tw-ring-color: #d4a574 !important;
    }
    .dark .hover\:bg-gray-100:hover {
        background-color: #2b2422 !important;
    }
    .dark .hover\:bg-gray-200:hover {
        background-color: #3b302c !important;
    }
    .dark .bg-coffee-100 {
        background-color: #3d2f2b !important;
        color: #d4a574 !important;
    }
    .dark .text-coffee-600 {
        color: #d4a574 !important;
    }
    .dark .text-coffee-800 {
        color: #f5ede6 !important;
    }
    .dark .hover\:border-coffee-300:hover {
        border-color: #d4a574 !important;
    }
    
    /* Product list items */
    .dark button.group.bg-white {
        background-color: #1f1a18 !important;
        border-color: #2b2422 !important;
    }
    .dark button.group.bg-white:hover {
        border-color: #d4a574 !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4) !important;
    }
    .dark .bg-gray-100 {
        background-color: #26201e !important;
    }
    
    /* Cart items */
    .dark .bg-gray-50.rounded-lg.p-3 {
        background-color: #1f1a18 !important;
        border: 1px solid #2b2422 !important;
    }
    .dark .bg-coffee-50 {
        background-color: #2d2320 !important;
    }
    
    /* Modals dark theme */
    .dark .fixed.inset-0.bg-gray-500\/75 {
        background-color: rgba(0, 0, 0, 0.8) !important;
    }
</style>
<div class="h-full flex flex-col">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-4 py-3 sticky top-0 z-10">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-bold text-gray-900">Quick Order</h1>
                
                <!-- Order Type Toggle -->
                <div class="flex items-center gap-2 bg-gray-100 rounded-lg p-1">
                    <button 
                        wire:click="$set('orderType', 'dine_in')"
                        class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors
                            {{ $orderType === 'dine_in' ? 'bg-coffee-600 text-white' : 'text-gray-600 hover:text-gray-900' }}"
                    >
                        <i class="heroicon-o-home mr-1"></i> Dine In
                    </button>
                    <button 
                        wire:click="$set('orderType', 'takeaway')"
                        class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors
                            {{ $orderType === 'takeaway' ? 'bg-coffee-600 text-white' : 'text-gray-600 hover:text-gray-900' }}"
                    >
                        <i class="heroicon-o-truck mr-1"></i> Takeaway
                    </button>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Search -->
                <div class="relative">
                    <input 
                        wire:model.debounce.300ms="search"
                        type="text"
                        placeholder="Cari menu..."
                        class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 focus:border-transparent"
                    >
                    <i class="heroicon-m-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                
                <!-- Cart Summary & Theme Toggle -->
                <div class="flex items-center gap-4">
                    <!-- Theme Toggle Button -->
                    <button 
                        onclick="toggleThemeMode()" 
                        type="button"
                        class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 transition-colors border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 flex items-center justify-center"
                        title="Ubah Mode Gelap/Terang"
                    >
                        <!-- Sun icon (visible in dark mode) -->
                        <svg id="theme-icon-sun" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.243 17.657l.707-.707M7.757 6.364l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path>
                        </svg>
                        <!-- Moon icon (visible in light mode) -->
                        <svg id="theme-icon-moon" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                    
                    <span class="text-sm text-gray-600">
                        {{ count($cart) }} item
                    </span>
                    <span class="text-lg font-bold text-coffee-600">
                        Rp {{ number_format($subtotal, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Table/Tab Selection for Dine In -->
        @if($orderType === 'dine_in')
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <div class="flex items-center gap-4 flex-wrap">
                <span class="text-sm font-medium text-gray-700">Meja:</span>
                <select 
                    wire:model="selectedTableId"
                    class="flex-1 max-w-xs px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 focus:border-transparent bg-white"
                >
                    <option value="">Pilih Meja</option>
                    @foreach($tables as $table)
                        <option value="{{ $table->id }}" 
                            class="{{ $table->status === 'occupied' ? 'text-red-600' : '' }}">
                            {{ $table->label }} ({{ $table->capacity }} orang) 
                            @if($table->status === 'occupied') - Terisi @endif
                        </option>
                    @endforeach
                </select>
                
                <span class="text-sm font-medium text-gray-700">Pelanggan:</span>
                <input 
                    wire:model="customerName"
                    type="text"
                    placeholder="Nama pelanggan"
                    class="flex-1 max-w-xs px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 focus:border-transparent"
                >
                
                @if($activeTabs->count() > 0)
                    <div class="flex-1">
                        <select 
                            wire:model="tabId"
                            wire:change="loadTab($event.target.value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 focus:border-transparent bg-white"
                        >
                            <option value="">Buka Tab Aktif</option>
                            @foreach($activeTabs as $tab)
                                <option value="{{ $tab->id }}">
                                    {{ $tab->customer_name ?? 'Meja '.$tab->table->number }} - Rp {{ number_format($tab->items->sum('subtotal'), 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[250px]">
                        <div class="flex gap-1 flex-wrap">
                            <button
                                wire:click="openSplitBillModal(tabId)"
                                class="px-2 py-1.5 text-xs border border-gray-300 rounded hover:bg-gray-100 transition-colors text-gray-700"
                                title="Pecah Bill"
                                :disabled="{{ !$tabId }}"
                            >
                                <i class="heroicon-o-scissors mr-1"></i> Pecah
                            </button>
                            <button
                                wire:click="openMergeTabModal"
                                class="px-2 py-1.5 text-xs border border-gray-300 rounded hover:bg-gray-100 transition-colors text-gray-700"
                                title="Gabungkan Tab"
                            >
                                <i class="heroicon-o-arrow-path mr-1"></i> Gabung
                            </button>
                            <button
                                wire:click="openTransferTabModal(tabId)"
                                class="px-2 py-1.5 text-xs border border-gray-300 rounded hover:bg-gray-100 transition-colors text-gray-700"
                                title="Pindah Meja"
                                :disabled="{{ !$tabId }}"
                            >
                                <i class="heroicon-o-arrow-right-on-rectangle mr-1"></i> Pindah
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
    
    <!-- Main Content -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Category Sidebar -->
        <aside class="w-48 bg-gray-50 border-r border-gray-200 flex-shrink-0 overflow-y-auto">
            <nav class="p-2">
                @foreach($categories as $category)
                    <button
                        wire:click="selectCategory({{ $category->id }})"
                        class="w-full text-left px-3 py-3 rounded-lg text-sm font-medium transition-all
                            {{ $selectedCategoryId == $category->id 
                                ? 'bg-coffee-600 text-white shadow-sm' 
                                : 'text-gray-700 hover:bg-gray-200' }}"
                        style="{{ $category->color ? '--tw-bg-opacity: 1; background-color: ' . $category->color . ';' : '' }}"
                    >
                        @if($category->icon)
                            <i class="heroicon-o-{{ $category->icon }} mr-2"></i>
                        @endif
                        {{ $category->name }}
                        <span class="ml-2 px-1.5 py-0.5 text-xs rounded-full bg-white/20 backdrop-blur-sm">
                            {{ $category->products_count ?? 0 }}
                        </span>
                    </button>
                @endforeach
            </nav>
        </aside>
        
        <!-- Product Grid -->
        <main class="flex-1 overflow-y-auto p-4">
            @if($products->isEmpty())
                <div class="flex flex-col items-center justify-center h-64 text-gray-500">
                    <i class="heroicon-o-magnifying-glass text-6xl mb-4"></i>
                    <p class="text-lg">Tidak ada produk ditemukan</p>
                    <p class="text-sm">Coba ubah kategori atau kata kunci pencarian</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                    @foreach($products as $product)
                        <button
                            wire:click="addToCart({{ $product->id }})"
                            class="group relative bg-white border border-gray-200 rounded-xl p-3 hover:shadow-lg hover:border-coffee-300 transition-all duration-200 flex flex-col"
                        >
                            @if($product->image_path)
                                <img 
                                    src="{{ asset('storage/' . $product->image_path) }}" 
                                    alt="{{ $product->name }}"
                                    class="w-full h-24 object-cover rounded-lg mb-2"
                                >
                            @else
                                <div class="w-full h-24 bg-gray-100 rounded-lg mb-2 flex items-center justify-center">
                                    <i class="heroicon-o-cube text-3xl text-gray-300"></i>
                                </div>
                            @endif
                            
                            <div class="flex-1 min-h-0">
                                <h3 class="font-medium text-sm text-gray-900 line-clamp-1">{{ $product->name }}</h3>
                                @if($product->variant_name)
                                    <p class="text-xs text-gray-500">{{ $product->variant_name }}</p>
                                @endif
                            </div>
                            
                            <div class="mt-2 flex items-center justify-between">
                                <span class="font-bold text-coffee-600 text-sm">
                                    Rp {{ number_format($product->effective_price ?? $product->base_price, 0, ',', '.') }}
                                </span>
                                
                                @if($product->is_bestseller)
                                    <span class="text-xs bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded">
                                        <i class="heroicon-o-star mr-0.5"></i> Best
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Variant Indicator -->
                            @if($product->variants->count() > 1)
                                <div class="mt-1 pt-2 border-t border-gray-100">
                                    <span class="text-xs text-gray-500">{{ $product->variants->count() }} varian</span>
                                </div>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif
        </main>
        
        <!-- Cart Sidebar -->
        <aside class="w-80 bg-white border-l border-gray-200 flex-shrink-0 flex flex-col">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10">
                <h2 class="text-lg font-bold text-gray-900">Keranjang</h2>
                <span class="text-sm bg-coffee-100 text-coffee-800 px-2 py-0.5 rounded-full">
                    {{ count($cart) }} item
                </span>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                @if(empty($cart))
                    <div class="flex flex-col items-center justify-center h-full text-gray-400">
                        <i class="heroicon-o-shopping-cart text-5xl mb-3"></i>
                        <p class="text-center">Keranjang kosong</p>
                        <p class="text-xs text-gray-500">Klik produk untuk menambah</p>
                    </div>
                @else
                    @foreach($cart as $item)
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="flex items-start gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-medium text-sm text-gray-900 truncate pr-2">
                                            {{ $item['product_name'] }}
                                            @if($item['variant_name'])
                                                <span class="text-gray-500 ml-1">({{ $item['variant_name'] }})</span>
                                            @endif
                                        </h4>
                                        <button
                                            wire:click="removeFromCart('{{ $item['id'] }}')"
                                            class="text-gray-400 hover:text-red-500 transition-colors p-1"
                                        >
                                            <i class="heroicon-o-x-mark text-sm"></i>
                                        </button>
                                    </div>
                                    
                                    @if(!empty($item['modifiers']))
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($item['modifiers'] as $mod)
                                                <span class="text-xs bg-coffee-100 text-coffee-800 px-1.5 py-0.5 rounded">
                                                    {{ $mod['name'] }}
                                                    @if($mod['price_adjustment'] > 0)
                                                        +Rp {{ number_format($mod['price_adjustment'], 0, ',', '.') }}
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    @if(!empty($item['toppings']))
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($item['toppings'] as $top)
                                                <span class="text-xs bg-green-100 text-green-800 px-1.5 py-0.5 rounded">
                                                    {{ $top['name'] }} x{{ $top['quantity'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    @if($item['notes'])
                                        <p class="text-xs text-gray-500 mt-1 italic">{{ $item['notes'] }}</p>
                                    @endif
                                </div>
                                
                                <div class="flex items-center gap-2 ml-2">
                                    <button
                                        wire:click="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})"
                                        class="w-7 h-7 rounded border border-gray-300 flex items-center justify-center hover:bg-gray-100 transition-colors"
                                    >
                                        <i class="heroicon-o-minus text-sm"></i>
                                    </button>
                                    <span class="w-8 text-center text-sm font-medium">{{ $item['quantity'] }}</span>
                                    <button
                                        wire:click="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})"
                                        class="w-7 h-7 rounded border border-gray-300 flex items-center justify-center hover:bg-gray-100 transition-colors"
                                    >
                                        <i class="heroicon-o-plus text-sm"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mt-2 text-right">
                                <span class="font-bold text-coffee-600">
                                    Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            
            <!-- Cart Summary & Actions -->
            <div class="p-4 border-t border-gray-200 bg-gray-50 space-y-3">
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Pajak ({{ (Auth::user()->outlet->settings['tax_rate'] ?? 0.11) * 100 }}%)</span>
                        <span class="font-medium">Rp {{ number_format($tax_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Service ({{ (Auth::user()->outlet->settings['service_charge'] ?? 0.05) * 100 }}%)</span>
                        <span class="font-medium">Rp {{ number_format($service_charge, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-1">
                        <span class="text-gray-600">Total</span>
                        <span class="font-bold text-coffee-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                    @if($rounded_total != $total)
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Pembulatan</span>
                            <span>Rp {{ number_format($rounded_total - $total, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-lg font-bold text-coffee-600 border-t border-gray-200 pt-2">
                        <span>Bayar</span>
                        <span>Rp {{ number_format($rounded_total, 0, ',', '.') }}</span>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    @if($orderType === 'dine_in')
                        <button
                            wire:click="saveAsTab"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
                        >
                            <i class="heroicon-o-bookmark mr-1"></i> Simpan Tab
                        </button>
                    @endif
                    
                    <button
                        wire:click="openPaymentModal"
                        class="flex-1 px-4 py-3 bg-coffee-600 text-white rounded-lg font-medium hover:bg-coffee-700 transition-colors"
                        :disabled="{{ empty($cart) }}"
                    >
                        <i class="heroicon-o-credit-card mr-2"></i> Bayar
                    </button>
                </div>
                
                @if(!empty($cart))
                    <button
                        wire:click="clearCart"
                        class="w-full px-4 py-2 text-sm text-gray-500 hover:text-red-600 transition-colors"
                    >
                        <i class="heroicon-o-trash mr-1"></i> Kosongkan Keranjang
                    </button>
                @endif
            </div>
        </aside>
    </div>
    
    <!-- Payment Modal -->
    @if($showPaymentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10">
                <h3 class="text-lg font-bold text-gray-900">Pembayaran</h3>
                <button wire:click="closePaymentModal" class="text-gray-400 hover:text-gray-600">
                    <i class="heroicon-o-x-mark text-xl"></i>
                </button>
            </div>
            
            <div class="p-4 space-y-4">
                <!-- Total to Pay -->
                <div class="bg-coffee-50 border border-coffee-200 rounded-lg p-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Total Tagihan</span>
                        <span class="font-bold text-coffee-600">Rp {{ number_format($rounded_total, 0, ',', '.') }}</span>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['cash' => 'Tunai', 'qris' => 'QRIS', 'ewallet' => 'E-Wallet', 'card' => 'Kartu', 'split' => 'Split'] as $method => $label)
                            <button
                                wire:click="$set('paymentMethod', '{{ $method }}')"
                                class="px-3 py-2 rounded-lg border-2 text-sm font-medium transition-all
                                    {{ $paymentMethod === $method ? 'border-coffee-600 bg-coffee-50 text-coffee-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
                
                <!-- Cash Input -->
                @if($paymentMethod === 'cash')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Uang Diterima</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                            <input
                                wire:model.live="cashReceived"
                                type="number"
                                step="100"
                                min="{{ $rounded_total }}"
                                class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 focus:border-transparent text-lg font-medium"
                                placeholder="0"
                            >
                        </div>
                        <div class="mt-2 flex justify-between text-sm">
                            <span class="text-gray-600">Kembalian</span>
                            <span class="font-bold text-green-600 text-lg">Rp {{ number_format($change, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endif
                
                <!-- Split Payment -->
                @if($paymentMethod === 'split')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Split Payment</label>
                        <div class="space-y-2" id="split-payments">
                            @foreach($splitPayments as $index => $split)
                                <div class="flex gap-2">
                                    <select wire:model="splitPayments.{{ $index }}.method" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
                                        <option value="cash">Tunai</option>
                                        <option value="qris">QRIS</option>
                                        <option value="ewallet">E-Wallet</option>
                                        <option value="card">Kartu</option>
                                    </select>
                                    <input
                                        wire:model="splitPayments.{{ $index }}.amount"
                                        type="number"
                                        step="100"
                                        min="100"
                                        class="w-32 px-3 py-2 border border-gray-300 rounded-lg"
                                        placeholder="Jumlah"
                                    >
                                    <button wire:click="$dispatch('remove-split', index: {{ $index }})" class="px-3 py-2 text-red-500 hover:text-red-700">
                                        <i class="heroicon-o-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                            <button type="button" wire:click="$dispatch('add-split')" class="text-sm text-coffee-600 hover:text-coffee-700">
                                <i class="heroicon-o-plus-circle mr-1"></i> Tambah Metode
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Total split: Rp {{ number_format(collect($splitPayments)->sum('amount'), 0, ',', '.') }} / Rp {{ number_format($rounded_total, 0, ',', '.') }}</p>
                    </div>
                @endif
                
                <!-- Quick Cash Buttons -->
                @if($paymentMethod === 'cash')
                    <div class="grid grid-cols-4 gap-2">
                        @foreach([$rounded_total, ceil($rounded_total/10000)*10000, ceil($rounded_total/50000)*50000, ceil($rounded_total/100000)*100000] as $amount)
                            <button
                                wire:click="$set('cashReceived', {{ $amount }})"
                                class="py-2 px-3 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                            >
                                Rp {{ number_format($amount, 0, ',', '.') }}
                            </button>
                        @endforeach
                    </div>
                @endif
                
                <!-- Action Buttons -->
                <div class="flex gap-2 pt-4 border-t border-gray-200">
                    <button
                        wire:click="closePaymentModal"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        wire:click="processPayment"
                        class="flex-1 px-4 py-3 bg-coffee-600 text-white rounded-lg font-medium hover:bg-coffee-700 transition-colors"
                        :disabled="{{ $paymentMethod === 'cash' && $cashReceived < $rounded_total }}"
                    >
                        Proses Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Modifier Modal -->
    @if(isset($showModifierModal) && $showModifierModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10">
                <h3 class="text-lg font-bold text-gray-900">Pilih Varian & Modifier</h3>
                <button wire:click="$set('showModifierModal', false)" class="text-gray-400 hover:text-gray-600">
                    <i class="heroicon-o-x-mark text-xl"></i>
                </button>
            </div>
            
            <div class="p-4 space-y-4">
                <!-- Variants -->
                @if(isset($currentProduct) && $currentProduct->variants->count() > 0)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Varian</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($currentProduct->variants as $variant)
                                <button
                                    wire:click="$set('selectedVariantId', {{ $variant->id }})"
                                    class="px-4 py-2 border rounded-lg text-sm font-medium transition-all
                                        {{ $selectedVariantId == $variant->id ? 'border-coffee-600 bg-coffee-50 text-coffee-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}"
                                >
                                    {{ $variant->name }}
                                    @if($variant->price_adjustment != 0)
                                        {{ $variant->price_adjustment > 0 ? '+' : '' }}{{ number_format($variant->price_adjustment, 0, ',', '.') }}
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <!-- Modifier Groups -->
                @if(isset($currentProduct) && $currentProduct->modifierGroups->count() > 0)
                    @foreach($currentProduct->modifierGroups as $mg)
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-medium text-gray-700">
                                    {{ $mg->name }}
                                    @if($mg->is_required)
                                        <span class="text-red-500 ml-1">*</span>
                                    @endif
                                </label>
                                <span class="text-xs text-gray-500">
                                    {{ $mg->selection_type === 'single' ? 'Pilih 1' : 'Pilih hingga ' . ($mg->max_selection ?? 'bebas') }}
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($mg->modifiers as $mod)
                                    <button
                                        wire:click="toggleModifier({{ $mod->id }}, '{{ $mg->selection_type }}')"
                                        class="px-3 py-1.5 border rounded-lg text-sm transition-all
                                            {{ in_array($mod->id, $selectedModifierIds ?? []) ? 'border-coffee-600 bg-coffee-50 text-coffee-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}"
                                    >
                                        {{ $mod->name }}
                                        @if($mod->price_adjustment > 0)
                                            +{{ number_format($mod->price_adjustment, 0, ',', '.') }}
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
                
                <!-- Toppings -->
                @if(isset($currentProduct) && $currentProduct->toppings->count() > 0)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Topping / Extra</label>
                        <div class="space-y-2">
                            @foreach($currentProduct->toppings as $topping)
                                <div class="flex items-center justify-between p-2 border border-gray-200 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="checkbox"
                                            wire:model="selectedToppingIds"
                                            value="{{ $topping->id }}"
                                            class="w-4 h-4 text-coffee-600 border-gray-300 rounded focus:ring-coffee-500"
                                        >
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $topping->name }}</p>
                                            <p class="text-xs text-gray-500">+Rp {{ number_format($topping->price, 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button wire:click="decrementTopping({{ $topping->id }})" class="w-6 h-6 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-100">
                                            <i class="heroicon-o-minus text-xs"></i>
                                        </button>
                                        <span class="w-8 text-center text-sm font-medium">{{ $selectedToppingQuantities[$topping->id] ?? 0 }}</span>
                                        <button wire:click="incrementTopping({{ $topping->id }})" class="w-6 h-6 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-100">
                                            <i class="heroicon-o-plus text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea
                        wire:model="currentItemNotes"
                        rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 focus:border-transparent"
                        placeholder="Contoh: Kurang manis, tanpa es, dll."
                    ></textarea>
                </div>
                
                <!-- Add to Cart Button -->
                <button
                    wire:click="confirmAddToCart"
                    class="w-full px-4 py-3 bg-coffee-600 text-white rounded-lg font-medium hover:bg-coffee-700 transition-colors"
                >
                    Tambah ke Keranjang
                </button>
            </div>
        </div>
    </div>
    @endif
    
    <!-- QRIS Payment Modal -->
    @if($showQrisModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" wire:poll.5s="checkPaymentStatus">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10">
                <h3 class="text-lg font-bold text-gray-900">Pembayaran QRIS / E-Wallet</h3>
                <button wire:click="cancelQrisPayment" class="text-gray-400 hover:text-gray-600">
                    <i class="heroicon-o-x-mark text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 space-y-6 text-center">
                <div>
                    <p class="text-sm text-gray-500">Nomor Invoice</p>
                    <p class="font-semibold text-gray-800">{{ $qrisPaymentNumber }}</p>
                </div>

                <div class="bg-coffee-50 border border-coffee-200 rounded-lg p-4 max-w-xs mx-auto">
                    <p class="text-xs text-gray-600 uppercase tracking-wide font-semibold">Total yang Harus Dibayar</p>
                    <p class="text-2xl font-bold text-coffee-600 mt-1">Rp {{ number_format($qrisAmount, 0, ',', '.') }}</p>
                </div>

                <!-- QR Code Display -->
                <div class="relative w-64 h-64 mx-auto border-2 border-dashed border-gray-200 rounded-xl p-2 bg-white flex items-center justify-center">
                    @if($qrisQrImageUrl)
                        <img src="{{ $qrisQrImageUrl }}" alt="QRIS Code" class="w-full h-full object-contain">
                    @elseif($qrisQrString)
                        <!-- Fallback using public API if image url not directly provided -->
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($qrisQrString) }}" alt="QRIS Code" class="w-full h-full object-contain">
                    @else
                        <div class="text-gray-400 text-sm flex flex-col items-center">
                            <i class="heroicon-o-arrow-path animate-spin text-3xl mb-2"></i>
                            Membuka QR Code...
                        </div>
                    @endif
                </div>

                <div class="space-y-2">
                    <p class="text-xs text-gray-500">Silakan scan kode QRIS di atas menggunakan GoPay, OVO, DANA, LinkAja, ShopeePay, atau aplikasi Mobile Banking Anda.</p>
                    @if($qrisExpiresAt)
                        <p class="text-xs text-red-500 font-semibold flex items-center justify-center gap-1">
                            <i class="heroicon-o-clock"></i>
                            Kedaluwarsa pada: {{ $qrisExpiresAt->format('H:i:s') }}
                        </p>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-2 pt-4 border-t border-gray-100">
                    <button
                        wire:click="checkPaymentStatus"
                        class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                    >
                        <i class="heroicon-o-arrow-path"></i> Cek Status Pembayaran
                    </button>
                    <button
                        wire:click="cancelQrisPayment"
                        class="w-full px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-600 rounded-lg text-sm font-medium transition-colors"
                    >
                        Batalkan & Kembali
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Split Bill Modal -->
    @if($showSplitBillModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10">
                <h3 class="text-lg font-bold text-gray-900">Pecah Bill (Split Bill)</h3>
                <button wire:click="closeSplitBillModal" class="text-gray-400 hover:text-gray-600">
                    <i class="heroicon-o-x-mark text-xl"></i>
                </button>
            </div>
            
            <div class="p-4 space-y-4">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">Pilih item yang ingin dipindah ke tab baru. Isi jumlah pecah untuk setiap item.</p>
                </div>
                
                <!-- New Tab Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pelanggan Tab Baru</label>
                        <input
                            wire:model="splitBillNewTabCustomerName"
                            type="text"
                            placeholder="Kosongkan untuk menggunakan nama asli + (Split)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 focus:border-transparent"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meja Tab Baru (Opsional)</label>
                        <select
                            wire:model="splitBillNewTabTableId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 focus:border-transparent bg-white"
                        >
                            <option value="">Tanpa Meja</option>
                            @foreach($tables as $table)
                                @if($table->status === 'available')
                                    <option value="{{ $table->id }}">{{ $table->label }} ({{ $table->capacity }} orang)</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Items to Split -->
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach($splitBillItems as $index => $item)
                        <div class="border border-gray-200 rounded-lg p-3">
                            <div class="flex items-start gap-3">
                                <input
                                    type="checkbox"
                                    wire:model="splitBillItems.{{ $index }}.selected"
                                    class="w-4 h-4 mt-0.5 text-coffee-600 border-gray-300 rounded focus:ring-coffee-500"
                                >
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-medium text-sm text-gray-900 truncate pr-2">
                                            {{ $item['product_name'] }}
                                            @if($item['variant_name'])
                                                <span class="text-gray-500 ml-1">({{ $item['variant_name'] }})</span>
                                            @endif
                                        </h4>
                                        <span class="font-bold text-coffee-600 text-sm ml-2">
                                            Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                    
                                    @if(!empty($item['modifiers']))
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($item['modifiers'] as $mod)
                                                <span class="text-xs bg-coffee-100 text-coffee-800 px-1.5 py-0.5 rounded">
                                                    {{ $mod['name'] }}
                                                    @if($mod['price_adjustment'] > 0)
                                                        +Rp {{ number_format($mod['price_adjustment'], 0, ',', '.') }}
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    @if(!empty($item['toppings']))
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($item['toppings'] as $top)
                                                <span class="text-xs bg-green-100 text-green-800 px-1.5 py-0.5 rounded">
                                                    {{ $top['name'] }} x{{ $top['quantity'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    <div class="mt-2 flex items-center gap-4">
                                        <span class="text-xs text-gray-500">Tersedia: {{ $item['quantity'] }}</span>
                                        <div class="flex items-center gap-1">
                                            <button
                                                wire:click="$set('splitBillItems.{{ $index }}.split_quantity', Math.max(0, {{ $item['split_quantity'] }} - 1))"
                                                class="w-6 h-6 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-100 text-sm"
                                            >
                                                <i class="heroicon-o-minus"></i>
                                            </button>
                                            <input
                                                type="number"
                                                wire:model="splitBillItems.{{ $index }}.split_quantity"
                                                min="0"
                                                max="{{ $item['quantity'] }}"
                                                class="w-16 text-center text-sm border border-gray-300 rounded px-2 py-1"
                                            >
                                            <button
                                                wire:click="$set('splitBillItems.{{ $index }}.split_quantity', Math.min({{ $item['quantity'] }}, {{ $item['split_quantity'] }} + 1))"
                                                class="w-6 h-6 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-100 text-sm"
                                            >
                                                <i class="heroicon-o-plus"></i>
                                            </button>
                                            <span class="text-xs text-gray-500">Pecah</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-2 pt-4 border-t border-gray-200">
                    <button
                        wire:click="closeSplitBillModal"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        wire:click="processSplitBill"
                        class="flex-1 px-4 py-3 bg-coffee-600 text-white rounded-lg font-medium hover:bg-coffee-700 transition-colors"
                    >
                        <i class="heroicon-o-scissors mr-2"></i> Pecah Bill
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Merge Tab Modal -->
    @if($showMergeTabModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10">
                <h3 class="text-lg font-bold text-gray-900">Gabung Tab (Merge Tab)</h3>
                <button wire:click="closeMergeTabModal" class="text-gray-400 hover:text-gray-600">
                    <i class="heroicon-o-x-mark text-xl"></i>
                </button>
            </div>
            
            <div class="p-4 space-y-4">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">Pilih tab sumber (akan dikosongkan) dan tab tujuan (akan menerima item). Tab sumber akan ditutup setelah digabung.</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tab Sumber (Item akan dipindahkan dari sini)</label>
                    <select
                        wire:model="mergeTabSourceId"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 focus:border-transparent bg-white"
                    >
                        <option value="">Pilih Tab Sumber</option>
                        @foreach($activeTabs as $tab)
                            <option value="{{ $tab->id }}">
                                {{ $tab->customer_name ?? 'Meja '.$tab->table->number }} - Rp {{ number_format($tab->items->sum('subtotal'), 0, ',', '.') }} ({{ $tab->items->count() }} item)
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tab Tujuan (Item akan ditambahkan ke sini)</label>
                    <select
                        wire:model="mergeTabTargetId"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 focus:border-transparent bg-white"
                    >
                        <option value="">Pilih Tab Tujuan</option>
                        @foreach($activeTabs as $tab)
                            <option value="{{ $tab->id }}">
                                {{ $tab->customer_name ?? 'Meja '.$tab->table->number }} - Rp {{ number_format($tab->items->sum('subtotal'), 0, ',', '.') }} ({{ $tab->items->count() }} item)
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex gap-2 pt-4 border-t border-gray-200">
                    <button
                        wire:click="closeMergeTabModal"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        wire:click="processMergeTab"
                        class="flex-1 px-4 py-3 bg-coffee-600 text-white rounded-lg font-medium hover:bg-coffee-700 transition-colors"
                    >
                        <i class="heroicon-o-arrow-path mr-2"></i> Gabung Tab
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Transfer Tab Modal -->
    @if($showTransferTabModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10">
                <h3 class="text-lg font-bold text-gray-900">Pindah Meja (Transfer Tab)</h3>
                <button wire:click="closeTransferTabModal" class="text-gray-400 hover:text-gray-600">
                    <i class="heroicon-o-x-mark text-xl"></i>
                </button>
            </div>
            
            <div class="p-4 space-y-4">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">Pindahkan tab ke meja lain. Meja lama akan tersedia kembali.</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Meja Baru</label>
                    <select
                        wire:model="transferTabNewTableId"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 focus:border-transparent bg-white"
                    >
                        <option value="">Tanpa Meja (Antrian)</option>
                        @foreach($tables as $table)
                            @if($table->status === 'available')
                                <option value="{{ $table->id }}">{{ $table->label }} ({{ $table->capacity }} orang)</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pelanggan</label>
                    <input
                        wire:model="transferTabNewCustomerName"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 focus:border-transparent"
                        placeholder="Nama pelanggan"
                    >
                </div>
                
                <div class="flex gap-2 pt-4 border-t border-gray-200">
                    <button
                        wire:click="closeTransferTabModal"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        wire:click="processTransferTab"
                        class="flex-1 px-4 py-3 bg-coffee-600 text-white rounded-lg font-medium hover:bg-coffee-700 transition-colors"
                    >
                        <i class="heroicon-o-arrow-right-on-rectangle mr-2"></i> Pindah Meja
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    // Handle split payment dynamic fields
    document.addEventListener('livewire:load', () => {
        Livewire.on('add-split', () => {
            @this.$wire.call('addSplitPayment');
        });
        
        Livewire.on('remove-split', (index) => {
            @this.$wire.call('removeSplitPayment', index);
        });
    });

    function toggleThemeMode() {
        const isDark = document.documentElement.classList.contains('dark');
        if (isDark) {
            localStorage.setItem('theme', 'light');
            document.documentElement.classList.remove('dark');
        } else {
            localStorage.setItem('theme', 'dark');
            document.documentElement.classList.add('dark');
        }
        window.dispatchEvent(new Event('theme-changed'));
        updateToggleIcons();
    }

    function updateToggleIcons() {
        const isDark = document.documentElement.classList.contains('dark');
        const sun = document.getElementById('theme-icon-sun');
        const moon = document.getElementById('theme-icon-moon');
        if (sun && moon) {
            if (isDark) {
                sun.classList.remove('hidden');
                moon.classList.add('hidden');
            } else {
                sun.classList.add('hidden');
                moon.classList.remove('hidden');
            }
        }
    }

    // Initialize icons
    setTimeout(updateToggleIcons, 100);
    document.addEventListener('livewire:navigated', updateToggleIcons);
</script>
@endpush