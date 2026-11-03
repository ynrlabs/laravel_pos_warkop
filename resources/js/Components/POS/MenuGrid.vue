<script setup lang="ts">
import { ref, computed } from 'vue';
import type { Product } from '@/Types/pos';
import { Coffee, SearchX, Plus, Sparkles } from 'lucide-vue-next';

interface Props {
  products: Product[];
  isEmpty?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  isEmpty: false,
});

const emit = defineEmits<{
  (e: 'add-to-cart', product: Product): void;
}>();

const failedImages = ref<Record<number, boolean>>({});

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

const getProductImage = (product: Product): string | null => {
  if (product.image_path) {
    return `/storage/${product.image_path}`;
  }
  return null;
};

const getStockStatus = (product: Product): 'normal' | 'low' | 'out' => {
  if (!product.track_stock) return 'normal';
  if (product.stock_qty <= 0) return 'out';
  if (product.stock_qty <= product.min_stock) return 'low';
  return 'normal';
};
</script>

<template>
  <div class="h-full">
    <!-- Empty State -->
    <div v-if="isEmpty" class="flex flex-col items-center justify-center h-64 text-stone-400">
      <div class="w-14 h-14 rounded-full bg-stone-100 flex items-center justify-center text-stone-400 mb-3">
        <SearchX class="w-7 h-7" />
      </div>
      <p class="text-sm font-semibold text-stone-700">Tidak ada produk ditemukan</p>
      <p class="text-xs text-stone-500 mt-1">Coba ubah kata kunci atau pilih kategori lain</p>
    </div>

    <!-- Products Grid -->
    <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 pb-6">
      <div
        v-for="product in products"
        :key="product.id"
        @click="emit('add-to-cart', product)"
        class="group relative flex flex-col justify-between rounded-xl bg-white border border-[#E8E1D9] hover:border-[#8B6B3D]/60 shadow-[0_1px_3px_rgba(0,0,0,0.04)] hover:shadow-md transition-all duration-150 active:scale-[0.98] cursor-pointer overflow-hidden select-none"
      >
        <!-- Badges -->
        <div class="absolute top-2.5 inset-x-2.5 flex justify-between items-center z-10 pointer-events-none">
          <!-- Stock status badge -->
          <div>
            <span
              v-if="product.track_stock && getStockStatus(product) !== 'normal'"
              :class="[
                'px-2 py-0.5 rounded-full text-[10px] font-bold font-mono tracking-wide shadow-sm',
                getStockStatus(product) === 'low'
                  ? 'bg-amber-500 text-white'
                  : 'bg-rose-600 text-white'
              ]"
            >
              {{ getStockStatus(product) === 'low' ? `Sisa ${product.stock_qty}` : 'Habis' }}
            </span>
          </div>

          <!-- Best Seller -->
          <span
            v-if="product.is_bestseller"
            class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#D4B87A] text-[#2F2212] shadow-sm flex items-center gap-1"
          >
            <Sparkles class="w-3 h-3" />
            Favorit
          </span>
        </div>

        <!-- Product Image or Placeholder -->
        <div class="relative h-28 w-full bg-[#F5EFE4] flex items-center justify-center overflow-hidden shrink-0">
          <img
            v-if="getProductImage(product) && !failedImages[product.id]"
            :src="getProductImage(product)!"
            :alt="product.name"
            @error="failedImages[product.id] = true"
            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
            loading="lazy"
          />
          <div v-else class="flex flex-col items-center justify-center text-[#8B6B3D]/50">
            <Coffee class="w-8 h-8 stroke-[1.5]" />
            <span class="text-[10px] font-medium mt-1 text-[#8B6B3D]/70">{{ product.category?.name || 'Menu' }}</span>
          </div>
        </div>

        <!-- Product Details -->
        <div class="p-3 flex flex-col justify-between flex-1 gap-2">
          <div>
            <span class="text-[11px] font-medium text-stone-400 block uppercase tracking-wider">
              {{ product.category?.name || 'Menu' }}
            </span>
            <h3 class="text-sm font-bold text-[#2F2212] leading-snug line-clamp-2 mt-0.5 group-hover:text-[#8B6B3D] transition-colors">
              {{ product.name }}
            </h3>
            <span v-if="product.variants && product.variants.length > 1" class="text-[10px] text-stone-500 block mt-0.5">
              {{ product.variants.length }} pilihan varian
            </span>
          </div>

          <div class="pt-2 border-t border-[#F5EFE4] flex items-center justify-between mt-auto">
            <span class="font-mono font-bold text-sm text-[#8B6B3D]">
              {{ formatCurrency(product.effective_price ?? product.base_price) }}
            </span>
            <div
              class="w-7 h-7 rounded-lg bg-[#8B6B3D]/10 text-[#8B6B3D] group-hover:bg-[#8B6B3D] group-hover:text-white flex items-center justify-center transition-colors"
            >
              <Plus class="w-4 h-4 stroke-[2.5]" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
