<script setup>
const props = defineProps({
  tabs: { type: Array, required: true },
  sourceId: { type: [Number, String, null], default: null },
  targetId: { type: [Number, String, null], default: null },
});

const emit = defineEmits([
  'close',
  'update:mergeTabSourceId',
  'update:mergeTabTargetId',
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

const handleClose = () => {
  emit('close');
};

const handleProcess = () => {
  if (!props.sourceId || !props.targetId) {
    alert('Pilih tab sumber dan tab tujuan!');
    return;
  }
  
  if (props.sourceId === props.targetId) {
    alert('Tab sumber dan tujuan tidak boleh sama!');
    return;
  }
  
  emit('process', {
    source_id: props.sourceId,
    target_id: props.targetId,
  });
};
</script>

<template>
  <Transition name="modal-fade">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <!-- Header -->
        <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10 dark:bg-zinc-900 dark:border-zinc-800">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">Gabung Tab (Merge Tab)</h3>
          <button @click="handleClose" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        
        <div class="p-4 space-y-4">
          <div class="bg-gray-50 p-3 rounded-lg dark:bg-zinc-800">
            <p class="text-sm text-gray-600 dark:text-gray-400">Pilih tab sumber (akan dikosongkan) dan tab tujuan (akan menerima item). Tab sumber akan ditutup setelah digabung.</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tab Sumber (Item akan dipindahkan dari sini)</label>
            <select
              :value="sourceId"
              @change="emit('update:mergeTabSourceId', $event.target.value)"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 bg-white dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            >
              <option value="">Pilih Tab Sumber</option>
              <option 
                v-for="tab in tabs" 
                :key="tab.id" 
                :value="tab.id"
              >
                {{ tab.customer_name || 'Meja ' + tab.table?.number }} - {{ formatCurrency(tab.items.reduce((sum, item) => sum + item.subtotal, 0)) }} ({{ tab.items.length }} item)
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tab Tujuan (Item akan ditambahkan ke sini)</label>
            <select
              :value="targetId"
              @change="emit('update:mergeTabTargetId', $event.target.value)"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 bg-white dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            >
              <option value="">Pilih Tab Tujuan</option>
              <option 
                v-for="tab in tabs" 
                :key="tab.id" 
                :value="tab.id"
              >
                {{ tab.customer_name || 'Meja ' + tab.table?.number }} - {{ formatCurrency(tab.items.reduce((sum, item) => sum + item.subtotal, 0)) }} ({{ tab.items.length }} item)
              </option>
            </select>
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
              <span class="material-symbols-outlined text-sm">join_inner</span> Gabung Tab
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
.dark .bg-white select {
  background-color: #120e0c !important;
  border-color: #2b221d !important;
  color: #f3ede8 !important;
}
.dark .bg-gray-50 {
  background-color: #2b2422 !important;
}
.dark .text-gray-600 {
  color: #d2c4b6 !important;
}
.dark .border-gray-200 {
  border-color: #2b221d !important;
}
.dark .border-gray-300 {
  border-color: #3d322f !important;
}
</style>