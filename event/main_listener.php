<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\admanagement\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Advertisement management Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\admanagement\ad\manager */
	protected $manager;

	/** @var \phpbb\admanagement\location\manager */
	protected $location_manager;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/** @var int */
	protected $ad_preview;

	/**
	* {@inheritdoc}
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header_after'	=> array(array('stop_previewing_ad'), array('preview_ad'), array('setup_ads')),
		);
	}

	/**
	* Constructor
	*
	* @param \phpbb\request\request					$request			Request object
	* @param \phpbb\template\template				$template			Template object
	* @param \phpbb\user							$user				User object
	* @param \phpbb\config\config					$config				Config object
	* @param \phpbb\admanagement\ad\manager			$manager			Advertisement manager object
	* @param \phpbb\admanagement\location\manager	$location_manager	Template location manager object
	* @param string									$root_path			phpBB root path
	* @param string									$php_ext			PHP extension
	*/
	public function __construct(\phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \phpbb\config\config $config, \phpbb\admanagement\ad\manager $manager, \phpbb\admanagement\location\manager $location_manager, $root_path, $php_ext)
	{
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->config = $config;
		$this->manager = $manager;
		$this->location_manager = $location_manager;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Stop previewing advertisement
	 */
	public function stop_previewing_ad()
	{
		if ($this->request->variable('stop_ad_preview', false))
		{
			$this->user->set_cookie('ad_preview', $ad_preview, time() - 86400);

			// This will attempt to close the window automatically. Since we opened this
			// window by clicking on preview icon in ACP, browser will allow us to do it.
			$this->template->assign_vars(array(
				'S_STOP_PREVIEWING_AD'	=> true,
			));
		}
	}

	/**
	 * Preview advertisement
	 */
	public function preview_ad()
	{
		$ad_preview = $this->request->variable('ad_preview', 0);
		if ($ad_preview)
		{
			$this->user->set_cookie('ad_preview', $ad_preview, time() + 86400);
		}
		else
		{
			$ad_preview = $this->request->variable($this->config['cookie_name'] . '_ad_preview', 0, true, \phpbb\request\request_interface::COOKIE);
		}

		if ($ad_preview)
		{
			$this->user->add_lang_ext('phpbb/admanagement', 'common');

			$location_ids = $this->manager->get_ad_locations($ad_preview);
			$ad = $this->manager->get_ad($ad_preview);

			foreach ($location_ids as $location_id)
			{
				$this->template->assign_vars(array(
					'AD_' . strtoupper($location_id)	=> htmlspecialchars_decode($ad['ad_code']),
				));
			}

			$this->template->assign_vars(array(
				'S_PREVIEWING_AD'	=> true,
				'L_PREVIEWING_AD'	=> $this->user->lang('PREVIEWING_AD', append_sid($this->root_path . 'index.' . $this->php_ext, 'stop_ad_preview=true')),
			));
		}
	}

	/**
	 * Displays advertisements
	 */
	public function setup_ads()
	{
		if (!$this->request->variable('ad_preview', 0))
		{
			$location_ids = $this->location_manager->get_all_location_ids();

			foreach ($this->manager->get_ads($location_ids) as $row)
			{
				$this->template->assign_vars(array(
					'AD_' . strtoupper($row['location_id'])	=> htmlspecialchars_decode($row['ad_code']),
				));
			}
		}
	}
}
