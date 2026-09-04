/**
 * Mobile hamburger dropdown toggle for the site header.
 */
( function () {
	'use strict';

	var toggle = document.querySelector( '.site-header__toggle' );
	var menu   = document.querySelector( '.site-header__mobile-menu' );

	if ( ! toggle || ! menu ) {
		return;
	}

	toggle.addEventListener( 'click', function () {
		var isOpen = menu.classList.toggle( 'is-open' );
		toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
	} );

	menu.querySelectorAll( 'a' ).forEach( function ( link ) {
		link.addEventListener( 'click', function () {
			menu.classList.remove( 'is-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );
		} );
	} );
} )();
