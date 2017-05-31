<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\admanagement\ad;

class manager
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string */
	protected $ads_table;

	/** @var string */
	protected $ad_locations_table;

	/**
	* Constructor
	*
	* @param	\phpbb\db\driver\driver_interface	$db					DB driver interface
	* @param	string								$ads_table			Ads table
	* @param	string								$ad_locations_table	Ad locations table
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, $ads_table, $ad_locations_table)
	{
		$this->db = $db;
		$this->ads_table = $ads_table;
		$this->ad_locations_table = $ad_locations_table;
	}

	public function get_ad($ad_id)
	{
		$sql = 'SELECT *
			FROM ' . $this->ads_table . '
			WHERE ad_id = ' . (int) $ad_id;
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $data;
	}

	public function get_ads($ad_locations)
	{
		$sql = 'SELECT al.location_id, a.ad_code
			FROM ' . $this->ad_locations_table . ' al
			LEFT JOIN ' . $this->ads_table . ' a
				ON (al.ad_id = a.ad_id)
			WHERE a.ad_enabled = 1
				AND ' . $this->db->sql_in_set('al.location_id', $ad_locations) . '
			GROUP BY al.location_id';
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $data;
	}

	public function get_all_ads()
	{
		$sql = 'SELECT ad_id, ad_name, ad_enabled
			FROM ' . $this->ads_table;
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $data;
	}

	public function insert_ad($data)
	{
		$data = $this->intersect_ad_data($data);

		$sql = 'INSERT INTO ' . $this->ads_table . ' ' . $this->db->sql_build_array('INSERT', $data);
		$this->db->sql_query($sql);

		return $this->db->sql_nextid();
	}

	public function update_ad($ad_id, $data)
	{
		$data = $this->intersect_ad_data($data);

		$sql = 'UPDATE ' . $this->ads_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $data) . '
			WHERE ad_id = ' . (int) $ad_id;
		$this->db->sql_query($sql);

		return $this->db->sql_affectedrows();
	}

	public function delete_ad($ad_id)
	{
		$sql = 'DELETE FROM ' . $this->ads_table . '
			WHERE ad_id = ' . (int) $ad_id;
		$this->db->sql_query($sql);

		return $this->db->sql_affectedrows();
	}

	public function get_ad_locations($ad_id)
	{
		$ad_locations = array();

		$sql = 'SELECT location_id
			FROM ' . $this->ad_locations_table . '
			WHERE ad_id = ' . (int) $ad_id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ad_locations[] = $row['location_id'];
		}
		$this->db->sql_freeresult($result);

		return $ad_locations;
	}

	public function insert_ad_locations($ad_id, $ad_locations)
	{
		$sql_ary = array();
		foreach ($ad_locations as $ad_location)
		{
			$sql_ary[] = array(
				'ad_id'			=> $ad_id,
				'location_id'	=> $ad_location,
			);
		}
		$this->db->sql_multi_insert($this->ad_locations_table, $sql_ary);
	}

	public function delete_ad_locations($ad_id)
	{
		$sql = 'DELETE FROM ' . $this->ad_locations_table . '
			WHERE ad_id = ' . (int) $ad_id;
		$this->db->sql_query($sql);
	}

	// Make sure only necessary data make their way to SQL query
	protected function intersect_ad_data($data)
	{
		return array_intersect_key($data, array(
			'ad_name'		=> '',
			'ad_note'		=> '',
			'ad_code'		=> '',
			'ad_enabled'	=> '',
		));
	}
}
