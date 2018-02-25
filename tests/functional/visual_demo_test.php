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
	/**
	 * {@inheritDoc}
	 */
	public function setUp()
	{
		parent::setUp();

		$this->add_lang_ext('phpbb/ads', array(
			'common',
		));
	}

	public function test_visual_demo()
	{
		$crawler = self::request('GET', "index.php?enable_visual_demo=true&sid={$this->sid}");

		// We should be on index page now. Visual demo disable prompt should be displayed.
		$this->assertContains($this->lang('DISABLE_VISUAL_DEMO', './index.php?disable_visual_demo=true'), $crawler->filter('.rules')->html());

		// Above header demo should be displayed
		$this->assertContains($this->lang('AD_ABOVE_HEADER'), $crawler->filter('body')->children()->first()->text());

		// Click "disable visual demo" link
		$disable_link = $crawler->filter('.rules a')->first()->link();
		$crawler = self::$client->click($disable_link);

		// Above header demo should not be displayed
		$this->assertNotContains($this->lang('AD_ABOVE_HEADER'), $crawler->filter('body')->children()->first()->text());
	}
}
