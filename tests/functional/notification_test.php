<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\functional;

/**
 * @group functional
 */
class notification_test extends functional_base
{
	public function test_notification_option()
	{
		$crawler = self::request('GET', 'ucp.php?i=ucp_notifications&mode=notification_options');

		$this->assertContainsLang('NOTIFICATION_TYPE_PHPBB_ADS_AD_DISABLED', $crawler->filter('#cp-main')->text());
		self::assertCount(1, $crawler->filter('input[name="phpbb.ads.notification.type.ad_disabled_notification.method.board"]'));
		self::assertCount(1, $crawler->filter('input[name="phpbb.ads.notification.type.ad_disabled_notification.method.email"]'));
	}

	public function test_owner_triggering_click_limit_receives_notification()
	{
		$owner = 'ads-notification-owner';
		$this->create_user($owner);
		$this->disable_all_ads();
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-ads-acp-main_module&mode=manage&sid={$this->sid}");
		$form = $crawler->selectButton($this->lang('ACP_ADS_ADD'))->form();
		$crawler = self::submit($form);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		self::submit($form, array(
			'ad_name' => 'Click notification test',
			'ad_note' => '',
			'ad_code' => '<a href="https://example.com">Advertisement</a>',
			'ad_enabled' => 1,
			'ad_locations' => array('above_header'),
			'ad_start_date' => '',
			'ad_end_date' => '',
			'ad_priority' => 5,
			'ad_content_only' => 0,
			'ad_views_enabled' => 0,
			'ad_views_limit' => 0,
			'ad_clicks_enabled' => 1,
			'ad_clicks_limit' => 1,
			'ad_owner' => $owner,
			'ad_groups' => array(),
			'ad_centering' => 1,
			'ad_consent' => 0,
		));

		self::$client->restart();
		$this->login($owner);
		$crawler = self::request('GET', 'index.php');
		$ad = $crawler->filter('[data-phpbb-ads-click-url]');
		self::assertCount(1, $ad);
		$click_url = parse_url(htmlspecialchars_decode($ad->attr('data-phpbb-ads-click-url')));
		$click_url = ltrim($click_url['path'], '/') . (isset($click_url['query']) ? '?' . $click_url['query'] : '');

		self::$client->setServerParameter('HTTP_X_REQUESTED_WITH', 'XMLHttpRequest');
		self::request('POST', $click_url, array(), false);
		self::assertSame(200, self::$client->getResponse()->getStatusCode(), self::get_content());
		self::$client->setServerParameter('HTTP_X_REQUESTED_WITH', '');

		$sql = "SELECT ad_enabled, ad_clicks
			FROM phpbb_ads
			WHERE ad_name = 'Click notification test'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		self::assertEquals(1, $row['ad_clicks']);
		self::assertEquals(0, $row['ad_enabled']);

		$crawler = self::request('GET', 'ucp.php?i=ucp_notifications&mode=notification_list');
		$notification_text = $crawler->filter('#cp-main')->text();
		$reason = strip_tags($this->lang('PHPBB_ADS_NOTIFICATION_REASON_CLICKS_LIMIT'));
		self::assertStringContainsString('Click notification test', $notification_text);
		self::assertSame(1, substr_count($notification_text, $reason));
	}
}
