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

/**
 * Rentiva → Setup, Step 1: drives the Install Now / Activate buttons through
 * wp.updates — the same AJAX installer/activator the core Plugins and Add
 * Plugins screens use. Every plugin is one short request queued through
 * wp.updates.queue rather than one long blocking call, so chaining several
 * installs can't itself trip a PHP execution-time limit, and each request's
 * own resolve/reject drives this row's progress state.
 */
( function ( $ ) {
	'use strict';

	if ( 'undefined' === typeof wp || ! wp.updates ) {
		return;
	}

	var $rows = $( '[data-plugin-row]' );
	if ( ! $rows.length ) {
		return;
	}

	var $card         = $rows.first().closest( '.rentiva-card' );
	var $progress     = $card.find( '.rentiva-plugin-progress' );
	var $progressFill = $progress.find( '.rentiva-plugin-progress__bar span' );
	var $progressText = $progress.find( '.rentiva-plugin-progress__text' );

	function format( template, values ) {
		var out = template;
		$.each( values, function ( token, value ) {
			out = out.replace( token, value );
		} );
		return out;
	}

	function clearNotice( $row ) {
		$row.find( '.rentiva-plugin-row__notice' ).attr( 'class', 'rentiva-plugin-row__notice' ).attr( 'hidden', true ).empty();
	}

	/**
	 * @param {jQuery} $row
	 * @param {string} task      'install' or 'activate' — which step actually
	 *                           failed, since the heading and the advice both
	 *                           genuinely differ (a failed activate means the
	 *                           plugin is already sitting there installed —
	 *                           "download the zip" would be nonsense advice).
	 * @param {string} message   response.errorMessage from wp.updates, if any.
	 * @param {string} wporgFlag '1' or '0', from the button's data-wporg.
	 * @param {string} slug
	 */
	function showNotice( $row, task, message, wporgFlag, slug ) {
		var isInstall = 'install' === task;
		var heading    = isInstall ? rentivaAdmin.manualInstallHeading : rentivaAdmin.manualActivateHeading;
		var hint       = isInstall
			? ( '1' === wporgFlag ? rentivaAdmin.manualWporgHint : rentivaAdmin.manualPremiumHint )
			: rentivaAdmin.manualActivateHint;
		var html = '<strong>' + heading + '</strong>';
		if ( message ) {
			html += '<p>' + message + '</p>';
		}
		html += '<p>' + hint + '</p><p>';
		if ( isInstall ) {
			html += '<a href="' + rentivaAdmin.uploadPluginUrl + '" class="button button-small" target="_blank" rel="noopener noreferrer">' + rentivaAdmin.uploadLinkText + '</a> ';
			if ( '1' === wporgFlag ) {
				html += '<a href="https://wordpress.org/plugins/' + slug + '/" class="button button-small" target="_blank" rel="noopener noreferrer">' + rentivaAdmin.wporgLinkText + '</a> ';
			}
		} else {
			html += '<a href="' + rentivaAdmin.pluginsScreenUrl + '" class="button button-small" target="_blank" rel="noopener noreferrer">' + rentivaAdmin.pluginsScreenLinkText + '</a> ';
		}
		html += '<button type="button" class="button button-small rentiva-retry-install">' + rentivaAdmin.retryText + '</button></p>';
		$row.find( '.rentiva-plugin-row__notice' ).attr( 'class', 'rentiva-plugin-row__notice' ).html( html ).removeAttr( 'hidden' );
	}

	function showStallNotice( $row ) {
		// Informational only — the real request is still in flight (aborting
		// it client-side wouldn't stop it server-side anyway), so this never
		// replaces the busy button and never offers Retry, which could fire a
		// second, conflicting attempt. clearNotice() removes it as soon as
		// the real success/error callback finally arrives.
		var html = '<strong>' + rentivaAdmin.stallHeading + '</strong>';
		html += '<p>' + rentivaAdmin.stallHint + '</p><p>';
		html += '<a href="' + rentivaAdmin.pluginsScreenUrl + '" class="button button-small" target="_blank" rel="noopener noreferrer">' + rentivaAdmin.pluginsScreenLinkText + '</a>';
		html += '</p>';
		$row.find( '.rentiva-plugin-row__notice' ).attr( 'class', 'rentiva-plugin-row__notice rentiva-plugin-row__notice--stall' ).html( html ).removeAttr( 'hidden' );
	}

	/**
	 * Warns via onStall if neither success nor error arrives within `ms` —
	 * without abandoning the real request, since a client-side timeout can't
	 * stop whatever's actually still running server-side. Whichever of
	 * success()/error() the caller wires up should call the returned
	 * `clear()` first, so a stall warning never appears after the fact.
	 *
	 * @param {number}   ms
	 * @param {Function} onStall
	 * @return {Function} clear
	 */
	function watchForStall( ms, onStall ) {
		var timer = setTimeout( onStall, ms );
		return function clear() {
			clearTimeout( timer );
		};
	}

	function markActive( $row ) {
		$row.find( '[data-role="status-badge"]' )
			.attr( 'class', 'rentiva-badge rentiva-badge--ready' )
			.text( rentivaAdmin.activeText );
		$row.find( '.rentiva-plugin-row__action' ).empty();
		clearNotice( $row );
	}

	/**
	 * Builds a fresh Install Now / Activate button matching what
	 * rentiva_render_plugin_action_button() renders server-side, so the
	 * install → activate handoff below can swap one in for the other
	 * without a page reload. Built via attr()/text() rather than an HTML
	 * string, so nothing in `data` needs manual escaping.
	 *
	 * @param {string} task 'install' or 'activate'.
	 * @param {Object} data { slug, name, file, wporg }
	 * @return {jQuery}
	 */
	function buildActionButton( task, data ) {
		var isInstall = 'install' === task;
		var $btn       = $( '<a>', {
			href: '#',
			'class': 'button button-small rentiva-plugin-action ' + ( isInstall ? 'button-primary rentiva-plugin-action--install' : 'rentiva-plugin-action--activate' ),
			'data-task': task,
			'data-slug': data.slug,
			'data-plugin-file': data.file,
			'data-name': data.name,
			'data-wporg': data.wporg
		} );
		$( '<span>', { 'class': 'dashicons ' + ( isInstall ? 'dashicons-download' : 'dashicons-yes-alt' ), 'aria-hidden': 'true' } ).appendTo( $btn );
		$btn.append( document.createTextNode( ' ' + ( isInstall ? rentivaAdmin.installButtonText : rentivaAdmin.activateButtonText ) ) );
		return $btn;
	}

	/**
	 * Installed, but not yet active — swaps in a fresh Activate button so
	 * installing and activating stay two distinct, separately-clicked steps
	 * instead of one chained flow (a single long AJAX chain was exactly what
	 * made a slow or stalled activate step look like a stuck install).
	 *
	 * @param {jQuery} $row
	 * @param {Object} data { slug, name, file, wporg }
	 */
	function markInstalled( $row, data ) {
		$row.find( '[data-role="status-badge"]' )
			.attr( 'class', 'rentiva-badge rentiva-badge--neutral' )
			.text( rentivaAdmin.installedText );
		$row.find( '.rentiva-plugin-row__action' ).empty().append( buildActionButton( 'activate', data ) );
		clearNotice( $row );
	}

	function setBusy( $btn, text ) {
		// Core already renders a spinning icon via :before on any
		// .button.updating-message (see wp-admin/css/common.css) — swap only
		// the text, or that spinner would double up with one of our own.
		if ( undefined === $btn.data( 'originalHtml' ) ) {
			$btn.data( 'originalHtml', $btn.html() );
		}
		$btn.addClass( 'updating-message' ).text( text );
	}

	function restore( $btn ) {
		$btn.removeClass( 'updating-message' );
		if ( undefined !== $btn.data( 'originalHtml' ) ) {
			$btn.html( $btn.data( 'originalHtml' ) );
		}
	}

	/**
	 * Advances onTick through a fixed list of percentages on a timer, one
	 * step at a time, then holds at the last ("ceiling") value until told to
	 * stop — a request that's actually still in flight never looks frozen,
	 * even though wp.updates has no real progress events to report.
	 *
	 * @param {number[]}  checkpoints Ascending percentages, e.g. [5, 10, 15, 20, 30, 50].
	 * @param {Function}  onTick      Called with each checkpoint as it's reached.
	 * @param {number=}   intervalMs  Delay between ticks. Default 700.
	 * @return {Function} Call to stop the timer (does not undo the last tick shown).
	 */
	function creepProgress( checkpoints, onTick, intervalMs ) {
		var i = 0;
		onTick( checkpoints[ 0 ] );
		var timer = setInterval( function () {
			i++;
			if ( i >= checkpoints.length ) {
				clearInterval( timer );
				return;
			}
			onTick( checkpoints[ i ] );
		}, intervalMs || 700 );

		return function stop() {
			clearInterval( timer );
		};
	}

	/**
	 * Installs one plugin via wp.updates — and only installs it. On success
	 * the row's button is swapped for a fresh Activate button (markInstalled())
	 * rather than chaining straight into activation itself: a single
	 * install-then-activate AJAX chain was exactly what made a slow or
	 * stalled activate step look like a stuck/broken install, with no way to
	 * tell which half had actually failed. Two separate clicks means two
	 * separate, individually retryable outcomes.
	 *
	 * Always resolves — with { success: bool } — never rejects, so code
	 * processing several plugins in sequence (Install All) can carry on past
	 * a failure instead of stopping the whole run.
	 *
	 * @param {jQuery}    $btn       The .rentiva-plugin-action[data-task="install"] button clicked.
	 * @param {Function=} onProgress Optional ( fraction, text ) callback; fraction
	 *                               is 0..1, or null to signal failure (hide progress).
	 * @return {jQuery.Promise}
	 */
	function runInstall( $btn, onProgress ) {
		onProgress    = onProgress || function () {};
		var deferred  = $.Deferred();
		var $row      = $btn.closest( '[data-plugin-row]' );
		var slug      = $btn.data( 'slug' );
		var name      = $btn.data( 'name' );
		var file      = $btn.data( 'pluginFile' );
		var wporgFlag = String( $btn.data( 'wporg' ) );

		clearNotice( $row );
		setBusy( $btn, rentivaAdmin.installingText );

		// wp.updates only reports request success/failure, not real
		// byte-level download/unzip progress, so the percentage creeps
		// through a few checkpoints while the request is in flight and then
		// snaps to 100% the moment it actually resolves — an honest "still
		// working" indicator rather than one that sits frozen the whole time.
		var stopCreep = creepProgress( [ 5, 10, 15, 20, 30, 50, 70, 90 ], function ( pct ) {
			onProgress( pct / 100, format( rentivaAdmin.phaseInstalling, { '%s': name } ) );
		} );
		// A client-side timeout can't actually stop whatever's still running
		// server-side, so this only surfaces a "still working" notice at
		// 15s — it never fails the row or stops waiting for the real
		// success/error, which is handled normally whenever it does arrive.
		var clearStall = watchForStall( 15000, function () {
			showStallNotice( $row );
		} );

		wp.updates.installPlugin( {
			slug: slug,
			success: function () {
				clearStall();
				stopCreep();
				onProgress( 1, format( rentivaAdmin.phaseInstalled, { '%s': name } ) );
				markInstalled( $row, { slug: slug, name: name, file: file, wporg: wporgFlag } );
				deferred.resolve( { success: true, slug: slug } );
			},
			error: function ( response ) {
				clearStall();
				stopCreep();
				restore( $btn );
				showNotice( $row, 'install', response && response.errorMessage ? response.errorMessage : '', wporgFlag, slug );
				onProgress( null );
				deferred.resolve( { success: false, slug: slug } );
			}
		} );

		return deferred.promise();
	}

	/**
	 * Activates one already-installed plugin via wp.updates — and only
	 * activates it (see runInstall()'s docs for why install and activate are
	 * two separate functions/clicks rather than one chained flow). Always
	 * resolves — with { success: bool } — never rejects.
	 *
	 * @param {jQuery}    $btn       The .rentiva-plugin-action[data-task="activate"] button clicked.
	 * @param {Function=} onProgress Optional ( fraction, text ) callback; fraction
	 *                               is 0..1, or null to signal failure (hide progress).
	 * @return {jQuery.Promise}
	 */
	function runActivate( $btn, onProgress ) {
		onProgress    = onProgress || function () {};
		var deferred  = $.Deferred();
		var $row      = $btn.closest( '[data-plugin-row]' );
		var slug      = $btn.data( 'slug' );
		var name      = $btn.data( 'name' );
		var file      = $btn.data( 'pluginFile' );
		var wporgFlag = String( $btn.data( 'wporg' ) );

		clearNotice( $row );
		setBusy( $btn, rentivaAdmin.activatingText );

		var stopCreep = creepProgress( [ 10, 20, 30, 50, 70, 90 ], function ( pct ) {
			onProgress( pct / 100, format( rentivaAdmin.phaseActivating, { '%s': name } ) );
		} );
		var clearStall = watchForStall( 15000, function () {
			showStallNotice( $row );
		} );

		wp.updates.activatePlugin( {
			slug: slug,
			name: name,
			plugin: file,
			success: function () {
				clearStall();
				stopCreep();
				restore( $btn );
				markActive( $row );
				onProgress( 1, format( rentivaAdmin.phaseDone, { '%s': name } ) );
				deferred.resolve( { success: true, slug: slug } );
			},
			error: function ( response ) {
				clearStall();
				stopCreep();
				restore( $btn );
				showNotice( $row, 'activate', response && response.errorMessage ? response.errorMessage : '', wporgFlag, slug );
				onProgress( null );
				deferred.resolve( { success: false, slug: slug } );
			}
		} );

		return deferred.promise();
	}

	/**
	 * Dispatches to runInstall() or runActivate() based on which button was
	 * clicked — the shared entry point for the click handlers below.
	 *
	 * @param {jQuery}    $btn
	 * @param {Function=} onProgress
	 * @return {jQuery.Promise}
	 */
	function runPluginAction( $btn, onProgress ) {
		return 'install' === $btn.data( 'task' ) ? runInstall( $btn, onProgress ) : runActivate( $btn, onProgress );
	}

	/**
	 * Drives the shared progress bar for one plugin's own install/activate
	 * flow (not part of an Install All run) — shows a percentage and phase
	 * text, then fades out shortly after success.
	 *
	 * @param {number|null} fraction 0..1, or null on failure (hides the bar).
	 * @param {string=}      text
	 */
	function showSingleProgress( fraction, text ) {
		if ( null === fraction ) {
			$progress.attr( 'hidden', true );
			$progressFill.css( 'width', '0%' );
			return;
		}
		var percent = Math.round( fraction * 100 );
		$progress.removeAttr( 'hidden' );
		$progressFill.css( 'width', percent + '%' );
		$progressText.text( ( text || '' ) + ' ' + percent + '%' );
		if ( fraction >= 1 ) {
			setTimeout( function () {
				$progress.attr( 'hidden', true );
				$progressFill.css( 'width', '0%' );
			}, 900 );
		}
	}

	$( document ).on( 'click', '.rentiva-plugin-action', function ( event ) {
		event.preventDefault();
		runPluginAction( $( this ), showSingleProgress );
	} );

	$( document ).on( 'click', '.rentiva-retry-install', function ( event ) {
		event.preventDefault();
		var $row = $( this ).closest( '[data-plugin-row]' );
		clearNotice( $row );
		runPluginAction( $row.find( '.rentiva-plugin-action' ), showSingleProgress );
	} );
} )( jQuery );
