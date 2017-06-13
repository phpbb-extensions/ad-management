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
class location_before_posts_test extends functional_base
{
	public function test_location_before_posts()
	{
		$ad_code = $this->create_ad('before_posts');

		$crawler = self::request('GET', 'viewtopic.php?t=1');

		// Confirm before posts ad is before posts
		$this->assertContains($ad_code, $crawler->filter('.action-bar.bar-top')->nextAll()->html());
	}
}
