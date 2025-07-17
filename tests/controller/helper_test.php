<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\controller;

use DateTimeZone;
use phpbb\ads\ad\manager;
use phpbb\ads\controller\helper;
use phpbb\ads\location\manager as location_manager;
use phpbb\avatar\helper as avatar_helper;
use phpbb\config\config;
use phpbb\group\helper as group_helper;
use phpbb\language\language;
use phpbb\language\language_file_loader;
use phpbb\log\log;
use phpbb\path_helper;
use phpbb\symfony_request;
use phpbb\template\template;
use phpbb\user;
use phpbb\user_loader;
use phpbb_database_test_case;
use phpbb_mock_event_dispatcher;
use phpbb_mock_request;
use PHPUnit\DbUnit\DataSet\DefaultDataSet;
use PHPUnit\DbUnit\DataSet\XmlDataSet;
use PHPUnit\Framework\MockObject\MockObject;
use phpbb\datetime;
use phpbb\auth\auth;
use phpbb\cache\service;
use phpbb\request\request;

class helper_test extends phpbb_database_test_case
{
	/** @var user */
	protected user $user;

	/** @var user_loader */
	protected user_loader $user_loader;

	/** @var MockObject|language */
	protected language|MockObject $language;

	/** @var MockObject|template */
	protected template|MockObject $template;

	/** @var MockObject|log */
	protected log|MockObject $log;

	/** @var MockObject|manager */
	protected manager|MockObject $manager;

	/** @var MockObject|location_manager */
	protected MockObject|location_manager $location_manager;

	/** @var group_helper */
	protected group_helper $group_helper;

	/** @var string */
	protected string $root_path;

	/** @var string */
	protected string $php_ext;

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

		global $db, $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		// Global variables
		$db = $this->new_dbal();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		// Load/Mock classes required by the controller class
		$this->language = new language(new language_file_loader($phpbb_root_path, $phpEx));
		$this->user = new user($this->language, datetime::class);
		$this->user->timezone = new DateTimeZone('UTC');
		$avatar_helper = $this->getMockBuilder(avatar_helper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->user_loader = new user_loader($avatar_helper, $db, $phpbb_root_path, $phpEx, 'phpbb_users');
		$this->template = $this->getMockBuilder(template::class)
			->disableOriginalConstructor()
			->getMock();
		$this->log = $this->getMockBuilder(log::class)
			->disableOriginalConstructor()
			->getMock();
		$this->manager = $this->getMockBuilder(manager::class)
			->disableOriginalConstructor()
			->getMock();
		$this->location_manager = $this->getMockBuilder(location_manager::class)
			->disableOriginalConstructor()
			->getMock();
		$avatar_helper = $this->getMockBuilder(avatar_helper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->group_helper = new group_helper(
			$this->getMockBuilder(auth::class)->getMock(),
			$avatar_helper,
			$this->getMockBuilder(service::class)->disableOriginalConstructor()->getMock(),
			new config([]),
			$this->language,
			$phpbb_dispatcher,
			new path_helper(
				new symfony_request(
					new phpbb_mock_request()
				),
				$this->getMockBuilder(request::class)->getMock(),
				$phpbb_root_path,
				$phpEx
			),
			$this->user
		);

		$this->root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
	}

	/**
	 * Returns fresh new helper.
	 *
	 * @return	helper	Admin helper
	 */
	public function get_helper(): helper
	{
		return new helper(
			$this->user,
			$this->user_loader,
			$this->language,
			$this->template,
			$this->log,
			$this->manager,
			$this->location_manager,
			$this->group_helper,
			$this->root_path,
			$this->php_ext
		);
	}

	/**
	 * Data for test_assign_data
	 *
	 * @return array Array of test data
	 */
	public function assign_data_data(): array
	{
		return array(
			array(array(
					  'ad_name'			=> 'Ad Name #1',
					  'ad_note'			=> 'Ad Note #1',
					  'ad_code'			=> 'Ad Code #1',
					  'ad_enabled'		=> 1,
					  'ad_start_date'	=> '',
					  'ad_end_date'		=> '',
					  'ad_priority'		=> 5,
					  'ad_content_only'	=> 0,
					  'ad_views_limit'	=> 0,
					  'ad_clicks_limit'	=> 0,
					  'ad_owner'		=> 0,
					  'ad_centering'	=> false,
					  'ad_locations'	=> [],
				  ), '', array('AD_PRIORITY_INVALID'), true, 'AD_PRIORITY_INVALID'),
			array(array(
					  'ad_name'			=> 'Ad Name #1',
					  'ad_note'			=> 'Ad Note #1',
					  'ad_code'			=> 'Ad Code #1',
					  'ad_enabled'		=> 1,
					  'ad_start_date'	=> '0',
					  'ad_end_date'		=> '0',
					  'ad_priority'		=> 5,
					  'ad_content_only'	=> 0,
					  'ad_views_limit'	=> 0,
					  'ad_clicks_limit'	=> 0,
					  'ad_owner'		=> 0,
					  'ad_centering'	=> 0,
					  'ad_locations'	=> [],
				  ), '', array('AD_PRIORITY_INVALID', 'AD_NAME_REQUIRED'), true, 'AD_PRIORITY_INVALID<br>AD_NAME_REQUIRED'),
			array(array(
					  'ad_name'			=> 'Ad Name #2',
					  'ad_note'			=> 'Ad Note #2',
					  'ad_code'			=> 'Ad Code #2',
					  'ad_enabled'		=> 0,
					  'ad_start_date'	=> '1',
					  'ad_end_date'		=> '1',
					  'ad_priority'		=> 5,
					  'ad_content_only'	=> 0,
					  'ad_views_limit'	=> 0,
					  'ad_clicks_limit'	=> 0,
					  'ad_owner'		=> 99,
					  'ad_centering'	=> 0,
					  'ad_locations'	=> [],
				  ), 'Anonymous', array(), false, ''),
			array(array(
					  'ad_name'			=> 'Ad Name #2',
					  'ad_note'			=> 'Ad Note #2',
					  'ad_code'			=> 'Ad Code #2',
					  'ad_enabled'		=> 0,
					  'ad_start_date'	=> '1970-01-01',
					  'ad_end_date'		=> '1970-01-01',
					  'ad_priority'		=> 5,
					  'ad_content_only'	=> 0,
					  'ad_views_limit'	=> 0,
					  'ad_clicks_limit'	=> 0,
					  'ad_owner'		=> 99,
					  'ad_centering'	=> 0,
					  'ad_locations'	=> [],
				  ), 'Anonymous', array(), false, ''),
			array(array(
					  'ad_name'			=> 'Ad Name #3',
					  'ad_note'			=> 'Ad Note #3',
					  'ad_code'			=> 'Ad Code #3',
					  'ad_enabled'		=> 0,
					  'ad_start_date'	=> '1483228800',
					  'ad_end_date'		=> '1483228800',
					  'ad_priority'		=> 5,
					  'ad_content_only'	=> 0,
					  'ad_views_limit'	=> 0,
					  'ad_clicks_limit'	=> 0,
					  'ad_owner'		=> 2,
					  'ad_centering'	=> 0,
					  'ad_locations'	=> [],
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

		$this->location_manager->expects(self::once())
			->method('get_all_locations')
			->willReturn(array());

		$this->manager->expects(self::once())
			->method('load_groups')
			->willReturn(array());

		$this->template->expects(self::once())
			->method('assign_vars')
			->with(array(
				'S_ERROR'   => $s_errors,
				'ERROR_MSG' => $error_msg,

				'AD_NAME'         => $data['ad_name'],
				'AD_NOTE'         => $data['ad_note'],
				'AD_CODE'         => $data['ad_code'],
				'AD_ENABLED'      => $data['ad_enabled'],
				'AD_START_DATE'   => $data['ad_start_date'],
				'AD_END_DATE'     => $data['ad_end_date'],
				'AD_PRIORITY'     => $data['ad_priority'],
				'AD_CONTENT_ONLY' => $data['ad_content_only'],
				'AD_VIEWS_LIMIT'  => $data['ad_views_limit'],
				'AD_CLICKS_LIMIT' => $data['ad_clicks_limit'],
				'AD_OWNER'        => $owner,
				'AD_CENTERING'    => $data['ad_centering'],
			));

		$helper->assign_data($data, $errors);
	}

	/**
	 * Data for test_assign_locations
	 *
	 * @return array Array of test data
	 */
	public function assign_locations_data(): array
	{
		return array(
			array(array()),
			array(array('top_of_page_1')),
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

		$this->location_manager->expects(self::once())
			->method('get_all_locations')
			->willReturn(array(
				'CAT_TOP_OF_PAGE'	=> array(
					'top_of_page_1'	=> array(
						'name'	=> 'Location #1',
						'desc'	=> 'Location #1 desc',
					),
				),
				'CAT_BOTTOM_OF_PAGE'	=> array(
					'bottom_of_page_1'	=> array(
						'name'	=> 'Location #2',
						'desc'	=> 'Location #2 desc',
					),
				),
			));

		$expectations = [
			['ad_locations', ['CATEGORY_NAME' => 'CAT_TOP_OF_PAGE']],
			['ad_locations', [
				'LOCATION_ID' => 'top_of_page_1',
				'LOCATION_DESC' => 'Location #1 desc',
				'LOCATION_NAME' => 'Location #1',
				'S_SELECTED' => $ad_locations && in_array('top_of_page_1', $ad_locations),
			]],
			['ad_locations', ['CATEGORY_NAME' => 'CAT_BOTTOM_OF_PAGE']],
			['ad_locations', [
				'LOCATION_ID' => 'bottom_of_page_1',
				'LOCATION_DESC' => 'Location #2 desc',
				'LOCATION_NAME' => 'Location #2',
				'S_SELECTED' => $ad_locations && in_array('bottom_of_page_1', $ad_locations),
			]]
		];
		$this->template->expects(self::exactly(4))
			->method('assign_block_vars')
			->willReturnCallback(function($arg1, $arg2) use (&$expectations) {
				$expectation = array_shift($expectations);
				self::assertEquals($expectation[0], $arg1);
				self::assertEquals($expectation[1], $arg2);
			});

		$helper->assign_locations($ad_locations);
	}

	/**
	 * Test assign_groups()
	 */
	public function test_assign_groups()
	{
		$helper = $this->get_helper();

		$this->manager->expects(self::once())
			->method('load_groups')
			->willReturn(array(
				array(
					'group_id'			=> 1,
					'group_name'		=> 'ADMINISTRATORS',
					'group_selected'	=> true,
				),
				array(
					'group_id'			=> 2,
					'group_name'		=> 'Custom group name',
					'group_selected'	=> false,
				),
			));

		$expectations = [
			['groups', ['ID' => 1, 'NAME' => 'Administrators', 'S_SELECTED' => true]],
			['groups', ['ID' => 2, 'NAME' => 'Custom group name', 'S_SELECTED' => false]]
		];
		$this->template->expects(self::exactly(2))
			->method('assign_block_vars')
			->willReturnCallback(function($arg1, $arg2) use (&$expectations) {
				$expectation = array_shift($expectations);
				self::assertEquals($expectation[0], $arg1);
				self::assertEquals($expectation[1], $arg2);
			});

		$helper->assign_groups();
	}

	/**
	 * Test log()
	 */
	public function test_log()
	{
		$this->user->data['user_id'] = 1;
		$this->user->ip = '0.0.0.0';
		$helper = $this->get_helper();

		$this->log->expects(self::once())
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
		self::assertEquals("{$this->root_path}memberlist.$this->php_ext?mode=searchuser&amp;form=acp_admanagement_add&amp;field=ad_owner&amp;select_single=true", $result);
	}

	/**
	 * Data for test_is_expired
	 *
	 * @return array Array of test data
	 */
	public function is_expired_data(): array
	{
		return array(
			array(array(
				'ad_start_date'		=> '1',
				'ad_end_date'		=> '1',
				'ad_views_limit'	=> '',
				'ad_views'			=> '',
				'ad_clicks_limit'	=> '',
				'ad_clicks'			=> '',
			), true),
			array(array(
				'ad_start_date'		=> '0',
				'ad_end_date'		=> '0',
				'ad_views_limit'	=> '1',
				'ad_views'			=> '2',
				'ad_clicks_limit'	=> '',
				'ad_clicks'			=> '',
			), true),
			array(array(
				'ad_start_date'		=> '0',
				'ad_end_date'		=> '0',
				'ad_views_limit'	=> '0',
				'ad_views'			=> '0',
				'ad_clicks_limit'	=> '1',
				'ad_clicks'			=> '2',
			), true),
			array(array(
				'ad_start_date'		=> '9999999999',
				'ad_end_date'		=> '9999999999',
				'ad_views_limit'	=> '0',
				'ad_views'			=> '0',
				'ad_clicks_limit'	=> '0',
				'ad_clicks'			=> '0',
			), false),
			array(array(
				'ad_start_date'		=> '0',
				'ad_end_date'		=> '0',
				'ad_views_limit'	=> '0',
				'ad_views'			=> '1',
				'ad_clicks_limit'	=> '0',
				'ad_clicks'			=> '0',
			), false),
			array(array(
				'ad_start_date'		=> '0',
				'ad_end_date'		=> '0',
				'ad_views_limit'	=> '0',
				'ad_views'			=> '0',
				'ad_clicks_limit'	=> '0',
				'ad_clicks'			=> '1',
			), false),
			array(array(
				'ad_start_date'		=> '0',
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
		self::assertEquals($expected, $result);
	}
}
