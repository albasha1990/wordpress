<?php
/**
 * WP_REST_Block_Types_Controller tests.
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 5.5.0
 */

/**
 * Tests for WP_REST_Block_Types_Controller.
 *
 * @since 5.5.0
 *
 * @covers WP_REST_Block_Types_Controller
 *
 * @group restapi-blocks
 * @group restapi
 */
class REST_Block_Type_Controller_Test extends WP_Test_REST_Controller_Testcase {

	/**
	 * Admin user ID.
	 *
	 * @since 5.5.0
	 *
	 * @var int $subscriber_id
	 */
	protected static $admin_id;

	/**
	 * Subscriber user ID.
	 *
	 * @since 5.5.0
	 *
	 * @var int $subscriber_id
	 */
	protected static $subscriber_id;

	/**
	 * Create fake data before our tests run.
	 *
	 * @since 5.5.0
	 *
	 * @param WP_UnitTest_Factory $factory Helper that lets us create fake data.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		self::$admin_id      = $factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		self::$subscriber_id = $factory->user->create(
			array(
				'role' => 'subscriber',
			)
		);

		$name     = 'fake/test';
		$settings = array(
			'icon' => 'text',
		);

		register_block_type( $name, $settings );
	}

	/**
	 * @ticket 47620
	 */
	public static function wpTearDownAfterClass() {
		self::delete_user( self::$admin_id );
		self::delete_user( self::$subscriber_id );
		unregister_block_type( 'fake/test' );
	}

	/**
	 * @ticket 47620
	 */
	public function test_register_routes() {
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( '/wp/v2/block-types', $routes );
		$this->assertCount( 1, $routes['/wp/v2/block-types'] );
		$this->assertArrayHasKey( '/wp/v2/block-types/(?P<namespace>[a-zA-Z0-9_-]+)', $routes );
		$this->assertCount( 1, $routes['/wp/v2/block-types/(?P<namespace>[a-zA-Z0-9_-]+)'] );
		$this->assertArrayHasKey( '/wp/v2/block-types/(?P<namespace>[a-zA-Z0-9_-]+)/(?P<name>[a-zA-Z0-9_-]+)', $routes );
		$this->assertCount( 1, $routes['/wp/v2/block-types/(?P<namespace>[a-zA-Z0-9_-]+)/(?P<name>[a-zA-Z0-9_-]+)'] );
	}

	/**
	 * @ticket 47620
	 */
	public function test_context_param() {
		// Collection.
		$request  = new WP_REST_Request( 'OPTIONS', '/wp/v2/block-types' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 'view', $data['endpoints'][0]['args']['context']['default'] );
		$this->assertEquals( array( 'view', 'embed', 'edit' ), $data['endpoints'][0]['args']['context']['enum'] );
		// Single.
		$request  = new WP_REST_Request( 'OPTIONS', '/wp/v2/block-types/fake/test' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 'view', $data['endpoints'][0]['args']['context']['default'] );
		$this->assertEquals( array( 'view', 'embed', 'edit' ), $data['endpoints'][0]['args']['context']['enum'] );
	}

	/**
	 * @ticket 47620
	 */
	public function test_get_items() {
		$block_name = 'fake/test';
		wp_set_current_user( self::$admin_id );
		$request  = new WP_REST_Request( 'GET', '/wp/v2/block-types/fake' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$this->assertCount( 1, $data );
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( $block_name );
		$this->check_block_type_object( $block_type, $data[0], $data[0]['_links'] );
	}

	/**
	 * @ticket 47620
	 */
	public function test_get_item() {
		$block_name = 'fake/test';
		wp_set_current_user( self::$admin_id );
		$request    = new WP_REST_Request( 'GET', '/wp/v2/block-types/' . $block_name );
		$response   = rest_get_server()->dispatch( $request );
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( $block_name );
		$this->check_block_type_object( $block_type, $response->get_data(), $response->get_links() );
	}

	/**
	 * @ticket 47620
	 */
	public function test_get_item_with_styles() {
		$block_name   = 'fake/styles';
		$block_styles = array(
			'name'         => 'fancy-quote',
			'label'        => 'Fancy Quote',
			'style_handle' => 'myguten-style',
		);
		register_block_type( $block_name );
		register_block_style( $block_name, $block_styles );
		wp_set_current_user( self::$admin_id );
		$request  = new WP_REST_Request( 'GET', '/wp/v2/block-types/' . $block_name );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEqualSets( array( $block_styles ), $data['styles'] );

	}

	/**
	 * @ticket 47620
	 */
	public function test_get_item_with_styles_merge() {
		$block_name   = 'fake/styles2';
		$block_styles = array(
			'name'         => 'fancy-quote',
			'label'        => 'Fancy Quote',
			'style_handle' => 'myguten-style',
		);
		$settings     = array(
			'styles' => array(
				array(
					'name'         => 'blue-quote',
					'label'        => 'Blue Quote',
					'style_handle' => 'myguten-style',
				),
			),
		);
		register_block_type( $block_name, $settings );
		register_block_style( $block_name, $block_styles );
		wp_set_current_user( self::$admin_id );
		$request  = new WP_REST_Request( 'GET', '/wp/v2/block-types/' . $block_name );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$expected = array(
			array(
				'name'         => 'fancy-quote',
				'label'        => 'Fancy Quote',
				'style_handle' => 'myguten-style',
			),
			array(
				'name'         => 'blue-quote',
				'label'        => 'Blue Quote',
				'style_handle' => 'myguten-style',
			),
		);
		$this->assertEqualSets( $expected, $data['styles'] );

	}

	/**
	 * @ticket 47620
	 */
	public function test_get_block_invalid_name() {
		$block_type = 'fake/block';
		wp_set_current_user( self::$admin_id );
		$request  = new WP_REST_Request( 'GET', '/wp/v2/block-types/' . $block_type );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_block_type_invalid', $response, 404 );
	}

	/**
	 * @ticket 47620
	 */
	public function test_get_item_invalid() {
		$block_type = 'fake/invalid';
		$settings = array(
			'title'            => array( 'invalid_title' ),
			'description'      => array( 'invalid_description' ),
			'icon'             => array( 'invalid_icon' ),
			'attributes'       => 'invalid_attributes',
			'provides_context' => 'invalid_provides_context',
			'uses_context'     => 'invalid_uses_context',
			'category'         => false,
			'editor_script'    => array( 'invalid_editor_script' ),
			'script'           => array( 'invalid_script' ),
			'editor_style'     => array( 'invalid_editor_style' ),
			'style'            => array( 'invalid_style' ),
			'keywords'         => 'invalid_keywords',
			'parent'           => 'invalid_parent',
			'supports'         => 'invalid_supports',
			'styles'           => 'invalid_styles',
			'render_callback'  => 'invalid_callback',
			'textdomain'       => array( 'invalid_textdomain' ),
		);
		register_block_type( $block_type, $settings );
		wp_set_current_user( self::$admin_id );
		$request  = new WP_REST_Request( 'GET', '/wp/v2/block-types/' . $block_type );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( $block_type, $data['name'] );
		$this->assertEquals( 'invalid_title', $data['title'] );
		$this->assertEquals( 'invalid_description', $data['description'] );
		$this->assertEquals( 'invalid_icon', $data['icon'] );
		$this->assertEquals( 'editor_script', $data['invalid_editor_script'] );
		$this->assertEquals( 'script', $data['invalid_script'] );
		$this->assertEquals( 'editor_style', $data['invalid_editor_style'] );
		$this->assertEquals( 'style', $data['invalid_style'] );
		$this->assertEqualSets( array( 'invalid_provides_context' ), $data['provides_context'] );
		$this->assertEqualSets( array( 'invalid_uses_context' ), $data['uses_context'] );
		$this->assertEqualSets( array( 'invalid_keywords' ), $data['keywords'] );
		$this->assertEqualSets( array( 'invalid_parent' ), $data['parent'] );
		$this->assertEqualSets( array(), $data['supports'] );
		$this->assertEqualSets( array(), $data['styles'] );
		$this->assertEquals( null, $data['category'] );
		$this->assertEquals( null, $data['textdomain'] );
		$this->assertFalse( false, $data['is_dynamic'] );
	}

	/**
	 * @ticket 47620
	 */
	public function test_get_item_schema() {
		wp_set_current_user( self::$admin_id );
		$request    = new WP_REST_Request( 'OPTIONS', '/wp/v2/block-types' );
		$response   = rest_get_server()->dispatch( $request );
		$data       = $response->get_data();
		$properties = $data['schema']['properties'];
		$this->assertCount( 19, $properties );
		$this->assertArrayHasKey( 'title', $properties );
		$this->assertArrayHasKey( 'icon', $properties );
		$this->assertArrayHasKey( 'description', $properties );
		$this->assertArrayHasKey( 'keywords', $properties );
		$this->assertArrayHasKey( 'styles', $properties );
		$this->assertArrayHasKey( 'textdomain', $properties );
		$this->assertArrayHasKey( 'name', $properties );
		$this->assertArrayHasKey( 'attributes', $properties );
		$this->assertArrayHasKey( 'supports', $properties );
		$this->assertArrayHasKey( 'category', $properties );
		$this->assertArrayHasKey( 'is_dynamic', $properties );
		$this->assertArrayHasKey( 'editor_script', $properties );
		$this->assertArrayHasKey( 'script', $properties );
		$this->assertArrayHasKey( 'editor_style', $properties );
		$this->assertArrayHasKey( 'style', $properties );
		$this->assertArrayHasKey( 'parent', $properties );
		$this->assertArrayHasKey( 'example', $properties );
		$this->assertArrayHasKey( 'uses_context', $properties );
		$this->assertArrayHasKey( 'provides_context', $properties );
	}

	/**
	 * @ticket 47620
	 */
	public function test_get_items_wrong_permission() {
		wp_set_current_user( self::$subscriber_id );
		$request  = new WP_REST_Request( 'GET', '/wp/v2/block-types' );
		$response = rest_get_server()->dispatch( $request );
		$this->assertErrorResponse( 'rest_block_type_cannot_view', $response, 403 );
	}

	/**
	 * @ticket 47620
	 */
	public function test_get_item_wrong_permission() {
		wp_set_current_user( self::$subscriber_id );
		$request  = new WP_REST_Request( 'GET', '/wp/v2/block-types/fake/test' );
		$response = rest_get_server()->dispatch( $request );
		$this->assertErrorResponse( 'rest_block_type_cannot_view', $response, 403 );
	}

	/**
	 * @ticket 47620
	 */
	public function test_get_items_no_permission() {
		wp_set_current_user( 0 );
		$request  = new WP_REST_Request( 'GET', '/wp/v2/block-types' );
		$response = rest_get_server()->dispatch( $request );
		$this->assertErrorResponse( 'rest_block_type_cannot_view', $response, 401 );
	}

	/**
	 * @ticket 47620
	 */
	public function test_get_item_no_permission() {
		wp_set_current_user( 0 );
		$request  = new WP_REST_Request( 'GET', '/wp/v2/block-types/fake/test' );
		$response = rest_get_server()->dispatch( $request );
		$this->assertErrorResponse( 'rest_block_type_cannot_view', $response, 401 );
	}

	/**
	 * @ticket 47620
	 */
	public function test_prepare_item() {
		$registry = new WP_Block_Type_Registry;
		$settings = array(
			'icon'            => 'text',
			'render_callback' => '__return_null',
		);
		$registry->register( 'fake/line', $settings );
		$block_type = $registry->get_registered( 'fake/line' );
		$endpoint   = new WP_REST_Block_Types_Controller;
		$request    = new WP_REST_Request;
		$request->set_param( 'context', 'edit' );
		$response = $endpoint->prepare_item_for_response( $block_type, $request );
		$this->check_block_type_object( $block_type, $response->get_data(), $response->get_links() );
	}

	/**
	 * @ticket 47620
	 */
	public function test_prepare_item_limit_fields() {
		$registry = new WP_Block_Type_Registry;
		$settings = array(
			'icon'            => 'text',
			'render_callback' => '__return_null',
		);
		$registry->register( 'fake/line', $settings );
		$block_type = $registry->get_registered( 'fake/line' );
		$request    = new WP_REST_Request;
		$endpoint   = new WP_REST_Block_Types_Controller;
		$request->set_param( 'context', 'edit' );
		$request->set_param( '_fields', 'name' );
		$response = $endpoint->prepare_item_for_response( $block_type, $request );
		$this->assertEquals(
			array(
				'name',
			),
			array_keys( $response->get_data() )
		);
	}

	/**
	 * Util check block type object against.
	 *
	 * @param WP_Block_Type $block_type Sample block type.
	 * @param array         $data Data to compare against.
	 * @param array         $links Links to compare again.
	 */
	public function check_block_type_object( $block_type, $data, $links ) {
		// Test data.
		$this->assertEquals( $data['attributes'], $block_type->get_attributes() );
		$this->assertEquals( $data['is_dynamic'], $block_type->is_dynamic() );

		$extra_fields = array(
			'name'             => 'name',
			'category'         => 'category',
			'editor_script'    => 'editor_script',
			'script'           => 'script',
			'editor_style'     => 'editor_style',
			'style'            => 'style',
			'title'            => 'title',
			'icon'             => 'icon',
			'description'      => 'description',
			'keywords'         => 'keywords',
			'parent'           => 'parent',
			'provides_context' => 'provides_context',
			'uses_context'     => 'uses_context',
			'supports'         => 'supports',
			'styles'           => 'styles',
			'textdomain'       => 'textdomain',
			'example'          => 'example',
		);

		foreach ( $extra_fields as $key => $extra_field ) {
			if ( isset( $block_type->$extra_field ) ) {
				$this->assertEquals( $data[ $key ], $block_type->$extra_field );
			}
		}

		// Test links.
		$this->assertEquals( rest_url( 'wp/v2/block-types' ), $links['collection'][0]['href'] );
		$this->assertEquals( rest_url( 'wp/v2/block-types/' . $block_type->name ), $links['self'][0]['href'] );
		if ( $block_type->is_dynamic() ) {
			$this->assertArrayHasKey( 'https://api.w.org/render-block', $links );
		}
	}

	/**
	 * The test_create_item() method does not exist for block types.
	 */
	public function test_create_item() {}

	/**
	 * The test_update_item() method does not exist for block types.
	 */
	public function test_update_item() {}

	/**
	 * The test_delete_item() method does not exist for block types.
	 */
	public function test_delete_item() {}
}
