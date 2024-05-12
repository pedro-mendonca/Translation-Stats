/* global document, tstats, wp */

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
		tstatsPluginSubprojectsLoadAjax( tstatsPlugin, forceUpdate );
	} );

	// Action on each loading div.
	$( 'table.wp-list-table td.translation-stats div.translation-stats-loading' ).each( function() {
		var tstatsPlugin = $( this ).closest( 'tr' ).attr( 'data-slug' );
		var forceUpdate = false;
		tstatsPluginSubprojectsLoadAjax( tstatsPlugin, forceUpdate );
	} );

	/**
	 * Load subprojects stats.
	 *
	 * @since 0.9.4
	 * @param {string}  tstatsPlugin Project slug.
	 * @param {boolean} forceUpdate  True or false.
	 */
	function tstatsPluginSubprojectsLoadAjax( tstatsPlugin, forceUpdate ) {
		// If Update button was clicked, instantly show Waiting message.
		if ( forceUpdate ) {
			// Hide the Update button.
			$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats button.tstats-update-button' ).prop( 'disabled', true ).hide();
			// Show the Waiting notice.
			$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading' ).removeClass( 'notice-success updated-message notice-warning updating-message notice-error update-message' );
			$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading' ).addClass( 'notice-warning update-message' );
			$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading p' ).html( wp.i18n.__( 'Waiting...', 'translation-stats' ) );
			$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading' ).fadeIn();
			console.log( 'Ajax request to update translation stats for \'' + tstatsPlugin + '\' was queued.' );
		}

		// Add request to queue.
		queue( function() {
			return $.ajax( {

				url: tstats.ajaxurl,
				type: 'POST',
				data: {
					action: 'translation_stats_plugin_widget_content_load',
					tstatsPlugin: tstatsPlugin,
					forceUpdate: forceUpdate,
				},
				beforeSend: function() {
					console.log( 'Ajax request to update translation stats for \'' + tstatsPlugin + '\' is starting...' );

					// Start colored bars animation.
					$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats' ).addClass( 'tstats-loading' );

					// Show the Loading notice.
					$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading' ).removeClass( 'notice-success updated-message notice-warning updating-message notice-error update-message' );
					$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading' ).addClass( 'notice-warning updating-message' );
					$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading p' ).html( wp.i18n.__( 'Updating...', 'translation-stats' ) );
				},

			} ).done( function( html, textStatus, jqXHR ) {
				// If Update button was clicked, show Success message afterwards, if not just hide the status notice.
				if ( forceUpdate ) {
					// Show the Updated notice.
					$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading' ).removeClass( 'notice-success updated-message notice-warning updating-message notice-error update-message' );
					$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading' ).addClass( 'notice-success updated-message' );
					$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading p' ).html( wp.i18n.__( 'Updated!', 'translation-stats' ) );
				} else {
					// Hide the notice.
					$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading' ).hide();
				}

				// Show Translation Stats content.
				$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.content' ).html( html );

				console.log( 'Ajax request to update translation stats for \'' + tstatsPlugin + '\' has been completed (' + textStatus + '). Status: ' + jqXHR.status + ' ' + jqXHR.statusText );
			} ).fail( function( jqXHR, textStatus ) {
				// Show the Error notice.
				$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading' ).removeClass( 'notice-success updated-message notice-warning updating-message notice-error update-message' );
				$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading' ).addClass( 'notice-error update-message' );
				$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content div.notice.translation-stats-loading p' ).html( wp.i18n.__( 'An error occurred while updating.', 'translation-stats' ) );
				console.log( 'Ajax request to update translation stats for \'' + tstatsPlugin + '\' has failed (' + textStatus + '). Status: ' + jqXHR.status + ' ' + jqXHR.statusText );
			} ).always( function() {
				// Stop colored bars animation.
				$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats' ).removeClass( 'tstats-loading' );

				// Enable and show update button.
				$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats button.tstats-update-button' ).prop( 'disabled', false ).show();
			} );
		} );
	}
} );
