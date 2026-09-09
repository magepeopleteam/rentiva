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
 * Category terms to seed on `rbfw_item_caregory`, each paired with the
 * exact Unsplash photo id mockup/rentiva.html itself uses for that
 * category's "Explore What You Need" card — so a fresh demo import looks
 * identical to the mockup's photos too, not just its copy.
 * rentiva_import_demo_content() (inc/demo-import/importer.php) sideloads
 * these via rentiva_sideload_demo_photo() at `600×700` (that card's own
 * crop ratio, matching the mockup's own `w=600&h=700` params exactly) into
 * `rentiva_category_image_id` term meta — but only for a category that
 * doesn't already have an image, so a site that's already customized its
 * category photos is never overwritten.
 *
 * @return array<string,string> Category name => Unsplash photo id.
 */
function rentiva_demo_categories() {
	return array(
		'Bicycles'     => 'photo-1485965120184-e220f721d03e',
		'Scooters'     => 'photo-1558981403-c5f9899a28bc',
		'Camping'      => 'photo-1504280390367-361c6d9f38f4',
		'Cameras'      => 'photo-1516035069371-29a1b244cc32',
		'Water Sports' => 'photo-1507525428034-b723cf961d3e',
		'Outdoor Gear' => 'photo-1551632811-561732d1e306',
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
 * Each item's `photo` is the exact Unsplash photo id mockup/rentiva.html
 * itself uses for that item's rental card — rentiva_import_demo_content()
 * (inc/demo-import/importer.php) sideloads it as the item's featured
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
			'photo'      => 'photo-1571068316344-75bc76f77890',
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
			'photo'      => 'photo-1532298229144-0ec0c57515c7',
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
			'photo'      => 'photo-1558618666-fcd25c85cd64',
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
			'photo'      => 'photo-1485965120184-e220f721d03e',
			'specs'      => array( 'Type: Hybrid Bike', 'Gears: 18 Speed', 'Frame: Aluminum', 'Suitable For: Adults' ),
			'included'   => array( 'Helmet', 'Bike Lock' ),
		),
	);
}

/**
 * The homepage-widget photos (Hero background, Promo Banner background,
 * Why Rentiva, Testimonial avatar) — the exact Unsplash photo id
 * mockup/rentiva.html itself uses for each. rentiva_import_demo_homepage_images()
 * sideloads these into the matching `rentiva_settings` key
 * (`hero_image_id` etc.), which — since inc/integrations/elementor-widgets.php
 * uses that same setting as each widget's live Elementor `default` — is
 * also what makes the Hero/Promo Banner/Why Rentiva/Testimonial widgets
 * show a real, on-brand photo pre-filled the moment they're opened,
 * instead of an empty Photo field. Only sideloaded when that specific
 * setting is still unset, so a site that's already set its own image is
 * never overwritten.
 *
 * @return array<string,string> `rentiva_settings` key => Unsplash photo id.
 */
function rentiva_demo_homepage_images() {
	return array(
		'hero_image_id'         => 'photo-1476041800959-2f6bb412c8ce',
		'promo_image_id'        => 'photo-1464822759023-fed622ff2c3b',
		'why_image_id'          => 'photo-1506905925346-21bda4d32df4',
		'testimonial_avatar_id' => 'photo-1500648767791-00dcc994a43e',
	);
}
