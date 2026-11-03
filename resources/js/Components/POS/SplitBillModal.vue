<script setup lang="ts">
import { computed } from 'vue';
import type { Table } from '@/Types/pos';
import { X, Plus, Minus, Split } from 'lucide-vue-next';

interface SplitItem {
  id: number;
  product_name: string;
  variant_name?: string | null;
  unit_price: number;
  quantity: number;
  split_quantity?: number;
  selected?: boolean;
}

interface Props {
  items: SplitItem[];
  tables?: Table[];
  newTabCustomerName?: string;
  newTabTableId?: number | string | null;
}

const props = withDefaults(defineProps<Props>(), {
  tables: () => [],
  newTabCustomerName: '',
  newTabTableId: null,
});

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'update:splitBillItems', items: SplitItem[]): void;
  (e: 'update:splitBillNewTabCustomerName', name: string): void;
  (e: 'update:splitBillNewTabTableId', id: number | null): void;
  (e: 'process', payload: {
    items: { id: number; split_quantity: number }[];
    new_tab_customer_name: string;
    new_tab_table_id: number | null;
  }): void;
}>();

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

const availableTables = computed<Table[]>(() => {
  return props.tables.filter((t) => t.status === 'available');
});

const handleQuantityChange = (index: number, delta: number) => {
  const item = props.items[index];
  const currentSplit = item.split_quantity || 0;
  const newQty = Math.max(0, Math.min(item.quantity, currentSplit + delta));
  const updated = [...props.items];
  updated[index] = {
    ...updated[index],
    split_quantity: newQty,
    selected: newQty > 0,
  };
  emit('update:splitBillItems', updated);
};

const handleProcess = () => {
  const selectedItems = props.items.filter((item) => item.selected && (item.split_quantity || 0) > 0);
  if (selectedItems.length === 0) {
    return;
  }

  emit('process', {
    items: selectedItems.map((item) => ({
      id: item.id,
      split_quantity: item.split_quantity || 0,
    })),
    new_tab_customer_name: props.newTabCustomerName,
    new_tab_table_id: props.newTabTableId ? Number(props.newTabTableId) : null,
  });
};
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs select-none">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col border border-[#E8E1D9] overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-[#E8E1D9] bg-[#FAF7F2] flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-[#8B6B3D]/10 text-[#8B6B3D] flex items-center justify-center">
            <Split class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-base font-bold text-[#2F2212]">Pecah Bill (Split Bill)</h3>
            <p class="text-xs text-stone-500">Pindahkan sebagian item ke Tab / Meja baru</p>
          </div>
        </div>
        <button
          type="button"
          @click="emit('close')"
          class="w-8 h-8 rounded-lg flex items-center justify-center text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition-colors"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-4 overflow-y-auto flex-1">
        <!-- New Tab Info -->
        <div class="grid grid-cols-2 gap-3 p-3.5 rounded-xl bg-[#FAF7F2] border border-[#E8E1D9]">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-1">
              Nama Pelanggan Baru
            </label>
            <input
              :value="newTabCustomerName"
              @input="emit('update:splitBillNewTabCustomerName', ($event.target as HTMLInputElement).value)"
              type="text"
              class="w-full px-3 py-2 border border-[#E8E1D9] rounded-lg text-xs bg-white text-stone-800 focus:ring-2 focus:ring-[#8B6B3D]"
              placeholder="Contoh: Rombongan Meja 2"
            />
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-1">
              Pilih Meja Baru (Opsional)
            </label>
            <select
              :value="newTabTableId ?? ''"
              @change="emit('update:splitBillNewTabTableId', ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null)"
              class="w-full px-3 py-2 border border-[#E8E1D9] rounded-lg text-xs bg-white text-stone-800 focus:ring-2 focus:ring-[#8B6B3D]"
            >
              <option value="">Tetap di Meja Sama / Tanpa Meja</option>
              <option v-for="t in availableTables" :key="t.id" :value="t.id">
                {{ t.label }} (Kapasitas {{ t.capacity }})
              </option>
            </select>
          </div>
        </div>

        <!-- Items selection -->
        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-stone-600">Pilih Item untuk Dipisah</label>
          <div class="space-y-2">
            <div
              v-for="(item, index) in items"
              :key="item.id"
              class="flex items-center justify-between p-3 border border-[#E8E1D9] rounded-xl bg-white hover:border-stone-300 transition-colors"
            >
              <div class="flex-1 min-w-0 pr-3">
                <p class="text-xs font-bold text-[#2F2212] truncate">{{ item.product_name }}</p>
                <p class="text-[11px] text-stone-500 font-mono">
                  {{ formatCurrency(item.unit_price) }} | Total: {{ item.quantity }} pcs
                </p>
              </div>

              <!-- Split Quantity Stepper -->
              <div class="flex items-center gap-1.5 bg-[#FAF7F2] border border-[#E8E1D9] rounded-lg p-1">
                <button
                  type="button"
                  @click="handleQuantityChange(index, -1)"
                  class="w-7 h-7 flex items-center justify-center rounded text-stone-600 hover:bg-white active:scale-95"
                >
                  <Minus class="w-3.5 h-3.5" />
                </button>
                <span class="w-7 text-center font-mono text-xs font-bold text-[#2F2212]">
                  {{ item.split_quantity || 0 }}
                </span>
                <button
                  type="button"
                  @click="handleQuantityChange(index, 1)"
                  class="w-7 h-7 flex items-center justify-center rounded text-stone-600 hover:bg-white active:scale-95"
                >
                  <Plus class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="p-4 bg-[#FAF7F2] border-t border-[#E8E1D9] flex gap-2">
        <button
          type="button"
          @click="emit('close')"
          class="flex-1 h-12 border border-[#E8E1D9] bg-white hover:bg-stone-50 text-stone-700 rounded-xl font-bold text-xs transition-colors"
        >
          Batal
        </button>
        <button
          type="button"
          @click="handleProcess"
          class="flex-2 h-12 bg-[#8B6B3D] hover:bg-[#76582E] active:scale-[0.99] text-white rounded-xl font-bold text-xs shadow transition-all tracking-wide"
        >
          Proses Pecah Bill
        </button>
      </div>
    </div>
  </div>
</template>
