<?php
/**
 * Tests for the Products custom post type registration.
 *
 * Each public method starting with "test_" is one test case.
 * WP_UnitTestCase wraps PHPUnit and resets the WordPress state between tests
 * (rolls back DB transactions, clears caches, etc.).
 */
class PostTypeProductsTest extends WP_UnitTestCase {

    /**
     * Test that the 'product' post type exists after 'init' fires.
     *
     * post_type_exists() queries the global $wp_post_types registry
     * that WordPress builds when register_post_type() is called.
     */
    public function test_post_type_is_registered(): void {
        $this->assertTrue(
            post_type_exists( Post_Type_Products::SLUG ),
            'Post type "product" should be registered.'
        );
    }

    /**
     * Test that the CPT is marked as public.
     *
     * get_post_type_object() returns the full WP_Post_Type object
     * with all the arguments we passed to register_post_type().
     */
    public function test_post_type_is_public(): void {
        $post_type = get_post_type_object( Post_Type_Products::SLUG );

        $this->assertTrue(
            $post_type->public,
            'Post type should be public.'
        );
    }

    /**
     * Test that the CPT has an archive page enabled.
     */
    public function test_post_type_has_archive(): void {
        $post_type = get_post_type_object( Post_Type_Products::SLUG );

        $this->assertTrue(
            (bool) $post_type->has_archive,
            'Post type should have an archive.'
        );
    }

    /**
     * Test that the CPT supports 'title', 'editor' and 'thumbnail'.
     *
     * post_type_supports() checks the $wp_post_type_supports global.
     */
    public function test_post_type_supports_required_features(): void {
        $slug = Post_Type_Products::SLUG;

        $this->assertTrue( post_type_supports( $slug, 'title' ), 'Should support title.' );
        $this->assertTrue( post_type_supports( $slug, 'editor' ), 'Should support editor.' );
        $this->assertTrue( post_type_supports( $slug, 'thumbnail' ), 'Should support thumbnail.' );
    }

    /**
     * Test that the CPT is exposed in the REST API (show_in_rest = true).
     *
     * This is required for the Gutenberg editor and our React app to work.
     */
    public function test_post_type_is_in_rest(): void {
        $post_type = get_post_type_object( Post_Type_Products::SLUG );

        $this->assertTrue(
            $post_type->show_in_rest,
            'Post type should be available in the REST API.'
        );
    }

    /**
     * Test that the CPT rewrite slug is 'products'.
     */
    public function test_post_type_rewrite_slug(): void {
        $post_type = get_post_type_object( Post_Type_Products::SLUG );

        $this->assertSame(
            'products',
            $post_type->rewrite['slug'],
            'Rewrite slug should be "products".'
        );
    }
}
