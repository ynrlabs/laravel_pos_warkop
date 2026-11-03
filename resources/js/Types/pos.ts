// POS Types for TypeScript support

export interface Outlet {
  id: number;
  name: string;
  settings: Record<string, any>;
}

export interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  outlet_id: number;
  outlet: Outlet | null;
}

export interface Category {
  id: number;
  outlet_id: number;
  name: string;
  slug: string;
  icon: string | null;
  color: string | null;
  sort_order: number;
  is_active: boolean;
  products_count: number;
}

export interface ProductVariant {
  id: number;
  product_id: number;
  name: string;
  price_adjustment: number;
  is_default: boolean;
  sort_order: number;
  is_active: boolean;
}

export interface Modifier {
  id: number;
  modifier_group_id: number;
  name: string;
  price_adjustment: number;
  sort_order: number;
  is_active: boolean;
}

export interface ModifierGroup {
  id: number;
  outlet_id: number;
  name: string;
  selection_type: 'single' | 'multiple';
  is_required: boolean;
  max_selection: number | null;
  sort_order: number;
  is_active: boolean;
  modifiers: Modifier[];
}

export interface Topping {
  id: number;
  outlet_id: number;
  name: string;
  price: number;
  sort_order: number;
  is_active: boolean;
}

export interface Product {
  id: number;
  outlet_id: number;
  category_id: number;
  name: string;
  slug: string;
  description: string | null;
  base_price: number;
  effective_price: number;
  image_path: string | null;
  type: 'finished_good' | 'raw_material';
  track_stock: boolean;
  stock_qty: number;
  min_stock: number;
  is_bestseller: boolean;
  is_active: boolean;
  sort_order: number;
  category: Category;
  variants: ProductVariant[];
  modifierGroups: ModifierGroup[];
  toppings: Topping[];
}

export interface Table {
  id: number;
  outlet_id: number;
  number: string;
  label: string;
  capacity: number;
  status: 'available' | 'occupied' | 'reserved' | 'maintenance';
  position_x: number | null;
  position_y: number | null;
}

export interface TabItem {
  id: number;
  tab_id: number;
  product_id: number;
  variant_id: number | null;
  product_name: string;
  variant_name: string | null;
  quantity: number;
  unit_price: number;
  subtotal: number;
  notes: string | null;
  modifiers: TabItemModifier[];
  toppings: TabItemTopping[];
}

export interface TabItemModifier {
  id: number;
  tab_item_id: number;
  modifier_id: number;
  modifier_name: string;
  price_adjustment: number;
}

export interface TabItemTopping {
  id: number;
  tab_item_id: number;
  topping_id: number;
  topping_name: string;
  quantity: number;
  unit_price: number;
  subtotal: number;
}

export interface Tab {
  id: number;
  outlet_id: number;
  shift_id: number | null;
  table_id: number | null;
  customer_name: string | null;
  guest_count: number;
  status: 'open' | 'active' | 'paid' | 'void' | 'merged';
  opened_at: string;
  closed_at: string | null;
  opened_by: number;
  closed_by: number | null;
  subtotal: number;
  tax_amount: number;
  service_charge_amount: number;
  total_amount: number;
  table: Table | null;
  items: TabItem[];
}

export interface CartItem {
  id: string;
  product_id: number;
  product_name: string;
  variant_id: number | null;
  variant_name: string | null;
  base_price: number;
  quantity: number;
  modifiers: CartItemModifier[];
  toppings: CartItemTopping[];
  notes: string;
  subtotal: number;
}

export interface CartItemModifier {
  id: number;
  name: string;
  price_adjustment: number;
}

export interface CartItemTopping {
  id: number;
  name: string;
  price: number;
  quantity: number;
}

export interface OrderType {
  type: 'dine_in' | 'takeaway';
  table_id: number | null;
  customer_name: string;
}

export interface PaymentMethod {
  type: 'cash' | 'qris' | 'ewallet' | 'card' | 'split';
  amount: number;
  reference?: string;
}

export interface SplitPayment {
  method: string;
  amount: number;
  reference?: string;
}

export interface PaymentData {
  [key: string]: any;
  method: string;
  cash_received?: number;
  change?: number;
  split_payments?: SplitPayment[];
}

export interface QuickOrderPageProps {
  [key: string]: any;
  auth: {
    user: User;
  };
  categories: Category[];
  products: Product[];
  tables: Table[];
  activeTabs: Tab[];
  currentShift: {
    id: number;
    opening_cash: number;
    total_cash_sales: number;
    total_non_cash_sales: number;
    transaction_count: number;
  } | null;
  flash?: {
    message?: string;
    error?: string;
  };
}