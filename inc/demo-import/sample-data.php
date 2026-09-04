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
 * Category terms to seed on `rbfw_item_caregory`.
 *
 * @return string[]
 */
function rentiva_demo_categories() {
	return array( 'Bicycles', 'Scooters', 'Camping', 'Cameras', 'Water Sports', 'Outdoor Gear' );
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
			'specs'      => array( 'Type: Hybrid Bike', 'Gears: 18 Speed', 'Frame: Aluminum', 'Suitable For: Adults' ),
			'included'   => array( 'Helmet', 'Bike Lock' ),
		),
	);
}
