<script setup lang="ts">
import { computed, watch } from 'vue';
import type { SplitPayment, PaymentData } from '@/Types/pos';
import { Banknote, QrCode, Smartphone, CreditCard, Split, X, Trash2, Plus } from 'lucide-vue-next';

interface Props {
  subtotal: number;
  taxAmount: number;
  serviceCharge: number;
  total: number;
  roundedTotal: number;
  change: number;
  paymentMethod: string;
  cashReceived: number;
  splitPayments: SplitPayment[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'update:paymentMethod', method: string): void;
  (e: 'update:cashReceived', amount: number): void;
  (e: 'update:splitPayments', payments: SplitPayment[]): void;
  (e: 'process-payment', data: PaymentData): void;
  (e: 'add-split'): void;
  (e: 'remove-split', index: number): void;
}>();

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

const paymentMethods = [
  { value: 'cash', label: 'Tunai', icon: Banknote },
  { value: 'qris', label: 'QRIS', icon: QrCode },
  { value: 'ewallet', label: 'E-Wallet', icon: Smartphone },
  { value: 'card', label: 'Kartu', icon: CreditCard },
  { value: 'split', label: 'Split Bill', icon: Split },
];

const cashQuickAmounts = computed<number[]>(() => [
  props.roundedTotal,
  Math.ceil(props.roundedTotal / 10000) * 10000,
  Math.ceil(props.roundedTotal / 50000) * 50000,
  Math.ceil(props.roundedTotal / 100000) * 100000,
]);

const totalSplitAmount = computed<number>(() => {
  return props.splitPayments.reduce((sum, p) => sum + (p.amount || 0), 0);
});

const isSplitValid = computed<boolean>(() => {
  return Math.abs(totalSplitAmount.value - props.roundedTotal) < 1;
});

const handleClose = () => {
  emit('close');
};

const selectPaymentMethod = (method: string) => {
  emit('update:paymentMethod', method);
};

const handleCashInput = (e: Event) => {
  const val = Number((e.target as HTMLInputElement).value) || 0;
  emit('update:cashReceived', val);
};

const setQuickCash = (amount: number) => {
  emit('update:cashReceived', amount);
};

const processPayment = () => {
  if (props.paymentMethod === 'cash' && props.cashReceived < props.roundedTotal) {
    return;
  }

  if (props.paymentMethod === 'split' && !isSplitValid.value) {
    return;
  }

  const paymentData: PaymentData = {
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

watch(
  () => props.paymentMethod,
  (newMethod) => {
    if (newMethod === 'cash') {
      setTimeout(() => {
        document.getElementById('cash-input')?.focus();
      }, 100);
    }
  }
);
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs select-none">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] flex flex-col border border-[#E8E1D9] overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-[#E8E1D9] bg-[#FAF7F2] flex items-center justify-between">
        <div>
          <h3 class="text-base font-bold text-[#2F2212]">Pembayaran</h3>
          <p class="text-xs text-stone-500">Pilih metode dan selesaikan transaksi</p>
        </div>
        <button
          type="button"
          @click="handleClose"
          class="w-8 h-8 rounded-lg flex items-center justify-center text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition-colors"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-5 overflow-y-auto flex-1">
        <!-- Total Card -->
        <div class="p-4 rounded-xl bg-[#FAF7F2] border border-[#E8E1D9] flex items-center justify-between">
          <div>
            <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider block">Total Tagihan</span>
            <span class="text-xs text-stone-400">Termasuk pajak & service</span>
          </div>
          <span class="font-mono text-2xl font-black text-[#8B6B3D]">
            {{ formatCurrency(roundedTotal) }}
          </span>
        </div>

        <!-- Payment Methods -->
        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-stone-600">Metode Pembayaran</label>
          <div class="grid grid-cols-3 gap-2">
            <button
              v-for="method in paymentMethods"
              :key="method.value"
              type="button"
              @click="selectPaymentMethod(method.value)"
              :class="[
                'p-3 rounded-xl border text-xs font-bold flex flex-col items-center justify-center gap-1.5 transition-all',
                paymentMethod === method.value
                  ? 'border-[#8B6B3D] bg-[#8B6B3D]/10 text-[#8B6B3D] shadow-xs'
                  : 'border-[#E8E1D9] bg-white text-stone-600 hover:border-stone-300'
              ]"
            >
              <component :is="method.icon" class="w-5 h-5" />
              <span>{{ method.label }}</span>
            </button>
          </div>
        </div>

        <!-- Cash Payment Section -->
        <div v-if="paymentMethod === 'cash'" class="space-y-3 pt-2 border-t border-[#E8E1D9]">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-1">
              Uang Diterima (Tunai)
            </label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 font-mono font-bold text-stone-500 text-sm">Rp</span>
              <input
                id="cash-input"
                :value="cashReceived"
                @input="handleCashInput"
                type="number"
                step="1000"
                class="w-full pl-11 pr-4 py-3 border border-[#E8E1D9] rounded-xl font-mono text-xl font-bold text-[#2F2212] focus:ring-2 focus:ring-[#8B6B3D] focus:border-[#8B6B3D]"
                placeholder="0"
              />
            </div>
          </div>

          <!-- Quick Cash Suggestions -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5">
            <button
              v-for="amount in cashQuickAmounts"
              :key="amount"
              type="button"
              @click="setQuickCash(amount)"
              class="py-2 px-2 border border-[#E8E1D9] rounded-lg text-xs font-mono font-semibold text-stone-700 bg-white hover:bg-stone-50 active:scale-95 transition-all text-center"
            >
              {{ formatCurrency(amount) }}
            </button>
          </div>

          <!-- Change calculation -->
          <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#E8E1D9] flex justify-between items-center">
            <span class="text-xs font-semibold text-stone-600">Kembalian</span>
            <span
              :class="[
                'font-mono text-lg font-bold',
                change >= 0 ? 'text-emerald-700' : 'text-rose-600'
              ]"
            >
              {{ formatCurrency(change) }}
            </span>
          </div>
        </div>

        <!-- Split Payment Section -->
        <div v-if="paymentMethod === 'split'" class="space-y-3 pt-2 border-t border-[#E8E1D9]">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold uppercase tracking-wider text-stone-600">Kombinasi Metode</label>
            <button
              type="button"
              @click="emit('add-split')"
              class="text-xs text-[#8B6B3D] font-bold hover:underline flex items-center gap-1"
            >
              <Plus class="w-3.5 h-3.5" />
              Tambah Baris
            </button>
          </div>

          <div class="space-y-2">
            <div
              v-for="(split, index) in splitPayments"
              :key="index"
              class="flex items-center gap-2"
            >
              <select
                v-model="split.method"
                class="w-32 px-3 py-2 border border-[#E8E1D9] rounded-lg text-xs bg-white text-stone-800 focus:ring-2 focus:ring-[#8B6B3D]"
              >
                <option value="cash">Tunai</option>
                <option value="qris">QRIS</option>
                <option value="ewallet">E-Wallet</option>
                <option value="card">Kartu</option>
              </select>
              <input
                v-model.number="split.amount"
                type="number"
                step="1000"
                class="flex-1 px-3 py-2 border border-[#E8E1D9] rounded-lg text-xs font-mono font-bold text-stone-800 focus:ring-2 focus:ring-[#8B6B3D]"
                placeholder="Jumlah"
              />
              <button
                type="button"
                @click="emit('remove-split', index)"
                class="w-8 h-8 flex items-center justify-center text-stone-400 hover:text-rose-600 transition-colors"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div class="p-2.5 rounded-lg bg-stone-50 border border-stone-200 text-xs flex justify-between">
            <span>Total Split: {{ formatCurrency(totalSplitAmount) }}</span>
            <span
              :class="isSplitValid ? 'text-emerald-700 font-bold' : 'text-rose-600 font-bold'"
            >
              {{ isSplitValid ? 'Pas' : `Sisa: ${formatCurrency(Math.abs(roundedTotal - totalSplitAmount))}` }}
            </span>
          </div>
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="p-4 bg-[#FAF7F2] border-t border-[#E8E1D9] flex gap-2">
        <button
          type="button"
          @click="handleClose"
          class="flex-1 h-12 border border-[#E8E1D9] bg-white hover:bg-stone-50 text-stone-700 rounded-xl font-bold text-sm transition-colors"
        >
          Batal
        </button>
        <button
          type="button"
          @click="processPayment"
          :disabled="(paymentMethod === 'cash' && cashReceived < roundedTotal) || (paymentMethod === 'split' && !isSplitValid)"
          class="flex-2 h-12 bg-[#8B6B3D] hover:bg-[#76582E] active:scale-[0.99] text-white rounded-xl font-bold text-sm shadow transition-all disabled:opacity-40 disabled:pointer-events-none tracking-wide"
        >
          Konfirmasi Bayar
        </button>
      </div>
    </div>
  </div>
</template>
