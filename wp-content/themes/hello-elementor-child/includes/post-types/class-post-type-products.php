<?php

defined( 'ABSPATH' ) || exit;

class Post_Type_Products {

	const SLUG = 'product';

	public static function register(): void {
		$labels = [
			'name'               => _x( 'Products', 'post type general name', 'hello-elementor-child' ),
			'singular_name'      => _x( 'Product', 'post type singular name', 'hello-elementor-child' ),
			'menu_name'          => _x( 'Products', 'admin menu', 'hello-elementor-child' ),
			'add_new'            => __( 'Add New', 'hello-elementor-child' ),
			'add_new_item'       => __( 'Add New Product', 'hello-elementor-child' ),
			'edit_item'          => __( 'Edit Product', 'hello-elementor-child' ),
			'new_item'           => __( 'New Product', 'hello-elementor-child' ),
			'view_item'          => __( 'View Product', 'hello-elementor-child' ),
			'search_items'       => __( 'Search Products', 'hello-elementor-child' ),
			'not_found'          => __( 'No products found', 'hello-elementor-child' ),
			'not_found_in_trash' => __( 'No products found in trash', 'hello-elementor-child' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true,
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'products' ],
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 5,
			'menu_icon'          => 'dashicons-cart',
			'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
		];

		register_post_type( self::SLUG, $args );
	}
}
