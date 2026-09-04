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
