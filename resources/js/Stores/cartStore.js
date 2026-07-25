import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCartStore = defineStore('cart', () => {
  // State
  const items = ref([]);
  const orderType = ref('dine_in'); // dine_in, takeaway
  const selectedTableId = ref(null);
  const customerName = ref('');
  const tabId = ref(null);
  const notes = ref('');

  // Modals
  const showPaymentModal = ref(false);
  const showModifierModal = ref(false);
  const showQrisModal = ref(false);
  const showSplitBillModal = ref(false);
  const showMergeTabModal = ref(false);
  const showTransferTabModal = ref(false);

  // Payment
  const paymentMethod = ref('cash');
  const cashReceived = ref(0);
  const splitPayments = ref([]);

  // Modifier selection
  const currentProduct = ref(null);
  const selectedVariantId = ref(null);
  const selectedModifierIds = ref([]);
  const selectedToppingIds = ref([]);
  const selectedToppingQuantities = ref({});
  const currentItemNotes = ref('');

  // Split bill
  const splitBillTabId = ref(null);
  const splitBillItems = ref([]);
  const splitBillNewTabCustomerName = ref('');
  const splitBillNewTabTableId = ref(null);

  // Merge tab
  const mergeTabSourceId = ref(null);
  const mergeTabTargetId = ref(null);

  // Transfer tab
  const transferTabId = ref(null);
  const transferTabNewTableId = ref(null);
  const transferTabNewCustomerName = ref('');

  // QRIS
  const qrisQrString = ref('');
  const qrisQrImageUrl = ref('');
  const qrisPaymentNumber = ref('');
  const qrisAmount = ref(0);
  const qrisExpiresAt = ref(null);
  const qrisErrorMessage = ref('');

  // Getters
  const itemCount = computed(() => items.value.reduce((sum, item) => sum + item.quantity, 0));
  
  const subtotal = computed(() => items.value.reduce((sum, item) => sum + item.subtotal, 0));

  const taxRate = computed(() => {
    // Default 11% PPN
    return 0.11;
  });

  const serviceChargeRate = computed(() => {
    // Default 5% service charge
    return 0.05;
  });

  const taxAmount = computed(() => subtotal.value * taxRate.value);
  const serviceCharge = computed(() => subtotal.value * serviceChargeRate.value);
  const total = computed(() => subtotal.value + taxAmount.value + serviceCharge.value);

  const rounding = computed(() => {
    // Default rounding to 100
    return 100;
  });

  const roundedTotal = computed(() => Math.ceil(total.value / rounding.value) * rounding.value);
  const change = computed(() => Math.max(0, cashReceived.value - roundedTotal.value));

  const isEmpty = computed(() => items.value.length === 0);
  const canPay = computed(() => !isEmpty.value && (paymentMethod.value !== 'cash' || cashReceived.value >= roundedTotal.value));

  const hasRequiredModifiers = computed(() => {
    if (!currentProduct.value) return false;
    return currentProduct.value.modifierGroups?.some(mg => mg.is_required) ?? false;
  });

  // Actions
  function addItem(product, variant = null) {
    const variantId = variant?.id ?? null;
    const variantName = variant?.name ?? null;
    const priceAdjustment = variant?.price_adjustment ?? 0;
    const basePrice = product.base_price + priceAdjustment;

    const cartItem = {
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

    // Check if product has modifiers or toppings
    const hasModifiers = product.modifierGroups?.length > 0;
    const hasToppings = product.toppings?.length > 0;
    const hasRequiredModifiers = product.modifierGroups?.some(mg => mg.is_required) ?? false;

    if (hasRequiredModifiers || hasModifiers || hasToppings) {
      // Open modifier modal
      currentProduct.value = product;
      selectedVariantId.value = variantId;
      selectedModifierIds.value = [];
      selectedToppingIds.value = [];
      selectedToppingQuantities.value = {};
      currentItemNotes.value = '';
      showModifierModal.value = true;
    } else {
      // Add directly to cart
      addItemToCart(cartItem);
    }
  }

  function addItemToCart(cartItem) {
    const existingIndex = findExistingItemIndex(cartItem);
    
    if (existingIndex !== -1) {
      items.value[existingIndex].quantity += cartItem.quantity;
      items.value[existingIndex].subtotal = items.value[existingIndex].quantity * items.value[existingIndex].base_price;
    } else {
      items.value.push(cartItem);
    }
  }

  function findExistingItemIndex(newItem) {
    return items.value.findIndex(item => 
      item.product_id === newItem.product_id &&
      item.variant_id === newItem.variant_id &&
      modifiersMatch(item.modifiers, newItem.modifiers) &&
      toppingsMatch(item.toppings, newItem.toppings)
    );
  }

  function modifiersMatch(modifiers1, modifiers2) {
    if (modifiers1.length !== modifiers2.length) return false;
    const ids1 = modifiers1.map(m => m.id).sort();
    const ids2 = modifiers2.map(m => m.id).sort();
    return ids1.every((id, index) => id === ids2[index]);
  }

  function toppingsMatch(toppings1, toppings2) {
    if (toppings1.length !== toppings2.length) return false;
    const ids1 = toppings1.map(t => t.id).sort();
    const ids2 = toppings2.map(t => t.id).sort();
    return ids1.every((id, index) => id === ids2[index]);
  }

  function updateQuantity(itemId, quantity) {
    const index = items.value.findIndex(item => item.id === itemId);
    if (index !== -1) {
      if (quantity <= 0) {
        items.value.splice(index, 1);
      } else {
        items.value[index].quantity = quantity;
        items.value[index].subtotal = quantity * items.value[index].base_price;
      }
    }
  }

  function removeItem(itemId) {
    items.value = items.value.filter(item => item.id !== itemId);
  }

  function clearCart() {
    items.value = [];
    orderType.value = 'dine_in';
    selectedTableId.value = null;
    customerName.value = '';
    tabId.value = null;
    notes.value = '';
  }

  function setOrderType(type) {
    orderType.value = type;
    if (type === 'takeaway') {
      selectedTableId.value = null;
    }
  }

  function setSelectedTable(tableId) {
    selectedTableId.value = tableId;
  }

  function setCustomerName(name) {
    customerName.value = name;
  }

  function setTabId(id) {
    tabId.value = id;
  }

  function loadTab(tab) {
    tabId.value = tab.id;
    selectedTableId.value = tab.table_id;
    customerName.value = tab.customer_name;
    orderType.value = 'dine_in';
    items.value = tab.items.map(item => ({
      id: crypto.randomUUID(),
      product_id: item.product_id,
      product_name: item.product_name,
      variant_id: item.variant_id,
      variant_name: item.variant_name,
      base_price: item.unit_price,
      quantity: item.quantity,
      modifiers: item.modifiers.map(m => ({
        id: m.modifier_id,
        name: m.modifier_name,
        price_adjustment: m.price_adjustment,
      })),
      toppings: item.toppings.map(t => ({
        id: t.topping_id,
        name: t.topping_name,
        price: t.unit_price,
        quantity: t.quantity,
      })),
      notes: item.notes,
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

    const variant = currentProduct.value.variants?.find(v => v.id === selectedVariantId.value) ?? null;
    const variantId = variant?.id ?? null;
    const variantName = variant?.name ?? null;
    const priceAdjustment = variant?.price_adjustment ?? 0;
    const basePrice = currentProduct.value.base_price + priceAdjustment;

    // Get selected modifiers
    const modifiers = currentProduct.value.modifierGroups
      ?.flatMap(mg => mg.modifiers)
      .filter(m => selectedModifierIds.value.includes(m.id))
      .map(m => ({
        id: m.id,
        name: m.name,
        price_adjustment: m.price_adjustment,
      })) ?? [];

    // Get selected toppings
    const toppings = currentProduct.value.toppings
      ?.filter(t => selectedToppingIds.value.includes(t.id))
      .map(t => ({
        id: t.id,
        name: t.name,
        price: t.price,
        quantity: selectedToppingQuantities.value[t.id] ?? 1,
      })) ?? [];

    const modifierSubtotal = modifiers.reduce((sum, m) => sum + m.price_adjustment, 0);
    const toppingSubtotal = toppings.reduce((sum, t) => sum + t.price * t.quantity, 0);
    const itemBasePrice = basePrice + modifierSubtotal + toppingSubtotal;

    const cartItem = {
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

  function openModifierModal(product, existingItem = null) {
    currentProduct.value = product;
    selectedVariantId.value = existingItem?.variant_id ?? null;
    selectedModifierIds.value = existingItem?.modifiers?.map(m => m.id) ?? [];
    selectedToppingIds.value = existingItem?.toppings?.map(t => t.id) ?? [];
    selectedToppingQuantities.value = {};
    existingItem?.toppings?.forEach(t => {
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

  function toggleModifier(modifierId, selectionType) {
    const index = selectedModifierIds.value.indexOf(modifierId);
    if (selectionType === 'single') {
      // Single select - replace all
      selectedModifierIds.value = [modifierId];
    } else {
      // Multi select - toggle
      if (index === -1) {
        selectedModifierIds.value.push(modifierId);
      } else {
        selectedModifierIds.value.splice(index, 1);
      }
    }
  }

  function toggleTopping(toppingId) {
    const index = selectedToppingIds.value.indexOf(toppingId);
    if (index === -1) {
      selectedToppingIds.value.push(toppingId);
      selectedToppingQuantities.value[toppingId] = 1;
    } else {
      selectedToppingIds.value.splice(index, 1);
      delete selectedToppingQuantities.value[toppingId];
    }
  }

  function incrementTopping(toppingId) {
    if (selectedToppingQuantities.value[toppingId]) {
      selectedToppingQuantities.value[toppingId]++;
    } else {
      selectedToppingQuantities.value[toppingId] = 1;
      if (!selectedToppingIds.value.includes(toppingId)) {
        selectedToppingIds.value.push(toppingId);
      }
    }
  }

  function decrementTopping(toppingId) {
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
  function openSplitBillModal(tab) {
    splitBillTabId.value = tab.id;
    splitBillItems.value = tab.items.map(item => ({
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
  function openTransferTabModal(tab) {
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
  function openQrisModal(data) {
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

  function setQrisError(message) {
    qrisErrorMessage.value = message;
  }

  function addSplitPayment() {
    splitPayments.value.push({
      method: 'cash',
      amount: 0,
      reference: '',
    });
  }

  function removeSplitPayment(index) {
    splitPayments.value.splice(index, 1);
  }

  return {
    // State
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

    // Getters
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

    // Actions
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