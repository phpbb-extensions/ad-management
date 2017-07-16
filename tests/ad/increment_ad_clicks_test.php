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

class increment_ad_clicks_test extends ad_base
{
	/**
	 * Test data provider for test_increment_ad_clicks()
	 *
	 * @return array Array of test data
	 */
	public function increment_ad_clicks_data()
	{
		return array(
			array(1),
			array(0),
		);
	}

	/**
	 * Test increment_ad_clicks() method
	 *
	 * @dataProvider increment_ad_clicks_data
	 */
	public function test_increment_ad_clicks($ad_id)
	{
		$manager = $this->get_manager();

		$manager->increment_ad_clicks($ad_id);

		$ad = $manager->get_ad($ad_id);

		$this->assertEquals($ad_id ? 1 : null, $ad['ad_clicks']);
	}
}
