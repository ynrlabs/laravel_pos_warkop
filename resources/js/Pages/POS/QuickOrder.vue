<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { useCartStore } from '@/Stores/cartStore';
import type { QuickOrderPageProps, Product, PaymentData } from '@/Types/pos';

// Icons
import {
  Coffee,
  Search,
  X,
  Layers,
  Table2,
  Bookmark,
  ExternalLink,
  LogOut,
  Clock,
  User as UserIcon,
  CheckCircle2,
  AlertTriangle,
  XCircle,
  Info,
} from 'lucide-vue-next';

// Components
import MenuGrid from '@/Components/POS/MenuGrid.vue';
import CartPanel from '@/Components/POS/CartPanel.vue';
import PaymentModal from '@/Components/POS/PaymentModal.vue';
import ModifierModal from '@/Components/POS/ModifierModal.vue';
import QrisModal from '@/Components/POS/QrisModal.vue';
import SplitBillModal from '@/Components/POS/SplitBillModal.vue';
import MergeTabModal from '@/Components/POS/MergeTabModal.vue';
import TransferTabModal from '@/Components/POS/TransferTabModal.vue';

const page = usePage<QuickOrderPageProps>();
const cartStore = useCartStore();

// Props destructuring with safe fallbacks
const categories = computed(() => page.props.categories ?? []);
const products = computed<Product[]>(() => page.props.products ?? []);
const tables = computed(() => page.props.tables ?? []);
const activeTabs = computed(() => page.props.activeTabs ?? []);
const currentShift = computed(() => page.props.currentShift);
const currentUser = computed(() => page.props.auth?.user);

// Digital Clock
const currentTime = ref('');
const updateClock = () => {
  const d = new Date();
  currentTime.value = d.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  });
};
let clockInterval: ReturnType<typeof setInterval> | null = null;

// Notification state
interface Notification {
  message: string;
  type: 'success' | 'error' | 'warning' | 'info';
}
const notification = ref<Notification | null>(null);
const showNotification = (message: string, type: 'success' | 'error' | 'warning' | 'info' = 'info') => {
  notification.value = { message, type };
  setTimeout(() => {
    notification.value = null;
  }, 3500);
};

// Search & Filter
const searchQuery = ref('');
let searchTimeout: ReturnType<typeof setTimeout> | null = null;
const selectedCategory = ref<number | null>(null);

const debouncedSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilter();
  }, 300);
};

const handleSelectCategory = (categoryId: number | null) => {
  selectedCategory.value = categoryId;
  applyFilter();
};

const applyFilter = () => {
  const params: Record<string, any> = {};
  if (searchQuery.value) params.search = searchQuery.value;
  if (selectedCategory.value) params.category = selectedCategory.value;

  router.get(route('pos.quick-order'), params, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearSearch = () => {
  searchQuery.value = '';
  applyFilter();
};

// Currency helper
const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

// Handlers
const handleAddToCart = (product: Product) => {
  cartStore.addItem(product);
};

const handleLoadTab = (tabId: number | string | null) => {
  if (!tabId) return;
  const tab = activeTabs.value.find((t) => t.id === Number(tabId));
  if (tab) {
    cartStore.loadTab(tab);
    showNotification(`Tab meja ${tab.table?.number || ''} dimuat`, 'info');
  }
};

const handleSaveTab = () => {
  if (cartStore.isEmpty) {
    showNotification('Keranjang masih kosong', 'warning');
    return;
  }
  if (cartStore.orderType === 'dine_in' && !cartStore.selectedTableId) {
    showNotification('Pilih meja terlebih dahulu untuk menyimpan tab', 'warning');
    return;
  }

  router.post(
    route('pos.tab.save'),
    {
      cart: cartStore.items,
      order_type: cartStore.orderType,
      table_id: cartStore.selectedTableId,
      customer_name: cartStore.customerName,
      tab_id: cartStore.tabId,
      notes: cartStore.notes,
    },
    {
      preserveState: true,
      onSuccess: () => {
        cartStore.clearCart();
        showNotification('Tab berhasil disimpan', 'success');
      },
      onError: () => showNotification('Gagal menyimpan tab', 'error'),
    }
  );
};

const handleProcessPayment = (paymentData: PaymentData) => {
  router.post(
    route('pos.order.process'),
    {
      cart: cartStore.items,
      order_type: cartStore.orderType,
      table_id: cartStore.selectedTableId,
      customer_name: cartStore.customerName,
      tab_id: cartStore.tabId,
      payment: paymentData,
    },
    {
      preserveState: true,
      onSuccess: (page: any) => {
        cartStore.closePaymentModal();
        if (page.props?.qris_data) {
          cartStore.openQrisModal(page.props.qris_data);
        } else {
          cartStore.clearCart();
          showNotification('Pesanan berhasil dibayar dan diproses', 'success');
        }
      },
      onError: () => showNotification('Gagal memproses pembayaran', 'error'),
    }
  );
};

const handleCheckQrisStatus = () => {
  if (!cartStore.qrisPaymentNumber) return;
  router.post(route('pos.payment.check-qris', cartStore.qrisPaymentNumber), {}, {
    onSuccess: (page: any) => {
      if (page.props?.payment_status === 'paid') {
        cartStore.clearCart();
        cartStore.closeQrisModal();
        showNotification('Pembayaran QRIS berhasil dikonfirmasi', 'success');
      } else {
        showNotification('Menunggu pembayaran pelanggan...', 'info');
      }
    },
    onError: () => showNotification('Gagal memeriksa status QRIS', 'error'),
  });
};

const handleCancelQris = () => {
  if (!cartStore.qrisPaymentNumber) {
    cartStore.closeQrisModal();
    return;
  }
  router.post(route('pos.payment.cancel-qris', cartStore.qrisPaymentNumber), {}, {
    onSuccess: () => {
      cartStore.closeQrisModal();
      showNotification('Pembayaran QRIS dibatalkan', 'info');
    },
  });
};

const handleProcessSplitBill = (splitData: any) => {
  if (!cartStore.splitBillTabId) return;
  router.post(route('pos.tab.split', cartStore.splitBillTabId), splitData, {
    onSuccess: () => {
      cartStore.closeSplitBillModal();
      showNotification('Bill berhasil dipecah ke tab baru', 'success');
    },
    onError: () => showNotification('Gagal memecah bill', 'error'),
  });
};

const handleProcessMergeTab = (payload: { source_id: number; target_id: number }) => {
  router.post(route('pos.tab.merge'), payload, {
    onSuccess: () => {
      cartStore.closeMergeTabModal();
      showNotification('Tab berhasil digabungkan', 'success');
    },
    onError: () => showNotification('Gagal menggabungkan tab', 'error'),
  });
};

const handleProcessTransferTab = (payload: { table_id: number | null; customer_name: string }) => {
  if (!cartStore.transferTabId) return;
  router.post(route('pos.tab.transfer', cartStore.transferTabId), payload, {
    onSuccess: () => {
      cartStore.closeTransferTabModal();
      showNotification('Tab berhasil dipindahkan ke meja baru', 'success');
    },
    onError: () => showNotification('Gagal memindahkan tab', 'error'),
  });
};

const handleConfirmAddToCart = () => {
  cartStore.confirmAddToCart();
  showNotification('Item berhasil ditambahkan ke keranjang', 'success');
};

const handleLogout = () => {
  router.post(route('logout'));
};

// Keyboard shortcuts
const handleKeydown = (e: KeyboardEvent) => {
  // F9 = Open payment
  if (e.key === 'F9') {
    e.preventDefault();
    if (!cartStore.isEmpty) {
      cartStore.openPaymentModal();
    }
  }

  // Escape = Close modals
  if (e.key === 'Escape') {
    if (cartStore.showPaymentModal) cartStore.closePaymentModal();
    if (cartStore.showModifierModal) cartStore.closeModifierModal();
    if (cartStore.showQrisModal) cartStore.closeQrisModal();
    if (cartStore.showSplitBillModal) cartStore.closeSplitBillModal();
    if (cartStore.showMergeTabModal) cartStore.closeMergeTabModal();
    if (cartStore.showTransferTabModal) cartStore.closeTransferTabModal();
  }
};

onMounted(() => {
  updateClock();
  clockInterval = setInterval(updateClock, 1000);
  window.addEventListener('keydown', handleKeydown);

  // Sync initial query params
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('search')) searchQuery.value = urlParams.get('search')!;
  if (urlParams.has('category')) selectedCategory.value = Number(urlParams.get('category'));
});

onUnmounted(() => {
  if (clockInterval) clearInterval(clockInterval);
  window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <div class="h-screen w-screen flex flex-col bg-[#F5F0EB] text-[#2F2212] overflow-hidden select-none">
    <!-- Notification Toast -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform translate-y-[-20px] opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform translate-y-[-20px] opacity-0"
      >
        <div
          v-if="notification"
          :class="[
            'fixed top-5 left-1/2 -translate-x-1/2 z-[100] px-4 py-2.5 rounded-xl shadow-xl text-xs font-bold flex items-center gap-2 border',
            notification.type === 'success' ? 'bg-emerald-800 text-white border-emerald-700' :
            notification.type === 'error' ? 'bg-rose-800 text-white border-rose-700' :
            notification.type === 'warning' ? 'bg-amber-800 text-white border-amber-700' :
            'bg-[#2F2212] text-white border-stone-800'
          ]"
        >
          <component
            :is="notification.type === 'success' ? CheckCircle2 :
                 notification.type === 'error' ? XCircle :
                 notification.type === 'warning' ? AlertTriangle : Info"
            class="w-4 h-4 shrink-0"
          />
          <span>{{ notification.message }}</span>
        </div>
      </Transition>
    </Teleport>

    <!-- Top Navigation Header -->
    <header class="h-14 bg-white border-b border-[#E8E1D9] px-6 flex items-center justify-between shrink-0 shadow-xs z-20">
      <!-- Brand & Outlet -->
      <div class="flex items-center gap-4">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-[#8B6B3D] text-white flex items-center justify-center font-black">
            <Coffee class="w-4 h-4" />
          </div>
          <div>
            <h1 class="text-sm font-black tracking-wider text-[#2F2212] leading-none">WARKOP POS</h1>
            <p class="text-[10px] text-stone-500 font-medium mt-0.5">{{ currentUser?.outlet?.name || 'Warkop Kenangan' }}</p>
          </div>
        </div>

        <!-- Shift Indicator Badge -->
        <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-semibold">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          <span>Shift Aktif #{{ currentShift?.id || '1' }}</span>
        </div>
      </div>

      <!-- Right Header Actions: Clock, User, Admin, Logout -->
      <div class="flex items-center gap-3">
        <!-- Live Clock -->
        <div class="hidden md:flex items-center gap-1.5 px-3 py-1 bg-[#FAF7F2] border border-[#E8E1D9] rounded-lg text-xs font-mono font-bold text-stone-700">
          <Clock class="w-3.5 h-3.5 text-[#8B6B3D]" />
          <span>{{ currentTime }}</span>
        </div>

        <!-- Cashier Name -->
        <div class="flex items-center gap-2 pl-2">
          <div class="w-8 h-8 rounded-full bg-[#FAF7F2] border border-[#E8E1D9] flex items-center justify-center text-[#8B6B3D]">
            <UserIcon class="w-4 h-4" />
          </div>
          <div class="text-left hidden lg:block">
            <p class="text-xs font-bold leading-tight text-[#2F2212]">{{ currentUser?.name || 'Kasir' }}</p>
            <p class="text-[10px] text-stone-500 uppercase tracking-wider font-semibold">{{ currentUser?.role || 'Staff' }}</p>
          </div>
        </div>

        <!-- Admin Back-Office Link -->
        <a
          href="/admin"
          target="_blank"
          class="h-9 px-3 rounded-lg border border-[#E8E1D9] hover:bg-[#FAF7F2] text-xs font-semibold text-stone-700 flex items-center gap-1.5 transition-colors"
          title="Buka Admin Panel Filament"
        >
          <ExternalLink class="w-3.5 h-3.5 text-[#8B6B3D]" />
          <span class="hidden sm:inline">Admin</span>
        </a>

        <!-- Logout Button -->
        <button
          type="button"
          @click="handleLogout"
          class="h-9 px-2.5 rounded-lg border border-[#E8E1D9] hover:bg-rose-50 hover:text-rose-700 text-stone-500 text-xs font-semibold flex items-center gap-1 transition-colors"
          title="Keluar"
        >
          <LogOut class="w-3.5 h-3.5" />
        </button>
      </div>
    </header>

    <!-- Main Workspace -->
    <main class="flex flex-1 overflow-hidden">
      <!-- Left Sidebar: Order Settings & Categories -->
      <aside class="w-72 bg-white border-r border-[#E8E1D9] p-4 flex flex-col gap-4 shrink-0 overflow-y-auto">
        <!-- Order Type Segment -->
        <div class="space-y-1.5">
          <label class="text-[10px] font-bold tracking-wider uppercase text-stone-400">Mode Pesanan</label>
          <div class="grid grid-cols-2 gap-1.5 p-1 bg-[#FAF7F2] border border-[#E8E1D9] rounded-xl">
            <button
              type="button"
              @click="cartStore.setOrderType('dine_in')"
              :class="[
                'h-10 rounded-lg text-xs font-bold transition-all',
                cartStore.orderType === 'dine_in'
                  ? 'bg-[#8B6B3D] text-white shadow-xs'
                  : 'text-stone-600 hover:text-[#2F2212]'
              ]"
            >
              Dine In
            </button>
            <button
              type="button"
              @click="cartStore.setOrderType('takeaway')"
              :class="[
                'h-10 rounded-lg text-xs font-bold transition-all',
                cartStore.orderType === 'takeaway'
                  ? 'bg-[#8B6B3D] text-white shadow-xs'
                  : 'text-stone-600 hover:text-[#2F2212]'
              ]"
            >
              Take Away
            </button>
          </div>
        </div>

        <!-- Table & Customer Input (Dine In) -->
        <div v-if="cartStore.orderType === 'dine_in'" class="space-y-2 pt-1 border-t border-[#E8E1D9]">
          <div>
            <label class="text-[10px] font-bold tracking-wider uppercase text-stone-400 block mb-1">
              Pilih Meja
            </label>
            <select
              :value="cartStore.selectedTableId ?? ''"
              @change="cartStore.setSelectedTable(($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null)"
              class="w-full px-3 py-2 border border-[#E8E1D9] rounded-lg text-xs font-semibold bg-[#FAF7F2] text-stone-800 focus:ring-2 focus:ring-[#8B6B3D]"
            >
              <option value="">Pilih Meja</option>
              <option
                v-for="table in tables"
                :key="table.id"
                :value="table.id"
                :class="table.status === 'occupied' ? 'font-bold text-amber-700' : ''"
              >
                {{ table.label }} ({{ table.capacity }} pax){{ table.status === 'occupied' ? ' - Terisi' : '' }}
              </option>
            </select>
          </div>

          <div>
            <label class="text-[10px] font-bold tracking-wider uppercase text-stone-400 block mb-1">
              Nama Pelanggan
            </label>
            <input
              :value="cartStore.customerName"
              @input="cartStore.setCustomerName(($event.target as HTMLInputElement).value)"
              type="text"
              placeholder="Contoh: Bpk. Budi"
              class="w-full px-3 py-2 border border-[#E8E1D9] rounded-lg text-xs bg-[#FAF7F2] text-stone-800 focus:ring-2 focus:ring-[#8B6B3D]"
            />
          </div>
        </div>

        <!-- Active Tabs Quick Selector -->
        <div v-if="activeTabs.length > 0" class="pt-2 border-t border-[#E8E1D9] space-y-2">
          <div class="flex items-center justify-between">
            <label class="text-[10px] font-bold tracking-wider uppercase text-stone-400">
              Tab Aktif ({{ activeTabs.length }})
            </label>
          </div>
          <select
            :value="cartStore.tabId ?? ''"
            @change="handleLoadTab(($event.target as HTMLSelectElement).value)"
            class="w-full px-3 py-2 border border-[#E8E1D9] rounded-lg text-xs font-semibold bg-[#FAF7F2] text-stone-800 focus:ring-2 focus:ring-[#8B6B3D]"
          >
            <option value="">Muat Tab Aktif</option>
            <option v-for="t in activeTabs" :key="t.id" :value="t.id">
              {{ t.customer_name || 'Meja ' + (t.table?.number || '') }} - {{ formatCurrency(t.items.reduce((s, i) => s + i.subtotal, 0)) }}
            </option>
          </select>

          <!-- Tab Action Bar -->
          <div v-if="cartStore.tabId" class="grid grid-cols-3 gap-1">
            <button
              type="button"
              @click="cartStore.openSplitBillModal(activeTabs.find(t => t.id === cartStore.tabId)!)"
              class="py-1 text-[10px] font-bold border border-[#E8E1D9] rounded bg-[#FAF7F2] hover:bg-stone-100 text-stone-700"
            >
              Pecah
            </button>
            <button
              type="button"
              @click="cartStore.openMergeTabModal"
              class="py-1 text-[10px] font-bold border border-[#E8E1D9] rounded bg-[#FAF7F2] hover:bg-stone-100 text-stone-700"
            >
              Gabung
            </button>
            <button
              type="button"
              @click="cartStore.openTransferTabModal(activeTabs.find(t => t.id === cartStore.tabId)!)"
              class="py-1 text-[10px] font-bold border border-[#E8E1D9] rounded bg-[#FAF7F2] hover:bg-stone-100 text-stone-700"
            >
              Pindah
            </button>
          </div>
        </div>

        <!-- Categories List -->
        <div class="flex-1 flex flex-col gap-1.5 pt-2 border-t border-[#E8E1D9] overflow-y-auto">
          <label class="text-[10px] font-bold tracking-wider uppercase text-stone-400">Kategori Menu</label>
          <button
            type="button"
            @click="handleSelectCategory(null)"
            :class="[
              'w-full px-3.5 py-2.5 rounded-xl text-xs font-bold flex items-center justify-between transition-all text-left',
              selectedCategory === null
                ? 'bg-[#8B6B3D] text-white shadow-xs'
                : 'bg-white hover:bg-[#FAF7F2] text-stone-700 border border-[#E8E1D9]'
            ]"
          >
            <div class="flex items-center gap-2">
              <Layers class="w-4 h-4" />
              <span>Semua Menu</span>
            </div>
            <span
              :class="[
                'text-[10px] px-1.5 py-0.5 rounded-full font-mono',
                selectedCategory === null ? 'bg-white/20 text-white' : 'bg-stone-100 text-stone-500'
              ]"
            >
              {{ products.length }}
            </span>
          </button>

          <button
            v-for="cat in categories"
            :key="cat.id"
            type="button"
            @click="handleSelectCategory(cat.id)"
            :class="[
              'w-full px-3.5 py-2.5 rounded-xl text-xs font-bold flex items-center justify-between transition-all text-left',
              selectedCategory === cat.id
                ? 'bg-[#8B6B3D] text-white shadow-xs'
                : 'bg-white hover:bg-[#FAF7F2] text-stone-700 border border-[#E8E1D9]'
            ]"
          >
            <div class="flex items-center gap-2 truncate">
              <Coffee class="w-4 h-4 shrink-0" />
              <span class="truncate">{{ cat.name }}</span>
            </div>
            <span
              :class="[
                'text-[10px] px-1.5 py-0.5 rounded-full font-mono shrink-0',
                selectedCategory === cat.id ? 'bg-white/20 text-white' : 'bg-stone-100 text-stone-500'
              ]"
            >
              {{ cat.products_count ?? 0 }}
            </span>
          </button>
        </div>

        <!-- Hotkey Reference Footer -->
        <div class="pt-2 border-t border-[#E8E1D9] text-[10px] text-stone-400 space-y-1">
          <div class="flex justify-between">
            <span>F9</span>
            <span class="font-bold text-stone-600">Buka Pembayaran</span>
          </div>
          <div class="flex justify-between">
            <span>Esc</span>
            <span class="font-bold text-stone-600">Tutup Modal</span>
          </div>
        </div>
      </aside>

      <!-- Center Content Area: Search Bar & Menu Grid -->
      <section class="flex-1 flex flex-col p-5 gap-4 overflow-hidden">
        <!-- Top Filter Bar -->
        <div class="flex items-center justify-between gap-4 shrink-0">
          <div>
            <h2 class="text-lg font-black text-[#2F2212]">
              {{ selectedCategory ? (categories.find(c => c.id === selectedCategory)?.name || 'Kategori') : 'Semua Menu' }}
            </h2>
            <p class="text-xs text-stone-500">{{ products.length }} item siap dipesan</p>
          </div>

          <!-- Search Input -->
          <div class="relative w-72">
            <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400" />
            <input
              v-model="searchQuery"
              @input="debouncedSearch"
              type="text"
              placeholder="Cari menu kopi, makanan..."
              class="w-full pl-9 pr-9 py-2 border border-[#E8E1D9] rounded-xl bg-white text-xs text-stone-800 placeholder:text-stone-400 focus:ring-2 focus:ring-[#8B6B3D] focus:border-[#8B6B3D] shadow-xs"
            />
            <button
              v-if="searchQuery"
              type="button"
              @click="clearSearch"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600"
            >
              <X class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>

        <!-- Menu Grid Container -->
        <div class="flex-1 overflow-y-auto pr-1">
          <MenuGrid
            :products="products"
            :is-empty="products.length === 0"
            @add-to-cart="handleAddToCart"
          />
        </div>
      </section>

      <!-- Right Sidebar: Cart Panel -->
      <aside class="w-96 flex flex-col bg-white border-l border-[#E8E1D9] shadow-md z-10 overflow-hidden">
        <CartPanel
          :cart-items="cartStore.items"
          :subtotal="cartStore.subtotal"
          :tax-amount="cartStore.taxAmount"
          :service-charge="cartStore.serviceCharge"
          :total="cartStore.total"
          :rounded-total="cartStore.roundedTotal"
          :change="cartStore.change"
          :order-type="cartStore.orderType"
          :selected-table-id="cartStore.selectedTableId"
          :customer-name="cartStore.customerName"
          :tab-id="cartStore.tabId"
          :tables="tables"
          :is-empty="cartStore.isEmpty"
          :can-pay="cartStore.canPay"
          @update-quantity="cartStore.updateQuantity"
          @remove-item="cartStore.removeItem"
          @clear-cart="cartStore.clearCart"
          @save-tab="handleSaveTab"
          @open-payment="cartStore.openPaymentModal"
        />
      </aside>
    </main>

    <!-- Modals -->
    <ModifierModal
      v-if="cartStore.showModifierModal && cartStore.currentProduct"
      :product="cartStore.currentProduct"
      :selected-variant-id="cartStore.selectedVariantId"
      :selected-modifier-ids="cartStore.selectedModifierIds"
      :selected-topping-ids="cartStore.selectedToppingIds"
      :selected-topping-quantities="cartStore.selectedToppingQuantities"
      :current-item-notes="cartStore.currentItemNotes"
      @close="cartStore.closeModifierModal"
      @update:selected-variant-id="cartStore.selectedVariantId = $event"
      @toggle-modifier="cartStore.toggleModifier"
      @toggle-topping="cartStore.toggleTopping"
      @increment-topping="cartStore.incrementTopping"
      @decrement-topping="cartStore.decrementTopping"
      @update:current-item-notes="cartStore.currentItemNotes = $event"
      @confirm="handleConfirmAddToCart"
    />

    <PaymentModal
      v-if="cartStore.showPaymentModal"
      :subtotal="cartStore.subtotal"
      :tax-amount="cartStore.taxAmount"
      :service-charge="cartStore.serviceCharge"
      :total="cartStore.total"
      :rounded-total="cartStore.roundedTotal"
      :change="cartStore.change"
      :payment-method="cartStore.paymentMethod"
      :cash-received="cartStore.cashReceived"
      :split-payments="cartStore.splitPayments"
      @close="cartStore.closePaymentModal"
      @update:payment-method="cartStore.paymentMethod = $event"
      @update:cash-received="cartStore.cashReceived = $event"
      @update:split-payments="cartStore.splitPayments = $event"
      @process-payment="handleProcessPayment"
      @add-split="cartStore.addSplitPayment"
      @remove-split="cartStore.removeSplitPayment"
    />

    <QrisModal
      v-if="cartStore.showQrisModal"
      :qr-string="cartStore.qrisQrString"
      :qr-image-url="cartStore.qrisQrImageUrl"
      :payment-number="cartStore.qrisPaymentNumber"
      :amount="cartStore.qrisAmount"
      :expires-at="cartStore.qrisExpiresAt"
      :error-message="cartStore.qrisErrorMessage"
      @close="cartStore.closeQrisModal"
      @check-status="handleCheckQrisStatus"
      @cancel="handleCancelQris"
    />

    <SplitBillModal
      v-if="cartStore.showSplitBillModal"
      :items="cartStore.splitBillItems"
      :tables="tables"
      :new-tab-customer-name="cartStore.splitBillNewTabCustomerName"
      :new-tab-table-id="cartStore.splitBillNewTabTableId"
      @close="cartStore.closeSplitBillModal"
      @update:split-bill-items="cartStore.splitBillItems = $event"
      @update:split-bill-new-tab-customer-name="cartStore.splitBillNewTabCustomerName = $event"
      @update:split-bill-new-tab-table-id="cartStore.splitBillNewTabTableId = $event"
      @process="handleProcessSplitBill"
    />

    <MergeTabModal
      v-if="cartStore.showMergeTabModal"
      :tabs="activeTabs"
      :source-id="cartStore.mergeTabSourceId"
      :target-id="cartStore.mergeTabTargetId"
      @close="cartStore.closeMergeTabModal"
      @update:merge-tab-source-id="cartStore.mergeTabSourceId = $event"
      @update:merge-tab-target-id="cartStore.mergeTabTargetId = $event"
      @process="handleProcessMergeTab"
    />

    <TransferTabModal
      v-if="cartStore.showTransferTabModal"
      :tables="tables"
      :new-table-id="cartStore.transferTabNewTableId"
      :new-customer-name="cartStore.transferTabNewCustomerName"
      @close="cartStore.closeTransferTabModal"
      @update:transfer-tab-new-table-id="cartStore.transferTabNewTableId = $event"
      @update:transfer-tab-new-customer-name="cartStore.transferTabNewCustomerName = $event"
      @process="handleProcessTransferTab"
    />
  </div>
</template>
