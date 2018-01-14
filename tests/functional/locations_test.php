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
class locations_test extends functional_base
{
	public function test_location_above_footer()
	{
		$ad_code = $this->create_ad('above_footer');

		$crawler = self::request('GET', 'index.php');

		// Confirm above footer ad is directly before page footer
		$this->assertContains($ad_code, $crawler->filter('#page-footer')->previousAll()->html());
	}

	public function test_location_above_header()
	{
		$ad_code = $this->create_ad('above_header');

		$crawler = self::request('GET', 'index.php');

		// Confirm above header ad is first child of body
		$this->assertContains($ad_code, $crawler->filter('body')->children()->first()->html());
	}

	public function test_location_after_first_post()
	{
		$ad_code = $this->create_ad('after_first_post');

		$crawler = self::request('GET', 'viewtopic.php?t=1');

		// Confirm after first post ad is NOT after first post when it's the only post
		$this->assertNotContains($ad_code, $crawler->filter('#p1')->nextAll()->eq(1)->html());

		// Create a reply
		$this->create_post(2, 1, 'Re: Welcome to phpBB3', 'This is a test post.');

		$crawler = self::request('GET', 'viewtopic.php?t=1');

		// Confirm after first post ad is after first post when it's the only post
		$this->assertContains($ad_code, $crawler->filter('#p1')->nextAll()->eq(1)->html());
	}

	public function test_location_after_footer_navbar()
	{
		$ad_code = $this->create_ad('after_footer_navbar');

		$crawler = self::request('GET', 'index.php');

		// Confirm after footer navbar ad is present on correct location
		$this->assertContains($ad_code, $crawler->filter('.copyright')->html());
	}

	public function test_location_after_header_navbar()
	{
		$ad_code = $this->create_ad('after_header_navbar');

		$crawler = self::request('GET', 'index.php');

		// Confirm after header navbar ad is present on correct location
		$this->assertContains($ad_code, $crawler->filter('#page-header')->nextAll()->eq(0)->html());
	}

	public function test_location_after_not_first_post()
	{
		$ad_code = $this->create_ad('after_not_first_post');

		$crawler = self::request('GET', 'viewtopic.php?t=1');

		// Confirm after not first post ad is after second post
		$this->assertContains($ad_code, $crawler->filter('#p2')->nextAll()->eq(1)->html());
	}

	public function test_location_after_posts()
	{
		$ad_code = $this->create_ad('after_posts');

		$crawler = self::request('GET', 'viewtopic.php?t=1');

		// Confirm after posts ad is after posts
		$this->assertContains($ad_code, $crawler->filter('.action-bar.bar-bottom')->previousAll()->html());
	}

	public function test_location_after_profile()
	{
		$ad_code = $this->create_ad('after_profile');

		$crawler = self::request('GET', 'memberlist.php?mode=viewprofile&u=2');

		// Confirm after profile ad is after profile
		$this->assertContains($ad_code, $crawler->filter('#viewprofile')->nextAll()->html());
	}

	public function test_location_before_posts()
	{
		$ad_code = $this->create_ad('before_posts');

		$crawler = self::request('GET', 'viewtopic.php?t=1');

		// Confirm before posts ad is before posts
		$this->assertContains($ad_code, $crawler->filter('.action-bar.bar-top')->nextAll()->html());
	}

	public function test_location_before_profile()
	{
		$ad_code = $this->create_ad('before_profile');

		$crawler = self::request('GET', 'memberlist.php?mode=viewprofile&u=2');

		// Confirm before profile ad is before profile
		$this->assertContains($ad_code, $crawler->filter('#viewprofile')->previousAll()->html());
	}

	public function test_location_below_footer()
	{
		$ad_code = $this->create_ad('below_footer');

		$crawler = self::request('GET', 'index.php');

		// Confirm below footer ad is last visible body children
		$this->assertContains($ad_code, $crawler->filter('.phpbb-ads-center')->last()->html());
	}

	public function test_location_below_header()
	{
		$ad_code = $this->create_ad('below_header');

		$crawler = self::request('GET', 'index.php');

		// Confirm below header ad is directly after header
		$this->assertContains($ad_code, $crawler->filter('.headerbar')->nextAll()->html());
	}

	public function test_location_pop_up()
	{
		$ad_code = $this->create_ad('pop_up');

		$crawler = self::request('GET', 'index.php');

		// Confirm pop-up ad is present
		$this->assertContains($ad_code, $crawler->filter('script')->last()->html());
	}

	public function test_location_slide_up()
	{
		$ad_code = $this->create_ad('slide_up');

		$crawler = self::request('GET', 'index.php');

		// Confirm pop-up ad is present
		$this->assertContains($ad_code, $crawler->filter('.phpbbad-slide-up')->html());
	}
}
