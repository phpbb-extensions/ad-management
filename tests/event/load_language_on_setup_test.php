<?php
/**
 *
 * Pages extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\event;

class load_language_on_setup_test extends main_listener_base
{
	/**
	* Test the load_language_on_setup_test event
	*/
	public function test_load_language_on_setup()
	{
		$dispatcher = new \phpbb\event\dispatcher();
		$dispatcher->addListener('core.user_setup', array($this->get_listener(), 'load_language_on_setup'));

		$lang_set_ext = array();
		$event_data = array('lang_set_ext');
		$event_data_after = $dispatcher->trigger_event('core.user_setup', compact($event_data));
		extract($event_data_after, EXTR_OVERWRITE);

		self::assertEquals(array(
			array(
				'ext_name' => 'phpbb/ads',
				'lang_set' => 'common',
			)
		), $lang_set_ext);
	}
}
