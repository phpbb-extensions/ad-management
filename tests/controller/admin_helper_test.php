<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\controller;

class admin_helper_test extends \phpbb_database_test_case
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\language\language */
	protected $language;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\template\template */
	protected $template;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\log\log */
	protected $log;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\ads\location\manager */
	protected $location_manager;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * {@inheritDoc}
	 */
	protected static function setup_extensions()
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

		global $db, $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		// Global variables
		$db = $this->new_dbal();
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		// Load/Mock classes required by the controller class
		$this->language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$this->user = new \phpbb\user($this->language, '\phpbb\datetime');
		$this->user->timezone = new \DateTimeZone('UTC');
		$this->user_loader = new \phpbb\user_loader($db, $phpbb_root_path, $phpEx, 'phpbb_users');
		$this->template = $this->getMock('\phpbb\template\template');
		$this->log = $this->getMockBuilder('\phpbb\log\log')
			->disableOriginalConstructor()
			->getMock();
		$this->location_manager = $this->getMockBuilder('\phpbb\ads\location\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
	}

	/**
	 * Returns fresh new helper.
	 *
	 * @return	\phpbb\ads\controller\admin_helper	Admin helper
	 */
	public function get_helper()
	{
		$helper = new \phpbb\ads\controller\admin_helper(
			$this->user,
			$this->user_loader,
			$this->language,
			$this->template,
			$this->log,
			$this->location_manager,
			$this->root_path,
			$this->php_ext
		);

		return $helper;
	}

	/**
	 * Data for test_assign_data
	 *
	 * @return array Array of test data
	 */
	public function assign_data_data()
	{
		return array(
			array(array(
					  'ad_name'			=> 'Ad Name #1',
					  'ad_note'			=> 'Ad Note #1',
					  'ad_code'			=> 'Ad Code #1',
					  'ad_enabled'		=> '1',
					  'ad_end_date'		=> '',
					  'ad_priority'		=> '5',
					  'ad_views_limit'	=> '0',
					  'ad_clicks_limit'	=> '0',
					  'ad_owner'			=> '0',
				  ), '', array('AD_PRIORITY_INVALID'), true, 'AD_PRIORITY_INVALID'),
			array(array(
					  'ad_name'			=> 'Ad Name #1',
					  'ad_note'			=> 'Ad Note #1',
					  'ad_code'			=> 'Ad Code #1',
					  'ad_enabled'		=> '1',
					  'ad_end_date'		=> '0',
					  'ad_priority'		=> '5',
					  'ad_views_limit'	=> '0',
					  'ad_clicks_limit'	=> '0',
					  'ad_owner'			=> '0',
				  ), '', array('AD_PRIORITY_INVALID', 'AD_NAME_REQUIRED'), true, 'AD_PRIORITY_INVALID<br />AD_NAME_REQUIRED'),
			array(array(
					  'ad_name'			=> 'Ad Name #2',
					  'ad_note'			=> 'Ad Note #2',
					  'ad_code'			=> 'Ad Code #2',
					  'ad_enabled'		=> '0',
					  'ad_end_date'		=> '1',
					  'ad_priority'		=> '5',
					  'ad_views_limit'	=> '0',
					  'ad_clicks_limit'	=> '0',
					  'ad_owner'			=> '99',
				  ), 'Anonymous', array(), false, ''),
			array(array(
					  'ad_name'			=> 'Ad Name #2',
					  'ad_note'			=> 'Ad Note #2',
					  'ad_code'			=> 'Ad Code #2',
					  'ad_enabled'		=> '0',
					  'ad_end_date'		=> '1970-01-01',
					  'ad_priority'		=> '5',
					  'ad_views_limit'	=> '0',
					  'ad_clicks_limit'	=> '0',
					  'ad_owner'			=> '99',
				  ), 'Anonymous', array(), false, ''),
			array(array(
					  'ad_name'			=> 'Ad Name #3',
					  'ad_note'			=> 'Ad Note #3',
					  'ad_code'			=> 'Ad Code #3',
					  'ad_enabled'		=> '0',
					  'ad_end_date'		=> '1483228800',
					  'ad_priority'		=> '5',
					  'ad_views_limit'	=> '0',
					  'ad_clicks_limit'	=> '0',
					  'ad_owner'			=> '2',
				  ), 'admin', array(), false, ''),
		);
	}

	/**
	 * Test assign_data()
	 *
	 * @dataProvider assign_data_data
	 */
	public function test_assign_data($data, $owner, $errors, $s_errors, $error_msg)
	{
		$helper = $this->get_helper();

		$this->location_manager->expects($this->once())
			->method('get_all_locations')
			->willReturn(array());

		$this->template->expects($this->once())
			->method('assign_vars')
			->with(array(
				'S_ERROR'   => $s_errors,
				'ERROR_MSG' => $error_msg,

				'AD_NAME'         => $data['ad_name'],
				'AD_NOTE'         => $data['ad_note'],
				'AD_CODE'         => $data['ad_code'],
				'AD_ENABLED'      => $data['ad_enabled'],
				'AD_END_DATE'     => $data['ad_end_date'],
				'AD_PRIORITY'     => $data['ad_priority'],
				'AD_VIEWS_LIMIT'  => $data['ad_views_limit'],
				'AD_CLICKS_LIMIT' => $data['ad_clicks_limit'],
				'AD_OWNER'        => $owner,
			));

		$helper->assign_data($data, $errors);
	}

	/**
	 * Data for test_assign_locations
	 *
	 * @return array Array of test data
	 */
	public function assign_locations_data()
	{
		return array(
			array(false),
			array(array(1)),
		);
	}

	/**
	 * Test assign_locations()
	 *
	 * @dataProvider assign_locations_data
	 */
	public function test_assign_locations($ad_locations)
	{
		$helper = $this->get_helper();

		$this->location_manager->expects($this->once())
			->method('get_all_locations')
			->willReturn(array(
				1	=> array(
					'name'	=> 'Location #1',
					'desc'	=> 'Location #1 desc',
				),
				2	=> array(
					'name'	=> 'Location #2',
					'desc'	=> 'Location #2 desc',
				),
			));

		$this->template->expects($this->at(0))
			->method('assign_block_vars')
			->with('ad_locations', array(
				'LOCATION_ID'   => 1,
				'LOCATION_DESC' => 'Location #1 desc',
				'LOCATION_NAME' => 'Location #1',
				'S_SELECTED'    => $ad_locations ? in_array(1, $ad_locations) : false,
			));

		$this->template->expects($this->at(1))
			->method('assign_block_vars')
			->with('ad_locations', array(
				'LOCATION_ID'   => 2,
				'LOCATION_DESC' => 'Location #2 desc',
				'LOCATION_NAME' => 'Location #2',
				'S_SELECTED'    => $ad_locations ? in_array(2, $ad_locations) : false,
			));

		$helper->assign_locations($ad_locations);
	}

	/**
	 * Test log()
	 */
	public function test_log()
	{
		$this->user->data['user_id'] = 1;
		$this->user->ip = '0.0.0.0';
		$helper = $this->get_helper();

		$this->log->expects($this->once())
			->method('add')
			->with('admin', 1, '0.0.0.0', 'ACP_PHPBB_ADS_DELETE_LOG', $this->anything(), array('Ad Name'));

		$helper->log('DELETE', 'Ad Name');
	}

	/**
	 * Test get_find_username_link()
	 */
	public function test_get_find_username_link()
	{
		$helper = $this->get_helper();
		$result = $helper->get_find_username_link();
		$this->assertEquals("{$this->root_path}memberlist.{$this->php_ext}?mode=searchuser&amp;form=acp_admanagement_add&amp;field=ad_owner&amp;select_single=true", $result);
	}

	/**
	 * Data for test_is_expired
	 *
	 * @return array Array of test data
	 */
	public function is_expired_data()
	{
		return array(
			array(array(
				'ad_end_date'		=> '1',
				'ad_views_limit'	=> '',
				'ad_views'			=> '',
				'ad_clicks_limit'	=> '',
				'ad_clicks'			=> '',
			), true),
			array(array(
				'ad_end_date'		=> '0',
				'ad_views_limit'	=> '1',
				'ad_views'			=> '2',
				'ad_clicks_limit'	=> '',
				'ad_clicks'			=> '',
			), true),
			array(array(
				'ad_end_date'		=> '0',
				'ad_views_limit'	=> '0',
				'ad_views'			=> '0',
				'ad_clicks_limit'	=> '1',
				'ad_clicks'			=> '2',
			), true),
			array(array(
				'ad_end_date'		=> '9999999999',
				'ad_views_limit'	=> '0',
				'ad_views'			=> '0',
				'ad_clicks_limit'	=> '0',
				'ad_clicks'			=> '0',
			), false),
			array(array(
				'ad_end_date'		=> '0',
				'ad_views_limit'	=> '0',
				'ad_views'			=> '1',
				'ad_clicks_limit'	=> '0',
				'ad_clicks'			=> '0',
			), false),
			array(array(
				'ad_end_date'		=> '0',
				'ad_views_limit'	=> '0',
				'ad_views'			=> '0',
				'ad_clicks_limit'	=> '0',
				'ad_clicks'			=> '1',
			), false),
			array(array(
				'ad_end_date'		=> '0',
				'ad_views_limit'	=> '0',
				'ad_views'			=> '0',
				'ad_clicks_limit'	=> '0',
				'ad_clicks'			=> '0',
			), false),
		);
	}

	/**
	 * Test is_expired()
	 *
	 * @dataProvider is_expired_data
	 */
	public function test_is_expired($row, $expected)
	{
		$helper = $this->get_helper();
		$result = $helper->is_expired($row);
		$this->assertEquals($expected, $result);
	}
}
