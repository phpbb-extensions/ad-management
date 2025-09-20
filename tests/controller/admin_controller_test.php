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

use phpbb\ads\ad\manager;
use phpbb\ads\ext;
use phpbb\config\config;
use phpbb\config\db_text;
use phpbb\language\language;
use phpbb\language\language_file_loader;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb_database_test_case;
use phpbb_mock_cache;
use phpbb_mock_event_dispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use phpbb\datetime;
use ReflectionException;
use ReflectionObject;

class admin_controller_test extends phpbb_database_test_case
{
	/** @var bool A return value for confirm_box() */
	public static bool $confirm = true;

	/** @var MockObject|template */
	protected template|MockObject $template;

	/** @var language */
	protected language $language;

	/** @var MockObject|request */
	protected MockObject|request $request;

	/** @var MockObject|manager */
	protected manager|MockObject $manager;

	/** @var MockObject|db_text */
	protected db_text|MockObject $config_text;

	/** @var MockObject|config */
	protected config|MockObject $config;

	/** @var MockObject|\phpbb\ads\controller\admin_input */
	protected \phpbb\ads\controller\admin_input|MockObject $input;

	/** @var MockObject|\phpbb\ads\controller\helper */
	protected MockObject|\phpbb\ads\controller\helper $helper;

	/** @var MockObject|\phpbb\ads\analyser\manager */
	protected MockObject|\phpbb\ads\analyser\manager $analyser;

	/** @var MockObject|\phpbb\controller\helper */
	protected \phpbb\controller\helper|MockObject $controller_helper;

	/** @var string root_path */
	protected string $root_path;

	/** @var string php_ext */
	protected string $php_ext;

	/** @var string Custom form action */
	protected string $u_action;

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
		global $phpbb_dispatcher, $cache, $db, $user, $language;

		$lang_loader = new language_file_loader($phpbb_root_path, $phpEx);

		// Load/Mock classes required by the controller class
		$this->template = $this->getMockBuilder(template::class)
			->disableOriginalConstructor()
			->getMock();
		$language = $this->language = new language($lang_loader);
		$user = new user($this->language, datetime::class);
		$this->request = $this->getMockBuilder(request::class)
			->disableOriginalConstructor()
			->getMock();
		$this->manager = $this->getMockBuilder(manager::class)
			->disableOriginalConstructor()
			->getMock();
		$this->config_text = $this->getMockBuilder(db_text::class)
			->disableOriginalConstructor()
			->getMock();
		$this->config = $this->getMockBuilder(config::class)
			->disableOriginalConstructor()
			->getMock();
		$this->input = $this->getMockBuilder(\phpbb\ads\controller\admin_input::class)
			->disableOriginalConstructor()
			->getMock();
		$this->helper = $this->getMockBuilder(\phpbb\ads\controller\helper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->analyser = $this->getMockBuilder(\phpbb\ads\analyser\manager::class)
			->disableOriginalConstructor()
			->getMock();
		$this->controller_helper = $this->getMockBuilder(\phpbb\controller\helper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;

		$this->u_action = $phpbb_root_path . 'adm/index.php?i=-phpbb-ads-acp-main_module&mode=manage';

		// Global variables
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$cache = new phpbb_mock_cache();
		$db = $this->new_dbal();
	}

	/**
	* Returns fresh new controller.
	*
	* @return	\phpbb\ads\controller\admin_controller	Admin controller
	*/
	public function get_controller(): admin_controller
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
			));

		$controller->mode_settings();
	}

	/**
	 * Data for test_mode_manage
	 *
	 * @return array Array of test data
	 */
	public static function data_mode_settings(): array
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
		\phpbb\ads\controller\admin_input_test::$valid_form = $valid_form;

		$controller = $this->get_controller();

		$this->request->expects(self::once())
			->method('is_set_post')
			->with('submit')
			->willReturn(true);

		if ($valid_form)
		{
			$variable_expectations = [
				['adblocker_message', 0, $adblocker_data],
				['enable_views', 0, 1],
				['enable_clicks', 0, 1]
			];
			$this->request
				->expects(self::exactly(3))
				->method('variable')
				->willReturnCallback(function($arg1, $arg2) use (&$variable_expectations) {
					$expectation = array_shift($variable_expectations);
					self::assertEquals($expectation[0], $arg1);
					self::assertEquals($expectation[1], $arg2);
					return $expectation[2];
				});

			$config_expectations = [
				['phpbb_ads_adblocker_message', $adblocker_data],
				['phpbb_ads_enable_views', 1],
				['phpbb_ads_enable_clicks', 1]
			];
			$this->config
				->expects(self::exactly(3))
				->method('set')
				->willReturnCallback(function($arg1, $arg2) use (&$config_expectations) {
					$expectation = array_shift($config_expectations);
					self::assertEquals($expectation[0], $arg1);
					self::assertEquals($expectation[1], $arg2);
				});

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
	public static function data_mode_manage(): array
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
		/** @var MockObject|\phpbb\ads\controller\admin_controller $controller */
		$controller = $this->getMockBuilder(admin_controller::class)
			->onlyMethods(array('action_add', 'action_edit', 'ad_enable', 'action_delete', 'list_ads'))
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

		$post_expectations = [
			'preview',
			'upload_banner',
			'analyse_ad_code',
			'submit_add',
			'submit_edit'
		];
		$this->request
			->expects(self::exactly(5))
			->method('is_set_post')
			->willReturnCallback(function($arg) use (&$post_expectations) {
				$expectation = array_shift($post_expectations);
				self::assertEquals($expectation, $arg);
				return false;
			});

		$this->helper->expects(self::once())
			->method('assign_locations');

		$this->helper->expects(self::once())
			->method('get_find_username_link')
			->willReturn('u_find_username');

		$this->template->expects(self::once())
			->method('assign_vars')
			->with(array(
				'S_ADD_AD'				=> true,
				'U_BACK'				=> $this->u_action,
				'U_ACTION'				=> "$this->u_action&amp;action=add",
				'PICKER_DATE_FORMAT'	=> ext::DATE_FORMAT,
				'U_FIND_USERNAME'		=> 'u_find_username',
				'U_ENABLE_VISUAL_DEMO'	=> null,
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

		$post_expectations = ['preview', 'upload_banner'];
		$return_values = [false, true];
		$this->request
			->expects(self::exactly(2))
			->method('is_set_post')
			->willReturnCallback(function($arg) use (&$post_expectations, &$return_values) {
				$expectation = array_shift($post_expectations);
				self::assertEquals($expectation, $arg);
				return array_shift($return_values);
			});

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

		$post_expectations = ['preview', 'upload_banner', 'analyse_ad_code'];
		$return_values = [false, false, true];
		$this->request
			->expects(self::exactly(3))
			->method('is_set_post')
			->willReturnCallback(function($arg) use (&$post_expectations, &$return_values) {
				$expectation = array_shift($post_expectations);
				self::assertEquals($expectation, $arg);
				return array_shift($return_values);
			});

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
	public static function action_add_data(): array
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

		$post_expectations = ['preview', 'upload_banner', 'analyse_ad_code', 'submit_add'];
		$return_values = [false, false, false, true];
		$this->request->expects(self::exactly(4))
			->method('is_set_post')
			->willReturnCallback(function($arg) use (&$post_expectations, &$return_values) {
				$expectation = array_shift($post_expectations);
				self::assertEquals($expectation, $arg);
				return array_shift($return_values);
			});

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

		$this->reflection($controller);
	}

	/**
	* Test data for the test_action_edit_no_submit() function
	*
	* @return array Array of test data
	*/
	public static function action_edit_no_submit_data(): array
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

		$variable_expectations = [['action', ''], ['id', 0]];
		$return_values = ['edit', $ad_id];
		$this->request->expects(self::exactly(2))
			->method('variable')
			->willReturnCallback(function($arg1, $arg2) use (&$variable_expectations, &$return_values) {
				$expectation = array_shift($variable_expectations);
				self::assertEquals($expectation[0], $arg1);
				self::assertEquals($expectation[1], $arg2);
				return array_shift($return_values);
			});

		$post_expectations = ['preview', 'upload_banner', 'analyse_ad_code', 'submit_add', 'submit_edit'];
		$this->request->expects(self::exactly(5))
			->method('is_set_post')
			->willReturnCallback(function($arg) use (&$post_expectations) {
				$expectation = array_shift($post_expectations);
				self::assertEquals($expectation, $arg);
				return false;
			});

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

			$this->template->expects(self::once())
				->method('assign_vars')
				->with(array(
					'S_EDIT_AD'				=> true,
					'EDIT_ID'				=> $ad_id,
					'U_BACK'				=> $this->u_action,
					'U_ACTION'				=> "$this->u_action&amp;action=edit&amp;id=" . $ad_id,
					'PICKER_DATE_FORMAT'	=> ext::DATE_FORMAT,
					'U_FIND_USERNAME'		=> 'u_find_username',
					'U_ENABLE_VISUAL_DEMO'	=> null,
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

		$variable_expectations = [['action', ''], ['id', 0]];
		$return_values = ['edit', 1];
		$this->request
			->expects(self::exactly(2))
			->method('variable')
			->willReturnCallback(function($arg1, $arg2) use (&$variable_expectations, &$return_values) {
				$expectation = array_shift($variable_expectations);
				self::assertEquals($expectation[0], $arg1);
				self::assertEquals($expectation[1], $arg2);
				return array_shift($return_values);
			});

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

		$this->template->expects(self::once())
			->method('assign_vars')
			->with(array(
				'S_EDIT_AD'				=> true,
				'EDIT_ID'				=> 1,
				'U_BACK'				=> $this->u_action,
				'U_ACTION'				=> "$this->u_action&amp;action=edit&amp;id=1",
				'PICKER_DATE_FORMAT'	=> ext::DATE_FORMAT,
				'U_FIND_USERNAME'		=> 'u_find_username',
				'U_ENABLE_VISUAL_DEMO'	=> null,
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
	public static function action_edit_data(): array
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

		$variable_expectations = [['action', ''], ['id', 0], ['id', 0]];
		$return_values = ['edit', 1, 1];
		$this->request
			->expects(self::exactly(3))
			->method('variable')
			->willReturnCallback(function($arg1, $arg2) use (&$variable_expectations, &$return_values) {
				$expectation = array_shift($variable_expectations);
				self::assertEquals($expectation[0], $arg1);
				self::assertEquals($expectation[1], $arg2);
				return array_shift($return_values);
			});

		$post_expectations = ['preview', 'upload_banner', 'analyse_ad_code', 'submit_add', 'submit_edit'];
		$post_return_values = [false, false, false, false, true];
		$this->request
			->expects(self::exactly(5))
			->method('is_set_post')
			->willReturnCallback(function($arg) use (&$post_expectations, &$post_return_values) {
				$expectation = array_shift($post_expectations);
				self::assertEquals($expectation, $arg);
				return array_shift($post_return_values);
			});

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

			$this->template->expects(self::once())
				->method('assign_vars')
				->with(array(
					'S_EDIT_AD'				=> true,
					'EDIT_ID'				=> 1,
					'U_BACK'				=> $this->u_action,
					'U_ACTION'				=> "$this->u_action&amp;action=edit&amp;id=1",
					'PICKER_DATE_FORMAT'	=> ext::DATE_FORMAT,
					'U_FIND_USERNAME'		=> 'u_find_username',
					'U_ENABLE_VISUAL_DEMO'	=> null,
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

		$this->reflection($controller);
	}

	/**
	* Test data for the test_ad_enable() function
	*
	* @return array Array of test data
	*/
	public static function ad_enable_data(): array
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

		$this->request->expects($ad_id ? self::once() : self::never())
			->method('is_ajax')
			->willReturn($is_ajax);

		if ($is_ajax)
		{
			$this->expectOutputString('{"text":"' . ($enable ? 'Enabled' : 'Disabled') . '","title":"AD_ENABLE_TITLE"}');
		}
		else
		{
			$this->setExpectedTriggerError($ad_id ? E_USER_NOTICE : E_USER_WARNING, $err_msg);
		}

		$variable_expectations = [['action', ''], ['id', 0]];
		$return_values = [$enable ? 'enable' : 'disable', $ad_id];
		$this->request
			->expects(self::exactly(2))
			->method('variable')
			->willReturnCallback(function($arg1, $arg2) use (&$variable_expectations, &$return_values) {
				$expectation = array_shift($variable_expectations);
				self::assertEquals($expectation[0], $arg1);
				self::assertEquals($expectation[1], $arg2);
				return array_shift($return_values);
			});

		$controller->mode_manage();
	}

	/**
	* Test data for the test_action_delete() function
	*
	* @return array Array of test data
	*/
	public static function action_delete_data(): array
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

		$variable_expectations = [['action', ''], ['id', 0], ['i', ''], ['mode', '']];
		$return_values = ['delete', $ad_id, '', ''];
		$this->request
			->expects(self::exactly($confirm ? 2 : 4))
			->method('variable')
			->willReturnCallback(function($arg1, $arg2) use (&$variable_expectations, &$return_values) {
				$expectation = array_shift($variable_expectations);
				self::assertEquals($expectation[0], $arg1);
				self::assertEquals($expectation[1], $arg2);
				return array_shift($return_values);
			});

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

		$this->reflection($controller);
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

		$expired_expectations = [$rows[0], $rows[1]];
		$return_values = [false, true];
		$this->helper
			->expects(self::exactly(2))
			->method('is_expired')
			->willReturnCallback(function($arg) use (&$expired_expectations, &$return_values) {
				$expectation = array_shift($expired_expectations);
				self::assertEquals($expectation, $arg);
				return array_shift($return_values);
			});

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

	/**
	 * @param admin_controller $controller
	 * @return void
	 */
	private function reflection(admin_controller $controller): void
	{
		try {
			$reflection = new ReflectionObject($controller);
			$auth_admin_prop = $reflection->getProperty('auth_admin');
			$auth_admin = $auth_admin_prop->getValue($controller);
			$auth_admin->acl_options['id']['u_'] = 0;
			$auth_admin->acl_options['id']['u_phpbb_ads'] = 0;
			$reflection->getMethod('mode_manage')->invoke($controller);
		} catch (ReflectionException $e) {
			$this->fail($e);
		}
	}
}

/**
 * Mock confirm_box()
 * Note: use the same namespace as the admin_controller
 *
 * @return bool
 */
function confirm_box(): bool
{
	return \phpbb\ads\controller\admin_controller_test::$confirm;
}
