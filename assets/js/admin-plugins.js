/* global document, wp */

jQuery( document ).ready( function( $ ) {
	var Queue = function() {
		var previous = new $.Deferred().resolve();

		return function( fn, fail ) {
			return previous = previous.then( fn, fail || fn );
		};
	};

	var queue = Queue();

	console.log( 'Loaded admin-plugins.js' );

	// Action on click update button.
	$( 'table.wp-list-table td.translation-stats button.tstats-update-button' ).on( 'click', function() {
		var tstatsPlugin = $( this ).closest( 'tr' ).attr( 'data-slug' );
		var forceUpdate = true;
		tstatsPluginSubprojectsLoad( tstatsPlugin, forceUpdate );
	} );

	// Action on each loading div, except the ones with the cached stats already rendered.
	$( 'table.wp-list-table td.translation-stats div.translation-stats-loading:not(.translation-stats-cached)' ).each( function() {
		var tstatsPlugin = $( this ).closest( 'tr' ).attr( 'data-slug' );
		var forceUpdate = false;
		tstatsPluginSubprojectsLoad( tstatsPlugin, forceUpdate );
	} );

	// Show the update button of the cached stats already rendered, as there is no request to load them.
	$( 'table.wp-list-table td.translation-stats div.translation-stats-loading.translation-stats-cached' ).closest( 'td.translation-stats' ).find( 'button.tstats-update-button' ).show();

	/**
	 * Load subprojects stats.
	 *
	 * @since 0.9.4
	 * @param {string}  tstatsPlugin Project slug.
	 * @param {boolean} forceUpdate  True or false.
	 */
	function tstatsPluginSubprojectsLoad( tstatsPlugin, forceUpdate ) {
		// If Update button was clicked, instantly show Waiting message.
		if ( forceUpdate ) {
			// Hide the Update button.
			$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats button.tstats-update-button' ).prop( 'disabled', true ).hide();
			// Show the Waiting notice.
			$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading' ).removeClass( 'hidden notice-success updated-message notice-error updating-message' ).addClass( 'notice-warning update-message' );
			$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading p' ).html( wp.i18n.__( 'Waiting...', 'translation-stats' ) );
			$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading' ).fadeIn();
			console.log( 'REST API request to update translation stats for \'' + tstatsPlugin + '\' was queued.' );
		}

		// Add request to queue.
		queue( function() {
			console.log( 'REST API request to update translation stats for \'' + tstatsPlugin + '\' is starting...' );

			// Start colored bars animation.
			$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats' ).addClass( 'tstats-loading' );

			// Show the Loading notice.
			$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading' ).removeClass( 'update-message' ).addClass( 'updating-message' );
			$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading p' ).html( wp.i18n.__( 'Updating...', 'translation-stats' ) );

			return wp.apiFetch( {
				path: '/translation-stats/v1/plugins/' + encodeURIComponent( tstatsPlugin ) + '/stats' + ( forceUpdate ? '?force=true' : '' ),
			} ).then( function( response ) {
				// If Update button was clicked, show Success message afterwards, if not just hide the status notice.
				if ( forceUpdate ) {
					// Show the Updated notice.
					$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading' ).removeClass( 'notice-warning updating-message' ).addClass( 'notice-success updated-message' );
					$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading p' ).html( wp.i18n.__( 'Updated!', 'translation-stats' ) );
				} else {
					// Hide the notice.
					$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading' ).hide();
				}

				// Show Translation Stats content.
				$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.content' ).html( response.html );

				console.log( 'REST API request to update translation stats for \'' + tstatsPlugin + '\' has been completed.' );
			} ).catch( function( error ) {
				// Show the Error notice.
				$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading' ).removeClass( 'notice-success updated-message notice-warning updating-message' ).addClass( 'notice-error update-message' );
				$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading p' ).html( wp.i18n.__( 'An error occurred while updating.', 'translation-stats' ) );
				console.log( 'REST API request to update translation stats for \'' + tstatsPlugin + '\' has failed (' + error.code + '): ' + error.message );
			} ).then( function() {
				// Stop colored bars animation.
				$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats' ).removeClass( 'tstats-loading' );

				// Enable and show update button.
				$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats button.tstats-update-button' ).prop( 'disabled', false ).show();
			} );
		} );
	}
} );
