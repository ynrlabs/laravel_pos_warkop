<script setup>
import { computed, watch } from 'vue';

const props = defineProps({
  subtotal: { type: Number, required: true },
  taxAmount: { type: Number, required: true },
  serviceCharge: { type: Number, required: true },
  total: { type: Number, required: true },
  roundedTotal: { type: Number, required: true },
  change: { type: Number, required: true },
  paymentMethod: { type: String, required: true },
  cashReceived: { type: Number, required: true },
  splitPayments: { type: Array, required: true },
});

const emit = defineEmits([
  'close',
  'update:paymentMethod',
  'update:cashReceived',
  'update:splitPayments',
  'process-payment',
  'check-qris-status',
  'cancel-qris',
  'add-split',
  'remove-split',
]);

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

const paymentMethods = [
  { value: 'cash', label: 'Tunai', icon: 'payments' },
  { value: 'qris', label: 'QRIS', icon: 'qr_code' },
  { value: 'ewallet', label: 'E-Wallet', icon: 'smartphone' },
  { value: 'card', label: 'Kartu', icon: 'credit_card' },
  { value: 'split', label: 'Split', icon: 'account_tree' },
];

const splitMethods = ['cash', 'qris', 'ewallet', 'card'];

const cashQuickAmounts = computed(() => [
  props.roundedTotal,
  Math.ceil(props.roundedTotal / 10000) * 10000,
  Math.ceil(props.roundedTotal / 50000) * 50000,
  Math.ceil(props.roundedTotal / 100000) * 100000,
]);

const totalSplitAmount = computed(() => {
  return props.splitPayments.reduce((sum, p) => sum + (p.amount || 0), 0);
});

const isSplitValid = computed(() => {
  return Math.abs(totalSplitAmount.value - props.roundedTotal) < 1;
});

const handleClose = () => {
  emit('close');
};

const selectPaymentMethod = (method) => {
  emit('update:paymentMethod', method);
};

const handleCashReceived = (value) => {
  emit('update:cashReceived', value);
};

const setQuickCash = (amount) => {
  emit('update:cashReceived', amount);
};

const addSplitPayment = () => {
  emit('add-split');
};

const removeSplitPayment = (index) => {
  emit('remove-split', index);
};

const processPayment = () => {
  if (props.paymentMethod === 'cash' && props.cashReceived < props.roundedTotal) {
    return;
  }
  
  if (props.paymentMethod === 'split' && !isSplitValid.value) {
    return;
  }

  const paymentData = {
    method: props.paymentMethod,
  };

  if (props.paymentMethod === 'cash') {
    paymentData.cash_received = props.cashReceived;
    paymentData.change = props.change;
  } else if (props.paymentMethod === 'split') {
    paymentData.split_payments = props.splitPayments;
  }

  emit('process-payment', paymentData);
};

watch(() => props.paymentMethod, (newMethod) => {
  if (newMethod === 'cash') {
    setTimeout(() => {
      document.getElementById('cash-input')?.focus();
    }, 100);
  }
});
</script>

<template>
  <Transition name="modal-fade">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10 dark:bg-zinc-900 dark:border-zinc-800">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pembayaran</h3>
          <button @click="handleClose" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        
        <div class="p-4 space-y-4">
          <!-- Total Display -->
          <div class="bg-coffee-50 border border-coffee-200 rounded-lg p-4 dark:bg-zinc-800 dark:border-zinc-700">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600 dark:text-gray-400">Total Tagihan</span>
              <span class="font-bold text-coffee-600 text-lg dark:text-coffee-400">{{ formatCurrency(roundedTotal) }}</span>
            </div>
          </div>
          
          <!-- Payment Methods -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Metode Pembayaran</label>
            <div class="grid grid-cols-2 gap-2">
              <button
                v-for="method in paymentMethods"
                :key="method.value"
                @click="selectPaymentMethod(method.value)"
                :class="[
                  'px-3 py-2 rounded-lg border-2 text-sm font-medium transition-all flex flex-col items-center gap-1',
                  paymentMethod === method.value
                    ? 'border-coffee-600 bg-coffee-50 text-coffee-700 dark:border-coffee-400 dark:bg-coffee-900/30 dark:text-coffee-300'
                    : 'border-gray-200 text-gray-600 hover:border-gray-300 dark:border-zinc-700 dark:text-gray-400 dark:hover:border-zinc-600'
                ]"
              >
                <span class="material-symbols-outlined text-lg">{{ method.icon }}</span>
                {{ method.label }}
              </button>
            </div>
          </div>
          
          <!-- Cash Payment -->
          <div v-if="paymentMethod === 'cash'" class="space-y-3">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Uang Diterima</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-bold">Rp</span>
              <input
                id="cash-input"
                :value="cashReceived"
                @input="handleCashReceived($event.target.value)"
                type="number"
                step="100"
                :min="roundedTotal"
                class="w-full pl-9 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 focus:border-transparent text-lg font-medium dark:bg-zinc-800 dark:border-zinc-700 dark:text-white"
                placeholder="0"
              >
            </div>
            <div class="mt-2 flex justify-between text-sm">
              <span class="text-gray-600 dark:text-gray-400">Kembalian</span>
              <span class="font-bold text-green-600 text-lg dark:text-green-400">{{ formatCurrency(change) }}</span>
            </div>

            <!-- Quick Cash Buttons -->
            <div class="grid grid-cols-4 gap-2">
              <button
                v-for="amount in cashQuickAmounts"
                :key="amount"
                @click="setQuickCash(amount)"
                class="py-2 px-3 border border-gray-200 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors dark:border-zinc-700 dark:text-gray-300 dark:hover:bg-zinc-800"
              >
                {{ formatCurrency(amount) }}
              </button>
            </div>
          </div>
          
          <!-- Split Payment -->
          <div v-if="paymentMethod === 'split'" class="space-y-3">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Split Payment</label>
            <div class="space-y-2">
              <div v-for="(split, index) in splitPayments" :key="index" class="flex gap-2">
                <select
                  v-model="splitPayments[index].method"
                  @change="emit('update:splitPayments', splitPayments)"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-lg dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                  <option value="cash">Tunai</option>
                  <option value="qris">QRIS</option>
                  <option value="ewallet">E-Wallet</option>
                  <option value="card">Kartu</option>
                </select>
                <input
                  v-model.number="splitPayments[index].amount"
                  @input="emit('update:splitPayments', splitPayments)"
                  type="number"
                  step="100"
                  class="w-32 px-3 py-2 border border-gray-300 rounded-lg dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                  placeholder="Jumlah"
                >
                <button
                  @click="removeSplitPayment(index)"
                  class="px-3 py-2 text-red-500 hover:text-red-700"
                >
                  <span class="material-symbols-outlined">delete</span>
                </button>
              </div>
              <button
                @click="addSplitPayment"
                class="text-sm text-coffee-600 hover:text-coffee-700 font-bold flex items-center gap-1"
              >
                <span class="material-symbols-outlined text-sm">add_circle</span> Tambah Metode
              </button>
            </div>
            <p class="text-xs text-gray-500 mt-2">
              Total split: {{ formatCurrency(totalSplitAmount) }} / {{ formatCurrency(roundedTotal) }}
              <span v-if="!isSplitValid" class="text-red-500 font-semibold"> - Sisa: {{ formatCurrency(Math.abs(roundedTotal - totalSplitAmount)) }}</span>
            </p>
          </div>
          
          <!-- Action Buttons -->
          <div class="flex gap-2 pt-4 border-t border-gray-200 dark:border-zinc-700">
            <button
              @click="handleClose"
              class="flex-1 px-4 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors dark:border-zinc-700 dark:text-gray-300 dark:hover:bg-zinc-800"
            >
              Batal
            </button>
            <button
              @click="processPayment"
              :disabled="paymentMethod === 'cash' && cashReceived < roundedTotal || paymentMethod === 'split' && !isSplitValid"
              class="flex-1 px-4 py-3 bg-coffee-600 text-white rounded-lg font-medium hover:bg-coffee-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Proses Bayar
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
.dark .bg-coffee-50 {
  background-color: #2d231e !important;
  border-color: #584411 !important;
}
.dark .text-gray-600 {
  color: #d2c4b6 !important;
}
.dark .text-gray-700 {
  color: #e8c08b !important;
}
.dark .text-gray-400 {
  color: #ab9f96 !important;
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
.dark .hover\:bg-gray-50:hover {
  background-color: #2b2422 !important;
}
.dark .hover\:bg-gray-100:hover {
  background-color: #2b2422 !important;
}
</style>