<?php
/**
 * "Rent in 3 Simple Steps" — Find / Book / Enjoy.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_steps = array(
	array(
		'num'   => '01',
		'title' => __( 'Find', 'rentiva' ),
		'desc'  => __( 'Discover the perfect equipment near you.', 'rentiva' ),
	),
	array(
		'num'   => '02',
		'title' => __( 'Book', 'rentiva' ),
		'desc'  => __( 'Choose your dates and reserve in seconds.', 'rentiva' ),
	),
	array(
		'num'   => '03',
		'title' => __( 'Enjoy', 'rentiva' ),
		'desc'  => __( 'Pick it up and start your adventure.', 'rentiva' ),
	),
);
?>
<section class="rentiva-section--lg rentiva-how-it-works" id="how-it-works">
	<div class="rentiva-container">
		<div class="rentiva-how-it-works__head">
			<h2 class="rentiva-h2"><?php esc_html_e( 'Rent in 3 Simple Steps', 'rentiva' ); ?></h2>
		</div>

		<div class="rentiva-how-it-works__steps">
			<?php foreach ( $rentiva_steps as $rentiva_step ) : ?>
				<div class="rentiva-how-it-works__step">
					<div class="rentiva-how-it-works__number">
						<span><?php echo esc_html( $rentiva_step['num'] ); ?></span>
					</div>
					<h3 class="rentiva-h4"><?php echo esc_html( $rentiva_step['title'] ); ?></h3>
					<p class="rentiva-body"><?php echo esc_html( $rentiva_step['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
