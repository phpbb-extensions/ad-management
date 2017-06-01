<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\admanagement\tests\event;

class main_listener_base extends \phpbb_database_test_case
{
	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\request\request */
	protected $request;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\template\template */
	protected $template;

	/** @var string ads_table */
	protected $ads_table;

	/**
	* {@intheritDoc}
	*/
	static protected function setup_extensions()
	{
		return array('phpbb/admanagement');
	}

	/**
	* {@intheritDoc}
	*/
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/../fixtures/ad.xml');
	}

	/**
	* {@intheritDoc}
	*/
	public function setUp()
	{
		parent::setUp();

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$this->ads_table = 'phpbb_ads';
		$this->ad_locations_table = 'phpbb_ad_locations';

		// Load/Mock classes required by the listener class
		$this->template = $this->getMock('\phpbb\template\template');
		$this->manager = new \phpbb\admanagement\ad\manager($this->new_dbal(), $this->ads_table, $this->ad_locations_table);
		$this->location_manager = new \phpbb\admanagement\location\manager(array(
			new \phpbb\admanagement\location\type\above_header($user),
			new \phpbb\admanagement\location\type\below_header($user),
		));
	}

	/**
	* Get the event listener
	*
	* @return \phpbb\admanagement\event\main_listener
	*/
	protected function get_listener()
	{
		return new \phpbb\admanagement\event\main_listener(
			$this->template,
			$this->manager,
			$this->location_manager
		);
	}
}
