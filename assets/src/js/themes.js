/**
 * External dependencies
 */
import jQuery from 'jquery';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { createQueue, loadWidgetStats } from './stats-widget';

/** @typedef {import('./stats-widget').Queue} Queue */

/**
 * Translation stats of a theme, from the themes data of the Themes screen.
 *
 * @typedef {Object} ThemeStats
 * @property {string}      title    Widget title HTML, with the update button.
 * @property {string}      stats    Stats HTML, or an empty stats placeholder if not cached.
 * @property {string}      notice   Status notice HTML.
 * @property {string}      language Translation language native name.
 * @property {boolean}     cached   Whether the stats are cached.
 * @property {number|null} percent  Percent translated, or null if not cached.
 * @property {boolean}     loading  Whether the stats are loading.
 */

/**
 * Get the REST API route path of the theme stats.
 *
 * @since 1.5.0
 *
 * @param {string} slug Theme slug.
 *
 * @return {string} REST API route path.
 */
export function themeStatsPath( slug ) {
	return (
		'/translation-stats/v1/themes/' + encodeURIComponent( slug ) + '/stats'
	);
}

/**
 * Render the theme card stats bar, with the markup and styles of the plugins stats bars:
 * filled to the percent translated, or an empty placeholder while loading.
 *
 * @since 1.5.0
 *
 * @param {ThemeStats} stats Theme translation stats.
 *
 * @return {Object} Theme card stats jQuery object.
 */
export function renderCardBar( stats ) {
	const loading = null === stats.percent || undefined === stats.percent;

	const card = jQuery(
		'<div class="tstats-theme-card"><div class="translation-stats-content"><div class="translation-stats-content-stats">' +
			'<div class="content__subproject project"><a><div class="subproject project"><div class="subproject-bar">' +
			'<span class="subproject-bar__name"></span>' +
			'</div></div></a></div>' +
			'</div></div></div>'
	);

	const label = loading
		? sprintf(
				/* translators: %s: Language native name. */
				__( 'Translated to %s', 'translation-stats' ),
				stats.language
			)
		: sprintf(
				/* translators: 1: Language native name. 2: Percent translated. */
				__( 'Translated to %1$s: %2$d%%', 'translation-stats' ),
				stats.language,
				stats.percent
			);

	card.find( '.subproject-bar__name' ).text( label );

	if ( loading ) {
		card.find( 'a' ).addClass( 'placeholder' );
		return card;
	}

	card.find( 'a' ).addClass( 'enabled' );
	card.find( 'div.subproject' )
		.addClass( 'percent' + 10 * Math.floor( stats.percent / 10 ) )
		.css( 'width', stats.percent + '%' );

	return card;
}

/**
 * Render the theme widget, for the theme details.
 *
 * @since 1.5.0
 *
 * @param {string}     slug  Theme slug.
 * @param {ThemeStats} stats Theme translation stats.
 *
 * @return {Object} Widget jQuery object.
 */
export function renderWidget( slug, stats ) {
	const widget = jQuery(
		'<div class="tstats-theme-widget">' +
			'<div class="translation-stats-title"><p>' +
			stats.title +
			'</p></div>' +
			'<div class="translation-stats-content"><div class="content">' +
			stats.stats +
			'</div>' +
			stats.notice +
			'</div>' +
			'</div>'
	);

	return widget.attr( 'data-slug', slug );
}

/**
 * Load the theme stats, and update its card badge and its widget if open.
 *
 * @since 1.5.0
 *
 * @param {Object}  model       Theme model.
 * @param {Object}  widget      Widget or theme card jQuery object, to show the loading state.
 * @param {boolean} forceUpdate True to get new stats, false to use the cached stats.
 * @param {Queue}   queue       Queue to add the request.
 *
 * @return {Promise} Promise resolved when the request is completed.
 */
export function loadThemeStats( model, widget, forceUpdate, queue ) {
	const slug = model.get( 'id' );
	const stats = model.get( 'translationStats' );

	stats.loading = true;

	// Show the loading state also in the theme card, with the moving stripes while updating.
	const targets = widget.add(
		jQuery( '.theme[data-slug="' + slug + '"] .tstats-theme-card' )
	);

	return loadWidgetStats(
		targets,
		themeStatsPath( slug ),
		forceUpdate,
		queue
	).then( function ( response ) {
		stats.loading = false;

		if ( ! response ) {
			return;
		}

		// Keep the stats in the model, for the next renders of the card and details.
		stats.cached = true;
		stats.stats = response.html;
		stats.percent = response.percent;

		jQuery(
			'.theme[data-slug="' + slug + '"] .tstats-theme-card'
		).replaceWith( renderCardBar( stats ) );
		jQuery(
			'.tstats-theme-widget[data-slug="' +
				slug +
				'"] div.translation-stats-content div.content'
		).html( response.html );
	} );
}

/**
 * Add the stats bar to a theme card, at the bottom of the screenshot, above the theme name, without changing the card height.
 * Loads the theme stats if not cached.
 *
 * @since 1.5.0
 *
 * @param {Object} view  Theme card view.
 * @param {Queue}  queue Queue to add the request.
 */
export function addThemeCardBar( view, queue ) {
	const stats = view.model.get( 'translationStats' );

	if ( ! stats ) {
		return;
	}

	const card = renderCardBar( stats );

	view.$el.find( '.theme-screenshot' ).append( card );

	// Load with the card, as the theme card isn't in the page yet to find it.
	if ( ! stats.cached && ! stats.loading ) {
		loadThemeStats( view.model, card, false, queue );
	}
}

/**
 * Add the widget to the theme details, above the theme description, with the update button.
 *
 * @since 1.5.0
 *
 * @param {Object} view  Theme details view.
 * @param {Queue}  queue Queue to add the request.
 */
export function addThemeWidget( view, queue ) {
	const stats = view.model.get( 'translationStats' );

	if ( ! stats ) {
		return;
	}

	const widget = renderWidget( view.model.get( 'id' ), stats );

	const description = view.$el.find( '.theme-info .theme-description' );

	if ( description.length ) {
		description.first().before( widget );
	} else {
		view.$el.find( '.theme-info' ).append( widget );
	}

	// Show the update button with its display, as the theme details aren't in the page yet to show it with jQuery.
	widget
		.find( 'button.tstats-update-button' )
		.css( 'display', 'inline-block' )
		.on( 'click', function () {
			loadThemeStats( view.model, widget, true, queue );
		} );

	if ( ! stats.cached && ! stats.loading ) {
		loadThemeStats( view.model, widget, false, queue );
	}
}

/**
 * Add the link to the Translation Stats themes settings in the Themes screen header, after the Add New Theme button.
 *
 * @since 1.5.0
 *
 * @param {string} settingsUrl Themes settings URL, or empty if the user can't manage the settings.
 *
 * @return {boolean} True if the link was added.
 */
export function addSettingsLink( settingsUrl ) {
	const actions = jQuery( '.wrap > .page-title-action' );

	if ( ! settingsUrl || ! actions.length ) {
		return false;
	}

	const link = jQuery(
		'<a class="page-title-action tstats-themes-settings-link"><span class="dashicons dashicons-edit" aria-hidden="true"></span> </a>'
	);

	link.attr( 'href', settingsUrl ).append(
		document.createTextNode(
			__( 'Translation Stats', 'translation-stats' )
		)
	);

	actions.last().after( link );

	return true;
}

/**
 * Add the translation stats to the Themes screen: a stats bar on the theme cards, and a widget in the theme details.
 * Extends the render of the WordPress themes views, if available.
 *
 * @since 1.5.0
 *
 * @param {Object} themes WordPress themes, with the views. Defaults to wp.themes.
 * @param {Queue}  queue  Queue to add the requests. Defaults to a new queue.
 *
 * @return {boolean} True if the themes views were extended.
 */
export function initThemesStats(
	themes = window.wp && window.wp.themes,
	queue = createQueue()
) {
	if (
		! themes ||
		! themes.view ||
		! themes.view.Theme ||
		! themes.view.Details
	) {
		return false;
	}

	const renderTheme = themes.view.Theme.prototype.render;
	const renderDetails = themes.view.Details.prototype.render;

	themes.view.Theme.prototype.render = function () {
		const result = renderTheme.apply( this, arguments );
		addThemeCardBar( this, queue );
		return result;
	};

	themes.view.Details.prototype.render = function () {
		const result = renderDetails.apply( this, arguments );
		addThemeWidget( this, queue );
		return result;
	};

	return true;
}
