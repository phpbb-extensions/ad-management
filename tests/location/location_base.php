<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\location;

use phpbb\ads\location\manager;
use phpbb\config\config;
use phpbb\language\language;
use phpbb\language\language_file_loader;
use phpbb\request\request;
use phpbb\user;
use phpbb_test_case;
use PHPUnit\Framework\MockObject\MockObject;
use phpbb\datetime;
use phpbb\template\template;

class location_base extends phpbb_test_case
{
	/** @var array */
	protected array $template_locations;

	/** @var user */
	protected user $user;

	/** @var language */
	protected language $language;

	protected static function setup_extensions(): array
	{
		return array('phpbb/ads');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$lang_loader = new language_file_loader($phpbb_root_path, $phpEx);
		$this->language = new language($lang_loader);
		$this->user = new user($this->language, datetime::class);
		// Location types
		$locations = array(
			'above_footer',
			'above_header',
			'after_first_post',
			'after_footer_navbar',
			'after_header_navbar',
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
			'scripts',
			'slide_up',
		);
		$location_types = array();
		foreach ($locations as $type)
		{
			$class = "\\phpbb\\ads\\location\\type\\$type";
			$location_types['phpbb.ads.location.type.' . $type] = new $class($this->user, $this->language);
		}

		$this->template_locations = $location_types;
	}

	/**
	 * Returns fresh new location manager.
	 *
	 * @return    manager    Location manager
	 */
	public function get_manager(): manager
	{
		return new manager($this->template_locations);
	}
}
