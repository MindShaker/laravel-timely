# Design System Styleguide

This document captures every design token, pattern, and convention used in this project so it can be replicated exactly in another codebase.

---

## Stack

- **Framework**: React 18 + Inertia.js (Laravel backend)
- **Styling**: Tailwind CSS v3 + CSS custom properties
- **Fonts**: Figtree (Google Fonts) — loaded via `<link>` in the HTML template
- **Icons**: Material Symbols SVG (400 & 600 weight)
- **Charts**: Recharts

---

## Theme Architecture

The app supports two themes: **Dark (MSK Classic)** — the default — and **Light (Pure White)**.

All colors are CSS custom properties on `:root`. The dark theme is defined in `app.css`; the light theme overwrites those variables inline. Tailwind color tokens are aliases to these CSS vars.

No theme-related transitions fire on initial load — a `no-theme-transitions` class is temporarily added to `<html>` during switches to prevent flicker.

---

## CSS Custom Properties — Dark Theme (Default)

```css
:root {
  /* ─── Surfaces ─────────────────────────────────────────── */
  --color-surface-canvas:        #1e1f21;  /* Page background */
  --color-chrome-top-bar:        #2a2b2d;  /* Nav bar & header strip */
  --color-surface-panel:         #2a2b2d;  /* Cards, panels */
  --color-surface-detail-shell:  #2a2b2d;  /* Detail pane wrapper */
  --color-surface-panel-hover:   #353739;  /* Raised/hovered surfaces */
  --color-surface-inset-well:    #111213;  /* Recessed wells */
  --color-board-lane-bg:         #191a1c;  /* Board column fill */
  --color-board-column-outline:  #262626;  /* Board column frame */
  --color-divider-line:          #404040;  /* Borders between surfaces */
  --color-watermark-fill:        #1a1b1d;  /* Background watermark */

  /* ─── Typography ────────────────────────────────────────── */
  --color-text-body:             #e8e8e8;
  --color-text-muted:            #b0b0b8;
  --color-text-heading:          #fafafa;
  --color-badge-icon-on-solid-fg: var(--color-text-body);

  /* ─── Accent / Brand ────────────────────────────────────── */
  --color-accent-brand:          #d39a11;  /* Amber — primary brand color */
  --color-accent-soft:           #e5a020;  /* Softer amber variant */
  --color-accent-button-bg:      #5c430a;  /* Primary button fill */
  --color-accent-button-bg-hover:#6b5012;  /* Primary button hover */
  --color-text-on-accent:        #ffffff;

  /* ─── Navigation ────────────────────────────────────────── */
  --color-nav-item-fg:           #ffffff;
  --color-nav-active-bg:         #1e1f21;
  --color-dropdown-active-bg:    #1e1f21;
  --color-nav-active-fg:         #ffffff;
  --color-nav-item-hover-bg:     #3a3a3c;

  /* ─── Status Badges ─────────────────────────────────────── */
  --color-status-badge-completed-fill:        #052e16;
  --color-status-badge-completed-outline:     #15803d;
  --color-status-badge-in-progress-fill:      #172554;
  --color-status-badge-in-progress-outline:   #1d4ed8;
  --color-status-badge-on-hold-fill:          #5b471c;
  --color-status-badge-on-hold-outline:       #f3bd4a;
  --color-status-badge-done-in-dev-fill:      #042f2e;
  --color-status-badge-done-in-dev-outline:   #0f766e;
  --color-status-badge-in-review-fill:        #3b0764;
  --color-status-badge-in-review-outline:     #7e22ce;
  --color-status-badge-recurring-fill:        #5e2842;
  --color-status-badge-recurring-outline:     #b0607e;
  --color-status-badge-not-completed-fill:    #1e1f21;
  --color-status-badge-not-completed-outline: #404040;

  /* ─── Priority ──────────────────────────────────────────── */
  --color-priority-urgent:  #ef4444;
  --color-priority-highest: #c0392b;
  --color-priority-high:    #e07a6e;
  --color-priority-medium:  #d4a83d;
  --color-priority-low:     #5a9fd4;
  --color-priority-lowest:  #7a8a9a;

  /* ─── Form Fields ───────────────────────────────────────── */
  --color-field-bg:              #1e1f21;
  --color-field-border:          #3f3f46;
  --color-field-focus:           #737373;
  --color-field-placeholder:     #737373;
  --color-field-outline-strong:  #a1a1aa;
  --color-filter-select-selected-bg: #2a2b2d;

  /* ─── Interactive surfaces ──────────────────────────────── */
  --color-chrome-hover-wash:     var(--color-surface-panel-hover);
  --color-card-hover-outline:    #525252;
  --color-table-row-line:        #404040;
  --color-menu-option-hover-bg:  #353739;

  /* ─── Chip / pill ───────────────────────────────────────── */
  --color-chip-neutral-bg:       #1e2126;
  --color-chip-neutral-border:   #1e2126;
  --color-chip-neutral-hover-bg: var(--color-surface-panel);

  /* ─── Segmented control ─────────────────────────────────── */
  --color-segmented-track-bg:    #202124;
  --color-segmented-selected-bg: var(--color-surface-panel-hover);
  --color-segmented-selected-fg: var(--color-text-body);
  --color-segmented-unselected-bg: transparent;

  /* ─── Markdown editor ───────────────────────────────────── */
  --color-markdown-bar-bg:           #2a2b2d;
  --color-markdown-tabs-active-bg:   var(--color-segmented-selected-bg);
  --color-markdown-tabs-active-fg:   var(--color-segmented-selected-fg);

  /* ─── Task list badges ──────────────────────────────────── */
  --color-task-strip-badge-bg:    #3a3b3d;
  --color-task-strip-badge-fg:    #ffffff;
  --color-task-strip-badge-border:#3f3f46;

  /* ─── Skeleton loaders ──────────────────────────────────── */
  --color-skeleton-block: #2c2c2f;

  /* ─── Filter toolbar ────────────────────────────────────── */
  --color-toolbar-filter-bg:    #1e1f21;
  --color-toolbar-filter-hover: #2a2b2d;

  /* ─── Disabled controls ─────────────────────────────────── */
  --color-control-disabled-border: #39393e;
  --color-control-disabled-fg:     #71717a;

  /* ─── Destructive actions ───────────────────────────────── */
  --color-destructive-bg:              #7f1d1d;
  --color-destructive-border:          #b91c1c;
  --color-destructive-hover:           #991b1b;
  --color-destructive-fg:              #ffffff;
  --color-destructive-disabled-bg:     #7f1d1d;
  --color-destructive-disabled-border: #dc2626;
  --color-destructive-disabled-fg:     #a1a1aa;

  /* ─── Timeline / Charts ─────────────────────────────────── */
  --color-timeline-billable:         var(--color-accent-brand);
  --color-timeline-non-billable:     #3b82f6;
  --color-timeline-grid-line:        #3f3f46;
  --color-timeline-selected-day-fill:#9ca3af;
  --color-timeline-hover-day-fill:   #9ca3af;
  --color-timeline-target-limit-line:#ef4444;
  --color-timeline-axis-text:        #9ca3af;
  --color-timeline-tooltip-bg:       #1e1f21;
  --color-timeline-tooltip-border:   #3f3f46;
  --color-timeline-tooltip-text:     #ffffff;
  --color-timeline-tooltip-label-text:#9ca3af;
  --color-timeline-time-entries-btn-fg:#ffffff;

  /* ─── Time log badges ───────────────────────────────────── */
  --color-timelog-badge-billable-fill:       var(--color-accent-button-bg);
  --color-timelog-badge-billable-outline:    var(--color-accent-brand);
  --color-timelog-badge-billable-fg:         var(--color-text-on-accent);
  --color-timelog-badge-non-billable-fill:   #1d4ed8;
  --color-timelog-badge-non-billable-outline:#3b82f6;
  --color-timelog-badge-non-billable-fg:     #ffffff;
  --color-timelog-badge-over-budget-fill:    rgba(69, 10, 10, 0.4);
  --color-timelog-badge-over-budget-outline: rgba(220, 38, 38, 0.8);

  /* ─── Misc ──────────────────────────────────────────────── */
  --color-tag-overlay-darken-percent: 45;
  --color-lane-chip-tint-alpha:       0.14;
  --color-lane-without-phase-border:  #404040;
  --color-lane-without-phase-fg:      #d4d4d4;
  --color-profile-role-swash:         #71717a;
  --color-permission-allowed:         #4ade80;
  --color-permission-blocked:         #f87171;
  --color-permission-conditional:     #fbbf24;
  --color-history-dot:               var(--color-field-border);
  --color-history-comment-text:      #a6a6ad;
}
```

---

## CSS Custom Properties — Light Theme (Pure White)

Apply these as inline overrides on `<html>` (or `[data-theme="pure-white"]`):

```css
[data-theme="pure-white"] {
  --color-surface-canvas:        #ffffff;
  --color-chrome-top-bar:        #0a0a0a;
  --color-surface-panel:         #ffffff;
  --color-surface-detail-shell:  #ffffff;
  --color-surface-panel-hover:   #f8fafc;
  --color-surface-inset-well:    #e2e8f0;
  --color-board-lane-bg:         #ffffff;
  --color-board-column-outline:  #ededed;
  --color-divider-line:          #404040;
  --color-watermark-fill:        #eaedf0;
  --color-text-body:             #0f172a;
  --color-text-muted:            #64748b;
  --color-text-heading:          #000000;
  --color-accent-brand:          #000000;
  --color-accent-soft:           #262626;
  --color-accent-button-bg:      #000000;
  --color-accent-button-bg-hover:#262626;
  --color-text-on-accent:        #ffffff;
  --color-nav-item-fg:           #ffffff;
  --color-nav-active-bg:         #ffffff;
  --color-nav-active-fg:         #000000;
  --color-nav-item-hover-bg:     rgba(255, 255, 255, 0.1);
  --color-status-badge-completed-fill:        #16a34a;
  --color-status-badge-completed-outline:     #16a34a;
  --color-status-badge-in-progress-fill:      #266bea;
  --color-status-badge-in-progress-outline:   #266bea;
  --color-status-badge-on-hold-fill:          #dfba48;
  --color-status-badge-on-hold-outline:       #dfba48;
  --color-status-badge-done-in-dev-fill:      #0d9488;
  --color-status-badge-done-in-dev-outline:   #0d9488;
  --color-status-badge-in-review-fill:        #7c3aed;
  --color-status-badge-in-review-outline:     #7c3aed;
  --color-status-badge-recurring-fill:        #e9629e;
  --color-status-badge-recurring-outline:     #e9629e;
  --color-status-badge-not-completed-fill:    #000000;
  --color-status-badge-not-completed-outline: #000000;
  --color-priority-urgent:   #ef4444;
  --color-priority-highest:  #b91c1c;
  --color-priority-high:     #dc2626;
  --color-priority-medium:   #d97706;
  --color-priority-low:      #2563eb;
  --color-priority-lowest:   #64748b;
  --color-field-bg:              #ffffff;
  --color-field-border:          #d1d9e0;
  --color-field-focus:           #2563eb;
  --color-field-placeholder:     #9ca3af;
  --color-field-outline-strong:  #000000;
  --color-filter-select-selected-bg: #f2f3f4;
  --color-chip-neutral-bg:       #eaedf0;
  --color-chip-neutral-border:   #eaedf0;
  --color-chip-neutral-hover-bg: #eaedf0;
  --color-skeleton-block:        #ebebeb;
  --color-toolbar-filter-bg:     #f6f8fa;
  --color-toolbar-filter-hover:  #eaedf0;
  --color-control-disabled-border: #e9eef6;
  --color-control-disabled-fg:     #bcc9db;
  --color-destructive-bg:              #dc2626;
  --color-destructive-border:          #dc2626;
  --color-destructive-hover:           #b91c1c;
  --color-destructive-fg:              #ffffff;
  --color-destructive-disabled-bg:     #dc2626;
  --color-destructive-disabled-border: #dc2626;
  --color-destructive-disabled-fg:     #ffffff;
  --color-menu-option-hover-bg:  #f2f3f4;
  --color-chrome-hover-wash:     #eaedf0;
  --color-card-hover-outline:    #404040;
  --color-table-row-line:        #e5e7eb;
  --color-timeline-grid-line:    #e0e0e0;
  --color-timeline-selected-day-fill: #fcffc5;
  --color-timeline-hover-day-fill:    #d9d9d9;
  --color-timeline-target-limit-line: #f7a35c;
  --color-timeline-axis-text:    #585858;
  --color-timeline-tooltip-bg:   #ffffff;
  --color-timeline-tooltip-border:#585858;
  --color-timeline-tooltip-text: #000000;
  --color-timeline-tooltip-label-text: #000000;
  --color-timeline-time-entries-btn-fg: #000000;
  --color-timeline-billable:     #43c86f;
  --color-timeline-non-billable: #7798bf;
  --color-timelog-badge-billable-fill:        #43c86f;
  --color-timelog-badge-billable-outline:     #43c86f;
  --color-timelog-badge-billable-fg:          #ffffff;
  --color-timelog-badge-non-billable-fill:    #7798bf;
  --color-timelog-badge-non-billable-outline: #7798bf;
  --color-timelog-badge-non-billable-fg:      #ffffff;
  --color-timelog-badge-over-budget-fill:     #ffffff;
  --color-timelog-badge-over-budget-outline:  #dc2626;
  --color-segmented-track-bg:    #ffffff;
  --color-segmented-selected-bg: #000000;
  --color-segmented-selected-fg: #ffffff;
  --color-segmented-unselected-bg: #ffffff;
  --color-markdown-bar-bg:       #f6f8fa;
  --color-markdown-tabs-active-bg: #eaedf0;
  --color-markdown-tabs-active-fg: #0f172a;
  --color-task-strip-badge-bg:    #000000;
  --color-task-strip-badge-fg:    #ffffff;
  --color-task-strip-badge-border:#000000;
  --color-badge-icon-on-solid-fg: #ffffff;
  --color-lane-without-phase-border: #d1d9e0;
  --color-lane-without-phase-fg:     #000000;
  --color-tag-overlay-darken-percent: 0;
  --color-lane-chip-tint-alpha: 1;
  --color-profile-role-swash:    #000000;
  --color-permission-allowed:    #15803d;
  --color-permission-blocked:    #b91c1c;
  --color-permission-conditional:#b45309;
}
```

---

## Tailwind Config

```js
// tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.jsx',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
      },
      colors: {
        background:           'var(--color-surface-canvas)',
        base:                 'var(--color-chrome-top-bar)',
        surface:              'var(--color-surface-panel)',
        'surface-detail':     'var(--color-surface-detail-shell)',
        'surface-hover':      'var(--color-surface-panel-hover)',
        'chrome-hover':       'var(--color-chrome-hover-wash)',
        well:                 'var(--color-surface-inset-well)',
        'board-lane':         'var(--color-board-lane-bg)',
        'board-column':       'var(--color-board-column-outline)',
        primary:              'var(--color-accent-brand)',
        'primary-light':      'var(--color-accent-soft)',
        'primary-btn':        'var(--color-accent-button-bg)',
        'primary-btn-hover':  'var(--color-accent-button-bg-hover)',
        'on-primary':         'var(--color-text-on-accent)',
        content:              'var(--color-text-body)',
        'content-muted':      'var(--color-text-muted)',
        'icon-emphasis':      'var(--color-text-heading)',
        'nav-fg':             'var(--color-nav-item-fg)',
        'nav-active-bg':      'var(--color-nav-active-bg)',
        'nav-active-fg':      'var(--color-nav-active-fg)',
        'nav-hover-bg':       'var(--color-nav-item-hover-bg)',
        'dropdown-active-bg': 'var(--color-dropdown-active-bg)',
        'input':              'var(--color-field-bg)',
        'input-border':       'var(--color-field-border)',
        'input-ring':         'var(--color-field-focus)',
        'input-placeholder':  'var(--color-field-placeholder)',
        'input-selected-border':'var(--color-field-outline-strong)',
        'filter-bg':          'var(--color-toolbar-filter-bg)',
        'filter-hover':       'var(--color-toolbar-filter-hover)',
        'filter-select-selected': 'var(--color-filter-select-selected-bg)',
        'meta-chip':          'var(--color-chip-neutral-bg)',
        'meta-chip-border':   'var(--color-chip-neutral-border)',
        'meta-chip-control-hover': 'var(--color-chip-neutral-hover-bg)',
        'segmented-track':    'var(--color-segmented-track-bg)',
        'segmented-active':   'var(--color-segmented-selected-bg)',
        'segmented-active-fg':'var(--color-segmented-selected-fg)',
        'segmented-inactive': 'var(--color-segmented-unselected-bg)',
        'markdown-header':    'var(--color-markdown-bar-bg)',
        'markdown-segmented-active':    'var(--color-markdown-tabs-active-bg)',
        'markdown-segmented-active-fg': 'var(--color-markdown-tabs-active-fg)',
        'task-list-status-pill-bg':     'var(--color-task-strip-badge-bg)',
        'task-list-status-pill-fg':     'var(--color-task-strip-badge-fg)',
        'task-list-status-pill-border': 'var(--color-task-strip-badge-border)',
        'status-chip-label':  'var(--color-badge-icon-on-solid-fg)',
        'card-hover-border':  'var(--color-card-hover-outline)',
        'table-row-divider':  'var(--color-table-row-line)',
        'select-option-hover':'var(--color-menu-option-hover-bg)',
        'fase-menu-row-hover':'var(--color-lane-dropdown-row-hover-bg)',
        'cloud-bg':           'var(--color-watermark-fill)',
        'control-disabled-border': 'var(--color-control-disabled-border)',
        'control-disabled-fg':     'var(--color-control-disabled-fg)',
        'danger-btn-bg':      'var(--color-destructive-bg)',
        'danger-btn-border':  'var(--color-destructive-border)',
        'danger-btn-hover':   'var(--color-destructive-hover)',
        'danger-btn-fg':      'var(--color-destructive-fg)',
        'history-dot':        'var(--color-history-dot)',
        'history-comment-text':'var(--color-history-comment-text)',
        'permission-allowed': 'var(--color-permission-allowed)',
        'permission-blocked': 'var(--color-permission-blocked)',
        'permission-conditional': 'var(--color-permission-conditional)',
      },
    },
  },
  plugins: [forms],
};
```

> **Note on `border-neutral-700` / `border-neutral-600`**: These Tailwind defaults are overridden via `@layer utilities` to resolve to `--color-divider-line`. Use them freely as a "divider" shorthand. Likewise `bg-neutral-800` maps to `--color-skeleton-block`.

---

## Typography

- **Font**: Figtree — load from Google Fonts: `https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap`
- `font-sans` resolves to Figtree
- Body text: `text-content` (CSS var: `--color-text-body`)
- Muted/secondary: `text-content-muted`
- Headings / emphasis: `text-icon-emphasis`

---

## Scrollbar

Thin, pill-shaped, transparent track:

```css
::-webkit-scrollbar { width: 4px; height: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #404040; border-radius: 9999px; }
::-webkit-scrollbar-thumb:hover { background: #525252; }
* { scrollbar-width: thin; scrollbar-color: #404040 transparent; }
```

---

## Layout

### Page wrapper

```jsx
<div className="min-h-screen bg-background relative">
  {/* Background watermark — centered, fixed, non-interactive */}
  <div className="fixed inset-0 flex items-center justify-center pointer-events-none z-0" aria-hidden>
    <Watermark className="w-[min(90vmin,700px)] opacity-[0.42] select-none" />
  </div>

  <div className="relative z-10 pt-3">
    <nav>…</nav>
    <header>…</header>
    <main>
      <div className="max-w-[1570px] mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        {children}
      </div>
    </main>
  </div>
</div>
```

- **Max content width**: `1570px`
- **Page bg**: `bg-background`
- **Nav/content top padding**: `pt-3` on the outer wrapper, `pt-4` on main content

---

## Navigation Bar

A pill-shaped, floating navbar that sits `pt-3` from the top:

```jsx
<nav className="overflow-visible bg-base border border-neutral-700 rounded-xl max-w-[1570px] mx-auto px-4 sm:px-6 lg:px-8 py-[3px] text-nav-fg">
  <div className="flex h-14 justify-between">

    {/* Left: logo + nav links */}
    <div className="flex gap-0">
      <div className="flex items-center pl-5 pr-5 rounded-l-xl bg-base">
        <a href="/"><Logo /></a>
      </div>
      <div className="flex items-center gap-1">
        <NavLink href="/tasks" active={…}>Tasks</NavLink>
        {/* … more NavLinks */}
      </div>
    </div>

    {/* Right: theme switcher + user dropdown */}
    <div className="flex items-center gap-2 pl-3 pr-4 rounded-r-xl bg-base">
      <ThemeSwitcher />
      <UserDropdown />
    </div>

  </div>
</nav>
```

Key details:
- `rounded-xl` — the entire nav is pill-shaped
- `bg-base` — uses `--color-chrome-top-bar`, the slightly-lighter-than-canvas surface
- `border border-neutral-700` — thin divider-colored border
- `h-14` — 56px height
- Left end and right end have `rounded-l-xl` / `rounded-r-xl` nested wrappers with same `bg-base`

### Nav link (active vs inactive)

```jsx
// Inactive
"inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-nav-fg hover:bg-nav-hover-bg leading-5 transition duration-150 ease-in-out"

// Active
"inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium bg-nav-active-bg text-nav-active-fg leading-5 transition duration-150 ease-in-out"
```

### Page sub-header (optional, below nav)

Attaches flush to the bottom of the navbar — notice `border-t-0` and `rounded-b-xl`:

```jsx
<header className="mx-3 mt-0 sm:mx-4 bg-base border border-neutral-700 border-t-0 rounded-b-xl px-4 py-4 sm:px-6 lg:px-8 text-nav-fg">
  {header}
</header>
```

---

## Dropdown

```jsx
// Panel
"absolute right-0 z-50 mt-2 w-max min-w-48 origin-top-right rounded-xl border border-neutral-700 bg-surface py-1 shadow-lg focus:outline-none"

// Row (inactive)
"block w-full px-4 py-2 text-left text-sm text-content hover:bg-select-option-hover focus:bg-select-option-hover focus:outline-none"

// Row (active)
"block w-full px-4 py-2 text-left text-sm bg-dropdown-active-bg text-content font-medium"
```

User info header inside dropdown:

```jsx
<div className="py-4 px-4 border-b border-neutral-700 flex flex-col gap-2 items-center rounded-t-xl">
  <div className="text-sm text-content font-medium">{name}</div>
  <div className="text-sm text-content-muted">{email}</div>
</div>
```

---

## Buttons

### Primary Button

Amber-bordered, dark amber background:

```jsx
"inline-flex items-center rounded-md border border-primary bg-primary-btn px-4 py-2 text-xs font-semibold uppercase tracking-widest text-on-primary transition duration-150 ease-in-out hover:bg-primary-btn-hover hover:border-primary-btn-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-surface active:bg-primary-btn disabled:opacity-25"
```

### Secondary Button

Subtle, border-only:

```jsx
// Small
"px-2 py-1 text-xs leading-5 text-content transition duration-150 ease-in-out hover:bg-chrome-hover border border-neutral-700 rounded"

// Medium
"px-3 py-2 text-sm leading-5 text-content transition duration-150 ease-in-out hover:bg-chrome-hover border border-neutral-700 rounded"
```

### Primary Button (compact variants from constants)

```jsx
// Small
"px-2 py-1 text-xs leading-5 text-on-primary transition duration-150 ease-in-out rounded border border-primary bg-primary-btn hover:bg-primary-btn-hover"

// Medium
"px-3 py-2 text-sm leading-5 text-on-primary transition duration-150 ease-in-out rounded border border-primary bg-primary-btn hover:bg-primary-btn-hover"
```

### Destructive / Danger Button

```jsx
"text-danger-btn-fg bg-danger-btn-bg border border-danger-btn-border hover:bg-danger-btn-hover"
```

---

## Form Inputs

### Text input

```jsx
"rounded-md border-input-border bg-input text-content placeholder:text-input-placeholder shadow-sm focus:border-input-ring focus:ring-input-ring focus:outline-none"
```

### Input class variants (from constants)

```js
BASE:     "px-2 py-1 text-sm bg-background border border-neutral-700 rounded-md text-content focus:outline-none focus:ring-2 focus:ring-neutral-500"
DATE:     "h-9 px-2 py-1 text-sm bg-background border border-neutral-700 rounded-md text-content focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:border-neutral-500"
TEXTAREA: "px-2 py-1 text-sm bg-background border border-neutral-700 rounded-md text-content focus:outline-none focus:ring-2 focus:ring-neutral-500 resize-none"
```

### Checkbox

```css
/* Standard */
input[type="checkbox"] {
  accent-color: var(--color-accent-brand);
}

/* Custom "billable" checkbox */
input[type="checkbox"].billable-checkbox {
  appearance: none;
  width: 1.25rem; height: 1.25rem;
  border-radius: 0.25rem;
  border: 2px solid var(--color-field-border);
  background-color: var(--color-field-bg);
  cursor: pointer;
  transition: border-color 150ms ease;
}
input[type="checkbox"].billable-checkbox:checked {
  border-color: var(--color-accent-brand);
  background-color: var(--color-accent-brand);
  /* white SVG checkmark via background-image */
}
```

---

## Cards / Panels

```jsx
// Basic panel
"bg-surface border border-neutral-700 rounded-xl"

// Hoverable card
"bg-surface border border-neutral-700 rounded-xl hover:border-card-hover-border transition-colors"
```

---

## Time-Entry Duration Pill

Reusable chrome pattern for clock/duration displays:

```js
// Read-only
"rounded-full border border-neutral-700 bg-background text-content"

// Interactive (adds hover states)
"rounded-full border border-neutral-700 bg-background text-content hover:bg-chrome-hover hover:border-neutral-600 transition-colors"
```

---

## Skeleton Loaders

```jsx
"bg-neutral-800 rounded animate-pulse"
// bg-neutral-800 is overridden to --color-skeleton-block via @layer utilities
```

---

## Color Reference (Quick Cheat Sheet)

| Tailwind class          | CSS var                        | Dark value  | Light value |
|-------------------------|--------------------------------|-------------|-------------|
| `bg-background`         | `--color-surface-canvas`       | `#1e1f21`   | `#ffffff`   |
| `bg-base`               | `--color-chrome-top-bar`       | `#2a2b2d`   | `#0a0a0a`   |
| `bg-surface`            | `--color-surface-panel`        | `#2a2b2d`   | `#ffffff`   |
| `bg-surface-hover`      | `--color-surface-panel-hover`  | `#353739`   | `#f8fafc`   |
| `bg-well`               | `--color-surface-inset-well`   | `#111213`   | `#e2e8f0`   |
| `text-content`          | `--color-text-body`            | `#e8e8e8`   | `#0f172a`   |
| `text-content-muted`    | `--color-text-muted`           | `#b0b0b8`   | `#64748b`   |
| `text-icon-emphasis`    | `--color-text-heading`         | `#fafafa`   | `#000000`   |
| `text-primary`          | `--color-accent-brand`         | `#d39a11`   | `#000000`   |
| `bg-primary-btn`        | `--color-accent-button-bg`     | `#5c430a`   | `#000000`   |
| `text-on-primary`       | `--color-text-on-accent`       | `#ffffff`   | `#ffffff`   |
| `border-neutral-700`    | `--color-divider-line`         | `#404040`   | `#404040`   |
| `bg-input`              | `--color-field-bg`             | `#1e1f21`   | `#ffffff`   |
| `border-input-border`   | `--color-field-border`         | `#3f3f46`   | `#d1d9e0`   |
| `bg-meta-chip`          | `--color-chip-neutral-bg`      | `#1e2126`   | `#eaedf0`   |
| `bg-chrome-hover`       | `--color-chrome-hover-wash`    | `#353739`   | `#eaedf0`   |

---

## Spacing & Sizing Conventions

| Purpose                      | Value                    |
|------------------------------|--------------------------|
| Max content width            | `max-w-[1570px]`         |
| Page horizontal padding      | `px-4 sm:px-6 lg:px-8`  |
| Nav bar height               | `h-14` (56px)            |
| Nav bar border radius        | `rounded-xl`             |
| Panel/card border radius     | `rounded-xl`             |
| Dropdown border radius       | `rounded-xl`             |
| Button border radius         | `rounded-md`             |
| Input border radius          | `rounded-md`             |
| Nav top offset               | `pt-3` (12px)            |
| Sections gap                 | `pt-4` (16px)            |
| Small icon size              | `16px`                   |
| Medium icon size             | `24px`                   |

---

## Global Base Styles

```css
html, body {
  background-color: var(--color-surface-canvas);
  color: var(--color-text-body);
  overscroll-behavior: none;
}

/* Date/time picker icon — inverted for dark mode */
input[type="date"]::-webkit-calendar-picker-indicator,
input[type="time"]::-webkit-calendar-picker-indicator {
  filter: invert(1);
  opacity: 0.8;
  cursor: pointer;
}
[data-theme="pure-white"] input[type="date"]::-webkit-calendar-picker-indicator,
[data-theme="pure-white"] input[type="time"]::-webkit-calendar-picker-indicator {
  filter: none;
}
```

---

## Transition Conventions

| Use case           | Classes                                          |
|--------------------|--------------------------------------------------|
| Nav/button hover   | `transition duration-150 ease-in-out`            |
| Color transitions  | `transition-colors`                              |
| Theme switch       | All transitions suppressed via `no-theme-transitions` class on `<html>` |

---

## How to Apply in Another Project

1. **Copy both CSS variable blocks** (dark `:root` and light override) into your `app.css`.
2. **Copy the Tailwind `colors` block** into your `tailwind.config.js`. Install `@tailwindcss/forms`.
3. **Add `Figtree`** to your HTML `<head>`:
   ```html
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
   ```
4. **Set `font-sans`** in your Tailwind config to `['Figtree', ...defaultTheme.fontFamily.sans]`.
5. **Add the scrollbar CSS** to your global stylesheet.
6. **Add the global base styles** (`html, body` block).
7. Use the Tailwind semantic tokens (`bg-surface`, `text-content`, `border-neutral-700`, etc.) everywhere — never hard-code hex values in components.
8. For theme switching: toggle the light theme by setting `--color-*` inline overrides on `<html>` (or a `[data-theme]` attribute), suppressing transitions with the `no-theme-transitions` class for one frame.
