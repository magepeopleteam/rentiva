# Demo importer

**Where:** the "Import Demo Content" button on Step 2 (Import) of the
**Rentiva → Setup** wizard (`admin.php?page=rentiva-settings&step=demo`),
which opens automatically right after activating Rentiva, or the same
button on its Dashboard widget.

**How it runs.** The import is a fixed list of small steps
(`rentiva_get_demo_import_steps()`): one per category, locations, one per
rental item, menus, one per homepage photo, then `finish`. Each step
sideloads at most one photo, so no single request can hit a PHP
execution-time limit on a slow host.

- **Step 2 button (JS):** `assets/js/admin-setup.js` runs the steps one
  AJAX request at a time (`wp_ajax_rentiva_import_demo_step`) with a live
  progress bar and per-group checklist — no page reload. When done, Step 2's
  badge, "Demo already imported" banner, and Skip/Continue button update in
  place. If a request fails the import stops with a **Retry** button that
  resumes from the failed step (or re-runs everything if the final check
  failed).
- **No JS / Dashboard widget:** the same form posts to `admin-post.php`,
  which runs every step in one request (`rentiva_import_demo_content()`) and
  returns to Step 2 with a notice.
- **Activation auto-provisioning** (`rentiva_maybe_auto_provision_demo()`)
  also runs `rentiva_import_demo_content()`.

**Only when the booking plugin is really loaded.** Every entry point checks
`rentiva_demo_import_ready()` — the `rbfw_item` post type *and* both
taxonomies. Booking and Rental Manager deliberately skips registering its
taxonomies under WP-CLI (`admin/taxonomy_register.php`) while still
registering the post type, so an import from WP-CLI would otherwise create
every item with no category or location. Auto-provisioning skips instead,
and the Step 2 button imports correctly from wp-admin.

**Marked done only when it is.** The `finish` step runs
`rentiva_get_demo_import_problems()` — every demo category (with its
image), location, and rental item (with its category, location, and photo)
must really exist — and only then sets `rentiva_demo_imported_at`. If
something is still missing it reports exactly what instead. Step 2 runs the
same check on page load: a site an earlier import left incomplete shows an
**Incomplete** badge and the list of problems.

**What it does** (`inc/demo-import/importer.php`):

1. Creates the 6 demo categories and 3 demo locations if they don't already
   exist (matched by term name — running it twice never creates duplicates).
2. Creates the 8 demo rental items if a published `rbfw_item` with the same
   title doesn't already exist.
3. Sets each item's pricing meta (`rbfw_item_type`, `rbfw_enable_daily_rate`,
   `rbfw_daily_rate`, etc.) and, where applicable, the Feature List meta
   (`rbfw_feature_category`) for specs/included-items.
4. Attaches a real photo (via `rentiva_sideload_demo_photo()`, which copies
   the bundled file out of `assets/images/demo/` into a temp file and hands
   it to `media_handle_sideload()`) for:
   - each of the 6 demo categories, into `rentiva_category_image_id` term
     meta — feeding the "Explore What You Need" cards;
   - each of the 8 demo items, set as the item's featured image — feeding
     the rental cards;
   - the 4 homepage-widget photos (Hero, Promo Banner, Why Rentiva,
     Testimonial avatar), into `rentiva_settings` (`hero_image_id` etc.) —
     the same setting the Elementor widgets read as their live `default`, so
     those sections show a real photo the moment they're opened.

   Every sideload is gated on "no valid image yet" — a category, item, or
   `rentiva_settings` key whose image still exists (attachment row *and*
   file on disk, `rentiva_demo_attachment_is_valid()`) is left alone, so
   re-running import (or running it against a site whose admin has already
   picked different photos) never overwrites anything; a stale id whose
   attachment or file is gone gets a fresh photo. Also sets
   `rbfw_item_stock_quantity` to `10` on any demo item still missing it —
   matching the plugin's own bundled demo importer — so items never show as
   "out of stock" out of the box.

   **Re-running import repairs.** Existing demo items (matched by exact
   title) go through the same fill-what's-missing path as new ones: a
   missing category or location term, `rbfw_categories`, item type, rates,
   Feature List, stock, FAQs, or photo is filled in; anything already set —
   including a price an admin changed — is kept.
5. Creates 4 real, editable nav menus — "Primary Navigation", "Footer —
   Explore", "Footer — Company", "Footer — Support" — populated from the
   theme's own default nav items (`rentiva_default_primary_nav_items()` /
   `rentiva_default_footer_nav_items()`, `inc/template-functions.php` — the
   exact same items the header/footer already show as a PHP-only fallback
   when no menu is assigned) and assigns each to its matching theme
   location (`rentiva_import_demo_menus()`). Without this, Appearance →
   Menus would show nothing at all to click and edit even though the
   header/footer look fully populated, since that content only ever
   existed as render-time fallback markup, never as real menu data — a
   fresh install (or a reset one) would otherwise look "complete" but
   leave an admin with no visible way to customize it. "Footer — Explore"
   uses the same featured-category list as the homepage's "Explore What
   You Need" section (`rentiva_get_homepage_categories()`), not an
   unordered `get_terms()` call, so it always shows the theme's intended 4
   categories rather than whatever a site happens to have lying around.
   Safe to run more than once: an existing menu with the same name is
   reused (never duplicated), items already present (matched by title) are
   skipped, and a theme location that already has *any* menu assigned —
   even one the admin picked themselves — is left alone rather than
   reassigned.

**Safety:** the button is a real `admin-post.php` action gated by
`current_user_can( 'edit_theme_options' )` and a WordPress nonce
(`check_admin_referer( 'rentiva_import_demo' )`) — it is not a bare link, so
it can't be triggered by a stray GET request (CSRF-safe).

**Requirements:** Booking and Rental Manager for WooCommerce must be active
— the button/notice checks `rentiva_has_booking_plugin()` first and shows an
error notice instead of silently doing nothing if it's missing. Photos need
no network access: all 18 are committed under `assets/images/demo/` and read
from disk. If one is missing or unreadable, `rentiva_sideload_demo_photo()`
returns `0` and the import continues without that one photo rather than
failing the whole run.

**Removing demo content:** delete the imported `rbfw_item` posts and terms
from the plugin's own admin screens like any other content — the importer
does not tag them specially or track them for automated removal.
