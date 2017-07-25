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
		global $db, $phpbb_dispatcher;

		if (!function_exists('user_get_id_name'))
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);

		// Load/Mock classes required by the controller class
		$this->user = new \phpbb\user($lang, '\phpbb\datetime');
		$this->user->timezone = new \DateTimeZone('UTC');
		$this->template = $this->getMock('\phpbb\template\template');
		$this->log = $this->getMockBuilder('\phpbb\log\log')
			->disableOriginalConstructor()
			->getMock();
		$this->location_manager = $this->getMockBuilder('\phpbb\ads\location\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;

		$db = $this->new_dbal();
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
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
			$this->template,
			$this->log,
			$this->location_manager,
			$this->root_path,
			$this->php_ext
		);

		return $helper;
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
	 * Data for test_assign_form_data
	 *
	 * @return array Array of test data
	 */
	public function assign_form_data_data()
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
			), '', ''),
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
			), '', ''),
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
			), '1970-01-01', ''),
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
			), '1970-01-01', ''),
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
			), '2017-01-01', 'admin'),
		);
	}

	/**
	 * Test assign_form_data()
	 *
	 * @dataProvider assign_form_data_data
	 */
	public function test_assign_form_data($data, $end_date, $owner)
	{
		$helper = $this->get_helper();

		$this->template->expects($this->once())
			->method('assign_vars')
			->with(array(
				'AD_NAME'         => $data['ad_name'],
				'AD_NOTE'         => $data['ad_note'],
				'AD_CODE'         => $data['ad_code'],
				'AD_ENABLED'      => $data['ad_enabled'],
				'AD_END_DATE'     => $end_date,
				'AD_PRIORITY'     => $data['ad_priority'],
				'AD_VIEWS_LIMIT'  => $data['ad_views_limit'],
				'AD_CLICKS_LIMIT' => $data['ad_clicks_limit'],
				'AD_OWNER'        => $owner,
			));

		$helper->assign_form_data($data);
	}

	/**
	 * Data for test_assign_errors
	 *
	 * @return array Array of test data
	 */
	public function assign_errors_data()
	{
		return array(
			array(array(), false, ''),
			array(array('ERROR_1'), true, 'ERROR_1'),
			array(array('ERROR_1', 'ERROR_2'), true, 'ERROR_1<br />ERROR_2'),
		);
	}

	/**
	 * Test assign_errors()
	 *
	 * @dataProvider assign_errors_data
	 */
	public function test_assign_errors($errors, $s_error, $error_msg)
	{
		$helper = $this->get_helper();

		$this->template->expects($this->once())
			->method('assign_vars')
			->with(array(
				'S_ERROR'   => $s_error,
				'ERROR_MSG' => $error_msg,
			));

		$helper->assign_errors($errors);
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
}
