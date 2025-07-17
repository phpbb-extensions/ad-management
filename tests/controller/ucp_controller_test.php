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

use phpbb\ads\ad\manager;
use phpbb\ads\controller\helper;
use phpbb\ads\controller\ucp_controller;
use phpbb\config\config;
use phpbb\language\language;
use phpbb\language\language_file_loader;
use phpbb\template\template;
use phpbb\user;
use phpbb_database_test_case;
use PHPUnit\DbUnit\DataSet\DefaultDataSet;
use PHPUnit\DbUnit\DataSet\XmlDataSet;
use PHPUnit\Framework\MockObject\MockObject;

class ucp_controller_test extends phpbb_database_test_case
{
	/** @var MockObject|manager */
	protected manager|MockObject $manager;

	/** @var MockObject|helper */
	protected MockObject|helper $helper;

	/** @var MockObject|user */
	protected user|MockObject $user;

	/** @var MockObject|language */
	protected language|MockObject $language;

	/** @var MockObject|template */
	protected template|MockObject $template;

	/** @var MockObject|config */
	protected config|MockObject $config;

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

		global $phpbb_root_path, $phpEx;

		$lang_loader = new language_file_loader($phpbb_root_path, $phpEx);

		$this->manager = $this->getMockBuilder(manager::class)
			->disableOriginalConstructor()
			->getMock();
		$this->helper = $this->getMockBuilder(helper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->user = $this->getMockBuilder(user::class)
			->disableOriginalConstructor()
			->getMock();
		$this->language = new language($lang_loader);
		$this->template = $this->getMockBuilder(template::class)
			->disableOriginalConstructor()
			->getMock();
		$this->config = new config(array(
			'phpbb_ads_enable_views'	=> 0,
			'phpbb_ads_enable_clicks'	=> 0,
		));

		$this->u_action = $phpbb_root_path . 'ucp.php?i=-phpbb-ads-ucp-main_module&mode=stats';
	}

	/**
	 * Returns fresh new controller.
	 *
	 * @return	ucp_controller	UCP controller
	 */
	public function get_controller(): ucp_controller
	{
		$controller = new ucp_controller(
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
	 * Test data for the test_main() function
	 *
	 * @return array Array of test data
	 */
	public function main_data(): array
	{
		return array(
			array(1, 1, array(
				array(
					'ad_id'				=> 1,
					'ad_name'			=> 'First ad',
					'ad_views'			=> 0,
					'ad_clicks'			=> 0,
					'ad_enabled'		=> 1,
					'ad_start_date'		=> 0,
					'ad_end_date'		=> 0,
					'ad_views_limit'	=> 0,
					'ad_clicks_limit'	=> 0,
				),
				array(
					'ad_id'				=> 2,
					'ad_name'			=> 'Second ad',
					'ad_views'			=> 10,
					'ad_clicks'			=> 0,
					'ad_enabled'		=> 1,
					'ad_start_date'		=> 0,
					'ad_end_date'		=> 0,
					'ad_views_limit'	=> 0,
					'ad_clicks_limit'	=> 0,
				),
				array(
					'ad_id'				=> 3,
					'ad_name'			=> 'Third ad',
					'ad_views'			=> 20,
					'ad_clicks'			=> 10,
					'ad_enabled'		=> 1,
					'ad_start_date'		=> 0,
					'ad_end_date'		=> 0,
					'ad_views_limit'	=> 0,
					'ad_clicks_limit'	=> 0,
				),
				array(
					'ad_id'				=> 4,
					'ad_name'			=> 'Fourth ad',
					'ad_views'			=> 0,
					'ad_clicks'			=> 0,
					'ad_enabled'		=> 1,
					'ad_start_date'		=> 0,
					'ad_end_date'		=> 1,
					'ad_views_limit'	=> 0,
					'ad_clicks_limit'	=> 0,
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
		$this->user->data['user_id'] = 2;
		$this->config['phpbb_ads_enable_views'] = $enable_views;
		$this->config['phpbb_ads_enable_clicks'] = $enable_clicks;
		$controller = $this->get_controller();

		$this->manager->expects(self::once())
			->method('get_ads_by_owner')
			->willReturn($ads);

		$this->helper->expects(self::exactly(count($ads)))
			->method('is_expired')
			->willReturnOnConsecutiveCalls(false, false, false, true);

		$this->template->expects(self::exactly(count($ads)))
			->method('assign_block_vars');

		$this->template->expects(self::once())
			->method('assign_vars')
			->with(array(
				'S_VIEWS_ENABLED'	=> $enable_views,
				'S_CLICKS_ENABLED'	=> $enable_clicks,
			));

		$controller->main();
	}
}
