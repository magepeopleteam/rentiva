<?php
/**
 * Bundled demo dataset — the exact 4 rental items, categories, and
 * locations shown in mockup/src/pages/HomePage.tsx and DetailPage.tsx,
 * recreated as real `rbfw_item` posts so the demo importer (importer.php)
 * produces a site that looks identical to the mockup out of the box.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Category terms to seed on `rbfw_item_caregory`, each paired with its demo
 * photo — a filename under assets/images/demo/, bundled with the theme
 * (originally sourced from Unsplash, matching mockup/rentiva.html's
 * "Explore What You Need" cards, then downloaded once and committed as a
 * real theme asset so import never depends on outbound internet access).
 * rentiva_import_demo_content() (inc/demo-import/importer.php) attaches
 * these via rentiva_sideload_demo_photo() into `rentiva_category_image_id`
 * term meta — but only for a category that doesn't already have an image,
 * so a site that's already customized its category photos is never
 * overwritten.
 *
 * @return array<string,string> Category name => filename in assets/images/demo/.
 */
function rentiva_demo_categories() {
	return array(
		'Bicycles'     => 'category-bicycles.jpg',
		'Scooters'     => 'category-scooters.jpg',
		'Camping'      => 'category-camping.jpg',
		'Cameras'      => 'category-cameras.jpg',
		'Water Sports' => 'category-water-sports.jpg',
		'Outdoor Gear' => 'category-outdoor-gear.jpg',
	);
}

/**
 * Location terms to seed on `rbfw_item_location`.
 *
 * @return string[]
 */
function rentiva_demo_locations() {
	return array( 'Dhaka', 'Chittagong', 'Sylhet' );
}

/**
 * Demo `rbfw_item` posts, matching mockup/src/pages/HomePage.tsx's 4
 * products and mockup/src/pages/DetailPage.tsx's full Explorer X1 detail.
 * Each item's `photo` is a filename under assets/images/demo/ (see
 * rentiva_demo_categories() above for why these are bundled files, not
 * live Unsplash ids) — rentiva_import_demo_content()
 * (inc/demo-import/importer.php) attaches it as the item's featured
 * image via rentiva_sideload_demo_photo(), but only when the item doesn't
 * already have one, so re-running import never overwrites a photo an admin
 * has since changed. Each item also gets `rbfw_item_stock_quantity` set to
 * `10` (matching the plugin's own bundled demo importer), so freshly
 * imported items never show as "out of stock".
 *
 * @return array<int,array<string,mixed>>
 */
function rentiva_demo_items() {
	return array(
		array(
			'title'      => 'Explorer X1 Mountain Bike',
			'excerpt'    => 'Premium mountain bike designed for city rides, trails and weekend adventures.',
			'content'    => "The Explorer X1 is our most popular rental. Suited for riders of all experience levels, this bike handles both urban streets and light trails with ease. Maintained weekly and cleaned before every rental so it always arrives in pristine condition.",
			'category'   => 'Bicycles',
			'location'   => 'Dhaka',
			'daily_rate' => 18,
			'weekly_rate' => 85,
			'photo'      => 'item-explorer-x1-mountain-bike.jpg',
			'specs'      => array(
				'Type: Mountain Bike',
				'Gears: 21 Speed',
				'Frame: Aluminum',
				'Weight: 12.5 kg',
				'Suitable For: Adults',
				'Included: Helmet + Lock',
			),
			'included'   => array( 'Helmet', 'Bike Lock', 'Repair Kit', 'Front & Rear Lights' ),
		),
		array(
			'title'      => 'Urban Cruiser',
			'excerpt'    => 'A comfortable city bike built for effortless commuting and weekend errands.',
			'content'    => 'The Urban Cruiser pairs an upright riding position with a lightweight frame, making it the easiest way to get around town.',
			'category'   => 'Bicycles',
			'location'   => 'Dhaka',
			'daily_rate' => 14,
			'weekly_rate' => 0,
			'photo'      => 'item-urban-cruiser.jpg',
			'specs'      => array( 'Type: City Bike', 'Gears: 7 Speed', 'Frame: Steel', 'Suitable For: Adults' ),
			'included'   => array( 'Bike Lock', 'Front Basket' ),
		),
		array(
			'title'      => 'Trail Master',
			'excerpt'    => 'A rugged adventure bike ready for mixed terrain and longer rides.',
			'content'    => 'Trail Master is built for riders who want to go further — hydraulic disc brakes, wide-tread tires, and a durable frame designed for mixed terrain.',
			'category'   => 'Bicycles',
			'location'   => 'Chittagong',
			'daily_rate' => 22,
			'weekly_rate' => 0,
			'photo'      => 'item-trail-master.jpg',
			'specs'      => array( 'Type: Adventure Bike', 'Gears: 24 Speed', 'Frame: Aluminum', 'Suitable For: Adults' ),
			'included'   => array( 'Helmet', 'Repair Kit' ),
		),
		array(
			'title'      => 'Weekend Pro',
			'excerpt'    => 'A versatile hybrid bike suited to both weekday commutes and weekend trails.',
			'content'    => 'Weekend Pro blends road-bike efficiency with light off-road capability, making it a flexible choice for any kind of ride.',
			'category'   => 'Bicycles',
			'location'   => 'Sylhet',
			'daily_rate' => 16,
			'weekly_rate' => 0,
			'photo'      => 'item-weekend-pro.jpg',
			'specs'      => array( 'Type: Hybrid Bike', 'Gears: 18 Speed', 'Frame: Aluminum', 'Suitable For: Adults' ),
			'included'   => array( 'Helmet', 'Bike Lock' ),
		),
	);
}

/**
 * The homepage-widget photos (Hero background, Promo Banner background,
 * Why Rentiva, Testimonial avatar) — bundled files under assets/images/demo/
 * (see rentiva_demo_categories() above). rentiva_import_demo_homepage_images()
 * attaches these into the matching `rentiva_settings` key
 * (`hero_image_id` etc.), which — since inc/integrations/elementor-widgets.php
 * uses that same setting as each widget's live Elementor `default` — is
 * also what makes the Hero/Promo Banner/Why Rentiva/Testimonial widgets
 * show a real, on-brand photo pre-filled the moment they're opened,
 * instead of an empty Photo field. Only attached when that specific
 * setting is still unset, so a site that's already set its own image is
 * never overwritten.
 *
 * @return array<string,string> `rentiva_settings` key => filename in assets/images/demo/.
 */
function rentiva_demo_homepage_images() {
	return array(
		'hero_image_id'         => 'hero.jpg',
		'promo_image_id'        => 'promo.jpg',
		'why_image_id'          => 'why.jpg',
		'testimonial_avatar_id' => 'testimonial-avatar.jpg',
	);
}
