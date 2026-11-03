<script setup lang="ts">
import { ref } from 'vue';
import { DollarSign, X } from 'lucide-vue-next';

const props = defineProps<{
  shiftDefinitions?: Array<{ id: number; name: string }>;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'submit', payload: { opening_cash: number; shift_definition_id?: number; notes?: string }): void;
}>();

const openingCash = ref<number>(200000);
const selectedDefId = ref<number | undefined>(props.shiftDefinitions?.[0]?.id);
const notes = ref<string>('');

const presets = [100000, 200000, 300000, 500000];

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

const handleSubmit = () => {
  if (openingCash.value < 0) return;
  emit('submit', {
    opening_cash: openingCash.value,
    shift_definition_id: selectedDefId.value,
    notes: notes.value,
  });
};
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs select-none">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full border border-[#E8E1D9] overflow-hidden flex flex-col">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-[#E8E1D9] bg-[#FAF7F2] flex items-center justify-between">
        <div>
          <h2 class="text-base font-bold text-[#2F2212]">Buka Shift Kasir</h2>
          <p class="text-xs text-stone-500">Masukkan modal kas awal untuk memulai transaksi</p>
        </div>
        <button
          type="button"
          @click="emit('close')"
          class="w-8 h-8 rounded-lg flex items-center justify-center text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Body -->
      <div class="p-6 space-y-4">
        <!-- Shift Definition -->
        <div v-if="shiftDefinitions && shiftDefinitions.length > 1">
          <label class="block text-xs font-bold text-stone-700 mb-1.5">Pilih Shift</label>
          <select
            v-model="selectedDefId"
            class="w-full h-11 px-3 text-sm rounded-xl border border-stone-300 bg-white font-medium focus:outline-hidden focus:border-[#8B6B3D]"
          >
            <option v-for="def in shiftDefinitions" :key="def.id" :value="def.id">
              {{ def.name }}
            </option>
          </select>
        </div>

        <!-- Opening Cash Input -->
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1.5">Modal Kas Awal (Rp)</label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 font-bold text-sm">Rp</span>
            <input
              type="number"
              v-model.number="openingCash"
              min="0"
              step="1000"
              class="w-full h-12 pl-10 pr-4 text-base font-bold rounded-xl border border-stone-300 bg-white focus:outline-hidden focus:border-[#8B6B3D] text-[#2F2212]"
              placeholder="0"
            />
          </div>
        </div>

        <!-- Presets -->
        <div>
          <span class="block text-[11px] font-semibold text-stone-500 mb-1.5">Pilihan Cepat Modal:</span>
          <div class="grid grid-cols-2 gap-2">
            <button
              v-for="amt in presets"
              :key="amt"
              type="button"
              @click="openingCash = amt"
              :class="[
                'h-9 px-3 rounded-lg text-xs font-bold border transition text-left flex items-center justify-between',
                openingCash === amt
                  ? 'border-[#8B6B3D] bg-[#8B6B3D]/10 text-[#8B6B3D]'
                  : 'border-stone-200 hover:bg-stone-50 text-stone-700'
              ]"
            >
              <span>{{ formatCurrency(amt) }}</span>
            </button>
          </div>
        </div>

        <!-- Notes -->
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1.5">Catatan Buka Shift (Opsional)</label>
          <textarea
            v-model="notes"
            rows="2"
            class="w-full px-3 py-2 text-xs rounded-xl border border-stone-300 bg-white focus:outline-hidden focus:border-[#8B6B3D] text-stone-800 resize-none"
            placeholder="Contoh: Pecahan 50rb 2 lbr, 20rb 5 lbr..."
          ></textarea>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 border-t border-[#E8E1D9] bg-[#FAF7F2] flex items-center gap-3">
        <button
          type="button"
          @click="emit('close')"
          class="flex-1 h-11 border border-stone-300 hover:bg-stone-100 text-stone-700 font-bold text-xs rounded-xl transition"
        >
          Batal
        </button>
        <button
          type="button"
          @click="handleSubmit"
          class="flex-2 h-11 bg-[#8B6B3D] hover:bg-[#73552C] text-white font-bold text-xs rounded-xl shadow transition"
        >
          Buka Shift Sekarang
        </button>
      </div>
    </div>
  </div>
</template>
