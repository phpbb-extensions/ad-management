<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\admanagement\controller;

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
		return array('phpbb/admanagement');
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
		$lang = new \phpbb\language\language($lang_loader);

		// Load/Mock classes required by the controller class
		$this->db = $this->new_dbal();
		$this->template = $this->getMock('\phpbb\template\template');
		$this->user = new \phpbb\user($lang, '\phpbb\datetime');
		$this->request = $this->getMock('\phpbb\request\request');
		$this->ads_table = 'phpbb_ads';
		$this->php_ext = $phpEx;
		$this->ext_path = $phpbb_root_path . 'ext/phpbb/admanagement/';
	
		$this->u_action = $phpbb_root_path . 'adm/index.php?i=-phpbb-admanagement-acp-main_module&mode=manage';

		// globals
		$phpbb_extension_manager = new \phpbb_mock_extension_manager($phpbb_root_path);
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
		$template = $this->getMock('\phpbb\template\template');
		$request = new \phpbb_mock_request();
		$config = new \phpbb\config\config(array());
		set_config(null, null, null, $config);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
	}

	/**
	* Returns fresh new controller.
	*
	* @return	\phpbb\admanagement\controller\admin_controller	Admin controller
	*/
	public function get_controller()
	{
		$controller = new \phpbb\admanagement\controller\admin_controller(
			$this->db,
			$this->template,
			$this->user,
			$this->request,
			$this->ads_table,
			$this->php_ext,
			$this->ext_path
		);
		$controller->set_page_url($this->u_action);

		return $controller;
	}

	/**
	* Data for test_main
	*
	* @return array Array of test data
	*/
	public function data_main()
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
	* Test main()
	*
	* @dataProvider data_main
	*/
	public function test_main($action, $expected)
	{
		$controller = $this->getMockBuilder('\phpbb\admanagement\controller\admin_controller')
			->setMethods(array('action_add', 'action_edit', 'ad_enable', 'action_delete', 'list_ads'))
			->setConstructorArgs(array(
				$this->db,
				$this->template,
				$this->user,
				$this->request,
				$this->ads_table,
				$this->php_ext,
				$this->ext_path,
			))
			->getMock();

		$this->request->expects($this->once())
			->method('variable')
			->willReturn($action);

		$controller->expects($this->once())
			->method($expected);

		$controller->main();
	}

	/**
	* Test get_page_title() method
	*/
	public function test_get_page_title()
	{
		$controller = $this->get_controller();
		$this->assertEquals($controller->get_page_title(), $this->user->lang('ACP_ADMANAGEMENT_TITLE'));
	}

	/**
	* Test action_add() method without submitted data
	*/
	public function test_action_add_no_submit()
	{
		$controller = $this->get_controller();

		$this->request->expects($this->once())
			->method('is_set_post')
			->with('submit')
			->willReturn(false);
		
		$this->template->expects($this->once())
			->method('assign_vars')
			->with(array(
				'S_ADD_AD'	=> true,
				'U_BACK'	=> $this->u_action,
			));
		
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
			array('', true, 'AD_NAME_REQUIRED'),
			array(str_repeat('a', 256), true, 'AD_NAME_TOO_LONG'),
			array('Unit test advertisement', false, ''),
		);
	}

	/**
	* Test action_add() method with submitted data
	*
	* @dataProvider action_add_data
	*/
	public function test_action_add_submit($ad_name, $s_error, $error_msg)
	{
		$controller = $this->get_controller();

		$this->request->expects($this->once())
			->method('is_set_post')
			->with('submit')
			->willReturn(true);

		$this->request->expects($this->any())
			->method('variable')
			->will($this->onConsecutiveCalls($ad_name, '', '', false));

		if ($s_error)
		{
			$this->template->expects($this->at(0))
				->method('assign_vars')
				->with(array(
					'S_ERROR'		=> $s_error,
					'ERROR_MSG'		=> $error_msg,
					'AD_NAME'		=> $ad_name,
					'AD_NOTE'		=> '',
					'AD_CODE'		=> '',
					'AD_ENABLED'	=> false,
				));
		}
		else
		{
			$this->setExpectedTriggerError(E_USER_NOTICE, 'ACP_AD_ADD_SUCCESS');
		}

		$controller->action_add();

		// Check ad is in the DB
		if (!$s_error)
		{
			$sql = 'SELECT * FROM ' . $this->ads_table . '
				WHERE ad_name = "' . $ad_name . '"';
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);

			$this->assertEquals('', $row['ad_note']);
			$this->assertEquals('', $row['ad_code']);
			$this->assertEquals('0', $row['ad_enabled']);
		}
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

		$this->request->expects($this->once())
			->method('is_set_post')
			->with('submit')
			->willReturn(false);

		if (!$ad_id)
		{
			$this->setExpectedTriggerError(E_USER_WARNING, 'ACP_AD_DOES_NOT_EXIST');
		}
		else
		{
			$this->template->expects($this->at(0))
				->method('assign_vars')
				->with(array(
					'S_EDIT_AD'	=> true,
					'EDIT_ID'	=> $ad_id,
					'U_BACK'	=> $this->u_action,
				));

			$this->template->expects($this->at(1))
				->method('assign_vars')
				->with(array(
					'S_ERROR'		=> false,
					'ERROR_MSG'		=> '',
					'AD_NAME'		=> 'One and only',
					'AD_NOTE'		=> 'And it\'s desc',
					'AD_CODE'		=> 'admanagementcode',
					'AD_ENABLED'	=> '1',
				));
		}

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
			array(0, 'Unit test advertisement', true, ''),
			array(1, '', true, 'AD_NAME_REQUIRED'),
			array(1, str_repeat('a', 256), true, 'AD_NAME_TOO_LONG'),
			array(1, 'Unit test advertisement', false, ''),
		);
	}

	/**
	* Test action_edit() method with submitted data
	*
	* @dataProvider action_edit_data
	*/
	public function test_action_edit_submit($ad_id, $ad_name, $s_error, $error_msg)
	{
		$controller = $this->get_controller();

		$this->request->expects($this->any())
			->method('variable')
			->will($this->onConsecutiveCalls($ad_id, $ad_name, '', '', false));

		$this->request->expects($this->once())
			->method('is_set_post')
			->with('submit')
			->willReturn(true);

		if ($ad_id)
		{
			if (!$s_error)
			{
				$this->setExpectedTriggerError(E_USER_NOTICE, 'ACP_AD_EDIT_SUCCESS');
			}
			else
			{
				$this->template->expects($this->at(1))
					->method('assign_vars')
					->with(array(
						'S_ERROR'		=> $s_error,
						'ERROR_MSG'		=> $error_msg,
						'AD_NAME'		=> $ad_name,
						'AD_NOTE'		=> '',
						'AD_CODE'		=> '',
						'AD_ENABLED'	=> false,
					));
			}
		}
		else
		{
			$this->setExpectedTriggerError(E_USER_WARNING, 'ACP_AD_DOES_NOT_EXIST');
		}

		$controller->action_edit();

		// Check ad is in the DB
		if (!$s_error)
		{
			$sql = 'SELECT * FROM ' . $this->ads_table . '
				WHERE ad_id = "' . $ad_id . '"';
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);

			$this->assertEquals('Unit test advertisement', $row['ad_name']);
			$this->assertEquals('', $row['ad_note']);
			$this->assertEquals('', $row['ad_code']);
			$this->assertEquals('0', $row['ad_enabled']);
		}
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
		
		$this->setExpectedTriggerError($ad_id ? E_USER_NOTICE : E_USER_WARNING, $err_msg);

		if ($enable)
		{
			$controller->action_enable();
		}
		else
		{
			$controller->action_disable();
		}

		if ($ad_id)
		{
			$sql = 'SELECT ad_enabled
				FROM ' . $this->ads_table . '
				WHERE ad_id = ' . $ad_id;
			$result = $this->db->sql_query($sql);
			$ad_enabled = (bool) $this->db->sql_fetchfield('ad_enabled', $result);
			$this->db->sql_freeresult($result);

			$this->assertEquals(!$enable, $ad_enabled);
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
			array(999, true),
			array(1, false),
		);
	}
	/**
	* Test action_delete() method
	*
	* @dataProvider action_delete_data
	*/
	public function test_action_delete($ad_id, $error)
	{
		$controller = $this->get_controller();

		$this->request->expects($this->once())
			->method('variable')
			->willReturn($ad_id);

		if ($error)
		{
			$this->setExpectedTriggerError(E_USER_WARNING, 'ACP_AD_DELETE_ERRORED');
		}
		else
		{
			$this->setExpectedTriggerError(E_USER_NOTICE, 'ACP_AD_DELETE_SUCCESS');
		}

		$controller->action_delete();

		$sql = 'SELECT ad_id
			FROM ' . $this->ads_table . '
			WHERE ad_id = ' . $ad_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->assertTrue(empty($row));
	}

	/**
	* Test list_ads() method
	*/
	public function test_list_ads()
	{
		$controller = $this->get_controller();

		$this->template->expects($this->atLeastOnce())
			->method('assign_block_vars');
		$this->template->expects($this->once())
			->method('assign_vars')
			->with(array(
				'U_ACTION_ADD'	=> $this->u_action . '&amp;action=add',
				'ICON_PREVIEW'	=> '<img src="' . $this->ext_path . 'adm/images/icon_preview.png" alt="' . $this->user->lang('AD_PREVIEW') . '" title="' . $this->user->lang('AD_PREVIEW') . '" />',
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
	return \phpbb\admanagement\controller\admin_controller_test::$confirm;
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
	return \phpbb\admanagement\controller\admin_controller_test::$valid_form;
}
