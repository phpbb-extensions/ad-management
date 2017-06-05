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
class location_below_header_test extends location_base
{
	/**
	* {@inheritDoc}
	*/
	public function setUp()
	{
		parent::setUp();
	}

	public function test_location_below_header()
	{
		$ad_code = $this->create_ad('below_header');

		$crawler = self::request('GET', "index.php");

		// Confirm below header ad is directly after header
		$this->assertContains($ad_code, $crawler->filter('.headerbar')->nextAll()->html());
	}
}
