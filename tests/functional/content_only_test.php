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
class content_only_test extends functional_base
{
	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->disable_all_ads();
	}

	public function test_content_only_ad_displays()
	{
		$ad_code = $this->create_ad('above_header', '', true);

		$crawler = self::request('GET', 'index.php');

		// Confirm above header ad is present
		self::assertStringContainsString($ad_code, $crawler->html());
	}

	public function test_content_only_ad_hides()
	{
		$ad_code = $this->create_ad('above_header', '', true);

		$crawler = self::request('GET', 'ucp.php');
		$this->assertContainsLang('UCP', $crawler->filter('h2')->text());
		self::assertStringNotContainsString($ad_code, $crawler->html());

		$crawler = self::request('GET', 'mcp.php');
		$this->assertContainsLang('MCP', $crawler->filter('h2')->text());
		self::assertStringNotContainsString($ad_code, $crawler->html());

		$crawler = self::request('GET', 'posting.php?mode=post&f=2');
		self::assertCount(1, $crawler->filter('#postingbox'));
		self::assertStringNotContainsString($ad_code, $crawler->html());

		$crawler = self::request('GET', 'memberlist.php');
		$this->assertContainsLang('MEMBERLIST', $crawler->filter('h2')->eq(1)->text());
		self::assertStringNotContainsString($ad_code, $crawler->html());

		$crawler = self::request('GET', 'viewonline.php');
		self::assertCount(1, $crawler->filter('.viewonline-title'));
		self::assertStringNotContainsString($ad_code, $crawler->html());

		$this->logout();
		$this->login();
		$crawler = self::request('GET', 'adm/index.php?sid=' . $this->sid);
		self::assertStringContainsString($this->lang('LOGIN_ADMIN_CONFIRM'), $crawler->filter('html')->text());
		self::assertStringNotContainsString($ad_code, $crawler->html());
	}
}
