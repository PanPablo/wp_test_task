<?php

defined( 'ABSPATH' ) || exit;

class Products_Admin_Page {

	const MENU_SLUG = 'products-manager';

	public static function init(): void {
		add_action( 'admin_menu', [ self::class, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_scripts' ] );
	}

	public static function register_menu(): void {
		add_menu_page(
			__( 'Products Manager', 'hello-elementor-child' ),
			__( 'Products Manager', 'hello-elementor-child' ),
			'edit_posts',
			self::MENU_SLUG,
			[ self::class, 'render_page' ],
			'dashicons-cart',
			6
		);
	}

	public static function render_page(): void {
		echo '<div id="products-admin-root"></div>';
	}

	public static function enqueue_scripts( string $hook ): void {
		if ( 'toplevel_page_' . self::MENU_SLUG !== $hook ) {
			return;
		}

		$asset_file = get_stylesheet_directory() . '/build/products-admin.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			'products-admin',
			get_stylesheet_directory_uri() . '/build/products-admin.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_localize_script(
			'products-admin',
			'productsAdminData',
			[
				'restUrl' => esc_url_raw( rest_url() ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			]
		);

		wp_enqueue_style( 'wp-components' );
	}
}
