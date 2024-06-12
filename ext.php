<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads;

class ext extends \phpbb\extension\base
{
	public const DATE_FORMAT = 'Y-m-d';
	public const MAX_NAME_LENGTH = 255;
	public const DEFAULT_PRIORITY = 5;
	public const AD_BLOCK_MODES = [0, 1, 2];

	/**
	 * {@inheritdoc}
	 *
	 * Requires phpBB 4.0.0 due to use of Icon().
	 */
	public function is_enableable()
	{
		return phpbb_version_compare(PHPBB_VERSION, '4.0.0-dev', '>=');
	}
}
