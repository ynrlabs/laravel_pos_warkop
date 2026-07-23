# Stitch.withgoogle.com Prompt
## Warkop POS - UI/UX Design Generation

**Tujuan:** Generate desain UI/UX untuk aplikasi POS Warung Kopi (Warkop) menggunakan Laravel + FilamentPHP 3, dengan fokus pada **Quick Order**, **KDS (Kitchen Display System)**, **Payment**, dan **Inventory Management**.

---

## 🎯 Project Context

```
Nama Aplikasi: Warkop POS
Tagline: "Nongkrong Nyaman, Transaksi Cepat"
Target User: Warung Kopi Indonesia (Warkop) - UKM skala kecil-menengah
Device Utama: Tablet 10-13 inch (Kasir), Tablet 10-15 inch (KDS Barista/Dapur), Thermal Printer 58mm/80mm
Tech Stack: Laravel 11 + FilamentPHP 3 + Laravel Reverb (WebSocket) + MySQL
```

---

## 🎨 Design System Requirements

### Color Palette (Warkop Vibes)
```css
/* Primary - Kopi Tua */
--primary-50: #F5F0EB;
--primary-100: #E8DCCC;
--primary-200: #D4C4A8;
--primary-300: #BFA885;
--primary-400: #A88B5E;
--primary-500: #8B6B3D;      /* MAIN BRAND */
--primary-600: #735631;
--primary-700: #5C4326;
--primary-800: #46331C;
--primary-900: #2F2212;

/* Secondary - Susu/Cream */
--secondary-50: #FDFBF7;
--secondary-100: #FAF5EB;
--secondary-200: #F4EBD7;
--secondary-300: #EEDFC3;
--secondary-400: #E8D1A8;
--secondary-500: #D4B87A;     /* ACCENT */

/* Status Colors */
--success: #2E7D32;           /* Stok aman, order ready */
--warning: #F57F17;           /* Stok menipis, order > 5 menit */
--danger: #C62828;            /* Stok habis, order > 10 menit, error */
--info: #1565C0;              /* Info, payment pending */

/* Neutral */
--surface: #FFFFFF;
--surface-variant: #FAFAFA;
--background: #F5F0EB;        /* Warm cream background */
--on-surface: #2F2212;
--on-surface-variant: #5C4326;
--outline: #D4C4A8;
--divider: #E8DCCC;

/* Dark Mode (Malam) */
--dark-bg: #1A1612;
--dark-surface: #241E18;
--dark-primary: #D4B87A;
```

### Typography
```css
/* Font Family: Inter (UI) + JetBrains Mono (Numbers/Timer) */
--font-ui: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
--font-mono: 'JetBrains Mono', 'Fira Code', monospace;
--font-display: 'Plus Jakarta Sans', sans-serif;

/* Scale */
--text-xs: 0.75rem;    /* 12px - Labels, badges */
--text-sm: 0.875rem;   /* 14px - Body small, modifier text */
--text-base: 1rem;     /* 16px - Body default */
--text-lg: 1.125rem;   /* 18px - Product name, table header */
--text-xl: 1.25rem;    /* 20px - Price, total */
--text-2xl: 1.5rem;    /* 24px - Section title */
--text-3xl: 2rem;      /* 32px - Page title */
--text-4xl: 3rem;      /* 48px - KDS Timer, Queue Number */
--text-5xl: 4rem;      /* 64px - Hero number */

/* Weight */
--font-normal: 400;
--font-medium: 500;
--font-semibold: 600;
--font-bold: 700;
```

### Spacing & Radius
```css
--space-1: 4px;
--space-2: 8px;
--space-3: 12px;
--space-4: 16px;
--space-5: 20px;
--space-6: 24px;
--space-8: 32px;
--space-10: 40px;
--space-12: 48px;

--radius-sm: 6px;
--radius-md: 10px;
--radius-lg: 16px;
--radius-xl: 24px;
--radius-full: 9999px;

--shadow-sm: 0 1px 2px rgba(47,34,18,0.05);
--shadow-md: 0 4px 12px rgba(47,34,18,0.08);
--shadow-lg: 0 8px 24px rgba(47,34,18,0.12);
--shadow-xl: 0 16px 40px rgba(47,34,18,0.15);
```

### Touch Targets (Critical for POS)
```css
--touch-min: 48px;        /* Minimum touch target */
--touch-comfortable: 56px; /* Comfortable for fat fingers */
--touch-large: 64px;      /* Primary actions */
```

---

## 📱 Screen Specifications

### 1. POS - Quick Order Screen (Kasir) - **MOST CRITICAL**

#### Layout Structure (Tablet Landscape 1280x800)
```
┌─────────────────────────────────────────────────────────────────────────────┐
│ TOP BAR (56px)                                                              │
│ [← Back]  Warkop POS - Shift Pagi  [👤 Kasir]  [⚙]  [🔔]  [🌙]  [🔌 Online] │
├──────────────────┬──────────────────────────────────────┬───────────────────┤
│                  │                                      │                   │
│  LEFT PANEL      │         CENTER - MENU GRID           │  RIGHT PANEL      │
│  (320px)         │         (Flex 1)                     │  (400px)          │
│                  │                                      │                   │
│  ┌────────────┐  │  ┌──────────────────────────────┐   │  ┌──────────────┐ │
│  │ KATEGORI   │  │  │  [Kopi] [Non-Kopi] [Makanan] │   │  │ KERANJANG    │ │
│  │ [Tabs]     │  │  │  [Camilan] [Indomie] [Rokok] │   │  │ (Order List) │ │
│  ├────────────┤  │  ├──────────────────────────────┤   │  ├──────────────┤ │
│  │            │  │  │                              │   │  │              │ │
│  │ QUICK      │  │  │   PRODUCT CARD GRID          │   │  │  [Meja 5]    │ │
│  │ ACTIONS    │  │  │                              │   │  │  A012        │ │
│  │ [F1] Kopi  │  │  │  ┌─────┐ ┌─────┐ ┌─────┐    │   │  ├──────────────┤ │
│  │ [F2] Non   │  │  │  │ ☕  │ │ 🧋 │ │ 🍔  │    │   │  │ 2x Kopi Susu │ │
│  │ [F3] Makan │  │  │  │Kopi │ │Thai │ │Nasi │    │   │  │   Gula Normal│ │
│  │ [F4] Cemil │  │  │  │Susu │ │Tea  │ │Goreng│   │   │  │   Es Extra   │ │
│  │ [F5] Indom │  │  │  │15k  │ │12k  │ │18k  │    │   │  │   + Boba     │ │
│  │ [F6] Rokok │  │  │  └─────┘ └─────┘ └─────┘    │   │  │  1x Es Teh   │ │
│  │            │  │  │                              │   │  │   Less Sugar │ │
│  ├────────────┤  │  │   [Load more / Infinite]     │   │  ├──────────────┤ │
│  │ MODE       │  │  │                              │   │  │ Subtotal:    │ │
│  │ [🍽 Dine-In]│  │  │                              │   │  │   Rp 42.000  │ │
│  │ [📦 Take]   │  │  │                              │   │  │ Diskon:      │ │
│  │ [🚗 Drive]  │  │  │                              │   │  │   -Rp 0      │ │
│  │             │  │  │                              │   │  │ Pajak (11%): │ │
│  │ MEJA/ANTRIAN│  │  │                              │   │  │   Rp 4.620   │ │
│  │ [Meja 1-50] │  │  │                              │   │  ├──────────────┤ │
│  │ [Antrian+]  │  │  │                              │   │  │ TOTAL:       │ │
│  │ [QR Meja]   │  │  │                              │   │  │   Rp 46.620  │ │
│  └────────────┘  │  │                              │   │  ├──────────────┤ │
│                  │  │                              │   │  │ [BAYAR]      │ │
│                  │  │                              │   │  │ [SIMPAN TAB] │ │
│                  │  │                              │   │  │ [BATAL]      │ │
│                  │  │                              │   │  └──────────────┘ │
└──────────────────┴──────────────────────────────────────┴───────────────────┘
```

#### Product Card Component
```
┌─────────────────────────────────────┐
│           PRODUCT CARD (160x180)    │
├─────────────────────────────────────┤
│                                     │
│        [IMAGE 120x120]              │
│        (rounded-lg, object-cover)   │
│                                     │
├─────────────────────────────────────┤
│  Kopi Susu          🔥 Best Seller  │  ← Name + Badge
│  Rp 15.000                          │  ← Price (mono font)
│  ●●● Stok: 45                       │  ← Stock indicator (dots)
│  ████████░░ 80%                     │  ← Progress bar (optional)
└─────────────────────────────────────┘

States:
- Default: border-outline, bg-surface
- Hover/Active: border-primary-300, bg-primary-50, shadow-md
- Pressed: scale-98, bg-primary-100
- Out of Stock: opacity-50, grayscale, "HABIS" badge
- Low Stock (≤10): border-warning, warning dot pulse
```

#### Modifier Modal (Bottom Sheet - Mobile/Tablet)
```
┌─────────────────────────────────────────────────────────────────┐
│  DRAG HANDLE                                                     │
├─────────────────────────────────────────────────────────────────┤
│  Kopi Susu                    [✕]                               │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌── VARIAN WAJIB (Single Select) ─────────────────────────┐   │
│  │  ○ Kopi Susu Panas      Rp 15.000                        │   │
│  │  ● Kopi Susu Dingin     Rp 16.000     ← SELECTED        │   │
│  │  ○ Kopi Susu Blended    Rp 18.000                        │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌── LEVEL GULA (Required, Radio) ─────────────────────────┐   │
│  │  ○ Less Sugar      (0%)                                  │   │
│  │  ● Normal          (100%)     ← DEFAULT                  │   │
│  │  ○ Extra Sugar     (150%)    +Rp 1.000                   │   │
│  │  ○ Custom          (input %)     +Rp 1.000               │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌── ES (Required, Radio) ──────────────────────────────────┐   │
│  │  ● Normal Ice                                               │   │
│  │  ○ Extra Ice                                                │   │
│  │  ○ Less Ice                                                 │   │
│  │  ○ No Ice          (Hanya Panas)                           │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌── TOPPING (Multi Select, Qty) ───────────────────────────┐   │
│  │  ☑ Boba              +Rp 3.000   Qty: [−] 1 [+]          │   │
│  │  ☐ Jelly             +Rp 2.000   Qty: [−] 0 [+]          │   │
│  │  ☐ Extra Shot        +Rp 4.000   Qty: [−] 0 [+]          │   │
│  │  ☐ Whipped Cream     +Rp 2.000   Qty: [−] 0 [+]          │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌── CATATAN KHUSUS ────────────────────────────────────────┐   │
│  │  [Textarea: "Jangan terlalu manis, es sedikit aja"]      │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ────────────────────────────────────────────────────────────   │
│  ESTIMASI: Rp 19.000                              [BATAL] [OK] │
│                                    (Primary, touch-large)       │
└─────────────────────────────────────────────────────────────────┘
```

#### Cart Item Row (Right Panel)
```
┌────────────────────────────────────────────────────────────┐
│  2×  Kopi Susu (Dingin)                    Rp 32.000       │
│      Gula: Normal  |  Es: Extra Ice                        │
│      + Boba (1x)  +Rp 3.000                                │
│      ──────────────────────────────────────────────────    │
│      [−]  Qty: 2  [+]      [🗑 Hapus]  [✏ Edit]           │
└────────────────────────────────────────────────────────────┘
```

---

### 2. KDS - Kitchen Display System (Barista & Dapur)

#### Layout (Tablet Landscape - Full Screen Kiosk Mode)
```
┌─────────────────────────────────────────────────────────────────────────────┐
│ HEADER (64px) - STICKY                                                      │
│ ┌─────────────────────┐                    ┌─────────────────────────────┐  │
│ │  ☕ BARISTA STATION  │   🔴 LIVE · 3/5    │  🔍 Filter: [Semua ▼]       │  │
│ │  [Barista] [Dapur]  │   Pending: 2       │  [☕ Minuman] [🍔 Makanan]   │  │
│ └─────────────────────┘   Making: 1        │  [⏱ >5menit] [🔊 Sound: On] │  │
│                          Ready: 1           └─────────────────────────────┘  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ORDER TICKET CARDS (Vertical Scroll, Auto-scroll to new)                  │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  [MEJA 5]  A012  12:34:12  ⏱ 03:24  🔴 PENDING (Baru)            │   │
│  │  ─────────────────────────────────────────────────────────────────  │   │
│  │  2×  KOPI SUSU DINGIN                                               │   │
│  │      Gula: Normal    |    Es: Extra Ice                             │   │
│  │      + Boba (2x)     + Jelly (1x)                                   │   │
│  │  ─────────────────────────────────────────────────────────────────  │   │
│  │  1×  ESPRESSO                                                       │   │
│  │      No Sugar    |    No Ice                                        │   │
│  │  ─────────────────────────────────────────────────────────────────  │   │
│  │  [🟡 MULAI]                    [⏭ SKIP]   [🖨 PRINT]               │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  [MEJA 2]  A010  12:32:45  ⏱ 05:12  🟡 SEDANG DIBUAT             │   │
│  │  ─────────────────────────────────────────────────────────────────  │   │
│  │  1×  AMERICANO                                                      │   │
│  │      No Sugar    |    Extra Ice                                     │   │
│  │  ─────────────────────────────────────────────────────────────────  │   │
│  │  [✅ SELESAI]                  [⏭ SKIP]   [🖨 PRINT]               │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  [MEJA 8]  A008  12:30:10  ⏱ 12:45  🟢 READY TO SERVE            │   │
│  │  ─────────────────────────────────────────────────────────────────  │   │
│  │  1×  KOPI HITAM                                                     │   │
│  │      Less Sugar   |    Normal Ice                                   │   │
│  │  ─────────────────────────────────────────────────────────────────  │   │
│  │  [✅ SAJIKAN]                  [🖨 PRINT]                          │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
├─────────────────────────────────────────────────────────────────────────────┤
│ FOOTER BAR (56px) - STICKY                                                  │
│ [⏸ PAUSE]  [📊 STATS]  [🖨 REPRINT]  [⚙ SETTINGS]  [🔊 TEST SOUND]        │
└─────────────────────────────────────────────────────────────────────────────┘
```

#### KDS Ticket Card States
```css
/* PENDING - Baru masuk */
.ticket-pending {
  border-left: 4px solid var(--danger);
  background: linear-gradient(90deg, #FEF2F2 0%, #FFF 50%);
  animation: pulse-border 2s infinite;
}

/* MAKING - Sedang dibuat */
.ticket-making {
  border-left: 4px solid var(--warning);
  background: linear-gradient(90deg, #FFFBEB 0%, #FFF 50%);
}

/* READY - Siap saji */
.ticket-ready {
  border-left: 4px solid var(--success);
  background: linear-gradient(90deg, #F0FDF4 0%, #FFF 50%);
}

/* Timer Color Coding */
.timer-normal { color: var(--on-surface); }
.timer-warning { color: var(--warning); font-weight: 600; }  /* > 5 min */
.timer-danger { color: var(--danger); font-weight: 700; animation: blink 1s infinite; } /* > 10 min */
```

#### Bump Bar Navigation (Accessibility)
```
KEY MAPPING (USB Bump Bar / Keyboard):
├── F1-F6          → Switch Station / Filter Category
├── Arrow Up/Down  → Navigate Tickets
├── Enter          → Start/Complete Selected Item
├── Space          → Toggle Item Done
├── Escape         → Skip / Back
├── Ctrl+P         → Print Current Ticket
├── Ctrl+R         → Reprint Last
└── Alt+P          → Pause/Resume Station
```

---

### 3. Payment Screen (Modal/Full Screen)

#### Payment Flow
```
┌─────────────────────────────────────────────────────────────────┐
│  PEMBAYARAN                                                    │
│  ────────────────────────────────────────────────────────────  │
│  TOTAL: Rp 46.620                                              │
│                                                                 │
│  ┌─ METODE PEMBAYARAN ──────────────────────────────────────┐  │
│  │  ○ Tunai (Cash)                                          │  │
│  │  ● QRIS Dynamic (Midtrans)                               │  │
│  │  ○ QRIS Static                                           │  │
│  │  ○ E-Wallet (GoPay/ShopeePay/Dana/OVO)                  │  │
│  │  ○ Split Bayar                                           │  │
│  │  ○ Hutang / Piutang                                      │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌─ CASH CALCULATOR (Visible when Tunai selected) ──────────┐  │
│  │  Diterima:  [____________]  ← Numpad besar                │  │
│  │  ──────────────────────────────────────────────────────   │  │
│  │  Kembalian:  Rp 3.380      (Auto-calc, big font mono)    │  │
│  │  [100k] [50k] [20k] [10k] [5k] [1k] [500] [100] [Kembali]│  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌─ QRIS DYNAMIC (Visible when QRIS selected) ─────────────┐  │
│  │  [QR CODE CANVAS - 280x280]                              │  │
│  │  Scan untuk bayar Rp 46.620                              │  │
│  │  ⏳ Menunggu pembayaran... 04:59                         │  │
│  │  [BATAL] [CEK STATUS]                                    │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ────────────────────────────────────────────────────────────  │
│  [KEMBALI]                                    [BAYAR / SELESAI] │
└─────────────────────────────────────────────────────────────────┘
```

---

### 4. Shift Open/Close (Buka/Tutup Kasir)

#### Buka Kasir (Shift Start)
```
┌─────────────────────────────────────────────────────────────────┐
│  BUKA SHIFT - PAGI (06:00 - 14:00)                              │
│  ────────────────────────────────────────────────────────────  │
│  Kasir: Budi Santoso    |    Tanggal: 22 Juli 2026             │
│                                                                 │
│  MODAL AWAL (Denominasi Wajib)                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  Pecahan    │ Jumlah │ Subtotal    │ Quick Buttons        │  │
│  ├─────────────┼────────┼─────────────┼──────────────────────┤  │
│  │  Rp 100.000 │   [2]  │ Rp 200.000  │ [+1] [+5] [+10] [Maks]│  │
│  │  Rp 50.000  │   [4]  │ Rp 200.000  │ [+1] [+5] [+10] [Maks]│  │
│  │  Rp 20.000  │   [5]  │ Rp 100.000  │ [+1] [+5] [+10] [Maks]│  │
│  │  Rp 10.000  │   [3]  │ Rp 30.000   │ [+1] [+5] [+10] [Maks]│  │
│  │  Rp 5.000   │   [2]  │ Rp 10.000   │ [+1] [+5] [+10] [Maks]│  │
│  │  Rp 2.000   │   [5]  │ Rp 10.000   │ [+1] [+5] [+10] [Maks]│  │
│  │  Rp 1.000   │  [10]  │ Rp 10.000   │ [+1] [+5] [+10] [Maks]│  │
│  │  Koin 500   │  [20]  │ Rp 10.000   │ [+1] [+5] [+10] [Maks]│  │
│  │  Koin 200   │  [10]  │ Rp 2.000    │ [+1] [+5] [+10] [Maks]│  │
│  │  Koin 100   │  [20]  │ Rp 2.000    │ [+1] [+5] [+10] [Maks]│  │
│  ├─────────────┼────────┼─────────────┤                      │  │
│  │  TOTAL      │        │ Rp 564.000  │                      │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  Catatan: [Textarea: "Shift pagi normal"]                            │
│                                                                 │
│  ────────────────────────────────────────────────────────────  │
│  [BATAL]                                    [MULAI SHIFT]      │
└─────────────────────────────────────────────────────────────────┘
```

#### Tutup Kasir (Shift End - Reconciliation)
```
┌─────────────────────────────────────────────────────────────────┐
│  TUTUP SHIFT - PAGI                                             │
│  ────────────────────────────────────────────────────────────  │
│  Kasir: Budi Santoso    |    Shift: 06:00 - 14:00              │
│                                                                 │
│  ┌─ RINGKASAN SISTEM ───────────────────────────────────────┐  │
│  │  Modal Awal:           Rp 564.000                        │  │
│  │  Penjualan Tunai:      Rp 2.450.000                      │  │
│  │  Penjualan Non-Tunai:  Rp 1.200.000                      │  │
│  │  Refund/Void:         -Rp 25.000                         │  │
│  │  ─────────────────────────────────────────────────────   │  │
│  │  EXPECTED CASH:       Rp 3.189.000        ← Sistem        │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌─ HITUNG FISIK (Denominasi) ──────────────────────────────┐  │
│  │  Pecahan    │ Jumlah │ Subtotal    │ Quick Buttons        │  │
│  ├─────────────┼────────┼─────────────┼──────────────────────┤  │
│  │  Rp 100.000 │   [31] │ Rp 3.100.000│ [+1] [+5] [+10] [Maks]│  │
│  │  Rp 50.000  │   [ 1] │ Rp   50.000 │ [+1] [+5] [+10] [Maks]│  │
│  │  Rp 20.000  │   [ 2] │ Rp   40.000 │ [+1] [+5] [+10] [Maks]│  │
│  │  ...        │        │             │                      │  │
│  ├─────────────┼────────┼─────────────┤                      │  │
│  │  FISIK CASH:              Rp 3.190.000                     │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌─ SELISIH ────────────────────────────────────────────────┐  │
│  │  Sistem:  Rp 3.189.000                                    │  │
│  │  Fisik:   Rp 3.190.000                                    │  │
│  │  ────────────────────────────                             │  │
│  │  SELISIH: +Rp 1.000  🟢 LEBIH                            │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  Catatan Wajib (jika selisih ≠ 0):                            │
│  [_______________________________________________________]    │
│  "Koin 500 tambah 2 pcs dari drop box"                         │
│                                                                 │
│  ────────────────────────────────────────────────────────────  │
│  [SIMPAN DRAFT]                              [TUTUP SHIFT]     │
└─────────────────────────────────────────────────────────────────┘
```

---

### 5. Inventory - Recipe/BOM Management

#### Recipe Editor (Admin)
```
┌─────────────────────────────────────────────────────────────────┐
│  RESEP: Kopi Susu (1 Porsi)                    HPP: Rp 4.850   │
│  Margin: 67.7%  |  Harga Jual: Rp 15.000                       │
│  ────────────────────────────────────────────────────────────  │
│                                                                 │
│  ┌─ BAHAN BAKU ─────────────────────────────────────────────┐  │
│  │  [+ TAMBAH BAHAN]                                         │  │
│  ├──────────────────────────────────────────────────────────┤  │
│  │  Bahan Baku           │ Qty  │ Satuan │ Harga/Sat │ Subtotal│  │
│  ├───────────────────────┼──────┼────────┼───────────┼────────┤  │
│  │  ● Biji Kopi Arabika  │ 18   │ gram   │ Rp 120/g  │ Rp 2.160│  │
│  │  ● Susu UHT Full Cream│ 150  │ ml     │ Rp 8.500/L│ Rp 1.275│  │
│  │  ● Gula Pasir         │ 15   │ gram   │ Rp 14.000/kg│Rp 210 │  │
│  │  ● Cup 12oz + Tutup   │ 1    │ set    │ Rp 850/pcs│ Rp 850  │  │
│  │  ○ Es Batu (Optional) │ 100  │ gram   │ Rp 5.000/kg│ Rp 500  │  │
│  ├───────────────────────┼──────┼────────┼───────────┼────────┤  │
│  │  TOTAL HPP                                           │ Rp 4.495│  │
│  │  + Overhead 5% (Listrik, Gas, Air)                   │ Rp 225  │  │
│  │  ───────────────────────────────────────────────────   │  │
│  │  HPP FINAL                                             │ Rp 4.720│  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ⚠️  Peringatan: Margin 67.7% di atas target 60% ✓            │
│                                                                 │
│  [BATAL]                                    [SIMPAN RESEP]     │
└─────────────────────────────────────────────────────────────────┘
```

---

### 6. Reports Dashboard (Owner/Manager)

#### Real-time Dashboard Widgets
```
┌─────────────────────────────────────────────────────────────────────────────┐
│  DASHBOARD OWNER - REAL TIME                                    [🔄] [⚙]   │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐      │
│  │ OMZET HARI INI│ │ TRANSAKSI    │ │ AVG TICKET   │ │ MEJA AKTIF   │      │
│  │ Rp 12.4M      │ │ 147 transaksi│ │ Rp 84.354    │ │ 8/20 meja    │      │
│  │ ▲ +12% vs kmrn│ │ ▲ +8%        │ │ ▼ -3%        │ │ 40% occupancy│      │
│  └──────────────┘ └──────────────┘ └──────────────┘ └──────────────┘      │
│                                                                             │
│  ┌─────────────────────────────────┐ ┌─────────────────────────────────┐  │
│  │ TOP 5 TERLARIS (7 HARI)         │ │ PAYMENT MIX (HARI INI)          │  │
│  │ 1. Kopi Susu      234 pcs █████ │ │ ████████████ Cash 52%           │  │
│  │ 2. Es Teh Manis   189 pcs ████  │ │ ████████ QRIS 28%               │  │
│  │ 3. Nasi Goreng    156 pcs ███   │ │ ██████ E-Wallet 15%             │  │
│  │ 4. Indomie Goreng 134 pcs ███   │ │ ██ Hutang 5%                    │  │
│  │ 5. Americano      98 pcs  ██    │ └─────────────────────────────────┘  │
│  └─────────────────────────────────┘                                     │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐  │
│  │ PROFIT & LOSS REAL-TIME (BULAN INI)                                  │  │
│  │ Revenue: Rp 342M  |  COGS: Rp 118M  |  Gross Profit: Rp 224M (65%)  │  │
│  │ Opex: Rp 89M      |  Net Profit: Rp 135M (39%)                      │  │
│  └─────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐  │
│  │ STOK KRITIS (Perlu Restock)                                          │  │
│  │ 🔴 Biji Kopi Robusta: 2.1kg (Min: 5kg)  → [BUAT PO]                │  │
│  │ 🟡 Susu UHT: 12 liter (Min: 20L)       → [BUAT PO]                │  │
│  │ 🟢 Gula Pasir: 25kg (Min: 10kg)        OK                           │  │
│  └─────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 🔧 Technical Implementation Notes for Stitch

### FilamentPHP 3 Specific Components

#### 1. POS Panel (Custom Filament Page)
```php
// app/Filament/Pages/POS.php
// - Custom Livewire component for POS
// - Alpine.js for modifier modal, cart state
// - Web Bluetooth API for thermal printing
// - Keyboard shortcuts via @keydown
```

#### 2. KDS Panel (Filament Page + Reverb)
```php
// app/Filament/Pages/KDS/Barista.php
// app/Filament/Pages/KDS/Kitchen.php
// - Listen to Reverb channels
// - Real-time ticket updates via Laravel Echo
// - IndexedDB for offline queue
// - Web Audio API for sound alerts
```

#### 3. Resources (Filament Resources)
```php
// ProductResource - dengan Repeater untuk Modifier Groups
// ModifierGroupResource - nested modifiers
// IngredientResource - dengan Unit conversion
// RecipeResource - BOM builder dengan calculated HPP
// ShiftResource - Open/Close dengan denomination input
// OrderResource - read-only dengan print action
```

### Responsive Breakpoints
```css
/* Tablet POS (Landscape) */
@media (min-width: 1024px) and (orientation: landscape) { ... }

/* Tablet KDS (Landscape) */
@media (min-width: 1024px) and (orientation: landscape) { ... }

/* Mobile fallback (Emergency) */
@media (max-width: 768px) { ... }

/* Thermal Printer (58mm/80mm) */
@media print and (max-width: 80mm) { ... }
```

### Accessibility (WCAG 2.1 AA)
- Semantic HTML5
- ARIA labels untuk modifier modal, KDS tickets
- Focus visible states (ring-2 ring-primary-500)
- Color contrast ratio ≥ 4.5:1
- Touch target ≥ 48x48px
- Screen reader announcements untuk order baru, status change
- Keyboard navigable semua flow

### Performance Hints
- Virtual scrolling untuk menu grid (react-window style)
- Lazy load modifier config (load on demand)
- Debounced search/filter (300ms)
- Optimistic UI untuk cart actions
- Service Worker untuk offline POS
- Image optimization: WebP, responsive sizes, blur placeholder

---

## 📋 Deliverables Checklist untuk Stitch

### High Priority (Generate First)
- [ ] **POS Quick Order Screen** - Full layout dengan grid menu, cart, modifier modal
- [ ] **KDS Barista Screen** - Ticket cards, status flow, timer, controls
- [ ] **KDS Kitchen Screen** - Same as barista, different filter
- [ ] **Payment Modal** - Cash calculator, QRIS display, split payment
- [ ] **Shift Open/Close** - Denomination input, reconciliation view

### Medium Priority
- [ ] **Product/Modifier Management** - Admin forms dengan repeater
- [ ] **Recipe/BOM Editor** - Ingredient picker, HPP calculator
- [ ] **Reports Dashboard** - Widgets, charts, critical stock
- [ ] **Thermal Print Templates** - Struk pelanggan, tiket dapur, tiket antrian

### Low Priority (Nice to Have)
- [ ] **Customer Display** - Simple order view untuk meja
- [ ] **Settings Panels** - Printer config, tax, rounding, integrasi
- [ ] **Login/Shift Select** - Kasir login dengan PIN
- [ ] **Offline Indicator** - Sync status, pending queue count

---

## 🎨 Style Guidelines untuk Stitch Output

### Component Naming (Filament Compatible)
```blade
{{-- Resources/views/filament/pages/pos.blade.php --}}
<x-filament::page>
    <x-slot name="heading">Quick Order</x-slot>
    
    <div class="pos-layout h-screen flex overflow-hidden">
        {{-- Left: Categories & Quick Actions --}}
        <aside class="w-80 bg-surface border-r border-divider flex flex-col">
            <x-filament-tabs :tabs="$categories" wire:model="activeCategory" />
            <x-pos-quick-actions />
            <x-pos-table-queue />
        </aside>
        
        {{-- Center: Menu Grid --}}
        <main class="flex-1 overflow-y-auto p-4">
            <x-filament-tabs :tabs="$categoryTabs" wire:model="activeSubCategory" />
            <x-pos-menu-grid :products="$products" wire:model="cart" />
        </main>
        
        {{-- Right: Cart & Payment --}}
        <aside class="w-[400px] bg-surface-variant border-l border-divider flex flex-col">
            <x-pos-cart-header :table="$currentTable" :queue="$currentQueue" />
            <x-pos-cart-items :items="$cart" wire:model="cart" />
            <x-pos-cart-totals :items="$cart" />
            <x-pos-cart-actions />
        </aside>
    </div>
</x-filament::page>
```

### State Management Pattern (Alpine.js + Livewire)
```javascript
// POS Alpine Component
Alpine.data('pos', () => ({
    cart: [],
    activeCategory: 'kopi',
    currentTable: null,
    currentQueue: null,
    modifierModal: { open: false, product: null, variant: null },
    
    addToCart(product) {
        if (product.hasModifiers) {
            this.openModifier(product);
        } else {
            this.cart.push({ ...product, qty: 1, modifiers: [], toppings: [] });
            this.$dispatch('cart-updated');
        }
    },
    
    // Keyboard shortcuts
    @keydown.f1.prevent="activeCategory = 'kopi'",
    @keydown.f2.prevent="activeCategory = 'non-kopi'",
    @keydown.enter.prevent="confirmOrder()",
    @keydown.escape.prevent="closeModals()",
}))
```

---

## 📝 Prompt untuk Stitch.withgoogle.com

> **Copy-paste prompt berikut ke Stitch:**
> 
> ```
> Buatkan desain UI/UX lengkap untuk aplikasi POS Warung Kopi (Warkop) bernama "Warkop POS" dengan tech stack Laravel 11 + FilamentPHP 3.
> 
> KARAKTERISTIK UNIK WARKOP:
> - High velocity, low ticket (transaksi cepat, nilai kecil)
> - Customization heavy: setiap menu punya banyak varian (gula, es, topping, level pedas)
> - Dine-in dominant: Open Tab / Simpan Bil (bayar di akhir)
> - Dual production line: Barista (minuman) & Dapur (makanan) butuh KDS real-time
> - Thermal printing 58mm/80mm via Bluetooth/USB
> 
> SCREEN YANG DIBUTUHKAN (Priority Order):
> 
> 1. POS QUICK ORDER (KASIR) - TABLET LANDSCAPE 1280x800
>    - Left: Kategori tabs + Quick actions (F1-F6) + Meja/Antrian
>    - Center: Product grid (responsive, touch-friendly, stock indicator)
>    - Right: Cart/Order list dengan modifier display, totals, payment actions
>    - Modifier Modal (Bottom Sheet): Varian wajib (radio), Modifier group (radio/checkbox), Topping (multi-select + qty), Catatan
>    - Keyboard shortcuts: F1-F6 kategori, Enter konfirmasi, Esc batal, Space toggle dine-in
> 
> 2. KDS BARISTA - TABLET LANDSCAPE FULL SCREEN KIOSK
>    - Header: Station tabs, Live indicator, Filter dropdown
>    - Ticket cards vertical scroll: Meja/Antrian, Timer, Status badge (Pending/Making/Ready), Items dengan modifier
>    - Action buttons per ticket: Mulai/Selesai/Skip/Print
>    - Color coding: Merah baru, Kuning >5 menit, Hijau ready
>    - Bump bar navigation support (keyboard accessible)
>    - Sound alerts untuk order baru & ready
> 
> 3. KDS DAPUR - SAME LAYOUT, FILTER MAKANAN
> 
> 4. PAYMENT SCREEN (MODAL/FULL)
>    - Payment method selector: Cash, QRIS Dynamic, QRIS Static, E-Wallet, Split, Hutang
>    - Cash Calculator: Numpad besar, quick buttons (100k, 50k, 20k), auto kembalian
>    - QRIS Dynamic: Canvas QR code, countdown timer, polling status
>    - Split payment: Multiple methods per transaction
> 
> 5. SHIFT OPEN/CLOSE (BUKA/TUTUP KASIR)
>    - Buka: Denomination input wajib (100k, 50k, 20k, 10k, 5k, 2k, 1k, koin) dengan quick buttons
>    - Tutup: Sistem vs Fisik reconciliation, selisih highlight, catatan wajib jika beda
>    - Print shift report thermal
> 
> 6. RECIPE/BOM EDITOR (ADMIN)
>    - Ingredient picker dengan unit conversion (kg→gram, L→ml)
>    - Auto HPP calculation dengan moving average cost
>    - Margin indicator dengan warning jika < target
> 
> 7. DASHBOARD OWNER
>    - Real-time widgets: Omzet, Transaksi, Avg Ticket, Meja Aktif
>    - Top selling chart, Payment mix donut, P&L summary
>    - Stok kritis dengan quick PO action
> 
> DESIGN SYSTEM:
> - Warm coffee palette: Primary #8B6B3D (kopi tua), Secondary #D4B87A (susu/cream)
> - Background cream #F5F0EB, Dark mode untuk malam
> - Font: Inter (UI) + JetBrains Mono (numbers/timer) + Plus Jakarta Sans (display)
> - Touch targets minimal 48px, comfortable 56px
> - FilamentPHP 3 compatible components (x-filament::)
> - WCAG 2.1 AA accessible
> - Responsive: Tablet landscape primary, mobile fallback
> 
> OUTPUT: Generate Figma-ready components dengan proper naming, states (hover, active, disabled, loading), dan responsive variants.
> ```

---

## 🔗 References & Inspiration

### POS Reference UI
- **Toast POS** - Quick service layout
- **Square POS** - Modifier bottom sheet
- **Lightspeed Restaurant** - Table management
- **Odoo POS** - Keyboard shortcuts

### KDS Reference UI
- **Toast KDS** - Ticket card design
- **Square KDS** - Station routing
- **FreshKDS** - Timer & color coding
- **TouchBistro KDS** - Bump bar support

### Thermal Print References
- **ESC/POS Print Templates** - 58mm/80mm layouts
- **Star Micronics CloudPRNT** - Web-based printing
- **Epson ePOS SDK** - Browser printing patterns

---

**Versi:** 1.0  
**Tanggal:** 22 Juli 2026  
**Dibuat untuk:** Stitch.withgoogle.com Generation  
**Project:** Warkop POS - Laravel + FilamentPHP 3