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
	public function test_click_without_ajax()
	{
		$this->test_increment_controller('app.php/adsclick/1');
	}

	public function test_views_without_ajax()
	{
		$this->test_increment_controller('app.php/adsview/1');
	}

	protected function test_increment_controller($url)
	{
		$crawler = self::request('GET', $url, [], false);
		$this->assertContainsLang('NOT_AUTHORISED', $crawler->text());
	}
}
