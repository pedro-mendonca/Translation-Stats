/**
 * External dependencies
 */
import jQuery from 'jquery';
import 'tablesorter/dist/js/jquery.tablesorter.combined';

/**
 * Internal dependencies
 */
import { initSettings } from './settings';

jQuery( document ).ready( function () {
	initSettings();
} );
