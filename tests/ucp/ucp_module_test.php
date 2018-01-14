<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

require_once __DIR__ . '/../../../../../includes/functions_module.php';

class ucp_module_test extends \phpbb_test_case
{
	/** @var \phpbb_mock_extension_manager */
	protected $extension_manager;

	/** @var \phpbb\module\module_manager */
	protected $module_manager;

	public function setUp()
	{
		global $phpbb_dispatcher, $phpbb_extension_manager, $phpbb_root_path, $phpEx;

		$this->extension_manager = new \phpbb_mock_extension_manager(
			$phpbb_root_path,
			array(
				'phpbb/ads' => array(
					'ext_name' => 'phpbb/ads',
					'ext_active' => '1',
					'ext_path' => 'ext/phpbb/ads/',
				),
			));
		$phpbb_extension_manager = $this->extension_manager;

		$this->module_manager = new \phpbb\module\module_manager(
			new \phpbb\cache\driver\dummy(),
			$this->getMock('\phpbb\db\driver\driver_interface'),
			$this->extension_manager,
			MODULES_TABLE,
			$phpbb_root_path,
			$phpEx
		);

		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
	}

	public function test_module_info()
	{
		$this->assertEquals(array(
			'\\phpbb\\ads\\ucp\\main_module' => array(
				'filename'	=> '\\phpbb\\ads\\ucp\\main_module',
				'title'		=> 'UCP_PHPBB_ADS_TITLE',
				'modes'		=> array(
					'stats'	=> array(
						'title'	=> 'UCP_PHPBB_ADS_STATS',
						'auth'	=> 'ext_phpbb/ads && acl_u_phpbb_ads',
						'cat'	=> array('UCP_PHPBB_ADS_TITLE')
					),
				),
			),
		), $this->module_manager->get_module_infos('ucp', 'ucp_main_module'));
	}

	public function module_auth_test_data()
	{
		return array(
			// module_auth, expected result
			array('ext_foo/bar', false),
			array('ext_phpbb/ads', true),
		);
	}

	/**
	 * @dataProvider module_auth_test_data
	 */
	public function test_module_auth($module_auth, $expected)
	{
		$this->assertEquals($expected, p_master::module_auth($module_auth, 0));
	}

	public function test_main_module()
	{
		global $phpbb_container, $request, $template;

		define('IN_ADMIN', true);
		$request = $this->getMock('\phpbb\request\request');
		$template = $this->getMock('\phpbb\template\template');
		$phpbb_container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$ucp_controller = $this->getMockBuilder('\phpbb\ads\controller\ucp_controller')
			->disableOriginalConstructor()
			->getMock();

		$phpbb_container
			->expects($this->any())
			->method('get')
			->with('phpbb.ads.ucp.controller')
			->will($this->returnValue($ucp_controller));

		$ucp_controller
			->expects($this->once())
			->method('set_page_url');

		$ucp_controller
			->expects($this->once())
			->method('get_page_title');

		$ucp_controller
			->expects($this->once())
			->method('main');

		$p_master = new p_master();
		$p_master->load('acp', '\phpbb\ads\ucp\main_module', 'stats');
	}
}
