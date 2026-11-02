/**
 * External dependencies
 */
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import jQuery from 'jquery';

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { createQueue } from '../../assets/src/js/stats-widget';
import { addSettingsLink, initThemesStats, renderCardBar, themeStatsPath } from '../../assets/src/js/themes';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );

/**
 * Create the WordPress themes views, rendering the theme card and the theme details like the Themes screen templates.
 *
 * @return {Object} WordPress themes, with the views.
 */
function createThemes() {
	function Theme( model ) {
		this.model = model;
		this.$el = jQuery( '<div class="theme"></div>' );
	}

	Theme.prototype.render = function() {
		this.$el.html( '<div class="theme-screenshot"></div><div class="theme-id-container"><h2 class="theme-name">Theme</h2></div>' ).attr( 'data-slug', this.model.get( 'id' ) );
		return this;
	};

	function Details( model ) {
		this.model = model;
		this.$el = jQuery( '<div class="theme-overlay"></div>' );
	}

	Details.prototype.render = function() {
		this.$el.html( '<div class="theme-about"><div class="theme-screenshots"><div class="screenshot"></div></div><div class="theme-info"><h2 class="theme-name">Theme</h2><p class="theme-description">Description</p></div></div>' );
		return this;
	};

	return { view: { Theme, Details } };
}

/**
 * Create a theme model, with the translation stats data.
 *
 * @param {string}      slug  Theme slug.
 * @param {Object|null} stats Translation stats data, or null if the theme isn't enabled.
 *
 * @return {Object} Theme model.
 */
function createModel( slug, stats ) {
	const attributes = { id: slug };

	if ( stats ) {
		attributes.translationStats = {
			title: 'Translation for <a href="#">Português</a><span class="tstats-update-link"><span class="tstats-update-status"></span><button class="tstats-update-button" type="button"></button></span>',
			stats: '<div class="translation-stats-placeholder">Placeholder</div>',
			notice: '<div class="notice notice-warning translation-stats-loading hidden"><p></p></div>',
			language: 'Português',
			cached: false,
			percent: null,
			...stats,
		};
	}

	return {
		get: ( key ) => attributes[ key ],
	};
}

/**
 * Wait for the queued requests and their promises.
 */
async function flush() {
	// The queue and the request handlers resolve in a few chained promises.
	for ( let tick = 0; tick < 10; tick++ ) {
		await new Promise( ( resolve ) => setTimeout( resolve, 0 ) );
	}
}

beforeEach( () => {
	apiFetch.mockResolvedValue( { html: '<div class="translation-stats-content-stats">Loaded stats</div>', percent: 91 } );
} );

afterEach( () => {
	vi.clearAllMocks();
	document.body.innerHTML = '';
} );

describe( 'renderCardBar', () => {
	it( 'shows the language and the percent translated, filled with its color', () => {
		const card = renderCardBar( { language: 'Português', percent: 91 } );

		expect( card.find( '.subproject-bar__name' ).text() ).toBe( 'Translated to Português: 91%' );
		expect( card.find( 'a' ).hasClass( 'enabled' ) ).toBe( true );
		expect( card.find( 'div.subproject' ).hasClass( 'percent90' ) ).toBe( true );
		expect( card.find( 'div.subproject' ).css( 'width' ) ).toBe( '91%' );
	} );

	it( 'shows the language while loading, empty', () => {
		const card = renderCardBar( { language: 'Português', percent: null } );

		expect( card.find( '.subproject-bar__name' ).text() ).toBe( 'Translated to Português' );
		expect( card.find( 'a' ).hasClass( 'placeholder' ) ).toBe( true );
		expect( card.find( 'div.subproject' ).attr( 'class' ) ).toBe( 'subproject project' );
	} );
} );

describe( 'initThemesStats', () => {
	it( 'does nothing without the themes views', () => {
		expect( initThemesStats( undefined ) ).toBe( false );
		expect( initThemesStats( { view: {} } ) ).toBe( false );
	} );

	it( 'extends the WordPress themes views by default', () => {
		window.wp = { themes: createThemes() };

		const render = window.wp.themes.view.Theme.prototype.render;

		expect( initThemesStats() ).toBe( true );
		expect( window.wp.themes.view.Theme.prototype.render ).not.toBe( render );

		delete window.wp;
	} );

	it( 'adds the cached stats bar to the enabled theme cards, without requesting the stats', () => {
		const themes = createThemes();
		initThemesStats( themes, createQueue() );

		const enabled = new themes.view.Theme( createModel( 'enabled', { cached: true, percent: 100 } ) ).render();
		const disabled = new themes.view.Theme( createModel( 'disabled', null ) ).render();

		expect( enabled.$el.find( '.tstats-theme-card .subproject-bar__name' ).text() ).toBe( 'Translated to Português: 100%' );

		// At the bottom of the screenshot, without new card sections.
		expect( enabled.$el.find( '.theme-screenshot > .tstats-theme-card:last-child' ).length ).toBe( 1 );
		expect( enabled.$el.children().map( ( index, child ) => child.className ).get() ).toEqual( [ 'theme-screenshot', 'theme-id-container' ] );
		expect( disabled.$el.find( '.tstats-theme-card' ).length ).toBe( 0 );
		expect( apiFetch ).not.toHaveBeenCalled();
	} );

	it( 'loads the stats without cache once, and updates the card bar', async () => {
		const themes = createThemes();
		initThemesStats( themes, createQueue() );

		const model = createModel( 'generatepress', {} );
		const card = new themes.view.Theme( model ).render();
		document.body.append( card.$el[ 0 ] );

		expect( card.$el.find( '.tstats-theme-card a' ).hasClass( 'placeholder' ) ).toBe( true );

		// A new render while loading doesn't request the stats again.
		card.render();

		await flush();

		expect( apiFetch ).toHaveBeenCalledTimes( 1 );
		expect( apiFetch ).toHaveBeenCalledWith( { path: themeStatsPath( 'generatepress' ) } );
		expect( card.$el.find( '.tstats-theme-card .subproject-bar__name' ).text() ).toBe( 'Translated to Português: 91%' );
		expect( model.get( 'translationStats' ) ).toEqual( expect.objectContaining( { cached: true, percent: 91, loading: false } ) );
	} );

	it( 'shows the moving stripes in the theme card while updating', async () => {
		let resolve;
		apiFetch.mockReturnValue( new Promise( ( res ) => {
			resolve = res;
		} ) );

		const themes = createThemes();
		initThemesStats( themes, createQueue() );

		const card = new themes.view.Theme( createModel( 'generatepress', {} ) ).render();
		document.body.append( card.$el[ 0 ] );

		await flush();

		expect( card.$el.find( '.tstats-theme-card' ).hasClass( 'tstats-loading' ) ).toBe( true );

		resolve( { html: '<div class="translation-stats-content-stats">Loaded stats</div>', percent: 91 } );

		await flush();

		expect( card.$el.find( '.tstats-theme-card' ).hasClass( 'tstats-loading' ) ).toBe( false );
		expect( card.$el.find( '.tstats-theme-card a' ).hasClass( 'enabled' ) ).toBe( true );
	} );

	it( 'adds the widget to the theme details, with the update button, and forces the update on click', async () => {
		const themes = createThemes();
		initThemesStats( themes, createQueue() );

		const model = createModel( 'generatepress', { cached: true, percent: 100, stats: '<div class="translation-stats-content-stats">Cached stats</div>' } );
		const details = new themes.view.Details( model ).render();
		document.body.append( details.$el[ 0 ] );

		// Above the description.
		const widget = details.$el.find( '.theme-info > .tstats-theme-widget[data-slug="generatepress"] + .theme-description' ).prev();

		expect( widget.length ).toBe( 1 );

		expect( widget.find( '.translation-stats-title' ).text() ).toContain( 'Translation for Português' );
		expect( widget.find( 'div.content' ).text() ).toBe( 'Cached stats' );
		expect( widget.find( 'button.tstats-update-button' ).css( 'display' ) ).toBe( 'inline-block' );
		expect( apiFetch ).not.toHaveBeenCalled();

		widget.find( 'button.tstats-update-button' ).trigger( 'click' );

		await flush();

		expect( apiFetch ).toHaveBeenCalledWith( { path: themeStatsPath( 'generatepress' ) + '?force=true' } );
		expect( widget.find( 'div.content' ).text() ).toBe( 'Loaded stats' );
		expect( model.get( 'translationStats' ).stats ).toBe( '<div class="translation-stats-content-stats">Loaded stats</div>' );
	} );

	it( 'loads the stats without cache in the theme details', async () => {
		const themes = createThemes();
		initThemesStats( themes, createQueue() );

		const details = new themes.view.Details( createModel( 'generatepress', {} ) ).render();
		document.body.append( details.$el[ 0 ] );

		await flush();

		expect( apiFetch ).toHaveBeenCalledWith( { path: themeStatsPath( 'generatepress' ) } );
		expect( details.$el.find( 'div.content' ).text() ).toBe( 'Loaded stats' );
	} );

	it( 'keeps the stats without cache if the request fails', async () => {
		apiFetch.mockRejectedValue( { code: 'translation_stats_api_unavailable', message: 'Unavailable' } );

		const themes = createThemes();
		initThemesStats( themes, createQueue() );

		const model = createModel( 'generatepress', {} );
		const card = new themes.view.Theme( model ).render();
		document.body.append( card.$el[ 0 ] );

		await flush();

		expect( model.get( 'translationStats' ) ).toEqual( expect.objectContaining( { cached: false, percent: null, loading: false } ) );
		expect( card.$el.find( '.tstats-theme-card a' ).hasClass( 'placeholder' ) ).toBe( true );
		expect( card.$el.find( '.tstats-theme-card' ).hasClass( 'tstats-loading' ) ).toBe( false );
	} );
} );

describe( 'addSettingsLink', () => {
	it( 'adds the link to the themes settings after the header buttons', () => {
		document.body.innerHTML = '<div class="wrap"><h1 class="wp-heading-inline">Themes</h1> <a href="theme-install.php" class="page-title-action">Add New Theme</a></div>';

		expect( addSettingsLink( 'options-general.php?page=translation-stats#themes' ) ).toBe( true );

		const link = jQuery( '.wrap > a.page-title-action' ).last();

		expect( link.hasClass( 'tstats-themes-settings-link' ) ).toBe( true );
		expect( link.attr( 'href' ) ).toBe( 'options-general.php?page=translation-stats#themes' );
		expect( link.text().trim() ).toBe( 'Translation Stats' );
	} );

	it( 'adds no link without permission or without the header buttons', () => {
		document.body.innerHTML = '<div class="wrap"><a href="theme-install.php" class="page-title-action">Add New Theme</a></div>';

		expect( addSettingsLink( '' ) ).toBe( false );

		document.body.innerHTML = '<div class="wrap"></div>';

		expect( addSettingsLink( 'options-general.php?page=translation-stats#themes' ) ).toBe( false );
		expect( jQuery( '.tstats-themes-settings-link' ).length ).toBe( 0 );
	} );
} );

describe( 'addThemeWidget', () => {
	it( 'adds the widget at the end of the theme info, without description', () => {
		const themes = createThemes();
		themes.view.Details.prototype.render = function() {
			this.$el.html( '<div class="theme-info"><h2 class="theme-name">Theme</h2></div>' );
			return this;
		};
		initThemesStats( themes, createQueue() );

		const details = new themes.view.Details( createModel( 'generatepress', { cached: true, percent: 100 } ) ).render();

		expect( details.$el.find( '.theme-info > .tstats-theme-widget:last-child' ).length ).toBe( 1 );
	} );

	it( 'adds no widget to the details of the themes without stats', () => {
		const themes = createThemes();
		initThemesStats( themes, createQueue() );

		const details = new themes.view.Details( createModel( 'disabled', null ) ).render();

		expect( details.$el.find( '.tstats-theme-widget' ).length ).toBe( 0 );
	} );
} );

