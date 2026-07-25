<script setup>
import { onMounted, onUnmounted, ref, computed, watch } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';
import { useCartStore } from '@/Stores/cartStore';

// Components
import MenuGrid from '@/Components/POS/MenuGrid.vue';
import CartPanel from '@/Components/POS/CartPanel.vue';
import PaymentModal from '@/Components/POS/PaymentModal.vue';
import ModifierModal from '@/Components/POS/ModifierModal.vue';
import QrisModal from '@/Components/POS/QrisModal.vue';
import SplitBillModal from '@/Components/POS/SplitBillModal.vue';
import MergeTabModal from '@/Components/POS/MergeTabModal.vue';
import TransferTabModal from '@/Components/POS/TransferTabModal.vue';

const page = usePage();
const cartStore = useCartStore();
const form = useForm({});

// Notifications state
const notification = ref(null);
const showNotification = (message, type = 'info') => {
  notification.value = { message, type };
  setTimeout(() => { notification.value = null; }, 3000);
};

// Search
const searchQuery = ref(page.props?.url?.includes('search=') ? '' : '');
let searchTimeout = null;

const debouncedSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    form.get(route('pos.quick-order'), { search: searchQuery.value }, {
      preserveState: true,
      preserveScroll: true,
    });
  }, 300);
};

// Keyboard shortcuts
const handleKeydown = (e) => {
  // Space = Toggle dine-in/takeaway
  if (e.code === 'Space' && e.target === document.body) {
    e.preventDefault();
    cartStore.setOrderType(cartStore.orderType === 'dine_in' ? 'takeaway' : 'dine_in');
  }
  
  // Escape = Close modals
  if (e.key === 'Escape') {
    cartStore.closePaymentModal();
    cartStore.closeModifierModal();
    cartStore.closeQrisModal();
    cartStore.closeSplitBillModal();
    cartStore.closeMergeTabModal();
    cartStore.closeTransferTabModal();
  }
};

onMounted(() => {
  document.addEventListener('keydown', handleKeydown);
  document.body.classList.add('overflow-hidden');
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
  document.body.classList.remove('overflow-hidden');
});

// Computed from page props
const categories = computed(() => page.props.categories || []);
const products = computed(() => page.props.products || []);
const tables = computed(() => page.props.tables || []);
const activeTabs = computed(() => page.props.activeTabs || []);

// Format currency utility
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

// Theme toggle
const toggleTheme = () => {
  const isDark = document.documentElement.classList.contains('dark');
  if (isDark) {
    localStorage.setItem('theme', 'light');
    document.documentElement.classList.remove('dark');
  } else {
    localStorage.setItem('theme', 'dark');
    document.documentElement.classList.add('dark');
  }
};

// Initialize theme on mount
onMounted(() => {
  const savedTheme = localStorage.getItem('theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
    document.documentElement.classList.add('dark');
  }
});

// Actions
const handleAddToCart = (product) => {
  cartStore.addItem(product);
};

const handleSelectCategory = (categoryId) => {
  form.get(route('pos.quick-order'), { category: categoryId }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handleLoadTab = (tabId) => {
  if (!tabId) return;
  
  form.post(route('pos.tab.load', tabId), {
    preserveState: true,
    onSuccess: (page) => {
      if (page.props?.tab) {
        cartStore.loadTab(page.props.tab);
        showNotification('Tab dimuat ke keranjang!', 'success');
      }
    },
    onError: () => showNotification('Gagal memuat tab', 'error'),
  });
};

const handleSaveTab = () => {
  if (cartStore.isEmpty) {
    showNotification('Keranjang kosong!', 'warning');
    return;
  }
  
  if (!cartStore.customerName && !cartStore.selectedTableId) {
    showNotification('Isi nama pelanggan atau pilih meja!', 'warning');
    return;
  }
  
  form.post(route('pos.tab.save'), {
    cart: cartStore.items,
    order_type: cartStore.orderType,
    table_id: cartStore.selectedTableId,
    customer_name: cartStore.customerName,
  }, {
    preserveState: true,
    onSuccess: () => {
      cartStore.clearCart();
      showNotification('Tab berhasil disimpan!', 'success');
    },
    onError: () => showNotification('Gagal menyimpan tab', 'error'),
  });
};

const handleClearCart = () => {
  cartStore.clearCart();
};

const handleProcessPayment = (paymentData) => {
  form.post(route('pos.order.process'), {
    cart: cartStore.items,
    order_type: cartStore.orderType,
    table_id: cartStore.selectedTableId,
    customer_name: cartStore.customerName,
    tab_id: cartStore.tabId,
    payment: paymentData,
  }, {
    preserveState: true,
    onSuccess: (page) => {
      if (page.props?.qris_data) {
        cartStore.openQrisModal(page.props.qris_data);
      } else {
        cartStore.clearCart();
        cartStore.selectedTableId = null;
        cartStore.tabId = null;
        cartStore.customerName = '';
        cartStore.setOrderType('dine_in');
        showNotification('Pesanan berhasil diproses!', 'success');
      }
    },
    onError: () => showNotification('Gagal memproses pesanan', 'error'),
  });
};

const handleCheckQrisStatus = () => {
  if (!cartStore.qrisPaymentNumber) return;
  
  form.post(route('pos.payment.check-qris', cartStore.qrisPaymentNumber), {
    onSuccess: (page) => {
      if (page.props?.payment_status === 'paid') {
        cartStore.clearCart();
        cartStore.closeQrisModal();
        showNotification('Pembayaran QRIS berhasil!', 'success');
      }
    },
    onError: () => showNotification('Gagal cek status pembayaran', 'error'),
  });
};

const handleCancelQris = () => {
  if (!cartStore.qrisPaymentNumber) {
    cartStore.closeQrisModal();
    return;
  }
  
  form.post(route('pos.payment.cancel-qris', cartStore.qrisPaymentNumber), {
    onSuccess: () => {
      cartStore.closeQrisModal();
      showNotification('Pembayaran QRIS dibatalkan', 'info');
    },
  });
};

const handleProcessSplitBill = (splitData) => {
  form.post(route('pos.tab.split', cartStore.splitBillTabId), splitData, {
    onSuccess: () => {
      cartStore.closeSplitBillModal();
      showNotification('Bill berhasil dipecah!', 'success');
    },
    onError: () => showNotification('Gagal memecah bill', 'error'),
  });
};

const handleProcessMergeTab = () => {
  if (!cartStore.mergeTabSourceId || !cartStore.mergeTabTargetId) {
    showNotification('Pilih tab sumber dan tujuan!', 'warning');
    return;
  }
  
  if (cartStore.mergeTabSourceId === cartStore.mergeTabTargetId) {
    showNotification('Tab sumber dan tujuan tidak boleh sama!', 'warning');
    return;
  }
  
  form.post(route('pos.tab.merge'), {
    source_id: cartStore.mergeTabSourceId,
    target_id: cartStore.mergeTabTargetId,
  }, {
    onSuccess: () => {
      cartStore.closeMergeTabModal();
      showNotification('Tab berhasil digabung!', 'success');
    },
    onError: () => showNotification('Gagal menggabungkan tab', 'error'),
  });
};

const handleProcessTransferTab = () => {
  form.post(route('pos.tab.transfer', cartStore.transferTabId), {
    table_id: cartStore.transferTabNewTableId,
    customer_name: cartStore.transferTabNewCustomerName,
  }, {
    onSuccess: () => {
      cartStore.closeTransferTabModal();
      showNotification('Tab berhasil dipindah!', 'success');
    },
    onError: () => showNotification('Gagal memindah tab', 'error'),
  });
};

const handleConfirmAddToCart = () => {
  cartStore.confirmAddToCart();
  showNotification('Item ditambahkan ke keranjang', 'success');
};

const handlePrintReceipt = (orderId) => {
  form.post(route('pos.order.print', orderId), {
    onError: () => showNotification('Gagal mencetak struk', 'error'),
  });
};
</script>

<template>
  <div class="h-screen flex flex-col bg-[#F5F0EB] font-body-md text-on-surface">
    <!-- Notification Toast -->
    <Teleport to="body">
      <Transition name="toast-slide">
        <div v-if="notification" 
          :class="[
            'fixed top-4 right-4 z-[100] px-4 py-3 rounded-lg shadow-lg text-sm font-semibold transition-all duration-300',
            notification.type === 'success' ? 'bg-green-600 text-white' :
            notification.type === 'error' ? 'bg-red-600 text-white' :
            notification.type === 'warning' ? 'bg-yellow-500 text-white' :
            'bg-blue-600 text-white'
          ]"
        >
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">
              {{ notification.type === 'success' ? 'check_circle' : 
                 notification.type === 'error' ? 'error' : 
                 notification.type === 'warning' ? 'warning' : 'info' }}
            </span>
            {{ notification.message }}
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Top Navigation -->
    <header class="flex justify-between items-center w-full px-8 h-14 bg-white border-b border-gray-200 shadow-sm shrink-0 z-20">
      <div class="flex items-center gap-4">
        <span class="font-bold text-[20px]" style="color: #8B6B3D;">Warkop POS</span>
        <nav class="flex ml-4 gap-4">
          <Link :href="route('pos.quick-order')" class="text-sm font-bold pb-2 border-b-2" style="color: #8B6B3D; border-color: #8B6B3D;">
            POS Kasir
          </Link>
          <Link :href="url('/pos/shift-management')" class="text-sm pb-2 text-gray-500 hover:text-gray-700">
            Shift
          </Link>
          <Link :href="url('/admin')" class="text-sm pb-2 text-gray-500 hover:text-gray-700">
            Admin
          </Link>
        </nav>
      </div>
      
      <div class="flex items-center gap-4">
        <span class="material-symbols-outlined" style="color: #8B6B3D;">wifi</span>
        <span class="material-symbols-outlined" style="color: #8B6B3D;">notifications</span>
        <button @click="toggleTheme" class="material-symbols-outlined" style="color: #8B6B3D;">dark_mode</button>
        <div class="flex items-center gap-2 ml-4">
          <span class="text-sm font-semibold">{{ page.props?.auth?.user?.name }}</span>
          <span class="material-symbols-outlined text-3xl" style="color: #8B6B3D;">account_circle</span>
        </div>
      </div>
    </header>

    <main class="flex flex-1 overflow-hidden">
      <!-- Left Sidebar -->
      <aside class="w-80 flex flex-col bg-white border-r border-gray-200 p-4 gap-4 shrink-0 h-full overflow-y-auto">
        <!-- Order Type -->
        <div class="flex flex-col gap-2">
          <label class="font-bold text-xs tracking-wider text-gray-500 uppercase">TABLE / QUEUE</label>
          <div class="grid grid-cols-2 gap-2">
            <button @click="cartStore.setOrderType('dine_in')"
              :class="[
                'flex items-center justify-center h-12 rounded-lg font-bold border transition-colors',
                cartStore.orderType === 'dine_in'
                  ? 'text-white border-transparent' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'
              ]"
              :style="cartStore.orderType === 'dine_in' ? 'background-color: #8B6B3D;' : ''"
            >Dine In</button>
            <button @click="cartStore.setOrderType('takeaway')"
              :class="[
                'flex items-center justify-center h-12 rounded-lg font-bold border transition-colors',
                cartStore.orderType === 'takeaway'
                  ? 'text-white border-transparent' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'
              ]"
              :style="cartStore.orderType === 'takeaway' ? 'background-color: #8B6B3D;' : ''"
            >Take Away</button>
          </div>
          
          <!-- Table Selection (Dine In) -->
          <div v-if="cartStore.orderType === 'dine_in'" class="flex flex-col gap-2 mt-2">
            <select v-model="cartStore.selectedTableId" @change="handleLoadTab(cartStore.selectedTableId)"
              class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-white text-gray-800 focus:ring-2 focus:ring-[#8B6B3D]">
              <option value="">Pilih Meja</option>
              <option v-for="table in tables" :key="table.id" :value="table.id"
                :class="table.status === 'occupied' ? 'font-bold text-red-600' : ''">
                {{ table.label }} ({{ table.capacity }} pax){{ table.status === 'occupied' ? ' - Terisi' : '' }}
              </option>
            </select>
            <input v-model="cartStore.customerName" type="text" placeholder="Nama pelanggan"
              class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-white text-gray-800 focus:ring-2 focus:ring-[#8B6B3D]">
          </div>

          <!-- Active Tabs -->
          <div v-if="activeTabs.length > 0" class="flex flex-col gap-2 mt-3 pt-3 border-t border-gray-200">
            <label class="font-bold text-xs tracking-wider text-gray-500 uppercase">TAB AKTIF</label>
            <select v-model="cartStore.tabId" @change="handleLoadTab(cartStore.tabId)"
              class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-white text-gray-800 focus:ring-2 focus:ring-[#8B6B3D]">
              <option value="">Buka Tab Aktif</option>
              <option v-for="tab in activeTabs" :key="tab.id" :value="tab.id">
                {{ tab.customer_name || 'Meja ' + (tab.table?.number || '') }} - Rp {{ formatCurrency(tab.items.reduce((s, i) => s + i.subtotal, 0)) }}
              </option>
            </select>
            
            <div v-if="cartStore.tabId" class="grid grid-cols-3 gap-1">
              <button @click="cartStore.openSplitBillModal(activeTabs.find(t => t.id === cartStore.tabId))"
                class="px-1 py-1.5 text-[10px] font-bold border border-gray-200 rounded bg-white hover:bg-gray-50">Pecah</button>
              <button @click="cartStore.openMergeTabModal"
                class="px-1 py-1.5 text-[10px] font-bold border border-gray-200 rounded bg-white hover:bg-gray-50">Gabung</button>
              <button @click="cartStore.openTransferTabModal(activeTabs.find(t => t.id === cartStore.tabId))"
                class="px-1 py-1.5 text-[10px] font-bold border border-gray-200 rounded bg-white hover:bg-gray-50">Pindah</button>
            </div>
          </div>
        </div>

        <!-- Categories -->
        <div class="flex-1 flex flex-col gap-2 mt-3 pt-3 border-t border-gray-200">
          <label class="font-bold text-xs tracking-wider text-gray-500 uppercase">CATEGORIES</label>
          <nav class="flex flex-col gap-2">
            <button @click="handleSelectCategory(null)"
              :class="[
                'flex items-center gap-3 px-4 h-14 rounded-lg font-bold transition-colors w-full text-left',
                !page.props?.url?.includes('category=') && !page.props?.url?.includes('category=')
                  ? 'text-white border' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
              ]"
              :style="!page.props?.url?.includes('category=') ? 'background-color: #8B6B3D; border-color: #8B6B3D;' : ''"
            >
              <span class="material-symbols-outlined">menu_book</span>
              <span class="flex-1 truncate">Semua Kategori</span>
            </button>
            
            <button v-for="category in categories" :key="category.id" @click="handleSelectCategory(category.id)"
              :class="[
                'flex items-center gap-3 px-4 h-14 rounded-lg font-bold transition-colors w-full text-left',
                page.props?.url?.includes('category=' + category.id)
                  ? 'text-white border' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
              ]"
              :style="page.props?.url?.includes('category=' + category.id) ? 'background-color: #8B6B3D; border-color: #8B6B3D;' : ''"
            >
              <span class="material-symbols-outlined" :style="category.color ? 'color: ' + category.color : ''">
                {{ category.icon || 'coffee' }}
              </span>
              <span class="flex-1 truncate">{{ category.name }}</span>
              <span class="px-1.5 py-0.5 text-xs rounded-full bg-gray-100 text-gray-500">
                {{ category.products_count || 0 }}
              </span>
            </button>
          </nav>
        </div>

        <!-- Quick Actions -->
        <div class="flex flex-col gap-2 pt-4 border-t border-gray-200 mt-auto">
          <div class="grid grid-cols-2 gap-2">
            <button class="h-10 text-[10px] font-bold bg-yellow-100 text-yellow-800 rounded border border-yellow-200">F1: CARI</button>
            <button class="h-10 text-[10px] font-bold bg-yellow-100 text-yellow-800 rounded border border-yellow-200">F2: DISKON</button>
            <button class="h-10 text-[10px] font-bold bg-yellow-100 text-yellow-800 rounded border border-yellow-200">F3: LACI</button>
            <button class="h-10 text-[10px] font-bold bg-yellow-100 text-yellow-800 rounded border border-yellow-200">F4: STRUK</button>
          </div>
        </div>
      </aside>

      <!-- Center: Menu Grid -->
      <section class="flex-1 flex flex-col bg-[#F5F0EB] p-4 gap-4 overflow-hidden">
        <div class="flex justify-between items-center border-b border-gray-200 pb-4">
          <div class="flex gap-2">
            <button class="px-4 py-1 rounded-full text-white text-xs font-semibold" style="background-color: #8B6B3D;">Semua Menu</button>
          </div>
          
          <!-- Search Bar -->
          <div class="relative w-64">
            <input v-model="searchQuery" @input="debouncedSearch" type="text" placeholder="Cari menu..."
              class="w-full pl-9 pr-4 py-1.5 border border-gray-200 rounded-full bg-white text-gray-800 focus:ring-2 focus:ring-[#8B6B3D] text-sm">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
          </div>
        </div>
        
        <div class="flex-1 overflow-y-auto pr-1">
          <MenuGrid :products="products" :is-empty="products.length === 0" @add-to-cart="handleAddToCart" />
        </div>
      </section>

      <!-- Right: Cart Panel -->
      <aside class="w-[400px] flex flex-col bg-white border-l border-gray-200 shadow-lg z-10 overflow-hidden">
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
          @clear-cart="handleClearCart"
          @save-tab="handleSaveTab"
          @open-payment="cartStore.openPaymentModal"
        />
      </aside>
    </main>

    <!-- Modals -->
    <PaymentModal v-if="cartStore.showPaymentModal"
      :subtotal="cartStore.subtotal" :tax-amount="cartStore.taxAmount"
      :service-charge="cartStore.serviceCharge" :total="cartStore.total"
      :rounded-total="cartStore.roundedTotal" :change="cartStore.change"
      :payment-method="cartStore.paymentMethod" :cash-received="cartStore.cashReceived"
      :split-payments="cartStore.splitPayments"
      @close="cartStore.closePaymentModal"
      @update:payment-method="cartStore.paymentMethod = $event"
      @update:cash-received="cartStore.cashReceived = $event"
      @update:split-payments="cartStore.splitPayments = $event"
      @process-payment="handleProcessPayment"
      @check-qris-status="handleCheckQrisStatus"
      @cancel-qris="handleCancelQris"
      @add-split="cartStore.addSplitPayment"
      @remove-split="cartStore.removeSplitPayment"
    />

    <ModifierModal v-if="cartStore.showModifierModal"
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

    <QrisModal v-if="cartStore.showQrisModal"
      :qr-string="cartStore.qrisQrString" :qr-image-url="cartStore.qrisQrImageUrl"
      :payment-number="cartStore.qrisPaymentNumber" :amount="cartStore.qrisAmount"
      :expires-at="cartStore.qrisExpiresAt" :error-message="cartStore.qrisErrorMessage"
      @close="cartStore.closeQrisModal" @check-status="handleCheckQrisStatus"
      @cancel="handleCancelQris"
    />

    <SplitBillModal v-if="cartStore.showSplitBillModal"
      :items="cartStore.splitBillItems" :tables="tables"
      :new-tab-customer-name="cartStore.splitBillNewTabCustomerName"
      :new-tab-table-id="cartStore.splitBillNewTabTableId"
      @close="cartStore.closeSplitBillModal"
      @update:split-bill-items="cartStore.splitBillItems = $event"
      @update:split-bill-new-tab-customer-name="cartStore.splitBillNewTabCustomerName = $event"
      @update:split-bill-new-tab-table-id="cartStore.splitBillNewTabTableId = $event"
      @process="handleProcessSplitBill"
    />

    <MergeTabModal v-if="cartStore.showMergeTabModal"
      :tabs="activeTabs" :source-id="cartStore.mergeTabSourceId" :target-id="cartStore.mergeTabTargetId"
      @close="cartStore.closeMergeTabModal"
      @update:merge-tab-source-id="cartStore.mergeTabSourceId = $event"
      @update:merge-tab-target-id="cartStore.mergeTabTargetId = $event"
      @process="handleProcessMergeTab"
    />

    <TransferTabModal v-if="cartStore.showTransferTabModal"
      :tables="tables" :new-table-id="cartStore.transferTabNewTableId"
      :new-customer-name="cartStore.transferTabNewCustomerName"
      @close="cartStore.closeTransferTabModal"
      @update:transfer-tab-new-table-id="cartStore.transferTabNewTableId = $event"
      @update:transfer-tab-new-customer-name="cartStore.transferTabNewCustomerName = $event"
      @process="handleProcessTransferTab"
    />
  </div>
</template>

<style>
/* Notification toast */
.toast-slide-enter-active { transition: all 0.3s ease-out; }
.toast-slide-leave-active { transition: all 0.3s ease-in; }
.toast-slide-enter-from { transform: translateX(100%); opacity: 0; }
.toast-slide-leave-to { transform: translateX(100%); opacity: 0; }

/* Custom scrollbar */
.custom-scroll::-webkit-scrollbar { width: 4px; }
.custom-scroll::-webkit-scrollbar-track { background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb { background: #d2c4b6; border-radius: 10px; }

/* Animations */
@keyframes pulse-red {
  0% { box-shadow: 0 0 0 0 rgba(198, 40, 40, 0.4); }
  70% { box-shadow: 0 0 0 8px rgba(198, 40, 40, 0); }
  100% { box-shadow: 0 0 0 0 rgba(198, 40, 40, 0); }
}
.low-stock-pulse { animation: pulse-red 2s infinite; }

/* Dark Mode Overrides */
.dark body {
  background-color: #120e0c !important;
  color: #f3ede8 !important;
}
.dark header { background-color: #1a1512 !important; border-color: #2b221d !important; }
.dark header span, .dark header a { color: #f3ede8 !important; }
.dark aside { background-color: #1a1512 !important; border-color: #2b221d !important; }
.dark aside label { color: #d2c4b6 !important; }
.dark aside button { background-color: #221c18 !important; border-color: #382d26 !important; color: #f3ede8 !important; }
.dark aside button:hover { background-color: #2b231e !important; }
.dark section { background-color: #120e0c !important; }
.dark section button.bg-primary { background-color: #8b6b3d !important; color: #fff8f4 !important; }
.dark aside.w-\[400px\] { background-color: #1a1512 !important; border-color: #2b221d !important; }
.dark .bg-white { background-color: #1a1512 !important; }
.dark .border-gray-200 { border-color: #2b221d !important; }
.dark .text-gray-600 { color: #d2c4b6 !important; }
.dark .text-gray-500 { color: #ab9f96 !important; }
.dark input, .dark select { background-color: #120e0c !important; border-color: #2b221d !important; color: #f3ede8 !important; }
</style>