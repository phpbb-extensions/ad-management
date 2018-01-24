<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\location;

class get_all_locations_test extends location_base
{
	/**
	 * Test get_all_locations() method
	 */
	public function test_get_all_locations()
	{
		$manager = $this->get_manager();

		$locations = $manager->get_all_locations();

		$this->assertEquals(array(
			'CAT_TOP_OF_PAGE'	=> array(
				'above_header'	=> array(
					'name'	=> 'AD_ABOVE_HEADER',
					'desc'	=> 'AD_ABOVE_HEADER_DESC',
				),
				'after_header_navbar'	=> array(
					'name'	=> 'AD_AFTER_HEADER_NAVBAR',
					'desc'	=> 'AD_AFTER_HEADER_NAVBAR_DESC',
				),
				'below_header'	=> array(
					'name'	=> 'AD_BELOW_HEADER',
					'desc'	=> 'AD_BELOW_HEADER_DESC',
				),
			),
			'CAT_BOTTOM_OF_PAGE'	=> array(
				'above_footer'	=> array(
					'name'	=> 'AD_ABOVE_FOOTER',
					'desc'	=> 'AD_ABOVE_FOOTER_DESC',
				),
				'after_footer_navbar'	=> array(
					'name'	=> 'AD_AFTER_FOOTER_NAVBAR',
					'desc'	=> 'AD_AFTER_FOOTER_NAVBAR_DESC',
				),
				'below_footer'	=> array(
					'name'	=> 'AD_BELOW_FOOTER',
					'desc'	=> 'AD_BELOW_FOOTER_DESC',
				),
			),
			'CAT_IN_POSTS'	=> array(
				'after_first_post'	=> array(
					'name'	=> 'AD_AFTER_FIRST_POST',
					'desc'	=> 'AD_AFTER_FIRST_POST_DESC',
				),
				'after_not_first_post'	=> array(
					'name'	=> 'AD_AFTER_NOT_FIRST_POST',
					'desc'	=> 'AD_AFTER_NOT_FIRST_POST_DESC',
				),
				'after_posts'	=> array(
					'name'	=> 'AD_AFTER_POSTS',
					'desc'	=> 'AD_AFTER_POSTS_DESC',
				),
				'before_posts'	=> array(
					'name'	=> 'AD_BEFORE_POSTS',
					'desc'	=> 'AD_BEFORE_POSTS_DESC',
				),
			),
			'CAT_OTHER'	=> array(
				'after_profile'	=> array(
					'name'	=> 'AD_AFTER_PROFILE',
					'desc'	=> 'AD_AFTER_PROFILE_DESC',
				),
				'before_profile'	=> array(
					'name'	=> 'AD_BEFORE_PROFILE',
					'desc'	=> 'AD_BEFORE_PROFILE_DESC',
				),
			),
			'CAT_INTERACTIVE'	=> array(
				'pop_up'	=> array(
					'name'	=> 'AD_POP_UP',
					'desc'	=> 'AD_POP_UP_DESC',
				),
				'slide_up'	=> array(
					'name'	=> 'AD_SLIDE_UP',
					'desc'	=> 'AD_SLIDE_UP_DESC',
				),
			),
		), $locations);
	}
}
