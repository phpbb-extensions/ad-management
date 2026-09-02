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
* Increment controller
*/
class increment_controller
{
	/** @var \phpbb\ads\ad\manager */
	protected $manager;

	/** @var \phpbb\request\request */
	protected $request;

	/**
	 * Constructor
	 *
	 * @param \phpbb\ads\ad\manager  $manager Advertisement manager object
	 * @param \phpbb\request\request $request Request object
	 */
	public function __construct(\phpbb\ads\ad\manager $manager, \phpbb\request\request $request)
	{
		$this->manager = $manager;
		$this->request = $request;
	}

	/**
	 * Handle request.
	 *
	 * @param	mixed	$data	Ad ID
	 * @param	string	$mode	clicks or views
	 * @return	\Symfony\Component\HttpFoundation\JsonResponse	A Symfony JsonResponse object
	 * @throws	\phpbb\exception\http_exception
	 */
	public function handle($data, $mode)
	{
		if (!in_array($mode, array('clicks', 'views'), true) || !$this->is_valid_data($data))
		{
			throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED');
		}

		$link_name = $mode === 'views' ? 'phpbb_ads_views_' . $data : 'phpbb_ads_click_' . (int) $data;
		if (!$this->request->is_ajax()
			|| !check_link_hash($this->request->variable('hash', ''), $link_name))
		{
			throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED');
		}

		$this->{$mode}((int) $data);

		return new \Symfony\Component\HttpFoundation\JsonResponse();
	}

	/**
	 * Validate route payload before using it.
	 *
	 * @param mixed $data Route payload
	 * @return bool
	 */
	protected function is_valid_data($data)
	{
		return ctype_digit((string) $data) && (int) $data > 0;
	}

	/**
	 * Increment clicks for an ad.
	 *
	 * @param	int	$ad_id	Advertisement ID
	 */
	protected function clicks($ad_id)
	{
		$this->manager->increment_ad_clicks($ad_id);
	}

	/**
	 * Increment views for an ad.
	 *
	 * @param	int	$ad_id	Advertisement ID
	 */
	protected function views($ad_id)
	{
		$this->manager->increment_ad_views($ad_id);
	}
}
