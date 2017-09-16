<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\ad;

class manager
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var string */
	protected $ads_table;

	/** @var string */
	protected $ad_locations_table;

	/**
	 * Constructor
	 *
	 * @param    \phpbb\db\driver\driver_interface $db                 DB driver interface
	 * @param    \phpbb\config\config              $config             Config object
	 * @param    string                            $ads_table          Ads table
	 * @param    string                            $ad_locations_table Ad locations table
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, $ads_table, $ad_locations_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->ads_table = $ads_table;
		$this->ad_locations_table = $ad_locations_table;
	}

	/**
	 * Get specific ad
	 *
	 * @param	int	$ad_id	Advertisement ID
	 * @return	array	Array with advertisement data
	 */
	public function get_ad($ad_id)
	{
		$sql = 'SELECT *
			FROM ' . $this->ads_table . '
			WHERE ad_id = ' . (int) $ad_id;
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $data !== false ? $data : array();
	}

	/**
	 * Get one ad per every location
	 *
	 * @param    array $ad_locations List of ad locations to fetch ads for
	 * @return    array    List of ad codes for each location
	 */
	public function get_ads($ad_locations)
	{
		$sql_where_views = $this->config['phpbb_ads_enable_views'] ? 'AND (a.ad_views_limit = 0 OR a.ad_views_limit > a.ad_views)' : '';
		$sql_where_clicks = $this->config['phpbb_ads_enable_clicks'] ? 'AND (a.ad_clicks_limit = 0 OR a.ad_clicks_limit > a.ad_clicks)' : '';

		$sql = 'SELECT location_id, ad_id, ad_code
			FROM (
				SELECT al.location_id, a.ad_id, a.ad_code
				FROM ' . $this->ad_locations_table . ' al
				LEFT JOIN ' . $this->ads_table . ' a
					ON (al.ad_id = a.ad_id)
				WHERE a.ad_enabled = 1
					AND (a.ad_end_date = 0
						OR a.ad_end_date > ' . time() . ")
					$sql_where_views
					$sql_where_clicks
					AND " . $this->db->sql_in_set('al.location_id', $ad_locations) . '
				ORDER BY (' . $this->sql_random() . ' * a.ad_priority) DESC
			) z
			ORDER BY z.location_id';
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$current_location_id = '';
		$data = array_filter($data, function ($row) use (&$current_location_id) {
			$return = $current_location_id !== $row['location_id'];
			$current_location_id = $row['location_id'];
			return $return;
		});

		return $data;
	}

	/**
	 * Get all advertisements.
	 *
	 * @return    array    List of all ads
	 */
	public function get_all_ads()
	{
		$sql = 'SELECT ad_id, ad_priority, ad_name, ad_enabled, ad_end_date, ad_views, ad_clicks, ad_views_limit, ad_clicks_limit
			FROM ' . $this->ads_table;
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $data;
	}

	/**
	 * Get all owner's ads
	 *
	 * @param    int $user_id Ad owner
	 * @return    array    List of owner's ads
	 */
	public function get_ads_by_owner($user_id)
	{
		$sql = 'SELECT ad_name, ad_views, ad_clicks
			FROM ' . $this->ads_table . '
			WHERE ad_owner = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $data;
	}

	/**
	 * Increment views for specified ads
	 *
	 * Note, that views are incremented only by one even when
	 * an ad is displayed multiple times on the same page.
	 *
	 * @param    array $ad_ids IDs of ads to increment views
	 * @return    void
	 */
	public function increment_ads_views($ad_ids)
	{
		if (!empty($ad_ids))
		{
			$sql = 'UPDATE ' . $this->ads_table . '
				SET ad_views = ad_views + 1
				WHERE ' . $this->db->sql_in_set('ad_id', $ad_ids);
			$this->db->sql_query($sql);
		}
	}

	/**
	 * Increment clicks for specified ad
	 *
	 * @param    int $ad_id ID of an ad to increment clicks
	 * @return    void
	 */
	public function increment_ad_clicks($ad_id)
	{
		$sql = 'UPDATE ' . $this->ads_table . '
			SET ad_clicks = ad_clicks + 1
			WHERE ad_id = ' . (int) $ad_id;
		$this->db->sql_query($sql);
	}

	/**
	 * Insert new advertisement to the database
	 *
	 * @param    array $data New ad data
	 * @return    int        New advertisement ID
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
	 * @param    int   $ad_id Advertisement ID
	 * @param    array $data  List of data to update in the database
	 * @return    int        Number of affected rows. Can be used to determine if any ad has been updated.
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
	 * @param    int $ad_id Advertisement ID
	 * @return    int        Number of affected rows. Can be used to determine if any ad has been deleted.
	 */
	public function delete_ad($ad_id)
	{
		$sql = 'DELETE FROM ' . $this->ads_table . '
			WHERE ad_id = ' . (int) $ad_id;
		$this->db->sql_query($sql);

		return $this->db->sql_affectedrows();
	}

	/**
	 * Remove ad owner
	 *
	 * @param    array $user_ids User IDs
	 * @return    void
	 */
	public function remove_ad_owner(array $user_ids)
	{
		if (empty($user_ids))
		{
			return;
		}

		$sql = 'UPDATE ' . $this->ads_table . '
			SET ad_owner = 0
			WHERE ' . $this->db->sql_in_set('ad_owner', $user_ids);
		$this->db->sql_query($sql);
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
	 * Load memberships of the user
	 *
	 * @param	int		$user_id	User ID to load memberships
	 * @return	array	List of group IDs user is member of
	 */
	public function load_memberships($user_id)
	{
		$memberships = array();
		$sql = 'SELECT group_id
			FROM ' . USER_GROUP_TABLE . '
			WHERE user_id = ' . (int) $user_id . '
			AND user_pending = 0';
		$result = $this->db->sql_query($sql, 3600);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$memberships[] = $row['group_id'];
		}
		$this->db->sql_freeresult($result);
		return $memberships;
	}

	/**
	 * Load all board groups
	 *
	 * @return	array	List of groups
	 */
	public function load_groups()
	{
		$sql = 'SELECT group_id, group_name, group_type
			FROM ' . GROUPS_TABLE . "
			WHERE group_name <> 'BOTS'
			ORDER BY group_name ASC";
		$result = $this->db->sql_query($sql);
		$groups = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $groups;
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
			'ad_name'			=> '',
			'ad_note'			=> '',
			'ad_code'			=> '',
			'ad_enabled'		=> '',
			'ad_end_date'		=> '',
			'ad_priority'		=> '',
			'ad_views_limit'	=> '',
			'ad_clicks_limit'	=> '',
			'ad_owner'			=> '',
		));
	}

	/**
	 * Get the random statement for this database layer
	 * Random function should generate a float value between 0 and 1
	 *
	 * @return	string	Random statement for current database layer
	 */
	protected function sql_random()
	{
		switch ($this->db->get_sql_layer())
		{
			case 'oracle':
				return 'VALUE()';

			case 'postgres':
				return 'RANDOM()';

			// https://stackoverflow.com/a/35369410/2908600
			case 'sqlite':
			case 'sqlite3':
				return '(0.5 - RANDOM() / CAST(-9223372036854775808 AS REAL) / 2)';

			default:
				return 'RAND()';
		}
	}
}
