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

class location_base extends \phpbb_test_case
{
	/** @var array */
	protected $template_locations;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	protected static function setup_extensions()
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

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$this->language = new \phpbb\language\language($lang_loader);
		$this->user = new \phpbb\user($this->language, '\phpbb\datetime');
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
	 * @return    \phpbb\ads\location\manager    Location manager
	 */
	public function get_manager()
	{
		return new \phpbb\ads\location\manager($this->template_locations);
	}
}
