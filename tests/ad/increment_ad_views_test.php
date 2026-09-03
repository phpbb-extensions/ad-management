<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\ad;

class increment_ad_views_test extends ad_base
{
	/**
	 * Test data provider for test_increment_ad_views()
	 *
	 * @return array Array of test data
	 */
	public function increment_ad_views_data()
	{
		return array(
			array(1, 1),
			array(2, 0),
			array(3, 0),
		);
	}

	/**
	 * Test increment_ad_views() method
	 *
	 * @dataProvider increment_ad_views_data
	 */
	public function test_increment_ad_views($ad_id, $expected)
	{
		$manager = $this->get_manager();

		$manager->increment_ad_views($ad_id);

		$ad = $manager->get_ad($ad_id);

		self::assertEquals($expected, $ad['ad_views']);
	}
}
