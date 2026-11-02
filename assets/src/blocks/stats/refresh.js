/**
 * Wait for some time.
 *
 * @since 2.1.0
 *
 * @param {number} ms Time to wait, in milliseconds.
 *
 * @return {Promise} Promise resolved after the time.
 */
function wait( ms ) {
	return new Promise( ( resolve ) => setTimeout( resolve, ms ) );
}

/**
 * Show the stats of a block loading in the background, as soon as they are cached.
 * Checks the cached stats, without loading them from translate.wordpress.org.
 *
 * @since 2.1.0
 *
 * @param {HTMLElement}                        widget             Block widget, with the cached stats REST API URL.
 * @param {Object}                             options            Options.
 * @param {number}                             options.interval   Time between the checks, in milliseconds.
 * @param {number}                             options.attempts   Maximum number of checks.
 * @param {(url: string) => Promise<Response>} options.fetchStats Fetch the cached stats, as window.fetch.
 *
 * @return {Promise<boolean>} Promise resolved with true if the stats were shown, false if not cached after all the checks.
 */
export function refreshBlock(
	widget,
	{
		interval = 3000,
		attempts = 20,
		fetchStats = ( url ) => window.fetch( url ),
	} = {}
) {
	const url = widget.dataset.tstatsRefresh;

	const check = ( attempt ) =>
		wait( interval )
			.then( () => fetchStats( url ) )
			.then( ( response ) => ( response.ok ? response.json() : {} ) )
			.catch( () => ( {} ) )
			.then( ( data ) => {
				// Moving stripes only while loading, as the queued stats have the static placeholder.
				widget.classList.toggle(
					'tstats-loading',
					!! data && 'loading' === data.status
				);

				if ( data && data.html ) {
					widget.querySelector(
						'.translation-stats-content'
					).innerHTML = data.html;
					widget.classList.remove( 'tstats-loading' );
					delete widget.dataset.tstatsRefresh;
					return true;
				}

				return attempt < attempts ? check( attempt + 1 ) : false;
			} );

	return check( 1 );
}

/**
 * Show the stats of all the blocks loading in the background, as soon as they are cached.
 *
 * @since 2.1.0
 *
 * @param {Document} doc     Document with the blocks.
 * @param {Object}   options Options of refreshBlock().
 *
 * @return {Promise<boolean[]>} Promise resolved when all the blocks were refreshed or gave up.
 */
export function refreshBlocks( doc = document, options = {} ) {
	return Promise.all(
		Array.from(
			doc.querySelectorAll(
				'.wp-block-translation-stats-stats [data-tstats-refresh]'
			)
		).map( ( widget ) => refreshBlock( widget, options ) )
	);
}
