<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\ad;

class get_ads_by_timezone_test extends ad_base
{
	public function test_timezone_boundary_behavior()
	{
		$this->create_test_ad(strtotime('2020-01-01 00:00:00 UTC'));

		// Users at Jan 1 midnight in their timezone should see Jan 1 ad
		$this->assert_user_sees_ad('Pacific/Honolulu', '2020-01-01 00:00:00', true);
		$this->assert_user_sees_ad('Asia/Tokyo', '2020-01-01 00:00:00', true);

		// User before Jan 1 in their timezone should not see Jan 1 ad
		$this->assert_user_sees_ad('Pacific/Honolulu', '2019-12-31 23:59:59', false);
		$this->assert_user_sees_ad('Asia/Tokyo', '2019-12-31 23:59:59', false);
	}

	private function create_test_ad($start_timestamp)
	{
		$ad_data = [
			'ad_name' => 'Test',
			'ad_note' => 'Test',
			'ad_code' => 'Test Ad',
			'ad_enabled' => 1,
			'ad_start_date' => $start_timestamp,
			'ad_end_date' => 0,
			'ad_priority' => 5,
		];
		$this->db->sql_query('INSERT INTO ' . $this->ads_table . ' ' . $this->db->sql_build_array('INSERT', $ad_data));
		$ad_id = $this->db->sql_last_inserted_id();
		$this->db->sql_query('INSERT INTO ' . $this->ad_locations_table . ' ' . $this->db->sql_build_array('INSERT', ['ad_id' => $ad_id, 'location_id' => 'test_location']));
	}

	private function assert_user_sees_ad($timezone, $datetime, $should_see)
	{
		$test_time = new \DateTime($datetime, new \DateTimeZone($timezone));
		$utc_timestamp = strtotime($test_time->format('Y-m-d H:i:s') . ' UTC');

		$this->user = $this->createMock('\phpbb\user');
		$this->user->method('create_datetime')->willReturn($test_time);
		$this->user->method('get_timestamp_from_format')->willReturn($utc_timestamp);

		$ads = $this->get_manager()->get_ads(['test_location'], []);
		self::assertCount($should_see ? 1 : 0, $ads);
	}
}
