/**
 * WordPress dependencies
 */
import { __, _x } from '@wordpress/i18n';

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
 * Get the options of the projects select: the installed plugins or themes, and the current slug if not installed.
 *
 * @since 2.1.0
 *
 * @param {Array<{value: string, label: string}>} projects Installed plugins or themes.
 * @param {string}                                slug     Current project slug.
 *
 * @return {Array<{value: string, label: string}>} Select options.
 */
export function projectOptions( projects, slug ) {
	const options = [
		{ value: '', label: __( 'Select…', 'translation-stats' ) },
		...projects,
	];

	if ( slug && ! projects.some( ( project ) => project.value === slug ) ) {
		options.push( { value: slug, label: slug } );
	}

	return options;
}
