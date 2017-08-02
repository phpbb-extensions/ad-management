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
	public function get_all_location_ids_data()
	{
		return array(
			array('index', '', array(
				'above_footer',
				'above_header',
				'after_footer_navbar',
				'after_header_navbar',
				'below_footer',
				'below_header',
				'pop_up',
				'slide_up',
			)),
			array('viewtopic', '', array(
				'above_footer',
				'above_header',
				'after_first_post',
				'after_footer_navbar',
				'after_header_navbar',
				'after_not_first_post',
				'after_posts',
				'before_posts',
				'below_footer',
				'below_header',
				'pop_up',
				'slide_up',
			)),
			array('memberlist', 'viewprofile', array(
				'above_footer',
				'above_header',
				'after_footer_navbar',
				'after_header_navbar',
				'after_profile',
				'before_profile',
				'below_footer',
				'below_header',
				'pop_up',
				'slide_up',
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

		$this->assertEquals($expected, $location_ids);
	}
}
