<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\admanagement\tests\controller;

require_once dirname(__FILE__) . '/../../../../../includes/functions.php';
require_once dirname(__FILE__) . '/../../../../../includes/functions_content.php';
require_once dirname(__FILE__) . '/../../../../../includes/functions_acp.php';

class admin_controller_test extends \phpbb_database_test_case
{
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
	protected $phpbb_admin_path;

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
		return $this->createXMLDataSet(__DIR__ . '/fixtures/ad.xml');
	}

	/**
	* {@inheritDoc}
	*/
	public function setUp()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;
		global $phpbb_extension_manager, $phpbb_dispatcher, $template, $request, $config, $user;

		// Load/Mock classes required by the controller class
		$this->db = $this->new_dbal();
		$this->template = $this->getMock('\phpbb\template\template');
		$this->user = new \phpbb\user('\phpbb\datetime');
		$this->request = $this->getMock('\phpbb\request\request');
		$this->ads_table = 'phpbb_ads';
		$this->php_ext = $phpEx;
		$this->phpbb_admin_path = $phpbb_root_path . 'adm/';
	
		$this->u_action = $this->phpbb_admin_path . 'index.php?i=-phpbb-admanagement-acp-main_module&mode=manage';

		// globals
		$phpbb_extension_manager = new \phpbb_mock_extension_manager($phpbb_root_path);
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
		$template = $this->getMock('\phpbb\template\template');
		$request = new \phpbb_mock_request();
		$config = new \phpbb\config\config(array());
		set_config(null, null, null, $config);
		$user = new \phpbb\user('\phpbb\datetime');
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
			$this->phpbb_admin_path
		);
		$controller->set_page_url($this->u_action);
		$controller->load_lang();

		return $controller;
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
	* Test get_action() method
	*/
	public function test_get_action()
	{
		$controller = $this->get_controller();

		$this->request->expects($this->once())
			->method('variable')
			->with('action', '')
			->willReturn('default');

		$action = $controller->get_action();
		$this->assertEquals($action, 'default');
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

	// /**
	// * Test data for the test_action_add_submit() function
	// *
	// * @return array Array of test data
	// */
	// public function action_add_data()
	// {
	// 	return array(
	// 		array('', true, 'Name is required.'),
	// 		array('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec.', true, 'Name length is limited to 255 characters.'),
	// 		array('Unit test advertisement', false, ''),
	// 	);
	// }

	// /**
	// * Test action_add() method with submitted data
	// *
	// * @dataProvider action_add_data
	// */
	// public function test_action_add_submit($ad_name, $s_error, $error_msg)
	// {
	// 	$controller = $this->get_controller();

	// 	// TODO: fails because of check_form_key()

	// 	$this->request->expects($this->once())
	// 		->method('is_set_post')
	// 		->with('submit')
	// 		->willReturn(true);

	// 	$this->request->method('variable')->will($this->onConsecutiveCalls($ad_name, '', '', false));

	// 	if ($s_error)
	// 	{
	// 		$this->template->expects($this->at(0))
	// 			->method('assign_vars')
	// 			->with(array(
	// 				'S_ERROR'	=> $s_error,
	// 				'ERROR_MSG'	=> $error_msg,
	// 			));

	// 		$this->template->expects($this->at(1))
	// 			->method('assign_vars')
	// 			->with(array(
	// 				'AD_NAME'		=> $ad_name,
	// 				'AD_NOTE'		=> '',
	// 				'AD_CODE'		=> '',
	// 				'AD_ENABLED'	=> false,
	// 			));
	// 	}
	// 	else
	// 	{
	// 		$sql = 'SELECT * FROM ' . $this->ads_table . '
	// 			WHERE ad_name = "' . $ad_name . '"';
	// 		$result = $this->db->sql_query($sql);
	// 		$row = $this->db->sql_fetchrow($result);

	// 		$this->assertEquals('', $row['ad_note']);
	// 		$this->assertEquals('', $row['ad_code']);
	// 		$this->assertEquals(0, $row['ad_enabled']);
	// 	}

	// 	// TODO: fails because trigger_error is called

	// 	$controller->action_add();
	// }

	/**
	* Test data for the test_ad_enable() function
	*
	* @return array Array of test data
	*/
	public function ad_enable_data()
	{
		return array(
			array(0, true),
			array(0, false),
			array(1, false),
			array(1, true),
		);
	}

	/**
	* Test ad_enable() method
	*
	* @dataProvider ad_enable_data
	*/
	public function test_ad_enable($ad_id, $enable)
	{
		$controller = $this->get_controller();

		$this->request->expects($this->once())
			->method('variable')
			->with('id', 0)
			->willReturn($ad_id);

		$controller->ad_enable($enable);

		// TODO: fails here because trigger_error is called

		if ($ad_id)
		{
			$sql = 'SELECT ad_enabled
				FROM ' . $this->ads_table . '
				WHERE ad_id = ' . $ad_id;
			$result = $this->db->sql_query($sql);
			$ad_enabled = (bool) $this->db->sql_fetchfield('ad_enabled', $result);
			$this->db->sql_freeresult($result);

			$this->assertEqual(!$enabled, $ad_enabled);
		}

		// TODO: should check for trigger_error lang here
	}

	/**
	* Test action_delete() method
	*/
	public function test_action_delete()
	{
		$controller = $this->get_controller();

		$this->request->method('variable')->willReturn(1);

		// TODO: how to set `confirm_box(true) == true`?

		$controller->action_delete();

		// TODO: fails here because trigger_error is called

		$sql = 'SELECT ad_id
			FROM ' . $this->ads_table . '
			WHERE ad_id = 1';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->assertEqual(true, empty($row));
	}
}