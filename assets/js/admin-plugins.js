jQuery( document ).ready( function( $ ) {
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
	 *
	 * @param {string} tstatsPlugin - Project slug.
	 * @param {boolean} forceUpdate - True or false.
	 */
	function tstatsPluginSubprojectsLoadAjax( tstatsPlugin, forceUpdate ) {
		// TODO: Cancel last request, if exist, to avoid multiple requests queue.

		$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats' ).addClass( 'tstats-loading' );

		$.ajax( {

			url: tstats.ajaxurl,
			type: 'POST',
			data: {
				action: 'translation_stats_plugin_widget_content_load',
				tstatsPlugin: tstatsPlugin,
				forceUpdate: forceUpdate,
			},
			beforeSend: function() {
				console.log( 'Start plugin \'' + tstatsPlugin + '\' Translation Stats update.' );
			},

		} ).done( function( tstatsResponse ) {
			$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats' ).removeClass( 'tstats-loading' );
			$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats button.tstats-update-button' ).show();
			$( 'tr[data-slug=' + tstatsPlugin + '] td.translation-stats div.translation-stats-content' ).html( tstatsResponse );

			console.log( 'End plugin \'' + tstatsPlugin + '\' Translation Stats update.' );
		} ).fail( function() {
			console.log( 'Translation Stats Ajax Error.' );
		} );
	}
} );
