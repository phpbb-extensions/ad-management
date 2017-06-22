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

class get_ads_test extends \phpbb_database_test_case
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string */
	protected $ads_table;

	/** @var string */
	protected $ad_locations_table;

	/**
	 * {@inheritDoc}
	 */
	static protected function setup_extensions()
	{
		return array('phpbb/ads');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/../fixtures/ad.xml');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setUp()
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->ads_table = 'phpbb_ads';
		$this->ad_locations_table = 'phpbb_ad_locations';
	}

	/**
	 * Returns fresh new ad manager.
	 *
	 * @return    \phpbb\ads\ad\manager    Ad manager
	 */
	public function get_manager()
	{
		return new \phpbb\ads\ad\manager($this->db, $this->ads_table, $this->ad_locations_table);
	}

	public function test_get_ads_priority()
	{
		$low = $mid = $high = 0;

		$manager = $this->get_manager();

		for ($i = 0; $i < 100; $i++)
		{
			$test = $manager->get_ads(array('above_header'));

			$ad = end($test);

			if ($ad['ad_code'] === 'adscode')
			{
				$high++;
			}
			else if ($ad['ad_code'] === 'adscodemid')
			{
				$mid++;
			}
			else if ($ad['ad_code'] === 'adscodelow')
			{
				$low++;
			}
		}

		$this->assertTrue($high > $mid);
		$this->assertTrue($mid > $low);
	}
}
