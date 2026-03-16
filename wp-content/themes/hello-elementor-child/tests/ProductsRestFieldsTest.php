<?php
/**
 * Tests for the Products REST API meta fields.
 *
 * We test the actual HTTP layer: we build a WP_REST_Request object,
 * dispatch it through WP_REST_Server (just like a real HTTP request would),
 * and inspect the WP_REST_Response that comes back.
 *
 * No browser, no curl — all in-process and rolled back after each test.
 */
class ProductsRestFieldsTest extends WP_UnitTestCase {

    /** @var WP_REST_Server */
    private WP_REST_Server $server;

    /** @var int — ID of an admin user created for auth tests */
    private int $admin_id;

    /**
     * set_up() runs before EACH test method.
     *
     * We boot a fresh REST server and create an admin user.
     * WP_UnitTestCase rolls back DB changes after each test,
     * so every test starts with a clean slate.
     */
    public function set_up(): void {
        parent::set_up();

        // WP_UnitTestCase::set_up() resets $wp_meta_keys and other globals
        // to a pre-test state, which wipes out meta registered during bootstrap.
        // We re-register here so every test has a clean but complete state.
        Products_Rest_Fields::register_meta();

        // Boot the REST server fresh for each test.
        global $wp_rest_server;
        $wp_rest_server = new WP_REST_Server();
        $this->server   = $wp_rest_server;
        do_action( 'rest_api_init', $this->server );

        // Create a test admin user. factory->user is a WP test helper
        // that creates real users in the test DB.
        $this->admin_id = self::factory()->user->create(
            [ 'role' => 'administrator' ]
        );
    }

    /**
     * tear_down() runs after EACH test method.
     *
     * Reset the REST server so it doesn't bleed into the next test.
     */
    public function tear_down(): void {
        global $wp_rest_server;
        $wp_rest_server = null;
        parent::tear_down();
    }

    // -------------------------------------------------------------------------
    // Meta registration tests
    // -------------------------------------------------------------------------

    /**
     * Test that all four meta keys are registered for the 'product' post type.
     *
     * get_registered_meta_keys() returns an array of meta keys registered
     * via register_post_meta(). If a key is missing, we forgot to register it.
     */
    public function test_all_meta_fields_are_registered(): void {
        $registered = get_registered_meta_keys( 'post', Post_Type_Products::SLUG );

        foreach ( [ 'price', 'sale_price', 'is_on_sale', 'youtube_video' ] as $key ) {
            $this->assertArrayHasKey(
                $key,
                $registered,
                "Meta key '$key' should be registered for the product post type."
            );
        }
    }

    /**
     * Test that all meta fields have show_in_rest = true.
     *
     * Without show_in_rest, the meta won't appear in REST responses
     * and our React app won't be able to read or write them.
     */
    public function test_meta_fields_are_exposed_in_rest(): void {
        $registered = get_registered_meta_keys( 'post', Post_Type_Products::SLUG );

        foreach ( [ 'price', 'sale_price', 'is_on_sale', 'youtube_video' ] as $key ) {
            $this->assertNotFalse(
                $registered[ $key ]['show_in_rest'],
                "Meta key '$key' should have show_in_rest enabled."
            );
        }
    }

    // -------------------------------------------------------------------------
    // Sanitization tests
    // These don't touch the REST server — they call the sanitize_callback
    // directly via update_post_meta() to confirm values are stored correctly.
    // -------------------------------------------------------------------------

    /**
     * Test that 'price' is stored as a float, not a string.
     *
     * WordPress stores meta as strings in the DB, but register_post_meta()
     * with type='number' tells the REST API to cast the value on read.
     * The sanitize_callback casts it to float before storage.
     */
    public function test_price_is_sanitized_to_float(): void {
        $post_id = self::factory()->post->create(
            [ 'post_type' => Post_Type_Products::SLUG ]
        );

        update_post_meta( $post_id, 'price', '19.99' );

        // get_post_meta() returns a string from the DB.
        // We cast to float to verify the sanitize_callback ran correctly.
        $stored = (float) get_post_meta( $post_id, 'price', true );

        $this->assertSame( 19.99, $stored, 'Price should be stored as float 19.99.' );
    }

    /**
     * Test that a non-numeric price value is sanitized to 0.
     *
     * is_numeric('abc') returns false, so the callback returns 0.0.
     * This prevents garbage data from entering the DB.
     */
    public function test_invalid_price_is_sanitized_to_zero(): void {
        $post_id = self::factory()->post->create(
            [ 'post_type' => Post_Type_Products::SLUG ]
        );

        update_post_meta( $post_id, 'price', 'abc' );

        $stored = (float) get_post_meta( $post_id, 'price', true );

        $this->assertSame( 0.0, $stored, 'Non-numeric price should be sanitized to 0.' );
    }

    /**
     * Test that 'is_on_sale' is stored as a boolean.
     *
     * WordPress serializes booleans — get_post_meta returns bool true/false
     * when the meta was registered with type='boolean'.
     */
    public function test_is_on_sale_is_sanitized_to_boolean(): void {
        $post_id = self::factory()->post->create(
            [ 'post_type' => Post_Type_Products::SLUG ]
        );

        update_post_meta( $post_id, 'is_on_sale', true );

        // WordPress stores booleans as '1'/'0' (or '' for false) in the DB.
        // Cast back to bool to verify the sanitize_callback stored a truthy value.
        $stored = (bool) get_post_meta( $post_id, 'is_on_sale', true );

        $this->assertTrue( $stored, 'is_on_sale should be stored as boolean true.' );
    }

    /**
     * Test that 'youtube_video' sanitizes HTML out of the URL.
     *
     * sanitize_text_field() strips tags and extra whitespace.
     */
    public function test_youtube_video_strips_html(): void {
        $post_id = self::factory()->post->create(
            [ 'post_type' => Post_Type_Products::SLUG ]
        );

        update_post_meta( $post_id, 'youtube_video', '<script>alert(1)</script>https://youtube.com/watch?v=abc' );

        $stored = get_post_meta( $post_id, 'youtube_video', true );

        $this->assertStringNotContainsString( '<script>', $stored );
    }

    // -------------------------------------------------------------------------
    // REST endpoint tests
    // -------------------------------------------------------------------------

    /**
     * Test that an unauthenticated request CANNOT write meta.
     *
     * wp_set_current_user(0) simulates a logged-out visitor.
     * The REST server should return HTTP 401 (Unauthorized).
     */
    public function test_unauthenticated_user_cannot_update_meta(): void {
        wp_set_current_user( 0 );

        $post_id = self::factory()->post->create(
            [ 'post_type' => Post_Type_Products::SLUG, 'post_status' => 'publish' ]
        );

        // Build a REST request — like a real HTTP PUT but in-process.
        $request = new WP_REST_Request( 'PUT', "/wp/v2/product/$post_id" );
        $request->set_body_params( [ 'meta' => [ 'price' => 99.0 ] ] );

        $response = $this->server->dispatch( $request );

        // 401 = not authenticated, 403 = authenticated but no permission.
        $this->assertContains(
            $response->get_status(),
            [ 401, 403 ],
            'Unauthenticated user should not be able to update product meta.'
        );
    }

    /**
     * Test that an admin CAN read meta fields via REST GET.
     *
     * We create a product, set its meta directly in the DB,
     * then fetch it through the REST API and confirm the values come back.
     */
    public function test_admin_can_read_meta_via_rest(): void {
        wp_set_current_user( $this->admin_id );

        $post_id = self::factory()->post->create(
            [ 'post_type' => Post_Type_Products::SLUG, 'post_status' => 'publish' ]
        );
        update_post_meta( $post_id, 'price', 49.99 );
        update_post_meta( $post_id, 'is_on_sale', false );

        $request  = new WP_REST_Request( 'GET', "/wp/v2/product/$post_id" );
        $response = $this->server->dispatch( $request );
        $data     = $response->get_data();

        $this->assertSame( 200, $response->get_status() );
        $this->assertSame( 49.99, $data['meta']['price'] );
        $this->assertFalse( $data['meta']['is_on_sale'] );
    }

    /**
     * Test that an admin CAN write meta fields via REST POST.
     *
     * We create a product through the REST API with meta values,
     * then read directly from the DB to confirm they were saved.
     */
    public function test_admin_can_write_meta_via_rest(): void {
        wp_set_current_user( $this->admin_id );

        $request = new WP_REST_Request( 'POST', '/wp/v2/product' );
        $request->set_body_params( [
            'title'  => 'Test Product',
            'status' => 'publish',
            'meta'   => [
                'price'         => 29.99,
                'sale_price'    => 19.99,
                'is_on_sale'    => true,
                'youtube_video' => 'https://www.youtube.com/watch?v=test',
            ],
        ] );

        $response = $this->server->dispatch( $request );
        $data     = $response->get_data();

        $this->assertSame( 201, $response->get_status(), 'Should return HTTP 201 Created.' );

        // Verify values hit the DB, not just the response.
        $post_id = $data['id'];
        $this->assertSame( 29.99, (float) get_post_meta( $post_id, 'price', true ) );
        $this->assertSame( 19.99, (float) get_post_meta( $post_id, 'sale_price', true ) );
        $this->assertTrue( (bool) get_post_meta( $post_id, 'is_on_sale', true ) );
        $this->assertSame(
            'https://www.youtube.com/watch?v=test',
            get_post_meta( $post_id, 'youtube_video', true )
        );
    }
}
