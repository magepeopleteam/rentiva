# Getting started

1. Install and activate, in this order: **Booking and Rental Manager for
   WooCommerce**, **Elementor**, then **Rentiva**. (Both are declared as
   required plugins in `style.css`, so WordPress prompts for them on the
   Themes screen if either is missing.) **WooCommerce** is optional — the
   booking plugin has its own native checkout, so only install WooCommerce
   if you want its cart/checkout flow instead.
2. On activation (with Elementor active), Rentiva automatically creates a
   "Homepage" page — pre-built out of the theme's 9 Elementor section
   widgets — and sets it as your static front page, so the homepage is a
   real, fully editable Elementor page from the start. A one-time welcome
   notice appears too, and the **Rentiva** admin menu (with its **Setup**
   and **Theme Settings** submenus — see docs/architecture.md) stays
   available afterwards, along with a compact "Rentiva Setup" Dashboard
   widget.
3. Click **Import Demo Content** on **Rentiva → Setup** to create 6 rental
   categories, 3 pickup locations, 4 demo rental items, and 4 real,
   editable nav menus (Primary Navigation, Footer — Explore/Company/
   Support) assigned to their theme locations — so the homepage, archive
   pages, header, and footer are all fully populated (and fully editable,
   nothing left as an invisible PHP-only fallback) while you build out your
   real catalog. Safe to run more than once.
4. Open the **Homepage** page in Elementor (Pages → Homepage → Edit with
   Elementor, or the **Edit Homepage** tile on **Rentiva → Setup**) to edit
   copy/photos section by section, or rearrange/remove/duplicate sections.
   Every field on every section widget is pre-filled with real content the
   moment you open it — see docs/elementor.md.
5. Adjust the menus Import Demo Content created at **Appearance → Menus**
   if you want different links/labels — Rentiva only creates a menu for a
   location that doesn't already have one assigned, so your own changes are
   never overwritten by re-running the import.
6. Add real rental items under the plugin's own "Rentals" (or similarly
   named) admin menu — see the plugin's own documentation for pricing,
   availability, and item-type setup. Add specs/included-items via the
   Feature List field per docs/customization.md.
7. Visit **Rentiva → Theme Settings** for sitewide colors, footer tagline/
   social links, and integration behavior (single-item layout, "List Your
   Item" link, default pickup hours) — homepage copy/photos stay in
   Elementor, not here (see docs/theme-settings.md).

See docs/installation.md for plugin requirements and docs/theme-settings.md
for the full settings reference.
