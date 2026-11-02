/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import {
	BlockControls,
	InspectorControls,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	CheckboxControl,
	ComboboxControl,
	Notice,
	PanelBody,
	Placeholder,
	SelectControl,
	TextControl,
	ToggleControl,
	ToolbarButton,
	ToolbarGroup,
} from '@wordpress/components';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';

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
} from './utils';

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

	// Projects found on WordPress.org.
	const [ found, setFound ] = useState( [] );

	// Updating the stats from translate.wordpress.org, the updates count to render the preview again, and the update error.
	const [ updating, setUpdating ] = useState( false );
	const [ updates, setUpdates ] = useState( 0 );
	const [ updateError, setUpdateError ] = useState( '' );

	// Spin the update button icon while updating, as the admin widgets update button.
	const updateButton = useRef();
	useEffect( () => spinIcon( updateButton.current, updating ), [ updating ] );

	// Update the stats, as the update button of the admin widgets.
	const update = () => {
		setUpdating( true );
		setUpdateError( '' );
		apiFetch( updateRequest( attributes ) )
			.then( () => setUpdates( updates + 1 ) )
			.catch( ( error ) =>
				setUpdateError(
					( error && error.message ) ||
						__(
							'An error occurred while updating.',
							'translation-stats'
						)
				)
			)
			.finally( () => setUpdating( false ) );
	};

	// Search WordPress.org when the typing stops, from 2 characters.
	const search = useMemo(
		() =>
			debounce( ( value ) => {
				if ( value.trim().length < 2 ) {
					return;
				}
				apiFetch( { path: searchPath( type, value.trim() ) } )
					.then( setFound )
					.catch( () => setFound( [] ) );
			}, 500 ),
		[ type ]
	);

	return (
		<div { ...useBlockProps() }>
			{ slug && (
				<BlockControls>
					<ToolbarGroup>
						<ToolbarButton
							icon="update"
							label={ __(
								'Update translation stats',
								'translation-stats'
							) }
							ref={ updateButton }
							disabled={ updating }
							onClick={ update }
						/>
					</ToolbarGroup>
				</BlockControls>
			) }
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
						onChange={ ( value ) => {
							setFound( [] );
							setAttributes( {
								type: value,
								slug: '',
								name: '',
							} );
						} }
					/>
					{ /* With the space below of the other fields, as the search field has none. */ }
					<div style={ { marginBottom: '24px' } }>
						<ComboboxControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={
								'themes' === type
									? __( 'Theme', 'translation-stats' )
									: __( 'Plugin', 'translation-stats' )
							}
							help={
								'themes' === type
									? __(
											'Search the installed themes, or the themes on WordPress.org.',
											'translation-stats'
										)
									: __(
											'Search the installed plugins, or the plugins on WordPress.org.',
											'translation-stats'
										)
							}
							value={ slug }
							options={ searchOptions(
								projects,
								found,
								slug,
								name
							) }
							onFilterValueChange={ search }
							onChange={ ( value ) =>
								setAttributes( {
									slug: value || '',
									name: value
										? projectName( projects, found, value )
										: '',
								} )
							}
						/>
					</div>
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
			{ updateError && (
				<Notice status="error" onRemove={ () => setUpdateError( '' ) }>
					{ updateError }
				</Notice>
			) }
			{ slug ? (
				// The moving stripes while updating, and rendered again when updated.
				<div className={ updating ? 'tstats-block-updating' : '' }>
					<ServerSideRender
						key={ updates }
						block="translation-stats/stats"
						attributes={ attributes }
					/>
				</div>
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
