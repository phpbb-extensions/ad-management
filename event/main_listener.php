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

	/** @var string ads_table */
	protected $ads_table;

	/**
	* {@inheritdoc}
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header_after'	=> 'ad_preview',
		);
	}

	/**
	* Constructor
	*
	* @param \phpbb\request\request				$request	Request object
	* @param \phpbb\db\driver\driver_interface	$db			DB driver interface
	* @param \phpbb\template\template			$template	Template object
	* @param string								$ads_table	Ads table
	*/
	public function __construct(\phpbb\request\request $request, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, $ads_table)
	{
		$this->request = $request;
		$this->db = $db;
		$this->template = $template;
		$this->ads_table = $ads_table;
	}

	/**
	 * Displays advertisement preview if requested
	 */
	public function ad_preview()
	{
		$ad_preview = $this->request->variable('ad_preview', 0);

		$sql = 'SELECT ad_code
			FROM ' . $this->ads_table . '
			WHERE ad_id = ' . (int) $ad_preview;
		$result = $this->db->sql_query($sql);
		$ad_code = $this->db->sql_fetchfield('ad_code', $result);
		$this->db->sql_freeresult($result);

		if (!empty($ad_code))
		{
			$this->template->assign_vars(array(
				'S_AD_PREVIEW'	=> true,
				'AD_CODE'		=> htmlspecialchars_decode($ad_code),
			));
		}
	}
}
