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

use phpbb\ads\controller\admin_input_test as input;
use phpbb\ads\ext;

class admin_controller_test extends \phpbb_database_test_case
{
	/** @var bool A return value for confirm_box() */
	public static $confirm = true;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\request\request */
	protected $request;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\ads\ad\manager */
	protected $manager;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\config\db_text */
	protected $config_text;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\config\config */
	protected $config;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\ads\controller\admin_input */
	protected $input;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\ads\controller\helper */
	protected $helper;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\ads\analyser\manager */
	protected $analyser;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\controller\helper */
	protected $controller_helper;

	/** @var string root_path */
	protected $root_path;

	/** @var string php_ext */
	protected $php_ext;

	/** @var string Custom form action */
	protected $u_action;

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
	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;
		global $phpbb_dispatcher, $cache, $db, $user;

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);

		// Load/Mock classes required by the controller class
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->disableOriginalConstructor()
			->getMock();
		$this->language = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($this->language, '\phpbb\datetime');
		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$this->manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->config_text = $this->getMockBuilder('\phpbb\config\db_text')
			->disableOriginalConstructor()
			->getMock();
		$this->config = $this->getMockBuilder('\phpbb\config\config')
			->disableOriginalConstructor()
			->getMock();
		$this->input = $this->getMockBuilder('\phpbb\ads\controller\admin_input')
			->disableOriginalConstructor()
			->getMock();
		$this->helper = $this->getMockBuilder('\phpbb\ads\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$this->analyser = $this->getMockBuilder('\phpbb\ads\analyser\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$this->root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;

		$this->u_action = $phpbb_root_path . 'adm/index.php?i=-phpbb-ads-acp-main_module&mode=manage';

		// Global variables
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
		$cache = new \phpbb_mock_cache();
		$db = $this->new_dbal();
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
			$this->language,
			$this->request,
			$this->manager,
			$this->config_text,
			$this->config,
			$this->input,
			$this->helper,
			$this->analyser,
			$this->controller_helper,
			$this->root_path,
			$this->php_ext
		);
		$controller->set_page_url($this->u_action);

		return $controller;
	}

	/**
	 * Test mode_settings()
	 */
	public function test_mode_settings_no_submit()
	{
		$controller = $this->get_controller();

		$this->config['phpbb_ads_adblocker_message'] = '1';

		$this->template->expects(self::once())
			->method('assign_vars')
			->with(array(
				'U_ACTION'			=> $this->u_action,
				'AD_BLOCK_MODES'	=> ext::AD_BLOCK_MODES,
				'AD_BLOCK_CONFIG'	=> $this->config['phpbb_ads_adblocker_message'],
				'ENABLE_VIEWS'		=> $this->config['phpbb_ads_enable_views'],
				'ENABLE_CLICKS'		=> $this->config['phpbb_ads_enable_clicks'],
				'SHOW_AGREEMENT'	=> $this->config['phpbb_ads_show_agreement'],
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
		input::$valid_form = $valid_form;

		$controller = $this->get_controller();

		$this->request->expects(self::once())
			->method('is_set_post')
			->with('submit')
			->willReturn(true);

		if ($valid_form)
		{
			$this->request
				->expects(self::exactly(4))
				->method('variable')
				->withConsecutive(
					['adblocker_message', 0],
					['enable_views', 0],
					['enable_clicks', 0],
					['show_agreement', 0]
				)
				->willReturnOnConsecutiveCalls(
					$adblocker_data,
					1,
					1,
					1
				);

			$this->config
				->expects(self::exactly(4))
				->method('set')
				->withConsecutive(
					['phpbb_ads_adblocker_message', $adblocker_data],
					['phpbb_ads_enable_views', 1],
					['phpbb_ads_enable_clicks', 1],
					['phpbb_ads_show_agreement', 1]
				);

			$this->setExpectedTriggerError(E_USER_NOTICE, 'ACP_AD_SETTINGS_SAVED');
		}
		else
		{
			$this->setExpectedTriggerError(E_USER_WARNING, 'The submitted form was invalid. Try submitting again.');
		}

		$controller->mode_settings();
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
		/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\ads\controller\admin_controller $controller */
		$controller = $this->getMockBuilder('\phpbb\ads\controller\admin_controller')
			->setMethods(array('action_add', 'action_edit', 'ad_enable', 'action_delete', 'list_ads'))
			->setConstructorArgs(array(
				$this->template,
				$this->language,
				$this->request,
				$this->manager,
				$this->config_text,
				$this->config,
				$this->input,
				$this->helper,
				$this->analyser,
				$this->controller_helper,
				$this->root_path,
				$this->php_ext
			))
			->getMock();

		$this->request->expects(self::once())
			->method('variable')
			->willReturn($action);

		$controller->expects(self::once())
			->method($expected);

		$controller->mode_manage();
	}

	/**
	* Test action_add() method without submitted data
	*/
	public function test_action_add_no_submit()
	{
		$controller = $this->get_controller();

		$this->request
			->expects(self::exactly(5))
			->method('is_set_post')
			->withConsecutive(
				['preview'],
				['upload_banner'],
				['analyse_ad_code'],
				['submit_add'],
				['submit_edit']
			)
			->willReturnOnConsecutiveCalls(
				false,
				false,
				false,
				false,
				false
			);

		$this->helper->expects(self::once())
			->method('assign_locations');

		$this->helper->expects(self::once())
			->method('get_find_username_link')
			->willReturn('u_find_username');

		$this->helper->expects(self::once())
			->method('get_date')
			->with('tomorrow')
			->willReturn('2000-12-16');

		$this->template->expects(self::once())
			->method('assign_vars')
			->with(array(
				'S_ADD_AD'				=> true,
				'U_BACK'				=> $this->u_action,
				'U_ACTION'				=> "{$this->u_action}&amp;action=add",
				'U_FIND_USERNAME'		=> 'u_find_username',
				'U_ENABLE_VISUAL_DEMO'	=> null,
				'DATE_MINIMUM'			=> '2000-12-16',
			));

		$this->request->expects(self::once())
			->method('variable')
			->with('action', '')
			->willReturn('add');

		$controller->mode_manage();
	}

	/**
	* Test action_add() method's preview
	*/
	public function test_action_add_preview()
	{
		$controller = $this->get_controller();

		$this->request->expects(self::once())
			->method('is_set_post')
			->with('preview')
			->willReturn(true);

		$data = array(
			'ad_code'		=> '<!-- AD CODE SAMPLE -->',
			'ad_locations'	=> array(),
		);

		$this->input->expects(self::once())
			->method('get_form_data')
			->willReturn($data);

		$this->template->expects(self::once())
			->method('assign_var')
			->with('PREVIEW', '<!-- AD CODE SAMPLE -->');

		$this->input->expects(self::once())
			->method('get_errors')
			->willReturn(array());

		$this->helper->expects(self::once())
			->method('assign_data')
			->with($data, array());

		$this->request->expects(self::once())
			->method('variable')
			->with('action', '')
			->willReturn('add');

		$controller->mode_manage();
	}

	/**
	 * Test action_add() method with upload_banner submitted data
	 */
	public function test_action_add_upload_banner()
	{
		$controller = $this->get_controller();

		$this->request
			->expects(self::exactly(2))
			->method('is_set_post')
			->withConsecutive(
				['preview'],
				['upload_banner']
			)
			->willReturnOnConsecutiveCalls(
				false,
				true
			);

		$data = array(
			'ad_code'		=> '<!-- AD CODE SAMPLE -->',
			'ad_locations'	=> array(),
		);

		$banner_ad_code = '<!-- BANNER AD CODE -->';

		$this->input->expects(self::once())
			->method('get_form_data')
			->willReturn($data);

		$this->input->expects(self::once())
			->method('banner_upload')
			->with($data['ad_code'])
			->willReturn($banner_ad_code);

		$data['ad_code'] = $banner_ad_code;

		$this->input->expects(self::once())
			->method('get_errors')
			->willReturn(array());

		$this->helper->expects(self::once())
			->method('assign_data')
			->with($data, array());

		$this->request->expects(self::once())
			->method('variable')
			->with('action', '')
			->willReturn('add');

		$controller->mode_manage();
	}

	/**
	 * Test action_add() method with analyse_ad_code submitted data
	 */
	public function test_action_add_analyse_ad_code()
	{
		$controller = $this->get_controller();

		$this->request
			->expects(self::exactly(3))
			->method('is_set_post')
			->withConsecutive(
				['preview'],
				['upload_banner'],
				['analyse_ad_code']
			)
			->willReturnOnConsecutiveCalls(
				false,
				false,
				true
			);

		$data = array(
			'ad_code'		=> '<!-- AD CODE SAMPLE -->',
			'ad_locations'	=> array(),
		);

		$this->input->expects(self::once())
			->method('get_form_data')
			->willReturn($data);

		$this->analyser->expects(self::once())
			->method('run')
			->with($data['ad_code']);

		$this->input->expects(self::once())
			->method('get_errors')
			->willReturn(array());

		$this->helper->expects(self::once())
			->method('assign_data')
			->with($data, array());

		$this->request->expects(self::once())
			->method('variable')
			->with('action', '')
			->willReturn('add');

		$controller->mode_manage();
	}

	/**
	* Test data for the test_action_add_submit() function
	*
	* @return array Array of test data
	*/
	public function action_add_data()
	{
		return array(
			array(true, 0),
			array(true, 2),
			array(false, 0),
			array(false, 2),
		);
	}

	/**
	* Test action_add() method with submitted data
	*
	* @dataProvider action_add_data
	*/
	public function test_action_add_submit($s_error, $ad_owner)
	{
		$controller = $this->get_controller();

		$this->request->expects(self::exactly(4))
			->method('is_set_post')
			->withConsecutive(
				['preview'],
				['upload_banner'],
				['analyse_ad_code'],
				['submit_add']
			)
			->willReturnOnConsecutiveCalls(
				false,
				false,
				false,
				true
			);

		$data = array(
			'ad_name'		=> 'Ad Name #1',
			'ad_code'		=> 'Ad Code #1',
			'ad_locations'	=> array(),
			'ad_owner'		=> $ad_owner,
		);

		$this->input->expects(self::once())
			->method('get_form_data')
			->willReturn($data);

		$this->input->expects(self::once())
			->method('has_errors')
			->willReturn($s_error);

		if ($s_error)
		{
			$this->input->expects(self::once())
				->method('get_errors')
				->willReturn(array());

			$this->helper->expects(self::once())
				->method('assign_data')
				->with($data, array());
		}
		else
		{
			$this->manager->expects(self::once())
				->method('insert_ad')
				->with($data)
				->willReturn(1);

			$this->manager->expects(($ad_owner ? self::once() : self::never()))
				->method('get_ads_by_owner')
				->with($ad_owner)
				->willReturn(array());

			$this->manager->expects(self::once())
				->method('insert_ad_locations')
				->with(1, array());

			$this->helper->expects(self::once())
				->method('log')
				->with('ADD', 'Ad Name #1');

			$this->setExpectedTriggerError(E_USER_NOTICE, 'ACP_AD_ADD_SUCCESS');
		}

		$this->request->expects(self::once())
			->method('variable')
			->with('action', '')
			->willReturn('add');

		$reflection_controller = new \ReflectionObject($controller);
		$reflection_controller_auth_admin = $reflection_controller->getProperty('auth_admin');
		$reflection_controller_auth_admin->setAccessible(true);
		$reflection_controller_auth_admin = $reflection_controller_auth_admin->getValue($controller);
		$reflection_controller_auth_admin->acl_options['id']['u_'] = 0;
		$reflection_controller_auth_admin->acl_options['id']['u_phpbb_ads'] = 0;
		$reflection_controller_mode_manage_method = $reflection_controller->getMethod('mode_manage');
		$reflection_controller_mode_manage_method->invoke($controller);
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

		$this->request->expects(self::exactly(2))
			->method('variable')
			->withConsecutive(
				['action', ''],
				['id', 0]
			)
			->willReturnOnConsecutiveCalls(
				'edit',
				$ad_id
			);

		$this->request->expects(self::exactly(5))
			->method('is_set_post')
			->withConsecutive(
				['preview'],
				['upload_banner'],
				['analyse_ad_code'],
				['submit_add'],
				['submit_edit']
			)
			->willReturnOnConsecutiveCalls(
				false,
				false,
				false,
				false,
				false
			);

		$data = array(
			'ad_name'			=> 'Primary ad',
			'ad_note'			=> 'Ad description #1',
			'ad_code'			=> 'Ad Code #1',
			'ad_enabled'		=> '1',
			'ad_start_date'		=> '1514764800',
			'ad_end_date'		=> '2051308800',
			'ad_priority'		=> '5',
			'ad_views_limit'	=> '0',
			'ad_clicks_limit'	=> '0',
			'ad_owner'			=> '2',
		);

		$this->manager->expects(self::once())
			->method('get_ad')
			->willReturn(!$ad_id ? false : $data);

		if (!$ad_id)
		{
			$this->setExpectedTriggerError(E_USER_WARNING, 'ACP_AD_DOES_NOT_EXIST');
		}
		else
		{
			$ad_locations = array(
				'above_footer',
				'above_header',
			);
			$this->manager->expects(self::once())
				->method('get_ad_locations')
				->willReturn($ad_locations);
			$data['ad_locations'] = $ad_locations;

			$this->helper->expects(self::once())
				->method('get_find_username_link')
				->willReturn('u_find_username');

			$this->helper->expects(self::once())
				->method('get_date')
				->with('tomorrow')
				->willReturn('2000-12-16');

			$this->template->expects(self::once())
				->method('assign_vars')
				->with(array(
					'S_EDIT_AD'				=> true,
					'EDIT_ID'				=> $ad_id,
					'U_BACK'				=> $this->u_action,
					'U_ACTION'				=> "{$this->u_action}&amp;action=edit&amp;id=" . $ad_id,
					'U_FIND_USERNAME'		=> 'u_find_username',
					'U_ENABLE_VISUAL_DEMO'	=> null,
					'DATE_MINIMUM'			=> '2000-12-16',
				));

			$this->input->expects(self::once())
				->method('get_errors')
				->willReturn(array());

			$this->helper->expects(self::once())
				->method('assign_data')
				->with($data, array());
		}

		$controller->mode_manage();
	}

	/**
	* Test action_edit() method's preview
	*/
	public function test_action_edit_preview()
	{
		$controller = $this->get_controller();

		$this->request
			->expects(self::exactly(2))
			->method('variable')
			->withConsecutive(
				['action', ''],
				['id', 0]
			)
			->willReturnOnConsecutiveCalls(
				'edit',
				1
			);

		$this->request->expects(self::once())
			->method('is_set_post')
			->with('preview')
			->willReturn(true);

		$data = array(
			'ad_code'		=> 'Ad Code #1',
			'ad_locations'	=> array(),
		);

		$this->input->expects(self::once())
			->method('get_form_data')
			->willReturn($data);

		$this->helper->expects(self::once())
			->method('get_find_username_link')
			->willReturn('u_find_username');

		$this->template->expects(self::once())
			->method('assign_var')
			->with('PREVIEW', 'Ad Code #1');

		$this->helper->expects(self::once())
			->method('get_date')
			->with('tomorrow')
			->willReturn('2000-12-16');

		$this->template->expects(self::once())
			->method('assign_vars')
			->with(array(
				'S_EDIT_AD'				=> true,
				'EDIT_ID'				=> 1,
				'U_BACK'				=> $this->u_action,
				'U_ACTION'				=> "{$this->u_action}&amp;action=edit&amp;id=1",
				'U_FIND_USERNAME'		=> 'u_find_username',
				'U_ENABLE_VISUAL_DEMO'	=> null,
				'DATE_MINIMUM'			=> '2000-12-16',
			));

		$this->input->expects(self::once())
			->method('get_errors')
			->willReturn(array());

		$this->helper->expects(self::once())
			->method('assign_data')
			->with($data, array());

		$controller->mode_manage();
	}

	/**
	* Test data for the test_action_edit_submit() function
	*
	* @return array Array of test data
	*/
	public function action_edit_data()
	{
		return array(
			array(true, false, 0),
			array(false, true, 0),
			array(false, true, 2),
			array(false, false, 0),
			array(true, true, 0),
		);
	}

	/**
	* Test action_edit() method with submitted data
	*
	* @dataProvider action_edit_data
	*/
	public function test_action_edit_submit($s_error, $success, $ad_owner)
	{
		$controller = $this->get_controller();

		$this->request
			->expects(self::exactly(3))
			->method('variable')
			->withConsecutive(
				['action', ''],
				['id', 0],
				['id', 0]
			)
			->willReturnOnConsecutiveCalls(
				'edit',
				1,
				1
			);

		$this->request
			->expects(self::exactly(5))
			->method('is_set_post')
			->withConsecutive(
				['preview'],
				['upload_banner'],
				['analyse_ad_code'],
				['submit_add'],
				['submit_edit']
			)
			->willReturnOnConsecutiveCalls(
				false,
				false,
				false,
				false,
				true
			);

		$old_data = array(
			'ad_name'		=> 'Old Ad Name #1',
			'ad_code'		=> 'Old Ad Code #1',
			'ad_locations'	=> array(),
			'ad_owner'		=> $ad_owner,
		);

		$data = array(
			'ad_name'		=> 'Ad Name #1',
			'ad_code'		=> 'Ad Code #1',
			'ad_locations'	=> array(),
			'ad_owner'		=> $ad_owner,
		);

		$this->input->expects(self::once())
			->method('get_form_data')
			->willReturn($data);

		$this->input->expects(self::once())
			->method('has_errors')
			->willReturn($s_error);

		if ($s_error)
		{
			$this->helper->expects(self::once())
				->method('get_find_username_link')
				->willReturn('u_find_username');

			$this->helper->expects(self::once())
				->method('get_date')
				->with('tomorrow')
				->willReturn('2000-12-16');

			$this->template->expects(self::once())
				->method('assign_vars')
				->with(array(
					'S_EDIT_AD'				=> true,
					'EDIT_ID'				=> 1,
					'U_BACK'				=> $this->u_action,
					'U_ACTION'				=> "{$this->u_action}&amp;action=edit&amp;id=1",
					'U_FIND_USERNAME'		=> 'u_find_username',
					'U_ENABLE_VISUAL_DEMO'	=> null,
					'DATE_MINIMUM'			=>'2000-12-16',
				));

			$this->input->expects(self::once())
				->method('get_errors')
				->willReturn(array());

			$this->helper->expects(self::once())
				->method('assign_data')
				->with($data, array());
		}
		else
		{
			$this->manager->expects((self::once()))
				->method('get_ad')
				->with(1)
				->willReturn($old_data);

			$this->manager->expects(self::once())
				->method('update_ad')
				->with(1, $data)
				->willReturn($success);

			if ($success)
			{
				$this->manager->expects(($ad_owner ? self::exactly(2) : self::never()))
					->method('get_ads_by_owner')
					->with($ad_owner)
					->willReturn(array());

				$this->manager->expects(self::once())
					->method('delete_ad_locations')
					->with(1);

				$this->manager->expects(self::once())
					->method('insert_ad_locations')
					->with(1, array());

				$this->helper->expects(self::once())
					->method('log')
					->with('EDIT', 'Ad Name #1');

				$this->setExpectedTriggerError(E_USER_NOTICE, 'ACP_AD_EDIT_SUCCESS');
			}
			else
			{
				$this->setExpectedTriggerError(E_USER_WARNING, 'ACP_AD_DOES_NOT_EXIST');
			}
		}

		$reflection_controller = new \ReflectionObject($controller);
		$reflection_controller_auth_admin = $reflection_controller->getProperty('auth_admin');
		$reflection_controller_auth_admin->setAccessible(true);
		$reflection_controller_auth_admin = $reflection_controller_auth_admin->getValue($controller);
		$reflection_controller_auth_admin->acl_options['id']['u_'] = 0;
		$reflection_controller_auth_admin->acl_options['id']['u_phpbb_ads'] = 0;
		$reflection_controller_mode_manage_method = $reflection_controller->getMethod('mode_manage');
		$reflection_controller_mode_manage_method->invoke($controller);
	}

	/**
	* Test data for the test_ad_enable() function
	*
	* @return array Array of test data
	*/
	public function ad_enable_data()
	{
		return array(
			array(0, true, false, 'ACP_AD_ENABLE_ERRORED'),
			array(0, false, false, 'ACP_AD_DISABLE_ERRORED'),
			array(1, false, false, 'ACP_AD_DISABLE_SUCCESS'),
			array(1, true, false, 'ACP_AD_ENABLE_SUCCESS'),
			array(1, false, true, 'ACP_AD_DISABLE_SUCCESS'),
			array(1, true, true, 'ACP_AD_ENABLE_SUCCESS'),
		);
	}

	/**
	* Test ad_enable() method
	*
	* @dataProvider ad_enable_data
	*/
	public function test_ad_enable($ad_id, $enable, $is_ajax, $err_msg)
	{
		$controller = $this->get_controller();

		$this->manager->expects(self::once())
			->method('update_ad')
			->willReturn((bool) $ad_id);

		$this->request->expects(self::once())
			->method('is_ajax')
			->willReturn($is_ajax);

		if ($is_ajax)
		{
			// Handle trigger_error() output called from json_response
			$this->setExpectedTriggerError(E_WARNING);
		}
		else
		{
			$this->setExpectedTriggerError($ad_id ? E_USER_NOTICE : E_USER_WARNING, $err_msg);
		}

		$this->request
			->expects(self::exactly(2))
			->method('variable')
			->withConsecutive(
				['action', ''],
				['id', 0]
			)
			->willReturnOnConsecutiveCalls(
				$enable ? 'enable' : 'disable',
				$ad_id
			);

		$controller->mode_manage();
	}

	/**
	* Test data for the test_action_delete() function
	*
	* @return array Array of test data
	*/
	public function action_delete_data()
	{
		return array(
			array(999, 0, true, true),
			array(1, 0, false, false),
			array(1, 0, false, true),
			array(1, 2, false, true),
		);
	}

	/**
	 * Test action_delete() method
	 *
	 * @dataProvider action_delete_data
	 */
	public function test_action_delete($ad_id, $ad_owner, $error, $confirm)
	{
		self::$confirm = $confirm;

		$controller = $this->get_controller();

		$this->request
			->expects(self::exactly($confirm ? 2 : 4))
			->method('variable')
			->withConsecutive(['action', ''], ['id', 0], ['i', ''], ['mode', ''])
			->willReturnOnConsecutiveCalls('delete', $ad_id, '', '');

		if (!$confirm)
		{
			// called in list_ads()
			$this->manager->expects(self::atMost(1))
				->method('get_all_ads')
				->willReturn(array());
		}
		else if ($error)
		{
			$this->setExpectedTriggerError(E_USER_WARNING, 'ACP_AD_DELETE_ERRORED');
			$this->manager->expects(self::once())
				->method('get_ad')
				->with($ad_id)
				->willReturn(array('id' => $ad_id, 'ad_owner' => $ad_owner, 'ad_name' => ''));
		}
		else
		{
			$this->manager->expects(self::once())
				->method('get_ad')
				->with($ad_id)
				->willReturn(array('id' => $ad_id, 'ad_owner' => $ad_owner, 'ad_name' => ''));
			$this->manager->expects(self::once())
				->method('delete_ad')
				->willReturn((bool) $ad_id);
			$this->manager->expects(($ad_owner ? self::once() : self::never()))
				->method('get_ads_by_owner')
				->with($ad_owner)
				->willReturn(array());

			$this->setExpectedTriggerError(E_USER_NOTICE, 'ACP_AD_DELETE_SUCCESS');
		}

		$reflection_controller = new \ReflectionObject($controller);
		$reflection_controller_auth_admin = $reflection_controller->getProperty('auth_admin');
		$reflection_controller_auth_admin->setAccessible(true);
		$reflection_controller_auth_admin = $reflection_controller_auth_admin->getValue($controller);
		$reflection_controller_auth_admin->acl_options['id']['u_'] = 0;
		$reflection_controller_auth_admin->acl_options['id']['u_phpbb_ads'] = 0;
		$reflection_controller_mode_manage_method = $reflection_controller->getMethod('mode_manage');
		$reflection_controller_mode_manage_method->invoke($controller);
	}

	/**
	* Test list_ads() method
	*/
	public function test_list_ads()
	{
		$controller = $this->get_controller();

		$rows = array(
			array(
				'ad_id'			=> 1,
				'ad_name'		=> '',
				'ad_enabled'	=> 1,
				'ad_start_date'	=> 0,
				'ad_end_date'	=> 0,
				'ad_priority'	=> 5,
				'ad_views'		=> 0,
				'ad_clicks'		=> 0,
				'ad_views_limit'	=> 0,
				'ad_clicks_limit'	=> 0,

			),
			array(
				'ad_id'			=> 2,
				'ad_name'		=> '',
				'ad_enabled'	=> 1,
				'ad_start_date'	=> 0,
				'ad_end_date'	=> 1,
				'ad_priority'	=> 5,
				'ad_views'		=> 0,
				'ad_clicks'		=> 0,
				'ad_views_limit'	=> 0,
				'ad_clicks_limit'	=> 0,
			),
		);

		$this->manager->expects(self::once())
			->method('get_all_ads')
			->willReturn($rows);

		$this->helper
			->expects(self::exactly(2))
			->method('is_expired')
			->withConsecutive(
				[$rows[0]],
				[$rows[1]]
			)
			->willReturnOnConsecutiveCalls(
				false,
				true
			);

		$this->manager->expects(self::once())
			->method('update_ad')
			->with(2, array('ad_enabled' => 0));

		$this->template->expects(self::atLeastOnce())
			->method('assign_block_vars');

		$this->template->expects(self::once())
			->method('assign_vars')
			->with(array(
				'U_ACTION_ADD'		=> $this->u_action . '&amp;action=add',
				'S_VIEWS_ENABLED'	=> $this->config['phpbb_ads_enable_views'],
				'S_CLICKS_ENABLED'	=> $this->config['phpbb_ads_enable_clicks'],
			));

		$this->request->expects(self::once())
			->method('variable')
			->with('action', '')
			->willReturn('');

		$controller->mode_manage();
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
