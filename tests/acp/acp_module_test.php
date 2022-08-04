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

class acp_module_test extends \phpbb_test_case
{
	/** @var \phpbb_mock_extension_manager */
	protected $extension_manager;

	/** @var \phpbb\module\module_manager */
	protected $module_manager;

	protected function setUp(): void
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
			$this->getMockBuilder('\phpbb\db\driver\driver_interface')->getMock(),
			$this->extension_manager,
			MODULES_TABLE,
			$phpbb_root_path,
			$phpEx
		);

		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
	}

	public function test_module_info()
	{
		self::assertEquals(array(
			'\\phpbb\\ads\\acp\\main_module' => array(
				'filename'	=> '\\phpbb\\ads\\acp\\main_module',
				'title'		=> 'ACP_PHPBB_ADS_TITLE',
				'modes'		=> array(
					'manage'	=> array(
						'title'	=> 'ACP_MANAGE_ADS_TITLE',
						'auth'	=> 'ext_phpbb/ads && acl_a_phpbb_ads_m',
						'cat'	=> array('ACP_PHPBB_ADS_TITLE')
					),
					'settings'	=> array(
						'title'	=> 'ACP_ADS_SETTINGS_TITLE',
						'auth'	=> 'ext_phpbb/ads && acl_a_phpbb_ads_s',
						'cat'	=> array('ACP_PHPBB_ADS_TITLE')
					),
				),
			),
		), $this->module_manager->get_module_infos('acp', 'acp_main_module'));
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
		self::assertEquals($expected, p_master::module_auth($module_auth, 0));
	}

	public function main_module_test_data()
	{
		return array(
			array('manage'),
			array('settings'),
		);
	}

	/**
	 * @dataProvider main_module_test_data
	 */
	public function test_main_module($mode)
	{
		global $phpbb_container, $request, $template;

		if (!defined('IN_ADMIN'))
		{
			define('IN_ADMIN', true);
		}

		$request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$template = $this->getMockBuilder('\phpbb\template\template')
			->disableOriginalConstructor()
			->getMock();
		$phpbb_container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
			->disableOriginalConstructor()
			->getMock();
		$admin_controller = $this->getMockBuilder('\phpbb\ads\controller\admin_controller')
			->disableOriginalConstructor()
			->getMock();

		$phpbb_container
			->expects(self::once())
			->method('get')
			->with('phpbb.ads.admin.controller')
			->willReturn($admin_controller);

		$admin_controller
			->expects(self::once())
			->method('set_page_url');

		$admin_controller
			->expects(self::once())
			->method("mode_$mode");

		$p_master = new p_master();
		$p_master->module_ary[0]['is_duplicate'] = 0;
		$p_master->module_ary[0]['url_extra'] = '';
		$p_master->load('acp', '\phpbb\ads\acp\main_module', $mode);
	}
}
