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

	/** @var \phpbb\admanagement\location\manager */
	protected $location_manager;

	/** @var string ads_table */
	protected $ads_table;

	/** @var string ad_locations_table */
	protected $ad_locations_table;

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
	* @param \phpbb\admanagement\location\manager	$location_manager	Template location manager object
	* @param string									$ads_table			Ads table
	* @param string									$ad_locations_table	Ad locations table
	*/
	public function __construct(\phpbb\request\request $request, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\admanagement\location\manager $location_manager, $ads_table, $ad_locations_table)
	{
		$this->request = $request;
		$this->db = $db;
		$this->template = $template;
		$this->location_manager = $location_manager;
		$this->ads_table = $ads_table;
		$this->ad_locations_table = $ad_locations_table;
	}

	/**
	 * Displays advertisements or preview one
	 */
	public function setup_ads()
	{
		$ad_preview = $this->request->variable('ad_preview', 0);

		if ($ad_preview)
		{
			$sql = 'SELECT ad_code
				FROM ' . $this->ads_table . '
				WHERE ad_id = ' . (int) $ad_preview;
			$result = $this->db->sql_query($sql);
			$ad_code = $this->db->sql_fetchfield('ad_code');
			$this->db->sql_freeresult($result);

			if (!empty($ad_code))
			{
				$this->template->assign_vars(array(
					'S_AD_PREVIEW'	=> true,
					'AD_CODE'		=> htmlspecialchars_decode($ad_code),
				));
			}
		}
		else
		{
			$location_ids = $this->location_manager->get_all_location_ids();

			$sql = 'SELECT al.location_id, a.ad_code
				FROM ' . $this->ad_locations_table . ' al
				LEFT JOIN ' . $this->ads_table . ' a
					ON (al.ad_id = a.ad_id)
				WHERE a.ad_enabled = 1
					AND ' . $this->db->sql_in_set('al.location_id', $location_ids) . '
				GROUP BY al.location_id';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->template->assign_vars(array(
					'AD_' . strtoupper($row['location_id'])	=> $row['ad_code'],
				));
			}
		}
	}
}
