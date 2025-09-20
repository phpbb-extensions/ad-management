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

class get_all_location_ids_test extends location_base
{
	/**
	 * Test data provider for test_get_all_location_ids()
	 *
	 * @return array Array of test data
	 */
	public static function get_all_location_ids_data(): array
	{
		return array(
			array('index', '', array(
				'above_header',
				'after_header_navbar',
				'below_header',
				'above_footer',
				'after_footer_navbar',
				'below_footer',
				'pop_up',
				'slide_up',
				'scripts',
			)),
			array('viewtopic', '', array(
				'above_header',
				'after_header_navbar',
				'below_header',
				'above_footer',
				'after_footer_navbar',
				'below_footer',
				'after_first_post',
				'after_not_first_post',
				'after_posts',
				'after_quickreply',
				'before_posts',
				'before_quickreply',
				'pop_up',
				'slide_up',
				'scripts',
			)),
			array('memberlist', 'viewprofile', array(
				'above_header',
				'after_header_navbar',
				'below_header',
				'above_footer',
				'after_footer_navbar',
				'below_footer',
				'after_profile',
				'before_profile',
				'pop_up',
				'slide_up',
				'scripts',
			)),
			array('index', '', array(
				'above_header',
				'after_header_navbar',
				'below_header',
				'above_footer',
				'after_footer_navbar',
				'below_footer',
				'pop_up',
				'slide_up',
				'scripts',
			)),
		);
	}

	/**
	 * Test get_all_location_ids() method
	 *
	 * @dataProvider get_all_location_ids_data
	 */
	public function test_get_all_location_ids($page_name, $query_string, $expected)
	{
		$this->user->page['page_name'] = $page_name;
		$this->user->page['query_string'] = $query_string;

		$manager = $this->get_manager();

		$location_ids = $manager->get_all_location_ids();

		self::assertEquals($expected, $location_ids);
	}
}
