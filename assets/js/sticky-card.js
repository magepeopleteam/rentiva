/**
 * Hides the fixed mobile booking bar once the real sticky booking card has
 * scrolled into view, so mobile visitors never see two competing
 * "Reserve"/price CTAs at once.
 */
( function () {
	'use strict';

	var bar  = document.querySelector( '.rentiva-mobile-booking-bar' );
	var card = document.getElementById( 'rentiva-booking-card' );

	if ( ! bar || ! card || typeof IntersectionObserver === 'undefined' ) {
		return;
	}

	var observer = new IntersectionObserver(
		function ( entries ) {
			entries.forEach( function ( entry ) {
				bar.classList.toggle( 'is-hidden', entry.isIntersecting );
			} );
		},
		{ rootMargin: '0px 0px -40% 0px' }
	);

	observer.observe( card );
} )();
