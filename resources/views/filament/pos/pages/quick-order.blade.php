<script>
    // Initialize Theme immediately to prevent flash of light/dark mode
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
</script>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Inter:wght@400;500;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

<!-- Tailwind CSS Play CDN -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "on-tertiary-fixed": "#1d1b19",
                    "tertiary": "#5a5854",
                    "primary-fixed-dim": "#e8c08b",
                    "on-primary-fixed": "#291800",
                    "on-secondary": "#ffffff",
                    "inverse-on-surface": "#ffeede",
                    "on-primary-container": "#fff4ea",
                    "error": "#ba1a1a",
                    "background": "#fff8f4",
                    "tertiary-fixed-dim": "#cac6c1",
                    "tertiary-fixed": "#e6e2dd",
                    "surface-dim": "#eed6bd",
                    "on-surface": "#25190a",
                    "on-surface-variant": "#4e453b",
                    "on-primary": "#ffffff",
                    "outline-variant": "#d2c4b6",
                    "surface-tint": "#77592d",
                    "on-tertiary": "#ffffff",
                    "surface-container-high": "#fde4cb",
                    "error-container": "#ffdad6",
                    "on-tertiary-container": "#faf5f0",
                    "secondary-container": "#fadc9b",
                    "surface-variant": "#f7dfc5",
                    "on-secondary-container": "#76602b",
                    "secondary": "#715c27",
                    "surface-container": "#ffebd6",
                    "on-secondary-fixed": "#251a00",
                    "on-error-container": "#93000a",
                    "primary": "#705327",
                    "outline": "#807569",
                    "on-tertiary-fixed-variant": "#484643",
                    "surface-bright": "#fff8f4",
                    "inverse-primary": "#e8c08b",
                    "on-background": "#25190a",
                    "surface-container-low": "#fff1e5",
                    "surface": "#fff8f4",
                    "inverse-surface": "#3c2e1d",
                    "secondary-fixed-dim": "#e0c384",
                    "surface-container-highest": "#f7dfc5",
                    "on-secondary-fixed-variant": "#584411",
                    "surface-container-lowest": "#ffffff",
                    "on-error": "#ffffff",
                    "primary-container": "#8b6b3d",
                    "primary-fixed": "#ffddb2",
                    "tertiary-container": "#73706c",
                    "on-primary-fixed-variant": "#5d4218",
                    "secondary-fixed": "#fddf9e"
                },
                borderRadius: {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
                },
                spacing: {
                    "touch-min": "48px",
                    "gutter": "16px",
                    "stack-sm": "8px",
                    "margin-mobile": "16px",
                    "touch-ideal": "56px",
                    "margin-desktop": "32px",
                    "stack-md": "16px",
                    "stack-lg": "24px"
                },
                fontFamily: {
                    "headline-sm": ["Plus Jakarta Sans"],
                    "body-md": ["Inter"],
                    "body-sm": ["Inter"],
                    "display-lg": ["Plus Jakarta Sans"],
                    "label-caps": ["Inter"],
                    "num-lg": ["JetBrains Mono"],
                    "headline-md": ["Plus Jakarta Sans"],
                    "num-md": ["JetBrains Mono"],
                    "body-lg": ["Inter"]
                }
            }
        }
    }
</script>

<style>
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
    body {
        background-color: #F5F0EB;
        overflow: hidden;
    }
    .custom-scroll::-webkit-scrollbar { width: 4px; }
    .custom-scroll::-webkit-scrollbar-track { background: transparent; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #d2c4b6; border-radius: 10px; }
    
    @keyframes pulse-red {
        0% { box-shadow: 0 0 0 0 rgba(198, 40, 40, 0.4); }
        70% { box-shadow: 0 0 0 8px rgba(198, 40, 40, 0); }
        100% { box-shadow: 0 0 0 0 rgba(198, 40, 40, 0); }
    }
    .low-stock-pulse {
        animation: pulse-red 2s infinite;
    }

    /* Premium Coffee Dark Mode overrides */
    .dark body {
        background-color: #120e0c !important;
        color: #f3ede8 !important;
    }
    .dark header {
        background-color: #1a1512 !important;
        border-color: #2b221d !important;
    }
    .dark header span, .dark header a {
        color: #f3ede8 !important;
    }
    .dark header a.border-b-2 {
        border-color: #e8c08b !important;
    }
    .dark aside {
        background-color: #1a1512 !important;
        border-color: #2b221d !important;
    }
    .dark aside label {
        color: #d2c4b6 !important;
    }
    .dark aside button {
        background-color: #221c18 !important;
        border-color: #382d26 !important;
        color: #f3ede8 !important;
    }
    .dark aside button:hover {
        background-color: #2b231e !important;
    }
    .dark aside button.bg-primary-container {
        background-color: #8b6b3d !important;
        color: #fff8f4 !important;
        border-color: #8b6b3d !important;
    }
    .dark section {
        background-color: #120e0c !important;
    }
    .dark section div.border-b {
        border-color: #2b221d !important;
    }
    .dark section button.bg-primary {
        background-color: #8b6b3d !important;
        color: #fff8f4 !important;
    }
    .dark section button.bg-surface {
        background-color: #1a1512 !important;
        border-color: #2b221d !important;
        color: #d2c4b6 !important;
    }
    .dark section button.bg-surface:hover {
        background-color: #221c18 !important;
    }
    .dark .grid > div.bg-surface {
        background-color: #1a1512 !important;
        border-color: #2b221d !important;
        color: #f3ede8 !important;
    }
    .dark .grid > div.bg-surface:hover {
        border-color: #e8c08b !important;
    }
    .dark .grid > div.bg-surface span.font-headline-sm {
        color: #f3ede8 !important;
    }
    .dark .grid > div.bg-surface span.font-num-lg {
        color: #e8c08b !important;
    }
    
    /* Right Panel (Cart) */
    .dark aside.w-\[400px\] {
        background-color: #1a1512 !important;
        border-color: #2b221d !important;
    }
    .dark aside.w-\[400px\] h2 {
        color: #e8c08b !important;
    }
    .dark aside.w-\[400px\] div.border-b {
        background-color: #1a1512 !important;
        border-color: #2b221d !important;
    }
    .dark aside.w-\[400px\] div.pb-4 {
        border-color: #2b221d !important;
    }
    .dark aside.w-\[400px\] h3 {
        color: #f3ede8 !important;
    }
    .dark aside.w-\[400px\] span.font-num-md {
        color: #f3ede8 !important;
    }
    .dark aside.w-\[400px\] div.bg-surface-container {
        background-color: #221c18 !important;
        border-color: #2b221d !important;
    }
    .dark aside.w-\[400px\] span.text-primary {
        color: #e8c08b !important;
    }
    .dark aside.w-\[400px\] button.bg-primary {
        background-color: #8b6b3d !important;
        color: #fff8f4 !important;
    }
    .dark aside.w-\[400px\] button.bg-primary:hover {
        background-color: #a47f4c !important;
    }
    .dark aside.w-\[400px\] button.border-primary {
        border-color: #8b6b3d !important;
        color: #e8c08b !important;
    }
    .dark aside.w-\[400px\] button.border-primary:hover {
        background-color: rgba(139, 107, 61, 0.1) !important;
    }
    
    /* Modals in Dark Mode */
    .dark .bg-white.rounded-xl {
        background-color: #1a1512 !important;
        color: #f3ede8 !important;
        border: 1px solid #2b221d !important;
    }
    .dark .bg-white.rounded-xl h3 {
        color: #e8c08b !important;
    }
    .dark .bg-white.rounded-xl label {
        color: #d2c4b6 !important;
    }
    .dark .bg-white.rounded-xl input,
    .dark .bg-white.rounded-xl select,
    .dark .bg-white.rounded-xl textarea {
        background-color: #120e0c !important;
        border-color: #2b221d !important;
        color: #f3ede8 !important;
    }
    .dark .bg-white.rounded-xl button.border-gray-200 {
        border-color: #2b221d !important;
        color: #d2c4b6 !important;
    }
    .dark .bg-white.rounded-xl button.border-coffee-600 {
        border-color: #e8c08b !important;
        background-color: #2d231e !important;
        color: #e8c08b !important;
    }
</style>

<div class="h-screen flex flex-col font-body-md text-on-surface">
    <!-- TopNavBar -->
    <header class="flex justify-between items-center w-full px-margin-desktop h-touch-ideal bg-surface border-b border-outline-variant shadow-sm shrink-0">
        <div class="flex items-center gap-stack-md">
            <span class="font-headline-md text-[20px] font-bold text-primary">Warkop POS</span>
            <div class="flex ml-gutter gap-stack-md">
                <a class="text-primary border-b-2 border-primary font-bold pb-2 font-label-caps text-label-caps" href="{{ url('/pos/quick-order') }}">POS</a>
                <a class="text-on-surface-variant pb-2 font-label-caps text-label-caps hover:text-primary transition-colors" href="{{ url('/pos/shift-management') }}">Shifts</a>
                <a class="text-on-surface-variant pb-2 font-label-caps text-label-caps hover:text-primary transition-colors" href="{{ url('/admin') }}">Back to Admin</a>
            </div>
        </div>
        <div class="flex items-center gap-stack-md">
            <span class="material-symbols-outlined text-primary cursor-pointer">wifi</span>
            <span class="material-symbols-outlined text-primary cursor-pointer">notifications</span>
            <span class="material-symbols-outlined text-primary cursor-pointer" onclick="toggleThemeMode()">dark_mode</span>
            <div class="flex items-center gap-stack-sm ml-gutter">
                <span class="font-label-caps text-label-caps text-sm font-semibold">{{ Auth::user()->name }}</span>
                <span class="material-symbols-outlined text-primary text-3xl">account_circle</span>
            </div>
        </div>
    </header>

    <main class="flex flex-1 overflow-hidden">
        <!-- Left Sidebar: Table, Queue & Categories -->
        <aside class="w-80 flex flex-col bg-surface-container-low border-r border-outline-variant p-gutter gap-stack-md shrink-0 h-full overflow-y-auto custom-scroll">
            <div class="flex flex-col gap-stack-sm">
                <label class="font-label-caps text-label-caps text-on-surface-variant mb-1 font-bold text-xs tracking-wider">TABLE / QUEUE</label>
                <div class="grid grid-cols-2 gap-2">
                    <button 
                        wire:click="$set('orderType', 'dine_in')"
                        class="flex items-center justify-center h-touch-min rounded-lg font-bold border transition-colors
                            {{ $orderType === 'dine_in' 
                                ? 'bg-primary-container text-on-primary-container border-primary' 
                                : 'bg-surface border-outline-variant text-on-surface hover:bg-surface-variant' }}"
                    >
                        Dine In
                    </button>
                    <button 
                        wire:click="$set('orderType', 'takeaway')"
                        class="flex items-center justify-center h-touch-min rounded-lg font-bold border transition-colors
                            {{ $orderType === 'takeaway' 
                                ? 'bg-primary-container text-on-primary-container border-primary' 
                                : 'bg-surface border-outline-variant text-on-surface hover:bg-surface-variant' }}"
                    >
                        Take Away
                    </button>
                </div>
                
                @if($orderType === 'dine_in')
                    <div class="flex flex-col gap-2 mt-2">
                        <select 
                            wire:model.live="selectedTableId"
                            class="w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface text-on-surface focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                            <option value="">Pilih Meja</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}" class="{{ $table->status === 'occupied' ? 'text-error font-bold' : '' }}">
                                    {{ $table->label }} ({{ $table->capacity }} pax) @if($table->status === 'occupied') - Terisi @endif
                                </option>
                            @endforeach
                        </select>
                        <input 
                            wire:model.live="customerName"
                            type="text" 
                            placeholder="Nama pelanggan" 
                            class="w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface text-on-surface focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                    </div>
                @endif

                @if($activeTabs->count() > 0)
                    <div class="flex flex-col gap-2 mt-3 pt-3 border-t border-outline-variant/60">
                        <label class="font-label-caps text-label-caps text-on-surface-variant font-bold text-xs tracking-wider">TAB AKTIF</label>
                        <select 
                            wire:model="tabId"
                            wire:change="loadTab($event.target.value)"
                            class="w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface text-on-surface focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                            <option value="">Buka Tab Aktif</option>
                            @foreach($activeTabs as $tab)
                                <option value="{{ $tab->id }}">
                                    {{ $tab->customer_name ?? 'Meja '.$tab->table->number }} - Rp {{ number_format($tab->items->sum('subtotal'), 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        @if($tabId)
                            <div class="grid grid-cols-3 gap-1">
                                <button wire:click="openSplitBillModal({{ $tabId }})" class="px-1 py-1.5 text-[10px] font-bold border border-outline-variant rounded bg-surface hover:bg-surface-variant text-on-surface">Pecah</button>
                                <button wire:click="openMergeTabModal" class="px-1 py-1.5 text-[10px] font-bold border border-outline-variant rounded bg-surface hover:bg-surface-variant text-on-surface">Gabung</button>
                                <button wire:click="openTransferTabModal({{ $tabId }})" class="px-1 py-1.5 text-[10px] font-bold border border-outline-variant rounded bg-surface hover:bg-surface-variant text-on-surface">Pindah</button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex-1 flex flex-col gap-stack-sm mt-3 pt-3 border-t border-outline-variant/60">
                <label class="font-label-caps text-label-caps text-on-surface-variant mb-1 font-bold text-xs tracking-wider">CATEGORIES</label>
                <nav class="flex flex-col gap-2">
                    <button 
                        wire:click="selectCategory(null)"
                        class="flex items-center gap-3 px-4 h-touch-ideal rounded-lg font-bold transition-colors w-full text-left
                            {{ is_null($selectedCategoryId) 
                                ? 'bg-primary-container text-on-primary-container border border-primary' 
                                : 'bg-surface border border-outline-variant text-on-surface hover:bg-surface-variant' }}"
                    >
                        <span class="material-symbols-outlined">menu_book</span>
                        <span class="flex-1 truncate">Semua Kategori</span>
                    </button>
                    @foreach($categories as $category)
                        <button
                            wire:click="selectCategory({{ $category->id }})"
                            class="flex items-center gap-3 px-4 h-touch-ideal rounded-lg font-bold transition-colors w-full text-left
                                {{ $selectedCategoryId == $category->id 
                                    ? 'bg-primary-container text-on-primary-container border border-primary' 
                                    : 'bg-surface border border-outline-variant text-on-surface hover:bg-surface-variant' }}"
                        >
                            @php
                                $iconMap = [
                                    'kopi' => 'coffee',
                                    'non-kopi' => 'local_cafe',
                                    'makanan' => 'restaurant',
                                    'makanan berat' => 'restaurant',
                                    'camilan' => 'bakery_dining',
                                    'indomie' => 'ramen_dining',
                                    'rokok' => 'smoke_free',
                                ];
                                $slug = strtolower($category->slug ?? $category->name);
                                $icon = $iconMap[$slug] ?? ($category->icon ?? 'coffee');
                            @endphp
                            <span class="material-symbols-outlined" style="{{ $category->color ? 'color: ' . $category->color : '' }}">{{ $icon }}</span>
                            <span class="flex-1 truncate">{{ $category->name }}</span>
                            <span class="px-1.5 py-0.5 text-xs rounded-full bg-surface-variant/40 dark:bg-surface-container-high/40 text-[10px]">
                                {{ $category->products_count ?? 0 }}
                            </span>
                        </button>
                    @endforeach
                </nav>
            </div>
            
            <div class="flex flex-col gap-2 pt-gutter border-t border-outline-variant mt-auto">
                <div class="grid grid-cols-2 gap-2">
                    <button class="h-10 text-[10px] font-bold bg-secondary-container text-on-secondary-container rounded border border-outline-variant">F1: CARI</button>
                    <button class="h-10 text-[10px] font-bold bg-secondary-container text-on-secondary-container rounded border border-outline-variant">F2: DISKON</button>
                    <button class="h-10 text-[10px] font-bold bg-secondary-container text-on-secondary-container rounded border border-outline-variant">F3: LACI</button>
                    <button class="h-10 text-[10px] font-bold bg-secondary-container text-on-secondary-container rounded border border-outline-variant">F4: STRUK</button>
                </div>
            </div>
        </aside>

        <!-- Center Menu Grid Section -->
        <section class="flex-1 flex flex-col bg-background p-gutter gap-gutter overflow-hidden h-full">
            <div class="flex justify-between items-center border-b border-outline-variant pb-stack-md">
                <div class="flex gap-2">
                    <button class="px-4 py-1 rounded-full bg-primary text-on-primary font-label-caps text-xs font-semibold">Semua Menu</button>
                </div>
                <!-- Search bar -->
                <div class="relative w-64">
                    <input 
                        wire:model.debounce.300ms="search"
                        type="text" 
                        placeholder="Cari menu..." 
                        class="w-full pl-9 pr-4 py-1.5 border border-outline-variant rounded-full bg-surface text-on-surface focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                    >
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg">search</span>
                </div>
            </div>
            
            <div class="flex-1 overflow-y-auto custom-scroll pr-1">
                @if($products->isEmpty())
                    <div class="flex flex-col items-center justify-center h-64 text-on-surface-variant/60">
                        <span class="material-symbols-outlined text-5xl mb-2">search_off</span>
                        <p class="text-sm font-semibold">Tidak ada produk ditemukan</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-gutter">
                        @foreach($products as $product)
                            <div
                                wire:click="addToCart({{ $product->id }})"
                                class="bg-surface rounded-xl border border-outline-variant overflow-hidden shadow-sm hover:shadow-md transition-all active:scale-95 cursor-pointer relative flex flex-col group"
                            >
                                @if($product->is_bestseller)
                                    <span class="absolute top-2 right-2 bg-secondary-container text-on-secondary-container font-num-md text-[10px] px-2 py-0.5 rounded-full font-bold z-10">Best Seller</span>
                                @endif
                                
                                @if($product->track_stock && $product->stock_qty <= $product->min_stock)
                                    <span class="absolute top-2 left-2 bg-error text-on-error font-num-md text-[10px] px-2 py-0.5 rounded-full font-bold z-10">
                                        Low: {{ number_format($product->stock_qty, 0) }}
                                    </span>
                                @endif

                                <div class="h-32 bg-surface-container flex items-center justify-center overflow-hidden shrink-0">
                                    @if($product->image_path)
                                        <div class="w-full h-full bg-cover bg-center transition-transform duration-300 group-hover:scale-105" 
                                             style="background-image: url('{{ asset('storage/' . $product->image_path) }}')">
                                        </div>
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-surface-container-high text-outline">
                                            <span class="material-symbols-outlined text-4xl">image</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="p-3 flex flex-col justify-between flex-1 gap-2">
                                    <div class="flex-1">
                                        <span class="font-headline-sm text-sm leading-tight text-on-surface font-bold block">{{ $product->name }}</span>
                                        @if($product->variant_name)
                                            <span class="text-[11px] text-on-surface-variant block italic">{{ $product->variant_name }}</span>
                                        @endif
                                    </div>
                                    <span class="font-num-lg text-sm text-primary text-right mt-auto block font-semibold">
                                        Rp {{ number_format($product->effective_price ?? $product->base_price, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <!-- Right Cart Panel -->
        <aside class="w-[400px] flex flex-col bg-surface border-l border-outline-variant shadow-lg z-10 overflow-hidden h-full">
            <div class="p-gutter border-b border-outline-variant bg-surface-container-low flex justify-between items-center">
                <div>
                    <h2 class="font-headline-sm text-[16px] font-bold text-primary">
                        {{ $orderType === 'dine_in' ? ($tables->find($selectedTableId)->label ?? 'Pilih Meja') : 'Take Away' }}
                    </h2>
                    <p class="font-label-caps text-[10px] text-on-surface-variant uppercase tracking-wider">
                        {{ $customerName ? 'Pelanggan: ' . $customerName : 'ORDER ID: ' . ($tabId ? 'TAB-' . $tabId : 'BARU') }}
                    </p>
                </div>
            </div>
            
            <!-- Cart Items List -->
            <div class="flex-1 overflow-y-auto custom-scroll p-gutter space-y-4">
                @if(empty($cart))
                    <div class="h-full flex flex-col items-center justify-center text-on-surface-variant/40 p-4">
                        <span class="material-symbols-outlined text-5xl mb-2">shopping_basket</span>
                        <p class="text-sm font-semibold">Keranjang kosong</p>
                        <p class="text-xs">Klik produk di samping untuk menambahkan</p>
                    </div>
                @else
                    @foreach($cart as $item)
                        <div class="flex flex-col gap-1 pb-4 border-b border-outline-variant">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="font-bold text-on-surface text-sm">{{ $item['quantity'] }}x {{ $item['product_name'] }}</h3>
                                    @if(!empty($item['variant_name']))
                                        <p class="text-[11px] text-on-surface-variant font-medium">Varian: {{ $item['variant_name'] }}</p>
                                    @endif
                                    
                                    @if(!empty($item['modifiers']) || !empty($item['toppings']))
                                        @php
                                            $details = [];
                                            foreach($item['modifiers'] as $mod) {
                                                $details[] = $mod['name'];
                                            }
                                            foreach($item['toppings'] as $top) {
                                                $details[] = '+' . $top['name'] . ' (x' . $top['quantity'] . ')';
                                            }
                                        @endphp
                                        <p class="text-[11px] text-on-surface-variant italic">{{ implode(', ', $details) }}</p>
                                    @endif
                                    
                                    @if(!empty($item['notes']))
                                        <p class="text-[11px] text-error italic">Note: "{{ $item['notes'] }}"</p>
                                    @endif
                                </div>
                                <span class="font-num-md text-num-md font-bold text-on-surface text-sm">
                                    Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex items-center gap-4 mt-2">
                                <button 
                                    wire:click="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})"
                                    class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant bg-surface hover:bg-surface-variant text-on-surface text-lg font-bold"
                                >-</button>
                                <span class="font-num-md text-on-surface font-semibold text-sm">{{ $item['quantity'] }}</span>
                                <button 
                                    wire:click="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})"
                                    class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant bg-surface hover:bg-surface-variant text-on-surface text-lg font-bold"
                                >+</button>
                                <button 
                                    wire:click="removeFromCart('{{ $item['id'] }}')"
                                    class="ml-auto text-error flex items-center hover:text-red-700 transition-colors"
                                    title="Hapus"
                                >
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Totals Section -->
            <div class="p-gutter bg-surface-container gap-stack-sm flex flex-col border-t border-outline-variant">
                <div class="flex justify-between font-body-sm text-body-sm text-on-surface-variant">
                    <span>Subtotal</span>
                    <span class="font-num-md font-semibold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between font-body-sm text-body-sm text-on-surface-variant">
                    <span>Pajak (11%)</span>
                    <span class="font-num-md font-semibold">Rp {{ number_format($tax_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between font-body-sm text-body-sm text-on-surface-variant">
                    <span>Service (5%)</span>
                    <span class="font-num-md font-semibold">Rp {{ number_format($service_charge, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-baseline mt-2 border-t border-outline-variant pt-stack-sm">
                    <span class="font-bold text-lg">TOTAL</span>
                    <span class="font-num-lg text-[28px] font-bold text-primary">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Cart Actions -->
            <div class="p-gutter grid grid-cols-2 gap-stack-md bg-surface border-t border-outline-variant">
                <button 
                    wire:click="openPaymentModal"
                    class="col-span-2 h-touch-ideal bg-primary text-on-primary rounded-lg font-bold text-lg shadow-md active:scale-95 transition-all flex items-center justify-center gap-2 hover:bg-opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
                    @if(empty($cart)) disabled @endif
                >
                    <span class="material-symbols-outlined">payments</span> BAYAR
                </button>
                <button 
                    wire:click="saveAsTab"
                    class="h-touch-ideal border border-primary text-primary rounded-lg font-bold hover:bg-primary-container/10 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    @if(empty($cart)) disabled @endif
                >
                    SIMPAN TAB
                </button>
                <button 
                    wire:click="clearCart"
                    class="h-touch-ideal border border-error text-error rounded-lg font-bold hover:bg-error-container/10 transition-colors disabled:opacity-50"
                    @if(empty($cart)) disabled @endif
                >
                    BATAL
                </button>
            </div>
        </aside>
    </main>

    <!-- Payment Modal -->
    @if($showPaymentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10 dark:bg-zinc-900 dark:border-zinc-800">
                <h3 class="text-lg font-bold text-gray-900">Pembayaran</h3>
                <button wire:click="closePaymentModal" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-4 space-y-4">
                <div class="bg-coffee-50 border border-coffee-200 rounded-lg p-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Total Tagihan</span>
                        <span class="font-bold text-coffee-600 text-lg">Rp {{ number_format($rounded_total, 0, ',', '.') }}</span>
                    </div>
                </div>
                
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
                
                @if($paymentMethod === 'cash')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Uang Diterima</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-bold">Rp</span>
                            <input
                                wire:model.live="cashReceived"
                                type="number"
                                step="100"
                                min="{{ $rounded_total }}"
                                class="w-full pl-9 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 focus:border-transparent text-lg font-medium"
                                placeholder="0"
                            >
                        </div>
                        <div class="mt-2 flex justify-between text-sm">
                            <span class="text-gray-600">Kembalian</span>
                            <span class="font-bold text-green-600 text-lg">Rp {{ number_format($change, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-2">
                        @foreach([$rounded_total, ceil($rounded_total/10000)*10000, ceil($rounded_total/50000)*50000, ceil($rounded_total/100000)*100000] as $amount)
                            <button
                                wire:click="$set('cashReceived', {{ $amount }})"
                                class="py-2 px-3 border border-gray-200 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors"
                            >
                                Rp {{ number_format($amount, 0, ',', '.') }}
                            </button>
                        @endforeach
                    </div>
                @endif
                
                @if($paymentMethod === 'split')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Split Payment</label>
                        <div class="space-y-2">
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
                                        class="w-32 px-3 py-2 border border-gray-300 rounded-lg"
                                        placeholder="Jumlah"
                                    >
                                    <button wire:click="$dispatch('remove-split', index: {{ $index }})" class="px-3 py-2 text-red-500 hover:text-red-700">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </div>
                            @endforeach
                            <button type="button" wire:click="$dispatch('add-split')" class="text-sm text-coffee-600 hover:text-coffee-700 font-bold flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">add_circle</span> Tambah Metode
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Total split: Rp {{ number_format(collect($splitPayments)->sum('amount'), 0, ',', '.') }} / Rp {{ number_format($rounded_total, 0, ',', '.') }}</p>
                    </div>
                @endif
                
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
                        Proses Bayar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Modifier Modal -->
    @if(isset($showModifierModal) && $showModifierModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10 dark:bg-zinc-900 dark:border-zinc-800">
                <h3 class="text-lg font-bold text-gray-900">Pilih Varian & Modifier</h3>
                <button wire:click="$set('showModifierModal', false)" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-4 space-y-4">
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
                                        ({{ $variant->price_adjustment > 0 ? '+' : '' }}{{ number_format($variant->price_adjustment, 0, ',', '.') }})
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
                
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
                                            (+{{ number_format($mod->price_adjustment, 0, ',', '.') }})
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
                
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
                                            <span class="material-symbols-outlined text-xs">remove</span>
                                        </button>
                                        <span class="w-8 text-center text-sm font-medium">{{ $selectedToppingQuantities[$topping->id] ?? 0 }}</span>
                                        <button wire:click="incrementTopping({{ $topping->id }})" class="w-6 h-6 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-100">
                                            <span class="material-symbols-outlined text-xs">add</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea
                        wire:model="currentItemNotes"
                        rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500"
                        placeholder="Contoh: Kurang manis, tanpa es, dll."
                    ></textarea>
                </div>
                
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
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" wire:poll.5s="checkPaymentStatus">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10 dark:bg-zinc-900 dark:border-zinc-800">
                <h3 class="text-lg font-bold text-gray-900">Pembayaran QRIS / E-Wallet</h3>
                <button wire:click="cancelQrisPayment" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
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

                <div class="relative w-64 h-64 mx-auto border-2 border-dashed border-gray-200 rounded-xl p-2 bg-white flex items-center justify-center">
                    @if($qrisQrImageUrl)
                        <img src="{{ $qrisQrImageUrl }}" alt="QRIS Code" class="w-full h-full object-contain">
                    @elseif($qrisQrString)
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($qrisQrString) }}" alt="QRIS Code" class="w-full h-full object-contain">
                    @else
                        <div class="text-gray-400 text-sm flex flex-col items-center">
                            <span class="material-symbols-outlined animate-spin text-3xl mb-2">sync</span>
                            Membuka QR Code...
                        </div>
                    @endif
                </div>

                <div class="space-y-2">
                    <p class="text-xs text-gray-500">Silakan scan kode QRIS di atas menggunakan GoPay, OVO, DANA, LinkAja, ShopeePay, atau aplikasi Mobile Banking Anda.</p>
                    @if($qrisExpiresAt)
                        <p class="text-xs text-red-500 font-semibold flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-sm">schedule</span>
                            Kedaluwarsa pada: {{ $qrisExpiresAt->format('H:i:s') }}
                        </p>
                    @endif
                </div>

                <div class="flex flex-col gap-2 pt-4 border-t border-gray-100">
                    <button
                        wire:click="checkPaymentStatus"
                        class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                    >
                        <span class="material-symbols-outlined text-sm">sync</span> Cek Status Pembayaran
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
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10 dark:bg-zinc-900 dark:border-zinc-800">
                <h3 class="text-lg font-bold text-gray-900">Pecah Bill (Split Bill)</h3>
                <button wire:click="closeSplitBillModal" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-4 space-y-4">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">Pilih item yang ingin dipindah ke tab baru. Isi jumlah pecah untuk setiap item.</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pelanggan Tab Baru</label>
                        <input
                            wire:model="splitBillNewTabCustomerName"
                            type="text"
                            placeholder="Kosongkan untuk menggunakan nama asli + (Split)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meja Tab Baru (Opsional)</label>
                        <select
                            wire:model="splitBillNewTabTableId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 bg-white"
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
                                        <span class="text-xs text-gray-500 font-semibold">Tersedia: {{ $item['quantity'] }}</span>
                                        <div class="flex items-center gap-1">
                                            <button
                                                wire:click="$set('splitBillItems.{{ $index }}.split_quantity', Math.max(0, {{ $item['split_quantity'] }} - 1))"
                                                class="w-6 h-6 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-100 text-sm"
                                            >
                                                <span class="material-symbols-outlined text-xs">remove</span>
                                            </button>
                                            <input
                                                type="number"
                                                wire:model="splitBillItems.{{ $index }}.split_quantity"
                                                min="0"
                                                max="{{ $item['quantity'] }}"
                                                class="w-16 text-center text-sm border border-gray-300 rounded px-2 py-1 bg-white text-black"
                                            >
                                            <button
                                                wire:click="$set('splitBillItems.{{ $index }}.split_quantity', Math.min({{ $item['quantity'] }}, {{ $item['split_quantity'] }} + 1))"
                                                class="w-6 h-6 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-100 text-sm"
                                            >
                                                <span class="material-symbols-outlined text-xs">add</span>
                                            </button>
                                            <span class="text-xs text-gray-500 font-semibold">Pecah</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="flex gap-2 pt-4 border-t border-gray-200">
                    <button
                        wire:click="closeSplitBillModal"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        wire:click="processSplitBill"
                        class="flex-1 px-4 py-3 bg-coffee-600 text-white rounded-lg font-medium hover:bg-coffee-700 transition-colors flex items-center justify-center gap-2"
                    >
                        <span class="material-symbols-outlined text-sm">content_cut</span> Pecah Bill
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Merge Tab Modal -->
    @if($showMergeTabModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10 dark:bg-zinc-900 dark:border-zinc-800">
                <h3 class="text-lg font-bold text-gray-900">Gabung Tab (Merge Tab)</h3>
                <button wire:click="closeMergeTabModal" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
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
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 bg-white"
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
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 bg-white"
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
                        class="flex-1 px-4 py-3 bg-coffee-600 text-white rounded-lg font-medium hover:bg-coffee-700 transition-colors flex items-center justify-center gap-2"
                    >
                        <span class="material-symbols-outlined text-sm">join_inner</span> Gabung Tab
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Transfer Tab Modal -->
    @if($showTransferTabModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10 dark:bg-zinc-900 dark:border-zinc-800">
                <h3 class="text-lg font-bold text-gray-900">Pindah Meja (Transfer Tab)</h3>
                <button wire:click="closeTransferTabModal" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
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
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 bg-white"
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
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500"
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
                        class="flex-1 px-4 py-3 bg-coffee-600 text-white rounded-lg font-medium hover:bg-coffee-700 transition-colors flex items-center justify-center gap-2"
                    >
                        <span class="material-symbols-outlined text-sm">move_down</span> Pindah Meja
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
        // No explicit icons needed to toggle since Material Symbols updates dark mode,
        // but we keep the helper function here for safety.
    }
</script>
@endpush