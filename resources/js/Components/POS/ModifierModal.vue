<script setup>
import { computed, watch } from 'vue';

const props = defineProps({
  product: { type: Object, required: true },
  selectedVariantId: { type: [Number, String, null], default: null },
  selectedModifierIds: { type: Array, required: true },
  selectedToppingIds: { type: Array, required: true },
  selectedToppingQuantities: { type: Object, required: true },
  currentItemNotes: { type: String, default: '' },
});

const emit = defineEmits([
  'close',
  'update:selectedVariantId',
  'toggle-modifier',
  'toggle-topping',
  'increment-topping',
  'decrement-topping',
  'update:currentItemNotes',
  'confirm',
]);

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

const variantGroups = computed(() => {
  if (!props.product?.variants?.length) return [];
  return props.product.variants;
});

const modifierGroups = computed(() => {
  if (!props.product?.modifierGroups?.length) return [];
  return props.product.modifierGroups;
});

const toppings = computed(() => {
  if (!props.product?.toppings?.length) return [];
  return props.product.toppings;
});

const isModifierSelected = (modifierId) => {
  return props.selectedModifierIds.includes(modifierId);
};

const isToppingSelected = (toppingId) => {
  return props.selectedToppingIds.includes(toppingId);
};

const getToppingQuantity = (toppingId) => {
  return props.selectedToppingQuantities[toppingId] || 0;
};

const handleClose = () => {
  emit('close');
};

const handleConfirm = () => {
  emit('confirm');
};

const handleVariantClick = (variantId) => {
  emit('update:selectedVariantId', variantId);
};

const handleModifierClick = (modifierId, selectionType) => {
  emit('toggle-modifier', modifierId, selectionType);
};

const handleToppingClick = (toppingId) => {
  emit('toggle-topping', toppingId);
};

const handleIncrementTopping = (toppingId) => {
  emit('increment-topping', toppingId);
};

const handleDecrementTopping = (toppingId) => {
  emit('decrement-topping', toppingId);
};

const handleNotesChange = (value) => {
  emit('update:currentItemNotes', value);
};
</script>

<template>
  <Transition name="modal-fade">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10 dark:bg-zinc-900 dark:border-zinc-800">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pilih Varian & Modifier</h3>
          <button @click="handleClose" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        
        <div class="p-4 space-y-4">
          <!-- Variants -->
          <div v-if="variantGroups.length > 0" class="space-y-3">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Varian</label>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="variant in variantGroups"
                :key="variant.id"
                @click="handleVariantClick(variant.id)"
                :class="[
                  'px-4 py-2 border rounded-lg text-sm font-medium transition-all',
                  selectedVariantId == variant.id
                    ? 'border-coffee-600 bg-coffee-50 text-coffee-700 dark:border-coffee-400 dark:bg-coffee-900/30 dark:text-coffee-300'
                    : 'border-gray-200 text-gray-600 hover:border-gray-300 dark:border-zinc-700 dark:text-gray-400 dark:hover:border-zinc-600'
                ]"
              >
                {{ variant.name }}
                <span v-if="variant.price_adjustment !== 0" class="ml-1">
                  ({{ variant.price_adjustment > 0 ? '+' : '' }}{{ formatCurrency(variant.price_adjustment) }})
                </span>
              </button>
            </div>
          </div>
          
          <!-- Modifier Groups -->
          <div v-if="modifierGroups.length > 0" class="space-y-4">
            <div v-for="mg in modifierGroups" :key="mg.id" class="space-y-2">
              <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                  {{ mg.name }}
                  <span v-if="mg.is_required" class="text-red-500 ml-1">*</span>
                </label>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  {{ mg.selection_type === 'single' ? 'Pilih 1' : 'Pilih hingga ' + (mg.max_selection ?? 'bebas') }}
                </span>
              </div>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="mod in mg.modifiers"
                  :key="mod.id"
                  @click="handleModifierClick(mod.id, mg.selection_type)"
                  :class="[
                    'px-3 py-1.5 border rounded-lg text-sm transition-all',
                    isModifierSelected(mod.id)
                      ? 'border-coffee-600 bg-coffee-50 text-coffee-700 dark:border-coffee-400 dark:bg-coffee-900/30 dark:text-coffee-300'
                      : 'border-gray-200 text-gray-600 hover:border-gray-300 dark:border-zinc-700 dark:text-gray-400 dark:hover:border-zinc-600'
                  ]"
                >
                  {{ mod.name }}
                  <span v-if="mod.price_adjustment > 0" class="ml-1">(+{{ formatCurrency(mod.price_adjustment) }})</span>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Toppings -->
          <div v-if="toppings.length > 0" class="space-y-3">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Topping / Extra</label>
            <div class="space-y-2">
              <div
                v-for="topping in toppings"
                :key="topping.id"
                class="flex items-center justify-between p-2 border border-gray-200 rounded-lg dark:border-zinc-700"
              >
                <div class="flex items-center gap-3">
                  <input
                    type="checkbox"
                    :checked="isToppingSelected(topping.id)"
                    @change="handleToppingClick(topping.id)"
                    class="w-4 h-4 text-coffee-600 border-gray-300 rounded focus:ring-coffee-500 dark:border-zinc-700"
                  >
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ topping.name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">+{{ formatCurrency(topping.price) }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <button
                    @click="handleDecrementTopping(topping.id)"
                    :disabled="getToppingQuantity(topping.id) <= 0"
                    class="w-6 h-6 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-100 disabled:opacity-50 dark:border-zinc-700 dark:hover:bg-zinc-800"
                  >
                    <span class="material-symbols-outlined text-xs">remove</span>
                  </button>
                  <span class="w-8 text-center text-sm font-medium">{{ getToppingQuantity(topping.id) }}</span>
                  <button
                    @click="handleIncrementTopping(topping.id)"
                    class="w-6 h-6 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-100 dark:border-zinc-700 dark:hover:bg-zinc-800"
                  >
                    <span class="material-symbols-outlined text-xs">add</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Notes -->
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
              <textarea
                :value="currentItemNotes"
                @input="handleNotesChange($event.target.value)"
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coffee-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                placeholder="Contoh: Kurang manis, tanpa es, dll."
              ></textarea>
          </div>
          
          <!-- Confirm Button -->
          <button
            @click="handleConfirm"
            class="w-full px-4 py-3 bg-coffee-600 text-white rounded-lg font-medium hover:bg-coffee-700 transition-colors"
          >
            Tambah ke Keranjang
          </button>
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
.dark .bg-gray-100 {
  background-color: #2b2422 !important;
}
</style>