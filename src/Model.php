<?php
namespace cncNL;

class Model {

	private $db_table = 'cnc_newsletter';

	function __construct()
	{
		global $wpdb;
		$this->db = $wpdb;
		$this->db_table = $this->db->prefix . $this->db_table;
	}
	public function pluginActivate()
	{
		$charset_collate = $this->db->get_charset_collate();
		if ($this->db->get_var("show tables like '{$this->db_table}'") != $this->db_table) {
			$sql = "CREATE TABLE " . $this->db_table . " (
				`id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT,
				`title` VARCHAR(512) NOT NULL,
				`segment_id` INT(10),
				`campaign_id` INT(10),
				`lead_id` INT(10),
				`lead_image` VARCHAR(512),
				`featured` LONGTEXT,
				`recommendations` LONGTEXT,
				`yt_url` VARCHAR(512),
				`yt_title` VARCHAR(512),
				`creation` DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
				UNIQUE KEY id (id)
				) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
	}

	public function filterNlData($raw)
	{
		parse_str($raw['featured'], $featured);
		parse_str($raw['recommendations'], $recommendations);
		$data = [
			'title' => sanitize_text_field($raw['title']),
			'segment' => intval($raw['segment']),
			'lead-id' => intval($raw['lead-id']),
			'lead-image' => esc_url($raw['lead-image']),
			'featured' => $featured,
			'recommendations' => $recommendations,
			'yt-url' => esc_url($raw['yt-url']),
			'yt-title' => sanitize_text_field($raw['yt-title']),
			'mc_template' => intval($raw['mc_template']),
		];
		return $data;
	}

	public function insertNewsletter($data)
	{
	}
}
