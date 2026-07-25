<script setup>
import { computed } from 'vue';

const props = defineProps({
  items: { type: Array, required: true },
  tables: { type: Array, default: () => [] },
  newTabCustomerName: { type: String, default: '' },
  newTabTableId: { type: [Number, String, null], default: null },
});

const emit = defineEmits([
  'close',
  'update:splitBillItems',
  'update:splitBillNewTabCustomerName',
  'update:splitBillNewTabTableId',
  'process',
]);

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

const availableTables = computed(() => {
  return props.tables.filter(t => t.status === 'available');
});

const handleClose = () => {
  emit('close');
};

const handleQuantityChange = (index, delta) => {
  const item = props.items[index];
  const newQty = Math.max(0, Math.min(item.quantity, (item.split_quantity || 0) + delta));
  const updated = [...props.items];
  updated[index] = { ...updated[index], split_quantity: newQty };
  emit('update:splitBillItems', updated);
};

const handleSelectionChange = (index) => {
  const updated = [...props.items];
  updated[index] = { ...updated[index], selected: !updated[index].selected };
  if (!updated[index].selected) {
    updated[index].split_quantity = 0;
  }
  emit('update:splitBillItems', updated);
};

const handleProcess = () => {
  const selectedItems = props.items.filter(item => item.selected && item.split_quantity > 0);
  
  if (selectedItems.length === 0) {
    alert('Pilih minimal 1 item untuk dipecah!');
    return;
  }

  // Validate quantities
  for (const item of selectedItems) {
    if (item.split_quantity > item.quantity) {
      alert(`Jumlah pecah untuk ${item.product_name} melebihi jumlah tersedia!`);
      return;
    }
  }

  emit('process', {
    items: selectedItems.map(item => ({
      id: item.id,
      split_quantity: item.split_quantity,
    })),
    new_tab_customer_name: props.newTabCustomerName,
    new_tab_table_id: props.newTabTableId,
  });
};
</script>

<template>
  <Transition name="modal-fade">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10 dark:bg-zinc-900 dark:border-zinc-800">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pecah Bill (Split Bill)</h3>
          <button @click="handleClose" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        
        <div class="p-4 space-y-4">
          <div class="bg-gray-50 p-3 rounded-lg dark:bg-zinc-800">
            <p class="text-sm text-gray-600 dark:text-gray-400">Pilih item yang ingin dipindah ke tab baru. Isi jumlah pecah untuk setiap item.</p>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Pelanggan Tab Baru</label>
              <input
                :value="newTabCustomerName"
                @input="emit('update:splitBillNewTabCustomerName', $event.target.value)"
                type="text"
                placeholder="Kosongkan untuk menggunakan nama asli + (Split)"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meja Tab Baru (Opsional)</label>
              <select
                :value="newTabTableId"
                @change="emit('update:splitBillNewTabTableId', $event.target.value)"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 bg-white dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
              >
                <option value="">Tanpa Meja</option>
                <option v-for="table in availableTables" :key="table.id" :value="table.id">
                  {{ table.label }} ({{ table.capacity }} orang)
                </option>
              </select>
            </div>
          </div>
          
          <div class="space-y-2 max-h-96 overflow-y-auto">
            <div
              v-for="(item, index) in items"
              :key="item.id"
              class="border border-gray-200 rounded-lg p-3 dark:border-zinc-700"
            >
              <div class="flex items-start gap-3">
                <input
                  type="checkbox"
                  v-model="item.selected"
                  @change="handleSelectionChange(index)"
                  class="w-4 h-4 mt-0.5 text-coffee-600 border-gray-300 rounded focus:ring-coffee-500 dark:border-zinc-700"
                >
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <h4 class="font-medium text-sm text-gray-900 truncate pr-2">
                      {{ item.product_name }}
                      <span v-if="item.variant_name" class="text-gray-500 ml-1">({{ item.variant_name }})</span>
                    </h4>
                    <span class="font-bold text-coffee-600 text-sm ml-2">
                      {{ formatCurrency(item.subtotal) }}
                    </span>
                  </div>
                  
                  <div v-if="item.modifiers?.length > 0" class="mt-1 flex flex-wrap gap-1">
                    <span
                      v-for="mod in item.modifiers"
                      :key="mod.id"
                      class="text-xs bg-coffee-100 text-coffee-800 px-1.5 py-0.5 rounded dark:bg-coffee-900/30 dark:text-coffee-300"
                    >
                      {{ mod.name }}
                    </span>
                  </div>
                  
                  <div v-if="item.toppings?.length > 0" class="mt-1 flex flex-wrap gap-1">
                    <span
                      v-for="top in item.toppings"
                      :key="top.id"
                      class="text-xs bg-green-100 text-green-800 px-1.5 py-0.5 rounded dark:bg-green-900/30 dark:text-green-300"
                    >
                      {{ top.name }} x{{ top.quantity }}
                    </span>
                  </div>
                  
                  <div class="mt-2 flex items-center gap-4">
                    <span class="text-xs text-gray-500 font-semibold">Tersedia: {{ item.quantity }}</span>
                    <div class="flex items-center gap-1">
                      <button
                        @click="handleQuantityChange(index, -1)"
                        :disabled="item.split_quantity <= 0"
                        class="w-6 h-6 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-100 text-sm disabled:opacity-50 dark:border-zinc-700 dark:hover:bg-zinc-800"
                      >
                        <span class="material-symbols-outlined text-xs">remove</span>
                      </button>
              <input
                type="number"
                :value="item.split_quantity"
                @input="handleQuantityChange(index, $event.target.value - item.split_quantity)"
                :min="0"
                :max="item.quantity"
                class="w-16 text-center text-sm border border-gray-300 rounded px-2 py-1 bg-white text-black dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
              >
                      <button
                        @click="handleQuantityChange(index, 1)"
                        :disabled="item.split_quantity >= item.quantity"
                        class="w-6 h-6 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-100 text-sm disabled:opacity-50 dark:border-zinc-700 dark:hover:bg-zinc-800"
                      >
                        <span class="material-symbols-outlined text-xs">add</span>
                      </button>
                      <span class="text-xs text-gray-500 font-semibold">Pecah</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex gap-2 pt-4 border-t border-gray-200 dark:border-zinc-700">
            <button
              @click="handleClose"
              class="flex-1 px-4 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors dark:border-zinc-700 dark:text-gray-300 dark:hover:bg-zinc-800"
            >
              Batal
            </button>
            <button
              @click="handleProcess"
              class="flex-1 px-4 py-3 bg-coffee-600 text-white rounded-lg font-medium hover:bg-coffee-700 transition-colors flex items-center justify-center gap-2"
            >
              <span class="material-symbols-outlined text-sm">content_cut</span> Pecah Bill
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
@keyframes modal-fade-in {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

@keyframes modal-fade-out {
  from { opacity: 1; transform: scale(1); }
  to { opacity: 0; transform: scale(0.95); }
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: all 0.2s ease;
}

.modal-fade-enter-from {
  opacity: 0;
  transform: scale(0.95);
}

.modal-fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

/* Dark Mode */
.dark .bg-white {
  background-color: #1a1512 !important;
  color: #f3ede8 !important;
  border: 1px solid #2b221d !important;
}
.dark .bg-white h3 {
  color: #e8c08b !important;
}
.dark .bg-white label {
  color: #d2c4b6 !important;
}
.dark .bg-white input,
.dark .bg-white select,
.dark .bg-white textarea {
  background-color: #120e0c !important;
  border-color: #2b221d !important;
  color: #f3ede8 !important;
}
.dark .bg-white button.border-gray-200 {
  border-color: #2b221d !important;
  color: #d2c4b6 !important;
}
.dark .bg-white button.border-coffee-600 {
  border-color: #e8c08b !important;
  background-color: #2d231e !important;
  color: #e8c08b !important;
}
.dark .hover\:bg-gray-50:hover {
  background-color: #2b2422 !important;
}
.dark .hover\:bg-gray-100:hover {
  background-color: #2b2422 !important;
}
.dark .bg-gray-50 {
  background-color: #2b2422 !important;
}
.dark .text-gray-600 {
  color: #d2c4b6 !important;
}
.dark .text-gray-500 {
  color: #ab9f96 !important;
}
.dark .border-gray-200 {
  border-color: #2b221d !important;
}
.dark .border-gray-300 {
  border-color: #3d322f !important;
}
.dark .border-gray-100 {
  border-color: #2b221d !important;
}
</style>