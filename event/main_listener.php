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

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

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
	* @param \phpbb\request\request					$request			Request object
	* @param \phpbb\db\driver\driver_interface		$db					DB driver interface
	* @param \phpbb\template\template				$template			Template object
	* @param \phpbb\admanagement\ad\manager			$manager			Advertisement manager object
	* @param \phpbb\admanagement\location\manager	$location_manager	Template location manager object
	*/
	public function __construct(\phpbb\request\request $request, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\admanagement\ad\manager $manager, \phpbb\admanagement\location\manager $location_manager)
	{
		$this->request = $request;
		$this->db = $db;
		$this->template = $template;
		$this->manager = $manager;
		$this->location_manager = $location_manager;
	}

	/**
	 * Displays advertisements or preview one
	 */
	public function setup_ads()
	{
		$ad_preview = $this->request->variable('ad_preview', 0);

		if ($ad_preview)
		{
			$ad = $this->manager->get_ad($ad_preview);
			if (!empty($ad))
			{
				$this->template->assign_vars(array(
					'S_AD_PREVIEW'	=> true,
					'AD_CODE'		=> htmlspecialchars_decode($ad['ad_code']),
				));
			}
		}
		else
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
