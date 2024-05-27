/* global document, event, window */

jQuery( document ).ready( function( $ ) {
	// Run scripts on page load.
	tstatsOnLoad();

	// Sort and filter plugins settings list.
	tstatsPluginsSort();

	// Click on Settings Navigation Tab.
	$( '.tstats-settings__content > .nav-tab-wrapper a' ).on( 'click', tstatsClickSettingsTab );

	// Select all projects checkbox on Settings projects table.
	$( '.tstats-settings__content #all_plugins' ).on( 'click', tstatsPluginsSelectAll );

	// Select single project checkbox on Settings projects table.
	$( '.tstats-settings__content input.checkbox-plugin' ).on( 'click', tstatsSelectPlugin );

	// Select single subproject checkbox on Settings projects table.
	$( '.tstats-settings__content input.checkbox-subproject' ).on( 'click', tstatsUpdatePluginsTableRow );

	// Click plugins table header.
	$( '.tablesorter-header:not(.sorter-false)' ).on( 'mouseup', tstatsClickPluginsSort );

	// Select single subproject checkbox on Settings projects table.
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

		// Declare variable.
		var tstatsActiveTabID;

		// Check for URL hash.
		if ( tstatsSettingsURLHash ) {
			// Use Hash tab ID.
			tstatsActiveTabID = tstatsSettingsURLHash;
			console.log( 'Use hash tab: ' + tstatsActiveTabID );
		} else {
			// Use default tab ID.
			tstatsActiveTabID = tstatsDefaultTabID;
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

		console.log( 'Loaded admin-settings.js' );
	}

	/**
	 * Translation Stats plugins list table sorting.
	 *
	 * @since 1.0.2
	 */
	function tstatsPluginsSort() {
		// Sort table.
		$( '#tstats-table-plugins' ).tablesorter( {
			// Initial sort column (2nd column).
			sortList: [
				[ 1, 0 ],
			],

			// Resets the sort direction so that clicking on an unsorted column will sort in the sortInitialOrder direction.
			sortRestart: true,
			// Default order.
			sortInitialOrder: 'asc',

			widgets: [
				'filter',
			],
			widgetOptions: {

				// External filter input selector.
				filter_external: '#plugins-search-input',
				// Don't include column filters.
				filter_columnFilters: false,
				// Save last used filter.
				filter_saveFilters: false,
				// Filter reset selector.
				filter_reset: '#plugins-search-reset',

			},
		} );
	}

	/**
	 * Change active Settings Navigation Tab.
	 *
	 * @since 0.9.3
	 */
	function tstatsClickSettingsTab() {
		var tstatsActiveTabContentID = $( this ).attr( 'href' ).replace( '#', '#tab-' );

		// Active tab.
		$( this ).parent().children().removeClass( 'nav-tab-active' );
		$( this ).addClass( 'nav-tab-active' );

		// Active tab content.
		$( '.tabs-content form' ).children( '.tab-content' ).addClass( 'hidden' );
		$( '.tabs-content form div' + tstatsActiveTabContentID ).removeClass( 'hidden' );
	}

	/**
	 * Enable/disable all projects in Settings projects table.
	 * Automatically enable/disable all row subprojects.
	 *
	 * @since 0.9.3
	 */
	function tstatsPluginsSelectAll() {
		if ( $( '.tstats-plugin-list-table input#all_plugins' ).prop( 'checked' ) ) {
			// Enable all plugins.
			tstatsPluginsUpdatePlugins( 'enable' );
			// Set all project rows as active.
			$( '.tstats-plugin-list-table tr.inactive' ).addClass( 'active' ).removeClass( 'inactive' ).attr( 'data-subprojects', '4' );
			// Set plugins as active.
			$( 'input.checkbox-plugin' ).prop(
				{
					checked: true,
				}
			);

			console.log( 'Enabled all projects checkbox.' );
		} else {
			// Disable all plugins.
			tstatsPluginsUpdatePlugins( 'disable' );
			// Set all project rows as inactive.
			$( '.tstats-plugin-list-table tr.active' ).addClass( 'inactive' ).removeClass( 'active' ).attr( 'data-subprojects', '0' );
			// Set plugins as inactive.
			$( 'input.checkbox-plugin' ).prop(
				{
					checked: false,
				}
			);

			console.log( 'Disabled all projects checkbox.' );
		}
	}

	/*
	ActivateAll
		Loop plugins
			- ActivatePlugin
				- ActivateSubprojects
				- UpdateSubprojectsCount
		- UpdatePluginsCount
	DeactivateAll
		Loop plugins
			- DeactivatePlugin
				- DeactivateSubprojects
				- UpdateSubprojectsCount
		- UpdatePluginsCount

		UpdateColumnsStatus On selecting subproject or Row
		UpdateRowsStatus    On selecting subproject or Column
		UpdateInputAll      On selecting subproject or Column or Row

		SelectSubproject
			- RecalculateRow
			- RecalculateColumn

		SelectRow
			- UpdateRow
			- RecalculateColumns
		SelectColumn
			- UpdateColumn
			- RecalculateRows

		SelectAll
			- UpdateRows
				- UpdateRow (loop)
	*/

	/**
	 * Update plugin status.
	 * Automatically enable/disable all row subprojects.
	 *
	 * @since 1.2.9
	 */
	function tstatsPluginsUpdatePlugins( action ) {
		if ( $( '.tstats-plugin-list-table input#all_plugins' ).prop( 'checked' ) ) {
			// Set all project rows as active.
			$( '.tstats-plugin-list-table tr.inactive' ).addClass( 'active' ).removeClass( 'inactive' ).attr( 'data-subprojects', '4' );
			// Set plugins as active.
			$( 'input.checkbox-plugin' ).prop(
				{
					checked: true,
				}
			);

			console.log( 'Enabled all projects checkbox.' );
		} else {
			// Set all project rows as inactive.
			$( '.tstats-plugin-list-table tr.active' ).addClass( 'inactive' ).removeClass( 'active' ).attr( 'data-subprojects', '0' );
			// Set plugins as inactive.
			$( 'input.checkbox-plugin' ).prop(
				{
					checked: false,
				}
			);

			console.log( 'Disabled all projects checkbox.' );
		}
	}

	/**
	 * Enable/disable single project in Settings projects table.
	 * Automatically enable/disable all row subprojects.
	 *
	 * @since 0.9.3
	 */
	function tstatsSelectPlugin() {
		var pluginSubprojectsCount = {};
		var pluginSubprojectsTotal = {};

		// Get the clicked project row ID from Settings projects table.
		var id = $( event.target ).attr( 'id' );

		// Set row subprojects count.
		pluginSubprojectsCount = $( 'input.' + id + ':checked' ).length;

		// Set row subprojects total.
		pluginSubprojectsTotal = $( 'input.' + id ).length;

		if ( pluginSubprojectsCount === pluginSubprojectsTotal ) {
			// Set project row as inactive.
			$( 'input.' + id ).parents( 'tr' ).addClass( 'inactive' ).removeClass( 'active' );
			// Set row subprojects data as 0.
			$( 'input.' + id ).parents( 'tr' ).attr( 'data-subprojects', '0' );
			// Set row subprojects as unselected.
			$( 'input.' + id ).prop( 'checked', false );

			console.log( 'Project disabled.' );
		} else {
			// Set project row as active.
			$( 'input.' + id ).parents( 'tr' ).addClass( 'active' ).removeClass( 'inactive' );
			// Set row subprojects data as 4.
			$( 'input.' + id ).parents( 'tr' ).attr( 'data-subprojects', '4' );
			// Set project as selected.
			$( 'input#' + id ).prop( 'checked', true );
			// Set row subprojects as selected.
			$( 'input.' + id ).prop( 'checked', true );

			console.log( 'Project fully enabled.' );
		}

		tstatsUpdatePluginsTableAll();

		console.log( 'Clicked single project ID "' + id + '" checkbox.' );
	}

	/**
	 * Update input checkbox '#all_plugins' status.
	 *
	 * @since 1.2.9
	 */
	function tstatsUpdatePluginsTableAll() {
		// Total of fully active plugins.
		var fullyActivePluginsCount = $( '#tstats-table-plugins tbody tr.active[data-subprojects="4"]' ).length;

		// Total of available plugins.
		var avaliablePluginsCount = $( '#tstats-table-plugins tbody tr:not(.disabled)' ).length;

		console.log( 'Fully active plugins', fullyActivePluginsCount );
		//console.log( 'Partially active plugins', partiallyActivePluginsCount );
		console.log( 'Available plugins', avaliablePluginsCount );

		// Do nothing if no plugin available.
		if ( avaliablePluginsCount === 0 ) {
			return;
		}

		// All plugins enabled.
		if ( fullyActivePluginsCount === avaliablePluginsCount ) {
			$( 'input#all_plugins' ).prop(
				{
					checked: true,
				}
			).attr( 'data-plugins', 'all' );

			console.log( 'All plugins active.' );
		} else if ( fullyActivePluginsCount === 0 ) {
			$( 'input#all_plugins' ).prop(
				{
					checked: false,
				}
			).attr( 'data-plugins', 'none' );
			console.log( 'All plugins inactive.' );
		} else {
			$( 'input#all_plugins' ).prop(
				{
					checked: false,
				}
			).attr( 'data-plugins', 'indeterminate' );
			console.log( 'Some plugins active.' );
		}
	}

	/**
	 * Enable/disable single row subproject.
	 * If any subproject is enabled, set project row as active.
	 * If all subprojects are disabled, set project row as inactive.
	 *
	 * @since 0.9.3
	 */
	function tstatsUpdatePluginsTableRow() {
		var pluginSubprojectsCount = {};
		var pluginSubprojectsTotal = {};

		// Get the clicked subproject class from Settings projects table.
		var checkboxClass = $( event.target ).attr( 'class' );

		// Get project row id.
		var id = checkboxClass.substring( 'checkbox-subproject '.length );

		// Set row subprojects count.
		pluginSubprojectsCount = $( 'input.' + id + ':checked' ).length;

		// Update row subprojects data count.
		$( 'input.' + id ).parents( 'tr' ).attr( 'data-subprojects', pluginSubprojectsCount );

		// Set row subprojects total.
		pluginSubprojectsTotal = $( 'input.' + id ).length;

		switch ( pluginSubprojectsCount ) {
			case pluginSubprojectsTotal:
				// Set project row as active.
				$( 'input.' + id ).parents( 'tr' ).addClass( 'active' ).removeClass( 'inactive' );
				// Set project checkbox as unchecked.
				$( 'input#' + id ).prop(
					{
						checked: true,
					}
				);
				console.log( 'Project fully active.' );
				break;
			case 0:
				// Set project row as inactive.
				$( 'input.' + id ).parents( 'tr' ).addClass( 'inactive' ).removeClass( 'active' );
				// Set project checkbox as unchecked.
				$( 'input#' + id ).prop(
					{
						checked: false,
					}
				);
				console.log( 'Project inactive.' );
				break;
			default:
				// Set project row as active.
				$( 'input.' + id ).parents( 'tr' ).addClass( 'active' ).removeClass( 'inactive' );
				// Set project checkbox as indeterminate.
				$( 'input#' + id ).prop(
					{
						checked: true,
					}
				);
				console.log( 'Project partially active (css "indeterminate").' );
		}

		tstatsUpdatePluginsTableAll();

		console.log( 'Clicked single project ID "' + id + '" subproject checkbox.' );
		console.log( pluginSubprojectsCount + ' subproject(s) of project ID "' + id + '" selected.' );
	}

	/**
	 * Click plugins table header.
	 *
	 * @since 1.0.2
	 */
	function tstatsClickPluginsSort() {
		// Unfocus element.
		$( this ).blur();
	}
} );
