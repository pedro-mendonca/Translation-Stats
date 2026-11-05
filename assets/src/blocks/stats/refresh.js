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
 * @since 2.1.1 Added the isCancelled option.
 *
 * @param {HTMLElement}                        widget              Block widget, with the cached stats REST API URL.
 * @param {Object}                             options             Options.
 * @param {number}                             options.interval    Time between the checks, in milliseconds. Defaults to 3 seconds.
 * @param {number}                             options.attempts    Maximum number of checks. Defaults to 20.
 * @param {(url: string) => Promise<Response>} options.fetchStats  Fetch the cached stats, as window.fetch.
 * @param {() => boolean}                      options.isCancelled Stop checking, as when the block is removed. Defaults to never.
 *
 * @return {Promise<boolean>} Promise resolved with true if the stats were shown, false if not cached after all the checks, or cancelled.
 */
export function refreshBlock( widget, options = {} ) {
	const interval = undefined === options.interval ? 3000 : options.interval;
	const attempts = undefined === options.attempts ? 20 : options.attempts;
	const fetchStats =
		options.fetchStats || ( ( path ) => window.fetch( path ) );
	const isCancelled = options.isCancelled || ( () => false );
	const url = widget.dataset.tstatsRefresh;

	const check = ( attempt ) =>
		wait( interval ).then( () => {
			if ( isCancelled() ) {
				return false;
			}

			return fetchStats( url )
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

/**
 * Show the stats of the blocks loading in the background in a container, as soon as they are cached,
 * also for the blocks added later, as the block editor previews when rendered.
 * Each block stops checking when removed, or when stopped.
 *
 * @since 2.1.1
 *
 * @param {HTMLElement|undefined} container Container of the blocks.
 * @param {Object}                options   Options of refreshBlock(), without isCancelled.
 *
 * @return {() => void} Stop watching and checking the blocks.
 */
export function watchBlocks( container, options = {} ) {
	if ( ! container || ! window.MutationObserver ) {
		return () => {};
	}

	let stopped = false;
	const watched = new WeakSet();

	const refreshNew = () =>
		container
			.querySelectorAll(
				'.wp-block-translation-stats-stats [data-tstats-refresh]'
			)
			.forEach( ( widget ) => {
				if ( watched.has( widget ) ) {
					return;
				}
				watched.add( widget );

				const widgetOptions = Object.assign( {}, options );
				widgetOptions.isCancelled = () =>
					stopped || ! widget.isConnected;
				refreshBlock( widget, widgetOptions );
			} );

	const observer = new window.MutationObserver( refreshNew );
	observer.observe( container, { childList: true, subtree: true } );
	refreshNew();

	return () => {
		stopped = true;
		observer.disconnect();
	};
}
