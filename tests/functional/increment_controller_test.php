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
class increment_controller_test extends functional_base
{
	/**
	 * Data for test_increment_controller
	 *
	 * @return array Array of test data
	 */
	public function increment_controller_data()
	{
		return array(
			array('app.php/adsclick/1'),
			array('app.php/adsview/1')
		);
	}

	/**
	 * Test increment controller
	 *
	 * @dataProvider increment_controller_data
	 */
	public function test_increment_controller($url)
	{
		$crawler = self::request('GET', $url, [], false);
		$this->assertContainsLang('NOT_AUTHORISED', $crawler->text());
	}
}
