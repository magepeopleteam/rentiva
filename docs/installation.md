# Installation

## Requirements

| Plugin | Role |
|---|---|
| WooCommerce | Cart, checkout, orders |
| Booking and Rental Manager for WooCommerce | Rental items, pricing, availability, booking form |
| Elementor | Powers the 9 homepage-section widgets (docs/elementor.md) |

Rentiva declares all three under `style.css`'s `Requires Plugins` header, so
WordPress will prompt you to install/activate them from the themes screen
if any are missing.

## Steps

1. Upload the `rentiva` folder to `wp-content/themes/`, or install the
   theme .zip via **Appearance → Themes → Add New → Upload Theme**.
2. Install/activate WooCommerce, Booking and Rental Manager for WooCommerce,
   and Elementor if you haven't already.
3. Activate Rentiva.
4. Follow docs/getting-started.md.

## PHP/WordPress requirements

- WordPress 6.4+
- PHP 7.4+

## Uninstalling / switching themes

Rentiva stores exactly one settings row (`rentiva_settings` — see
docs/theme-settings.md) plus the demo content it created via the importer
(real `rbfw_item` posts and taxonomy terms, owned by the booking plugin, not
the theme). Switching themes does not delete your rental catalog.
