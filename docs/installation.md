# Installation

## Requirements

| Plugin | Required? | Role | Installed from |
|---|---|---|---|
| Elementor | Required | Powers the 9 homepage-section widgets (docs/elementor.md) | WordPress.org |
| Booking and Rental Manager for WooCommerce (free) | Required | Rental items, pricing, availability, booking form | WordPress.org |
| Booking and Rental Manager Pro | Optional | Premium add-on features | Upload the zip from your purchase |
| WooCommerce | Optional | Only if you want WooCommerce cart/checkout instead of the booking plugin's native checkout | WordPress.org |

Activating Rentiva opens **Rentiva → Setup**, whose Step 1 installs and
activates each of these in place — **Install Now** for the WordPress.org
plugins, **Upload & Install** for Pro — without leaving the screen.

## Steps

1. Upload the `rentiva` folder to `wp-content/themes/`, or install the
   theme .zip via **Appearance → Themes → Add New → Upload Theme**.
2. Activate Rentiva — you land on **Rentiva → Setup**.
3. Follow docs/getting-started.md.

## PHP/WordPress requirements

- WordPress 6.4+
- PHP 7.4+

## Uninstalling / switching themes

Rentiva stores exactly one settings row (`rentiva_settings` — see
docs/theme-settings.md) plus the demo content it created via the importer
(real `rbfw_item` posts and taxonomy terms, owned by the booking plugin, not
the theme). Switching themes does not delete your rental catalog.
