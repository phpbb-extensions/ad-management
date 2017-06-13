<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\admanagement\tests\functional;

/**
* @group functional
*/
class location_after_profile_test extends location_base
{
	public function test_location_after_profile()
	{
		$ad_code = $this->create_ad('after_profile');

		$crawler = self::request('GET', 'memberlist.php?mode=viewprofile&u=2');

		// Confirm after profile ad is after profile
		$this->assertContains($ad_code, $crawler->filter('#viewprofile')->nextAll()->html());
	}
}
