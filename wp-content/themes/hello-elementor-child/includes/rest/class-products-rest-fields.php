<?php

defined( 'ABSPATH' ) || exit;

class Products_Rest_Fields {

	public static function init(): void {
		add_action( 'init', [ self::class, 'register_meta' ] );
	}

	public static function register_meta(): void {
		$meta_fields = [
			'price'         => [
				'type'        => 'number',
				'description' => __( 'Product price', 'hello-elementor-child' ),
			],
			'sale_price'    => [
				'type'        => 'number',
				'description' => __( 'Product sale price', 'hello-elementor-child' ),
			],
			'is_on_sale'    => [
				'type'        => 'boolean',
				'description' => __( 'Whether the product is on sale', 'hello-elementor-child' ),
			],
			'youtube_video' => [
				'type'        => 'string',
				'description' => __( 'YouTube video URL', 'hello-elementor-child' ),
			],
		];

		foreach ( $meta_fields as $key => $config ) {
			register_post_meta(
				Post_Type_Products::SLUG,
				$key,
				[
					'type'              => $config['type'],
					'description'       => $config['description'],
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => self::get_sanitize_callback( $config['type'] ),
					'auth_callback'     => fn() => current_user_can( 'edit_posts' ),
				]
			);
		}
	}

	private static function get_sanitize_callback( string $type ): callable {
		return match ( $type ) {
			'number'  => fn( $val ) => is_numeric( $val ) ? (float) $val : 0.0,
			'boolean' => fn( $val ) => (bool) $val,
			default   => 'sanitize_text_field',
		};
	}
}
