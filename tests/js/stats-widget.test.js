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
import { createQueue, initStatsWidgets, loadStats } from '../../assets/src/js/stats-widget';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );

/**
 * Add a plugin stats widget to the plugins table.
 *
 * @param {string}  slug           Plugin slug.
 * @param {Object}  options        Options.
 * @param {boolean} options.cached Whether the stats are cached and rendered.
 *
 * @return {Object} The widget table cell, as a jQuery object.
 */
function addWidget( slug, { cached = false } = {} ) {
	document.body.insertAdjacentHTML(
		'beforeend',
		`<table class="wp-list-table"><tbody><tr data-slug="${ slug }"><td class="translation-stats">
			<div class="translation-stats-title"><p>
				<span class="tstats-update-link">
					<span class="tstats-update-status" aria-live="polite"></span>
					<button class="tstats-update-button" type="button" style="display: none;"></button>
				</span>
			</p></div>
			<div class="translation-stats-content">
				<div class="content">${ cached ? '<div class="translation-stats-content-stats">Cached stats</div>' : '<div class="translation-stats-placeholder">Placeholder</div>' }</div>
				<div class="notice notice-warning translation-stats-loading hidden${ cached ? ' translation-stats-cached' : '' }"><p></p></div>
			</div>
		</td></tr></tbody></table>`,
	);

	return jQuery( `tr[data-slug="${ slug }"] td.translation-stats` );
}

/**
 * Create a promise to resolve or reject later.
 *
 * @return {Object} The promise, with its resolve and reject functions.
 */
function deferred() {
	let resolve;
	let reject;
	const promise = new Promise( ( res, rej ) => {
		resolve = res;
		reject = rej;
	} );
	return { promise, resolve, reject };
}

/**
 * Get the widget elements.
 *
 * @param {Object} widget The widget table cell.
 *
 * @return {Object} The widget status, button, notice and content.
 */
function elements( widget ) {
	return {
		status: widget.find( '.tstats-update-status' ),
		button: widget.find( 'button.tstats-update-button' ),
		notice: widget.find( 'div.notice.translation-stats-loading' ),
		content: widget.find( 'div.content' ),
	};
}

beforeEach( () => {
	vi.mocked( apiFetch ).mockReset();
	vi.spyOn( console, 'log' ).mockImplementation( () => {} );
} );

afterEach( () => {
	vi.useRealTimers();
	document.body.innerHTML = '';
} );

describe( 'loadStats', () => {
	it( 'shows the queued, loading and loaded states of the first load', async () => {
		const widget = addWidget( 'hello' );
		const { status, button, content } = elements( widget );
		const request = deferred();
		vi.mocked( apiFetch ).mockReturnValue( request.promise );

		const loaded = loadStats( 'hello', false, createQueue() );

		// Queued.
		expect( status.text() ).toBe( 'Waiting…' );
		expect( button.prop( 'disabled' ) ).toBe( true );
		expect( button.hasClass( 'waiting' ) ).toBe( true );

		// Loading.
		await vi.waitFor( () => expect( apiFetch ).toHaveBeenCalledWith( { path: '/translation-stats/v1/plugins/hello/stats' } ) );
		expect( status.text() ).toBe( 'Updating…' );
		expect( button.hasClass( 'waiting' ) ).toBe( false );
		expect( button.hasClass( 'updating' ) ).toBe( true );
		expect( widget.hasClass( 'tstats-loading' ) ).toBe( true );

		// Loaded.
		request.resolve( { html: '<div class="translation-stats-content-stats">95%</div>' } );
		await loaded;

		expect( content.text() ).toBe( '95%' );
		expect( status.text() ).toBe( '' );
		expect( button.prop( 'disabled' ) ).toBe( false );
		expect( button.hasClass( 'updating' ) ).toBe( false );
		expect( button[ 0 ].style.display ).not.toBe( 'none' );
		expect( widget.hasClass( 'tstats-loading' ) ).toBe( false );
	} );

	it( 'shows the Updated status for a few seconds after forcing the update', async () => {
		vi.useFakeTimers();
		const widget = addWidget( 'hello', { cached: true } );
		const { status, content } = elements( widget );
		vi.mocked( apiFetch ).mockResolvedValue( { html: 'New stats' } );

		loadStats( 'hello', true, createQueue() );
		await vi.advanceTimersByTimeAsync( 100 );

		expect( apiFetch ).toHaveBeenCalledWith( { path: '/translation-stats/v1/plugins/hello/stats?force=true' } );
		expect( content.text() ).toBe( 'New stats' );
		expect( status.text() ).toBe( 'Updated!' );

		await vi.advanceTimersByTimeAsync( 3000 );

		expect( status.text() ).toBe( '' );
	} );

	it( 'shows the error notice when the request fails', async () => {
		const widget = addWidget( 'hello' );
		const { status, button, notice, content } = elements( widget );
		vi.mocked( apiFetch ).mockRejectedValue( { code: 'translation_stats_api_unavailable', message: 'Unavailable.' } );

		await loadStats( 'hello', false, createQueue() );

		expect( notice.hasClass( 'hidden' ) ).toBe( false );
		expect( notice.hasClass( 'notice-error' ) ).toBe( true );
		expect( notice[ 0 ].style.display ).not.toBe( 'none' );
		expect( notice.text() ).toBe( 'An error occurred while updating.' );
		expect( status.text() ).toBe( '' );
		expect( button.prop( 'disabled' ) ).toBe( false );
		expect( content.text() ).toBe( 'Placeholder' );
	} );

	it( 'runs the requests one after another', async () => {
		addWidget( 'first' );
		const second = elements( addWidget( 'second' ) );
		const firstRequest = deferred();
		vi.mocked( apiFetch ).mockReturnValueOnce( firstRequest.promise ).mockResolvedValueOnce( { html: 'Second stats' } );

		const queue = createQueue();
		loadStats( 'first', false, queue );
		const secondLoaded = loadStats( 'second', false, queue );

		await vi.waitFor( () => expect( apiFetch ).toHaveBeenCalledTimes( 1 ) );
		await new Promise( ( resolve ) => setTimeout( resolve, 50 ) );

		// The second request waits for the first.
		expect( apiFetch ).toHaveBeenCalledTimes( 1 );
		expect( second.status.text() ).toBe( 'Waiting…' );
		expect( second.button.hasClass( 'waiting' ) ).toBe( true );

		firstRequest.resolve( { html: 'First stats' } );
		await secondLoaded;

		expect( apiFetch ).toHaveBeenCalledTimes( 2 );
		expect( apiFetch ).toHaveBeenLastCalledWith( { path: '/translation-stats/v1/plugins/second/stats' } );
		expect( second.content.text() ).toBe( 'Second stats' );
	} );
} );

describe( 'initStatsWidgets', () => {
	it( 'loads the stats without cache, and shows the update buttons', async () => {
		const cached = elements( addWidget( 'cached', { cached: true } ) );
		const notCached = elements( addWidget( 'not-cached' ) );
		vi.mocked( apiFetch ).mockResolvedValue( { html: 'Loaded stats' } );

		initStatsWidgets( createQueue() );

		// The cached stats aren't loaded, and their update button is enabled.
		expect( cached.button[ 0 ].style.display ).not.toBe( 'none' );
		expect( cached.button.prop( 'disabled' ) ).toBe( false );

		// The update button of the stats being loaded is shown, disabled while loading.
		expect( notCached.button[ 0 ].style.display ).not.toBe( 'none' );
		expect( notCached.button.prop( 'disabled' ) ).toBe( true );

		await vi.waitFor( () => expect( notCached.content.text() ).toBe( 'Loaded stats' ) );

		expect( apiFetch ).toHaveBeenCalledTimes( 1 );
		expect( apiFetch ).toHaveBeenCalledWith( { path: '/translation-stats/v1/plugins/not-cached/stats' } );
		expect( cached.content.text() ).toBe( 'Cached stats' );
		expect( notCached.button.prop( 'disabled' ) ).toBe( false );
	} );

	it( 'forces the update of the stats on click', async () => {
		const cached = elements( addWidget( 'cached', { cached: true } ) );
		vi.mocked( apiFetch ).mockResolvedValue( { html: 'Updated stats' } );

		initStatsWidgets( createQueue() );
		cached.button.trigger( 'click' );

		await vi.waitFor( () => expect( cached.content.text() ).toBe( 'Updated stats' ) );

		expect( apiFetch ).toHaveBeenCalledWith( { path: '/translation-stats/v1/plugins/cached/stats?force=true' } );
	} );
} );
