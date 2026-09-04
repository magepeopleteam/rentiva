/**
 * Wishlist heart toggle on rental cards. RBFW has no native wishlist
 * feature, so this is a lightweight, per-browser localStorage list —
 * it never touches cart/checkout state.
 */
( function () {
	'use strict';

	var STORAGE_KEY = 'rentivaFavorites';

	function readFavorites() {
		try {
			var raw = window.localStorage.getItem( STORAGE_KEY );
			return raw ? JSON.parse( raw ) : [];
		} catch ( e ) {
			return [];
		}
	}

	function writeFavorites( ids ) {
		try {
			window.localStorage.setItem( STORAGE_KEY, JSON.stringify( ids ) );
		} catch ( e ) {
			// Storage unavailable (private browsing, blocked site data) — fail silently.
		}
	}

	function applyState( button, isActive ) {
		button.classList.toggle( 'is-active', isActive );
		button.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
	}

	function init() {
		var favorites = readFavorites();

		document.querySelectorAll( '.js-rentiva-favorite' ).forEach( function ( button ) {
			var id = button.getAttribute( 'data-rentiva-favorite-id' );
			if ( ! id ) {
				return;
			}

			applyState( button, favorites.indexOf( id ) !== -1 );

			button.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				event.stopPropagation();

				var current = readFavorites();
				var index   = current.indexOf( id );
				var active;

				if ( index === -1 ) {
					current.push( id );
					active = true;
				} else {
					current.splice( index, 1 );
					active = false;
				}

				writeFavorites( current );
				applyState( button, active );
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
