/**
 * Item-name autocomplete for the hero search panel and the archive "Search"
 * filter. Progressive enhancement only — every host input already posts a
 * plain `s` search to the rentals archive on submit, so a slow/failed AJAX
 * call (or JS disabled entirely) never breaks the search, it just loses the
 * live suggestions.
 *
 * Markup contract per `[data-rentiva-autocomplete="items"]` field:
 *   - one `input[role="combobox"]` with `aria-controls` pointing at
 *   - one sibling `ul[role="listbox"]`, initially `hidden`
 *   - an optional `.rentiva-autocomplete-clear` button inside the same
 *     `.rentiva-field__input-wrap`
 *
 * @package Rentiva
 */
( function () {
	'use strict';

	if ( 'undefined' === typeof rentivaSearch ) {
		return;
	}

	var MIN_CHARS = parseInt( rentivaSearch.minChars, 10 ) || 2;
	var DEBOUNCE_MS = 220;

	/**
	 * @param {Function} fn
	 * @param {number}   wait
	 * @return {Function}
	 */
	function debounce( fn, wait ) {
		var timer;
		return function () {
			var context = this;
			var args = arguments;
			window.clearTimeout( timer );
			timer = window.setTimeout( function () {
				fn.apply( context, args );
			}, wait );
		};
	}

	/**
	 * Builds one suggestion <li>, bolding the typed term inside the title
	 * via textContent-built <mark> nodes only — nothing here is ever
	 * assigned through innerHTML, so a title containing HTML-looking text
	 * can't inject markup.
	 *
	 * @param {Object} item
	 * @param {string} term
	 * @param {string} idPrefix
	 * @param {number} index
	 * @return {HTMLLIElement}
	 */
	function buildItem( item, term, idPrefix, index ) {
		var li = document.createElement( 'li' );
		li.className = 'rentiva-autocomplete-item';
		li.id = idPrefix + '-option-' + index;
		li.setAttribute( 'role', 'option' );
		li.setAttribute( 'tabindex', '-1' );
		li.dataset.url = item.url || '';

		var thumb = document.createElement( 'span' );
		thumb.className = 'rentiva-autocomplete-item__thumb';
		if ( item.image ) {
			var img = document.createElement( 'img' );
			img.src = item.image;
			img.alt = '';
			img.loading = 'lazy';
			thumb.appendChild( img );
		} else {
			thumb.innerHTML = rentivaSearchIcon(); // Static local SVG string, not user data — see below.
		}

		var body = document.createElement( 'span' );
		body.className = 'rentiva-autocomplete-item__body';

		var title = document.createElement( 'span' );
		title.className = 'rentiva-autocomplete-item__title';
		appendHighlighted( title, item.title || '', term );

		body.appendChild( title );

		if ( item.type || item.price ) {
			var meta = document.createElement( 'span' );
			meta.className = 'rentiva-autocomplete-item__meta';
			meta.textContent = [ item.type, item.price ].filter( Boolean ).join( ' · ' );
			body.appendChild( meta );
		}

		li.appendChild( thumb );
		li.appendChild( body );

		return li;
	}

	/**
	 * Fallback glyph used only when an item has no thumbnail — a fixed,
	 * hardcoded SVG string (never interpolated with any request data), so
	 * it's safe to assign via innerHTML.
	 *
	 * @return {string}
	 */
	function rentivaSearchIcon() {
		return '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="7" cy="7" r="5"/><path d="M11 11l3.5 3.5"/></svg>';
	}

	/**
	 * Splits `text` on the first case-insensitive match of `term` and
	 * appends it to `target` as plain text nodes plus one <mark>, so the
	 * match is bolded without ever touching innerHTML.
	 *
	 * @param {HTMLElement} target
	 * @param {string}      text
	 * @param {string}      term
	 */
	function appendHighlighted( target, text, term ) {
		var term_ = term.trim();
		if ( ! term_ ) {
			target.textContent = text;
			return;
		}

		var lower = text.toLowerCase();
		var at = lower.indexOf( term_.toLowerCase() );
		if ( -1 === at ) {
			target.textContent = text;
			return;
		}

		target.appendChild( document.createTextNode( text.slice( 0, at ) ) );
		var mark = document.createElement( 'mark' );
		mark.textContent = text.slice( at, at + term_.length );
		target.appendChild( mark );
		target.appendChild( document.createTextNode( text.slice( at + term_.length ) ) );
	}

	/**
	 * Wires one autocomplete field.
	 *
	 * @param {HTMLElement} field `[data-rentiva-autocomplete="items"]` wrapper.
	 */
	function initField( field ) {
		var input = field.querySelector( 'input[role="combobox"]' );
		var list = field.querySelector( 'ul[role="listbox"]' );
		var clearBtn = field.querySelector( '.rentiva-autocomplete-clear' );
		var form = field.closest( 'form' );

		if ( ! input || ! list ) {
			return;
		}

		var activeIndex = -1;
		var requestToken = 0;
		var currentTerm = '';

		function toggleClear() {
			if ( ! clearBtn ) {
				return;
			}
			clearBtn.hidden = ! input.value;
		}

		function closeList() {
			list.hidden = true;
			list.innerHTML = '';
			input.setAttribute( 'aria-expanded', 'false' );
			input.removeAttribute( 'aria-activedescendant' );
			activeIndex = -1;
		}

		function openList() {
			list.hidden = false;
			input.setAttribute( 'aria-expanded', 'true' );
		}

		function setStatus( message ) {
			list.innerHTML = '';
			var li = document.createElement( 'li' );
			li.className = 'rentiva-autocomplete-status';
			li.setAttribute( 'aria-live', 'polite' );
			li.textContent = message;
			list.appendChild( li );
			openList();
		}

		function highlight( index ) {
			var options = list.querySelectorAll( '[role="option"]' );
			if ( ! options.length ) {
				return;
			}
			options.forEach( function ( option, i ) {
				option.classList.toggle( 'is-active', i === index );
			} );
			activeIndex = index;
			input.setAttribute( 'aria-activedescendant', options[ index ].id );
			options[ index ].scrollIntoView( { block: 'nearest' } );
		}

		function goTo( url ) {
			if ( url ) {
				window.location.href = url;
			}
		}

		function render( items, term ) {
			list.innerHTML = '';
			activeIndex = -1;

			if ( ! items.length ) {
				setStatus( rentivaSearch.i18nNoResults.replace( '%s', term ) );
				return;
			}

			items.forEach( function ( item, index ) {
				list.appendChild( buildItem( item, term, list.id, index ) );
			} );

			if ( form ) {
				var seeAll = document.createElement( 'li' );
				seeAll.className = 'rentiva-autocomplete-item rentiva-autocomplete-item--all';
				seeAll.setAttribute( 'role', 'option' );
				seeAll.id = list.id + '-option-all';
				seeAll.setAttribute( 'tabindex', '-1' );
				seeAll.textContent = rentivaSearch.i18nSeeAll.replace( '%s', term );
				list.appendChild( seeAll );
			}

			openList();
		}

		var fetchSuggestions = debounce( function ( term ) {
			currentTerm = term;

			if ( term.length < MIN_CHARS ) {
				closeList();
				return;
			}

			var token = ++requestToken;
			setStatus( rentivaSearch.i18nSearching );

			var url = rentivaSearch.ajaxUrl +
				'?action=rentiva_search_suggest' +
				'&nonce=' + encodeURIComponent( rentivaSearch.nonce ) +
				'&term=' + encodeURIComponent( term );

			window.fetch( url, { credentials: 'same-origin' } )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( response ) {
					if ( token !== requestToken ) {
						return; // A newer keystroke already superseded this request.
					}
					var items = ( response && response.success && Array.isArray( response.data ) ) ? response.data : [];
					render( items, term );
				} )
				.catch( function () {
					if ( token === requestToken ) {
						closeList();
					}
				} );
		}, DEBOUNCE_MS );

		input.addEventListener( 'input', function () {
			toggleClear();
			fetchSuggestions( input.value.trim() );
		} );

		input.addEventListener( 'focus', function () {
			if ( input.value.trim().length >= MIN_CHARS && list.children.length ) {
				openList();
			}
		} );

		input.addEventListener( 'keydown', function ( event ) {
			var options = list.querySelectorAll( '[role="option"]' );

			if ( 'Escape' === event.key ) {
				closeList();
				return;
			}

			if ( list.hidden || ! options.length ) {
				return;
			}

			if ( 'ArrowDown' === event.key ) {
				event.preventDefault();
				highlight( ( activeIndex + 1 ) % options.length );
			} else if ( 'ArrowUp' === event.key ) {
				event.preventDefault();
				highlight( ( activeIndex - 1 + options.length ) % options.length );
			} else if ( 'Enter' === event.key && activeIndex > -1 ) {
				event.preventDefault();
				var chosen = options[ activeIndex ];
				if ( chosen.classList.contains( 'rentiva-autocomplete-item--all' ) ) {
					closeList();
					if ( form ) {
						form.submit();
					}
				} else {
					goTo( chosen.dataset.url );
				}
			}
		} );

		list.addEventListener( 'click', function ( event ) {
			var option = event.target.closest( '[role="option"]' );
			if ( ! option ) {
				return;
			}
			if ( option.classList.contains( 'rentiva-autocomplete-item--all' ) ) {
				closeList();
				if ( form ) {
					form.submit();
				}
				return;
			}
			goTo( option.dataset.url );
		} );

		if ( clearBtn ) {
			clearBtn.addEventListener( 'click', function () {
				input.value = '';
				toggleClear();
				closeList();
				input.focus();
			} );
		}

		document.addEventListener( 'click', function ( event ) {
			if ( ! field.contains( event.target ) ) {
				closeList();
			}
		} );

		toggleClear();
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var fields = document.querySelectorAll( '[data-rentiva-autocomplete="items"]' );
		fields.forEach( initField );
	} );
} )();
