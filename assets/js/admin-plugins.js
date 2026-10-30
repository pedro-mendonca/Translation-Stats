/* global document, wp, setTimeout, clearTimeout */

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
		var widget = $( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats' );
		var notice = widget.find( 'div.translation-stats-content div.notice.translation-stats-loading' );
		var button = widget.find( 'button.tstats-update-button' );
		var status = widget.find( '.tstats-update-status' );

		// If Update button was clicked, show the update status in the widget title, to not move the stats.
		if ( forceUpdate ) {
			// Disable the Update button and spin its icon.
			button.prop( 'disabled', true ).addClass( 'updating' );
			// Hide any previous error notice.
			notice.hide();
			// Show the Waiting status.
			clearTimeout( status.data( 'timeout' ) );
			status.text( wp.i18n.__( 'Waiting...', 'translation-stats' ) );
			console.log( 'REST API request to update translation stats for \'' + tstatsPlugin + '\' was queued.' );
		}

		// Add request to queue.
		queue( function() {
			console.log( 'REST API request to update translation stats for \'' + tstatsPlugin + '\' is starting...' );

			// Start colored bars animation.
			widget.addClass( 'tstats-loading' );

			if ( forceUpdate ) {
				// Show the Updating status.
				status.text( wp.i18n.__( 'Updating...', 'translation-stats' ) );
			} else {
				// Show the Updating notice, in the empty widget without the cached stats.
				notice.removeClass( 'update-message' ).addClass( 'updating-message' );
				notice.find( 'p' ).html( wp.i18n.__( 'Updating...', 'translation-stats' ) );
			}

			return wp.apiFetch( {
				path: '/translation-stats/v1/plugins/' + encodeURIComponent( tstatsPlugin ) + '/stats' + ( forceUpdate ? '?force=true' : '' ),
			} ).then( function( response ) {
				if ( forceUpdate ) {
					// Show the Updated status for a few seconds.
					status.text( wp.i18n.__( 'Updated!', 'translation-stats' ) );
					status.data( 'timeout', setTimeout( function() {
						status.text( '' );
					}, 3000 ) );
				} else {
					// Hide the notice.
					notice.hide();
				}

				// Show Translation Stats content.
				widget.find( 'div.translation-stats-content div.content' ).html( response.html );

				console.log( 'REST API request to update translation stats for \'' + tstatsPlugin + '\' has been completed.' );
			} ).catch( function( error ) {
				// Clear the status and show the Error notice.
				status.text( '' );
				notice.removeClass( 'hidden notice-success updated-message notice-warning updating-message' ).addClass( 'notice-error update-message' ).show();
				notice.find( 'p' ).html( wp.i18n.__( 'An error occurred while updating.', 'translation-stats' ) );
				console.log( 'REST API request to update translation stats for \'' + tstatsPlugin + '\' has failed (' + error.code + '): ' + error.message );
			} ).then( function() {
				// Stop colored bars animation.
				widget.removeClass( 'tstats-loading' );

				// Enable and show update button, and stop spinning its icon.
				button.prop( 'disabled', false ).removeClass( 'updating' ).show();
			} );
		} );
	}
} );
