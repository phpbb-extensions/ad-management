<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\ad;

class tracking_test extends ad_base
{
	/**
	 * Disabled counters reject otherwise valid increments.
	 */
	public function test_disabled_counters_do_not_increment()
	{
		$manager = $this->get_manager();
		$manager->update_ad(1, array(
			'ad_views_enabled' => 0,
			'ad_clicks_enabled' => 0,
		));

		$manager->increment_ads_views(array(1));
		$manager->increment_ad_clicks(1);

		$ad = $manager->get_ad(1);
		self::assertEquals(0, $ad['ad_views']);
		self::assertEquals(0, $ad['ad_clicks']);
	}

	/**
	 * Reaching click limit atomically caps count, disables ad, and notifies owner once.
	 */
	public function test_click_limit_disables_and_notifies()
	{
		$notifications = $this->getMockBuilder('\phpbb\notification\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->expect_disabled_notification($notifications, array(
				'ad_id' => 1,
				'ad_name' => 'Primary ad',
				'ad_owner' => 2,
				'reason' => \phpbb\ads\ad\manager::DISABLED_CLICKS_LIMIT,
		));

		$manager = $this->get_manager_with_notifications($notifications);
		$manager->update_ad(1, array(
			'ad_clicks_enabled' => 1,
			'ad_clicks_limit' => 1,
		));

		$manager->increment_ad_clicks(1);
		$manager->increment_ad_clicks(1);

		$ad = $manager->get_ad(1);
		self::assertEquals(1, $ad['ad_clicks']);
		self::assertEquals(0, $ad['ad_enabled']);
	}

	/**
	 * Duplicate IDs in a batch count as one view.
	 */
	public function test_view_batch_deduplicates_ads()
	{
		$manager = $this->get_manager();
		$manager->update_ad(1, array(
			'ad_views_enabled' => 1,
			'ad_views_limit' => 2,
		));

		$query_count = $this->db->sql_num_queries();
		$manager->increment_ads_views(array(1, 1, 1));
		self::assertSame(2, $this->db->sql_num_queries() - $query_count);

		$ad = $manager->get_ad(1);
		self::assertEquals(1, $ad['ad_views']);
		self::assertEquals(1, $ad['ad_enabled']);
	}

	/**
	 * Reaching view limit disables an ad and notifies its owner.
	 */
	public function test_view_limit_disables_ad()
	{
		$notifications = $this->getMockBuilder('\phpbb\notification\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->expect_disabled_notification($notifications, array(
				'ad_id' => 1,
				'ad_name' => 'Primary ad',
				'ad_owner' => 2,
				'reason' => \phpbb\ads\ad\manager::DISABLED_VIEWS_LIMIT,
		));

		$manager = $this->get_manager_with_notifications($notifications);
		$manager->update_ad(1, array(
			'ad_views_enabled' => 1,
			'ad_views_limit' => 1,
		));

		$manager->increment_ads_views(array(1));

		$ad = $manager->get_ad(1);
		self::assertEquals(1, $ad['ad_views']);
		self::assertEquals(0, $ad['ad_enabled']);
	}

	/**
	 * Expiration sweep disables dated ads and notifies their owners.
	 */
	public function test_expiration_sweep_disables_and_notifies()
	{
		$notifications = $this->getMockBuilder('\phpbb\notification\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->expect_disabled_notification($notifications, array(
				'ad_id' => 3,
				'ad_name' => 'Expired ad',
				'ad_owner' => 3,
				'reason' => \phpbb\ads\ad\manager::DISABLED_END_DATE,
		));

		$manager = $this->get_manager_with_notifications($notifications);

		$query_count = $this->db->sql_num_queries();
		self::assertEquals(1, $manager->disable_expired_ads());
		self::assertEquals(2, $this->db->sql_num_queries() - $query_count);
		self::assertEquals(0, $manager->get_ad(3)['ad_enabled']);
	}

	/**
	 * Create manager using notification mock.
	 *
	 * @param \phpbb\notification\manager $notifications Notification manager
	 * @return \phpbb\ads\ad\manager
	 */
	protected function get_manager_with_notifications(\phpbb\notification\manager $notifications)
	{
		return new \phpbb\ads\ad\manager(
			$this->db,
			$this->user,
			$this->ads_table,
			$this->ad_locations_table,
			$this->ad_group_table,
			$notifications
		);
	}

	/**
	 * Expect replacement of previous notification before sending a new one.
	 *
	 * @param \phpbb\notification\manager $notifications Notification manager mock
	 * @param array $expected Expected notification payload
	 * @return void
	 */
	protected function expect_disabled_notification(\phpbb\notification\manager $notifications, $expected)
	{
		$notifications->expects(self::once())
			->method('delete_notifications')
			->with(
				\phpbb\ads\ext::NOTIFICATION_TYPE_DISABLED,
				$expected['ad_id'],
				false,
				$expected['ad_owner']
			);
		$notifications->expects(self::once())
			->method('add_notifications')
			->with(\phpbb\ads\ext::NOTIFICATION_TYPE_DISABLED, $expected);
	}
}
