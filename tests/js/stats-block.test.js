/**
 * External dependencies
 */
import { afterEach, describe, expect, it, vi } from 'vitest';

/**
 * Internal dependencies
 */
import {
	debounce,
	pluginSubprojects,
	projectName,
	searchOptions,
	searchPath,
	spinIcon,
	toggleSubproject,
	updateRequest,
} from '../../assets/src/blocks/stats/utils';

describe( 'pluginSubprojects', () => {
	it( 'lists the plugin subprojects in the translate.wordpress.org order', () => {
		expect(
			pluginSubprojects().map( ( subproject ) => subproject.slug )
		).toEqual( [ 'dev', 'dev-readme', 'stable', 'stable-readme' ] );
	} );
} );

describe( 'toggleSubproject', () => {
	it( 'adds a subproject in the translate.wordpress.org order', () => {
		expect( toggleSubproject( [ 'stable' ], 'dev', true ) ).toEqual( [
			'dev',
			'stable',
		] );
	} );

	it( 'removes a subproject, and unknown subprojects', () => {
		expect(
			toggleSubproject(
				[ 'stable', 'stable-readme', 'unknown' ],
				'stable',
				false
			)
		).toEqual( [ 'stable-readme' ] );
	} );
} );

describe( 'searchOptions', () => {
	const installed = [ { value: 'hello', label: 'Hello Dolly' } ];
	const found = [
		{ value: 'hello', label: 'Hello Dolly from WordPress.org' },
		{ value: 'woocommerce', label: 'WooCommerce' },
	];

	it( 'lists the installed projects and the projects found, without repeated slugs', () => {
		expect( searchOptions( installed, found, 'hello', '' ) ).toEqual( [
			{ value: 'hello', label: 'Hello Dolly' },
			{ value: 'woocommerce', label: 'WooCommerce' },
		] );
	} );

	it( 'adds the current project, with its name or slug', () => {
		expect(
			searchOptions( installed, [], 'akismet', 'Akismet' )[ 0 ]
		).toEqual( { value: 'akismet', label: 'Akismet' } );
		expect( searchOptions( installed, [], 'akismet', '' )[ 0 ] ).toEqual( {
			value: 'akismet',
			label: 'akismet',
		} );
		expect( searchOptions( installed, [], '', '' ) ).toEqual( installed );
	} );
} );

describe( 'projectName', () => {
	const installed = [ { value: 'hello', label: 'Hello Dolly' } ];
	const found = [ { value: 'woocommerce', label: 'WooCommerce' } ];

	it( 'saves the WordPress.org name only for the projects not installed', () => {
		expect( projectName( installed, found, 'woocommerce' ) ).toBe(
			'WooCommerce'
		);
		expect( projectName( installed, found, 'hello' ) ).toBe( '' );
		expect( projectName( installed, found, 'unknown' ) ).toBe( '' );
	} );
} );

describe( 'searchPath', () => {
	it( 'gets the search REST API path, with the encoded search', () => {
		expect( searchPath( 'themes', 'twenty five&x' ) ).toBe(
			'/translation-stats/v1/block-projects?type=themes&search=twenty%20five%26x'
		);
	} );
} );

describe( 'debounce', () => {
	afterEach( () => {
		vi.useRealTimers();
	} );

	it( 'calls the function once, after it stops being called', () => {
		vi.useFakeTimers();
		const callback = vi.fn();
		const delayed = debounce( callback, 500 );

		delayed( 'w' );
		delayed( 'wo' );
		vi.advanceTimersByTime( 400 );
		delayed( 'woo' );
		vi.advanceTimersByTime( 499 );

		expect( callback ).not.toHaveBeenCalled();

		vi.advanceTimersByTime( 1 );

		expect( callback ).toHaveBeenCalledTimes( 1 );
		expect( callback ).toHaveBeenCalledWith( 'woo' );
	} );
} );

describe( 'updateRequest', () => {
	it( 'gets the REST API request to update the block stats', () => {
		expect(
			updateRequest( {
				type: 'plugins',
				slug: 'hello-dolly',
				language: '',
				subprojects: [ 'stable', 'stable-readme' ],
				showName: true,
			} )
		).toEqual( {
			path: '/translation-stats/v1/block-stats',
			method: 'POST',
			data: {
				type: 'plugins',
				slug: 'hello-dolly',
				language: '',
				subprojects: 'stable,stable-readme',
			},
		} );
	} );
} );

describe( 'spinIcon', () => {
	/**
	 * Get a button with an icon that can be animated.
	 *
	 * @return {Object} The button, and the icon animation cancel mock.
	 */
	function button() {
		document.body.innerHTML =
			'<button><svg class="dashicon"></svg></button>';
		const cancel = vi.fn();
		const icon = document.querySelector( 'svg' );
		icon.animate = vi.fn( () => ( { cancel } ) );
		return { element: document.querySelector( 'button' ), icon, cancel };
	}

	afterEach( () => {
		document.body.innerHTML = '';
	} );

	it( 'spins the icon until stopped', () => {
		window.matchMedia = vi.fn( () => ( { matches: false } ) );
		const { element, icon, cancel } = button();

		const stop = spinIcon( element, true );

		expect( icon.animate ).toHaveBeenCalledWith(
			[ { transform: 'rotate(0deg)' }, { transform: 'rotate(360deg)' } ],
			{ duration: 1000, iterations: Infinity }
		);

		stop();

		expect( cancel ).toHaveBeenCalled();
	} );

	it( "doesn't spin when not updating, without the button, or with reduced motion", () => {
		window.matchMedia = vi.fn( () => ( { matches: true } ) );
		const { element, icon } = button();

		expect( spinIcon( element, false ) ).toBeUndefined();
		expect( spinIcon( undefined, true ) ).toBeUndefined();
		expect( spinIcon( element, true ) ).toBeUndefined();
		expect( icon.animate ).not.toHaveBeenCalled();
	} );
} );
