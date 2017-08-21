<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\functional;

/**
 * @group functional
 */
class location_pop_up_test extends functional_base
{
	public function test_location_pop_up()
	{
		$ad_code = $this->create_ad('pop_up');

		$crawler = self::request('GET', 'index.php');

		// Confirm pop-up ad is present
		$this->assertContains($ad_code, $crawler->filter('script')->last()->html());
	}
}
