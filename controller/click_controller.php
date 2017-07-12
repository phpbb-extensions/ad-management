<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\controller;

/**
* Front controller
*/
class click_controller
{
	/** @var \phpbb\ads\ad\manager */
	protected $manager;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/**
	* Constructor
	*
	* @param \phpbb\ads\ad\manager		$manager	Advertisement manager object
	* @param \phpbb\controller\helper	$helper		Controller helper object
	*/
	public function __construct(\phpbb\ads\ad\manager $manager, \phpbb\controller\helper $helper)
	{
		$this->manager = $manager;
		$this->helper = $helper;
	}

	/**
	* Increment clicks for an ad
	*
	* @param	int	$ad_id	Advertisement ID
	* @return	\Symfony\Component\HttpFoundation\Response	A Symfony Response object
	*/
	public function increment_clicks($ad_id)
	{
		if (!$ad_id)
		{
			throw new \phpbb\exception\http_exception(404, 'NOT_FOUND');
		}

		$this->manager->increment_ad_clicks($ad_id);

		return $this->helper->message('');
	}
}