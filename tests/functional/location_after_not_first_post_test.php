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
class location_after_not_first_post_test extends functional_base
{
	public function test_location_after_not_first_post()
	{
		$ad_code = $this->create_ad('after_not_first_post');

		// Create a reply
		$this->create_post(2, 1, 'Re: Welcome to phpBB3', 'This is a test post.');

		$crawler = self::request('GET', 'viewtopic.php?t=1');

		// Confirm after not first post ad is after second post
		$this->assertContains($ad_code, $crawler->filter('#p2')->nextAll()->eq(1)->html());
	}
}
