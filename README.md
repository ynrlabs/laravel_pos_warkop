# Warkop POS

Point of Sale (POS) and Back-Office Management System designed specifically for Indonesian modern coffee shops ("warung kopi").

Built on a hybrid architecture combining a high-performance, zero-latency client-side frontend for cashier operations with a robust administration panel for inventory, recipe costing, shift controls, and reporting.

---

## Architecture Overview

- **Cashier POS Interface (`/pos`)**:
  - Powered by **Inertia.js**, **Vue 3**, **TypeScript**, and **Pinia**.
  - Local state management eliminates network roundtrips during cart manipulation, item additions, modifier selections, and price calculations.
  - Ergonomic touch targets (48px - 56px) optimized for high-velocity tablet and touchscreen counter environments.
  - Styled with **Tailwind CSS v4** and typed with strict TypeScript definitions.
- **Admin & Back-Office (`/admin`)**:
  - Powered by **FilamentPHP**.
  - Comprehensive resource management: products, categories, recipes, ingredients, purchase orders, stock opname, waste logs, and sales reports.
- **Backend Core**:
  - **Laravel 11** with MySQL 8.0+.
  - Real-time event broadcasting support via **Laravel Reverb** for Kitchen Display System (KDS) stations (Barista and Kitchen).
  - Multi-method payment handling (Cash, Dynamic QRIS, E-Wallet, Card, and Split Bill).

---

## Key Features

### 1. Point of Sale (POS)
- **Order Modes**: Dine In (table allocation) and Take Away.
- **Table & Open Tab Management**:
  - Open Tab tracking for ongoing dine-in orders.
  - **Split Bill**: Split items into a new tab/table with custom quantities.
  - **Merge Tab**: Combine two active customer tabs into one.
  - **Transfer Table**: Relocate active orders to available tables.
- **Product Customization**:
  - Multiple size/type variants with dynamic price adjustments.
  - Single-choice and multiple-choice modifiers (Sugar levels, Ice levels, Spice levels).
  - Paid add-ons and toppings with quantity steppers.
  - Item-level special cooking notes.
- **Payment Processing**:
  - Cash payment with quick-cash suggestion buttons and automatic change calculation.
  - Dynamic QRIS presentation with countdown timer and status checking.
  - Multi-tender Split Payment (combine Cash, QRIS, E-Wallet, and Card in one transaction).
  - Automatic tax (PB1 11%), service charge (5%), and rounding rules (nearest Rp 100).

### 2. Back-Office Management
- **Product & Category Catalog**: Full finished-goods and raw-materials catalog with variants, modifiers, and toppings configuration.
- **Inventory & Recipe Costing**:
  - Multi-unit measurement system (weight, volume, count) with base conversion.
  - Ingredient recipes linked to menu items for automated stock deduction and Cost of Goods Sold (COGS).
  - Stock Opname and Waste Logging.
  - Supplier management and Purchase Orders (PO).
- **Shift & Cash Drawer Management**:
  - Shift opening with initial drawer cash.
  - Real-time tracking of cash sales, non-cash sales, and transaction count.
  - Shift closing summary with actual drawer counting and variance audit logs.
- **Sales & Performance Reporting**: Daily/periodic revenue, payment mix breakdown, and cashier audit logs.

---

## Tech Stack

- **Backend**: Laravel 11, PHP 8.3+
- **Admin Panel**: FilamentPHP v3 / v5
- **POS Frontend**: Vue 3 (Composition API, `<script setup lang="ts">`), TypeScript 6, Inertia.js
- **State Management**: Pinia
- **Styling**: Tailwind CSS v4
- **Icons**: Lucide Vue (`lucide-vue-next`)
- **Database**: MySQL 8.0+
- **Real-time WebSockets**: Laravel Reverb
- **Build Tool**: Vite 8

---

## System Requirements

- PHP 8.3 or higher
- Composer 2.x
- Node.js 18.x or higher and npm
- MySQL 8.0 or MariaDB 10.5+
- PHP Extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`

---

## Installation & Setup

### 1. Clone Repository
```bash
git clone https://github.com/your-username/laravel_pos_warkop.git
cd laravel_pos_warkop
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

Configure your database and app settings in `.env`:
```ini
APP_NAME="Warkop POS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_pos_warkop
DB_USERNAME=root
DB_PASSWORD=
```

Generate the application encryption key:
```bash
php artisan key:generate
```

### 4. Database Migration & Seeding
Run migrations and populate default catalog, outlets, units, roles, permissions, and sample users:
```bash
php artisan migrate --seed
```

### 5. Build Frontend Assets
For local development with hot module replacement:
```bash
npm run dev
```

For production build:
```bash
npm run build
```

To run TypeScript verification across all Vue components and stores:
```bash
npm run typecheck
```

### 6. Run the Application
Start the Laravel local server:
```bash
php artisan serve
```

Access the application:
- **Cashier POS Interface**: [http://localhost:8000/pos](http://localhost:8000/pos)
- **Admin Back-Office**: [http://localhost:8000/admin](http://localhost:8000/admin)

---

## Default Development Accounts

All default seeded accounts use the password: `password123` and PIN: `123456`.

| Role | Email / Username | Default PIN | Access Scope |
| --- | --- | --- | --- |
| Super Admin | `superadmin@warkop.com` | `123456` | Full system access, settings, admin panel, POS |
| Manager | `manager@warkop.com` | `123456` | Store reports, stock opname, shifts, POS |
| Kasir 1 | `kasir1@warkop.com` | `123456` | POS terminal, open tab, shift operations |
| Barista 1 | `barista1@warkop.com` | `123456` | KDS Barista station |
| Koki 1 | `koki1@warkop.com` | `123456` | KDS Kitchen station |
| Gudang 1 | `gudang@warkop.com` | `123456` | Inventory and stock opname |

Note: In local development (`APP_ENV=local`), a one-click credential auto-fill widget is displayed on the login page for quick testing.

---

## Project Structure

```text
├── app/
│   ├── Filament/Admin/         # Filament Admin Panel resources & pages
│   ├── Http/
│   │   ├── Controllers/POS/    # POS Quick Order and checkout controller
│   │   └── Middleware/         # Inertia share data and auth redirection
│   ├── Models/                 # Eloquent models (Order, Product, Shift, Tab, etc.)
│   ├── Providers/Filament/     # Admin panel service provider
│   └── Services/               # Business logic & payment gateways
├── database/
│   ├── migrations/             # Schema definitions
│   └── seeders/                # Default catalog, roles, and sample data
├── resources/
│   ├── css/                    # Tailwind CSS v4 entry
│   ├── js/
│   │   ├── Components/POS/     # Vue 3 POS components (MenuGrid, CartPanel, Modals)
│   │   ├── Pages/POS/          # Inertia pages (QuickOrder.vue)
│   │   ├── Stores/             # Pinia store (cartStore.ts)
│   │   ├── Types/              # TypeScript interfaces (pos.ts)
│   │   └── app.ts              # Inertia client entry point
│   └── views/                  # Blade templates (Inertia root, receipts)
├── routes/
│   ├── web.php                 # POS and webhook routes
│   └── console.php             # Artisan console commands
└── tsconfig.json               # TypeScript compiler configuration
```

---

## Keyboard Shortcuts (POS)

| Key | Action |
| --- | --- |
| `F9` | Open Payment Modal |
| `Esc` | Close any active modal dialog |

---

## License

This project is open-source software licensed under the [MIT License](LICENSE).
