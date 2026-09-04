=== Rentiva ===

Contributors: rentivateam
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: e-commerce, booking, rental, block-patterns, custom-logo, custom-menu, editor-style, featured-images, full-width-template, rtl-language-support, threaded-comments, translation-ready, wide-blocks

A premium equipment & vehicle rental marketplace theme — bikes, scooters, cameras, camping gear, water sports and outdoor equipment.

== Description ==

Rentiva is a presentation-only theme: it never stores, prices, or books
anything itself. Every rentable item, price, availability window, and
checkout is handled by "Booking and Rental Manager for WooCommerce"
(required) together with WooCommerce (required). Rentiva's job is the
homepage, the browse/category pages, and the single-item page's layout —
plus a full set of Elementor widgets (Elementor required) mirroring every
homepage section, so the same sections can be reused to build other pages.

See docs/architecture.md for the full separation-of-concerns model and
docs/booking-integration.md for exactly how the theme talks to the booking
plugin without ever duplicating its pricing or availability logic.

== Requirements ==

* WooCommerce
* Booking and Rental Manager for WooCommerce (`booking-and-rental-manager-for-woocommerce`)
* Elementor

== Installation ==

See docs/installation.md.

== Changelog ==

= 1.0.0 =
* Initial release.

== Credits ==

* Underlying JavaScript/CSS is hand-written for this theme; no third-party
  libraries are bundled.
* Design based on the "Rentiva" mockup design brief (mockup/ directory,
  kept in the theme package as a design reference — not used at runtime).
