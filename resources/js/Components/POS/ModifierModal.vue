<script setup lang="ts">
import { computed } from 'vue';
import type { Product, ProductVariant, ModifierGroup, Topping } from '@/Types/pos';
import { X, Plus, Minus } from 'lucide-vue-next';

interface Props {
  product: Product;
  selectedVariantId?: number | string | null;
  selectedModifierIds: number[];
  selectedToppingIds: number[];
  selectedToppingQuantities: Record<number, number>;
  currentItemNotes?: string;
}

const props = withDefaults(defineProps<Props>(), {
  selectedVariantId: null,
  currentItemNotes: '',
});

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'update:selectedVariantId', id: number | null): void;
  (e: 'toggle-modifier', id: number, selectionType: 'single' | 'multiple'): void;
  (e: 'toggle-topping', id: number): void;
  (e: 'increment-topping', id: number): void;
  (e: 'decrement-topping', id: number): void;
  (e: 'update:currentItemNotes', notes: string): void;
  (e: 'confirm'): void;
}>();

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

const variantGroups = computed<ProductVariant[]>(() => {
  return props.product?.variants ?? [];
});

const modifierGroups = computed<ModifierGroup[]>(() => {
  return props.product?.modifierGroups ?? [];
});

const toppings = computed<Topping[]>(() => {
  return props.product?.toppings ?? [];
});

const isModifierSelected = (modifierId: number): boolean => {
  return props.selectedModifierIds.includes(modifierId);
};

const isToppingSelected = (toppingId: number): boolean => {
  return props.selectedToppingIds.includes(toppingId);
};

const getToppingQuantity = (toppingId: number): number => {
  return props.selectedToppingQuantities[toppingId] || 0;
};
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs select-none">
    <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full max-h-[85vh] flex flex-col border border-[#E8E1D9] overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-[#E8E1D9] bg-[#FAF7F2] flex items-center justify-between">
        <div>
          <h3 class="text-base font-bold text-[#2F2212]">Kustomisasi Pesanan</h3>
          <p class="text-xs text-[#8B6B3D] font-medium">{{ product.name }}</p>
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
      <div class="p-6 space-y-5 overflow-y-auto flex-1">
        <!-- Variants -->
        <div v-if="variantGroups.length > 0" class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-stone-600">Pilihan Varian</label>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="variant in variantGroups"
              :key="variant.id"
              type="button"
              @click="emit('update:selectedVariantId', variant.id)"
              :class="[
                'px-3.5 py-2 rounded-xl text-xs font-semibold border transition-all',
                selectedVariantId == variant.id
                  ? 'border-[#8B6B3D] bg-[#8B6B3D] text-white shadow-sm'
                  : 'border-[#E8E1D9] bg-white text-stone-700 hover:border-stone-400'
              ]"
            >
              {{ variant.name }}
              <span v-if="variant.price_adjustment !== 0" class="ml-1 opacity-90 font-mono">
                ({{ variant.price_adjustment > 0 ? '+' : '' }}{{ formatCurrency(variant.price_adjustment) }})
              </span>
            </button>
          </div>
        </div>

        <!-- Modifier Groups -->
        <div v-if="modifierGroups.length > 0" class="space-y-4">
          <div v-for="mg in modifierGroups" :key="mg.id" class="space-y-2">
            <div class="flex items-center justify-between">
              <label class="text-xs font-bold uppercase tracking-wider text-stone-600">
                {{ mg.name }}
                <span v-if="mg.is_required" class="text-rose-500 ml-0.5">*</span>
              </label>
              <span class="text-[10px] text-stone-400">
                {{ mg.selection_type === 'single' ? 'Pilih 1' : 'Pilih bebas' }}
              </span>
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="mod in mg.modifiers"
                :key="mod.id"
                type="button"
                @click="emit('toggle-modifier', mod.id, mg.selection_type)"
                :class="[
                  'px-3.5 py-1.5 rounded-lg text-xs font-medium border transition-all',
                  isModifierSelected(mod.id)
                    ? 'border-[#8B6B3D] bg-[#8B6B3D]/15 text-[#8B6B3D] font-bold'
                    : 'border-[#E8E1D9] bg-white text-stone-700 hover:border-stone-300'
                ]"
              >
                {{ mod.name }}
                <span v-if="mod.price_adjustment > 0" class="ml-1 font-mono text-[11px] opacity-80">
                  (+{{ formatCurrency(mod.price_adjustment) }})
                </span>
              </button>
            </div>
          </div>
        </div>

        <!-- Toppings -->
        <div v-if="toppings.length > 0" class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-stone-600">Topping / Tambahan</label>
          <div class="space-y-2">
            <div
              v-for="topping in toppings"
              :key="topping.id"
              class="flex items-center justify-between p-2.5 border border-[#E8E1D9] rounded-xl hover:border-stone-300 transition-colors"
            >
              <div class="flex items-center gap-3">
                <input
                  type="checkbox"
                  :checked="isToppingSelected(topping.id)"
                  @change="emit('toggle-topping', topping.id)"
                  class="w-4 h-4 text-[#8B6B3D] border-stone-300 rounded focus:ring-[#8B6B3D]"
                />
                <div>
                  <p class="text-xs font-bold text-stone-800">{{ topping.name }}</p>
                  <p class="text-[11px] font-mono text-[#8B6B3D]">+{{ formatCurrency(topping.price) }}</p>
                </div>
              </div>
              <div class="flex items-center gap-1.5">
                <button
                  type="button"
                  @click="emit('decrement-topping', topping.id)"
                  :disabled="getToppingQuantity(topping.id) <= 0"
                  class="w-6 h-6 border border-[#E8E1D9] rounded flex items-center justify-center hover:bg-stone-100 disabled:opacity-30"
                >
                  <Minus class="w-3 h-3" />
                </button>
                <span class="w-6 text-center text-xs font-mono font-bold">{{ getToppingQuantity(topping.id) }}</span>
                <button
                  type="button"
                  @click="emit('increment-topping', topping.id)"
                  class="w-6 h-6 border border-[#E8E1D9] rounded flex items-center justify-center hover:bg-stone-100"
                >
                  <Plus class="w-3 h-3" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="space-y-1.5">
          <label class="block text-xs font-bold uppercase tracking-wider text-stone-600">Catatan Khusus</label>
          <textarea
            :value="currentItemNotes"
            @input="emit('update:currentItemNotes', ($event.target as HTMLTextAreaElement).value)"
            rows="2"
            class="w-full px-3 py-2 border border-[#E8E1D9] rounded-xl focus:ring-2 focus:ring-[#8B6B3D] text-xs text-stone-800 placeholder:text-stone-400"
            placeholder="Contoh: Kurang manis, ekstra panas, pisah es..."
          ></textarea>
        </div>
      </div>

      <!-- Footer Confirm -->
      <div class="p-4 bg-[#FAF7F2] border-t border-[#E8E1D9]">
        <button
          type="button"
          @click="emit('confirm')"
          class="w-full h-12 bg-[#8B6B3D] hover:bg-[#76582E] active:scale-[0.99] text-white rounded-xl font-bold text-sm shadow transition-all tracking-wide"
        >
          Konfirmasi & Tambah ke Keranjang
        </button>
      </div>
    </div>
  </div>
</template>
