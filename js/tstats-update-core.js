jQuery( document ).ready( function( $ ) {
	console.log( 'Loaded tstats-update-core.js' );

	// Action on each loading div.
	$( 'div.translation-stats-loading.update-core' ).each( function() {
		tstatsWordPressSubprojectsLoadAjax();
	} );

	/**
	 * Load subprojects stats.
	 *
	 * @since 0.9.5
	 */
	function tstatsWordPressSubprojectsLoadAjax() {
		$.ajax( {

			url: tstats.ajaxurl,
			type: 'GET',
			data: {
				action: 'tstats_update_core_content_load',
			},
			beforeSend: function() {
				console.log( 'Start WordPress translation update.' );
			},

		} ).done( function( tstatsResponse ) {
			$( 'div.translation-stats-loading.update-core' ).removeClass( 'notice notice-warning notice-alt inline update-message updating-message' );
			$( 'div.translation-stats-loading.update-core' ).html( tstatsResponse );

			console.log( 'End WordPress translation update.' );
		} ).fail( function() {
			console.log( 'Translation Stats Ajax Error.' );
		} );
	}
} );
