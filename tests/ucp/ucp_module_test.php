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
			$this->getMockBuilder('\phpbb\db\driver\driver_interface')->disableOriginalConstructor()->getMock(),
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
		self::assertEquals($expected, p_master::module_auth($module_auth, 0));
	}

	public function test_main_module()
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
		$ucp_controller = $this->getMockBuilder('\phpbb\ads\controller\ucp_controller')
			->disableOriginalConstructor()
			->getMock();

		$phpbb_container
			->expects(self::once())
			->method('get')
			->with('phpbb.ads.ucp.controller')
			->willReturn($ucp_controller);

		$ucp_controller
			->expects(self::once())
			->method('set_page_url');

		$ucp_controller
			->expects(self::once())
			->method('main');

		$p_master = new p_master();
		$p_master->module_ary[0]['is_duplicate'] = 0;
		$p_master->module_ary[0]['url_extra'] = '';
		$p_master->load('acp', '\phpbb\ads\ucp\main_module', 'stats');
	}
}
