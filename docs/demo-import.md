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

**Safety:** the button is a real `admin-post.php` action gated by
`current_user_can( 'edit_theme_options' )` and a WordPress nonce
(`check_admin_referer( 'rentiva_import_demo' )`) — it is not a bare link, so
it can't be triggered by a stray GET request (CSRF-safe).

**Requirements:** Booking and Rental Manager for WooCommerce must be active
— the button/notice checks `rentiva_has_booking_plugin()` first and shows an
error notice instead of silently doing nothing if it's missing.

**Removing demo content:** delete the imported `rbfw_item` posts and terms
from the plugin's own admin screens like any other content — the importer
does not tag them specially or track them for automated removal.
