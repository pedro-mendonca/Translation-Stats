/**
 * External dependencies
 */
import { afterEach, describe, expect, it, vi } from 'vitest';

/**
 * Internal dependencies
 */
import { initStatsWidgets } from '../../assets/src/js/stats-widget';
import { initSettings } from '../../assets/src/js/settings';
import { addSettingsLink, initThemesStats } from '../../assets/src/js/themes';

vi.mock( '../../assets/src/js/stats-widget', () => ( { initStatsWidgets: vi.fn() } ) );
vi.mock( '../../assets/src/js/settings', () => ( { initSettings: vi.fn() } ) );
vi.mock( '../../assets/src/js/themes', () => ( { addSettingsLink: vi.fn(), initThemesStats: vi.fn() } ) );
vi.mock( 'tablesorter/dist/js/jquery.tablesorter.combined', () => ( {} ) );

/**
 * Wait for the jQuery document ready callbacks, run after the document is loaded.
 */
async function documentReady() {
	for ( let tick = 0; tick < 10; tick++ ) {
		await new Promise( ( resolve ) => setTimeout( resolve, 0 ) );
	}
}

afterEach( () => {
	vi.resetModules();
	delete window.tstatsThemes;
} );

describe( 'entries', () => {
	it( 'initializes the plugins stats widgets on document ready', async () => {
		await import( '../../assets/src/js/admin-plugins' );
		await documentReady();

		expect( initStatsWidgets ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'initializes the settings page on document ready', async () => {
		await import( '../../assets/src/js/admin-settings' );
		await documentReady();

		expect( initSettings ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'extends the themes views right away, and adds the settings link on document ready', async () => {
		window.tstatsThemes = { settingsUrl: 'options-general.php?page=translation-stats#themes' };

		await import( '../../assets/src/js/admin-themes' );

		// Before the WordPress themes views are rendered on document ready.
		expect( initThemesStats ).toHaveBeenCalledTimes( 1 );
		expect( addSettingsLink ).not.toHaveBeenCalled();

		await documentReady();

		expect( addSettingsLink ).toHaveBeenCalledWith( 'options-general.php?page=translation-stats#themes' );
	} );

	it( 'adds no settings link without the themes data', async () => {
		await import( '../../assets/src/js/admin-themes' );
		await documentReady();

		expect( addSettingsLink ).toHaveBeenCalledWith( undefined );
	} );
} );
