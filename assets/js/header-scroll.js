/**
 * Toggles the fixed header's solid/on-scroll appearance.
 * Mirrors mockup/src/components/Header.tsx: `scrolled = window.scrollY > 40`.
 */
( function () {
	'use strict';

	var header = document.querySelector( '.site-header' );
	if ( ! header ) {
		return;
	}

	var THRESHOLD = 40;

	function updateScrolledState() {
		if ( window.scrollY > THRESHOLD ) {
			header.classList.add( 'is-scrolled' );
		} else {
			header.classList.remove( 'is-scrolled' );
		}
	}

	window.addEventListener( 'scroll', updateScrolledState, { passive: true } );
	updateScrolledState();
} )();
