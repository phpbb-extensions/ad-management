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

class get_ads_test extends ad_base
{
	/**
	 * Test data provider for test_get_ads()
	 *
	 * @return array Array of test data
	 */
	public function get_ads_data()
	{
		return array(
			array(array('after_profile'), array(
				array('location_id' => 'after_profile', 'ad_code' => 'Ad Code #1', 'ad_id' => '1'),
			)),
			array(array('before_profile'), array(
				array('location_id' => 'before_profile', 'ad_code' => 'Ad Code #4', 'ad_id' => '4'),
			)),
			array(array('foo_bar'), array()),
			array(array(null), array()),
		);
	}

	/**
	 * Test get_ads() method gets only enabled and unexpired ads
	 *
	 * @dataProvider get_ads_data
	 */
	public function test_get_ads($locations, $expected)
	{
		$manager = $this->get_manager();

		$ads = $manager->get_ads($locations, false);

		$this->assertEquals($expected, $ads);
	}

	/**
	 * Test get_ads() priority feature is working as expected.
	 * Higher priority ads should occur more frequently in the results.
	 */
	public function test_get_ads_priority()
	{
		$low = $mid = $high = 0;

		$manager = $this->get_manager();

		for ($i = 0; $i < 100; $i++)
		{
			$test = $manager->get_ads(array('above_header'), false);

			$ad = end($test);

			if ($ad['ad_code'] === 'Ad Code #1')
			{
				$high++;
			}
			else if ($ad['ad_code'] === 'Ad Code #5')
			{
				$mid++;
			}
			else if ($ad['ad_code'] === 'Ad Code #4')
			{
				$low++;
			}
		}

		$this->assertTrue($high > $mid);
		$this->assertTrue($mid > $low);
	}
}
