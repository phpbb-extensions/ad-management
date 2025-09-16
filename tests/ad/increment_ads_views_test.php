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

class increment_ads_views_test extends ad_base
{
	/**
	 * Test data provider for test_increment_ads_views()
	 *
	 * @return array Array of test data
	 */
	public static function increment_ads_views_data(): array
	{
		return array(
			array(array(1)),
			array(array(2,3)),
		);
	}

	/**
	 * Test increment_ads_views() method
	 *
	 * @dataProvider increment_ads_views_data
	 */
	public function test_increment_ads_views($ad_ids)
	{
		$manager = $this->get_manager();

		$manager->increment_ads_views($ad_ids);

		foreach ($ad_ids as $ad_id)
		{
			$ad = $manager->get_ad($ad_id);

			self::assertEquals(1, $ad['ad_views']);
		}
	}
}
