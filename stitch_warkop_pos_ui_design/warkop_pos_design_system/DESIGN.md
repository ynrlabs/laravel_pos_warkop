---
name: Warkop POS Design System
colors:
  surface: '#fff8f4'
  surface-dim: '#eed6bd'
  surface-bright: '#fff8f4'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#fff1e5'
  surface-container: '#ffebd6'
  surface-container-high: '#fde4cb'
  surface-container-highest: '#f7dfc5'
  on-surface: '#25190a'
  on-surface-variant: '#4e453b'
  inverse-surface: '#3c2e1d'
  inverse-on-surface: '#ffeede'
  outline: '#807569'
  outline-variant: '#d2c4b6'
  surface-tint: '#77592d'
  primary: '#705327'
  on-primary: '#ffffff'
  primary-container: '#8b6b3d'
  on-primary-container: '#fff4ea'
  inverse-primary: '#e8c08b'
  secondary: '#715c27'
  on-secondary: '#ffffff'
  secondary-container: '#fadc9b'
  on-secondary-container: '#76602b'
  tertiary: '#5a5854'
  on-tertiary: '#ffffff'
  tertiary-container: '#73706c'
  on-tertiary-container: '#faf5f0'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffddb2'
  primary-fixed-dim: '#e8c08b'
  on-primary-fixed: '#291800'
  on-primary-fixed-variant: '#5d4218'
  secondary-fixed: '#fddf9e'
  secondary-fixed-dim: '#e0c384'
  on-secondary-fixed: '#251a00'
  on-secondary-fixed-variant: '#584411'
  tertiary-fixed: '#e6e2dd'
  tertiary-fixed-dim: '#cac6c1'
  on-tertiary-fixed: '#1d1b19'
  on-tertiary-fixed-variant: '#484643'
  background: '#fff8f4'
  on-background: '#25190a'
  surface-variant: '#f7dfc5'
typography:
  display-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  num-lg:
    fontFamily: JetBrains Mono
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 24px
  num-md:
    fontFamily: JetBrains Mono
    fontSize: 16px
    fontWeight: '500'
    lineHeight: 20px
  label-caps:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.05em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  touch-min: 48px
  touch-ideal: 56px
  gutter: 16px
  margin-mobile: 16px
  margin-desktop: 32px
  stack-sm: 8px
  stack-md: 16px
  stack-lg: 24px
---

## Brand & Style
The design system is engineered for the high-velocity, high-tactile environment of Indonesian coffee shops (*Warkops*). The brand personality is professional yet warm, bridging the gap between traditional hospitality and modern digital efficiency. It evokes a sense of reliability and artisanal quality, using a "Warm Professionalism" style that blends clean corporate logic with a coffee-inspired aesthetic.

The visual direction leans into **High-Contrast Modernism**. It utilizes structured grids and clear boundaries to ensure legibility under varied lighting conditions—from dim evening cafe settings to bright morning sunlight. The system prioritizes functional density, ensuring that baristas and cashiers can navigate complex orders with minimal cognitive load and maximum physical precision.

## Colors
The palette is rooted in the organic tones of the coffee brewing process. The primary "Deep Coffee" provides a strong anchor for brand actions, while "Creamy Milk" serves as a soft accent for secondary interactions and highlights.

- **Primary (#8B6B3D):** Used for main action buttons, active states, and primary branding.
- **Secondary (#D4B87A):** Used for highlighting promotions, soft call-to-actions, and subtle dividers.
- **Background (#F5F0EB):** A warm cream base that reduces eye strain compared to pure white, essential for long shifts.
- **Neutral (#2F2212):** A high-contrast dark brown for maximum legibility of text and iconography.
- **Status Colors:** Follow industry standards but are calibrated for high visibility against the warm cream background to ensure urgent alerts are never missed.

**Dark Mode:** When active, the background shifts to `#1A140B` (Midnight Brew) with text shifting to `#F5F0EB`, maintaining the warm temperature while protecting the user's vision in low-light environments.

## Typography
This design system employs a tri-font strategy to maximize functional clarity:

1.  **Plus Jakarta Sans (Display):** Used for headings and branding elements. Its soft curves provide a welcoming, modern Indonesian feel.
2.  **Inter (UI/Body):** The workhorse font for all interface labels, descriptions, and inputs. Chosen for its exceptional legibility at small sizes and high x-height.
3.  **JetBrains Mono (Data/Numbers):** Specifically utilized for prices, quantities, SKU numbers, and timestamps. Tabular figures ensure that columns of prices always align perfectly, facilitating faster mental arithmetic for staff.

**Mobile Scaling:** Headlines above 24px scale down by 15% on mobile devices to maintain layout integrity.

## Layout & Spacing
The layout follows a **Fluid-Responsive Grid** model with a heavy emphasis on vertical rhythm to support rapid scrolling and scanning.

- **Desktop/Tablet (POS Terminal):** 12-column grid. The layout is typically split between a 2/3 product selection area and a 1/3 fixed sidebar for the current order/receipt.
- **Mobile (Handheld Ordering):** Single column with fixed bottom actions.
- **Touch Strategy:** A strict 8px spacing system is enforced. Every interactive element must adhere to the `touch-min` (48px) requirement, with the `touch-ideal` (56px) preferred for high-frequency buttons like "Add to Cart" or "Pay."

Gutters are kept at a consistent 16px to maintain high information density without clutter.

## Elevation & Depth
Informed by FilamentPHP 3, this design system uses **Tonal Layering** combined with **Low-Contrast Outlines**.

- **Level 0 (Background):** Warm Cream (#F5F0EB). The canvas.
- **Level 1 (Surface):** Pure White (#FFFFFF). Used for cards and containers. These feature a 1px border in `#E5E0DA` and a very subtle, diffused shadow (0px 1px 3px rgba(47, 34, 18, 0.05)).
- **Level 2 (Interaction):** Floating elements like Modals or Popovers. These use a more pronounced shadow (0px 10px 15px -3px rgba(47, 34, 18, 0.1)) to clearly separate the interaction from the POS background.

Avoid heavy skeuomorphism; depth is communicated through border weight and subtle shifts in surface color.

## Shapes
The shape language is "Friendly Professional." Elements use the `Rounded` setting (8px base radius) to evoke a modern, approachable feel while maintaining the structured look of a professional tool.

- **Standard Buttons/Inputs:** 8px (rounded-md)
- **Cards/Containers:** 16px (rounded-lg)
- **Status Badges/Chips:** Full pill (999px) to contrast against the more geometric product cards.

## Components

### Buttons
- **Primary:** Filled #8B6B3D with White text. High-contrast.
- **Secondary:** Outlined with 1px #8B6B3D, transparent background.
- **Ghost:** No border, Primary color text. Used for less frequent actions.
- **Large Action:** Height 56px, bold typography, specifically for "Payment" and "Print."

### Product Cards
- Features a top-aligned image or color placeholder.
- **Stock Indicator:** Small badge in the top-right using `JetBrains Mono`. Turns Red (#C62828) when below 5 units.
- **Price:** Bolded in the bottom right, using `JetBrains Mono`.

### KDS (Kitchen Display System) Tickets
- **Priority Borders:** Tickets feature a 4px left-hand border indicating status (Success for "Done," Warning for "New/Urgent," Danger for "Delayed").
- **Checklist:** Items within tickets use 48px row heights for easy "tap-to-complete."

### Input Fields
- Clear 1px borders. Focused state uses a 2px Primary color ring. 
- Labels are always visible (no floating labels that disappear) to assist in high-speed data entry.

### Status Badges
- Used for "Paid," "Unpaid," "Dine-in," or "Takeaway."
- Semi-transparent background versions of the status colors with high-contrast text for a sophisticated, modern appearance.