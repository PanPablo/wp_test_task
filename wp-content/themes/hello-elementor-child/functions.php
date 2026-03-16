<?php
/**
 * Hello Elementor Child Theme functions
 */


//Register custom post type and taxonomy
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles' );

require_once get_stylesheet_directory() . '/includes/post-types/class-post-type-products.php';
require_once get_stylesheet_directory() . '/includes/taxonomies/class-taxonomy-product-category.php';
require_once get_stylesheet_directory() . '/includes/admin/class-products-admin-page.php';
require_once get_stylesheet_directory() . '/includes/rest/class-products-rest-fields.php';

add_action( 'init', [ Post_Type_Products::class, 'register' ] );
add_action( 'init', [ Taxonomy_Product_Category::class, 'register' ] );

Products_Admin_Page::init();
Products_Rest_Fields::init();

add_action( 'after_setup_theme', 'hello_elementor_child_disable_admin_bar' );


//Disable admin bar for users with editor role
function hello_elementor_child_disable_admin_bar() {
	$user = wp_get_current_user();

	if ( in_array( 'editor', (array) $user->roles, true ) ) {
		show_admin_bar( false );
	}
}

function hello_elementor_child_enqueue_styles() {
	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_uri(),
		[ 'hello-elementor-theme-style' ],
		wp_get_theme()->get( 'Version' )
	);
}
