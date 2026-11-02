/**
 * External dependencies
 */
import { describe, expect, it } from 'vitest';

/**
 * Internal dependencies
 */
import {
	pluginSubprojects,
	projectOptions,
	toggleSubproject,
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

describe( 'projectOptions', () => {
	const plugins = [ { value: 'hello', label: 'Hello Dolly' } ];

	it( 'lists the installed projects after the empty option', () => {
		expect( projectOptions( plugins, 'hello' ) ).toEqual( [
			{ value: '', label: 'Select…' },
			{ value: 'hello', label: 'Hello Dolly' },
		] );
	} );

	it( 'adds the slug of a project not installed', () => {
		expect( projectOptions( plugins, 'akismet' ).pop() ).toEqual( {
			value: 'akismet',
			label: 'akismet',
		} );
	} );
} );
