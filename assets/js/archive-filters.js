/**
 * Archive listing page: small progressive enhancements only — every control
 * here already works with JS disabled (the sort <select> has a real "Apply
 * Filters" button in the same form, and grid/list is a plain link), this
 * just removes the extra click for JS-enabled visitors.
 *
 * @package Rentiva
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var sortSelect = document.getElementById( 'rentiva-archive-sort' );
		if ( sortSelect && sortSelect.form ) {
			sortSelect.addEventListener( 'change', function () {
				sortSelect.form.submit();
			} );
		}
	} );
} )();
