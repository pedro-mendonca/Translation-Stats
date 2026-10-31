/**
 * External dependencies
 */
import { defineConfig } from 'vitest/config';

export default defineConfig( {
	test: {
		environment: 'jsdom',
		globals: false,
		include: [ 'tests/js/**/*.test.js' ],
		restoreMocks: true,
	},
} );
