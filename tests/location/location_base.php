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

	/** @var \phpbb\request\request|\PHPUnit_Framework_MockObject_MockObject */
	protected $request;

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
	public function setUp()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$this->language = new \phpbb\language\language($lang_loader);
		$this->user = new \phpbb\user($this->language, '\phpbb\datetime');
		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$config = new \phpbb\config\config(array());
		$template = $this->getMockBuilder('\phpbb\template\template')
			->disableOriginalConstructor()
			->getMock();
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
			'before_posts',
			'before_profile',
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
			if ($type === 'pop_up')
			{
				$location_types['phpbb.ads.location.type.' . $type] = new $class($this->user, $this->language, $this->request, $config, $template);
			}
			else
			{
				$location_types['phpbb.ads.location.type.' . $type] = new $class($this->user, $this->language);
			}
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
