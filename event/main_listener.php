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

	/** @var \phpbb\admanagement\ad\manager */
	protected $manager;

	/** @var \phpbb\admanagement\location\manager */
	protected $location_manager;

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
	* @param \phpbb\admanagement\ad\manager			$manager			Advertisement manager object
	* @param \phpbb\admanagement\location\manager	$location_manager	Template location manager object
	*/
	public function __construct(\phpbb\template\template $template, \phpbb\admanagement\ad\manager $manager, \phpbb\admanagement\location\manager $location_manager)
	{
		$this->template = $template;
		$this->manager = $manager;
		$this->location_manager = $location_manager;
	}

	/**
	 * Displays advertisements
	 */
	public function setup_ads()
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
