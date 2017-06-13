<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\event;

class main_listener_base extends \phpbb_database_test_case
{
	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\config\db_text */
	protected $config_text;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string ads_table */
	protected $ads_table;

	/** @var \phpbb\ads\ad\manager */
	protected $manager;

	/** @var \phpbb\ads\location\manager */
	protected $location_manager;

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

		global $phpbb_root_path, $phpEx;

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$this->ads_table = 'phpbb_ads';
		$this->ad_locations_table = 'phpbb_ad_locations';
		// Location types
		$locations = array(
			'above_footer',
			'above_header',
			'after_first_post',
			'after_not_first_post',
			'after_posts',
			'after_profile',
			'before_posts',
			'before_profile',
			'below_footer',
			'below_header'
		);
		$location_types = array();
		foreach ($locations as $type)
		{
			$class = "\\phpbb\\ads\\location\\type\\$type";
			$location_types['phpbb.ads.location.type.' . $type] = new $class($user);
		}

		// Load/Mock classes required by the listener class
		$this->template = $this->getMock('\phpbb\template\template');
		$this->user = $this->getMockBuilder('\phpbb\user')
			->disableOriginalConstructor()
			->getMock();
		$this->config_text = $this->getMockBuilder('\phpbb\config\db_text')
			->disableOriginalConstructor()
			->getMock();
		$this->manager = new \phpbb\ads\ad\manager($this->new_dbal(), $this->ads_table, $this->ad_locations_table);
		$this->location_manager = new \phpbb\ads\location\manager($location_types);
	}

	/**
	* Get the event listener
	*
	* @return \phpbb\ads\event\main_listener
	*/
	protected function get_listener()
	{
		return new \phpbb\ads\event\main_listener(
			$this->template,
			$this->user,
			$this->config_text,
			$this->manager,
			$this->location_manager
		);
	}
}
