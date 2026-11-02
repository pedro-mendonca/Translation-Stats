/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	CheckboxControl,
	PanelBody,
	Placeholder,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies
 */
import { pluginSubprojects, projectOptions, toggleSubproject } from './utils';

/**
 * Editor data: the languages, and the installed plugins and themes.
 *
 * @type {{languages: Array, plugins: Array, themes: Array}}
 */
const data = window.tstatsBlock || { languages: [], plugins: [], themes: [] };

/**
 * Edit the Translation Stats block, with a server side rendered preview.
 *
 * @since 2.1.0
 *
 * @param {Object}                       props               Block props.
 * @param {Object}                       props.attributes    Block attributes.
 * @param {(attributes: Object) => void} props.setAttributes Set the block attributes.
 *
 * @return {Element} Block edit element.
 */
export default function Edit( { attributes, setAttributes } ) {
	const type = attributes.type;
	const slug = attributes.slug;
	const name = attributes.name;
	const language = attributes.language;
	const subprojects = attributes.subprojects;
	const showName = attributes.showName;
	const showTitle = attributes.showTitle;
	const showCounts = attributes.showCounts;

	const projects = 'themes' === type ? data.themes : data.plugins;

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title={ __( 'Project', 'translation-stats' ) }>
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Type', 'translation-stats' ) }
						value={ type }
						options={ [
							{
								value: 'plugins',
								label: __( 'Plugin', 'translation-stats' ),
							},
							{
								value: 'themes',
								label: __( 'Theme', 'translation-stats' ),
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { type: value, slug: '' } )
						}
					/>
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={
							'themes' === type
								? __( 'Installed theme', 'translation-stats' )
								: __( 'Installed plugin', 'translation-stats' )
						}
						value={ slug }
						options={ projectOptions( projects, slug ) }
						onChange={ ( value ) =>
							setAttributes( { slug: value } )
						}
					/>
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Slug', 'translation-stats' ) }
						help={ __(
							'Or any project slug on WordPress.org, as in its URL.',
							'translation-stats'
						) }
						value={ slug }
						onChange={ ( value ) =>
							setAttributes( { slug: value.trim() } )
						}
					/>
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Language', 'translation-stats' ) }
						value={ language }
						options={ [
							{
								value: '',
								label: __(
									'Site language',
									'translation-stats'
								),
							},
							...data.languages,
						] }
						onChange={ ( value ) =>
							setAttributes( { language: value } )
						}
					/>
				</PanelBody>
				{ 'plugins' === type && (
					<PanelBody
						title={ __( 'Subprojects', 'translation-stats' ) }
					>
						{ pluginSubprojects().map( ( subproject ) => (
							<CheckboxControl
								__nextHasNoMarginBottom
								key={ subproject.slug }
								label={ subproject.name }
								checked={ subprojects.includes(
									subproject.slug
								) }
								onChange={ ( checked ) =>
									setAttributes( {
										subprojects: toggleSubproject(
											subprojects,
											subproject.slug,
											checked
										),
									} )
								}
							/>
						) ) }
					</PanelBody>
				) }
				<PanelBody title={ __( 'Display', 'translation-stats' ) }>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Show the project name',
							'translation-stats'
						) }
						checked={ showName }
						onChange={ ( value ) =>
							setAttributes( { showName: value } )
						}
					/>
					{ showName && (
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Name', 'translation-stats' ) }
							help={ __(
								'Leave empty to use the name of the installed plugin or theme, or the slug.',
								'translation-stats'
							) }
							value={ name }
							onChange={ ( value ) =>
								setAttributes( { name: value } )
							}
						/>
					) }
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show the language', 'translation-stats' ) }
						checked={ showTitle }
						onChange={ ( value ) =>
							setAttributes( { showTitle: value } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Show the strings count',
							'translation-stats'
						) }
						checked={ showCounts }
						onChange={ ( value ) =>
							setAttributes( { showCounts: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			{ slug ? (
				<ServerSideRender
					block="translation-stats/stats"
					attributes={ attributes }
				/>
			) : (
				<Placeholder
					icon="translation"
					label={ __( 'Translation Stats', 'translation-stats' ) }
					instructions={ __(
						'Select a plugin or theme in the block settings, to show its translation stats from translate.wordpress.org.',
						'translation-stats'
					) }
				/>
			) }
		</div>
	);
}
