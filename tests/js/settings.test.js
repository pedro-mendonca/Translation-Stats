/**
 * External dependencies
 */
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import jQuery from 'jquery';

/**
 * Internal dependencies
 */
import { initSettings, tstatsOnLoad } from '../../assets/src/js/settings';

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

describe( 'plugins table', () => {
	it( 'sorts and filters the plugins table', () => {
		addSettingsPage();

		initSettings();

		expect( jQuery.fn.tablesorter ).toHaveBeenCalledTimes( 1 );
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
