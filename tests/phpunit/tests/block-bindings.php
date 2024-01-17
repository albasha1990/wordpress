<?php
/**
 * Unit tests covering WP_Block_Bindings functionality.
 *
 * @since 6.5.0
 * @package wordpress
 */

class WP_Block_Bindings_Test extends WP_UnitTestCase {

	/**
	* Test register_source method.
	*/
	public function test_register_source() {
		$wp_block_bindings = new WP_Block_Bindings();

		$source_name = 'test_source';
		$label       = 'Test Source';
		$apply       = function () { };

		$wp_block_bindings->register_source( $source_name, $label, $apply );

		$sources = $wp_block_bindings->get_sources();
		$this->assertArrayHasKey( $source_name, $sources );
		$this->assertEquals( $label, $sources[ $source_name ]['label'] );
		$this->assertEquals( $apply, $sources[ $source_name ]['apply'] );
	}

	/**
	* Test replace_html method for content.
	*/
	public function test_replace_html_for_paragraph_content() {
		$wp_block_bindings = new WP_Block_Bindings();

		$block_content = '<p>Hello World</p>';
		$block_name    = 'core/paragraph';
		$block_attr    = 'content';
		$source_value  = 'Updated Content';

		$result = $wp_block_bindings->replace_html( $block_content, $block_name, $block_attr, $source_value );

		// Check if the block content was updated correctly.
		$this->assertStringContainsString( $source_value, $result );
	}

	/**
	* Test replace_html method for attributes.
	*/
	public function test_replace_html_for_attribute() {
		$wp_block_bindings = new WP_Block_Bindings();
		$block_content     = '<div><a url\="">Hello World</a></div>';
		$block_name        = 'core/button';
		$block_attr        = 'url';
		$source_value      = 'Updated URL';

		$result = $wp_block_bindings->replace_html( $block_content, $block_name, $block_attr, $source_value );
		$this->assertStringContainsString( $source_value, $result );
	}

	/**
	* Test case for scenarios where block type is not registered.
	*/
	public function test_replace_html_with_unregistered_block() {
		$wp_block_bindings = new WP_Block_Bindings();

		$block_content = '<p>Hello World</p>';
		$block_name    = 'NONEXISTENT';
		$block_attr    = 'content';
		$source_value  = 'Updated Content';

		$result = $wp_block_bindings->replace_html( $block_content, $block_name, $block_attr, $source_value );

		$this->assertEquals( $block_content, $result );
	}

	/**
	* Test case for scenarios where block is registered but attribute does not exist on block type.
	*/
	public function test_replace_html_with_registered_block_but_unsupported_source_type() {
		$wp_block_bindings = new WP_Block_Bindings();

		$block_content = '<div>Hello World</div>';
		$block_name    = 'core/paragraph';
		$block_attr    = 'NONEXISTENT';
		$source_value  = 'Updated Content';

		$result = $wp_block_bindings->replace_html( $block_content, $block_name, $block_attr, $source_value );

		$this->assertEquals( $block_content, $result );
	}
}
