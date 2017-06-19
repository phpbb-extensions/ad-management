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
	public function test_load_language_on_setup_test()
	{
		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.user_setup', array($this->get_listener(), 'load_language_on_setup'));

		$lang_set_ext = array();
		$event_data = array('lang_set_ext');
		$event = new \phpbb\event\data(compact($event_data));
		$dispatcher->dispatch('core.user_setup', $event);

		$event_data_after = $event->get_data_filtered($event_data);
		$this->assertEquals(array(
			'lang_set_ext'	=> array(
				array(
					'ext_name' => 'phpbb/ads',
					'lang_set' => 'common',
				)
			),
		), $event_data_after);
	}
}
