---
name: warkop-pos-development
description: Project context, design guidelines, tech stack details, and database references for developing the Warkop POS application (Laravel 11 + FilamentPHP 3 + Laravel Reverb). Use when building or modifying features in the Warkop POS project.
---

# Warkop POS Development Skill

This skill contains the guidelines, design tokens, and technical context for the **Warkop POS** application. Follow these instructions when creating, editing, or testing features in this codebase.

---

## 🎯 Project Overview
Warkop POS is a Point of Sale system specifically tailored for Indonesian coffee shops ("warung kopi"). 
- **Core Characteristics**: High-velocity/low-ticket transactions, highly customizable orders, dine-in with Open Tab support, dual production lines (Barista & Kitchen), and real-time KDS.
- **Tech Stack**: Laravel 11, FilamentPHP 3, Laravel Reverb (WebSocket), MySQL 8.0+.

---

## 🎨 Design Tokens & System

Refer to the design requirements below for any custom styling or UI modifications:

### Color Palette (Warkop Vibes)
- **Primary (Kopi Tua)**: `--primary-500: #8B6B3D;` (Main Brand), `--primary-900: #2F2212;`
- **Secondary (Susu/Cream)**: `--secondary-500: #D4B87A;` (Accent)
- **Status Colors**: 
  - Success: `#2E7D32` (Stock safe, order ready)
  - Warning: `#F57F17` (Low stock, order pending > 5 mins)
  - Danger: `#C62828` (Out of stock, order pending > 10 mins)
  - Info: `#1565C0` (Info, payment pending)
- **Neutral**: Warm cream background (`#F5F0EB`), surface color (`#FFFFFF`).
- **Dark Mode (Malam)**: Background (`#1A1612`), surface (`#241E18`), primary accent (`#D4B87A`).

### Typography
- **UI Font**: `Inter` or system sans-serif.
- **Mono Font (for numbers/timers)**: `JetBrains Mono` or monospace.

### Touch Targets (POS Crucial)
- **Minimum**: `48px`
- **Comfortable**: `56px` (for kasir/barista fast-paced inputs)
- **Large Action**: `64px`

---

## 🛠 Database & Architecture
The database is built on MySQL 8.0+.
Key tables include:
- `outlets`: Outlets configuration, footer receipt configuration.
- `users`: Staff details (role: `super_admin`, `manager`, `kasir`, `barista`, `koki`, `gudang`).
- `roles` & `permissions`: Spatie Permission compatible.

Refer to the full database schema design in [database_schema_warkop_pos.sql](file:///d:/laragon/www/laravel_pos_warkop/docs/database_schema_warkop_pos.sql).

---

## 📝 Key Reference Documents
Always consult these resources when working on POS screens, KDS, payment integration, or inventory recipe structures:
- **Product Requirements Document (PRD)**: [PRD_Warkop_POS.md](file:///d:/laragon/www/laravel_pos_warkop/docs/PRD_Warkop_POS.md)
- **Stitch Design Prompt Guide**: [STITCH_PROMPT_Warkop_POS.md](file:///d:/laragon/www/laravel_pos_warkop/docs/STITCH_PROMPT_Warkop_POS.md)
- **Database Schema**: [database_schema_warkop_pos.sql](file:///d:/laragon/www/laravel_pos_warkop/docs/database_schema_warkop_pos.sql)

---

## 🚀 Development Guidelines

1. **Keep it Fast**: Target touch inputs for tablets. Optimize database queries for high transaction volumes.
2. **Real-time KDS**: Use Laravel Reverb broadcast events on channels `kds.barista`, `kds.kitchen`, and `kds.all`.
3. **Receipt Generation**: Thermal print receipts are ESC/POS compliant (58mm/80mm size options). Use standard Blade templates for thermal outputs.
