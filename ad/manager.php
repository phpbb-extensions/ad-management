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

	/**
	* Get specific ad
	*
	* @param	int		$ad_id	Advertisement ID
	* @return	array	Advertisement data
	*/
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

	/**
	* Get one ad per every location
	*
	* @param	array	$ad_locations	List of ad locations to fetch ads for
	* @return	array	List of ad codes for each location
	*/
	public function get_ads($ad_locations)
	{
		$sql = 'SELECT location_id, ad_code
			FROM (
				SELECT al.location_id, a.ad_code
				FROM ' . $this->ad_locations_table . ' al
				LEFT JOIN ' . $this->ads_table . ' a
					ON (al.ad_id = a.ad_id)
				WHERE a.ad_enabled = 1
					AND (a.ad_end_date = 0
						OR a.ad_end_date > ' . time() . ')
					AND ' . $this->db->sql_in_set('al.location_id', $ad_locations) . '
				ORDER BY ' . $this->sql_random() . '
			) z
			ORDER BY z.location_id';
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $data;
	}

	/**
	* Get all advertisements
	*
	* @return	array	List of all ads
	*/
	public function get_all_ads()
	{
		$sql = 'SELECT ad_id, ad_name, ad_enabled, ad_end_date
			FROM ' . $this->ads_table;
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $data;
	}

	/**
	* Insert new advertisement to the database
	*
	* @param	array	$data	New ad data
	* @return	int		New advertisement ID
	*/
	public function insert_ad($data)
	{
		$data = $this->intersect_ad_data($data);

		$sql = 'INSERT INTO ' . $this->ads_table . ' ' . $this->db->sql_build_array('INSERT', $data);
		$this->db->sql_query($sql);

		return $this->db->sql_nextid();
	}

	/**
	* Update advertisement
	*
	* @param	int		$ad_id	Advertisement ID
	* @param	array	$data	List of data to update in the database
	* @return	int		Number of affected rows. Can be used to determine if any ad has been updated.
	*/
	public function update_ad($ad_id, $data)
	{
		$data = $this->intersect_ad_data($data);

		$sql = 'UPDATE ' . $this->ads_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $data) . '
			WHERE ad_id = ' . (int) $ad_id;
		$this->db->sql_query($sql);

		return $this->db->sql_affectedrows();
	}

	/**
	* Delete advertisement
	*
	* @param	int		$ad_id	Advertisement ID
	* @return	int		Number of affected rows. Can be used to determine if any ad has been deleted.
	*/
	public function delete_ad($ad_id)
	{
		$sql = 'DELETE FROM ' . $this->ads_table . '
			WHERE ad_id = ' . (int) $ad_id;
		$this->db->sql_query($sql);

		return $this->db->sql_affectedrows();
	}

	/**
	* Get all locations for specified advertisement
	*
	* @param	int		$ad_id	Advertisement ID
	* @return	array	List of template locations for specified ad
	*/
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

	/**
	* Insert advertisement locations
	*
	* @param	int		$ad_id			Advertisement ID
	* @param	array	$ad_locations	List of template locations for this ad
	* @return	void
	*/
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

	/**
	* Delete advertisement locations
	*
	* @param	int		$ad_id	Advertisement ID
	* @return	void
	*/
	public function delete_ad_locations($ad_id)
	{
		$sql = 'DELETE FROM ' . $this->ad_locations_table . '
			WHERE ad_id = ' . (int) $ad_id;
		$this->db->sql_query($sql);
	}

	/**
	* Make sure only necessary data make their way to SQL query
	*
	* @param	array	$data	List of data to query the database
	* @return	array	Cleaned data that contain only valid keys
	*/
	protected function intersect_ad_data($data)
	{
		return array_intersect_key($data, array(
			'ad_name'		=> '',
			'ad_note'		=> '',
			'ad_code'		=> '',
			'ad_enabled'	=> '',
			'ad_end_date'	=> '',
		));
	}

	/**
	* Get the random statement for this database layer
	*
	* @return	string	Random statement for current database layer
	*/
	protected function sql_random()
	{
		switch ($this->db->get_sql_layer())
		{
			case 'oracle':
			case 'postgres':
			case 'sqlite':
			case 'sqlite3':
				return 'RANDOM()';

			/* All other cases should use the default
			case 'mssql':
			case 'mssql_odbc':
			case 'mssqlnative':
			case 'mysql':
			case 'mysqli':*/
			default:
				return 'RAND()';
		}
	}
}
