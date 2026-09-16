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

class insert_ad_test extends ad_base
{
	/**
	 * Test data provider for test_insert_ad()
	 *
	 * @return array Array of test data
	 */
	public static function insert_ad_data(): array
	{
		return array(
			array(
				array(
					'ad_name'		=> 'Insert Ad Test #1',
					'ad_note'		=> '',
					'ad_code'		=> '',
					'ad_enabled'	=> 1,
					'ad_start_date'	=> 0,
					'ad_end_date'	=> 0,
					'ad_priority'	=> 5,
					'ad_groups'		=> [],
				),
				'Insert Ad Test #1',
			),
			array(
				array(
					'ad_name'		=> 'Insert Ad Test #2',
					'ad_note'		=> '',
					'ad_code'		=> '',
					'ad_enabled'	=> 1,
					'ad_start_date'	=> 0,
					'ad_end_date'	=> 0,
					'ad_priority'	=> 5,
					'random_column'	=> 'Random Value',
					'ad_groups'		=> ['2', '3'],
				),
				'Insert Ad Test #2',
			),
			array(
				array(
					'ad_name'		=> 'Ad 😀 中文 Кириллица',
					'ad_note'		=> 'Note 📝 日本語 Ελληνικά',
					'ad_code'		=> '',
					'ad_enabled'	=> 1,
					'ad_start_date'	=> 0,
					'ad_end_date'	=> 0,
					'ad_priority'	=> 5,
					'ad_groups'		=> [],
				),
				'Ad 😀 中文 Кириллица',
				'Note 📝 日本語 Ελληνικά',
			),
		);
	}

	/**
	 * Test insert_ad() method
	 *
	 * @dataProvider insert_ad_data
	 */
	public function test_insert_ad($data, $expected_name, $expected_note = '')
	{
		$manager = $this->get_manager();
		$encode = strpos($this->db->get_sql_layer(), 'mssql') === 0 ? 'utf8_encode_ncr' : 'utf8_encode_ucr';

		$ad_id = $manager->insert_ad($data);

		self::assertGreaterThan(6, $ad_id);

		$new_ad = $manager->get_ad($ad_id);

		self::assertSame($encode($expected_name), $new_ad['ad_name']);
		self::assertSame($encode($expected_note), $new_ad['ad_note']);
	}
}
