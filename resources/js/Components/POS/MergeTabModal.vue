<script setup lang="ts">
import type { Tab } from '@/Types/pos';
import { X, GitMerge } from 'lucide-vue-next';

interface Props {
  tabs: Tab[];
  sourceId?: number | string | null;
  targetId?: number | string | null;
}

const props = withDefaults(defineProps<Props>(), {
  sourceId: null,
  targetId: null,
});

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'update:mergeTabSourceId', id: number | null): void;
  (e: 'update:mergeTabTargetId', id: number | null): void;
  (e: 'process', payload: { source_id: number; target_id: number }): void;
}>();

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

const handleProcess = () => {
  if (!props.sourceId || !props.targetId) {
    return;
  }
  if (props.sourceId === props.targetId) {
    return;
  }
  emit('process', {
    source_id: Number(props.sourceId),
    target_id: Number(props.targetId),
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
            <GitMerge class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-base font-bold text-[#2F2212]">Gabung Tab (Merge)</h3>
            <p class="text-xs text-stone-500">Gabungkan dua tab aktif menjadi satu</p>
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
          <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-1.5">
            Tab Sumber (Akan ditutup)
          </label>
          <select
            :value="sourceId ?? ''"
            @change="emit('update:mergeTabSourceId', ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null)"
            class="w-full px-3 py-2.5 border border-[#E8E1D9] rounded-xl text-xs bg-white text-stone-800 focus:ring-2 focus:ring-[#8B6B3D]"
          >
            <option value="">Pilih Tab Sumber</option>
            <option v-for="t in tabs" :key="t.id" :value="t.id">
              {{ t.customer_name || 'Meja ' + (t.table?.number || '') }} - {{ formatCurrency(t.items.reduce((s, i) => s + i.subtotal, 0)) }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-1.5">
            Tab Tujuan (Akan menerima item)
          </label>
          <select
            :value="targetId ?? ''"
            @change="emit('update:mergeTabTargetId', ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null)"
            class="w-full px-3 py-2.5 border border-[#E8E1D9] rounded-xl text-xs bg-white text-stone-800 focus:ring-2 focus:ring-[#8B6B3D]"
          >
            <option value="">Pilih Tab Tujuan</option>
            <option v-for="t in tabs" :key="t.id" :value="t.id">
              {{ t.customer_name || 'Meja ' + (t.table?.number || '') }} - {{ formatCurrency(t.items.reduce((s, i) => s + i.subtotal, 0)) }}
            </option>
          </select>
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
          :disabled="!sourceId || !targetId || sourceId === targetId"
          class="flex-2 h-11 bg-[#8B6B3D] hover:bg-[#76582E] active:scale-[0.99] text-white rounded-xl font-bold text-xs shadow transition-all disabled:opacity-40 disabled:pointer-events-none tracking-wide"
        >
          Gabungkan Tab
        </button>
      </div>
    </div>
  </div>
</template>
