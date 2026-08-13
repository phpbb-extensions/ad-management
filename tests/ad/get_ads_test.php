<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\ad;

class get_ads_test extends ad_base
{
	/**
	 * Test data provider for test_get_ads()
	 *
	 * @return array Array of test data
	 */
	public static function get_ads_data(): array
	{
		return array(
			array(array('after_profile'), array(
				array('location_id' => 'after_profile', 'ad_code' => 'Ad Code #1', 'ad_id' => '1', 'ad_centering' => '1', 'ad_consent' => '1', 'ad_views_enabled' => '1', 'ad_clicks_enabled' => '1'),
			), false),
			array(array('before_profile'), array(
				array('location_id' => 'before_profile', 'ad_code' => 'Ad Code #4', 'ad_id' => '4', 'ad_centering' => '1', 'ad_consent' => '1', 'ad_views_enabled' => '1', 'ad_clicks_enabled' => '1'),
			), false),
			array(array('below_footer'), array(
				array('location_id' => 'below_footer', 'ad_code' => 'Ad Code #7', 'ad_id' => '7', 'ad_centering' => '1', 'ad_consent' => '1', 'ad_views_enabled' => '1', 'ad_clicks_enabled' => '1'),
			), false),
			array(array('below_footer'), array(), true),
			array(array('foo_bar'), array(), false),
			array(array(null), array(), false),
		);
	}

	/**
	 * Test get_ads() method gets only enabled and unexpired ads
	 *
	 * @dataProvider get_ads_data
	 */
	public function test_get_ads($locations, $expected, $non_content_page)
	{
		$manager = $this->get_manager();

		$ads = $manager->get_ads($locations, [], $non_content_page);

		self::assertEquals($expected, $ads);
	}

	public static function sql_random_data()
	{
		return array(
			'oracle' => array('oracle', 'DBMS_RANDOM.VALUE'),
			'postgres' => array('postgres', 'RANDOM()'),
			'sqlite' => array('sqlite', '(0.5 - RANDOM() / CAST(-9223372036854775808 AS REAL) / 2)'),
			'sqlite3' => array('sqlite3', '(0.5 - RANDOM() / CAST(-9223372036854775808 AS REAL) / 2)'),
			'mssql' => array('mssql', 'RAND(CAST(NEWID() AS VARBINARY))'),
			'mssql ODBC' => array('mssql_odbc', 'RAND(CAST(NEWID() AS VARBINARY))'),
			'mssql native' => array('mssqlnative', 'RAND(CAST(NEWID() AS VARBINARY))'),
			'mysql default' => array('mysqli', 'RAND()'),
		);
	}

	/**
	 * Each supported DBMS uses its valid random expression in weighted ad selection.
	 *
	 * @dataProvider sql_random_data
	 */
	public function test_get_ads_uses_dbms_random_expression($sql_layer, $random_expression)
	{
		$db = $this->getMockBuilder('\phpbb\db\driver\driver_interface')->getMock();
		$db->expects(self::once())
			->method('get_sql_layer')
			->willReturn($sql_layer);
		$db->expects(self::once())
			->method('sql_in_set')
			->with('al.location_id', array('above_header'))
			->willReturn("al.location_id = 'above_header'");
		$db->expects(self::once())
			->method('sql_query')
			->with(self::callback(function ($sql) use ($random_expression)
			{
				return strpos($sql, 'ORDER BY al.location_id, (' . $random_expression . ' * a.ad_priority) DESC') !== false;
			}))
			->willReturn('result');
		$db->expects(self::once())
			->method('sql_fetchrowset')
			->with('result')
			->willReturn(array());
		$db->expects(self::once())
			->method('sql_freeresult')
			->with('result');

		$manager = new \phpbb\ads\ad\manager(
			$db,
			$this->user,
			$this->ads_table,
			$this->ad_locations_table,
			$this->ad_group_table
		);

		self::assertSame(array(), $manager->get_ads(array('above_header'), array()));
	}

	/**
	 * Test get_ads() priority feature is working as expected.
	 * Higher priority ads should occur more frequently in the results.
	 */
	public function test_get_ads_priority()
	{
		$counter = [
			1 => 0, // Ad #1 has high priority
			4 => 0, // Ad #4 has low priority
			5 => 0, // Ad #5 has medium priority
		];

		$manager = $this->get_manager();

		for ($i = 0; $i < 100; $i++)
		{
			$test = $manager->get_ads(array('above_header'), array());

			$ad = end($test);

			$counter[$ad['ad_id']]++;
		}

		self::assertTrue($counter[1] > $counter[5]);
		self::assertTrue($counter[5] > $counter[4]);
	}
}
