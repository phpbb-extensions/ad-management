<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\location;

class manager
{
	/**
	 * Array that contains all available template location types which are passed
	 * via the service container
	 * @var array
	 */
	protected $template_locations;

	/**
	 * Construct an template locations manager object
	 *
	 * @param	array	$template_locations	Template location types passed via the service container
	 */
	public function __construct($template_locations)
	{
		$this->register_template_locations($template_locations);
	}

	/**
	 * Get a list of all template location types
	 *
	 * Returns an associated array where key is the location id
	 * and value is array of location name and location description.
	 *
	 * @return	array	Array containing a list of all template locations
	 */
	public function get_all_locations()
	{
		$location_types = array();

		foreach ($this->template_locations as $id => $location_type)
		{
			$location_types[$id] = array(
				'name'	=> $location_type->get_name(),
				'desc'	=> $location_type->get_desc(),
			);
		}

		return $location_types;
	}

	/**
	 * Get a list of all template location IDs for display
	 *
	 * @return	array	Array containing a list of all template location IDs
	 */
	public function get_all_location_ids()
	{
		$template_locations = array();

		foreach ($this->template_locations as $location_id => $location)
		{
			if ($location->will_display())
			{
				$template_locations[] = $location_id;
			}
		}

		return $template_locations;
	}

	/**
	 * Register template locations
	 *
	 * @param	array	$template_locations	Template location types passed via the service container
	 */
	protected function register_template_locations($template_locations)
	{
		if (!empty($template_locations))
		{
			foreach ($template_locations as $location)
			{
				$this->template_locations[$location->get_id()] = $location;
			}
		}
	}
}
