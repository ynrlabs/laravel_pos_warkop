import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type {
  Product,
  ProductVariant,
  CartItem,
  CartItemModifier,
  CartItemTopping,
  Tab,
  SplitPayment,
} from '@/Types/pos';

export const useCartStore = defineStore('cart', () => {
  // State
  const items = ref<CartItem[]>([]);
  const orderType = ref<'dine_in' | 'takeaway'>('dine_in');
  const selectedTableId = ref<number | null>(null);
  const customerName = ref<string>('');
  const tabId = ref<number | null>(null);
  const notes = ref<string>('');

  // Modals
  const showPaymentModal = ref<boolean>(false);
  const showModifierModal = ref<boolean>(false);
  const showQrisModal = ref<boolean>(false);
  const showSplitBillModal = ref<boolean>(false);
  const showMergeTabModal = ref<boolean>(false);
  const showTransferTabModal = ref<boolean>(false);

  // Payment
  const paymentMethod = ref<string>('cash');
  const cashReceived = ref<number>(0);
  const splitPayments = ref<SplitPayment[]>([]);

  // Modifier selection
  const currentProduct = ref<Product | null>(null);
  const selectedVariantId = ref<number | null>(null);
  const selectedModifierIds = ref<number[]>([]);
  const selectedToppingIds = ref<number[]>([]);
  const selectedToppingQuantities = ref<Record<number, number>>({});
  const currentItemNotes = ref<string>('');

  // Split bill
  const splitBillTabId = ref<number | null>(null);
  const splitBillItems = ref<any[]>([]);
  const splitBillNewTabCustomerName = ref<string>('');
  const splitBillNewTabTableId = ref<number | null>(null);

  // Merge tab
  const mergeTabSourceId = ref<number | null>(null);
  const mergeTabTargetId = ref<number | null>(null);

  // Transfer tab
  const transferTabId = ref<number | null>(null);
  const transferTabNewTableId = ref<number | null>(null);
  const transferTabNewCustomerName = ref<string>('');

  // QRIS
  const qrisQrString = ref<string>('');
  const qrisQrImageUrl = ref<string>('');
  const qrisPaymentNumber = ref<string>('');
  const qrisAmount = ref<number>(0);
  const qrisExpiresAt = ref<string | null>(null);
  const qrisErrorMessage = ref<string>('');

  // Getters
  const itemCount = computed<number>(() =>
    items.value.reduce((sum, item) => sum + item.quantity, 0)
  );

  const subtotal = computed<number>(() =>
    items.value.reduce((sum, item) => sum + item.subtotal, 0)
  );

  const taxRate = ref<number>(0.11);
  const serviceChargeRate = ref<number>(0.05);
  const rounding = ref<number>(100);

  const taxAmount = computed<number>(() => Math.round(subtotal.value * taxRate.value));
  const serviceCharge = computed<number>(() => Math.round(subtotal.value * serviceChargeRate.value));
  const total = computed<number>(() => subtotal.value + taxAmount.value + serviceCharge.value);

  const roundedTotal = computed<number>(() => (rounding.value > 0 ? Math.ceil(total.value / rounding.value) * rounding.value : total.value));
  const change = computed<number>(() => Math.max(0, cashReceived.value - roundedTotal.value));

  const isEmpty = computed<boolean>(() => items.value.length === 0);
  const canPay = computed<boolean>(
    () => !isEmpty.value && (paymentMethod.value !== 'cash' || cashReceived.value >= roundedTotal.value)
  );

  const hasRequiredModifiers = computed<boolean>(() => {
    if (!currentProduct.value) return false;
    return currentProduct.value.modifierGroups?.some((mg) => mg.is_required) ?? false;
  });

  // Actions
  function setOutletSettings(settings?: { tax_rate?: number; service_charge?: number; rounding?: number }) {
    if (settings?.tax_rate !== undefined) taxRate.value = Number(settings.tax_rate);
    if (settings?.service_charge !== undefined) serviceChargeRate.value = Number(settings.service_charge);
    if (settings?.rounding !== undefined) rounding.value = Number(settings.rounding);
  }

  function addItem(product: Product, variant: ProductVariant | null = null) {
    const variantId = variant?.id ?? null;
    const variantName = variant?.name ?? null;
    const priceAdjustment = variant?.price_adjustment ?? 0;
    const basePrice = product.base_price + priceAdjustment;

    const cartItem: CartItem = {
      id: crypto.randomUUID(),
      product_id: product.id,
      product_name: product.name,
      variant_id: variantId,
      variant_name: variantName,
      base_price: basePrice,
      quantity: 1,
      modifiers: [],
      toppings: [],
      notes: '',
      subtotal: basePrice,
    };

    const hasModifiers = (product.modifierGroups?.length ?? 0) > 0;
    const hasToppings = (product.toppings?.length ?? 0) > 0;
    const hasRequired = product.modifierGroups?.some((mg) => mg.is_required) ?? false;

    if (hasRequired || hasModifiers || hasToppings) {
      currentProduct.value = product;
      selectedVariantId.value = variantId;
      selectedModifierIds.value = [];
      selectedToppingIds.value = [];
      selectedToppingQuantities.value = {};
      currentItemNotes.value = '';
      showModifierModal.value = true;
    } else {
      addItemToCart(cartItem);
    }
  }

  function addItemToCart(cartItem: CartItem) {
    const existingIndex = findExistingItemIndex(cartItem);

    if (existingIndex !== -1) {
      items.value[existingIndex].quantity += cartItem.quantity;
      items.value[existingIndex].subtotal =
        items.value[existingIndex].quantity * items.value[existingIndex].base_price;
    } else {
      items.value.push(cartItem);
    }
  }

  function findExistingItemIndex(newItem: CartItem): number {
    return items.value.findIndex(
      (item) =>
        item.product_id === newItem.product_id &&
        item.variant_id === newItem.variant_id &&
        modifiersMatch(item.modifiers, newItem.modifiers) &&
        toppingsMatch(item.toppings, newItem.toppings)
    );
  }

  function modifiersMatch(modifiers1: CartItemModifier[], modifiers2: CartItemModifier[]): boolean {
    if (modifiers1.length !== modifiers2.length) return false;
    const ids1 = modifiers1.map((m) => m.id).sort();
    const ids2 = modifiers2.map((m) => m.id).sort();
    return ids1.every((id, index) => id === ids2[index]);
  }

  function toppingsMatch(toppings1: CartItemTopping[], toppings2: CartItemTopping[]): boolean {
    if (toppings1.length !== toppings2.length) return false;
    const ids1 = toppings1.map((t) => t.id).sort();
    const ids2 = toppings2.map((t) => t.id).sort();
    return ids1.every((id, index) => id === ids2[index]);
  }

  function updateQuantity(itemId: string, quantity: number) {
    const index = items.value.findIndex((item) => item.id === itemId);
    if (index !== -1) {
      if (quantity <= 0) {
        items.value.splice(index, 1);
      } else {
        items.value[index].quantity = quantity;
        items.value[index].subtotal = quantity * items.value[index].base_price;
      }
    }
  }

  function removeItem(itemId: string) {
    items.value = items.value.filter((item) => item.id !== itemId);
  }

  function clearCart() {
    items.value = [];
    orderType.value = 'dine_in';
    selectedTableId.value = null;
    customerName.value = '';
    tabId.value = null;
    notes.value = '';
  }

  function setOrderType(type: 'dine_in' | 'takeaway') {
    orderType.value = type;
    if (type === 'takeaway') {
      selectedTableId.value = null;
    }
  }

  function setSelectedTable(tableId: number | null) {
    selectedTableId.value = tableId;
  }

  function setCustomerName(name: string) {
    customerName.value = name;
  }

  function setTabId(id: number | null) {
    tabId.value = id;
  }

  function loadTab(tab: Tab) {
    tabId.value = tab.id;
    selectedTableId.value = tab.table_id;
    customerName.value = tab.customer_name ?? '';
    orderType.value = 'dine_in';
    items.value = tab.items.map((item) => ({
      id: crypto.randomUUID(),
      product_id: item.product_id,
      product_name: item.product_name,
      variant_id: item.variant_id,
      variant_name: item.variant_name,
      base_price: item.unit_price,
      quantity: item.quantity,
      modifiers: item.modifiers.map((m) => ({
        id: m.modifier_id,
        name: m.modifier_name,
        price_adjustment: m.price_adjustment,
      })),
      toppings: item.toppings.map((t) => ({
        id: t.topping_id,
        name: t.topping_name,
        price: t.unit_price,
        quantity: t.quantity,
      })),
      notes: item.notes ?? '',
      subtotal: item.subtotal,
    }));
  }

  function openPaymentModal() {
    if (isEmpty.value) return;
    showPaymentModal.value = true;
    paymentMethod.value = 'cash';
    cashReceived.value = roundedTotal.value;
    splitPayments.value = [];
  }

  function closePaymentModal() {
    showPaymentModal.value = false;
    paymentMethod.value = 'cash';
    cashReceived.value = 0;
    splitPayments.value = [];
  }

  function confirmAddToCart() {
    if (!currentProduct.value) return;

    const variant = currentProduct.value.variants?.find((v) => v.id === selectedVariantId.value) ?? null;
    const variantId = variant?.id ?? null;
    const variantName = variant?.name ?? null;
    const priceAdjustment = variant?.price_adjustment ?? 0;
    const basePrice = currentProduct.value.base_price + priceAdjustment;

    const modifiers: CartItemModifier[] =
      currentProduct.value.modifierGroups
        ?.flatMap((mg) => mg.modifiers)
        .filter((m) => selectedModifierIds.value.includes(m.id))
        .map((m) => ({
          id: m.id,
          name: m.name,
          price_adjustment: m.price_adjustment,
        })) ?? [];

    const toppings: CartItemTopping[] =
      currentProduct.value.toppings
        ?.filter((t) => selectedToppingIds.value.includes(t.id))
        .map((t) => ({
          id: t.id,
          name: t.name,
          price: t.price,
          quantity: selectedToppingQuantities.value[t.id] ?? 1,
        })) ?? [];

    const modifierSubtotal = modifiers.reduce((sum, m) => sum + m.price_adjustment, 0);
    const toppingSubtotal = toppings.reduce((sum, t) => sum + t.price * t.quantity, 0);
    const itemBasePrice = basePrice + modifierSubtotal + toppingSubtotal;

    const cartItem: CartItem = {
      id: crypto.randomUUID(),
      product_id: currentProduct.value.id,
      product_name: currentProduct.value.name,
      variant_id: variantId,
      variant_name: variantName,
      base_price: itemBasePrice,
      quantity: 1,
      modifiers,
      toppings,
      notes: currentItemNotes.value,
      subtotal: itemBasePrice,
    };

    addItemToCart(cartItem);
    closeModifierModal();
  }

  function openModifierModal(product: Product, existingItem: CartItem | null = null) {
    currentProduct.value = product;
    selectedVariantId.value = existingItem?.variant_id ?? null;
    selectedModifierIds.value = existingItem?.modifiers?.map((m) => m.id) ?? [];
    selectedToppingIds.value = existingItem?.toppings?.map((t) => t.id) ?? [];
    selectedToppingQuantities.value = {};
    existingItem?.toppings?.forEach((t) => {
      selectedToppingQuantities.value[t.id] = t.quantity;
    });
    currentItemNotes.value = existingItem?.notes ?? '';
    showModifierModal.value = true;
  }

  function closeModifierModal() {
    showModifierModal.value = false;
    currentProduct.value = null;
    selectedVariantId.value = null;
    selectedModifierIds.value = [];
    selectedToppingIds.value = [];
    selectedToppingQuantities.value = {};
    currentItemNotes.value = '';
  }

  function toggleModifier(modifierId: number, selectionType: 'single' | 'multiple') {
    const index = selectedModifierIds.value.indexOf(modifierId);
    if (selectionType === 'single') {
      selectedModifierIds.value = [modifierId];
    } else {
      if (index === -1) {
        selectedModifierIds.value.push(modifierId);
      } else {
        selectedModifierIds.value.splice(index, 1);
      }
    }
  }

  function toggleTopping(toppingId: number) {
    const index = selectedToppingIds.value.indexOf(toppingId);
    if (index === -1) {
      selectedToppingIds.value.push(toppingId);
      selectedToppingQuantities.value[toppingId] = 1;
    } else {
      selectedToppingIds.value.splice(index, 1);
      delete selectedToppingQuantities.value[toppingId];
    }
  }

  function incrementTopping(toppingId: number) {
    if (selectedToppingQuantities.value[toppingId]) {
      selectedToppingQuantities.value[toppingId]++;
    } else {
      selectedToppingQuantities.value[toppingId] = 1;
      if (!selectedToppingIds.value.includes(toppingId)) {
        selectedToppingIds.value.push(toppingId);
      }
    }
  }

  function decrementTopping(toppingId: number) {
    if (selectedToppingQuantities.value[toppingId]) {
      selectedToppingQuantities.value[toppingId]--;
      if (selectedToppingQuantities.value[toppingId] <= 0) {
        delete selectedToppingQuantities.value[toppingId];
        const index = selectedToppingIds.value.indexOf(toppingId);
        if (index !== -1) {
          selectedToppingIds.value.splice(index, 1);
        }
      }
    }
  }

  // Split Bill
  function openSplitBillModal(tab: Tab) {
    splitBillTabId.value = tab.id;
    splitBillItems.value = tab.items.map((item) => ({
      ...item,
      selected: false,
      split_quantity: 0,
    }));
    splitBillNewTabCustomerName.value = '';
    splitBillNewTabTableId.value = null;
    showSplitBillModal.value = true;
  }

  function closeSplitBillModal() {
    showSplitBillModal.value = false;
    splitBillTabId.value = null;
    splitBillItems.value = [];
    splitBillNewTabCustomerName.value = '';
    splitBillNewTabTableId.value = null;
  }

  // Merge Tab
  function openMergeTabModal() {
    mergeTabSourceId.value = null;
    mergeTabTargetId.value = null;
    showMergeTabModal.value = true;
  }

  function closeMergeTabModal() {
    showMergeTabModal.value = false;
    mergeTabSourceId.value = null;
    mergeTabTargetId.value = null;
  }

  // Transfer Tab
  function openTransferTabModal(tab: Tab) {
    transferTabId.value = tab.id;
    transferTabNewTableId.value = null;
    transferTabNewCustomerName.value = tab.customer_name ?? '';
    showTransferTabModal.value = true;
  }

  function closeTransferTabModal() {
    showTransferTabModal.value = false;
    transferTabId.value = null;
    transferTabNewTableId.value = null;
    transferTabNewCustomerName.value = '';
  }

  // QRIS
  function openQrisModal(data: {
    qr_string?: string;
    qr_image_url?: string;
    payment_number?: string;
    amount?: number;
    expires_at?: string;
  }) {
    qrisQrString.value = data.qr_string ?? '';
    qrisQrImageUrl.value = data.qr_image_url ?? '';
    qrisPaymentNumber.value = data.payment_number ?? '';
    qrisAmount.value = data.amount ?? 0;
    qrisExpiresAt.value = data.expires_at ?? null;
    qrisErrorMessage.value = '';
    showQrisModal.value = true;
    showPaymentModal.value = false;
  }

  function closeQrisModal() {
    showQrisModal.value = false;
    qrisQrString.value = '';
    qrisQrImageUrl.value = '';
    qrisPaymentNumber.value = '';
    qrisAmount.value = 0;
    qrisExpiresAt.value = null;
    qrisErrorMessage.value = '';
  }

  function setQrisError(message: string) {
    qrisErrorMessage.value = message;
  }

  function addSplitPayment() {
    splitPayments.value.push({
      method: 'cash',
      amount: 0,
      reference: '',
    });
  }

  function removeSplitPayment(index: number) {
    splitPayments.value.splice(index, 1);
  }

  return {
    items,
    orderType,
    selectedTableId,
    customerName,
    tabId,
    notes,
    showPaymentModal,
    showModifierModal,
    showQrisModal,
    showSplitBillModal,
    showMergeTabModal,
    showTransferTabModal,
    paymentMethod,
    cashReceived,
    splitPayments,
    currentProduct,
    selectedVariantId,
    selectedModifierIds,
    selectedToppingIds,
    selectedToppingQuantities,
    currentItemNotes,
    splitBillTabId,
    splitBillItems,
    splitBillNewTabCustomerName,
    splitBillNewTabTableId,
    mergeTabSourceId,
    mergeTabTargetId,
    transferTabId,
    transferTabNewTableId,
    transferTabNewCustomerName,
    qrisQrString,
    qrisQrImageUrl,
    qrisPaymentNumber,
    qrisAmount,
    qrisExpiresAt,
    qrisErrorMessage,

    itemCount,
    subtotal,
    taxRate,
    serviceChargeRate,
    taxAmount,
    serviceCharge,
    total,
    rounding,
    roundedTotal,
    change,
    isEmpty,
    canPay,
    hasRequiredModifiers,

    setOutletSettings,
    addItem,
    addItemToCart,
    updateQuantity,
    removeItem,
    clearCart,
    setOrderType,
    setSelectedTable,
    setCustomerName,
    setTabId,
    loadTab,
    openPaymentModal,
    closePaymentModal,
    confirmAddToCart,
    openModifierModal,
    closeModifierModal,
    toggleModifier,
    toggleTopping,
    incrementTopping,
    decrementTopping,
    openSplitBillModal,
    closeSplitBillModal,
    openMergeTabModal,
    closeMergeTabModal,
    openTransferTabModal,
    closeTransferTabModal,
    openQrisModal,
    closeQrisModal,
    setQrisError,
    addSplitPayment,
    removeSplitPayment,
  };
});
