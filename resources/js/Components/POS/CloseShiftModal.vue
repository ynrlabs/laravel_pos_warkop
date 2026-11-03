<script setup lang="ts">
import { ref, computed } from 'vue';
import { X, AlertCircle } from 'lucide-vue-next';

const props = defineProps<{
  shift: {
    id: number;
    opening_cash: number;
    total_cash_sales: number;
    total_non_cash_sales: number;
    transaction_count: number;
  };
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'submit', payload: { actual_cash: number; variance_notes?: string }): void;
}>();

const expectedCash = computed(() => {
  return Number(props.shift.opening_cash || 0) + Number(props.shift.total_cash_sales || 0);
});

const actualCash = ref<number>(expectedCash.value);
const varianceNotes = ref<string>('');

const variance = computed(() => {
  return actualCash.value - expectedCash.value;
});

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

const handleSubmit = () => {
  if (actualCash.value < 0) return;
  emit('submit', {
    actual_cash: actualCash.value,
    variance_notes: varianceNotes.value,
  });
};
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs select-none">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full border border-[#E8E1D9] overflow-hidden flex flex-col max-h-[90vh]">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-[#E8E1D9] bg-[#FAF7F2] flex items-center justify-between">
        <div>
          <h2 class="text-base font-bold text-[#2F2212]">Tutup Shift Kasir #{{ shift.id }}</h2>
          <p class="text-xs text-stone-500">Rekonsiliasi uang fisik kasir sebelum menutup shift</p>
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
      <div class="p-6 space-y-4 overflow-y-auto">
        <!-- Summary Box -->
        <div class="rounded-xl bg-[#FAF7F2] border border-[#E8E1D9] p-3.5 space-y-2 text-xs">
          <div class="flex justify-between items-center text-stone-600">
            <span>Modal Awal Kasir:</span>
            <span class="font-bold text-stone-900">{{ formatCurrency(shift.opening_cash) }}</span>
          </div>
          <div class="flex justify-between items-center text-stone-600">
            <span>Penjualan Tunai:</span>
            <span class="font-bold text-emerald-700">+ {{ formatCurrency(shift.total_cash_sales) }}</span>
          </div>
          <div class="flex justify-between items-center text-stone-600">
            <span>Penjualan Non-Tunai (QRIS/Card):</span>
            <span class="font-bold text-blue-700">{{ formatCurrency(shift.total_non_cash_sales) }}</span>
          </div>
          <div class="flex justify-between items-center text-stone-600">
            <span>Total Transaksi Selesai:</span>
            <span class="font-bold text-stone-900">{{ shift.transaction_count }} transaksi</span>
          </div>
          <div class="border-t border-[#E8E1D9] pt-2 flex justify-between items-center text-sm font-bold text-[#2F2212]">
            <span>Estimasi Kas di Laci:</span>
            <span class="text-base text-[#8B6B3D]">{{ formatCurrency(expectedCash) }}</span>
          </div>
        </div>

        <!-- Actual Cash Input -->
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1.5">
            Uang Fisik Kasir Aktual (Hitung di Laci)
          </label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 font-bold text-sm">Rp</span>
            <input
              type="number"
              v-model.number="actualCash"
              min="0"
              step="1000"
              class="w-full h-12 pl-10 pr-4 text-base font-bold rounded-xl border border-stone-300 bg-white focus:outline-hidden focus:border-[#8B6B3D] text-[#2F2212]"
            />
          </div>
        </div>

        <!-- Variance Indicator -->
        <div
          :class="[
            'p-3 rounded-xl border text-xs font-bold flex items-center justify-between',
            variance === 0
              ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
              : variance > 0
              ? 'bg-blue-50 border-blue-200 text-blue-800'
              : 'bg-rose-50 border-rose-200 text-rose-800'
          ]"
        >
          <div class="flex items-center gap-1.5">
            <AlertCircle class="w-4 h-4 shrink-0" />
            <span>Selisih Kas:</span>
          </div>
          <span class="text-sm">
            {{ variance > 0 ? '+ ' : '' }}{{ formatCurrency(variance) }}
            <span class="text-[10px] font-normal ml-1">
              ({{ variance === 0 ? 'Pas / Sesuai' : variance > 0 ? 'Lebih' : 'Kurang' }})
            </span>
          </span>
        </div>

        <!-- Variance Notes (Required if variance != 0) -->
        <div v-if="variance !== 0">
          <label class="block text-xs font-bold text-rose-800 mb-1.5">
            Alasan Selisih Kas (Wajib Diisi)
          </label>
          <textarea
            v-model="varianceNotes"
            rows="2"
            class="w-full px-3 py-2 text-xs rounded-xl border border-rose-300 bg-rose-50/30 focus:outline-hidden focus:border-rose-600 text-stone-800 resize-none"
            placeholder="Tuliskan keterangan penyebab selisih kas..."
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
          :disabled="variance !== 0 && !varianceNotes.trim()"
          class="flex-2 h-11 bg-stone-900 hover:bg-black text-white font-bold text-xs rounded-xl shadow transition disabled:opacity-40"
        >
          Konfirmasi Tutup Shift
        </button>
      </div>
    </div>
  </div>
</template>
