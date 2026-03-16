<?php
/**
 * Bootstrap file for PHPUnit tests.
 *
 * This file is the entry point for the test suite. It:
 * 1. Finds the WordPress test library (from wp-phpunit/wp-phpunit package).
 * 2. Registers a hook to load our theme classes BEFORE WordPress boots.
 * 3. Starts the WordPress test environment (creates tables, loads WP core).
 */

// Tell wp-phpunit where to find our wp-tests-config.php.
// Without this it looks inside the vendor package directory.
if ( ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
    define( 'WP_TESTS_CONFIG_FILE_PATH', dirname( __DIR__ ) . '/wp-tests-config.php' );
}

// Point to the WP test library installed via Composer.
$_tests_dir = dirname( __DIR__ ) . '/vendor/wp-phpunit/wp-phpunit';

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
    echo "ERROR: WordPress test library not found. Run: composer install\n";
    exit( 1 );
}

// Load the helper function tests_add_filter() — lets us hook into WP boot process.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Register our theme classes before WordPress fires its 'init' action.
 *
 * tests_add_filter() is like add_filter() but runs during the test bootstrap.
 * We use 'muplugins_loaded' — an early WP hook — to require our files
 * and register the same hooks that functions.php would register on a real site.
 */
tests_add_filter(
    'muplugins_loaded',
    function () {
        $theme_dir = dirname( __DIR__ ) . '/includes';

        require_once $theme_dir . '/post-types/class-post-type-products.php';
        require_once $theme_dir . '/taxonomies/class-taxonomy-product-category.php';
        require_once $theme_dir . '/rest/class-products-rest-fields.php';

        // Register the same hooks as in functions.php.
        add_action( 'init', [ Post_Type_Products::class, 'register' ] );
        add_action( 'init', [ Taxonomy_Product_Category::class, 'register' ] );
        Products_Rest_Fields::init();
    }
);

// Boot WordPress test environment.
// This creates the test database tables and loads all of WP core.
require_once $_tests_dir . '/includes/bootstrap.php';
