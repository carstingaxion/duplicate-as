/**
 * Jest global setup file.
 *
 * Configures the React 18 act() environment to suppress
 * "The current testing environment is not configured to support act(...)"
 * warnings that would otherwise cause test failures via @wordpress/jest-console.
 *
 * @package
 * @since   0.4.0
 * @see https://reactjs.org/blog/2022/03/08/react-18-upgrade-guide.html#configuring-your-testing-environment
 */

globalThis.IS_REACT_ACT_ENVIRONMENT = true;
