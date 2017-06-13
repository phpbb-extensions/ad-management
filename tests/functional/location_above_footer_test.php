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
class location_above_footer_test extends functional_base
{
	public function test_location_above_footer()
	{
		$ad_code = $this->create_ad('above_footer');

		$crawler = self::request('GET', 'index.php');

		// Confirm above footer ad is directly before page footer
		$this->assertContains($ad_code, $crawler->filter('#page-footer')->previousAll()->html());
	}
}
