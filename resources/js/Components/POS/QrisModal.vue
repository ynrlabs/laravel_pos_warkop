<script setup>
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
  qrString: { type: String, default: '' },
  qrImageUrl: { type: String, default: '' },
  paymentNumber: { type: String, default: '' },
  amount: { type: Number, default: 0 },
  expiresAt: { type: [String, Date, null], default: null },
  errorMessage: { type: String, default: '' },
});

const emit = defineEmits(['close', 'check-status', 'cancel']);

let countdownInterval = null;

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

const timeRemaining = computed(() => {
  if (!props.expiresAt) return null;
  const now = new Date();
  const expires = new Date(props.expiresAt);
  const diff = Math.max(0, Math.floor((expires - now) / 1000));
  return diff;
});

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const handleClose = () => {
  if (countdownInterval) clearInterval(countdownInterval);
  emit('close');
};

const handleCheckStatus = () => {
  emit('check-status');
};

const handleCancel = () => {
  emit('cancel');
};

onMounted(() => {
  if (props.expiresAt) {
    countdownInterval = setInterval(() => {}, 1000);
  }
});

onUnmounted(() => {
  if (countdownInterval) clearInterval(countdownInterval);
});
</script>

<template>
  <Transition name="modal-fade">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <!-- Header -->
        <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10 dark:bg-zinc-900 dark:border-zinc-800">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pembayaran QRIS / E-Wallet</h3>
          <button @click="handleCancel" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        
        <div class="p-6 space-y-6 text-center">
          <!-- Invoice Number -->
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Nomor Invoice</p>
            <p class="font-semibold text-gray-800 dark:text-white">{{ paymentNumber }}</p>
          </div>

          <!-- Amount -->
          <div class="bg-coffee-50 border border-coffee-200 rounded-lg p-4 max-w-xs mx-auto dark:bg-zinc-800 dark:border-zinc-700">
            <p class="text-xs text-gray-600 uppercase tracking-wide font-semibold dark:text-gray-400">Total yang Harus Dibayar</p>
            <p class="text-2xl font-bold text-coffee-600 mt-1 dark:text-coffee-400">{{ formatCurrency(amount) }}</p>
          </div>

          <!-- QR Code -->
          <div class="relative w-64 h-64 mx-auto border-2 border-dashed border-gray-200 rounded-xl p-2 bg-white flex items-center justify-center dark:border-zinc-700 dark:bg-zinc-900">
            <img
              v-if="qrImageUrl"
              :src="qrImageUrl"
              alt="QRIS Code"
              class="w-full h-full object-contain"
            />
            <img
              v-else-if="qrString"
              :src="'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(qrString)"
              alt="QRIS Code"
              class="w-full h-full object-contain"
            />
            <div v-else class="text-gray-400 text-sm flex flex-col items-center">
              <span class="material-symbols-outlined animate-spin text-3xl mb-2">sync</span>
              Membuka QR Code...
            </div>
          </div>

          <!-- Info & Timer -->
          <div class="space-y-2">
            <p class="text-xs text-gray-500 dark:text-gray-400">Silakan scan kode QRIS di atas menggunakan GoPay, OVO, DANA, LinkAja, ShopeePay, atau aplikasi Mobile Banking Anda.</p>
            <p v-if="timeRemaining !== null" class="text-xs text-red-500 font-semibold flex items-center justify-center gap-1">
              <span class="material-symbols-outlined text-sm">schedule</span>
              Kedaluwarsa pada: {{ formatTime(timeRemaining) }}
            </p>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="text-xs text-red-500 bg-red-50 p-2 rounded dark:bg-red-900/20">
            {{ errorMessage }}
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col gap-2 pt-4 border-t border-gray-100 dark:border-zinc-700">
            <button
              @click="handleCheckStatus"
              class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
            >
              <span class="material-symbols-outlined text-sm">sync</span> Cek Status Pembayaran
            </button>
            <button
              @click="handleCancel"
              class="w-full px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-600 rounded-lg text-sm font-medium transition-colors dark:border-zinc-700 dark:text-gray-400 dark:hover:bg-zinc-800"
            >
              Batalkan & Kembali
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
.dark .bg-coffee-50 {
  background-color: #2d231e !important;
  border-color: #584411 !important;
}
.dark .text-gray-500 {
  color: #ab9f96 !important;
}
.dark .text-gray-600 {
  color: #d2c4b6 !important;
}
.dark .border-gray-200 {
  border-color: #2b221d !important;
}
.dark .border-gray-100 {
  border-color: #2b221d !important;
}
.dark .hover\:bg-gray-50:hover {
  background-color: #2b2422 !important;
}
</style>