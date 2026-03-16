<?php
/**
 * Hello Elementor Child Theme functions
 */

add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles' );

add_action( 'after_setup_theme', 'hello_elementor_child_disable_admin_bar' );

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
