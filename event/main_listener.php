<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Advertisement management Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\template\context */
	protected $template_context;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\ads\ad\manager */
	protected $manager;

	/** @var \phpbb\ads\location\manager */
	protected $location_manager;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var string */
	protected $php_ext;

	/**
	 * {@inheritdoc}
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'core.permissions'				=> 'set_permissions',
			'core.user_setup'				=> 'load_language_on_setup',
			'core.page_footer_after'		=> array(array('setup_ads'), array('visual_demo')),
			'core.page_header_after'		=> array(array('adblocker'), array('clicks')),
			'core.delete_user_after'		=> 'remove_ad_owner',
			'core.adm_page_header_after'	=> 'disable_xss_protection',
		);
	}

	/**
	 * Constructor
	 *
	 * @param \phpbb\template\template				$template			Template object
	 * @param \phpbb\template\context				$template_context	Template context object
	 * @param \phpbb\user							$user				User object
	 * @param \phpbb\config\config					$config				Config object
	 * @param \phpbb\ads\ad\manager					$manager			Advertisement manager object
	 * @param \phpbb\ads\location\manager			$location_manager	Template location manager object
	 * @param \phpbb\controller\helper				$controller_helper	Controller helper object
	 * @param \phpbb\request\request				$request			Request object
	 * @param string								$php_ext			PHP extension
	 */
	public function __construct(\phpbb\template\template $template, \phpbb\template\context $template_context, \phpbb\user $user, \phpbb\config\config $config, \phpbb\ads\ad\manager $manager, \phpbb\ads\location\manager $location_manager, \phpbb\controller\helper $controller_helper, \phpbb\request\request $request, $php_ext)
	{
		$this->template = $template;
		$this->template_context = $template_context;
		$this->user = $user;
		$this->config = $config;
		$this->manager = $manager;
		$this->location_manager = $location_manager;
		$this->controller_helper = $controller_helper;
		$this->request = $request;
		$this->php_ext = $php_ext;
	}

	/**
	 * Wire up u_phpbb_ads permission
	 *
	 * @param	\phpbb\event\data	$event	The event object
	 * @return	void
	 */
	public function set_permissions($event)
	{
		$event->update_subarray('permissions', 'u_phpbb_ads', ['lang' => 'ACL_U_PHPBB_ADS', 'cat' => 'misc']);
	}

	/**
	 * Load common language file during user setup
	 *
	 * @param	\phpbb\event\data	$event	The event object
	 * @return	void
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'phpbb/ads',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Displays advertisements
	 *
	 * @return	void
	 */
	public function setup_ads()
	{
		// Reason we access template's root ref is to check for existence
		// of 'MESSAGE_TEXT', which signals error page.
		$rootref = $this->template_context->get_root_ref();
		$non_content_page = !empty($rootref['MESSAGE_TEXT']) || in_array($this->user->page['page_name'], array('ucp.' . $this->php_ext, 'mcp.' . $this->php_ext));
		$location_ids = $this->location_manager->get_all_location_ids();
		$user_groups = $this->manager->load_memberships($this->user->data['user_id']);
		$ad_ids = array();

		foreach ($this->manager->get_ads($location_ids, $user_groups, $non_content_page) as $row)
		{
			$ad_ids[] = $row['ad_id'];

			$this->template->assign_vars(array(
				'AD_' . strtoupper($row['location_id']) . '_ID'		=> $row['ad_id'],
				'AD_' . strtoupper($row['location_id'])				=> htmlspecialchars_decode($row['ad_code']),
				'AD_' . strtoupper($row['location_id']) . '_CENTER'	=> (bool) $row['ad_centering'],
			));
		}

		$this->views($ad_ids);
	}

	/**
	 * Display Ad blocker friendly message if allowed
	 *
	 * @return	void
	 */
	public function adblocker()
	{
		$this->template->assign_var('S_DISPLAY_ADBLOCKER', $this->config['phpbb_ads_adblocker_message']);
	}

	/**
	 * Add click tracking template variables
	 *
	 * @return	void
	 */
	public function clicks()
	{
		if ($this->config['phpbb_ads_enable_clicks'])
		{
			$this->template->assign_vars(array(
				'U_PHPBB_ADS_CLICK'		=> $this->controller_helper->route('phpbb_ads_click', array('data' => 0)),
				'S_PHPBB_ADS_ENABLE_CLICKS'	=> true,
			));
		}
	}

	/**
	 * Generate visual demo templates
	 *
	 * @return	void
	 */
	public function visual_demo()
	{
		if ($this->request->is_set($this->config['cookie_name'] . '_phpbb_ads_visual_demo', \phpbb\request\request_interface::COOKIE))
		{
			$all_locations = $this->location_manager->get_all_locations(false);
			foreach ($this->location_manager->get_all_location_ids() as $location_id)
			{
				$this->template->assign_vars(array(
					'AD_' . strtoupper($location_id) . '_ID'	=> $location_id,
					'AD_' . strtoupper($location_id)			=> '<div class="phpbb-ads-visual-demo" title="' . $all_locations[$location_id]['desc'] . '">' . $all_locations[$location_id]['name'] . '</div>',
				));
			}

			$this->template->assign_vars(array(
				'S_PHPBB_ADS_VISUAL_DEMO'	=> true,
				'U_DISABLE_VISUAL_DEMO'		=> $this->controller_helper->route('phpbb_ads_visual_demo', array('action' => 'disable')),
			));
		}
	}

	/**
	 * Prepare views counter template
	 *
	 * @param	array	$ad_ids	List of ads that will be displayed on current request's page
	 * @return	void
	 */
	protected function views($ad_ids)
	{
		if ($this->config['phpbb_ads_enable_views'] && !$this->user->data['is_bot'] && count($ad_ids))
		{
			$this->template->assign_vars(array(
				'S_INCREMENT_VIEWS'		=> true,
				'U_PHPBB_ADS_VIEWS'	=> $this->controller_helper->route('phpbb_ads_view', array('data' => implode('-', $ad_ids))),
			));
		}
	}

	/**
	 * Disable XSS Protection
	 * In Chrome browsers, previewing an Ad Code with javascript can
	 * be blocked, due to a false positive where Chrome thinks the
	 * javascript is an XSS injection. This will temporarily disable
	 * XSS protection in chrome while managing ads in the ACP.
	 *
	 * @param	\phpbb\event\data	$event	The event object
	 */
	public function disable_xss_protection($event)
	{
		if (stripos($this->user->browser, 'chrome') !== false &&
			stripos($this->user->page['page'], 'phpbb-ads') !== false)
		{
			$event['http_headers'] = array_merge($event['http_headers'], ['X-XSS-Protection' => '0']);
		}
	}

	/**
	 * Remove ad owner when deleting user(s)
	 *
	 * @param	\phpbb\event\data	$event	The event object
	 * @return	void
	 */
	public function remove_ad_owner($event)
	{
		$this->manager->remove_ad_owner($event['user_ids']);
	}
}
