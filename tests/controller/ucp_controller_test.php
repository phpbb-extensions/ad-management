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

class ucp_controller_test extends \phpbb_database_test_case
{
	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\ads\ad\manager */
	protected $manager;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\ads\controller\helper */
	protected $helper;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\user */
	protected $user;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\language\language */
	protected $language;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\template\template */
	protected $template;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\config\config */
	protected $config;

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
	public function setUp(): void
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);

		$this->manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->helper = $this->getMockBuilder('\phpbb\ads\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$this->user = $this->getMockBuilder('\phpbb\user')
			->disableOriginalConstructor()
			->getMock();
		$this->language = new \phpbb\language\language($lang_loader);
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->disableOriginalConstructor()
			->getMock();
		$this->config = new \phpbb\config\config(array(
			'phpbb_ads_enable_views'	=> 0,
			'phpbb_ads_enable_clicks'	=> 0,
		));

		$this->u_action = $phpbb_root_path . 'ucp.php?i=-phpbb-ads-ucp-main_module&mode=stats';
	}

	/**
	 * Returns fresh new controller.
	 *
	 * @return	\phpbb\ads\controller\ucp_controller	UCP controller
	 */
	public function get_controller()
	{
		$controller = new \phpbb\ads\controller\ucp_controller(
			$this->manager,
			$this->helper,
			$this->user,
			$this->language,
			$this->template,
			$this->config
		);
		$controller->set_page_url($this->u_action);

		return $controller;
	}

	/**
	 * Test get_page_title() method
	 */
	public function test_get_page_title()
	{
		$controller = $this->get_controller();

		$this->assertEquals($this->language->lang('UCP_PHPBB_ADS_STATS'), $controller->get_page_title());
	}

	/**
	 * Test data for the test_main() function
	 *
	 * @return array Array of test data
	 */
	public function main_data()
	{
		return array(
			array(1, 1, array(
				array(
					'ad_name'	=> 'First ad',
					'ad_views'	=> '0',
					'ad_cliks'	=> '0',
				),
				array(
					'ad_name'	=> 'Second ad',
					'ad_views'	=> '10',
					'ad_cliks'	=> '0',
				),
				array(
					'ad_name'	=> 'Third ad',
					'ad_views'	=> '20',
					'ad_cliks'	=> '10',
				),
			)),
			array(1, 0, array()),
			array(0, 1, array()),
			array(0, 0, array()),
		);
	}
	/**
	 * Test main() method
	 *
	 * @dataProvider main_data
	 */
	public function test_main($enable_views, $enable_clicks, $ads)
	{
		$this->config['phpbb_ads_enable_views'] = $enable_views;
		$this->config['phpbb_ads_enable_clicks'] = $enable_clicks;
		$controller = $this->get_controller();

		$this->manager->expects($this->once())
			->method('get_ads_by_owner')
			->willReturn($ads);

		$this->template->expects($this->exactly(count($ads)))
			->method('assign_block_vars');

		$this->template->expects($this->once())
			->method('assign_vars')
			->with(array(
				'S_VIEWS_ENABLED'	=> $enable_views,
				'S_CLICKS_ENABLED'	=> $enable_clicks,
			));

		$controller->main();
	}
}
