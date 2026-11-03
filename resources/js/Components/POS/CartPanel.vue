<script setup lang="ts">
import { computed } from 'vue';
import type { CartItem, Table } from '@/Types/pos';
import { ShoppingBag, Trash2, Plus, Minus, CreditCard, Bookmark, X } from 'lucide-vue-next';

interface Props {
  cartItems: CartItem[];
  subtotal: number;
  taxAmount: number;
  serviceCharge: number;
  total: number;
  roundedTotal: number;
  change: number;
  orderType: 'dine_in' | 'takeaway';
  selectedTableId?: number | string | null;
  customerName?: string;
  tabId?: number | string | null;
  tables?: Table[];
  isEmpty: boolean;
  canPay: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  selectedTableId: null,
  customerName: '',
  tabId: null,
  tables: () => [],
});

const emit = defineEmits<{
  (e: 'update-quantity', itemId: string, quantity: number): void;
  (e: 'remove-item', itemId: string): void;
  (e: 'clear-cart'): void;
  (e: 'save-tab'): void;
  (e: 'open-payment'): void;
}>();

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

const getTableLabel = (tableId: number | string | null): string => {
  if (!tableId) return '';
  const table = props.tables.find((t) => t.id === Number(tableId));
  return table ? table.label : `Meja ${tableId}`;
};
</script>

<template>
  <div class="flex flex-col h-full bg-white select-none">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-[#E8E1D9] bg-[#FAF7F2] flex items-center justify-between">
      <div>
        <div class="flex items-center gap-2">
          <span
            :class="[
              'px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider',
              orderType === 'dine_in'
                ? 'bg-[#8B6B3D]/15 text-[#8B6B3D]'
                : 'bg-amber-100 text-amber-800'
            ]"
          >
            {{ orderType === 'dine_in' ? 'Dine In' : 'Take Away' }}
          </span>
          <span v-if="orderType === 'dine_in' && selectedTableId" class="text-xs font-bold text-[#2F2212]">
            {{ getTableLabel(selectedTableId) }}
          </span>
        </div>
        <p class="text-[11px] text-stone-500 font-medium mt-1">
          {{ customerName ? `Pelanggan: ${customerName}` : (tabId ? `Tab #${tabId}` : 'Pesanan Baru') }}
        </p>
      </div>

      <button
        v-if="!isEmpty"
        type="button"
        @click="emit('clear-cart')"
        class="text-xs font-medium text-stone-400 hover:text-rose-600 transition-colors p-1"
        title="Kosongkan Keranjang"
      >
        Reset
      </button>
    </div>

    <!-- Items List -->
    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3">
      <!-- Empty State -->
      <div v-if="isEmpty" class="h-full flex flex-col items-center justify-center text-stone-400 py-12">
        <div class="w-14 h-14 rounded-full bg-stone-100 flex items-center justify-center text-stone-400 mb-3">
          <ShoppingBag class="w-7 h-7" />
        </div>
        <p class="text-sm font-semibold text-stone-600">Keranjang masih kosong</p>
        <p class="text-xs text-stone-400 mt-0.5">Pilih menu dari katalog untuk memulai pesanan</p>
      </div>

      <!-- Items -->
      <div v-else class="space-y-3">
        <div
          v-for="item in cartItems"
          :key="item.id"
          class="p-3 rounded-xl bg-[#FBF9F6] border border-[#E8E1D9] flex flex-col gap-2"
        >
          <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
              <h4 class="text-sm font-bold text-[#2F2212] leading-tight">{{ item.product_name }}</h4>
              <p v-if="item.variant_name" class="text-[11px] font-medium text-[#8B6B3D] mt-0.5">
                Varian: {{ item.variant_name }}
              </p>
              <div v-if="item.modifiers.length > 0 || item.toppings.length > 0" class="text-[11px] text-stone-500 mt-1 space-y-0.5">
                <span v-for="mod in item.modifiers" :key="mod.id" class="inline-block mr-2 text-[10px] bg-white px-1.5 py-0.5 rounded border border-[#E8E1D9]">
                  {{ mod.name }}{{ mod.price_adjustment > 0 ? ` (+${formatCurrency(mod.price_adjustment)})` : '' }}
                </span>
                <span v-for="top in item.toppings" :key="top.id" class="inline-block mr-2 text-[10px] bg-white px-1.5 py-0.5 rounded border border-[#E8E1D9]">
                  +{{ top.name }} x{{ top.quantity }} ({{ formatCurrency(top.price * top.quantity) }})
                </span>
              </div>
              <p v-if="item.notes" class="text-[10px] text-amber-800 italic mt-1 bg-amber-50 px-1.5 py-0.5 rounded">
                Catatan: "{{ item.notes }}"
              </p>
            </div>
            <span class="font-mono text-xs font-bold text-[#2F2212] whitespace-nowrap pt-0.5">
              {{ formatCurrency(item.subtotal) }}
            </span>
          </div>

          <!-- Quantity Stepper -->
          <div class="flex items-center justify-between pt-1 border-t border-[#E8E1D9]/60">
            <div class="flex items-center gap-1.5 bg-white border border-[#E8E1D9] rounded-lg p-0.5">
              <button
                type="button"
                @click="emit('update-quantity', item.id, item.quantity - 1)"
                class="w-7 h-7 flex items-center justify-center rounded text-stone-600 hover:bg-stone-100 active:scale-95 transition-all"
              >
                <Minus class="w-3.5 h-3.5" />
              </button>
              <span class="font-mono text-xs font-bold w-6 text-center text-[#2F2212]">
                {{ item.quantity }}
              </span>
              <button
                type="button"
                @click="emit('update-quantity', item.id, item.quantity + 1)"
                class="w-7 h-7 flex items-center justify-center rounded text-stone-600 hover:bg-stone-100 active:scale-95 transition-all"
              >
                <Plus class="w-3.5 h-3.5" />
              </button>
            </div>

            <button
              type="button"
              @click="emit('remove-item', item.id)"
              class="w-7 h-7 flex items-center justify-center text-stone-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors"
              title="Hapus Item"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Pricing Summary -->
    <div class="px-5 py-3.5 bg-[#FAF7F2] border-t border-[#E8E1D9] space-y-1.5 text-xs text-stone-600">
      <div class="flex justify-between">
        <span>Subtotal</span>
        <span class="font-mono font-semibold text-[#2F2212]">{{ formatCurrency(subtotal) }}</span>
      </div>
      <div class="flex justify-between text-stone-500">
        <span>Pajak (PB1 11%)</span>
        <span class="font-mono font-semibold">{{ formatCurrency(taxAmount) }}</span>
      </div>
      <div class="flex justify-between text-stone-500">
        <span>Service Charge (5%)</span>
        <span class="font-mono font-semibold">{{ formatCurrency(serviceCharge) }}</span>
      </div>
      <div class="pt-2 border-t border-[#E8E1D9] flex justify-between items-baseline">
        <span class="text-sm font-bold text-[#2F2212]">TOTAL</span>
        <span class="font-mono text-2xl font-black text-[#8B6B3D]">
          {{ formatCurrency(roundedTotal) }}
        </span>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="p-4 bg-white border-t border-[#E8E1D9] space-y-2">
      <!-- Pay Button (56px touch target) -->
      <button
        type="button"
        @click="emit('open-payment')"
        :disabled="isEmpty"
        class="w-full h-14 bg-[#8B6B3D] hover:bg-[#76582E] active:scale-[0.99] text-white rounded-xl font-bold text-base shadow-md disabled:opacity-40 disabled:pointer-events-none transition-all flex items-center justify-center gap-2 tracking-wide"
      >
        <CreditCard class="w-5 h-5" />
        BAYAR PESANAN
      </button>

      <!-- Secondary Actions -->
      <div class="grid grid-cols-2 gap-2">
        <button
          v-if="orderType === 'dine_in'"
          type="button"
          @click="emit('save-tab')"
          :disabled="isEmpty"
          class="h-11 border border-[#8B6B3D] text-[#8B6B3D] hover:bg-[#8B6B3D]/10 active:scale-[0.99] rounded-lg font-semibold text-xs transition-colors disabled:opacity-40 disabled:pointer-events-none flex items-center justify-center gap-1.5"
        >
          <Bookmark class="w-4 h-4" />
          Simpan Tab
        </button>
        <button
          type="button"
          @click="emit('clear-cart')"
          :disabled="isEmpty"
          :class="[
            'h-11 border border-stone-200 text-stone-600 hover:bg-stone-50 active:scale-[0.99] rounded-lg font-semibold text-xs transition-colors disabled:opacity-40 disabled:pointer-events-none flex items-center justify-center gap-1.5',
            orderType !== 'dine_in' ? 'col-span-2' : ''
          ]"
        >
          <X class="w-4 h-4" />
          Batal
        </button>
      </div>
    </div>
  </div>
</template>
