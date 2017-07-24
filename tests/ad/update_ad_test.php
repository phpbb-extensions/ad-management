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

class update_ad_test extends ad_base
{
	/**
	 * Test data provider for test_update_ad()
	 *
	 * @return array Array of test data
	 */
	public function update_ad_data()
	{
		return array(
			array(
				1,
				array(
					'ad_name'	=> 'Primary ad Updated',
				),
			),
			array(
				1,
				array(
					'ad_name'	=> 'Primary ad Updated #2',
					'ad_note'	=> 'Note Updated',
				),
			),
			array(
				0,
				array(
					'ad_name'	=> '',
				),
			),
			array(
				9999,
				array(
					'ad_name'	=> '',
				),
			),
		);
	}

	/**
	 * Test update_ad() method
	 *
	 * @dataProvider update_ad_data
	 */
	public function test_update_ad($ad_id, $data)
	{
		$manager = $this->get_manager();

		$manager->update_ad($ad_id, $data);

		$ad = $manager->get_ad($ad_id);
		foreach ($data as $key => $value)
		{
			$this->assertEquals($value, $ad[$key]);
		}
	}
}
