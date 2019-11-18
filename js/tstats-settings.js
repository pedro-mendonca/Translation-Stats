jQuery( document ).ready( function( $ ) {
	// Run scripts on page load.
	tstatsOnLoad();

	// Click on Settings Navigation Tab.
	$( '.tstats-settings__content > .nav-tab-wrapper a' ).on( 'click', tstatsClickSettingsTab );

	// Select all plugins checkbox on Settings plugins table.
	$( '.tstats-settings__content #all_plugins' ).on( 'click', tstatsSelectAllPlugins );

	// Select single plugin checkbox on Settings plugins table.
	$( '.tstats-settings__content input.checkbox-plugin' ).on( 'click', tstatsSelectPlugin );

	// Select single subproject checkbox on Settings plugins table.
	$( '.tstats-settings__content input.checkbox-subproject' ).on( 'click', tstatsSelectPluginSubproject );

	// Select single subproject checkbox on Settings plugins table.
	// $( 'table.wp-list-table td.translation-stats button.tstats-update-button' ).on( 'click', tstatsPluginSubprojectsLoadAjax );

	/**
	 * Translation Stats scripts executed on page load.
	 *
	 * @since 0.9.3
	 */
	function tstatsOnLoad() {
		// Set default Settings Active tab.
		var tstatsDefaultTabID = '#plugins';

		// Get URL hash.
		var tstatsSettingsURLHash = window.location.hash;

		// Check for URL hash.
		if ( tstatsSettingsURLHash ) {
			// Use Hash tab ID.
			var tstatsActiveTabID = tstatsSettingsURLHash;
			console.log( 'Use hash tab: ' + tstatsActiveTabID );
		} else {
			// Use default tab ID.
			var tstatsActiveTabID = tstatsDefaultTabID;
			console.log( 'Use default tab: ' + tstatsActiveTabID );
		}

		// Check for URL tstatsActiveTabID on Settings page load.
		if ( tstatsActiveTabID ) {
			// Set Active Tab on Settings page load.
			$( '.nav-tab-wrapper' ).children().removeClass( 'nav-tab-active' );
			$( '.nav-tab-wrapper a[href="' + tstatsActiveTabID + '"]' ).addClass( 'nav-tab-active' );

			// Set Active Tab Content on Settings page load.
			$( '.tabs-content form' ).children( '.tab-content' ).addClass( 'hidden' );
			$( '.tabs-content form div' + tstatsActiveTabID.replace( '#', '#tab-' ) ).removeClass( 'hidden' );
		}

		console.log( 'Loaded tstats-settings.js' );
	}

	/**
	 * Change active Settings Navigation Tab.
	 *
	 * @since 0.9.3
	 */
	function tstatsClickSettingsTab() {
		var tstatsActiveTabContentID = $( this ).attr( 'href' ).replace( '#', '#tab-' );
		var tstatsActiveTabID = $( this ).attr( 'href' );

		// Active tab.
		$( this ).parent().children().removeClass( 'nav-tab-active' );
		$( this ).addClass( 'nav-tab-active' );

		// Active tab content.
		$( '.tabs-content form' ).children( '.tab-content' ).addClass( 'hidden' );
		$( '.tabs-content form div' + tstatsActiveTabContentID ).removeClass( 'hidden' );
	}

	/**
	 * Enable/disable all plugins in Settings plugins table.
	 * Automatically enable/disable all plugin subprojects.
	 *
	 * @since 0.9.3
	 */
	function tstatsSelectAllPlugins() {
		if ( $( '.tstats-plugin-list-table input#all_plugins' ).is( ':checked' ) ) {
			// Set all plugin rows as active.
			$( '.tstats-plugin-list-table tr.inactive' ).addClass( 'active' ).removeClass( 'inactive' );
		} else {
			// Set all plugin rows as inactive.
			$( '.tstats-plugin-list-table tr.active' ).addClass( 'inactive' ).removeClass( 'active' );
		}

		console.log( 'Clicked all plugins checkbox.' );
	}

	/**
	 * Enable/disable single plugin in Settings plugins table.
	 * Automatically enable/disable all plugin subprojects.
	 *
	 * @since 0.9.3
	 */
	function tstatsSelectPlugin() {
		// Get the clicked plugin row ID from Settings plugins table.
		var id = $( event.target ).attr( 'id' );

		$( 'input.' + id ).prop( 'checked', this.checked );
		if ( $( 'input.' + id ).parents( 'tr' ).hasClass( 'active' ) ) {
			// Set plugin row as inactive.
			$( 'input.' + id ).parents( 'tr' ).addClass( 'inactive' ).removeClass( 'active' );
		} else {
			// Set plugin row as active.
			$( 'input.' + id ).parents( 'tr' ).addClass( 'active' ).removeClass( 'inactive' );
		}

		console.log( 'Clicked single plugin ID #' + id + ' checkbox.' );
	}

	/**
	 * Enable/disable single plugin subproject.
	 * If any subproject is enabled, set plugin row as active.
	 * If all subprojects are disabled, set plugin row as inactive.
	 *
	 * @since 0.9.3
	 */
	function tstatsSelectPluginSubproject() {
		var pluginSubprojectsCount = {};

		// Get the clicked plugin subproject class from Settings plugins table.
		var checkboxClass = $( event.target ).attr( 'class' );

		// Get plugin row id.
		var id = checkboxClass.substring( 'checkbox-subproject plugin_'.length );

		// Set plugin subprojects count.
		pluginSubprojectsCount[ id ] = $( 'input.plugin_' + id + ':checked' ).length;

		// Check plugin subprojects count.
		if ( 0 === pluginSubprojectsCount[ id ] ) {
			// Set plugin row as inactive.
			$( 'input.plugin_' + id ).parents( 'tr' ).addClass( 'inactive' ).removeClass( 'active' );
			$( 'input#plugin_' + id ).prop( 'checked', false );
		} else {
			// Set plugin row as active.
			$( 'input.plugin_' + id ).parents( 'tr' ).addClass( 'active' ).removeClass( 'inactive' );
			$( 'input#plugin_' + id ).prop( 'checked', true );
		}

		console.log( 'Clicked single plugin ID #' + id + ' subproject checkbox.' );
		console.log( pluginSubprojectsCount[ id ] + ' subproject(s) of plugin ID#' + id + ' selected.' );
	}
} );
