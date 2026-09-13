<?php
/**
 * "Rent in 3 Simple Steps" — Find / Book / Enjoy.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// $args['steps'] — a full replacement list when rendering as the Rentiva:
// How It Works Elementor widget's repeater (its own add/remove/reorder rows
// wholly define the set; only an entirely empty repeater falls back to the
// built-in Find/Book/Enjoy steps) — see Rentiva_Elementor_Widget_How_It_Works::render().
// The repeater only collects title/desc; the step number is always the row's
// own position, auto-formatted "01", "02", ... so reordering never leaves a
// stale/duplicate number behind.
// Fallback icon cycle for the Elementor widget's repeater rows, which only
// collect title/desc (no icon field) — see the comment above. The built-in
// Find/Book/Enjoy steps carry their own icon per step instead.
$rentiva_step_icons = array( 'search', 'calendar', 'check' );

if ( ! empty( $args['steps'] ) && is_array( $args['steps'] ) ) {
	$rentiva_steps = array();
	foreach ( array_values( $args['steps'] ) as $rentiva_index => $rentiva_step ) {
		$rentiva_steps[] = array(
			'num'   => sprintf( '%02d', $rentiva_index + 1 ),
			'title' => $rentiva_step['title'] ?? '',
			'desc'  => $rentiva_step['desc'] ?? '',
			'icon'  => $rentiva_step_icons[ $rentiva_index % count( $rentiva_step_icons ) ],
		);
	}
} else {
	$rentiva_steps = array();
	foreach ( rentiva_get_default_how_it_works_steps() as $rentiva_index => $rentiva_step ) {
		$rentiva_steps[] = array_merge( array( 'num' => sprintf( '%02d', $rentiva_index + 1 ) ), $rentiva_step );
	}
}

$rentiva_heading = ! empty( $args['heading'] ) ? $args['heading'] : __( 'Rent in 3 Simple Steps', 'rentiva' );
?>
<section class="rentiva-section--lg rentiva-how-it-works" id="how-it-works">
	<div class="rentiva-container">
		<div class="rentiva-how-it-works__head">
			<h2 class="rentiva-h2"><?php echo esc_html( $rentiva_heading ); ?></h2>
		</div>

		<div class="rentiva-how-it-works__steps">
			<?php foreach ( $rentiva_steps as $rentiva_step ) : ?>
				<div class="rentiva-how-it-works__step">
					<span class="rentiva-how-it-works__number"><?php echo esc_html( $rentiva_step['num'] ); ?></span>
					<div class="rentiva-how-it-works__icon">
						<?php echo rentiva_get_icon( $rentiva_step['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted local SVG, see rentiva_get_icon(). ?>
					</div>
					<h3 class="rentiva-h4"><?php echo esc_html( $rentiva_step['title'] ); ?></h3>
					<p class="rentiva-body"><?php echo esc_html( $rentiva_step['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
