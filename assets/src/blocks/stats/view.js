/**
 * Internal dependencies
 */
import { refreshBlocks } from './refresh';

// Show the stats loading in the background, after the page is loaded.
if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', () => refreshBlocks() );
} else {
	refreshBlocks();
}
