# Developer Documentation

## Table of Contents

- [Getting Started](#getting-started)
- [Architecture](#architecture)
- [Testing](#testing)
  - [PHP Tests](#php-tests)
  - [JavaScript Tests](#javascript-tests)
- [Build & Development](#build--development)
- [Available Scripts](#available-scripts)
- [Filter & Action Hooks](#filter--action-hooks)

---

## Getting Started

### Prerequisites

- **Node.js** >= 18.x and **pnpm** (or npm)
- **PHP** >= 7.4
- **Composer** >= 2.x
- **Docker** (for `wp-env` based testing)

### Installation

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
pnpm install

# Build the editor assets
pnpm run build
```

---

## Architecture

The plugin follows a **Singleton + Single Responsibility** pattern. Each PHP class under `includes/classes/` handles one concern:

| Class                          | Responsibility                              |
| ------------------------------ | ------------------------------------------- |
| `Duplicate_As_Post_Type_Support` | Registers and queries `duplicate_as` support |
| `Duplicate_As_Permissions`       | Centralises all permission checks            |
| `Duplicate_As_Duplicator`        | Core duplication / transformation logic      |
| `Duplicate_As_Rest_Api`          | REST endpoint registration and handling      |
| `Duplicate_As_Admin_Actions`     | Admin action handlers (list table links)     |
| `Duplicate_As_Row_Actions`       | Post list table row action links             |
| `Duplicate_As_Assets`            | Editor script and style enqueueing           |

JavaScript modules under `src/` follow the same principle:

| Module                                    | Responsibility                              |
| ----------------------------------------- | ------------------------------------------- |
| `src/constants.js`                        | Shared constants (REST path, default icon)  |
| `src/utils.js`                            | Pure utility functions (no side effects)    |
| `src/hooks/use-post-data.js`              | Custom hook: selects post data from stores  |
| `src/hooks/use-duplicate.js`              | Custom hook: API call + loading state       |
| `src/components/duplicate-button.js`      | Single duplicate / transform button         |
| `src/components/duplicate-post-status-info.js` | Container component for sidebar panel  |

---

## Testing

### PHP Tests

PHP tests use **PHPUnit** with the **WordPress test framework** (`wp-phpunit`). Tests are split into two suites:

```
tests/
├── bootstrap.php                     # Test bootstrapping
├── unit/php/                         # Unit tests (fast, isolated)
│   ├── PostTypeSupportTest.php
│   ├── PermissionsTest.php
│   ├── DuplicatorTest.php
│   ├── RowActionsTest.php
│   ├── RestApiTest.php
│   ├── AssetsTest.php
│   ├── AdminActionsTest.php
│   └── MainPluginTest.php
└── integration/php/                  # Integration tests (full WordPress)
    ├── DuplicationFlowTest.php
    ├── RestApiFlowTest.php
    ├── FilterHooksTest.php
    └── PostTypeSupportFlowTest.php
```

#### Running PHP Tests with `wp-env`

The recommended way to run PHP tests is via `wp-env`, which provides a consistent WordPress test environment:

```bash
# Start the wp-env environment
pnpm run env:start

# Run all PHP tests (unit + integration)
pnpm run test:php

# Run only unit tests
pnpm run test:php:unit

# Run only integration tests
pnpm run test:php:integration

# Stop the environment
pnpm run env:stop
```

**What happens under the hood:**

```bash
# wp-env executes PHPUnit inside the test container:
npx wp-env run tests-cli \
  --env-cwd='wp-content/plugins/duplicate-post-button' \
  bash -c 'WP_TESTS_DIR=/wordpress-phpunit vendor/bin/phpunit'
```

#### Running PHP Tests without `wp-env`

If you have a local WordPress test suite installed:

```bash
# Set the path to your WordPress test suite
export WP_TESTS_DIR=/path/to/wordpress-phpunit

# Run all tests
composer test

# Run only unit tests
composer test:unit

# Run only integration tests
composer test:integration
```

#### PHPUnit Configuration

The `phpunit.xml.dist` file defines two test suites:

- **`unit`** — Tests in `tests/unit/php/` (class-level isolation)
- **`integration`** — Tests in `tests/integration/php/` (end-to-end flows)

#### Writing PHP Tests

All test classes extend `WP_UnitTestCase` which provides:

- `self::factory()` — Post, user, term, and attachment factories
- Automatic database transaction rollback between tests
- WordPress function availability (`get_post()`, `wp_insert_post()`, etc.)

Example:

```php
class MyTest extends WP_UnitTestCase {
    public function test_something(): void {
        $post_id = self::factory()->post->create( [
            'post_title' => 'Test Post',
        ] );

        $post = get_post( $post_id );
        $this->assertEquals( 'Test Post', $post->post_title );
    }
}
```

---

### JavaScript Tests

JavaScript tests use **Jest** via `@wordpress/scripts`. Tests are also split into unit and integration suites:

```
tests/
├── __mocks__/
│   └── styleMock.js                  # Mock for CSS/SCSS imports
├── unit/js/                          # Unit tests
│   ├── constants.test.js
│   ├── utils.test.js
│   ├── hooks/
│   │   ├── use-duplicate.test.js
│   │   └── use-post-data.test.js
│   └── components/
│       ├── duplicate-button.test.js
│       └── duplicate-post-status-info.test.js
└── integration/js/                   # Integration tests
    └── editor-flow.test.js
```

#### Running JavaScript Tests

```bash
# Run all JS tests
pnpm run test:js

# Run in watch mode (re-runs on file change)
pnpm run test:js:watch

# Run with coverage report
pnpm run test:js:coverage
```

#### Jest Configuration

The `jest.config.js` extends the `@wordpress/scripts` default configuration with:

- Custom test paths (`tests/unit/js/` and `tests/integration/js/`)
- CSS/SCSS module mocking
- PHP test directory exclusion
- Babel transform for JSX support

#### Writing JavaScript Tests

**Unit tests** for pure functions:

```js
import { getTransformTargets } from '../../../src/utils';

describe( 'getTransformTargets', () => {
    it( 'returns empty array for null input', () => {
        expect( getTransformTargets( null ) ).toEqual( [] );
    } );
} );
```

**Hook tests** using `@testing-library/react`:

```js
import { renderHook, act } from '@testing-library/react';
import { useDuplicate } from '../../../../src/hooks/use-duplicate';

describe( 'useDuplicate', () => {
    it( 'returns isLoading as false initially', () => {
        const { result } = renderHook( () => useDuplicate() );
        expect( result.current.isLoading ).toBe( false );
    } );
} );
```

**Component tests** using `@testing-library/react`:

```js
import { render, screen, fireEvent } from '@testing-library/react';
import { DuplicateButton } from '../../../../src/components/duplicate-button';

describe( 'DuplicateButton', () => {
    it( 'renders "Duplicate" label', () => {
        render( <DuplicateButton postId={ 42 } targetPostType={ null } ... /> );
        expect( screen.getByText( 'Duplicate' ) ).toBeInTheDocument();
    } );
} );
```

#### Mocking WordPress Dependencies

WordPress packages are mocked in individual test files using `jest.mock()`:

```js
// Mock @wordpress/api-fetch
jest.mock( '@wordpress/api-fetch', () => ( {
    __esModule: true,
    default: jest.fn(),
} ) );

// Mock @wordpress/data
jest.mock( '@wordpress/data', () => ( {
    useSelect: ( selector ) => selector( mockSelectFn ),
    dispatch: () => ( { createNotice: jest.fn() } ),
} ) );

// Mock @wordpress/i18n
jest.mock( '@wordpress/i18n', () => ( {
    __: ( str ) => str,
    sprintf: ( format, ...args ) => {
        let result = format;
        args.forEach( ( arg ) => { result = result.replace( '%s', arg ); } );
        return result;
    },
} ) );
```

---

## Build & Development

```bash
# Development build with watch mode
pnpm run start

# Production build
pnpm run build

# Lint JavaScript
pnpm run lint:js

# Lint CSS/SCSS
pnpm run lint:css

# Lint PHP (requires composer dependencies)
composer lint
```

---

## Available Scripts

### package.json Scripts

| Script                  | Description                                          |
| ----------------------- | ---------------------------------------------------- |
| `pnpm run build`        | Production build of editor assets                    |
| `pnpm run start`        | Development build with file watching                 |
| `pnpm run test:js`      | Run all JavaScript tests                             |
| `pnpm run test:js:watch`| Run JS tests in watch mode                           |
| `pnpm run test:js:coverage` | Run JS tests with coverage report                |
| `pnpm run test:php`     | Run all PHP tests via wp-env                         |
| `pnpm run test:php:unit`| Run PHP unit tests via wp-env                        |
| `pnpm run test:php:integration` | Run PHP integration tests via wp-env          |
| `pnpm run test`         | Run all tests (JS + PHP)                             |
| `pnpm run lint:js`      | Lint JavaScript files                                |
| `pnpm run lint:css`     | Lint CSS/SCSS files                                  |
| `pnpm run env:start`    | Start the wp-env environment                         |
| `pnpm run env:stop`     | Stop the wp-env environment                          |
| `pnpm run env:destroy`  | Destroy the wp-env environment                       |

### Composer Scripts

| Script                  | Description                                          |
| ----------------------- | ---------------------------------------------------- |
| `composer test`         | Run all PHP tests                                    |
| `composer test:unit`    | Run PHP unit tests only                              |
| `composer test:integration` | Run PHP integration tests only                   |

---

## Filter & Action Hooks

For a full list of available filter and action hooks, see [hooks/Hooks.md](hooks/Hooks.md).
