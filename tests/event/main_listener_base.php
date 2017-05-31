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
		global $phpbb_root_path, $phpEx;
		global $phpbb_dispatcher;

		parent::setUp();

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$this->ads_table = 'phpbb_ads';
		$this->ad_locations_table = 'phpbb_ad_locations';

		// Load/Mock classes required by the listener class
		$this->request = $this->getMock('\phpbb\request\request');
		$this->template = $this->getMock('\phpbb\template\template');
		$this->user = new \phpbb\user($lang, '\phpbb\datetime');
		$this->config = new \phpbb\config\config(array());
		$this->root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
		$this->manager = new \phpbb\admanagement\ad\manager($this->new_dbal(), $this->ads_table, $this->ad_locations_table);
		$this->location_manager = new \phpbb\admanagement\location\manager(array(
			new \phpbb\admanagement\location\type\above_header($this->user),
			new \phpbb\admanagement\location\type\below_header($this->user),
		));

		// globals
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
	}

	/**
	* Get the event listener
	*
	* @return \phpbb\admanagement\event\main_listener
	*/
	protected function get_listener()
	{
		return new \phpbb\admanagement\event\main_listener(
			$this->request,
			$this->template,
			$this->user,
			$this->config,
			$this->manager,
			$this->location_manager,
			$this->root_path,
			$this->php_ext
		);
	}
}
