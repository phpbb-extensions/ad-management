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

use phpbb\ads\ad\manager;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb_database_test_case;
use PHPUnit\DbUnit\DataSet\DefaultDataSet;
use PHPUnit\DbUnit\DataSet\XmlDataSet;

class ad_base extends phpbb_database_test_case
{
	/** @var driver_interface */
	protected driver_interface $db;

	/** @var config */
	protected config $config;

	/** @var string */
	protected string $ads_table;

	/** @var string */
	protected string $ad_locations_table;

	/** @var string */
	protected string $ad_group_table;

	/**
	 * {@inheritDoc}
	 */
	protected static function setup_extensions(): array
	{
		return array('phpbb/ads');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDataSet(): XmlDataSet|DefaultDataSet
	{
		return $this->createXMLDataSet(__DIR__ . '/../fixtures/ad.xml');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->config = new config(array());
		$this->ads_table = 'phpbb_ads';
		$this->ad_locations_table = 'phpbb_ad_locations';
		$this->ad_group_table = 'phpbb_ad_group';
	}

	/**
	 * Returns fresh new ad manager.
	 *
	 * @return    manager    Ad manager
	 */
	public function get_manager(): manager
	{
		return new manager($this->db, $this->config, $this->ads_table, $this->ad_locations_table, $this->ad_group_table);
	}
}
