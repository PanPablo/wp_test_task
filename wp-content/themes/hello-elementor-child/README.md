# Hello Elementor Child Theme

Child theme for Hello Elementor with a custom React admin app for managing Products.

## Requirements

- PHP 8.2+
- Node.js 18+
- npm 9+
- WordPress 6.x
- Elementor plugin

## Build environment

The React app is built using [@wordpress/scripts](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/) which provides a preconfigured webpack + Babel setup optimized for WordPress development.

### Key dependencies

| Package | Purpose |
|---|---|
| `@wordpress/scripts` | Build tooling (webpack, Babel, ESLint) |
| `@wordpress/components` | Gutenberg UI component library |
| `@wordpress/api-fetch` | HTTP client for the WordPress REST API (handles nonce automatically) |
| `@wordpress/element` | React wrapper used across WordPress |
| `@wordpress/i18n` | Translations (`__()`, `_x()`) |
| `react-router-dom` | Client-side routing between List / Add / Edit screens |

### Source structure

```
src/
└── components/     # React components
build/              # Compiled output (gitignored)
```

### Available commands

```bash
# Install dependencies
npm install

# Start dev server with file watcher
npm start

# Production build
npm run build
```

The build outputs `build/products-admin.js` and `build/products-admin.asset.php` (auto-generated dependency manifest used by WordPress to load scripts in the correct order).

## PHP Unit Tests

Integration tests for the REST API and custom post type, using the WordPress test suite via Composer.

### Test dependencies

| Package | Purpose |
|---|---|
| `phpunit/phpunit` | Test runner (v9.x — required by wp-phpunit) |
| `wp-phpunit/wp-phpunit` | WordPress test library (boots real WP environment with test DB) |
| `yoast/phpunit-polyfills` | Compatibility shims between PHPUnit versions |

### Setup

**1. Install Composer dependencies**
```bash
composer install
```

**2. Create a test database** (separate from production — the test suite drops and recreates tables on each run)
```bash
mysql -u root -p -e "CREATE DATABASE wp_test_task_tests;"
```

**3. Configure credentials**
```bash
cp wp-tests-config.php.example wp-tests-config.php
# Edit wp-tests-config.php — set ABSPATH, DB_USER, DB_PASSWORD
```

### Running tests

```bash
vendor/bin/phpunit
```

### Test coverage

| File | Tests | What is tested |
|---|---|---|
| `tests/PostTypeProductsTest.php` | 6 | CPT registration, visibility, REST exposure, rewrite slug |
| `tests/ProductsRestFieldsTest.php` | 9 | Meta registration, sanitization, REST read/write, auth |

### How it works

WordPress test suite boots a minimal WordPress installation connected to the test database. Each test method runs inside a database transaction that is rolled back afterwards — so tests never leave data behind and always start with a clean state.

REST API tests build a `WP_REST_Request` object in-process and dispatch it through `WP_REST_Server`, simulating a real HTTP request without needing a running web server.
