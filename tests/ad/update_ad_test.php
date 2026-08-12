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
	public static function update_ad_data(): array
	{
		return array(
			array(
				1,
				array(
					'ad_name'	=> 'Primary ad Updated',
					'ad_groups'	=> [],
				),
			),
			array(
				1,
				array(
					'ad_name'	=> 'Primary ad Updated #2',
					'ad_note'	=> 'Note Updated',
					'ad_groups'	=> ['2', '3'],
				),
			),
			array(
				0,
				array(
					'ad_name'	=> '',
					'ad_groups'	=> [],
				),
			),
			array(
				9999,
				array(
					'ad_name'	=> '',
					'ad_groups'	=> [],
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

		$updated = $manager->update_ad($ad_id, $data);

		$ad = $manager->get_ad($ad_id);
		unset($data['ad_groups']);
		foreach ($data as $key => $value)
		{
			if ($updated)
			{
				self::assertEquals($value, $ad[$key]);
				continue;
			}
			self::assertEmpty($ad);
		}
	}

	/**
	 * Test partial scalar updates preserve existing group restrictions.
	 */
	public function test_partial_update_preserves_ad_groups()
	{
		$manager = $this->get_manager();
		$updated = $manager->update_ad(1, array(
			'ad_name' => 'Primary ad',
			'ad_groups' => array(2),
		));
		self::assertEquals(1, $updated);

		$manager->update_ad(1, array('ad_enabled' => 0));

		$groups = $manager->load_groups(1);
		$selected_groups = array_filter($groups, static function ($group) {
			return !empty($group['group_selected']);
		});

		self::assertCount(1, $selected_groups);
		self::assertEquals(2, reset($selected_groups)['group_id']);

		$manager->update_ad(1, array(
			'ad_name' => 'Primary ad',
			'ad_groups' => array(),
		));
		$groups = $manager->load_groups(1);
		$selected_groups = array_filter($groups, static function ($group) {
			return !empty($group['group_selected']);
		});

		self::assertEmpty($selected_groups);
	}
}
