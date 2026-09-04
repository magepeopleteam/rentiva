/**
 * Single-item gallery: clicking a thumbnail swaps the main image and marks
 * the active thumbnail — matches mockup/src/pages/DetailPage.tsx's gallery.
 */
( function () {
	'use strict';

	var mainImage = document.querySelector( '.js-rentiva-gallery-main' );
	var thumbs    = document.querySelectorAll( '.js-rentiva-gallery-thumb' );

	if ( ! mainImage || ! thumbs.length ) {
		return;
	}

	thumbs.forEach( function ( thumb ) {
		thumb.addEventListener( 'click', function () {
			var fullSrc = thumb.getAttribute( 'data-full-src' );
			if ( fullSrc && 'IMG' === mainImage.tagName ) {
				mainImage.setAttribute( 'src', fullSrc );
			}

			thumbs.forEach( function ( other ) {
				other.classList.toggle( 'is-active', other === thumb );
			} );
		} );
	} );
} )();
