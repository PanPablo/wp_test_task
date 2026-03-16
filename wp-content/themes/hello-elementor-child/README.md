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
