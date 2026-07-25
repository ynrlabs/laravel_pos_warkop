<script setup>
import { computed } from 'vue';

const props = defineProps({
  cartItems: {
    type: Array,
    required: true,
  },
  subtotal: {
    type: Number,
    required: true,
  },
  taxAmount: {
    type: Number,
    required: true,
  },
  serviceCharge: {
    type: Number,
    required: true,
  },
  total: {
    type: Number,
    required: true,
  },
  roundedTotal: {
    type: Number,
    required: true,
  },
  change: {
    type: Number,
    required: true,
  },
  orderType: {
    type: String,
    required: true,
  },
  selectedTableId: {
    type: [Number, String, null],
    default: null,
  },
  customerName: {
    type: String,
    default: '',
  },
  tabId: {
    type: [Number, String, null],
    default: null,
  },
  tables: {
    type: Array,
    default: () => [],
  },
  isEmpty: {
    type: Boolean,
    required: true,
  },
  canPay: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits([
  'update-quantity',
  'remove-item',
  'clear-cart',
  'save-tab',
  'open-payment',
]);

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

const getTableLabel = (tableId) => {
  if (!tableId) return '';
  const table = props.tables.find(t => t.id === Number(tableId));
  return table ? table.label : `Meja ${tableId}`;
};
</script>

<template>
  <div class="flex flex-col h-full">
    <!-- Header -->
    <div class="p-gutter border-b border-outline-variant bg-surface-container-low flex justify-between items-center">
      <div>
        <h2 class="font-headline-sm text-[16px] font-bold text-primary">
          {{ orderType === 'dine_in' ? getTableLabel(selectedTableId) || 'Pilih Meja' : 'Take Away' }}
        </h2>
        <p class="font-label-caps text-[10px] text-on-surface-variant uppercase tracking-wider">
          {{ customerName ? 'Pelanggan: ' + customerName : 'ORDER ID: ' + (tabId ? 'TAB-' + tabId : 'BARU') }}
        </p>
      </div>
    </div>
    
    <!-- Cart Items List -->
    <div class="flex-1 overflow-y-auto custom-scroll p-gutter space-y-4">
      <div v-if="isEmpty" class="h-full flex flex-col items-center justify-center text-on-surface-variant/40 p-4">
        <span class="material-symbols-outlined text-5xl mb-2">shopping_basket</span>
        <p class="text-sm font-semibold">Keranjang kosong</p>
        <p class="text-xs">Klik produk di samping untuk menambahkan</p>
      </div>
      
      <div v-else>
        <div v-for="item in cartItems" :key="item.id" class="flex flex-col gap-1 pb-4 border-b border-outline-variant">
          <div class="flex justify-between items-start">
            <div class="flex-1 min-w-0">
              <h3 class="font-bold text-on-surface text-sm">{{ item.quantity }}x {{ item.product_name }}</h3>
              <p v-if="item.variant_name" class="text-[11px] text-on-surface-variant font-medium">Varian: {{ item.variant_name }}</p>
              
              <p v-if="item.modifiers.length > 0 || item.toppings.length > 0" class="text-[11px] text-on-surface-variant italic">
                <span v-for="mod in item.modifiers" :key="mod.id">{{ mod.name }}{{ mod.price_adjustment > 0 ? ` (+${formatCurrency(mod.price_adjustment)})` : '' }}, </span>
                <span v-for="top in item.toppings" :key="top.id">+{{ top.name }} x{{ top.quantity }} ({{ formatCurrency(top.price * top.quantity) }}){{ top !== item.toppings[item.toppings.length - 1] ? ', ' : '' }}</span>
              </p>
              
              <p v-if="item.notes" class="text-[11px] text-error italic">Note: "{{ item.notes }}"</p>
            </div>
            <span class="font-num-md text-num-md font-bold text-on-surface text-sm whitespace-nowrap">
              {{ formatCurrency(item.subtotal) }}
            </span>
          </div>
          
          <div class="flex items-center gap-4 mt-2">
            <button
              @click="$emit('update-quantity', item.id, item.quantity - 1)"
              class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant bg-surface hover:bg-surface-variant text-on-surface text-lg font-bold"
            >-</button>
            <span class="font-num-md text-on-surface font-semibold text-sm">{{ item.quantity }}</span>
            <button
              @click="$emit('update-quantity', item.id, item.quantity + 1)"
              class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant bg-surface hover:bg-surface-variant text-on-surface text-lg font-bold"
            >+</button>
            <button
              @click="$emit('remove-item', item.id)"
              class="ml-auto text-error flex items-center hover:text-red-700 transition-colors"
              title="Hapus"
            >
              <span class="material-symbols-outlined text-[20px]">delete</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Totals Section -->
    <div class="p-gutter bg-surface-container gap-stack-sm flex flex-col border-t border-outline-variant">
      <div class="flex justify-between font-body-sm text-body-sm text-on-surface-variant">
        <span>Subtotal</span>
        <span class="font-num-md font-semibold">{{ formatCurrency(subtotal) }}</span>
      </div>
      <div class="flex justify-between font-body-sm text-body-sm text-on-surface-variant">
        <span>Pajak (11%)</span>
        <span class="font-num-md font-semibold">{{ formatCurrency(taxAmount) }}</span>
      </div>
      <div class="flex justify-between font-body-sm text-body-sm text-on-surface-variant">
        <span>Service (5%)</span>
        <span class="font-num-md font-semibold">{{ formatCurrency(serviceCharge) }}</span>
      </div>
      <div class="flex justify-between items-baseline mt-2 border-t border-outline-variant pt-stack-sm">
        <span class="font-bold text-lg">TOTAL</span>
        <span class="font-num-lg text-[28px] font-bold text-primary">{{ formatCurrency(roundedTotal) }}</span>
      </div>
    </div>

    <!-- Cart Actions -->
    <div class="p-gutter grid grid-cols-2 gap-stack-md bg-surface border-t border-outline-variant">
      <button
        @click="$emit('open-payment')"
        class="col-span-2 h-touch-ideal bg-primary text-on-primary rounded-lg font-bold text-lg shadow-md active:scale-95 transition-all flex items-center justify-center gap-2 hover:bg-opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="isEmpty"
      >
        <span class="material-symbols-outlined">payments</span> BAYAR
      </button>
      <button
        @click="$emit('save-tab')"
        class="h-touch-ideal border border-primary text-primary rounded-lg font-bold hover:bg-primary-container/10 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="isEmpty"
      >
        SIMPAN TAB
      </button>
      <button
        @click="$emit('clear-cart')"
        class="h-touch-ideal border border-error text-error rounded-lg font-bold hover:bg-error-container/10 transition-colors disabled:opacity-50"
        :disabled="isEmpty"
      >
        BATAL
      </button>
    </div>
  </div>
</template>

<style scoped>
.custom-scroll::-webkit-scrollbar {
  width: 4px;
}
.custom-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background: #d2c4b6;
  border-radius: 10px;
}

/* Dark Mode */
.dark .bg-surface {
  background-color: #1a1512 !important;
  border-color: #2b221d !important;
  color: #f3ede8 !important;
}
.dark .bg-surface-container {
  background-color: #221c18 !important;
  border-color: #2b221d !important;
}
.dark .bg-surface-container-low {
  background-color: #1a1512 !important;
}
.dark .text-on-surface {
  color: #f3ede8 !important;
}
.dark .text-on-surface-variant {
  color: #d2c4b6 !important;
}
.dark .text-primary {
  color: #e8c08b !important;
}
.dark .border-outline-variant {
  border-color: #2b221d !important;
}
.dark .text-error {
  color: #ef9a9a !important;
}
</style>