# Product Requirements Document (PRD)

## Warkop POS - Point of Sale System untuk Warung Kopi

**Versi:** 1.0
**Tanggal:** 22 Juli 2026
**Status:** Draft
**Teknologi Stack:** Laravel 11 + FilamentPHP 3 + Laravel Reverb (WebSocket) + MySQL/PostgreSQL

name database: db_pos_warkop

---

## 1. Executive Summary

### 1.1 Latar Belakang

Warung kopi (warkop) memiliki karakteristik operasional yang unik dan berbeda dari retail/toko biasa:

- **High velocity, low ticket** - Transaksi cepat, nilai kecil, volume tinggi
- **Customization heavy** - Setiap menu memiliki banyak varian (gula, es, topping, level pedas)
- **Dine-in dominant** - Pelanggan nongkrong lama, bayar di akhir (Open Tab)
- **Dual production line** - Barista (minuman) & Dapur (makanan) butuh koordinasi real-time
- **Thermal printing** - Struk 58mm/80mm via Bluetooth/USB

### 1.2 Tujuan Produk

Membangun POS warkop yang **cepat, andal, dan mudah dipakai** oleh kasir (non-teknis) dengan fokus pada:

1. **Quick Order** - Input pesanan < 10 detik per item
2. **Real-time KDS** - Dapur/Barista terima order instan via WebSocket
3. **Flexible Payment** - Cash, QRIS Dynamic, E-Wallet, Split Bill
4. **Inventory Accuracy** - Auto-deduction bahan baku (Recipe/BDM)
5. **Shift Management** - Buka/Tutup kasir dengan rekonsiliasi fisik

### 1.3 Success Metrics

| Metric                                 | Target    |
| -------------------------------------- | --------- |
| Waktu input 1 item (dengan modifier)   | < 8 detik |
| Latency order ke KDS (WebSocket)       | < 500ms   |
| Uptime sistem (operasional 16 jam)     | 99.9%     |
| Akurasi stok bahan baku (sample audit) | > 98%     |
| Waktu tutup kasir (reconciliation)     | < 3 menit |

### 1.4 Design System & Visual Guidelines

Sistem UI/UX dirancang khusus untuk lingkungan Warkop dengan tingkat kepadatan informasi tinggi dan kontras yang jelas.
- **Dokumentasi Design System**: [DESIGN.md](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/warkop_pos_design_system/DESIGN.md)
- **Desain Warna & Layout**: Didominasi oleh warna hangat bertema kopi (*Deep Coffee* & *Creamy Milk*) untuk kenyamanan mata kasir dan barista dalam shift panjang.

---

## 2. User Personas & Roles

| Persona                     | Peran                                | Device                      | Kebutuhan Utama                                    |
| --------------------------- | ------------------------------------ | --------------------------- | -------------------------------------------------- |
| **Kasir (Front)**     | Menerima order, kasir, print struk   | Tablet/PC + Thermal Printer | UI cepat, kalkulator kembalian, open tab           |
| **Barista**           | Membuat minuman                      | Tablet di bar (KDS)         | Filter kategori minuman, status real-time          |
| **Koki/Dapur**        | Membuat makanan                      | Tablet di dapur (KDS)       | Filter kategori makanan, timer order               |
| **Owner/Manager**     | Monitoring, laporan, stok            | Laptop/HP                   | Dashboard real-time, laporan profit, stok kritis   |
| **Admin/Super Admin** | Setup menu, user, printer, integrasi | Laptop                      | CRUD menu/modifier, manajemen user, config printer |

---

## 3. Functional Requirements

### 3.1 Modul 1: Transaksi Kasir (Quick Order) - **PRIORITAS TINGGI**

#### 3.1.1 Grid Menu Visual

- **Layout:** Grid 2-4 kolom (responsive), kategori sebagai tab horizontal
- **Kategori Default:** Kopi, Non-Kopi, Makanan Berat, Camilan, Indomie, Rokok
- **Kustomisasi:** Admin bisa add/edit/delete kategori & urutan
- **Produk Card:** Thumbnail, nama, harga, indikator stok (hijau/kuning/merah), badge "Best Seller"

#### 3.1.2 Varian & Modifier System (Hierarchical)

```
Produk (Parent)
├── Varian Wajib (Single Select) - Contoh: Indomie Goreng/Rebus
│   └── Harga beda per varian
├── Modifier Group (Multi Select) - Contoh: Level Pedas, Tambah Telur
│   ├── Modifier Item (checkbox/radio) - Contoh: Pedas Level 1/2/3
│   │   └── Harga tambahan (bisa 0)
│   └── Aturan: Required/Optional, Max Selection
└── Topping/Extra (Multi Select dengan qty) - Contoh: Boba, Jelly, Extra Shot
    └── Harga per porsi
```

**Contoh Implementasi:**

| Produk    | Varian Wajib | Modifier Group                                       | Topping                 |
| --------- | ------------ | ---------------------------------------------------- | ----------------------- |
| Kopi Susu | -            | Gula (Less/Normal/Extra), Es (Normal/Extra/Less Ice) | Boba (+3k), Jelly (+2k) |
| Indomie   | Goreng/Rebus | Pedas (1-5), Telur (Mata Sapi/Dadar/Setengah Matang) | Keju (+3k), Sosis (+4k) |
| Es Teh    | -            | Gula, Es                                             | -                       |
| Rokok     | Varian Merk  | -                                                    | -                       |

#### 3.1.3 Open Tab / Simpan Bil (Dine-in Flow)

- **Buat Tab:** Input nama pelanggan / nomor meja / kode antrian (QR Code meja optional)
- **Tambah ke Tab:** Order masuk ke tab yang sama tanpa print struk
- **Lihat Tab Aktif:** List semua tab dengan total, waktu buka, item count
- **Split/Join Tab:** Bisa pecah 1 tab jadi 2, atau gabung 2 tab
- **Transfer Tab:** Pindah meja (update nomor meja)
- **Bayar Tab:** Pilih tab → pilih payment method → print struk final
- **Auto-save:** Setiap perubahan auto-save ke DB (draft order)

#### 3.1.4 Nomor Meja / Kode Antrian

- **Mode Meja:** Nomor meja fisik (1-50), custom label (VIP 1, Teras 2)
- **Mode Antrian:** Running number per shift (A001, A002...), print tiket antrian thermal
- **Display:** Tampil di KDS + Customer Display (opsional)
- **Call System:** Tombol "Panggil" di kasir → suara/notif di customer display

#### 3.1.5 Quick Actions (Keyboard/Shortcut Friendly)

- `F1-F12` / `Ctrl+1-9` → Kategori cepat
- `Enter` → Konfirmasi item + buka modifier
- `Esc` → Batal / tutup modal
- `Space` → Toggle dine-in/takeaway
- `Ctrl+P` → Print ulang struk terakhir
- `Ctrl+S` → Simpan tab (Open Bill)

#### 3.1.6 Rancangan UI/UX (Mockup & Prototype)
Untuk visualisasi dan purwarupa interaktif dari Modul Transaksi Kasir, silakan merujuk pada rancangan berikut:

##### A. POS Quick Order (Layar Utama Kasir)
- Layar utama transaksi kasir dengan visualisasi grid kategori, pencarian menu, dan keranjang belanja.
- [Folder UI](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/pos_quick_order_warkop_pos) | [Interactive Prototype (HTML)](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/pos_quick_order_warkop_pos/code.html)
- Preview Desain:
  ![POS Quick Order Screen](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/pos_quick_order_warkop_pos/screen.png)

##### B. Table Management (Manajemen Meja Dine-in)
- Tata letak meja visual warkop untuk memantau status dan durasi pesanan aktif (*Open Tab*).
- [Folder UI](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/table_management_warkop_pos) | [Interactive Prototype (HTML)](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/table_management_warkop_pos/code.html)
- Preview Desain:
  ![Table Management Screen](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/table_management_warkop_pos/screen.png)

##### C. Self Order E-Menu (Pesanan Mandiri)
- Layar antarmuka ramah pelanggan untuk melakukan pemesanan mandiri lewat tablet di meja.
- [Folder UI](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/self_order_e_menu_warkop_pos) | [Interactive Prototype (HTML)](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/self_order_e_menu_warkop_pos/code.html)
- Preview Desain:
  ![Self Order E-Menu](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/self_order_e_menu_warkop_pos/screen.png)

##### D. Customer Queue Display (Layar Antrian Pelanggan)
- Tampilan display antrian untuk dipasang di TV/Monitor agar pelanggan tahu pesanan yang siap diambil.
- [Folder UI](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/customer_queue_display_warkop_pos) | [Interactive Prototype (HTML)](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/customer_queue_display_warkop_pos/code.html)
- Preview Desain:
  ![Customer Queue Display](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/customer_queue_display_warkop_pos/screen.png)

##### E. Quick Lock Mode & Login Screen
- Fitur penguncian cepat layar kasir untuk mengamankan POS saat kasir meninggalkan meja.
- [Folder UI Quick Lock](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/quick_lock_mode_warkop_pos) | [Interactive Prototype Quick Lock (HTML)](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/quick_lock_mode_warkop_pos/code.html)
- Preview Desain Quick Lock:
  ![Quick Lock Mode](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/quick_lock_mode_warkop_pos/screen.png)
- [Folder UI Login Screen](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/pos_login_lock_screen_warkop_pos) | [Interactive Prototype Login Screen (HTML)](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/pos_login_lock_screen_warkop_pos/code.html)
- Preview Desain Login Screen:
  ![POS Login Lock Screen](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/pos_login_lock_screen_warkop_pos/screen.png)

---

### 3.2 Modul 2: Kitchen Display System (KDS) - **PRIORITAS TINGGI**

#### 3.2.1 Arsitektur Real-time

- **Laravel Reverb** (WebSocket server) - Self-hosted, no Pusher cost
- **Channel per Station:** `kds.barista`, `kds.kitchen`, `kds.all`
- **Event:** `OrderCreated`, `OrderItemStatusChanged`, `OrderCancelled`
- **Fallback:** Polling 5 detik jika WebSocket disconnect

#### 3.2.2 Tampilan KDS (Tablet Landscape)

```
┌─────────────────────────────────────────────────────────────┐
│  BARISTA STATION          🔴 Live    🔍 Filter: [Semua ▼]   │
├─────────────────────────────────────────────────────────────┤
│  [Meja 5]  A012  12:34:12  ●●● PENDING (3 menit)           │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ 2x Kopi Susu      [Normal Sugar] [Extra Ice]       │   │
│  │       + Boba (+3k)  + Jelly (+2k)                  │   │
│  │  1x Es Teh Manis  [Less Sugar] [Normal Ice]        │   │
│  └─────────────────────────────────────────────────────┘   │
│  [🟡 Mulai]  [✅ Selesai]  [⏭ Skip]                        │
├─────────────────────────────────────────────────────────────┤
│  [Meja 2]  A010  12:32:45  🟡 SEDANG DIBUAT (1 menit)      │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ 1x Americano      [No Sugar] [Extra Ice]           │   │
│  └─────────────────────────────────────────────────────┘   │
│  [✅ Selesai]  [⏭ Skip]                                    │
└─────────────────────────────────────────────────────────────┘
```

#### 3.2.3 Fitur KDS

- **Auto-routing:** Item minuman → Barista, Item makanan → Dapur, Campuran → Keduanya
- **Status Flow:** `Pending` → `Sedang Dibuat` → `Ready to Serve` → `Served`
- **Timer:** Hitung mundur dari order masuk, highlight > 5 menit (kuning), > 10 menit (merah)
- **Sound Alert:** Suara notif baru (configurable), suara "Ready" beda per station
- **Bump Bar Support:** Hardware bump bar (USB) untuk navigasi tanpa touch
- **Offline Queue:** Simpan lokal (IndexedDB) jika disconnect, sync saat reconnect
- **Multi-station:** 1 tablet bisa handle 2 station (Barista + Dessert) dengan tab

#### 3.2.4 KDS Admin Controls

- **Pause Station:** Hentikan terima order baru (misal: kebakaran, habis bahan)
- **Force Complete:** Admin paksa status "Ready" jika stuck
- **Reprint Ticket:** Cetak ulang tiket dapur (thermal 58mm)
- **Analytics:** Avg prep time per item, bottleneck detection

#### 3.2.5 Rancangan UI/UX (Mockup & Prototype)
Untuk visualisasi antarmuka Kitchen Display System (KDS), silakan merujuk pada rancangan berikut:

##### KDS Barista & Kitchen Screen
- Layar pemantauan pesanan real-time untuk barista dan dapur dengan filter stasiun, penanda keterlambatan waktu, dan bump control.
- [Folder UI](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/kds_barista_warkop_pos) | [Interactive Prototype (HTML)](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/kds_barista_warkop_pos/code.html)
- Preview Desain:
  ![KDS Barista Screen](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/kds_barista_warkop_pos/screen.png)

---

### 3.3 Modul 3: Pembayaran & Cetak Struk - **PRIORITAS TINGGI**

#### 3.3.1 Payment Methods

| Method                                           | Detail                                                                              | Integrasi             |
| ------------------------------------------------ | ----------------------------------------------------------------------------------- | --------------------- |
| **Cash**                                   | Kalkulator cepat (nominal uang diterima → hitung kembalian), pembulatan (rounding) | Built-in              |
| **QRIS Static**                            | Gambar QRIS print di meja/laci, kasir input nominal manual                          | Built-in              |
| **QRIS Dynamic (Midtrans/Xendit)**         | Generate QR per transaksi, callback/webhook verifikasi, expired 5 menit             | Midtrans / Xendit API |
| **E-Wallet (GoPay, ShopeePay, Dana, OVO)** | Via Midtrans/Xendit, deeplink app jika mobile                                       | Midtrans / Xendit API |
| **Debit/Credit Card**                      | EDC terintegrasi (opsional, via Midtrans EDC)                                       | Midtrans EDC          |
| **Split Payment**                          | Bayar sebagian cash, sebagian QRIS, dll                                             | Built-in              |
| **Hutang / Piutang**                       | Catat nama pelanggan, jatuh tempo, reminder                                         | Built-in              |

#### 3.3.2 Thermal Printing (ESC/POS)

- **Printer Support:** Bluetooth (ESC/POS), USB (Raw/ESC/POS), Network (IP:Port)
- **Paper Size:** 58mm (384 dots), 80mm (576 dots) - auto-detect
- **Print Jobs:**
  - **Struk Pelanggan:** Header, item + modifier, subtotal, diskon, pajak, total, payment detail, footer (promo/IG)
  - **Struk Dapur/KDS:** Hanya item & modifier (tanpa harga), nomor meja, waktu
  - **Tiket Antrian:** Nomor antrian besar, estimasi waktu
  - **Laporan Shift:** Ringkasan penjualan, payment breakdown, void/refund
- **Queue System:** Print queue di browser (Web Bluetooth/USB) + fallback server-side (PHP ESC/POS via CUPS/Printer Share)
- **Template:** Blade-based template, editable di admin panel

#### 3.3.3 Diskon & Promo

- **Diskon Item:** Persen / Nominal, butuh approval manager (PIN)
- **Diskon Total:** Voucher kode, member card, happy hour (time-based)
- **Buy X Get Y:** Konfigurasi di admin (contoh: Beli 5 gratis 1)
- **Combo/Paket:** Harga spesial untuk bundling (Nasi + Ayam + Es Teh)

#### 3.3.4 Rancangan UI/UX (Mockup & Prototype)
Untuk visualisasi proses pembayaran dan penanganan piutang warkop, silakan merujuk pada rancangan berikut:

##### A. Payment Screen (Layar Pembayaran & Kembalian)
- Fitur kalkulator kembalian cepat, pembayaran split, e-wallet, dan QRIS dinamis.
- [Folder UI](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/payment_screen_warkop_pos) | [Interactive Prototype (HTML)](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/payment_screen_warkop_pos/code.html)
- Preview Desain:
  ![Payment Screen](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/payment_screen_warkop_pos/screen.png)

##### B. Customer Ledger (Buku Hutang / Piutang Pelanggan)
- Antarmuka pencatatan hutang pelanggan warkop, status jatuh tempo, riwayat transaksi, dan kalkulator cicilan piutang.
- [Folder UI](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/customer_ledger_warkop_pos) | [Interactive Prototype (HTML)](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/customer_ledger_warkop_pos/code.html)
- Preview Desain:
  ![Customer Ledger Screen](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/customer_ledger_warkop_pos/screen.png)

---

### 3.4 Modul 4: Manajemen Stok & Bahan Baku (Recipe / BOM) - **PRIORITAS SEDANG**

#### 3.4.1 Dua Tipe Stok

| Tipe                                   | Contoh                                                                                | Pengurangan                      |
| -------------------------------------- | ------------------------------------------------------------------------------------- | -------------------------------- |
| **Produk Jadi (Finished Goods)** | Rokok, Minuman Kemasan, Kopi Saset, Snack                                             | Langsung saat terjual (qty -1)   |
| **Bahan Baku (Raw Materials)**   | Biji Kopi (kg), Susu UHT (liter), Gula (kg), Indomie (pcs), Telur (pcs), Bumbu (gram) | Via Recipe/BOM saat menu terjual |

#### 3.4.2 Recipe / Bill of Materials (BOM)

```
Menu: Kopi Susu (1 porsi)
├── Biji Kopi Arabika     : 18 gram    (stok kg)
├── Susu UHT Full Cream   : 150 ml     (stok liter)
├── Gula Pasir            : 15 gram    (stok kg)
├── Es Batu               : 100 gram   (stok kg - optional tracking)
└── Cup 12oz + Tutup      : 1 set      (stok pcs)
```

#### 3.4.3 Fitur Stok

- **Auto Deduction:** Saat order status = "Paid" atau "Served" (configurable), kurangi stok bahan baku
- **Wastage/Write-off:** Input kerusakan/hilang (expired, jatuh, dll) dengan alasan & foto optional
- **Stock Opname:** Fisik vs Sistem, adjustment dengan audit trail
- **Purchase Order (PO):** Buat PO ke supplier, terima barang → update stok + harga rata-rata (moving average)
- **Low Stock Alert:** Notifikasi (WA/Email/In-app) saat stok < minimum threshold
- **Stock Transfer:** Antar cabang (jika multi-outlet nanti)
- **Unit Conversion:** Beli kg, pakai gram → konversi otomatis

#### 3.4.4 Cost of Goods Sold (COGS) & Margin

- **HPP per Menu:** Hitung otomatis dari recipe × harga bahan baku (moving average)
- **Margin Real-time:** Tampil di admin panel: Harga Jual - HPP = Margin
- **Alert Margin:** Jika margin < target (misal 40%), warning di menu management

#### 3.4.5 Rancangan UI/UX (Mockup & Prototype)
Untuk visualisasi pengelolaan stok bahan baku, resep/BOM, dan pembelian supplier, silakan merujuk pada rancangan berikut:

##### Inventory Operations Screen
- Modul administrasi stok, detail supplier, histori pergerakan stok, dan stock opname terintegrasi.
- [Folder UI](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/inventory_operations_warkop_pos) | [Interactive Prototype (HTML)](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/inventory_operations_warkop_pos/code.html)
- Preview Desain:
  ![Inventory Operations Screen](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/inventory_operations_warkop_pos/screen.png)

---

### 3.5 Modul 5: Laporan & Kas Operasional - **PRIORITAS SEDANG**

#### 3.5.1 Shift Management (Buka/Tutup Kasir)

```
SHIFT PAGI (06:00 - 14:00)          SHIFT MALAM (14:00 - 22:00)
┌─────────────────────────────┐     ┌─────────────────────────────┐
│ BUKA KASIR                  │     │ BUKA KASIR                  │
│ Modal Awal: Rp 500.000      │     │ Modal Awal: Rp 500.000      │
│   - 100k x 2                │     │   - 100k x 2                │
│   - 50k x 4                 │     │   - 50k x 4                 │
│   - 20k x 5                 │     │   - 20k x 5                 │
│   - Koin: 50k               │     │   - Koin: 50k               │
└─────────────────────────────┘     └─────────────────────────────┘
         ↓                                    ↓
    TRANSAKSI...                         TRANSAKSI...
         ↓                                    ↓
┌─────────────────────────────┐     ┌─────────────────────────────┐
│ TUTUP KASIR                 │     │ TUTUP KASIR                 │
│ Sistem: Rp 3.450.000        │     │ Sistem: Rp 4.120.000        │
│ Fisik:  Rp 3.448.500        │     │ Fisik:  Rp 4.125.000        │
│ Selisih: -Rp 1.500 (Kurang) │     │ Selisih: +Rp 5.000 (Lebih)  │
│ Catatan: "Koin 500 hilang"  │     │ Catatan: "Tukar 100k"       │
└─────────────────────────────┘     └─────────────────────────────┘
```

#### 3.5.2 Laporan Penjualan

| Laporan                     | Dimensi               | Metric                                           |
| --------------------------- | --------------------- | ------------------------------------------------ |
| **Omzet Harian**      | Tanggal, Shift, Kasir | Gross, Net, Discount, Tax, Payment Breakdown     |
| **Top Selling**       | Periode, Kategori     | Qty, Revenue, Margin, Rank                       |
| **Slow Moving**       | Periode               | Item < 5 porsi/hari, stok tinggi                 |
| **Profit & Loss**     | Harian/Bulanan        | Revenue, COGS, Gross Profit, Opex, Net Profit    |
| **Payment Mix**       | Periode               | % Cash, % QRIS, % E-Wallet, % Hutang             |
| **Kasir Performance** | Per Kasir             | Transaksi/jam, Avg ticket, Void rate, Error rate |
| **Meja Turnover**     | Per Meja              | Avg durasi, Revenue/meja, Occupancy rate         |

#### 3.5.3 Export & Scheduler

- **Format:** PDF (thermal & A4), Excel, CSV
- **Auto-email:** Kirim laporan harian ke owner jam 23:00
- **WhatsApp Gateway:** Kirim ringkasan ke grup owner (opsional, via Fonnte/Wablas)

#### 3.5.4 Rancangan UI/UX (Mockup & Prototype)
Untuk visualisasi laporan penutupan shift kasir dan pengelolaan kas operasional, silakan merujuk pada rancangan berikut:

##### A. Shift Closing (Rekonsiliasi Kasir & Tutup Shift)
- Formulir denominasi fisik, validasi selisih kas otomatis, dan penutupan sesi shift kasir.
- [Folder UI](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/shift_closing_warkop_pos) | [Interactive Prototype (HTML)](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/shift_closing_warkop_pos/code.html)
- Preview Desain:
  ![Shift Closing Screen](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/shift_closing_warkop_pos/screen.png)

##### B. Petty Cash (Buku Kas Kecil Operasional)
- Pencatatan pengeluaran operasional kecil (es batu tambahan, air mineral darurat, bumbu dapur) untuk menjaga keakuratan kas harian.
- [Folder UI](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/petty_cash_warkop_pos) | [Interactive Prototype (HTML)](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/petty_cash_warkop_pos/code.html)
- Preview Desain:
  ![Petty Cash Screen](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/petty_cash_warkop_pos/screen.png)

---

### 3.6 Modul Pendukung (Supporting Modules)

#### 3.6.1 User & Role Management (Filament Shield / Spatie Permission)

| Role        | Permission Utama                              |
| ----------- | --------------------------------------------- |
| Super Admin | Full access, setup printer, integrasi, backup |
| Manager     | Laporan, stok, user kasir, diskon approval    |
| Kasir       | POS, Open Tab, Payment, Print struk           |
| Barista     | KDS Barista only                              |
| Koki        | KDS Kitchen only                              |
| Gudang      | Stok, PO, Receive, Opname                     |

#### 3.6.2 Audit Trail (Activity Log)

- Semua aksi kritis: Void, Refund, Discount > 10%, Stok adjustment, Harga ubah, User login/logout
- Filter: User, Tanggal, Action, Resource
- Retention: 2 tahun

#### 3.6.3 Settings & Konfigurasi

- **Outlet Info:** Nama, alamat, NPWP, logo, footer struk
- **Pajak:** PPN 11%, PPh 22% (configurable), service charge %
- **Rounding:** Pembulatan ke 100 / 500 / 1000
- **Printer:** Multiple printer mapping (Kasir, Dapur, Barista, Antrian)
- **Integrasi:** Midtrans/Xendit keys, WA Gateway, Google Maps (delivery nanti)

#### 3.6.4 Rancangan UI/UX (Mockup & Prototype)
Untuk visualisasi halaman login dan pengaturan POS pendukung, silakan merujuk pada rancangan berikut:

##### POS Login & Lock Screen
- Layar otentikasi kasir/manager, input nomor PIN cepat, dan transisi pemulihan session yang aman.
- [Folder UI](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/pos_login_lock_screen_warkop_pos) | [Interactive Prototype (HTML)](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/pos_login_lock_screen_warkop_pos/code.html)
- Preview Desain:
  ![POS Login Lock Screen](file:///d:/laragon/www/laravel_pos_warkop/stitch_warkop_pos_ui_design/pos_login_lock_screen_warkop_pos/screen.png)

---

## 4. Non-Functional Requirements

### 4.1 Performance

| Requirement                     | Target     |
| ------------------------------- | ---------- |
| POS Load Time (First Paint)     | < 2 detik  |
| Add Item to Order (UI response) | < 100ms    |
| WebSocket Latency (Local LAN)   | < 100ms    |
| Print Struk (Thermal)           | < 3 detik  |
| Report Generation (1 bulan)     | < 10 detik |
| Concurrent Users (Kasir + KDS)  | 20+        |

### 4.2 Reliability

- **Offline Mode:** POS bisa jalan tanpa internet (local SQLite/IndexedDB), sync saat online
- **Auto-reconnect WebSocket:** Exponential backoff (1s, 2s, 4s, 8s, max 30s)
- **Database Backup:** Daily auto backup ke S3/MinIO/GDrive
- **Printer Failover:** Jika printer utama error, auto switch ke backup printer

### 4.3 Security

- **Auth:** Laravel Sanctum (SPA) + 2FA untuk Manager/Admin
- **PIN Kasir:** 4-6 digit untuk aksi sensitif (void, refund, diskon, buka laci)
- **API Rate Limit:** 60 req/min untuk endpoint publik
- **Data Encryption:** Field sensitif (API key, password) encrypted di DB
- **CORS:** Hanya domain outlet yang diizinkan

### 4.4 Usability

- **Touch Friendly:** Minimal 48x48px touch target, swipe gestures
- **High Contrast Mode:** Untuk pencahayaan redup warkop malam
- **Bahasa:** Indonesia (primary), English (secondary)
- **Keyboard Shortcut:** Semua aksi utama bisa via keyboard
- **Sound Feedback:** Beep sukses, error sound beda

---

## 5. Technical Architecture

### 5.1 Stack Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────────────┐  │
│  │  POS UI  │  │ KDS Bar  │  │ KDS Kit  │  │ Customer Display│  │
│  │ (Filament│  │ (Vue/    │  │ (Vue/    │  │  (Vue/React)   │  │
│  │  Panel)  │  │  Livewire)│  │ Livewire)│  │                │  │
│  └──────────┘  └──────────┘  └──────────┘  └────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              │
                    ┌─────────▼─────────┐
                    │  Laravel Reverb   │  (WebSocket Server)
                    │  (Port 8080/443)  │
                    └─────────┬─────────┘
                              │
┌─────────────────────────────────────────────────────────────────┐
│                      APPLICATION LAYER                           │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │              Laravel 11 + FilamentPHP 3                   │  │
│  │  • POS Panel (Filament)  • Admin Panel (Filament)        │  │
│  │  • KDS Broadcast Events  • API Resources                  │  │
│  │  • Jobs/Queues (Redis)   • Scheduled Commands             │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              │
                    ┌─────────▼─────────┐
                    │     DATABASE      │
                    │  MySQL 8 / PG 15  │
                    └───────────────────┘
                              │
                    ┌─────────▼─────────┐
                    │   EXTERNAL APIs   │
                    │  Midtrans / Xendit│
                    │  WA Gateway       │
                    │  Printer (CUPS)   │
                    └───────────────────┘
```

### 5.2 Database Schema (Key Tables)

```sql
-- Core
outlets, users, roles, permissions, shifts, shift_cash_details

-- Menu & Modifier
categories, products, product_variants, modifier_groups, modifiers, 
product_modifier_groups (pivot), product_toppings (pivot)

-- Transaction
orders, order_items, order_item_modifiers, order_item_toppings,
payments, payment_details, tabs, tab_items

-- Inventory
ingredients, units, product_recipes (BOM), stock_movements, 
purchase_orders, po_items, stock_opnames, stock_opname_items

-- KDS
kds_stations, kds_tickets, kds_ticket_items

-- Audit & Settings
activity_logs, settings, printers, tax_rates, rounding_rules
```

### 5.3 Key Packages

| Package                        | Fungsi                         |
| ------------------------------ | ------------------------------ |
| `filament/filament`          | Admin & POS Panel              |
| `beyondcode/laravel-reverb`  | WebSocket Server               |
| `midtrans/midtrans-php`      | Payment Gateway                |
| `xendit/xendit-php`          | Payment Gateway Alt            |
| `mike42/escpos-php`          | Thermal Printing (Server-side) |
| `spatie/laravel-permission`  | RBAC                           |
| `spatie/laravel-activitylog` | Audit Trail                    |
| `laravel/sanctum`            | API Auth                       |
| `predis/predis`              | Redis Queue/Cache              |
| `barryvdh/laravel-dompdf`    | PDF Report                     |
| `maatwebsite/excel`          | Excel Export                   |

---

## 6. Implementation Phases

### Phase 1: Foundation (Minggu 1-2) - **MVP Core**

- [ ] Laravel 11 + Filament 3 setup
- [ ] User, Role, Permission, Outlet
- [ ] Category, Product, Variant (basic)
- [ ] POS UI: Grid menu, add to cart, basic modifier
- [ ] Order → Payment (Cash only) → Print struk (ESC/POS)
- [ ] Shift Open/Close basic
- [ ] Database migration & seeder

### Phase 2: Modifier & Open Tab (Minggu 3-4)

- [ ] Modifier Group & Topping system (hierarchical)
- [ ] Open Tab / Simpan Bil (Create, Add, List, Pay, Split, Transfer)
- [ ] Nomor Meja & Kode Antrian
- [ ] Keyboard shortcuts & touch optimization
- [ ] Thermal print template (Blade-based)

### Phase 3: KDS Real-time (Minggu 5-6)

- [ ] Laravel Reverb setup + SSL
- [ ] KDS Vue/Livewire component
- [ ] WebSocket Events: OrderCreated, StatusChanged
- [ ] Station routing (Barista vs Kitchen)
- [ ] Status flow: Pending → Making → Ready → Served
- [ ] Timer, Sound, Offline queue (IndexedDB)
- [ ] Bump bar support (optional)

### Phase 4: Payment & Multi-print (Minggu 7-8)

- [ ] QRIS Dynamic (Midtrans/Xendit) + Webhook
- [ ] E-Wallet (GoPay, ShopeePay, dll)
- [ ] Split Payment
- [ ] Multi-printer mapping (Kasir, Dapur, Barista, Antrian)
- [ ] Web Bluetooth / USB Printing (client-side)
- [ ] Hutang / Piutang

### Phase 5: Inventory & Recipe (Minggu 9-10)

- [ ] Ingredients & Units (with conversion)
- [ ] Recipe/BOM per product/variant
- [ ] Auto deduction on paid/served
- [ ] Stock Movement log
- [ ] Purchase Order & Receive
- [ ] Stock Opname
- [ ] Low Stock Alert
- [ ] COGS & Margin calculation

### Phase 6: Reports & Polish (Minggu 11-12)

- [ ] Dashboard Real-time (Filament Widgets)
- [ ] Laporan: Omzet, Top Selling, Profit, Payment Mix, Kasir Perf
- [ ] Shift Report dengan Reconciliation
- [ ] Export PDF/Excel, Auto-email scheduler
- [ ] Audit Trail & Activity Log
- [ ] Settings Panel (Pajak, Rounding, Printer, Integrasi)
- [ ] UAT & Bug fixing
- [ ] Documentation & Training Video

---

## 7. Risks & Mitigation

| Risk                                          | Probabilitas | Dampak | Mitigasi                                                    |
| --------------------------------------------- | ------------ | ------ | ----------------------------------------------------------- |
| WebSocket disconnect di LAN tidak stabil      | Tinggi       | Tinggi | Fallback polling, offline queue, reconnect logic            |
| Thermal printer compatibility (berbagai merk) | Tinggi       | Sedang | Test 3+ merk, dual mode (Web Bluetooth + Server CUPS)       |
| Modifier complexity bikin UI lambat           | Sedang       | Tinggi | Lazy load modifier, virtual scrolling, cache config         |
| Stok bahan baku tidak akurat (human error)    | Tinggi       | Tinggi | Wajib PO receive, opname bulanan, waste log wajib foto      |
| Midtrans/Xendit webhook timeout               | Sedang       | Tinggi | Idempotency key, manual verify button, retry job            |
| Shift reconciliation selisih besar            | Sedang       | Tinggi | Enforce denomination input, CCTV integration (future)       |
| Data loss saat offline                        | Rendah       | Kritis | Local DB (SQLite/IndexedDB), auto-sync, conflict resolution |

---

## 8. Success Criteria (Definition of Done)

### Launch Criteria (Go-Live Minimum)

- [ ] POS bisa transaksi < 10 detik/item dengan modifier
- [ ] KDS terima order real-time < 1 detik (LAN)
- [ ] Cash + QRIS Dynamic + E-Wallet jalan 100%
- [ ] Print struk thermal 58mm/80mm tanpa error
- [ ] Shift open/close dengan reconciliation akurat
- [ ] Auto-deduction stok bahan baku jalan
- [ ] Laporan harian otomatis ke email owner
- [ ] Offline mode: POS jalan 30 menit tanpa internet, sync lancar
- [ ] Load test: 20 concurrent users (5 kasir, 5 KDS, 10 customer display) stabil

### Post-Launch (30 Hari)

- [ ] Uptime > 99.5%
- [ ] Stok opname selisih < 2%
- [ ] Zero data loss incident
- [ ] Kasir training completion 100%
- [ ] Feature request backlog diprioritaskan

---

## 9. Appendix

### 9.1 Glossary

| Istilah                  | Definisi                                          |
| ------------------------ | ------------------------------------------------- |
| **Open Tab**       | Pesanan dibuka, bayar nanti (dine-in)             |
| **KDS**            | Kitchen Display System - layar dapur/barista      |
| **BOM/Recipe**     | Bill of Materials - komposisi bahan baku per menu |
| **HPP**            | Harga Pokok Penjualan (COGS)                      |
| **Reverb**         | Laravel WebSocket server (replacement Pusher)     |
| **ESC/POS**        | Command set standar printer thermal               |
| **Moving Average** | Metode harga rata-rata stok untuk HPP             |
| **Bump Bar**       | Keyboard khusus KDS (navigasi tanpa touch)        |

### 9.2 Referensi Teknis

- [FilamentPHP 3 Docs](https://filamentphp.com/docs/3.x)
- [Laravel Reverb Docs](https://reverb.laravel.com)
- [Midtrans API](https://docs.midtrans.com)
- [Xendit API](https://developers.xendit.co)
- [ESC/POS Commands](https://reference.epson-biz.com/modules/ref_escpos/)
- [Web Bluetooth API](https://developer.mozilla.org/en-US/docs/Web/API/Web_Bluetooth_API)

---

**Dokumen ini bersifat living document. Update versi dengan changelog di setiap phase completion.**

---

*Prepared by: Development Team*
*Approved by: [Owner/Manager Name]*
*Date: 22 Juli 2026*
