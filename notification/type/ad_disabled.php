<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\notification\type;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ad_disabled extends \phpbb\notification\type\base
{
	/** @var array Notification preference metadata */
	public static $notification_option = array(
		'lang' => 'NOTIFICATION_TYPE_PHPBB_ADS_AD_DISABLED',
		'group' => 'NOTIFICATION_GROUP_MISCELLANEOUS',
	);

	/** @var string Email template */
	protected $email_template = '@phpbb_ads/ad_disabled';

	/**
	 * {@inheritdoc}
	 */
	public function get_type()
	{
		return \phpbb\ads\ext::NOTIFICATION_TYPE_DISABLED;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_item_id($type_data)
	{
		return (int) $type_data['ad_id'];
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_item_parent_id($type_data)
	{
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function find_users_for_notification($type_data, $options = array())
	{
		$owner = (int) $type_data['ad_owner'];
		if ($owner <= ANONYMOUS)
		{
			return array();
		}

		return $this->check_user_notification_options(array($owner), $options);
	}

	/**
	 * {@inheritdoc}
	 */
	public function users_to_query()
	{
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_title()
	{
		$this->language->add_lang('common', 'phpbb/ads');

		return $this->language->lang('PHPBB_ADS_NOTIFICATION_DISABLED', $this->get_data('ad_name'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_reason()
	{
		$this->language->add_lang('common', 'phpbb/ads');

		return $this->language->lang('PHPBB_ADS_NOTIFICATION_REASON_' . strtoupper($this->get_data('reason')));
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_reference()
	{
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_url($reference_type = UrlGeneratorInterface::ABSOLUTE_PATH)
	{
		$root = $reference_type === UrlGeneratorInterface::ABSOLUTE_URL
			? generate_board_url() . '/'
			: $this->phpbb_root_path;

		return append_sid($root . 'ucp.' . $this->php_ext, 'i=-phpbb-ads-ucp-main_module&amp;mode=stats');
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_email_template()
	{
		return $this->email_template;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_email_template_variables()
	{
		return array(
			'AD_NAME' => html_entity_decode($this->get_data('ad_name'), ENT_COMPAT),
			'REASON' => html_entity_decode(strip_tags($this->get_reason()), ENT_COMPAT),
			'U_VIEW_ADS' => htmlspecialchars_decode($this->get_url(UrlGeneratorInterface::ABSOLUTE_URL)),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function create_insert_array($type_data, $pre_create_data = array())
	{
		$this->set_data('ad_name', $type_data['ad_name']);
		$this->set_data('reason', $type_data['reason']);

		parent::create_insert_array($type_data, $pre_create_data);
	}
}
