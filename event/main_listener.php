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

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\ads\ad\manager */
	protected $manager;

	/** @var \phpbb\ads\location\manager */
	protected $location_manager;

	/**
	* {@inheritdoc}
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'			=> 'load_language_on_setup',
			'core.page_header_after'	=> 'setup_ads',
		);
	}

	/**
	* Constructor
	*
	* @param \phpbb\template\template				$template			Template object
	* @param \phpbb\user							$user				User object
	* @param \phpbb\config\db_text					$config_text		Config text object
	* @param \phpbb\config\config					$config				Config object
	* @param \phpbb\ads\ad\manager					$manager			Advertisement manager object
	* @param \phpbb\ads\location\manager			$location_manager	Template location manager object
	*/
	public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\config\db_text $config_text, \phpbb\config\config $config, \phpbb\ads\ad\manager $manager, \phpbb\ads\location\manager $location_manager)
	{
		$this->template = $template;
		$this->user = $user;
		$this->config_text = $config_text;
		$this->config = $config;
		$this->manager = $manager;
		$this->location_manager = $location_manager;
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
	 */
	public function setup_ads()
	{
		$user_groups = $this->manager->load_memberships($this->user->data['user_id']);
		$hide_groups = json_decode($this->config_text->get('phpbb_ads_hide_groups'), true);

		// If user is not in any groups that have ads hidden, display them then
		if (!array_intersect($user_groups, $hide_groups))
		{
			$location_ids = $this->location_manager->get_all_location_ids();

			foreach ($this->manager->get_ads($location_ids) as $row)
			{
				$this->template->assign_vars(array(
					'AD_' . strtoupper($row['location_id'])	=> htmlspecialchars_decode($row['ad_code']),
				));
			}
		}

		// Display Ad blocker friendly message if allowed
		$this->template->assign_var('S_DISPLAY_ADBLOCKER', $this->config['phpbb_ads_adblocker_message']);
	}
}
