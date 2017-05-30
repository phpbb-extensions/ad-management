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

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\db\driver\driver_interface */
	protected $db;

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

		// Load/Mock classes required by the listener class
        $this->request = $this->getMock('\phpbb\request\request');
		$this->db = $this->new_dbal();
		$this->template = $this->getMock('\phpbb\template\template');
		$this->ads_table = 'phpbb_ads';
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
			$this->db,
			$this->template,
			$this->ads_table
		);
	}
}
