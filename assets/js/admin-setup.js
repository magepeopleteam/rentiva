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

	var $card          = $rows.first().closest( '.rentiva-card' );
	var $installAllBtn = $card.find( '.rentiva-install-all' );
	var $progress      = $card.find( '.rentiva-plugin-progress' );
	var $progressFill  = $progress.find( '.rentiva-plugin-progress__bar span' );
	var $progressText  = $progress.find( '.rentiva-plugin-progress__text' );

	function format( template, values ) {
		var out = template;
		$.each( values, function ( token, value ) {
			out = out.replace( token, value );
		} );
		return out;
	}

	function pendingRequiredButtons() {
		return $rows
			.filter( '[data-required="1"]' )
			.find( '.rentiva-plugin-action' );
	}

	if ( pendingRequiredButtons().length > 1 ) {
		$installAllBtn.removeAttr( 'hidden' );
	}

	function clearNotice( $row ) {
		$row.find( '.rentiva-plugin-row__notice' ).attr( 'hidden', true ).empty();
	}

	function showNotice( $row, message, wporgFlag, slug ) {
		var hint = '1' === wporgFlag ? rentivaAdmin.manualWporgHint : rentivaAdmin.manualPremiumHint;
		var html = '<strong>' + rentivaAdmin.manualHeading + '</strong>';
		if ( message ) {
			html += '<p>' + message + '</p>';
		}
		html += '<p>' + hint + '</p><p>';
		html += '<a href="' + rentivaAdmin.uploadPluginUrl + '" class="button button-small" target="_blank" rel="noopener noreferrer">' + rentivaAdmin.uploadLinkText + '</a> ';
		if ( '1' === wporgFlag ) {
			html += '<a href="https://wordpress.org/plugins/' + slug + '/" class="button button-small" target="_blank" rel="noopener noreferrer">' + rentivaAdmin.wporgLinkText + '</a> ';
		}
		html += '<button type="button" class="button button-small rentiva-retry-install">' + rentivaAdmin.retryText + '</button></p>';
		$row.find( '.rentiva-plugin-row__notice' ).html( html ).removeAttr( 'hidden' );
	}

	function markActive( $row ) {
		$row.find( '[data-role="status-badge"]' )
			.attr( 'class', 'rentiva-badge rentiva-badge--ready' )
			.text( rentivaAdmin.activeText );
		$row.find( '.rentiva-plugin-row__action' ).empty();
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
	 * Installs (if needed) then activates one plugin via wp.updates. Always
	 * resolves — with { success: bool } — never rejects, so code processing
	 * several plugins in sequence (Install All) can carry on past a failure
	 * instead of stopping the whole run.
	 *
	 * wp.updates itself only reports request success/failure, not real
	 * byte-level download/unzip progress, so each phase's percentage creeps
	 * through a few checkpoints while its request is in flight (via
	 * creepProgress()) and then snaps to the phase's real end value the
	 * moment the request actually resolves — an honest "still working, here's
	 * roughly how far along" indicator rather than one that sits frozen for
	 * however long the request takes, or a literal (unavailable) byte count.
	 *
	 * @param {jQuery}    $btn        The .rentiva-plugin-action button clicked.
	 * @param {Function=} onProgress  Optional ( fraction, text ) callback; fraction
	 *                                is 0..1, or null to signal failure (hide progress).
	 * @return {jQuery.Promise}
	 */
	function runPluginAction( $btn, onProgress ) {
		onProgress    = onProgress || function () {};
		var deferred  = $.Deferred();
		var $row      = $btn.closest( '[data-plugin-row]' );
		var slug      = $btn.data( 'slug' );
		var name      = $btn.data( 'name' );
		var file      = $btn.data( 'pluginFile' );
		var wporgFlag = String( $btn.data( 'wporg' ) );
		var task      = $btn.data( 'task' );

		clearNotice( $row );

		function doActivate() {
			setBusy( $btn, rentivaAdmin.activatingText );
			var activateCheckpoints = 'install' === task ? [ 50, 70, 90 ] : [ 10, 20, 30, 50, 70, 90 ];
			var stopCreep           = creepProgress( activateCheckpoints, function ( pct ) {
				onProgress( pct / 100, format( rentivaAdmin.phaseActivating, { '%s': name } ) );
			} );
			wp.updates.activatePlugin( {
				slug: slug,
				name: name,
				plugin: file,
				success: function () {
					stopCreep();
					restore( $btn );
					markActive( $row );
					onProgress( 1, format( rentivaAdmin.phaseDone, { '%s': name } ) );
					deferred.resolve( { success: true, slug: slug } );
				},
				error: function ( response ) {
					stopCreep();
					restore( $btn );
					showNotice( $row, response && response.errorMessage ? response.errorMessage : '', wporgFlag, slug );
					onProgress( null );
					deferred.resolve( { success: false, slug: slug } );
				}
			} );
		}

		if ( 'install' === task ) {
			setBusy( $btn, rentivaAdmin.installingText );
			var stopCreep = creepProgress( [ 5, 10, 15, 20, 30, 50 ], function ( pct ) {
				onProgress( pct / 100, format( rentivaAdmin.phaseInstalling, { '%s': name } ) );
			} );
			wp.updates.installPlugin( {
				slug: slug,
				success: function () {
					stopCreep();
					onProgress( 0.5, format( rentivaAdmin.phaseInstalled, { '%s': name } ) );
					doActivate();
				},
				error: function ( response ) {
					stopCreep();
					restore( $btn );
					showNotice( $row, response && response.errorMessage ? response.errorMessage : '', wporgFlag, slug );
					onProgress( null );
					deferred.resolve( { success: false, slug: slug } );
				}
			} );
		} else {
			doActivate();
		}

		return deferred.promise();
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

	$installAllBtn.on( 'click', function () {
		var $btns = pendingRequiredButtons();
		var total = $btns.length;
		if ( ! total ) {
			return;
		}

		// Hide our own icon rather than swapping it for a spinning one — core
		// already renders a spinning :before icon on any .button.updating-message
		// (see wp-admin/css/common.css), so keeping ours too would double up.
		$installAllBtn
			.prop( 'disabled', true )
			.addClass( 'updating-message' )
			.find( '.dashicons' ).hide();
		$progress.removeAttr( 'hidden' );

		var completed = 0;
		var failures  = 0;

		function next( index ) {
			if ( index >= total ) {
				$progressFill.css( 'width', '100%' );
				if ( failures ) {
					$installAllBtn
						.prop( 'disabled', false )
						.removeClass( 'updating-message' )
						.find( '.dashicons' ).show();
					$progressText.text(
						format( rentivaAdmin.progressText, { '%1$d': completed - failures, '%2$d': total, '%3$s': '' } )
					);
				} else {
					$progressText.text( rentivaAdmin.installAllDoneText );
					setTimeout( function () {
						window.location.reload();
					}, 1200 );
				}
				return;
			}

			var $btn = $btns.eq( index );
			var name = $btn.data( 'name' );

			// Combine this plugin's own 0..1 progress with its position in the
			// queue so the bar advances smoothly across the whole run, not just
			// in one jump per plugin.
			function onStepProgress( fraction ) {
				if ( null === fraction ) {
					return;
				}
				var overall = Math.round( ( ( index + fraction ) / total ) * 100 );
				$progressFill.css( 'width', overall + '%' );
				$progressText.text(
					format( rentivaAdmin.progressText, { '%1$d': index + 1, '%2$d': total, '%3$s': name } ) + ' — ' + overall + '%'
				);
			}

			onStepProgress( 0 );

			runPluginAction( $btn, onStepProgress ).done( function ( result ) {
				completed++;
				if ( ! result.success ) {
					failures++;
				}
				next( index + 1 );
			} );
		}

		next( 0 );
	} );
} )( jQuery );
