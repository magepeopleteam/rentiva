/**
 * Decorates the booking plugin's real quantity <select> (name=
 * "rbfw_item_quantity") with a −/+ stepper UI matching the mockup, WITHOUT
 * replacing it: the native select stays in the DOM (visually hidden) so the
 * plugin's own price-recalculation listener keeps receiving real `change`
 * events exactly as it would from the plugin's own dropdown.
 */
( function () {
	'use strict';

	function decorate( select ) {
		if ( select.dataset.rentivaDecorated ) {
			return;
		}
		select.dataset.rentivaDecorated = 'true';

		var options = Array.prototype.slice.call( select.options );
		if ( options.length < 2 ) {
			return; // Nothing to step between.
		}

		select.classList.add( 'rentiva-qty-stepper__native' );

		var wrapper = document.createElement( 'div' );
		wrapper.className = 'rentiva-qty-stepper';

		var minus = document.createElement( 'button' );
		minus.type = 'button';
		minus.className = 'rentiva-qty-stepper__btn';
		minus.setAttribute( 'aria-label', 'Decrease quantity' );
		minus.textContent = '−';

		var value = document.createElement( 'span' );
		value.className = 'rentiva-qty-stepper__value';

		var plus = document.createElement( 'button' );
		plus.type = 'button';
		plus.className = 'rentiva-qty-stepper__btn';
		plus.setAttribute( 'aria-label', 'Increase quantity' );
		plus.textContent = '+';

		function sync() {
			value.textContent = select.options[ select.selectedIndex ] ? select.options[ select.selectedIndex ].text : select.value;
			minus.disabled = 0 === select.selectedIndex;
			plus.disabled = select.selectedIndex >= select.options.length - 1;
		}

		function step( delta ) {
			var nextIndex = select.selectedIndex + delta;
			if ( nextIndex < 0 || nextIndex >= select.options.length ) {
				return;
			}
			select.selectedIndex = nextIndex;
			sync();
			select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		}

		minus.addEventListener( 'click', function () {
			step( -1 );
		} );
		plus.addEventListener( 'click', function () {
			step( 1 );
		} );

		select.parentNode.insertBefore( wrapper, select );
		wrapper.appendChild( minus );
		wrapper.appendChild( value );
		wrapper.appendChild( plus );
		wrapper.appendChild( select );

		sync();
	}

	function init() {
		document.querySelectorAll( 'select[name="rbfw_item_quantity"]' ).forEach( decorate );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
