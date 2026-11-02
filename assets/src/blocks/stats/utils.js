/**
 * WordPress dependencies
 */
import { _x } from '@wordpress/i18n';

/**
 * Get the plugin subprojects on translate.wordpress.org.
 *
 * @since 2.1.0
 *
 * @return {Array<{slug: string, name: string}>} Subprojects.
 */
export function pluginSubprojects() {
	return [
		{
			slug: 'dev',
			name: _x( 'Development', 'Subproject name', 'translation-stats' ),
		},
		{
			slug: 'dev-readme',
			name: _x(
				'Development Readme',
				'Subproject name',
				'translation-stats'
			),
		},
		{
			slug: 'stable',
			name: _x( 'Stable', 'Subproject name', 'translation-stats' ),
		},
		{
			slug: 'stable-readme',
			name: _x( 'Stable Readme', 'Subproject name', 'translation-stats' ),
		},
	];
}

/**
 * Add or remove a subproject from the selected subprojects, keeping the translate.wordpress.org order.
 *
 * @since 2.1.0
 *
 * @param {string[]} selected Selected subprojects slugs.
 * @param {string}   slug     Subproject slug.
 * @param {boolean}  checked  True to add the subproject, false to remove it.
 *
 * @return {string[]} Selected subprojects slugs.
 */
export function toggleSubproject( selected, slug, checked ) {
	return pluginSubprojects()
		.map( ( subproject ) => subproject.slug )
		.filter( ( subprojectSlug ) =>
			subprojectSlug === slug
				? checked
				: selected.includes( subprojectSlug )
		);
}

/**
 * Get the options of the projects search: the current project, the installed projects, and the projects found on WordPress.org.
 *
 * @since 2.1.0
 *
 * @param {Array<{value: string, label: string}>} installed Installed plugins or themes.
 * @param {Array<{value: string, label: string}>} found     Projects found on WordPress.org.
 * @param {string}                                slug      Current project slug.
 * @param {string}                                name      Current project name.
 *
 * @return {Array<{value: string, label: string}>} Search options, without repeated slugs.
 */
export function searchOptions( installed, found, slug, name ) {
	const options = [ ...installed, ...found ];

	if ( slug && ! options.some( ( option ) => option.value === slug ) ) {
		options.unshift( { value: slug, label: name || slug } );
	}

	return options.filter(
		( option, index ) =>
			options.findIndex( ( other ) => other.value === option.value ) ===
			index
	);
}

/**
 * Get the name to save of the selected project: the WordPress.org name of a project not installed,
 * as the block shows the installed projects name.
 *
 * @since 2.1.0
 *
 * @param {Array<{value: string, label: string}>} installed Installed plugins or themes.
 * @param {Array<{value: string, label: string}>} found     Projects found on WordPress.org.
 * @param {string}                                slug      Selected project slug.
 *
 * @return {string} Project name, or empty for the installed projects.
 */
export function projectName( installed, found, slug ) {
	if ( installed.some( ( project ) => project.value === slug ) ) {
		return '';
	}

	const project = found.find( ( option ) => option.value === slug );

	return project ? project.label : '';
}

/**
 * Get the REST API path of the WordPress.org projects search.
 *
 * @since 2.1.0
 *
 * @param {string} type   Project type, 'plugins' or 'themes'.
 * @param {string} search Search terms.
 *
 * @return {string} REST API path.
 */
export function searchPath( type, search ) {
	return (
		'/translation-stats/v1/block-projects?type=' +
		encodeURIComponent( type ) +
		'&search=' +
		encodeURIComponent( search )
	);
}

/**
 * Delay a function until it stops being called for some time, as when typing.
 *
 * @since 2.1.0
 *
 * @param {(value: string) => void} callback Function to call.
 * @param {number}                  wait     Time to wait, in milliseconds.
 *
 * @return {(value: string) => void} Delayed function.
 */
export function debounce( callback, wait ) {
	let timeout;

	return ( ...args ) => {
		clearTimeout( timeout );
		timeout = setTimeout( () => callback( ...args ), wait );
	};
}
