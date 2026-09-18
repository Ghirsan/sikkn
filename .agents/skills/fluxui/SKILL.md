---
name: fluxui
description: "FluxUI Guidelines and Layouts. Use this skill when building or modifying user interfaces with the Flux UI library."
---

# FluxUI Guidelines & Layouts

This document summarizes the core principles, patterns, and layout structures of FluxUI based on the official documentation.

## Installation & Setup
- **Requirements:** Laravel 10+, Livewire 3.7+, Tailwind CSS 4.2+
- **Installation:** via `composer require livewire/flux` (and `livewire/flux-pro` if licensed).
- **Directives:** Include `@fluxAppearance` in `<head>` (handles dark mode) and `@fluxScripts` in `<body>`.
- **Tailwind Config:** Import `flux.css` in `app.css` directly after Tailwind:
  ```css
  @import 'tailwindcss';
  @import '../../vendor/livewire/flux/dist/flux.css';
  @custom-variant dark (&:where(.dark, .dark *));
  ```
- **Typography:** Recommended to use the Inter font (`--font-sans: Inter, sans-serif;`).

## Principles
1. **Simplicity:** Components have a clean and intuitive syntax. E.g., `<flux:input wire:model="email" label="Email" />`.
2. **Complexity / Composability:** Complex parts can be broken down. E.g., composing `<flux:field>`, `<flux:label>`, `<flux:input>`, and `<flux:error>` manually if more control is needed.
3. **Friendliness:** Uses familiar terminology (e.g., "accordion" instead of "disclosure", "input" instead of "form-control").
4. **Consistency:** Repeated naming conventions like `heading` for titles across different components.
5. **Brevity:** Shorter component names without deep dot-notations or hyphens when possible.
6. **Use the Browser / CSS:** Uses native features like `<dialog>` for modals and `popover` attributes. Uses CSS like `:has()` for interactivity instead of heavy JavaScript.
7. **We style, you space:** Flux handles component styling (padding, borders, backgrounds) but leaves layout spacing (margins, flexbox layouts) to the developer.

## Patterns
- **Props vs Attributes:** Props (e.g., `variant`) control internal logic/styling, while attributes (e.g., `x-on:change.prevent`) are passed directly to the underlying element.
- **Class Merging:** Custom classes are merged with Flux classes. For conflicts (e.g., `bg-zinc-800` overriding default bg), Tailwind's `!` modifier may be needed (`class="bg-zinc-800!"`).
- **Split Attribute Forwarding:** Attributes are intelligently distributed. E.g., in `<flux:input class="w-full" autofocus />`, `w-full` goes to the wrapper `<div>`, while `autofocus` goes to the `<input>`.
- **Common Props:**
  - `variant`: `primary`, `filled`, `flyout`, `solid`, `subtle`, `ghost`, etc.
  - `icon`: E.g., `<flux:button icon="magnifying-glass" />`. Use `icon:trailing` for end icons.
  - `size`: `sm`, `lg`.
  - `kbd`: Keyboard hints like `kbd="⌘S"`.
  - `inset`: Adjusts negative margin to align items perfectly inline (e.g., `inset="top bottom"`).
- **Shorthand Props:** E.g. `<flux:input type="email" label="Email" wire:model="email" />` expands internally to include `field`, `label`, `input`, and `error` sub-components.
- **Data Binding:** Standard Livewire binding: `wire:model`, `wire:model.live` work naturally on all inputs.
- **Component Groups:** Suffixes like `.group` (e.g., `flux:radio.group`) or `.item` (e.g., `flux:navbar.item`) are used for nested/grouped elements.

## Theming & Dark Mode
- **Base Color:** Default is `zinc`. To override, remap `--color-zinc-*` variables in `app.css` under `@theme` to another shade like `slate` or `neutral`.
- **Accent Color:** Three main variables manage the primary color: `--color-accent`, `--color-accent-content`, and `--color-accent-foreground`. These are defined in `@theme` and overridden in `@layer theme { .dark { ... } }`.
- **Dark Mode:**
  - Toggled via the `.dark` class on the `<html>` element.
  - `@fluxAppearance` automatically handles system preference and local storage.
  - JS/Alpine Utilities provided: `$flux.appearance` (`'light'`|`'dark'`|`'system'`) and `$flux.dark` (`true`|`false`).
  - Can be accessed globally in vanilla JS via `Flux.dark` and `Flux.appearance`.
- **Customization:**
  - Override styles with classes (using `!` if conflicting).
  - Globally style data attributes: e.g., `[data-flux-button] { @apply bg-blue-500; }`.
  - Publish components into your project to fully rewrite them using `php artisan flux:publish`.

## Layouts

### Header Layout (`<flux:header>`)
- Use as a full-width top navigation.
- Accepts `sticky` and `container` props.
- Commonly contains `<flux:sidebar.toggle>`, `<flux:brand>`, `<flux:navbar>`, and `<flux:profile>` or a profile dropdown.
- Example Structure:
  ```html
  <flux:header container class="bg-zinc-50 border-b ...">
      <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
      <flux:brand logo="..." name="..." />
      
      <flux:navbar class="max-lg:hidden">
          <flux:navbar.item icon="home" href="#" current>Home</flux:navbar.item>
      </flux:navbar>
      
      <flux:spacer />
      
      <!-- Right Side Actions & Profile -->
  </flux:header>
  ```

### Sidebar Layout (`<flux:sidebar>`)
- Provides primary or secondary sidebar navigation.
- Accepts `sticky` and `collapsible` (`collapsible="mobile"` or `collapsible` for both).
- Composed of `<flux:sidebar.header>`, `<flux:sidebar.search>`, `<flux:sidebar.nav>`, `<flux:sidebar.spacer>`, and `<flux:sidebar.profile>`.
- Sidebar components use specific properties like `expandable` and `heading` for `<flux:sidebar.group>`.
- Example Structure:
  ```html
  <flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 border-r ...">
      <flux:sidebar.header>
          <flux:sidebar.brand logo="..." name="..." />
          <flux:sidebar.collapse class="lg:hidden" />
      </flux:sidebar.header>
      
      <flux:sidebar.search placeholder="Search..." />
      
      <flux:sidebar.nav>
          <flux:sidebar.item icon="home" current>Home</flux:sidebar.item>
          <flux:sidebar.group expandable heading="Favorites">
              <flux:sidebar.item href="#">Marketing site</flux:sidebar.item>
          </flux:sidebar.group>
      </flux:sidebar.nav>
  </flux:sidebar>
  ```
