<?php

defined( 'ABSPATH' ) || exit;

class Taxonomy_Product_Category {

	const SLUG = 'product_category';

	public static function register(): void {
		$labels = [
			'name'              => _x( 'Product Categories', 'taxonomy general name', 'hello-elementor-child' ),
			'singular_name'     => _x( 'Product Category', 'taxonomy singular name', 'hello-elementor-child' ),
			'search_items'      => __( 'Search Categories', 'hello-elementor-child' ),
			'all_items'         => __( 'All Categories', 'hello-elementor-child' ),
			'parent_item'       => __( 'Parent Category', 'hello-elementor-child' ),
			'parent_item_colon' => __( 'Parent Category:', 'hello-elementor-child' ),
			'edit_item'         => __( 'Edit Category', 'hello-elementor-child' ),
			'update_item'       => __( 'Update Category', 'hello-elementor-child' ),
			'add_new_item'      => __( 'Add New Category', 'hello-elementor-child' ),
			'new_item_name'     => __( 'New Category Name', 'hello-elementor-child' ),
			'menu_name'         => __( 'Categories', 'hello-elementor-child' ),
		];

		$args = [
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'product-category' ],
		];

		register_taxonomy( self::SLUG, Post_Type_Products::SLUG, $args );
	}
}
