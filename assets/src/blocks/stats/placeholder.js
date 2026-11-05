/**
 * WordPress dependencies
 */
import { createInterpolateElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import data from './editor-data';
import {
	blockLanguage,
	blockProjectName,
	localeProjectUrl,
	placeholderSubprojects,
} from './utils';

/**
 * Get a stats bar of the block placeholder, as the server render placeholder.
 *
 * @since 2.1.1
 *
 * @param {{slug: string, name: string}} subproject Subproject, or the theme project.
 *
 * @return {Element} Stats bar placeholder element.
 */
function placeholderBar( subproject ) {
	// An anchor without a link, as the server render placeholder, for the stats widget styles.
	/* eslint-disable jsx-a11y/anchor-is-valid */
	const bar = (
		<a className="placeholder">
			<div className="subproject">
				<div className="subproject-bar">
					<span className="subproject-bar__name">
						{ subproject.name }
					</span>
				</div>
			</div>
		</a>
	);
	/* eslint-enable jsx-a11y/anchor-is-valid */

	return (
		<div
			key={ subproject.slug }
			className={
				'content__subproject ' + ( subproject.slug || 'project' )
			}
		>
			{ bar }
		</div>
	);
}

/**
 * Show the stats placeholder of the block while its preview loads, as the server render of the stats loading,
 * instead of a spinner. Shows the previous preview while it loads again, as when the attributes change.
 *
 * @since 2.1.1
 *
 * @param {Object}  props            ServerSideRender props.
 * @param {Object}  props.attributes Block attributes.
 * @param {Element} props.children   Previous preview, if any.
 *
 * @return {Element} Block placeholder element.
 */
export default function BlockPlaceholder( props ) {
	if ( props.children ) {
		return props.children;
	}

	const attributes = props.attributes;
	const name = blockProjectName(
		attributes,
		'themes' === attributes.type ? data.themes : data.plugins
	);
	const language = blockLanguage(
		attributes.language,
		data.languages,
		data.siteLanguage
	);

	return (
		<div className="wp-block-translation-stats-stats">
			<div className="translation-stats-widget">
				{ attributes.showName && (
					<p className="translation-stats-name">
						<strong>{ name }</strong>
					</p>
				) }
				{ attributes.showTitle && language && (
					<div className="translation-stats-title">
						<p>
							{ createInterpolateElement(
								sprintf(
									/* translators: %s: Language native name. */
									__(
										'Translation for %s',
										'translation-stats'
									),
									'<a>' + language.name + '</a>'
								),
								{
									// The language name is the link content, from the translated string.
									a: (
										// eslint-disable-next-line jsx-a11y/anchor-has-content
										<a
											href={ localeProjectUrl(
												attributes,
												language.slug
											) }
											target="_blank"
											rel="noreferrer"
										/>
									),
								}
							) }
						</p>
					</div>
				) }
				<div className="translation-stats-content">
					<div
						className="translation-stats-content-stats widget-inside translation-stats-placeholder"
						aria-hidden="true"
					>
						{ placeholderSubprojects( attributes, name ).map(
							placeholderBar
						) }
					</div>
				</div>
			</div>
		</div>
	);
}
