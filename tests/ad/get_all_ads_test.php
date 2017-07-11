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

class get_all_ads_test extends ad_base
{
	/**
	 * Test get_all_ads() method
	 */
	public function test_get_all_ads()
	{
		$manager = $this->get_manager();

		$ads = $manager->get_all_ads();

		$this->assertEquals(array(
			array(
				'ad_id' => '1',
				'ad_name' => 'Primary ad',
				'ad_enabled' => '1',
				'ad_end_date' => '2051308800',
			),
			array(
				'ad_id' => '2',
				'ad_name' => 'Disabled ad',
				'ad_enabled' => '0',
				'ad_end_date' => '0',
			),
			array(
				'ad_id' => '3',
				'ad_name' => 'Expired ad',
				'ad_enabled' => '1',
				'ad_end_date' => '1',
			),
			array(
				'ad_id' => '4',
				'ad_name' => 'Low priority ad',
				'ad_enabled' => '1',
				'ad_end_date' => '0',
			),
			array(
				'ad_id' => '5',
				'ad_name' => 'Med priority ad',
				'ad_enabled' => '1',
				'ad_end_date' => '0',
			),
			array(
				'ad_id' => '6',
				'ad_name' => 'Delete Me Ad',
				'ad_enabled' => '1',
				'ad_end_date' => '0',
			),
		), $ads);
	}
}
