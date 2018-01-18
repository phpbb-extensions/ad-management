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
		$ad_ids = array_column($ads, 'ad_id');
		$this->assertEquals(array(1,2,3,4,5,6,7), $ad_ids);
	}
}
