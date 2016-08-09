<?php
namespace cncNL;

class Model {

	private $db_table = CNCNL_TABLE;

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
				`nnews` LONGTEXT,
				`featured` LONGTEXT,
				`recommendations` LONGTEXT,
				`yt_url` VARCHAR(512),
				`yt_title` VARCHAR(512),
				`archive_url` VARCHAR(512),
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
		parse_str($raw['nnews'], $nnews);
		parse_str($raw['featured'], $featured);
		parse_str($raw['recommendations'], $recommendations);
		$data = [
			'title' => sanitize_text_field($raw['title']),
			'segment' => intval($raw['segment']),
			'lead-id' => intval($raw['lead-id']),
			'lead-image' => esc_url($raw['lead-image']),
			'nnews' => $nnews,
			'featured' => $featured,
			'recommendations' => $recommendations,
			'yt-url' => esc_url($raw['yt-url']),
			'yt-title' => sanitize_text_field($raw['yt-title']),
			'mc_template' => intval($raw['mc_template']),
		];
		return $data;
	}

	/**
	 * Sanitize campaign data for update
	 * @param  array $raw Unfiltered data
	 * @return arra      Filtered data
	 */
	public function filterUpdateNlData($raw)
	{
		parse_str($raw['featured'], $featured);
		parse_str($raw['recommendations'], $recommendations);
		$data = [
			'id' => intval($raw['id']),
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
	 * Create normalized detailed array of selected nnews
	 * @param  array $nnews_arr Selected nnews ids
	 * @return array            Detailed nnews list
	 */
	public function prepareNnewsList($nnews_arr)
	{
		$nnews = [];
		foreach ($nnews as $id) {
			$item = new stdClass();
			$item->title = get_the_title($id);
			$item->excerpt = get_the_excerpt($id);
			$item->permalink = get_permalink($id);
			$nnews[] = $item;
		}
		return $nnews;
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
			(id, title, segment_id, campaign_id, lead_id, lead_image, nnews, featured, recommendations, yt_url, yt_title, archive_url, creation, template_id, status)
			VALUES
			(%s, %s, %d, %s, %d, %s, %s, %s, %s, %s, %s, %s, %s, %d, %d)",
			[
				"null",
				$data['title'],
				$data['segment'],
				$data['campaign_id'],
				$data['lead-id'],
				$data['lead-image'],
				serialize($data['nnews']),
				serialize($data['featured']),
				serialize($data['recommendations']),
				$data['yt-url'],
				$data['yt-title'],
				$data['archive_url'],
				date('Y-m-d H:i:s'),
				$data['mc_template'],
				0,
			]
		));
		return $result;
	}

	public function updateNewsletter($data)
	{
		$this->db->update($this->db_table, 
			[
				'title' => $data['title'],
				'segment_id' => $data['segment'],
				'lead_id' => $data['lead-id'],
				'lead_image' => $data['lead-image'],
				'featured' => serialize($data['featured']),
				'recommendations' => serialize($data['recommendations']),
				'yt_url' => $data['yt-url'],
				'yt_title' => $data['yt-title'],
				'template_id' => $data['mc_template'],
			],
			['id' => $data['id']], 
			['%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d']
		);
	}

	/**
	 * Delete newsletter from WP database
	 * @param  int $id Newsletter ID
	 * @return int,bool     Affected rows or false
	 */
	public function deleteNewsletter($id)
	{
		$result = $this->db->delete($this->db_table, ['id' => $id], ['%d']);
		return $result;
	}

	/**
	 * Get newsletter by ID
	 * @param  int $id ID of the stored newsletter
	 * @return object,null     Result object or null
	 */
	public function getNewsletter($id)
	{
		$query = "SELECT * FROM {$this->db_table} WHERE id={$id}";
		return $this->db->get_row($query);
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

	public function getTableName()
	{
		return $this->db_table;
	}

	/**
	 * Get MC ampaign id by db ID
	 * @param  int $id ID in the db
	 * @return int     Campaign ID
	 */
	public function getCampaignIdByID($id)
	{
		$query = "SELECT campaign_id FROM {$this->db_table} WHERE id={$id}";
		return $this->db->get_var($query);
	}

	/**
	 * Get all nnews type posts
	 * @return object Query result
	 */
	public function getNnews()
	{
		$args = [
			'posts_per_page' => -1,
			'post_type' => 'nnews',
		];
		$query = new \WP_Query($args);
		return $query;
	}
}
