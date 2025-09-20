<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\event;

use phpbb\ads\ad\manager as ad_manager;
use phpbb\ads\event\main_listener;
use phpbb\ads\location\manager as location_manager;
use phpbb\cache\driver\driver_interface as cache;
use phpbb\config\config;
use phpbb\config\db_text;
use phpbb\controller\helper as controller_helper;
use phpbb\language\language;
use phpbb\language\language_file_loader;
use phpbb\request\request;
use phpbb\template\context;
use phpbb\template\template;
use phpbb\user;
use phpbb_database_test_case;
use phpbb_mock_event_dispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use phpbb\path_helper;
use phpbb\datetime;
use phpbb\cache\driver\dummy;

class main_listener_base extends phpbb_database_test_case
{
	/** @var db_text|MockObject */
	protected db_text|MockObject $config_text;

	/** @var template|MockObject */
	protected template|MockObject $template;

	/** @var user|MockObject */
	protected user|MockObject $user;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var string ads_table */
	protected string $ads_table;

	/** @var config */
	protected config $config;

	/** @var ad_manager */
	protected ad_manager $manager;

	/** @var location_manager */
	protected location_manager $location_manager;

	/** @var controller_helper|MockObject */
	protected controller_helper|MockObject $controller_helper;

	/** @var request|MockObject */
	protected request|MockObject $request;

	/** @var cache|MockObject */
	protected cache|MockObject $cache;

	/** @var string */
	protected string $php_ext;

	/** @var string */
	protected string $ad_locations_table;

	/** @var string */
	protected string $ad_group_table;

	/** @var array */
	protected array $locations;

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

		global $user, $phpbb_path_helper, $phpbb_root_path, $phpEx, $phpbb_dispatcher;

		$phpbb_path_helper = $this->getMockBuilder(path_helper::class)
			->disableOriginalConstructor()
			->getMock();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$lang_loader = new language_file_loader($phpbb_root_path, $phpEx);
		$this->language = new language($lang_loader);
		$user = new user($this->language, datetime::class);
		$request = $this->getMockBuilder(request::class)
			->disableOriginalConstructor()
			->getMock();
		$config = new config(array());
		$template = $this->getMockBuilder(template::class)
			->disableOriginalConstructor()
			->getMock();
		$this->ads_table = 'phpbb_ads';
		$this->ad_locations_table = 'phpbb_ad_locations';
		$this->ad_group_table = 'phpbb_ad_group';
		// Location types
		$this->locations = array(
			'above_footer',
			'above_header',
			'after_footer_navbar',
			'after_header_navbar',
			'after_first_post',
			'after_not_first_post',
			'after_posts',
			'after_profile',
			'after_quickreply',
			'before_posts',
			'before_profile',
			'before_quickreply',
			'below_footer',
			'below_header',
			'pop_up',
			'slide_up',
		);
		$location_types = array();
		foreach ($this->locations as $type)
		{
			$class = "\\phpbb\\ads\\location\\type\\$type";
			if ($type === 'pop_up')
			{
				$location_types['phpbb.ads.location.type.' . $type] = new $class($user, $this->language, $request, $config, $template);
			}
			else
			{
				$location_types['phpbb.ads.location.type.' . $type] = new $class($user, $this->language);
			}
		}

		// Load/Mock classes required by the listener class
		$this->template = $this->getMockBuilder(template::class)
			->disableOriginalConstructor()
			->getMock();
		$this->user = $user;
		$this->config = new config(array('phpbb_ads_adblocker_message' => '0'));
		$this->manager = new ad_manager($this->new_dbal(), $this->config, $this->ads_table, $this->ad_locations_table, $this->ad_group_table);
		$this->location_manager = new location_manager($location_types);
		$this->controller_helper = $this->getMockBuilder(controller_helper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->request = $request;
		$this->cache = $this->getMockBuilder(dummy::class)
			->disableOriginalConstructor()
			->getMock();
		$this->php_ext = $phpEx;
	}

	/**
	* Get the event listener
	*
	* @return main_listener
	*/
	protected function get_listener(): main_listener
	{
		return new main_listener(
			$this->language,
			$this->template,
			$this->user,
			$this->config,
			$this->manager,
			$this->location_manager,
			$this->controller_helper,
			$this->request,
			$this->cache,
			$this->php_ext
		);
	}
}
