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
		coverage: {
			provider: 'v8',
			include: [ 'assets/src/js/**/*.js' ],
			reporter: [ 'text', 'lcov' ],
		},
	},
} );
