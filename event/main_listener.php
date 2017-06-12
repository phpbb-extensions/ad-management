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
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\admanagement\ad\manager */
	protected $manager;

	/** @var \phpbb\admanagement\location\manager */
	protected $location_manager;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
	* {@inheritdoc}
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header_after'	=> 'setup_ads',
		);
	}

	/**
	* Constructor
	*
	* @param \phpbb\template\template				$template			Template object
	* @param \phpbb\user\user						$user				User object
	* @param \phpbb\config\db_text					$config_text		Config text object
	* @param \phpbb\admanagement\ad\manager			$manager			Advertisement manager object
	* @param \phpbb\admanagement\location\manager	$location_manager	Template location manager object
	* @param string									$root_path			phpBB root path
	* @param string									$php_ext			PHP extension
	*/
	public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\config\db_text $config_text, \phpbb\admanagement\ad\manager $manager, \phpbb\admanagement\location\manager $location_manager, $root_path, $php_ext)
	{
		$this->template = $template;
		$this->user = $user;
		$this->config_text = $config_text;
		$this->manager = $manager;
		$this->location_manager = $location_manager;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Displays advertisements
	 */
	public function setup_ads()
	{
		$user_groups = $this->manager->load_memberships($this->user->data['user_id']);
		$hide_groups = json_decode($this->config_text->get('phpbb_admanagement_hide_groups'), true);

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
	}
}
