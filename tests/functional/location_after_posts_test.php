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
class location_after_posts_test extends location_base
{
	public function test_location_after_posts()
	{
		$ad_code = $this->create_ad('after_posts');

		$crawler = self::request('GET', 'viewtopic.php?t=1');

		// Confirm after posts ad is after posts
		$this->assertContains($ad_code, $crawler->filter('.action-bar.bar-bottom')->previousAll()->html());
	}
}
