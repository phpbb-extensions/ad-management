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
	/** @var int Tracking cooldown in seconds */
	public const TRACKING_COOLDOWN = 10;

	/** @var \phpbb\ads\ad\manager */
	protected $manager;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param \phpbb\ads\ad\manager    $manager Advertisement manager object
	 * @param \phpbb\request\request                 $request Request object
	 * @param \phpbb\cache\driver\driver_interface  $cache Cache driver
	 * @param \phpbb\user                            $user User object
	 */
	public function __construct(\phpbb\ads\ad\manager $manager, \phpbb\request\request $request, \phpbb\cache\driver\driver_interface $cache, \phpbb\user $user)
	{
		$this->manager = $manager;
		$this->request = $request;
		$this->cache = $cache;
		$this->user = $user;
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

		$ad_id = (int) $data;
		if (!$this->is_rate_limited($mode, $ad_id))
		{
			$this->{$mode}($ad_id);
		}

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
	 * Suppress rapid repeat tracking requests for same IP, mode, and ad.
	 *
	 * @param string $mode Counter mode
	 * @param int    $ad_id Advertisement ID
	 * @return bool True when request should be suppressed
	 */
	protected function is_rate_limited($mode, $ad_id)
	{
		if (empty($this->user->ip))
		{
			return true;
		}

		$key = '_phpbb_ads_tracking_' . hash('sha256', $mode . ':' . $this->user->ip . ':' . (int) $ad_id);
		if ($this->cache->get($key) !== false)
		{
			return true;
		}

		$this->cache->put($key, true, self::TRACKING_COOLDOWN);

		return false;
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
