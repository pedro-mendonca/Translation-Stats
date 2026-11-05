/**
 * External dependencies
 */
import { afterEach, describe, expect, it, vi } from 'vitest';

/**
 * Internal dependencies
 */
import {
	refreshBlock,
	refreshBlocks,
	watchBlocks,
} from '../../assets/src/blocks/stats/refresh';

/**
 * Add a block with its stats queued to load in the background.
 *
 * @param {string} slug Project slug.
 *
 * @return {HTMLElement} The block widget.
 */
function addBlock( slug ) {
	document.body.insertAdjacentHTML(
		'beforeend',
		`<div class="wp-block-translation-stats-stats"><div class="translation-stats-widget" data-slug="${ slug }" data-tstats-refresh="/wp-json/translation-stats/v1/block-stats?slug=${ slug }">
			<div class="translation-stats-content"><div class="translation-stats-placeholder">Placeholder</div></div>
		</div></div>`
	);

	return document.querySelector( `[data-slug="${ slug }"]` );
}

/**
 * Get a fetch response.
 *
 * @param {Object}  data Response data.
 * @param {boolean} ok   Whether the response is successful.
 *
 * @return {Promise<Object>} Response.
 */
function response( data, ok = true ) {
	return Promise.resolve( { ok, json: () => Promise.resolve( data ) } );
}

afterEach( () => {
	document.body.innerHTML = '';
} );

describe( 'refreshBlock', () => {
	it( 'shows the stats as soon as they are cached', async () => {
		const widget = addBlock( 'hello' );
		const fetchStats = vi
			.fn()
			.mockReturnValueOnce( response( { html: null, status: 'queued' } ) )
			.mockReturnValueOnce(
				response( { html: null, status: 'loading' } )
			)
			.mockReturnValueOnce( response( {}, false ) )
			// Rejected when fetched, as a network error.
			.mockImplementationOnce( () =>
				Promise.reject( new Error( 'Offline' ) )
			)
			.mockReturnValueOnce(
				response( {
					html: '<div class="translation-stats-content-stats">95%</div>',
				} )
			);

		const shown = await refreshBlock( widget, {
			interval: 0,
			attempts: 5,
			fetchStats,
		} );

		expect( shown ).toBe( true );
		expect( fetchStats ).toHaveBeenCalledTimes( 5 );
		expect( fetchStats ).toHaveBeenCalledWith(
			'/wp-json/translation-stats/v1/block-stats?slug=hello'
		);
		expect(
			widget.querySelector( '.translation-stats-content' ).textContent
		).toBe( '95%' );
		expect( widget.classList.contains( 'tstats-loading' ) ).toBe( false );
		expect( widget.dataset.tstatsRefresh ).toBeUndefined();
	} );

	it( 'shows the moving stripes only while loading, not while queued', async () => {
		const widget = addBlock( 'hello' );
		const statuses = [ 'loading', 'queued', 'loading' ];
		const classes = [];
		const fetchStats = vi.fn( () => {
			classes.push( widget.classList.contains( 'tstats-loading' ) );
			return response( { html: null, status: statuses.shift() } );
		} );

		await refreshBlock( widget, {
			interval: 0,
			attempts: 4,
			fetchStats,
		} );

		// The class before each check: queued at first, then as the previous status.
		expect( classes ).toEqual( [ false, true, false, true ] );
		expect( widget.classList.contains( 'tstats-loading' ) ).toBe( false );
	} );

	it( 'gives up after the maximum checks, still queued', async () => {
		const widget = addBlock( 'hello' );
		const fetchStats = vi.fn( () => response( { html: null } ) );

		const shown = await refreshBlock( widget, {
			interval: 0,
			attempts: 3,
			fetchStats,
		} );

		expect( shown ).toBe( false );
		expect( fetchStats ).toHaveBeenCalledTimes( 3 );
		expect( widget.classList.contains( 'tstats-loading' ) ).toBe( false );
		expect( widget.textContent.trim() ).toBe( 'Placeholder' );
	} );

	it( 'stops checking when cancelled', async () => {
		const widget = addBlock( 'hello' );
		let cancelled = false;
		const fetchStats = vi.fn( () => {
			cancelled = true;
			return response( { html: null, status: 'queued' } );
		} );

		const shown = await refreshBlock( widget, {
			interval: 0,
			attempts: 5,
			fetchStats,
			isCancelled: () => cancelled,
		} );

		expect( shown ).toBe( false );
		expect( fetchStats ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'uses window.fetch by default', async () => {
		const widget = addBlock( 'hello' );
		const fetch = vi
			.spyOn( window, 'fetch' )
			.mockImplementation( () => response( { html: 'Stats' } ) );

		await refreshBlock( widget, { interval: 0 } );

		expect( fetch ).toHaveBeenCalledWith(
			'/wp-json/translation-stats/v1/block-stats?slug=hello'
		);
	} );
} );

describe( 'refreshBlocks', () => {
	it( 'refreshes only the blocks loading in the background', async () => {
		addBlock( 'loading' );
		document.body.insertAdjacentHTML(
			'beforeend',
			'<div class="wp-block-translation-stats-stats"><div class="translation-stats-widget" data-slug="cached"></div></div>'
		);
		const fetchStats = vi.fn( () => response( { html: 'Stats' } ) );

		expect(
			await refreshBlocks( document, { interval: 0, fetchStats } )
		).toEqual( [ true ] );
		expect( fetchStats ).toHaveBeenCalledTimes( 1 );
	} );
} );

describe( 'watchBlocks', () => {
	/**
	 * Wait for the pending checks, with the interval of 0.
	 *
	 * @return {Promise} Promise resolved after the pending checks.
	 */
	const flush = () => new Promise( ( resolve ) => setTimeout( resolve, 10 ) );

	it( 'refreshes the blocks loading in the background, also when added later', async () => {
		addBlock( 'first' );
		const fetchStats = vi.fn( () => response( { html: 'Stats' } ) );

		const stop = watchBlocks( document.body, { interval: 0, fetchStats } );
		await flush();

		expect( fetchStats ).toHaveBeenCalledTimes( 1 );

		// A block preview rendered later, as in the block editor.
		const later = addBlock( 'later' );
		await flush();

		expect( fetchStats ).toHaveBeenCalledTimes( 2 );
		expect( later.textContent.trim() ).toBe( 'Stats' );

		stop();
	} );

	it( 'checks each block once, while not removed or stopped', async () => {
		const widget = addBlock( 'hello' );
		const fetchStats = vi.fn( () => response( { html: null } ) );

		const stop = watchBlocks( document.body, {
			interval: 0,
			attempts: 1000,
			fetchStats,
		} );
		// Changes in the container don't check the same block again.
		document.body.insertAdjacentHTML( 'beforeend', '<p>Other</p>' );
		await flush();

		const checks = fetchStats.mock.calls.length;
		expect( checks ).toBeGreaterThan( 0 );

		// Removed, as a block preview rendered again.
		widget.closest( '.wp-block-translation-stats-stats' ).remove();
		await flush();
		const afterRemoved = fetchStats.mock.calls.length;
		await flush();

		expect( fetchStats.mock.calls.length ).toBe( afterRemoved );

		// Stopped, as a block removed from the editor.
		addBlock( 'other' );
		await flush();
		stop();
		await flush();
		const afterStopped = fetchStats.mock.calls.length;
		await flush();

		expect( fetchStats.mock.calls.length ).toBe( afterStopped );
	} );

	it( 'does nothing without a container', () => {
		expect( watchBlocks( undefined ) ).toBeInstanceOf( Function );
		expect( watchBlocks( undefined )() ).toBeUndefined();
	} );
} );
