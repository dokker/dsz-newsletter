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
				`campaign_id` CHAR(15),
				`lead_id` INT(10),
				`lead_image` VARCHAR(512),
				`featured` LONGTEXT,
				`recommendations` LONGTEXT,
				`yt_url` VARCHAR(512),
				`yt_title` VARCHAR(512),
				`creation` DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
				`template_id` INT(10),
				`status` INT(1),
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

	/**
	 * Create normalized detailed array from given show list
	 * @param  array $shows_arr List of shows
	 * @return array            Detailed ahows list
	 */
	public function prepareShowsList($shows_arr)
	{
		$shows = [];
		require_once(CNCNL_THEME . CNCNL_DS . 'inc/class/dumaszinhaz.class.php');
		$this->dsz = new \Dumaszinhaz\Dumaszinhaz();
		foreach ($shows_arr as $show_id) {
			$show = $this->dsz->getMusorById($show_id);
			$show->performers =  $this->preparePerformers($show->alkotok);
			$shows[] = $show;
		}
		return $shows;
	}

	/**
	 * Create names string from given performers
	 * @param  array $performers Performers
	 * @return string             Performer names list
	 */
	private function preparePerformers($performers)
	{
		$names = [];
		foreach ($performers as $performer) {
			$names[] = $performer->nev;
		}
		return implode(', ', $names);
	}

	/**
	 * Insert campaign data to the db
	 * @param  array $data Campaign data
	 * @return int,bool       Affected rows or false
	 */
	public function insertNewsletter($data)
	{
		$result = $this->db->query($this->db->prepare(
			"INSERT INTO {$this->db_table}
			(id, title, segment_id, campaign_id, lead_id, lead_image, featured, recommendations, yt_url, yt_title, creation, template_id, status)
			VALUES
			(%s, %s, %d, %s, %d, %s, %s, %s, %s, %s, %s, %d, %d)",
			[
				"null",
				$data['title'],
				$data['segment'],
				$data['campaign_id'],
				$data['lead-id'],
				$data['lead-image'],
				serialize($data['featured']),
				serialize($data['recommendations']),
				$data['yt-url'],
				$data['yt-title'],
				date('Y-m-d H:i:s'),
				$data['mc_template'],
				0,
			]
		));
		return $result;
	}

	/**
	 * Change campaign status
	 * @param string $campaign_id Campaign ID
	 * @param int $status      Status code
	 */
	public function setNlStatus($campaign_id, $status)
	{
		$this->db->update($this->db_table, 
			['status' => $status ],
			['campaign_id' => $campaign_id], 
			['%d']
		);
	}
}
