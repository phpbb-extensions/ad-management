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

	/** @var \phpbb\request\request */
	protected $request;

	/**
	 * Constructor
	 *
	 * @param \phpbb\ads\ad\manager    $manager Advertisement manager object
	 * @param \phpbb\request\request   $request	Request object
	 */
	public function __construct(\phpbb\ads\ad\manager $manager, \phpbb\request\request $request)
	{
		$this->manager = $manager;
		$this->request = $request;
	}

	/**
	* Increment clicks for an ad
	*
	* @param	int	$ad_id	Advertisement ID
	* @return	\Symfony\Component\HttpFoundation\Response	A Symfony Response object
	*/
	public function increment_clicks($ad_id)
	{
		if ($this->request->is_ajax() && !empty($ad_id))
		{
			$this->manager->increment_ad_clicks($ad_id);

			return new \Symfony\Component\HttpFoundation\JsonResponse();
		}

		throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED');
	}
}
