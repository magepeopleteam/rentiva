# Getting started

1. Activate **Rentiva**. Setup's Step 1 installs and activates the rest:
   **Elementor** and the free **Booking and Rental Manager for WooCommerce**
   (both required, both from WordPress.org — **Install Now**, then
   **Activate**). **Booking and Rental Manager Pro** is optional: click
   **Upload & Install** and choose the Pro zip from your purchase (a GitHub
   "Download ZIP" works too — its `-main` folder is renamed to the real
   plugin folder), then **Activate**. **WooCommerce** is optional — the
   booking plugin has its own native checkout, so only install WooCommerce
   if you want its cart/checkout flow instead.
2. Activating Rentiva takes you straight to **Rentiva → Setup**, a
   three-step wizard — **Plugins → Import → Explore**, one step per screen
   with Back/Continue buttons. Steps 2 and 3 stay locked until Step 1's
   required plugins are active. On activation (with Elementor active),
   Rentiva also automatically creates a "Homepage" page — pre-built out of
   the theme's 9 Elementor section widgets — and sets it as your static
   front page, so the homepage is a real, fully editable Elementor page from
   the start. The **Rentiva** admin menu (with its **Setup** and **Theme
   Settings** submenus — see docs/architecture.md) stays available
   afterwards, along with a compact "Rentiva Setup" Dashboard widget. If the
   redirect can't happen (e.g. the theme was activated via WP-CLI and no
   admin screen was opened within a minute), a one-time welcome notice links
   to Setup instead.
3. Click **Import Demo Content** on Setup's Step 2 (Import) to create 6 rental
   categories, 3 pickup locations, 8 demo rental items, and 4 real,
   editable nav menus (Primary Navigation, Footer — Explore/Company/
   Support) assigned to their theme locations — so the homepage, archive
   pages, header, and footer are all fully populated (and fully editable,
   nothing left as an invisible PHP-only fallback) while you build out your
   real catalog. It runs in place with live progress (no page reload), is
   safe to run more than once, and running it again repairs any demo
   content an earlier import left incomplete — see docs/demo-import.md.
4. Open the **Homepage** page in Elementor (Pages → Homepage → Edit with
   Elementor, or the **Edit Homepage** tile on Setup's Step 3) to edit
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
