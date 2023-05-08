<?php

/**
 * @group l10n
 * @group i18n
 *
 * @covers ::wp_word_count
 */
class Tests_L10n_wpWordcount extends WP_UnitTestCase {
	/**
	 * Tests that words are counted correctly based on the type.
	 *
	 * @ticket 56698
	 *
	 * @dataProvider data_get_string_variations
	 *
	 * @param string $text                        Text to count elements in.
	 * @param int    $words                       Expected value if the count type is based on word.
	 * @param int    $characters_excluding_spaces Expected value if the count type is based on single character excluding spaces.
	 * @param int    $characters_including_spaces Expected value if the count type is based on single character including spaces.
	 */
	public function test_word_count( $text, $words, $characters_excluding_spaces, $characters_including_spaces ) {
		$settings = array(
			'shortcodes' => array( 'shortcode' ),
		);

		$this->assertEquals( wp_word_count( $string, 'words', $settings ), $words );
		$this->assertEquals( wp_word_count( $string, 'characters_excluding_spaces', $settings ), $characters_excluding_spaces );
		$this->assertEquals( wp_word_count( $string, 'characters_including_spaces', $settings ), $characters_including_spaces );
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public function data_get_string_variations() {
		return array(
			'text containing spaces'     => array(
				'string'                      => 'one two three',
				'words'                       => 3,
				'characters_excluding_spaces' => 11,
				'characters_including_spaces' => 13,
			),
			'text containing HTML tags'      => array(
				'string'                      => 'one <em class="test">two</em><br />three',
				'words'                       => 3,
				'characters_excluding_spaces' => 11,
				'characters_including_spaces' => 12,
			),
			'Line breaks'    => array(
				'string'                      => "one\ntwo\nthree",
				'words'                       => 3,
				'characters_excluding_spaces' => 11,
				'characters_including_spaces' => 11,
			),
			'text containing encoded spaces' => array(
				'string'                      => 'one&nbsp;two&#160;three',
				'words'                       => 3,
				'characters_excluding_spaces' => 11,
				'characters_including_spaces' => 13,
			),
			'text containing punctuation'    => array(
				'string'                      => "It's two three " . json_decode( '"\u2026"' ) . ' 4?',
				'words'                       => 3,
				'characters_excluding_spaces' => 15,
				'characters_including_spaces' => 19,
			),
			'text containing an em dash'        => array(
				'string'                      => 'one' . json_decode( '"\u2014"' ) . 'two--three',
				'words'                       => 3,
				'characters_excluding_spaces' => 14,
				'characters_including_spaces' => 14,
			),
			'text containing shortcodes'     => array(
				'string'                      => 'one [shortcode attribute="value"]two[/shortcode]three',
				'words'                       => 3,
				'characters_excluding_spaces' => 11,
				'characters_including_spaces' => 12,
			),
			'text containing astrals'        => array(
				'string'                      => json_decode( '"\uD83D\uDCA9"' ),
				'words'                       => 1,
				'characters_excluding_spaces' => 1,
				'characters_including_spaces' => 1,
			),
			'HTML comment'   => array(
				'string'                      => 'one<!-- comment -->two three',
				'words'                       => 2,
				'characters_excluding_spaces' => 11,
				'characters_including_spaces' => 12,
			),
			'HTML entity'    => array(
				'string'                      => '&gt; test',
				'words'                       => 1,
				'characters_excluding_spaces' => 5,
				'characters_including_spaces' => 6,
			),
		);
	}
}
