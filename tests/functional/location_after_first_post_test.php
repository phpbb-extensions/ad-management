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
class location_after_first_post_test extends location_base
{
	/**
	* {@inheritDoc}
	*/
	public function setUp()
	{
		parent::setUp();
	}

	public function test_location_after_first_post()
	{
		$ad_code = $this->create_ad('after_first_post');

		$crawler = self::request('GET', "viewtopic.php?t=1");

		// Confirm after first post ad is after first post
		$this->assertContains($ad_code, $crawler->filter('#p1')->nextAll()->eq(1)->html());
	}
}
