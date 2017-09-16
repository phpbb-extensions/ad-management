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
class location_before_profile_test extends functional_base
{
	public function test_location_before_profile()
	{
		$ad_code = $this->create_ad('before_profile');

		$crawler = self::request('GET', 'memberlist.php?mode=viewprofile&u=2');

		// Confirm before profile ad is before profile
		$this->assertContains($ad_code, $crawler->filter('#viewprofile')->previousAll()->html());
	}
}