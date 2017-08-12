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
class increment_views_test extends functional_base
{
	public function test_click_without_ajax()
	{
		$crawler = self::request('GET', 'app.php/adsview/1', [], false);
		$this->assertContainsLang('NOT_AUTHORISED', $crawler->text());
	}
}
