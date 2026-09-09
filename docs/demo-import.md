# Demo importer

**Where:** the welcome notice shown right after activating Rentiva, or
**Rentiva → Setup**'s "Import Demo Content" button (also on its Dashboard
widget) — either shows a success/error notice after running, and the Setup
page additionally shows a persistent "Demo already imported on {date}"
banner once `rentiva_demo_imported_at` (set at the end of
`rentiva_import_demo_content()`) is populated.

**What it does** (`inc/demo-import/importer.php`):

1. Creates the 6 demo categories and 3 demo locations if they don't already
   exist (matched by term name — running it twice never creates duplicates).
2. Creates the 4 demo rental items if a published `rbfw_item` with the same
   title doesn't already exist.
3. Sets each item's pricing meta (`rbfw_item_type`, `rbfw_enable_daily_rate`,
   `rbfw_daily_rate`, etc.) and, where applicable, the Feature List meta
   (`rbfw_feature_category`) for specs/included-items.
4. Sideloads a real photo (via `rentiva_sideload_demo_photo()`, using
   `download_url()` + `media_handle_sideload()` rather than
   `media_sideload_image()`, since Unsplash's URLs carry no file extension
   for the latter to key off) for:
   - each of the 6 demo categories, at `600×700` into
     `rentiva_category_image_id` term meta — matching the "Explore What You
     Need" card crop;
   - each of the 4 demo items, at `600×450`, set as the item's featured
     image — matching the rental-card crop;
   - the 4 homepage-widget photos (Hero, Promo Banner, Why Rentiva,
     Testimonial avatar), at each widget's own registered crop size, into
     `rentiva_settings` (`hero_image_id` etc.) — the same setting the
     Elementor widgets read as their live `default`, so those sections show
     a real photo the moment they're opened.

   Every sideload is gated on "not already set" — a category with an image,
   an item with a featured image, or a `rentiva_settings` key that's already
   populated is left alone, so re-running import (or running it against a
   site whose admin has already picked different photos) never overwrites
   anything. Also sets `rbfw_item_stock_quantity` to `10` on any demo item
   still missing it — matching the plugin's own bundled demo importer —
   so items never show as "out of stock" out of the box. Both the photo and
   stock-quantity backfill run for existing demo items too (matched by
   title), not only newly-created ones, so upgrading from an older version
   of this importer still fills in whatever was missing.
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
error notice instead of silently doing nothing if it's missing. Photo
sideloading needs outbound internet access to `images.unsplash.com`; if a
fetch fails (offline, blocked, or a photo id no longer resolving),
`rentiva_sideload_demo_photo()` returns `0` and the import continues
without that one photo rather than failing the whole run.

**Removing demo content:** delete the imported `rbfw_item` posts and terms
from the plugin's own admin screens like any other content — the importer
does not tag them specially or track them for automated removal.
