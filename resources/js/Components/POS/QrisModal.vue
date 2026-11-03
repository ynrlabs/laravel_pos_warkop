<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { X, RefreshCw, AlertCircle } from 'lucide-vue-next';

interface Props {
  qrString?: string;
  qrImageUrl?: string;
  paymentNumber?: string;
  amount?: number;
  expiresAt?: string | Date | null;
  errorMessage?: string;
}

const props = withDefaults(defineProps<Props>(), {
  qrString: '',
  qrImageUrl: '',
  paymentNumber: '',
  amount: 0,
  expiresAt: null,
  errorMessage: '',
});

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'check-status'): void;
  (e: 'cancel'): void;
}>();

const now = ref(new Date());
let timer: ReturnType<typeof setInterval> | null = null;

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

const timeRemaining = computed<number | null>(() => {
  if (!props.expiresAt) return null;
  const expires = new Date(props.expiresAt);
  const diff = Math.max(0, Math.floor((expires.getTime() - now.value.getTime()) / 1000));
  return diff;
});

const formatTime = (seconds: number): string => {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${secs.toString().padStart(2, '0')}`;
};

onMounted(() => {
  timer = setInterval(() => {
    now.value = new Date();
  }, 1000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs select-none">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full border border-[#E8E1D9] overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-[#E8E1D9] bg-[#FAF7F2] flex items-center justify-between">
        <div>
          <h3 class="text-base font-bold text-[#2F2212]">Pembayaran QRIS</h3>
          <p class="text-xs text-stone-500 font-mono">Invoice: {{ paymentNumber }}</p>
        </div>
        <button
          type="button"
          @click="emit('cancel')"
          class="w-8 h-8 rounded-lg flex items-center justify-center text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition-colors"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 flex flex-col items-center text-center space-y-4">
        <!-- Amount -->
        <div class="p-3 bg-[#FAF7F2] rounded-xl border border-[#E8E1D9] w-full">
          <p class="text-[11px] font-semibold text-stone-500 uppercase tracking-wider">Total yang Harus Dibayar</p>
          <p class="font-mono text-2xl font-black text-[#8B6B3D] mt-0.5">{{ formatCurrency(amount) }}</p>
        </div>

        <!-- QR Image -->
        <div class="p-3 bg-white rounded-2xl border-2 border-dashed border-[#E8E1D9] shadow-inner">
          <img
            v-if="qrImageUrl"
            :src="qrImageUrl"
            alt="QRIS Code"
            class="w-56 h-56 object-contain"
          />
          <img
            v-else-if="qrString"
            :src="'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(qrString)"
            alt="QRIS Code"
            class="w-56 h-56 object-contain"
          />
          <div v-else class="w-56 h-56 flex items-center justify-center text-stone-400 text-xs">
            Menunggu QR Code...
          </div>
        </div>

        <!-- Timer -->
        <div v-if="timeRemaining !== null" class="text-xs text-stone-500">
          Sisa waktu: <span class="font-mono font-bold text-amber-700">{{ formatTime(timeRemaining) }}</span>
        </div>

        <!-- Error message -->
        <div v-if="errorMessage" class="p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
          <AlertCircle class="w-4 h-4 shrink-0" />
          <span>{{ errorMessage }}</span>
        </div>
      </div>

      <!-- Footer Buttons -->
      <div class="p-4 bg-[#FAF7F2] border-t border-[#E8E1D9] flex gap-2">
        <button
          type="button"
          @click="emit('cancel')"
          class="flex-1 h-11 border border-[#E8E1D9] bg-white hover:bg-stone-50 text-stone-700 rounded-xl font-bold text-xs transition-colors"
        >
          Batalkan QRIS
        </button>
        <button
          type="button"
          @click="emit('check-status')"
          class="flex-2 h-11 bg-[#8B6B3D] hover:bg-[#76582E] active:scale-[0.99] text-white rounded-xl font-bold text-xs shadow transition-all flex items-center justify-center gap-1.5"
        >
          <RefreshCw class="w-4 h-4" />
          Cek Status Pembayaran
        </button>
      </div>
    </div>
  </div>
</template>
