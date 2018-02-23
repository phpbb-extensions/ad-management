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
class visual_demo_test extends functional_base
{
	public function test_visual_demo()
	{
		$crawler = self::request('GET', "index.php?enable_visual_demo=true&sid={$this->sid}");

		// We should be on index page now. Visual demo disable prompt should be displayed.
		$this->assertContainsLang('DISABLE_VISUAL_DEMO', $crawler->filter('.rules')->text());
	}
}
