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

require_once __DIR__ . '/../../../../../includes/functions_acp.php';

class admin_controller_test extends \phpbb_database_test_case
{
	/** @var bool A return value for confirm_box() */
	public static $confirm = true;
	/** @var bool A return value for check_form_key() */
	public static $valid_form = true;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\request\request */
	protected $request;

	/** @var string */
	protected $ads_table;

	/** @var string */
	protected $ad_locations_table;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\ads\ad\manager */
	protected $manager;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\ads\location\manager */
	protected $location_manager;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\log\log */
	protected $log;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\config\db_text */
	protected $config_text;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\config\config */
	protected $config;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $ext_path;

	/** @var string */
	protected $u_action;

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
		global $phpbb_extension_manager, $phpbb_dispatcher, $template, $request, $config, $user;

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$this->lang = new \phpbb\language\language($lang_loader);

		// Load/Mock classes required by the controller class
		$this->db = $this->new_dbal();
		$this->template = $this->getMock('\phpbb\template\template');
		$this->user = new \phpbb\user($this->lang, '\phpbb\datetime');
		$this->user->timezone = new \DateTimeZone('UTC');
		$this->request = $this->getMock('\phpbb\request\request');
		$this->ads_table = 'phpbb_ads';
		$this->ad_locations_table = 'phpbb_ad_locations';
		$this->manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->location_manager = $this->getMockBuilder('\phpbb\ads\location\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->log = $this->getMockBuilder('\phpbb\log\log')
			->disableOriginalConstructor()
			->getMock();
		$this->config_text = $this->getMockBuilder('\phpbb\config\db_text')
			->disableOriginalConstructor()
			->getMock();
		$this->config = $this->getMockBuilder('\phpbb\config\config')
			->disableOriginalConstructor()
			->getMock();
		$this->php_ext = $phpEx;
		$this->ext_path = $phpbb_root_path . 'ext/phpbb/ads/';

		$this->u_action = $phpbb_root_path . 'adm/index.php?i=-phpbb-ads-acp-main_module&mode=manage';

		// globals
		$phpbb_extension_manager = new \phpbb_mock_extension_manager($phpbb_root_path);
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
		$template = $this->getMock('\phpbb\template\template');
		$request = new \phpbb_mock_request();
		$config = new \phpbb\config\config(array());
		$user = new \phpbb\user($this->lang, '\phpbb\datetime');
	}

	/**
	* Returns fresh new controller.
	*
	* @return	\phpbb\ads\controller\admin_controller	Admin controller
	*/
	public function get_controller()
	{
		$controller = new \phpbb\ads\controller\admin_controller(
			$this->template,
			$this->user,
			$this->request,
			$this->manager,
			$this->location_manager,
			$this->log,
			$this->config_text,
			$this->config,
			$this->php_ext,
			$this->ext_path
		);
		$controller->set_page_url($this->u_action);

		return $controller;
	}

	/**
	* Data for test_mode_manage
	*
	* @return array Array of test data
	*/
	public function data_mode_manage()
	{
		return array(
			array('add', 'action_add'),
			array('edit', 'action_edit'),
			array('enable', 'ad_enable'),
			array('disable', 'ad_enable'),
			array('delete', 'action_delete'),
			array('', 'list_ads'),
		);
	}

	/**
	* Test mode_manage()
	*
	* @dataProvider data_mode_manage
	*/
	public function test_mode_manage($action, $expected)
	{
		/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\ads\controller\admin_controller $controller */
		$controller = $this->getMockBuilder('\phpbb\ads\controller\admin_controller')
			->setMethods(array('action_add', 'action_edit', 'ad_enable', 'action_delete', 'list_ads'))
			->setConstructorArgs(array(
				$this->template,
				$this->user,
				$this->request,
				$this->manager,
				$this->location_manager,
				$this->log,
				$this->config_text,
				$this->config,
				$this->php_ext,
				$this->ext_path,
			))
			->getMock();

		$this->template->expects($this->once())
			->method('assign_var')
			->with('S_PHPBB_ADS', true);

		$this->request->expects($this->once())
			->method('variable')
			->willReturn($action);

		$controller->expects($this->once())
			->method($expected);

		$controller->mode_manage();
	}

	/**
	* Test mode_settings()
	*/
	public function test_mode_settings_no_submit()
	{
		$controller = $this->get_controller();

		$this->config_text->expects($this->once())
			->method('get')
			->with('phpbb_ads_hide_groups')
			->willReturn('[1,3]');

		$this->config['phpbb_ads_adblocker_message'] = '1';

		$this->manager->expects($this->once())
			->method('load_groups')
			->willReturn(array(
				array(
					'group_id'		=> 1,
					'group_name'	=> 'ADMINISTRATORS',
				),
				array(
					'group_id'		=> 2,
					'group_name'	=> 'Custom group name',
				),
			));

		$this->template->expects($this->exactly(2))
			->method('assign_block_vars')
			->withConsecutive(
				array(
					'groups',
					array(
						'ID'			=> '1',
						'NAME'			=> 'ADMINISTRATORS',
						'S_SELECTED'	=> true,
					),
				),
				array(
					'groups',
					array(
						'ID'			=> 2,
						'NAME'			=> 'Custom group name',
						'S_SELECTED'	=> false,
					),
				)
			);

		$this->template->expects($this->once())
			->method('assign_vars')
			->with(array(
				'U_ACTION'			=> $this->u_action,
				'ADBLOCKER_MESSAGE'	=> $this->config['phpbb_ads_adblocker_message'],
				'ENABLE_VIEWS'		=> $this->config['phpbb_ads_enable_views'],
				'ENABLE_CLICKS'		=> $this->config['phpbb_ads_enable_clicks'],
			));

		$controller->mode_settings();
	}

	/**
	* Data for test_mode_manage
	*
	* @return array Array of test data
	*/
	public function data_mode_settings()
	{
		return array(
			array(false, 0, array(0)),
			array(true, 1, array(3)),
		);
	}

	/**
	* Test mode_manage()
	*
	* @dataProvider data_mode_settings
	*/
	public function test_mode_settings_submit($valid_form, $adblocker_data, $hide_group_data)
	{
		self::$valid_form = $valid_form;

		$controller = $this->get_controller();

		$this->request->expects($this->once())
			->method('is_set_post')
			->with('submit')
			->willReturn(true);

		if ($valid_form)
		{
			$this->request->expects($this->at(1))
				->method('variable')
				->with('adblocker_message', 0)
				->willReturn($adblocker_data);

			$this->request->expects($this->at(2))
				->method('variable')
				->with('enable_views', 0)
				->willReturn(1);

			$this->request->expects($this->at(3))
				->method('variable')
				->with('enable_clicks', 0)
				->willReturn(1);

			$this->request->expects($this->at(4))
				->method('variable')
				->with('hide_groups', array(0))
				->willReturn($hide_group_data);

			$this->config->expects($this->at(0))
				->method('set')
				->with('phpbb_ads_adblocker_message', $adblocker_data);

			$this->config->expects($this->at(1))
				->method('set')
				->with('phpbb_ads_enable_views', 1);

			$this->config->expects($this->at(2))
				->method('set')
				->with('phpbb_ads_enable_clicks', 1);

			$this->config_text->expects($this->once())
				->method('set')
				->with('phpbb_ads_hide_groups', json_encode($hide_group_data));

			$this->setExpectedTriggerError(E_USER_NOTICE, 'ACP_AD_SETTINGS_SAVED');
		}
		else
		{
			$this->template->expects($this->at(1))
				->method('assign_vars')
				->with(array(
					'S_ERROR'		=> true,
					'ERROR_MSG'		=> 'The submitted form was invalid. Try submitting again.',
				));

			$this->config_text->expects($this->once())
				->method('get')
				->with('phpbb_ads_hide_groups')
				->willReturn('[1,3]');
			
			$this->manager->expects($this->once())
				->method('load_groups')
				->willReturn(array(
					array(
						'group_id'		=> 1,
						'group_name'	=> 'ADMINISTRATORS',
					),
					array(
						'group_id'		=> 2,
						'group_name'	=> 'Custom group name',
					),
				));
		}

		$controller->mode_settings();
	}

	/**
	* Test get_page_title() method
	*/
	public function test_get_page_title()
	{
		$controller = $this->get_controller();
		$this->assertEquals($controller->get_page_title(), $this->lang->lang('ACP_PHPBB_ADS_TITLE'));
	}

	/**
	* Test action_add() method without submitted data
	*/
	public function test_action_add_no_submit()
	{
		$controller = $this->get_controller();

		$this->request->expects($this->at(0))
			->method('is_set_post')
			->with('preview')
			->willReturn(false);

		$this->request->expects($this->at(1))
			->method('is_set_post')
			->with('submit')
			->willReturn(false);

		$this->location_manager->expects($this->once())
			->method('get_all_locations')
			->willReturn(array(
				'above_footer'	=> array(
					'name'	=> 'AD_FOOTER_HEADER',
					'desc'	=> 'AD_FOOTER_HEADER_DESC',
				),
				'above_header'	=> array(
					'name'	=> 'AD_ABOVE_HEADER',
					'desc'	=> 'AD_ABOVE_HEADER_DESC',
				),
			));

		$this->template->expects($this->any())
			->method('assign_block_vars');

		$this->template->expects($this->once())
			->method('assign_vars')
			->with(array(
				'S_ADD_AD'				=> true,
				'U_BACK'				=> $this->u_action,
				'U_ACTION'				=> "{$this->u_action}&amp;action=add",
				'PICKER_DATE_FORMAT'	=> $controller::DATE_FORMAT,
			));

		$controller->action_add();
	}

	/**
	* Test action_add() method's preview
	*/
	public function test_action_add_preview()
	{
		$controller = $this->get_controller();

		$this->request->expects($this->at(0))
			->method('is_set_post')
			->with('preview')
			->willReturn(true);

		$this->request->expects($this->at(1))
			->method('is_set_post')
			->with('submit')
			->willReturn(false);

		$this->location_manager->expects($this->once())
			->method('get_all_locations')
			->willReturn(array(
				'above_footer'	=> array(
					'name'	=> 'AD_FOOTER_HEADER',
					'desc'	=> 'AD_FOOTER_HEADER_DESC',
				),
				'above_header'	=> array(
					'name'	=> 'AD_ABOVE_HEADER',
					'desc'	=> 'AD_ABOVE_HEADER_DESC',
				),
			));

		$this->request->expects($this->any())
			->method('variable')
			->will($this->onConsecutiveCalls('AD NAME', '', '<!-- AD CODE SAMPLE -->', false, array(), ''));

		$this->template->expects($this->at(0))
				->method('assign_var')
				->with('PREVIEW', '<!-- AD CODE SAMPLE -->');

		$controller->action_add();
	}

	/**
	* Test data for the test_action_add_submit() function
	*
	* @return array Array of test data
	*/
	public function action_add_data()
	{
		return array(
			array('', true, 'AD_NAME_REQUIRED', true, '', 1, 0, 0),
			array(str_repeat('a', 256), true, 'AD_NAME_TOO_LONG', true, '', 1, 0, 0),
			array('Unit test advertisement', true, 'AD_END_DATE_INVALID', true, '2000-01-01', 1, 0, 0),
			array('Unit test advertisement', true, 'AD_END_DATE_INVALID', true, 'abcd', 1, 0, 0),
			array('Unit test advertisement', true, 'AD_PRIORITY_INVALID', true, '', 0, 0, 0),
			array('Unit test advertisement', true, 'AD_PRIORITY_INVALID', true, '', 11, 0, 0),
			array('Unit test advertisement', true, 'AD_VIEWS_LIMIT_INVALID', true, '', 5, -1, 0),
			array('Unit test advertisement', true, 'AD_CLICKS_LIMIT_INVALID', true, '', 5, 0, -1),
			array('Unit test advertisement', true, 'The submitted form was invalid. Try submitting again.', false, '', 1, 0, 0),
			array('Unit test advertisement', false, '', true, '2035-01-01', 1, 0, 0),
		);
	}

	/**
	* Test action_add() method with submitted data
	*
	* @dataProvider action_add_data
	*/
	public function test_action_add_submit($ad_name, $s_error, $error_msg, $valid_form, $end_date, $ad_priority, $ad_views_limit, $ad_clicks_limit)
	{
		self::$valid_form = $valid_form;

		$controller = $this->get_controller();

		$this->request->expects($this->at(0))
			->method('is_set_post')
			->with('preview')
			->willReturn(false);

		$this->request->expects($this->at(1))
			->method('is_set_post')
			->with('submit')
			->willReturn(true);

		$this->request->expects($this->any())
			->method('variable')
			->will($this->onConsecutiveCalls($ad_name, '', '', false, array('above_footer', 'below_footer'), $end_date, $ad_priority, $ad_views_limit, $ad_clicks_limit));

		$this->template->expects($this->any())
			->method('assign_block_vars');

		if ($s_error)
		{
			$this->location_manager->expects($this->once())
				->method('get_all_locations')
				->willReturn(array(
					'above_footer'	=> array(
						'name'	=> 'AD_FOOTER_HEADER',
						'desc'	=> 'AD_FOOTER_HEADER_DESC',
					),
					'above_header'	=> array(
						'name'	=> 'AD_ABOVE_HEADER',
						'desc'	=> 'AD_ABOVE_HEADER_DESC',
					),
				));

			$this->template->expects($this->at(2))
				->method('assign_vars')
				->with(array(
					'S_ERROR'			=> $s_error,
					'ERROR_MSG'			=> $error_msg,
					'AD_NAME'			=> $ad_name,
					'AD_NOTE'			=> '',
					'AD_CODE'			=> '',
					'AD_ENABLED'		=> false,
					'AD_END_DATE'		=> $end_date,
					'AD_PRIORITY'		=> $ad_priority,
					'AD_VIEWS_LIMIT'	=> $ad_views_limit,
					'AD_CLICKS_LIMIT'	=> $ad_clicks_limit,
				));
		}
		else
		{
			$this->setExpectedTriggerError(E_USER_NOTICE, 'ACP_AD_ADD_SUCCESS');
		}

		$controller->action_add();
	}

	/**
	* Test data for the test_action_edit_no_submit() function
	*
	* @return array Array of test data
	*/
	public function action_edit_no_submit_data()
	{
		return array(
			array(0),
			array(1),
		);
	}

	/**
	* Test action_edit() method without submitted data
	*
	* @dataProvider action_edit_no_submit_data
	*/
	public function test_action_edit_no_submit($ad_id)
	{
		$controller = $this->get_controller();

		$this->request->expects($this->once())
			->method('variable')
			->with('id', 0)
			->willReturn($ad_id);

		$this->request->expects($this->at(1))
			->method('is_set_post')
			->with('preview')
			->willReturn(false);

		$this->request->expects($this->at(2))
			->method('is_set_post')
			->with('submit')
			->willReturn(false);

		$this->template->expects($this->any())
			->method('assign_block_vars');

		$this->manager->expects($this->once())
			->method('get_ad')
			->willReturn(!$ad_id ? false : array(
				'ad_name'			=> 'Primary ad',
				'ad_note'			=> 'Ad description #1',
				'ad_code'			=> 'Ad Code #1',
				'ad_enabled'		=> '1',
				'ad_end_date'		=> '2051308800',
				'ad_priority'		=> '5',
				'ad_views_limit'	=> '0',
				'ad_clicks_limit'	=> '0',
			));

		if (!$ad_id)
		{
			$this->setExpectedTriggerError(E_USER_WARNING, 'ACP_AD_DOES_NOT_EXIST');
		}
		else
		{
			$this->manager->expects($this->once())
				->method('get_ad_locations')
				->willReturn(!$ad_id ? false : array(
					'above_footer',
					'above_header',
				));

			$this->location_manager->expects($this->once())
				->method('get_all_locations')
				->willReturn(array(
					'above_footer'	=> array(
						'name'	=> 'AD_FOOTER_HEADER',
						'desc'	=> 'AD_FOOTER_HEADER_DESC',
					),
					'above_header'	=> array(
						'name'	=> 'AD_ABOVE_HEADER',
						'desc'	=> 'AD_ABOVE_HEADER_DESC',
					),
				));

			$this->template->expects($this->at(0))
				->method('assign_vars')
				->with(array(
					'S_EDIT_AD'				=> true,
					'EDIT_ID'				=> $ad_id,
					'U_BACK'				=> $this->u_action,
					'U_ACTION'				=> "{$this->u_action}&amp;action=edit&amp;id=" . $ad_id,
					'PICKER_DATE_FORMAT'	=> $controller::DATE_FORMAT,
				));

			$this->template->expects($this->at(3))
				->method('assign_vars')
				->with(array(
					'S_ERROR'			=> false,
					'ERROR_MSG'			=> '',
					'AD_NAME'			=> 'Primary ad',
					'AD_NOTE'			=> 'Ad description #1',
					'AD_CODE'			=> 'Ad Code #1',
					'AD_ENABLED'		=> '1',
					'AD_END_DATE'		=> '2035-01-02',
					'AD_PRIORITY'		=> '5',
					'AD_VIEWS_LIMIT'	=> '0',
					'AD_CLICKS_LIMIT'	=> '0',
				));
		}

		$controller->action_edit();
	}

	/**
	* Test action_edit() method's preview
	*/
	public function test_action_edit_preview()
	{
		$controller = $this->get_controller();

		$this->request->expects($this->any())
			->method('variable')
			->will($this->onConsecutiveCalls(1, 'AD NAME', '', '<!-- AD CODE SAMPLE -->', false, array(), ''));

		$this->request->expects($this->at(1))
			->method('is_set_post')
			->with('preview')
			->willReturn(true);

		$this->request->expects($this->at(2))
			->method('is_set_post')
			->with('submit')
			->willReturn(false);

		$this->location_manager->expects($this->once())
			->method('get_all_locations')
			->willReturn(array(
				'above_footer'	=> array(
					'name'	=> 'AD_FOOTER_HEADER',
					'desc'	=> 'AD_FOOTER_HEADER_DESC',
				),
				'above_header'	=> array(
					'name'	=> 'AD_ABOVE_HEADER',
					'desc'	=> 'AD_ABOVE_HEADER_DESC',
				),
			));

		$this->template->expects($this->at(0))
				->method('assign_var')
				->with('PREVIEW', '<!-- AD CODE SAMPLE -->');

		$controller->action_edit();
	}

	/**
	* Test data for the test_action_edit_submit() function
	*
	* @return array Array of test data
	*/
	public function action_edit_data()
	{
		return array(
			array(0, 'Unit test advertisement', true, '', true, '', 1, 0, 0),
			array(1, '', true, 'AD_NAME_REQUIRED', true, '', 1, 0, 0),
			array(1, str_repeat('a', 256), true, 'AD_NAME_TOO_LONG', true, '', 1, 0, 0),
			array(1, 'Unit test advertisement', true, 'AD_END_DATE_INVALID', true, '2000-01-01', 1, 0, 0),
			array(1, 'Unit test advertisement', true, 'AD_END_DATE_INVALID', true, 'abcd', 1, 0, 0),
			array(1, 'Unit test advertisement', true, 'AD_PRIORITY_INVALID', true, '', 0, 0, 0),
			array(1, 'Unit test advertisement', true, 'AD_PRIORITY_INVALID', true, '', 11, 0, 0),
			array(1, 'Unit test advertisement', true, 'AD_VIEWS_LIMIT_INVALID', true, '', 5, -1, 0),
			array(1, 'Unit test advertisement', true, 'AD_CLICKS_LIMIT_INVALID', true, '', 5, 0, -1),
			array(1, 'Unit test advertisement', true, 'The submitted form was invalid. Try submitting again.', false, '', 1, 0, 0),
			array(1, 'Unit test advertisement', false, '', true, '2035-01-03', 1, 1, 1),
		);
	}

	/**
	* Test action_edit() method with submitted data
	*
	* @dataProvider action_edit_data
	*/
	public function test_action_edit_submit($ad_id, $ad_name, $s_error, $error_msg, $valid_form, $end_date, $ad_priority, $ad_views_limit, $ad_clicks_limit)
	{
		self::$valid_form = $valid_form;

		$controller = $this->get_controller();

		$this->request->expects($this->any())
			->method('variable')
			->will($this->onConsecutiveCalls($ad_id, $ad_name, '', '', false, array('after_posts', 'before_posts'), $end_date, $ad_priority, $ad_views_limit, $ad_clicks_limit));

		$this->request->expects($this->at(1))
			->method('is_set_post')
			->with('preview')
			->willReturn(false);

		$this->request->expects($this->at(2))
			->method('is_set_post')
			->with('submit')
			->willReturn(true);

		if ($ad_id)
		{
			if (!$s_error)
			{
				$this->manager->expects($this->once())
					->method('update_ad')
					->willReturn($ad_id ? true : false);

				$this->setExpectedTriggerError(E_USER_NOTICE, 'ACP_AD_EDIT_SUCCESS');
			}
			else
			{
				$this->location_manager->expects($this->once())
					->method('get_all_locations')
					->willReturn(array(
						'above_footer'	=> array(
							'name'	=> 'AD_FOOTER_HEADER',
							'desc'	=> 'AD_FOOTER_HEADER_DESC',
						),
						'above_header'	=> array(
							'name'	=> 'AD_ABOVE_HEADER',
							'desc'	=> 'AD_ABOVE_HEADER_DESC',
						),
					));

				$this->template->expects($this->at(3))
					->method('assign_vars')
					->with(array(
						'S_ERROR'			=> $s_error,
						'ERROR_MSG'			=> $error_msg,
						'AD_NAME'			=> $ad_name,
						'AD_NOTE'			=> '',
						'AD_CODE'			=> '',
						'AD_ENABLED'		=> false,
						'AD_END_DATE'		=> $end_date,
						'AD_PRIORITY'		=> $ad_priority,
						'AD_VIEWS_LIMIT'	=> $ad_views_limit,
						'AD_CLICKS_LIMIT'	=> $ad_clicks_limit,
					));
			}
		}
		else
		{
			$this->setExpectedTriggerError(E_USER_WARNING, 'ACP_AD_DOES_NOT_EXIST');
		}

		$controller->action_edit();
	}

	/**
	* Test data for the test_ad_enable() function
	*
	* @return array Array of test data
	*/
	public function ad_enable_data()
	{
		return array(
			array(0, true, 'ACP_AD_ENABLE_ERRORED'),
			array(0, false, 'ACP_AD_DISABLE_ERRORED'),
			array(1, false, 'ACP_AD_DISABLE_SUCCESS'),
			array(1, true, 'ACP_AD_ENABLE_SUCCESS'),
		);
	}

	/**
	* Test ad_enable() method
	*
	* @dataProvider ad_enable_data
	*/
	public function test_ad_enable($ad_id, $enable, $err_msg)
	{
		$controller = $this->get_controller();

		$this->request->expects($this->once())
			->method('variable')
			->with('id', 0)
			->willReturn($ad_id);

		$this->manager->expects($this->once())
			->method('update_ad')
			->willReturn($ad_id ? true : false);

		$this->setExpectedTriggerError($ad_id ? E_USER_NOTICE : E_USER_WARNING, $err_msg);

		if ($enable)
		{
			$controller->action_enable();
		}
		else
		{
			$controller->action_disable();
		}
	}

	/**
	* Test data for the test_action_delete() function
	*
	* @return array Array of test data
	*/
	public function action_delete_data()
	{
		return array(
			array(999, true, true),
			array(1, false, false),
			array(1, false, true),
		);
	}
	/**
	* Test action_delete() method
	*
	* @dataProvider action_delete_data
	*/
	public function test_action_delete($ad_id, $error, $confirm)
	{
		self::$confirm = $confirm;

		$controller = $this->get_controller();

		$this->request->expects($this->at(0))
			->method('variable')
			->with('id', 0)
			->willReturn($ad_id);

		if (!$confirm)
		{
			$this->request->expects($this->at(1))
				->method('variable')
				->with('i', '')
				->willReturn('');
			$this->request->expects($this->at(2))
				->method('variable')
				->with('mode', '')
				->willReturn('');
		}
		else
		{
			if ($error)
			{
				$this->setExpectedTriggerError(E_USER_WARNING, 'ACP_AD_DELETE_ERRORED');
			}
			else
			{
				$this->manager->expects($this->once())
					->method('delete_ad')
					->willReturn($ad_id ? true : false);

				$this->setExpectedTriggerError(E_USER_NOTICE, 'ACP_AD_DELETE_SUCCESS');
			}
		}

		$controller->action_delete();
	}

	/**
	* Test list_ads() method
	*/
	public function test_list_ads()
	{
		$controller = $this->get_controller();

		$this->manager->expects($this->once())
			->method('get_all_ads')
			->willReturn(array(
				array(
					'ad_id'			=> 1,
					'ad_name'		=> '',
					'ad_enabled'	=> 1,
					'ad_end_date'	=> 0,
				),
				array(
					'ad_id'			=> 1,
					'ad_name'		=> '',
					'ad_enabled'	=> 1,
					'ad_end_date'	=> 1,
				),
			));

		$this->template->expects($this->atLeastOnce())
			->method('assign_block_vars');
		$this->template->expects($this->once())
			->method('assign_vars')
			->with(array(
				'U_ACTION_ADD'	=> $this->u_action . '&amp;action=add',
				'S_VIEWS_ENABLED'	=> $this->config['phpbb_ads_enable_views'],
				'S_CLICKS_ENABLED'	=> $this->config['phpbb_ads_enable_clicks'],
			));

		$controller->list_ads();
	}
}

/**
 * Mock confirm_box()
 * Note: use the same namespace as the admin_controller
 *
 * @return bool
 */
function confirm_box()
{
	return \phpbb\ads\controller\admin_controller_test::$confirm;
}

/**
 * Mock add_form_key()
 * Note: use the same namespace as the admin_controller
 */
function add_form_key()
{
}

/**
 * Mock check_form_key()
 * Note: use the same namespace as the admin_controller
 *
 * @return bool
 */
function check_form_key()
{
	return \phpbb\ads\controller\admin_controller_test::$valid_form;
}
