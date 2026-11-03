<script setup lang="ts">
import { computed } from 'vue';
import type { Table } from '@/Types/pos';
import { X, ArrowRightLeft } from 'lucide-vue-next';

interface Props {
  tables?: Table[];
  newTableId?: number | string | null;
  newCustomerName?: string;
}

const props = withDefaults(defineProps<Props>(), {
  tables: () => [],
  newTableId: null,
  newCustomerName: '',
});

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'update:transferTabNewTableId', id: number | null): void;
  (e: 'update:transferTabNewCustomerName', name: string): void;
  (e: 'process', payload: { table_id: number | null; customer_name: string }): void;
}>();

const availableTables = computed<Table[]>(() => {
  return props.tables.filter((t) => t.status === 'available');
});

const handleProcess = () => {
  emit('process', {
    table_id: props.newTableId ? Number(props.newTableId) : null,
    customer_name: props.newCustomerName,
  });
};
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs select-none">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full border border-[#E8E1D9] overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-[#E8E1D9] bg-[#FAF7F2] flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-[#8B6B3D]/10 text-[#8B6B3D] flex items-center justify-center">
            <ArrowRightLeft class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-base font-bold text-[#2F2212]">Pindah Meja (Transfer)</h3>
            <p class="text-xs text-stone-500">Pindahkan tab aktif ke meja lain</p>
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
      <div class="p-6 space-y-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-1.5">Meja Baru</label>
          <select
            :value="newTableId ?? ''"
            @change="emit('update:transferTabNewTableId', ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null)"
            class="w-full px-3 py-2.5 border border-[#E8E1D9] rounded-xl text-xs bg-white text-stone-800 focus:ring-2 focus:ring-[#8B6B3D]"
          >
            <option value="">Tanpa Meja (Antrian)</option>
            <option v-for="table in availableTables" :key="table.id" :value="table.id">
              {{ table.label }} (Kapasitas {{ table.capacity }} orang)
            </option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-1.5">Nama Pelanggan</label>
          <input
            :value="newCustomerName"
            @input="emit('update:transferTabNewCustomerName', ($event.target as HTMLInputElement).value)"
            type="text"
            class="w-full px-3 py-2.5 border border-[#E8E1D9] rounded-xl text-xs bg-white text-stone-800 focus:ring-2 focus:ring-[#8B6B3D]"
            placeholder="Nama pelanggan"
          />
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="p-4 bg-[#FAF7F2] border-t border-[#E8E1D9] flex gap-2">
        <button
          type="button"
          @click="emit('close')"
          class="flex-1 h-11 border border-[#E8E1D9] bg-white hover:bg-stone-50 text-stone-700 rounded-xl font-bold text-xs transition-colors"
        >
          Batal
        </button>
        <button
          type="button"
          @click="handleProcess"
          class="flex-2 h-11 bg-[#8B6B3D] hover:bg-[#76582E] active:scale-[0.99] text-white rounded-xl font-bold text-xs shadow transition-all tracking-wide"
        >
          Konfirmasi Pindah
        </button>
      </div>
    </div>
  </div>
</template>
