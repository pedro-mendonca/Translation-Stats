/**
 * External dependencies
 */
import jQuery from 'jquery';

const $ = jQuery;

/**
 * Initialize the settings page: the tabs, and the plugins table sorting and selection.
 *
 * @since 1.5.0
 */
export function initSettings() {
	// Run scripts on page load.
	tstatsOnLoad();

	// Sort and filter plugins settings list.
	tstatsPluginsSort();

	// Sort and filter themes settings list.
	tstatsThemesSort();

	// Click on Settings Navigation Tab.
	$( '.tstats-settings__content > .nav-tab-wrapper a' ).on( 'click', tstatsClickSettingsTab );

	// Select all projects checkbox on Settings projects table.
	$( '.tstats-settings__content #all_plugins' ).on( 'click', tstatsPluginsSelectAll );

	// Select single project checkbox on Settings projects table.
	$( '.tstats-settings__content input.checkbox-plugin' ).on( 'click', tstatsPluginsSelectPlugin );

	// Select single project checkbox on Settings projects table.
	$( '.tstats-settings__content input.checkbox-subproject' ).on( 'click', tstatsPluginsSelectSubprojectColumn );

	// Select single plugin subproject checkbox on Settings projects table.
	$( '.tstats-settings__content input.checkbox-plugin-subproject' ).on( 'click', tstatsPluginsSelectPluginSubproject );

	// Select all themes checkbox on Settings themes table.
	$( '.tstats-settings__content #all_themes' ).on( 'click', tstatsThemesSelectAll );

	// Select single theme checkbox on Settings themes table.
	$( '.tstats-settings__content input.checkbox-theme' ).on( 'click', tstatsThemesSelectTheme );

	// Click plugins table header.
	$( '.tablesorter-header:not(.sorter-false)' ).on( 'mouseup', tstatsClickPluginsSort );

	// Select single subproject checkbox on Settings projects table.
	// $( 'table.wp-list-table td.translation-stats button.tstats-update-button' ).on( 'click', tstatsPluginSubprojectsLoadAjax );
}

/**
 * Translation Stats scripts executed on page load.
 *
 * @since 0.9.3
 */
export function tstatsOnLoad() {
	// Set default Settings Active tab.
	const tstatsDefaultTabID = '#plugins';

	// Get URL hash.
	const tstatsSettingsURLHash = window.location.hash;

	// Declare variable.
	let tstatsActiveTabID;

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

		// Return to the active tab after saving.
		tstatsSetFormReferer( tstatsActiveTabID );
	}

	console.log( 'Loaded admin-settings.js' );
}

/**
 * Translation Stats plugins list table sorting.
 *
 * @since 1.0.2
 */
export function tstatsPluginsSort() {
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
 * Set the active tab in the settings form referer, to return to the same tab after saving.
 * The browser doesn't send the URL hash, and options.php redirects to the referer.
 *
 * @since 1.5.0
 *
 * @param {string} tabID Active tab ID ( e.g. '#themes' ).
 */
export function tstatsSetFormReferer( tabID ) {
	$( '.tabs-content form input[name="_wp_http_referer"]' ).val( function( index, referer ) {
		return referer.split( '#' )[ 0 ] + tabID;
	} );
}

/**
 * Change active Settings Navigation Tab.
 *
 * @since 0.9.3
 */
export function tstatsClickSettingsTab() {
	const tstatsActiveTabContentID = $( this ).attr( 'href' ).replace( '#', '#tab-' );

	// Return to the active tab after saving.
	tstatsSetFormReferer( $( this ).attr( 'href' ) );

	// Active tab.
	$( this ).parent().children().removeClass( 'nav-tab-active' );
	$( this ).addClass( 'nav-tab-active' );

	// Active tab content.
	$( '.tabs-content form' ).children( '.tab-content' ).addClass( 'hidden' );
	$( '.tabs-content form div' + tstatsActiveTabContentID ).removeClass( 'hidden' );
}

/**
 * Activate/deactivate all plugins and subprojects in Settings projects table.
 *
 * @since 1.3.0
 */
export function tstatsPluginsSelectAll() {
	const activeSubprojects = $( '#tstats-table-plugins thead input[data-indeterminate="false"].checkbox-subproject:checked' ).length;

	$( '#tstats-table-plugins thead input#all_plugins' ).attr( 'data-indeterminate', false );

	console.log( 'activeSubprojects', activeSubprojects );

	if ( activeSubprojects < 4 ) {
		// Activate all plugins.
		tstatsPluginsActivateAllPlugins();
	} else {
		// Deactivate all plugins.
		tstatsPluginsDeactivateAllPlugins();
	}

	// Update table header.
	tstatsPluginsUpdateHeader();
}

/**
 * Activate all plugins and subprojects in Settings projects table.
 *
 * @since 1.3.0
 */
export function tstatsPluginsActivateAllPlugins() {
	// Set all plugin rows as active.
	$( '.tstats-plugin-list-table tr:not(.disabled)' ).each( function() {
		const plugin = $( this ).attr( 'data-plugin' );
		// Activate plugin.
		tstatsPluginsActivatePlugin( plugin );
	} );

	console.log( 'Activate all plugins.' );
}

/**
 * Deactivate all plugins and subprojects in Settings projects table.
 *
 * @since 1.3.0
 */
export function tstatsPluginsDeactivateAllPlugins() {
	// Set all plugin rows as inactive.
	$( '.tstats-plugin-list-table tr:not(.disabled)' ).each( function() {
		const plugin = $( this ).attr( 'data-plugin' );
		// Deactivate plugin.
		tstatsPluginsDeactivatePlugin( plugin );
	} );

	console.log( 'Deactivate all plugins.' );
}

/**
 * Activate single plugin and subprojects in Settings projects table.
 *
 * @since 1.3.0
 *
 * @param {string} plugin Plugin slug.
 */
export function tstatsPluginsActivatePlugin( plugin ) {
	// Set row.
	const row = $( '.tstats-plugin-list-table tr[data-plugin="' + plugin + '"]' );

	// Set plugin row as active.
	$( row ).addClass( 'active' ).removeClass( 'inactive' ).attr( 'data-subprojects', '4' );

	// Keep selected in case checked with < 4 subprojects.
	$( row ).find( 'input.checkbox-plugin' ).prop(
		{
			checked: true,
		},
	);

	// Activate plugins subprojects.
	$( row ).find( 'input.checkbox-plugin-subproject' ).prop(
		{
			checked: true,
		},
	);

	console.log( 'Activate plugin.' );
}

/**
 * Deactivate single plugin and subprojects in Settings projects table.
 *
 * @since 1.3.0
 *
 * @param {string} plugin Plugin slug.
 */
export function tstatsPluginsDeactivatePlugin( plugin ) {
	// Set row.
	const row = $( '.tstats-plugin-list-table tr[data-plugin="' + plugin + '"]' );

	// Set plugin row as inactive.
	$( row ).addClass( 'inactive' ).removeClass( 'active' ).attr( 'data-subprojects', '0' );

	// Deactivate plugin.
	$( row ).find( 'input.checkbox-plugin' ).prop(
		{
			checked: false,
		},
	);

	// Deactivate plugins subprojects.
	$( row ).find( 'input.checkbox-plugin-subproject' ).prop(
		{
			checked: false,
		},
	);

	console.log( 'Deactivate plugin.' );
}

/**
 * Activate/deactivate single plugin in Settings projects table.
 * Update table header afterwards.
 *
 * @since 1.3.0
 *
 * @param {Event} event Click event of the plugin checkbox.
 */
export function tstatsPluginsSelectPlugin( event ) {
	// Get the clicked row ID from Settings projects table.
	const id = $( event.target ).attr( 'id' );

	// Get plugin ID.
	const plugin = id.substring( 'plugins_'.length );

	// Get plugin active subprojects count.
	const activeSubprojects = $( 'input[data-plugin="' + plugin + '"].checkbox-plugin-subproject:checked' ).length;

	if ( activeSubprojects < 4 ) {
		// Activate plugin.
		tstatsPluginsActivatePlugin( plugin );
	} else {
		// Deactivate plugin.
		tstatsPluginsDeactivatePlugin( plugin );
	}

	// Update table header.
	tstatsPluginsUpdateHeader();
}

/**
 * Activate/deactivate single plugin in Settings projects table.
 *
 * @since 1.3.0
 */
export function tstatsPluginsUpdateColumns() {
	$( '#tstats-table-plugins thead th.column-subproject' ).each( function() {
		const subproject = $( this ).attr( 'data-subproject' );
		tstatsPluginsUpdateColumn( subproject );
	} );
}

/**
 * Update rows in Settings projects table.
 *
 * @since 1.3.0
 */
export function tstatsPluginsUpdateRows() {
	$( '#tstats-table-plugins tbody tr:not(.disabled)' ).each( function() {
		const plugin = $( this ).attr( 'data-plugin' );
		tstatsPluginsUpdateRow( plugin );
	} );
}

/**
 * Activate/deactivate all subprojects of a specific type in Settings projects table.
 * Update table rows afterwards.
 * Update table header afterwards.
 *
 * @since 1.3.0
 *
 * @param {Event} event Click event of the subproject column checkbox.
 */
export function tstatsPluginsSelectSubprojectColumn( event ) {
	// Get the clicked subproject ID from Settings projects table.
	const id = $( event.target ).attr( 'id' );

	// Get subproject column id.
	const subproject = id.substring( 'subprojects_'.length );

	// Get column checked count.
	const currentCount = $( 'input[data-subproject="' + subproject + '"]:checked' ).length;

	// Get column total.
	const totalCount = $( 'input[data-subproject="' + subproject + '"]' ).length;

	if ( totalCount > 0 && currentCount < totalCount ) {
		$( 'input[data-subproject="' + subproject + '"]' ).prop( 'checked', true );

		console.log( 'Subproject enabled.' );
	} else {
		$( 'input[data-subproject="' + subproject + '"]' ).prop( 'checked', false );

		console.log( 'Subproject disabled.' );
	}

	// Update all rows.
	tstatsPluginsUpdateRows();

	// Update table header.
	tstatsPluginsUpdateHeader();
}

/**
 * Activate/deactivate single plugin subproject.
 * Update plugin row afterwards.
 * Update table header afterwards.
 *
 * @since 1.3.0
 *
 * @param {Event} event Click event of the plugin subproject checkbox.
 */
export function tstatsPluginsSelectPluginSubproject( event ) {
	// Get plugin ID.
	const plugin = $( event.target ).parents( 'tr' ).attr( 'data-plugin' );

	tstatsPluginsUpdateRow( plugin );

	// Update table header.
	tstatsPluginsUpdateHeader();
}

/**
 * Update plugin row css and checkbox.
 *
 * @since 1.3.0
 *
 * @param {string} plugin Plugin slug.
 */
export function tstatsPluginsUpdateRow( plugin ) {
	// Set row.
	const row = $( '.tstats-plugin-list-table tr[data-plugin="' + plugin + '"]' );

	// Get plugin active subprojects count.
	const pluginActiveSubprojects = $( 'input[data-plugin="' + plugin + '"].checkbox-plugin-subproject:checked' ).length;

	// Update plugin subprojects data count.
	$( row ).attr( 'data-subprojects', pluginActiveSubprojects );

	if ( pluginActiveSubprojects > 0 ) {
		// Set row as active.
		$( row ).addClass( 'active' ).removeClass( 'inactive' );
		// Set plugin checkbox as checked.
		$( row ).find( 'input.checkbox-plugin' ).prop(
			{
				checked: true,
			},
		);
	} else {
		// Set row as inactive.
		$( row ).addClass( 'inactive' ).removeClass( 'active' );
		// Set project checkbox as unchecked.
		$( row ).find( 'input.checkbox-plugin' ).prop(
			{
				checked: false,
			},
		);
	}
}

/**
 * Update subproject column css and checkbox.
 *
 * @since 1.3.0
 *
 * @param {string} subproject Subproject slug.
 */
export function tstatsPluginsUpdateColumn( subproject ) {
	// Get enabled plugins count.
	const enabledPlugins = $( '#tstats-table-plugins tbody tr:not(.disabled)' ).length;

	// Get plugin subproject active plugins count.
	const subprojectActivePlugins = $( 'input[data-subproject="' + subproject + '"].checkbox-plugin-subproject:checked' ).length;

	if ( enabledPlugins > 0 ) {
		// Select subproject if > 0 plugins active.
		if ( subprojectActivePlugins > 0 ) {
			$( 'input#subprojects_' + subproject ).prop(
				{
					checked: true,
				},
			);
			// Set subproject to indeterminate not all plugins are active.
			if ( subprojectActivePlugins < enabledPlugins ) {
				// Update plugin subprojects data count.
				$( 'input#subprojects_' + subproject ).attr( 'data-indeterminate', true );
			} else {
				// Update plugin subprojects data count.
				$( 'input#subprojects_' + subproject ).attr( 'data-indeterminate', false );
			}
		} else {
			$( 'input#subprojects_' + subproject ).prop(
				{
					checked: false,
				},
			);
			$( 'input#subprojects_' + subproject ).attr( 'data-indeterminate', false );
		}
	}
}

/**
 * Update plugins table header.
 *
 * @since 1.3.0
 */
export function tstatsPluginsUpdateHeader() {
	// Set activated subprojects.
	let activeSubprojects = 0;

	// Set completely activated subprojects.
	let completelyActiveSubprojects = 0;

	// Update plugins table header subprojects columns.
	tstatsPluginsUpdateColumns();

	activeSubprojects = $( '#tstats-table-plugins thead input.checkbox-subproject:checked' ).length;
	completelyActiveSubprojects = $( '#tstats-table-plugins thead input[data-indeterminate="false"].checkbox-subproject:checked' ).length;

	if ( activeSubprojects > 0 ) {
		$( '#tstats-table-plugins thead input#all_plugins' ).prop(
			{
				checked: true,
			},
		);
		if ( completelyActiveSubprojects < 4 ) {
			// Some subprojects activated.
			$( '#tstats-table-plugins thead input#all_plugins' ).attr( 'data-indeterminate', true );
			console.log( 'Some subprojects activated.' );
		} else {
			// All subprojects completely activated.
			$( '#tstats-table-plugins thead input#all_plugins' ).attr( 'data-indeterminate', false );
			console.log( 'All subprojects completely activated.' );
		}
	} else {
		// No subprojects activated.
		$( '#tstats-table-plugins thead input#all_plugins' ).prop(
			{
				checked: false,
			},
		);
		$( '#tstats-table-plugins thead input#all_plugins' ).attr( 'data-indeterminate', false );
		console.log( 'No subprojects activated.' );
	}
	console.log( 'Activated subprojects:', activeSubprojects );
	console.log( 'Completely activated subprojects:', completelyActiveSubprojects );
}

/**
 * Click plugins table header.
 *
 * @since 1.0.2
 */
export function tstatsClickPluginsSort() {
	// Unfocus element.
	$( this ).blur();
}

/**
 * Translation Stats themes list table sorting.
 *
 * @since 1.5.0
 */
export function tstatsThemesSort() {
	$( '#tstats-table-themes' ).tablesorter( {
		// Initial sort column (2nd column).
		sortList: [
			[ 1, 0 ],
		],
		sortRestart: true,
		sortInitialOrder: 'asc',
		widgets: [
			'filter',
		],
		widgetOptions: {
			filter_external: '#themes-search-input',
			filter_columnFilters: false,
			filter_saveFilters: false,
			filter_reset: '#themes-search-reset',
		},
	} );
}

/**
 * Select all the themes in Settings themes table, or unselect them if all are selected.
 *
 * @since 1.5.0
 */
export function tstatsThemesSelectAll() {
	const themes = $( '#tstats-table-themes input.checkbox-theme' );
	const select = themes.filter( ':checked' ).length < themes.length;

	themes.prop( 'checked', select ).each( function() {
		tstatsThemesUpdateRow( $( this ) );
	} );

	tstatsThemesUpdateHeader();
}

/**
 * Select single theme in Settings themes table.
 *
 * @since 1.5.0
 */
export function tstatsThemesSelectTheme() {
	tstatsThemesUpdateRow( $( this ) );
	tstatsThemesUpdateHeader();
}

/**
 * Update the theme row status with its checkbox.
 *
 * @since 1.5.0
 *
 * @param {Object} checkbox Theme checkbox jQuery object.
 */
export function tstatsThemesUpdateRow( checkbox ) {
	const checked = checkbox.prop( 'checked' );

	checkbox.closest( 'tr' ).toggleClass( 'active', checked ).toggleClass( 'inactive', ! checked );
}

/**
 * Update the select all checkbox of the themes table, indeterminate if only some themes are selected.
 *
 * @since 1.5.0
 */
export function tstatsThemesUpdateHeader() {
	const themes = $( '#tstats-table-themes input.checkbox-theme' );
	const selected = themes.filter( ':checked' ).length;

	$( '#tstats-table-themes thead input#all_themes' )
		.prop( 'checked', selected > 0 )
		.attr( 'data-indeterminate', selected > 0 && selected < themes.length ? 'true' : 'false' );
}
