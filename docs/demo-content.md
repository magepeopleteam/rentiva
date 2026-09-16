# Demo content

The bundled demo dataset (`inc/demo-import/sample-data.php`) matches the
approved mockup design exactly:

**Categories (6):** Bicycles, Scooters, Camping, Cameras, Water Sports,
Outdoor Gear.

**Locations (3):** Dhaka, Chittagong, Sylhet.

**Rental items (8):**

| Item | Category | Location | Pricing |
| --- | --- | --- | --- |
| Explorer X1 Mountain Bike | Bicycles | Dhaka | $18/day, $85/week |
| Urban Cruiser | Bicycles | Dhaka | $14/day |
| Trail Master | Bicycles | Chittagong | $22/day |
| Weekend Pro | Bicycles | Sylhet | $16/day |
| City Glide E-Scooter | Scooters | Dhaka | $12/day, $60/week |
| Capture Pro DSLR Kit | Cameras | Chittagong | $30/day, $160/week |
| Alpine 2-Person Tent | Camping | Sylhet | $15/day, $75/week |
| Sunset Paddleboard Duo | Water Sports | Chittagong | $20/day |

All are real `rbfw_item` posts with real pricing meta, so they behave exactly
like any other rental item (bookable, priced, filterable by
category/location), and each is seeded with `rbfw_item_stock_quantity` of
`10` so nothing shows as out of stock out of the box.

**Why 8 and not 4:** the homepage's Popular Rentals grid
(`template-parts/home/popular-rentals.php`) is 4 columns wide and asks for 8
cards, so the demo catalog has to be at least that large or the section
renders a single half-empty row. The 4 non-bicycle items also spread the
catalog across 5 of the 6 seeded categories, so a freshly imported site
doesn't look like a single-category shop. Outdoor Gear is seeded as a
category (with its own photo) but intentionally has no items — the
"Explore What You Need" section is driven by categories, not item counts.

Every item ships a full "Specifications" and "What's Included" Feature List
entry (`rbfw_feature_category`) plus 5 FAQ entries (`mep_event_faq`), so any
item you open is a fully fleshed-out example to model your own catalog on.

**Photos are bundled.** 18 images live in `assets/images/demo/` — one per
category (6), one per item (8), and the four homepage-widget photos (Hero,
Promo Banner, Why Rentiva, Testimonial avatar). They were originally sourced
from Unsplash, then downloaded once and committed as real theme assets, so
importing never depends on outbound internet access and the homepage and
catalog look complete the moment the importer finishes.

See docs/demo-import.md for how to run/re-run the importer.
