/**
 * Rentiva Settings screen: WP media uploader wiring for every image field.
 */
( function ( $ ) {
	'use strict';

	$( document ).on( 'click', '.rentiva-image-field__select', function ( event ) {
		event.preventDefault();

		var $field = $( this ).closest( '.rentiva-image-field' );
		var $input = $field.find( '.rentiva-image-field__input' );
		var $preview = $field.find( '.rentiva-image-field__preview' );
		var $remove = $field.find( '.rentiva-image-field__remove' );

		var frame = wp.media( {
			title: rentivaAdmin.selectImageTitle,
			multiple: false,
			library: { type: 'image' },
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			$input.val( attachment.id );
			$preview.html( '<img src="' + attachment.url + '" style="max-width:150px;height:auto;" />' );
			$remove.show();
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.rentiva-image-field__remove', function ( event ) {
		event.preventDefault();

		var $field = $( this ).closest( '.rentiva-image-field' );
		$field.find( '.rentiva-image-field__input' ).val( '' );
		$field.find( '.rentiva-image-field__preview' ).empty();
		$( this ).hide();
	} );
} )( jQuery );

/**
 * Theme Settings screen: sidebar tab switching, remembered across page
 * loads (e.g. after a save's redirect back to this same screen) via
 * localStorage — falls back to the first tab whenever storage is
 * unavailable or empty.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var nav = document.querySelector( '.rentiva-settings-nav' );
		if ( ! nav ) {
			return;
		}

		var buttons = nav.querySelectorAll( '.rentiva-settings-nav__item' );
		var tabs = document.querySelectorAll( '.rentiva-settings-tab' );

		function activate( slug ) {
			buttons.forEach( function ( button ) {
				button.classList.toggle( 'is-active', button.getAttribute( 'data-tab' ) === slug );
			} );
			tabs.forEach( function ( tab ) {
				tab.hidden = tab.getAttribute( 'data-tab' ) !== slug;
			} );
			try {
				window.localStorage.setItem( 'rentivaSettingsTab', slug );
			} catch ( error ) {
				// Storage unavailable (private browsing, etc.) — tab still switches, just isn't remembered.
			}
		}

		buttons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				activate( button.getAttribute( 'data-tab' ) );
			} );
		} );

		var stored = null;
		try {
			stored = window.localStorage.getItem( 'rentivaSettingsTab' );
		} catch ( error ) {
			// Ignore — falls back to the first tab below.
		}

		var hasStoredTab = stored && nav.querySelector( '[data-tab="' + stored + '"]' );
		activate( hasStoredTab ? stored : buttons[ 0 ].getAttribute( 'data-tab' ) );
	} );
} )();
