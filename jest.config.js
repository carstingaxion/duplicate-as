/**
 * Jest configuration for the Duplicate As plugin.
 *
 * Extends @wordpress/scripts default configuration and adds
 * custom test paths, module name mapping, and setup files.
 *
 * @see https://jestjs.io/docs/configuration
 * @package
 * @since   0.4.0
 */

const defaultConfig = require( '@wordpress/scripts/config/jest-unit.config' );

module.exports = {
	...defaultConfig,
	testPathIgnorePatterns: [
		'/node_modules/',
		'/vendor/',
		'/build/',
		'/tests/unit/php/',
		'/tests/integration/php/',
	],
	testMatch: [
		'<rootDir>/tests/unit/js/**/*.test.js',
		'<rootDir>/tests/integration/js/**/*.test.js',
	],
	setupFilesAfterEnv: [ '<rootDir>/tests/setup.js' ],
	transform: {
		...( defaultConfig.transform || {} ),
		'^.+\\.[jt]sx?$': 'babel-jest',
	},
	transformIgnorePatterns: [ '/node_modules/(?!@wordpress/)' ],
	moduleNameMapper: {
		...( defaultConfig.moduleNameMapper || {} ),
		'\\.(css|scss)$': '<rootDir>/tests/__mocks__/styleMock.js',
		'^@testing-library/react$':
			'<rootDir>/tests/__mocks__/@testing-library/react.js',
		'^@wordpress/i18n$': '<rootDir>/tests/__mocks__/@wordpress/i18n.js',
		'^@wordpress/api-fetch$':
			'<rootDir>/tests/__mocks__/@wordpress/api-fetch.js',
		'^@wordpress/data$': '<rootDir>/tests/__mocks__/@wordpress/data.js',
		'^@wordpress/element$':
			'<rootDir>/tests/__mocks__/@wordpress/element.js',
		'^@wordpress/components$':
			'<rootDir>/tests/__mocks__/@wordpress/components.js',
		'^@wordpress/editor$': '<rootDir>/tests/__mocks__/@wordpress/editor.js',
		'^@wordpress/core-data$':
			'<rootDir>/tests/__mocks__/@wordpress/core-data.js',
		'^@wordpress/plugins$':
			'<rootDir>/tests/__mocks__/@wordpress/plugins.js',
	},
};
