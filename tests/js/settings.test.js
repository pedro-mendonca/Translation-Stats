/**
 * External dependencies
 */
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import jQuery from 'jquery';

/**
 * Internal dependencies
 */
import { initSettings, tstatsOnLoad, tstatsPluginsUpdateColumn } from '../../assets/src/js/settings';

const SUBPROJECTS = [ 'dev', 'dev-readme', 'stable', 'stable-readme' ];

/**
 * Add the settings page, with the tabs and the plugins table.
 *
 * @param {string[]} plugins Slugs of the plugins on WordPress.org.
 */
function addSettingsPage( plugins = [ 'first', 'second' ] ) {
	const header = SUBPROJECTS.map( ( subproject ) =>
		`<th class="manage-column column-subproject" data-subproject="${ subproject }">
			<input class="checkbox-subproject" id="subprojects_${ subproject }" type="checkbox" value="true" data-indeterminate="false" />
		</th>`,
	).join( '' );

	const row = ( plugin ) =>
		`<tr class="inactive" data-plugin="${ plugin }" data-subprojects="0">
			<th><input id="plugins_${ plugin }" class="checkbox-plugin" type="checkbox" value="true" /></th>
			${ SUBPROJECTS.map( ( subproject ) => `<td><input class="checkbox-plugin-subproject" data-plugin="${ plugin }" data-subproject="${ subproject }" type="checkbox" value="true" /></td>` ).join( '' ) }
		</tr>`;

	document.body.innerHTML = `<div class="tstats-settings__content">
		<div class="nav-tab-wrapper">
			<a href="#plugins" class="nav-tab">Plugins</a>
			<a href="#settings" class="nav-tab">Settings</a>
		</div>
		<div class="tabs-content"><form>
			<input type="hidden" name="_wp_http_referer" value="/wp-admin/options-general.php?page=translation-stats" />
			<input type="hidden" name="_wp_http_referer" value="/wp-admin/options-general.php?page=translation-stats" />
			<div id="tab-plugins" class="tab-content">
				<table id="tstats-table-plugins" class="tstats-plugin-list-table">
					<thead><tr>
						<td><input class="all_plugins" id="all_plugins" type="checkbox" value="true" data-indeterminate="false" /></td>
						${ header }
					</tr></thead>
					<tbody>
						${ plugins.map( row ).join( '' ) }
						<tr class="disabled" data-plugin="disabled"><th></th></tr>
					</tbody>
				</table>
			</div>
			<div id="tab-settings" class="tab-content"></div>
		</form></div>
	</div>`;
}

/**
 * Get a plugin row and its checkboxes.
 *
 * @param {string} plugin Plugin slug.
 *
 * @return {Object} The row, the plugin checkbox and the subprojects checkboxes.
 */
function pluginRow( plugin ) {
	const row = jQuery( `tr[data-plugin="${ plugin }"]` );
	return {
		row,
		checkbox: row.find( 'input.checkbox-plugin' ),
		subprojects: row.find( 'input.checkbox-plugin-subproject' ),
	};
}

/**
 * Get the checked subprojects of a plugin.
 *
 * @param {string} plugin Plugin slug.
 *
 * @return {string[]} Slugs of the checked subprojects.
 */
function checkedSubprojects( plugin ) {
	return pluginRow( plugin ).subprojects.filter( ':checked' ).map( ( index, input ) => input.dataset.subproject ).get();
}

/**
 * Get the header checkbox state.
 *
 * @param {string} id Checkbox ID.
 *
 * @return {Object} Whether the checkbox is checked and indeterminate.
 */
function headerCheckbox( id ) {
	const checkbox = jQuery( `#${ id }` );
	return {
		checked: checkbox.prop( 'checked' ),
		indeterminate: checkbox.attr( 'data-indeterminate' ),
	};
}

beforeEach( () => {
	vi.spyOn( console, 'log' ).mockImplementation( () => {} );
	jQuery.fn.tablesorter = vi.fn().mockReturnThis();
} );

afterEach( () => {
	delete jQuery.fn.tablesorter;
	window.location.hash = '';
	document.body.innerHTML = '';
} );

describe( 'tabs', () => {
	it( 'shows the plugins tab by default', () => {
		addSettingsPage();

		tstatsOnLoad();

		expect( jQuery( 'a[href="#plugins"]' ).hasClass( 'nav-tab-active' ) ).toBe( true );
		expect( jQuery( '#tab-plugins' ).hasClass( 'hidden' ) ).toBe( false );
		expect( jQuery( '#tab-settings' ).hasClass( 'hidden' ) ).toBe( true );
	} );

	it( 'shows the tab from the URL hash', () => {
		addSettingsPage();
		window.location.hash = '#settings';

		tstatsOnLoad();

		expect( jQuery( 'a[href="#settings"]' ).hasClass( 'nav-tab-active' ) ).toBe( true );
		expect( jQuery( 'a[href="#plugins"]' ).hasClass( 'nav-tab-active' ) ).toBe( false );
		expect( jQuery( '#tab-settings' ).hasClass( 'hidden' ) ).toBe( false );
		expect( jQuery( '#tab-plugins' ).hasClass( 'hidden' ) ).toBe( true );
	} );

	it( 'changes the tab on click', () => {
		addSettingsPage();
		initSettings();

		jQuery( 'a[href="#settings"]' ).trigger( 'click' );

		expect( jQuery( 'a[href="#settings"]' ).hasClass( 'nav-tab-active' ) ).toBe( true );
		expect( jQuery( 'a[href="#plugins"]' ).hasClass( 'nav-tab-active' ) ).toBe( false );
		expect( jQuery( '#tab-settings' ).hasClass( 'hidden' ) ).toBe( false );
		expect( jQuery( '#tab-plugins' ).hasClass( 'hidden' ) ).toBe( true );
	} );
} );

describe( 'form referer', () => {
	/**
	 * Get the settings form referers.
	 *
	 * @return {string[]} Referers of the settings sections.
	 */
	function referers() {
		return jQuery( 'input[name="_wp_http_referer"]' ).map( ( index, input ) => input.value ).get();
	}

	it( 'returns to the active tab after saving', () => {
		addSettingsPage();
		window.location.hash = '#settings';

		tstatsOnLoad();

		expect( referers() ).toEqual( Array( 2 ).fill( '/wp-admin/options-general.php?page=translation-stats#settings' ) );
	} );

	it( 'returns to the clicked tab after saving', () => {
		addSettingsPage();
		window.location.hash = '#settings';
		initSettings();

		jQuery( 'a[href="#plugins"]' ).trigger( 'click' );

		expect( referers() ).toEqual( Array( 2 ).fill( '/wp-admin/options-general.php?page=translation-stats#plugins' ) );
	} );
} );

describe( 'plugins table', () => {
	it( 'sorts and filters the plugins table', () => {
		addSettingsPage();

		initSettings();

		// The plugins and themes tables.
		expect( jQuery.fn.tablesorter ).toHaveBeenCalledTimes( 2 );
		expect( jQuery.fn.tablesorter.mock.contexts[ 0 ].attr( 'id' ) ).toBe( 'tstats-table-plugins' );
		expect( jQuery.fn.tablesorter ).toHaveBeenCalledWith( expect.objectContaining( {
			sortList: [ [ 1, 0 ] ],
			widgets: [ 'filter' ],
			widgetOptions: expect.objectContaining( { filter_external: '#plugins-search-input' } ),
		} ) );
	} );

	it( 'selects all the subprojects of a plugin, and updates the header', () => {
		addSettingsPage();
		initSettings();
		const first = pluginRow( 'first' );

		first.checkbox.trigger( 'click' );

		expect( first.row.hasClass( 'active' ) ).toBe( true );
		expect( first.row.attr( 'data-subprojects' ) ).toBe( '4' );
		expect( checkedSubprojects( 'first' ) ).toEqual( SUBPROJECTS );
		expect( checkedSubprojects( 'second' ) ).toEqual( [] );

		// Only one of the two plugins has the subprojects.
		expect( headerCheckbox( 'subprojects_stable' ) ).toEqual( { checked: true, indeterminate: 'true' } );
		expect( headerCheckbox( 'all_plugins' ) ).toEqual( { checked: true, indeterminate: 'true' } );

		// Click again to unselect the plugin with all the subprojects.
		first.checkbox.trigger( 'click' );

		expect( first.row.hasClass( 'inactive' ) ).toBe( true );
		expect( checkedSubprojects( 'first' ) ).toEqual( [] );
		expect( headerCheckbox( 'all_plugins' ) ).toEqual( { checked: false, indeterminate: 'false' } );
	} );

	it( 'selects all the plugins, except the disabled', () => {
		addSettingsPage();
		initSettings();

		jQuery( '#all_plugins' ).trigger( 'click' );

		expect( checkedSubprojects( 'first' ) ).toEqual( SUBPROJECTS );
		expect( checkedSubprojects( 'second' ) ).toEqual( SUBPROJECTS );
		expect( pluginRow( 'disabled' ).row.hasClass( 'active' ) ).toBe( false );
		expect( headerCheckbox( 'subprojects_dev' ) ).toEqual( { checked: true, indeterminate: 'false' } );
		expect( headerCheckbox( 'all_plugins' ) ).toEqual( { checked: true, indeterminate: 'false' } );

		// Click again to unselect all the plugins.
		jQuery( '#all_plugins' ).trigger( 'click' );

		expect( checkedSubprojects( 'first' ) ).toEqual( [] );
		expect( checkedSubprojects( 'second' ) ).toEqual( [] );
		expect( headerCheckbox( 'all_plugins' ) ).toEqual( { checked: false, indeterminate: 'false' } );
	} );

	it( 'selects a subproject for all the plugins', () => {
		addSettingsPage();
		initSettings();

		jQuery( '#subprojects_stable' ).trigger( 'click' );

		expect( checkedSubprojects( 'first' ) ).toEqual( [ 'stable' ] );
		expect( checkedSubprojects( 'second' ) ).toEqual( [ 'stable' ] );
		expect( pluginRow( 'first' ).row.hasClass( 'active' ) ).toBe( true );
		expect( pluginRow( 'first' ).row.attr( 'data-subprojects' ) ).toBe( '1' );
		expect( pluginRow( 'first' ).checkbox.prop( 'checked' ) ).toBe( true );
		expect( headerCheckbox( 'subprojects_stable' ) ).toEqual( { checked: true, indeterminate: 'false' } );
		expect( headerCheckbox( 'all_plugins' ) ).toEqual( { checked: true, indeterminate: 'true' } );

		// Click again to unselect the subproject for all the plugins.
		jQuery( '#subprojects_stable' ).trigger( 'click' );

		expect( checkedSubprojects( 'first' ) ).toEqual( [] );
		expect( pluginRow( 'first' ).row.hasClass( 'inactive' ) ).toBe( true );
		expect( pluginRow( 'first' ).checkbox.prop( 'checked' ) ).toBe( false );
	} );

	it( 'keeps the subproject column header without plugins on WordPress.org', () => {
		addSettingsPage( [] );
		jQuery( '#subprojects_stable' ).prop( 'checked', true ).attr( 'data-indeterminate', 'true' );

		tstatsPluginsUpdateColumn( 'stable' );

		expect( headerCheckbox( 'subprojects_stable' ) ).toEqual( { checked: true, indeterminate: 'true' } );
	} );

	it( 'unfocuses the plugins table header after clicking to sort', () => {
		addSettingsPage();
		jQuery( '#tstats-table-plugins thead tr' ).append( '<th class="tablesorter-header" tabindex="0">Plugin</th>' );
		initSettings();

		const header = jQuery( '#tstats-table-plugins th.tablesorter-header' );
		header[ 0 ].focus();

		header.trigger( 'mouseup' );

		expect( document.activeElement ).not.toBe( header[ 0 ] );
	} );

	it( 'activates the plugin row when selecting one of its subprojects', () => {
		addSettingsPage();
		initSettings();
		const first = pluginRow( 'first' );
		const dev = first.subprojects.filter( '[data-subproject="dev"]' );

		dev.trigger( 'click' );

		expect( first.row.hasClass( 'active' ) ).toBe( true );
		expect( first.row.attr( 'data-subprojects' ) ).toBe( '1' );
		expect( first.checkbox.prop( 'checked' ) ).toBe( true );
		expect( headerCheckbox( 'subprojects_dev' ) ).toEqual( { checked: true, indeterminate: 'true' } );

		dev.trigger( 'click' );

		expect( first.row.hasClass( 'inactive' ) ).toBe( true );
		expect( first.checkbox.prop( 'checked' ) ).toBe( false );
		expect( headerCheckbox( 'subprojects_dev' ) ).toEqual( { checked: false, indeterminate: 'false' } );
	} );
} );

/**
 * Add the themes table to the settings page.
 *
 * @param {Object} themes Slugs of the themes on WordPress.org, with their selected status.
 */
function addThemesTable( themes ) {
	const rows = Object.keys( themes ).map( ( theme ) =>
		`<tr class="${ themes[ theme ] ? 'active' : 'inactive' }" data-theme="${ theme }">
			<th><input id="themes_${ theme }" class="checkbox-theme" type="checkbox" value="true" ${ themes[ theme ] ? 'checked' : '' } /></th>
		</tr>`,
	).join( '' );

	jQuery( '.tabs-content form' ).append( `<div id="tab-themes" class="tab-content">
		<table id="tstats-table-themes" class="tstats-theme-list-table">
			<thead><tr><td><input class="all_themes" id="all_themes" type="checkbox" value="true" data-indeterminate="false" /></td></tr></thead>
			<tbody>
				${ rows }
				<tr class="disabled" data-theme="disabled"><th></th></tr>
			</tbody>
		</table>
	</div>` );
}

/**
 * Get the selected themes.
 *
 * @return {string[]} Slugs of the selected themes.
 */
function selectedThemes() {
	return jQuery( '#tstats-table-themes input.checkbox-theme:checked' ).closest( 'tr' ).map( ( index, row ) => row.dataset.theme ).get();
}

describe( 'themes table', () => {
	it( 'sorts and filters the themes table', () => {
		addSettingsPage();
		addThemesTable( { first: false } );

		initSettings();

		const table = jQuery.fn.tablesorter.mock.contexts.find( ( context ) => 'tstats-table-themes' === context.attr( 'id' ) );
		const call = jQuery.fn.tablesorter.mock.contexts.indexOf( table );

		expect( table ).toBeDefined();
		expect( jQuery.fn.tablesorter.mock.calls[ call ][ 0 ] ).toEqual( expect.objectContaining( {
			sortList: [ [ 1, 0 ] ],
			widgetOptions: expect.objectContaining( { filter_external: '#themes-search-input', filter_reset: '#themes-search-reset' } ),
		} ) );
	} );

	it( 'selects a theme, and updates the row and the header', () => {
		addSettingsPage();
		addThemesTable( { first: false, second: false } );
		initSettings();

		jQuery( '#themes_first' ).trigger( 'click' );

		expect( selectedThemes() ).toEqual( [ 'first' ] );
		expect( jQuery( 'tr[data-theme="first"]' ).hasClass( 'active' ) ).toBe( true );
		expect( headerCheckbox( 'all_themes' ) ).toEqual( { checked: true, indeterminate: 'true' } );

		jQuery( '#themes_first' ).trigger( 'click' );

		expect( selectedThemes() ).toEqual( [] );
		expect( jQuery( 'tr[data-theme="first"]' ).hasClass( 'inactive' ) ).toBe( true );
		expect( headerCheckbox( 'all_themes' ) ).toEqual( { checked: false, indeterminate: 'false' } );
	} );

	it( 'selects all the themes, and unselects them if all are selected', () => {
		addSettingsPage();
		addThemesTable( { first: true, second: false } );
		initSettings();

		jQuery( '#all_themes' ).trigger( 'click' );

		expect( selectedThemes() ).toEqual( [ 'first', 'second' ] );
		expect( jQuery( 'tr[data-theme="second"]' ).hasClass( 'active' ) ).toBe( true );
		expect( jQuery( 'tr[data-theme="disabled"]' ).hasClass( 'active' ) ).toBe( false );
		expect( headerCheckbox( 'all_themes' ) ).toEqual( { checked: true, indeterminate: 'false' } );

		jQuery( '#all_themes' ).trigger( 'click' );

		expect( selectedThemes() ).toEqual( [] );
		expect( headerCheckbox( 'all_themes' ) ).toEqual( { checked: false, indeterminate: 'false' } );
	} );
} );

