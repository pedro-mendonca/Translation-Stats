/**
 * External dependencies
 */
import jQuery from 'jquery';

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

/**
 * Function to add a request to the queue, returning its promise.
 *
 * @callback Queue
 * @param {() => Promise} request Request to run after the previous requests are completed.
 * @return {Promise} Promise resolved when the request is completed.
 */

/**
 * Create a queue to run the REST API requests one after another.
 *
 * @since 1.5.0
 *
 * @return {Queue} Function to add a request to the queue.
 */
export function createQueue() {
	let previous = new jQuery.Deferred().resolve();

	return function( fn ) {
		previous = previous.then( fn, fn );
		return previous;
	};
}

/**
 * Load the plugin subprojects stats.
 *
 * @since 0.9.4
 * @since 1.5.0 Renamed from tstatsPluginSubprojectsLoad() to loadStats(), with the queue as argument.
 *
 * @param {string}  tstatsPlugin Project slug.
 * @param {boolean} forceUpdate  True to get new stats, false to use the cached stats.
 * @param {Queue}   queue        Queue to add the request.
 *
 * @return {Promise} Promise resolved when the request is completed.
 */
export function loadStats( tstatsPlugin, forceUpdate, queue ) {
	const widget = jQuery( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats' );
	const notice = widget.find( 'div.translation-stats-content div.notice.translation-stats-loading' );
	const button = widget.find( 'button.tstats-update-button' );
	const status = widget.find( '.tstats-update-status' );

	// Disable the Update button and dim its icon while queued.
	button.prop( 'disabled', true ).addClass( 'waiting' );

	// Hide any previous error notice.
	notice.hide();

	// Show the Waiting status in the widget title, to not move the stats.
	clearTimeout( status.data( 'timeout' ) );
	status.text( __( 'Waiting…', 'translation-stats' ) );
	console.log( 'REST API request to update translation stats for \'' + tstatsPlugin + '\' was queued.' );

	// Add request to queue.
	return queue( function() {
		console.log( 'REST API request to update translation stats for \'' + tstatsPlugin + '\' is starting...' );

		// Start colored bars animation.
		widget.addClass( 'tstats-loading' );

		// Show the Updating status, and spin the Update button icon while updating.
		status.text( __( 'Updating…', 'translation-stats' ) );
		button.removeClass( 'waiting' ).addClass( 'updating' );

		return apiFetch( {
			path: '/translation-stats/v1/plugins/' + encodeURIComponent( tstatsPlugin ) + '/stats' + ( forceUpdate ? '?force=true' : '' ),
		} ).then( function( response ) {
			if ( forceUpdate ) {
				// Show the Updated status for a few seconds.
				status.text( __( 'Updated!', 'translation-stats' ) );
				status.data( 'timeout', setTimeout( function() {
					status.text( '' );
				}, 3000 ) );
			} else {
				// Clear the status after the first load.
				status.text( '' );
			}

			// Show Translation Stats content.
			widget.find( 'div.translation-stats-content div.content' ).html( response.html );

			console.log( 'REST API request to update translation stats for \'' + tstatsPlugin + '\' has been completed.' );
		} ).catch( function( error ) {
			// Clear the status and show the Error notice.
			status.text( '' );
			notice.removeClass( 'hidden notice-success updated-message notice-warning updating-message' ).addClass( 'notice-error update-message' ).show();
			notice.find( 'p' ).html( __( 'An error occurred while updating.', 'translation-stats' ) );
			console.log( 'REST API request to update translation stats for \'' + tstatsPlugin + '\' has failed (' + error.code + '): ' + error.message );
		} ).then( function() {
			// Stop colored bars animation.
			widget.removeClass( 'tstats-loading' );

			// Enable and show update button, and stop spinning its icon.
			button.prop( 'disabled', false ).removeClass( 'waiting updating' ).show();
		} );
	} );
}

/**
 * Initialize the plugins stats widgets: load the stats without cache, and update them on click.
 *
 * @since 1.5.0
 *
 * @param {Queue} queue Queue to add the requests. Defaults to a new queue.
 */
export function initStatsWidgets( queue = createQueue() ) {
	console.log( 'Loaded admin-plugins.js' );

	// Action on click update button.
	jQuery( 'table.wp-list-table td.translation-stats button.tstats-update-button' ).on( 'click', function() {
		const tstatsPlugin = jQuery( this ).closest( 'tr' ).attr( 'data-slug' );
		loadStats( tstatsPlugin, true, queue );
	} );

	// Action on each loading div, except the ones with the cached stats already rendered.
	jQuery( 'table.wp-list-table td.translation-stats div.translation-stats-loading:not(.translation-stats-cached)' ).each( function() {
		const tstatsPlugin = jQuery( this ).closest( 'tr' ).attr( 'data-slug' );
		loadStats( tstatsPlugin, false, queue );
	} );

	// Show the update buttons, disabled and dimmed while loading the stats without cache.
	jQuery( 'table.wp-list-table td.translation-stats button.tstats-update-button' ).show();
}
