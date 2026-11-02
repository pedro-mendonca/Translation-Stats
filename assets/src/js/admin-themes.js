/**
 * External dependencies
 */
import jQuery from 'jquery';

/**
 * Internal dependencies
 */
import { addSettingsLink, initThemesStats } from './themes';

// Extend the themes views before the Themes screen renders them, on document ready.
initThemesStats();

jQuery( document ).ready( function () {
	addSettingsLink( window.tstatsThemes && window.tstatsThemes.settingsUrl );
} );
