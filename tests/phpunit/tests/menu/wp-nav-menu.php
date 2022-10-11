<?php

/**
 * @group menu
 *
 * @covers ::wp_nav_menu
 */
class Tests_Menu_wpNavMenu extends WP_UnitTestCase {

	static $menu_id        = 0;
	static $lvl0_menu_item = 0;
	static $lvl1_menu_item = 0;
	static $lvl2_menu_item = 0;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Create nav menu.
		self::$menu_id = wp_create_nav_menu( 'test' );

		// Create lvl0 menu item.
		self::$lvl0_menu_item = wp_update_nav_menu_item(
			self::$menu_id,
			0,
			array(
				'menu-item-title'  => 'Root menu item',
				'menu-item-url'    => '#',
				'menu-item-status' => 'publish',
			)
		);

		// Create lvl1 menu item.
		self::$lvl1_menu_item = wp_update_nav_menu_item(
			self::$menu_id,
			0,
			array(
				'menu-item-title'     => 'Lvl1 menu item',
				'menu-item-url'       => '#',
				'menu-item-parent-id' => self::$lvl0_menu_item,
				'menu-item-status'    => 'publish',
			)
		);

		// Create lvl2 menu item.
		self::$lvl2_menu_item = wp_update_nav_menu_item(
			self::$menu_id,
			0,
			array(
				'menu-item-title'     => 'Lvl2 menu item',
				'menu-item-url'       => '#',
				'menu-item-parent-id' => self::$lvl1_menu_item,
				'menu-item-status'    => 'publish',
			)
		);

		/**
		 * This filter is used to prevent reusing a menu item ID more that once. It cause the tests to failed
		 * after the first one since the IDs are missing from the HTML generated by `wp_nav_menu`.
		 *
		 * To allow the tests to pass, we remove the filter before running them and add it back after
		 * they ran ({@see Tests_Menu_wpNavMenu::tear_down_after_class()}).
		 */
		remove_filter( 'nav_menu_item_id', '_nav_menu_item_id_use_once' );
	}

	public static function tear_down_after_class() {
		wp_delete_nav_menu( self::$menu_id );

		/**
		 * This filter was removed to let the tests pass and need to be added back ({@see Tests_Menu_wpNavMenu::set_up_before_class}).
		 */
		add_filter( 'nav_menu_item_id', '_nav_menu_item_id_use_once', 10, 2 );

		parent::tear_down_after_class();
	}

	/**
	 * Test all menu items containing children have the CSS class `menu-item-has-children` when displaying the menu
	 * without specifying a custom depth.
	 *
	 * @ticket 28620
	 */
	public function test_wp_nav_menu_should_have_has_children_class_without_custom_depth() {

		// Render the menu with all its hierarchy.
		$menu_html = wp_nav_menu(
			array(
				'menu' => self::$menu_id,
				'echo' => false,
			)
		);

		// Level 0 should be present in the HTML output and have the `menu-item-has-children` class.
		$this->assertStringContainsString(
			sprintf(
				'<li id="menu-item-%1$d" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-%1$d">',
				self::$lvl0_menu_item
			),
			$menu_html,
			'Level 0 should be present in the HTML output and have the menu-item-has-children class'
		);

		// Level 1 should be present in the HTML output and have the `menu-item-has-children` class.
		$this->assertStringContainsString(
			sprintf(
				'<li id="menu-item-%1$d" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-%1$d">',
				self::$lvl1_menu_item
			),
			$menu_html,
			'Level 1 should be present in the HTML output and have the menu-item-has-children class'
		);

		// Level 2 should be present in the HTML output and not have the `menu-item-has-children` class since it has no
		// children.
		$this->assertStringContainsString(
			sprintf(
				'<li id="menu-item-%1$d" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-%1$d">',
				self::$lvl2_menu_item
			),
			$menu_html,
			'Level 2 should be present in the HTML output and not have the `menu-item-has-children` class since it has no children'
		);
	}

	/**
	 * Tests that when displaying a menu with a custom depth, the last menu item doesn't have the CSS class
	 * `menu-item-has-children` even if it's the case when displaying the full menu.
	 *
	 * @ticket 28620
	 */
	public function test_wp_nav_menu_should_not_have_has_children_class_with_custom_depth() {

		// Render the menu limited to 1 level of hierarchy (Lvl0 + Lvl1).
		$menu_html = wp_nav_menu(
			array(
				'menu'  => self::$menu_id,
				'depth' => 2,
				'echo'  => false,
			)
		);

		// Level 0 should be present in the HTML output and have the `menu-item-has-children` class.
		$this->assertStringContainsString(
			sprintf(
				'<li id="menu-item-%1$d" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-%1$d">',
				self::$lvl0_menu_item
			),
			$menu_html,
			'Level 0 should be present in the HTML output and have the menu-item-has-children class'
		);

		// Level 1 should be present in the HTML output and not have the `menu-item-has-children` class since its the
		// last item to be rendered.
		$this->assertStringContainsString(
			sprintf(
				'<li id="menu-item-%1$d" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-%1$d">',
				self::$lvl1_menu_item
			),
			$menu_html,
			'Level 1 should be present in the HTML output and not have the `menu-item-has-children` class since its the last item to be rendered'
		);

		// Level 2 should not be present in the HTML output.
		$this->assertStringNotContainsString(
			sprintf(
				'<li id="menu-item-%d"',
				self::$lvl2_menu_item
			),
			$menu_html,
			'Level 2 should not be present in the HTML output'
		);
	}

}
