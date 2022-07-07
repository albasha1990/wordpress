<?php

class TestFactoryFor extends WP_UnitTestCase {
	public function set_up() {
		parent::set_up();
		$this->category_factory = new WP_UnitTest_Factory_For_Term( null, 'category' );
	}

	/**
	 * @covers ::get_term_by
	 */
	public function test_create_creates_a_category() {
		$id = $this->category_factory->create();
		$this->assertInstanceOf( 'WP_Term', get_term_by( 'id', $id, 'category' ) );
	}

	/**
	 * @covers ::get_term
	 */
	public function test_get_object_by_id_gets_an_object() {
		$id = $this->category_factory->create();
		$this->assertInstanceOf( 'WP_Term', $this->category_factory->get_object_by_id( $id ) );
	}

	/**
	 * @covers ::get_term
	 */
	public function test_get_object_by_id_gets_an_object_with_the_same_name() {
		$id     = $this->category_factory->create( array( 'name' => 'Boo' ) );
		$object = $this->category_factory->get_object_by_id( $id );
		$this->assertSame( 'Boo', $object->name );
	}

	/**
	 * @covers ::get_term
	 */
	public function test_the_taxonomy_argument_overrules_the_factory_taxonomy() {
		$term_factory = new WP_UnitTest_Factory_For_term( null, 'category' );
		$id           = $term_factory->create( array( 'taxonomy' => 'post_tag' ) );
		$term         = get_term( $id, 'post_tag' );
		$this->assertSame( $id, $term->term_id );
	}

	/**
	 * @ticket 32536
	 *
	 * @covers ::register_taxonomy
	 */
	public function test_term_factory_create_and_get_should_return_term_object() {
		register_taxonomy( 'wptests_tax', 'post' );
		$term = self::factory()->term->create_and_get( array( 'taxonomy' => 'wptests_tax' ) );
		$this->assertIsObject( $term );
		$this->assertNotEmpty( $term->term_id );
	}
}
