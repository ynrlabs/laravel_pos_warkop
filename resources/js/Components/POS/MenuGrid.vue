<script setup>
import { computed } from 'vue';

const props = defineProps({
  products: {
    type: Array,
    required: true,
  },
  isEmpty: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['add-to-cart']);

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

const getProductImage = (product) => {
  if (product.image_path) {
    return `/storage/${product.image_path}`;
  }
  return null;
};

const getStockStatus = (product) => {
  if (!product.track_stock) return 'normal';
  if (product.stock_qty <= 0) return 'out';
  if (product.stock_qty <= product.min_stock) return 'low';
  return 'normal';
};
</script>

<template>
  <div class="flex-1 overflow-hidden">
    <div v-if="isEmpty" class="flex flex-col items-center justify-center h-full text-on-surface-variant/60">
      <span class="material-symbols-outlined text-5xl mb-2">search_off</span>
      <p class="text-sm font-semibold">Tidak ada produk ditemukan</p>
      <p class="text-xs">Coba ubah kategori atau kata kunci pencarian</p>
    </div>
    
    <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-gutter h-full overflow-y-auto custom-scroll pr-1">
      <div
        v-for="product in products"
        :key="product.id"
        @click="$emit('add-to-cart', product)"
        class="bg-surface rounded-xl border border-outline-variant overflow-hidden shadow-sm hover:shadow-md transition-all active:scale-95 cursor-pointer relative flex flex-col group"
      >
        <!-- Best Seller Badge -->
        <span v-if="product.is_bestseller" class="absolute top-2 right-2 bg-secondary-container text-on-secondary-container font-num-md text-[10px] px-2 py-0.5 rounded-full font-bold z-10">
          Best Seller
        </span>
        
        <!-- Stock Status -->
        <span v-if="product.track_stock && getStockStatus(product) !== 'normal'" class="absolute top-2 left-2 z-10"
          :class="[
            'px-2 py-0.5 rounded-full font-bold text-[10px] font-num-md',
            getStockStatus(product) === 'low' 
              ? 'bg-warning text-on-warning low-stock-pulse' 
              : 'bg-error text-on-error'
          ]"
        >
          {{ getStockStatus(product) === 'low' ? `Low: ${product.stock_qty}` : 'Habis' }}
        </span>

        <!-- Product Image -->
        <div class="h-32 bg-surface-container flex items-center justify-center overflow-hidden shrink-0 relative">
          <img
            v-if="getProductImage(product)"
            :src="getProductImage(product)"
            :alt="product.name"
            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
            loading="lazy"
          />
          <div v-else class="w-full h-full flex flex-col items-center justify-center bg-surface-container-high text-outline">
            <span class="material-symbols-outlined text-4xl">coffee</span>
            <span class="text-[10px] mt-1">{{ product.category?.name }}</span>
          </div>
        </div>
        
        <!-- Product Info -->
        <div class="p-3 flex flex-col justify-between flex-1 gap-2">
          <div class="flex-1 min-h-0">
            <span class="font-headline-sm text-sm leading-tight text-on-surface font-bold block truncate">{{ product.name }}</span>
            <span v-if="product.variants?.length > 0" class="text-[11px] text-on-surface-variant block italic truncate">
              {{ product.variants[0].name }} {{ product.variants.length > 1 ? `+${product.variants.length - 1} varian` : '' }}
            </span>
          </div>
          
          <!-- Price & Modifier Indicator -->
          <div class="flex items-center justify-between">
            <span class="font-num-lg text-sm text-primary text-right mt-auto block font-semibold">
              {{ formatCurrency(product.effective_price ?? product.base_price) }}
            </span>
            <div class="flex items-center gap-1">
              <span v-if="product.modifierGroups?.length > 0" class="material-symbols-outlined text-[16px] text-on-surface-variant/60" title="Memiliki modifier">tune</span>
              <span v-if="product.toppings?.length > 0" class="material-symbols-outlined text-[16px] text-secondary" title="Memiliki topping">add_circle</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.custom-scroll::-webkit-scrollbar {
  width: 4px;
}
.custom-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background: #d2c4b6;
  border-radius: 10px;
}

@keyframes pulse-red {
  0% { box-shadow: 0 0 0 0 rgba(198, 40, 40, 0.4); }
  70% { box-shadow: 0 0 0 8px rgba(198, 40, 40, 0); }
  100% { box-shadow: 0 0 0 0 rgba(198, 40, 40, 0); }
}
.low-stock-pulse {
  animation: pulse-red 2s infinite;
}

/* Dark Mode */
.dark .bg-surface {
  background-color: #1a1512 !important;
  border-color: #2b221d !important;
  color: #f3ede8 !important;
}
.dark .bg-surface:hover {
  border-color: #e8c08b !important;
}
.dark .bg-surface-container {
  background-color: #221c18 !important;
}
.dark .bg-surface-container-high {
  background-color: #2b231e !important;
}
.dark .text-on-surface {
  color: #f3ede8 !important;
}
.dark .text-on-surface-variant {
  color: #d2c4b6 !important;
}
.dark .text-primary {
  color: #e8c08b !important;
}
.dark .bg-secondary-container {
  background-color: #584411 !important;
}
.dark .text-on-secondary-container {
  color: #fddf9e !important;
}
.dark .bg-warning {
  background-color: #f57f17 !important;
}
.dark .text-on-warning {
  color: #1a1512 !important;
}
.dark .bg-error {
  background-color: #c62828 !important;
}
.dark .text-on-error {
  color: #fff !important;
}
.dark .border-outline-variant {
  border-color: #2b221d !important;
}
</style>