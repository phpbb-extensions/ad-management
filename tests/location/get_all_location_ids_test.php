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
			array('index', '', false, array(
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
			array('viewtopic', '', false, array(
				'above_header',
				'after_header_navbar',
				'below_header',
				'above_footer',
				'after_footer_navbar',
				'below_footer',
				'after_first_post',
				'after_not_first_post',
				'after_posts',
				'before_posts',
				'pop_up',
				'slide_up',
				'scripts',
			)),
			array('memberlist', 'viewprofile', false, array(
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
			array('index', '', true, array(
				'above_header',
				'after_header_navbar',
				'below_header',
				'above_footer',
				'after_footer_navbar',
				'below_footer',
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
	public function test_get_all_location_ids($page_name, $query_string, $cookie, $expected)
	{
		$this->user->page['page_name'] = $page_name;
		$this->user->page['query_string'] = $query_string;

		$this->request
			->method('is_set')
			->with('_pop_up')
			->willReturn($cookie);

		$manager = $this->get_manager();

		$location_ids = $manager->get_all_location_ids();

		$this->assertEquals($expected, $location_ids);
	}
}
