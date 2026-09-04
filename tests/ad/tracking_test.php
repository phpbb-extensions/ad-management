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
	public static function invalid_view_id_data(): array
	{
		return array(
			'zero' => array(0),
			'negative' => array(-1),
			'zero string' => array('0'),
		);
	}

	/**
	 * Invalid IDs cause no view update and leave advertisements unchanged.
	 *
	 * @dataProvider invalid_view_id_data
	 */
	public function test_invalid_view_id_does_not_increment($ad_id)
	{
		$manager = $this->get_manager();
		$query_count = $this->db->sql_num_queries();

		$manager->increment_ad_views($ad_id);

		self::assertSame($query_count, $this->db->sql_num_queries());
		self::assertEquals(0, $manager->get_ad(1)['ad_views']);
	}

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

		$manager->increment_ad_views(1);
		$manager->increment_ad_clicks(1);

		$ad = $manager->get_ad(1);
		self::assertEquals(0, $ad['ad_views']);
		self::assertEquals(0, $ad['ad_clicks']);
	}

	/**
	 * Click tracking updates metrics without disabling ads or notifying owners.
	 */
	public function test_click_tracking_does_not_disable_or_notify()
	{
		$notifications = $this->getMockBuilder('\phpbb\notification\manager')
			->disableOriginalConstructor()
			->getMock();
		$notifications->expects(self::never())->method('delete_notifications');
		$notifications->expects(self::never())->method('add_notifications');

		$manager = $this->get_manager_with_notifications($notifications);
		$manager->increment_ad_clicks(1);
		$manager->increment_ad_clicks(1);

		$ad = $manager->get_ad(1);
		self::assertEquals(2, $ad['ad_clicks']);
		self::assertEquals(1, $ad['ad_enabled']);
	}

	/**
	 * View tracking updates metrics without disabling ads or notifying owners.
	 */
	public function test_view_tracking_does_not_disable_or_notify()
	{
		$notifications = $this->getMockBuilder('\phpbb\notification\manager')
			->disableOriginalConstructor()
			->getMock();
		$notifications->expects(self::never())->method('delete_notifications');
		$notifications->expects(self::never())->method('add_notifications');

		$manager = $this->get_manager_with_notifications($notifications);
		$manager->increment_ad_views(1);
		$manager->increment_ad_views(1);

		$ad = $manager->get_ad(1);
		self::assertEquals(2, $ad['ad_views']);
		self::assertEquals(1, $ad['ad_enabled']);
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
	 * Expiration sweep disables multiple ads with one update query.
	 */
	public function test_expiration_sweep_uses_single_update()
	{
		$manager = $this->get_manager();
		$manager->update_ad(1, array('ad_end_date' => 1));

		$query_count = $this->db->sql_num_queries();
		self::assertEquals(2, $manager->disable_expired_ads());
		self::assertEquals(1, $this->db->sql_num_queries() - $query_count);
		self::assertEquals(0, $manager->get_ad(1)['ad_enabled']);
		self::assertEquals(0, $manager->get_ad(3)['ad_enabled']);
	}

	public static function expired_ad_id_data(): array
	{
		return array(
			'invalid ID' => array(0, false),
			'missing ad' => array(999, false),
			'active ad' => array(1, false),
			'expired ad' => array(3, true),
		);
	}

	/**
	 * Scalar IDs are loaded and disabled only when currently expired.
	 *
	 * @dataProvider expired_ad_id_data
	 */
	public function test_disable_expired_ad_by_id($ad_id, $expected)
	{
		$manager = $this->get_manager();

		self::assertSame($expected, $manager->disable_expired_ad($ad_id));
		if ($ad_id === 3)
		{
			self::assertEquals(0, $manager->get_ad($ad_id)['ad_enabled']);
		}
	}

	/**
	 * Atomic update rejects stale data which no longer meets expiry condition.
	 */
	public function test_disable_expired_ad_rechecks_condition_in_database()
	{
		$manager = $this->get_manager();
		$stale_ad = $manager->get_ad(1);
		$stale_ad['ad_end_date'] = 1;

		self::assertFalse($manager->disable_expired_ad($stale_ad));
		self::assertEquals(1, $manager->get_ad(1)['ad_enabled']);
	}

	/**
	 * Create manager using notification mock.
	 *
	 * @param \phpbb\notification\manager $notifications Notification manager
	 * @return \phpbb\ads\ad\manager
	 */
	protected function get_manager_with_notifications(\phpbb\notification\manager $notifications): \phpbb\ads\ad\manager
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
	protected function expect_disabled_notification(\phpbb\notification\manager $notifications, $expected): void
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
