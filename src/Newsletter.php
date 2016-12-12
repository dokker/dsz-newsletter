<?php
namespace cncNL;
use \DrewM\MailChimp\MailChimp;

class Newsletter
{
	private $api_key;
	private $list_id;
	private $timeout;
	
	function __construct()
	{
		// Call in config
		$cac_donation_config = include(CNCNL_PROJECT_PATH . CNCNL_DS . 'config.php');
		$this->api_key = $cac_donation_config['api_key'];
		$this->timeout = 100;

		$this->MC = new MailChimp($this->api_key);
	}

	/**
	 * Get segments for given list
	 * @param  int $list_id List ID
	 * @return object          Result
	 */
	public function getSegments($list_id)
	{
		// check stored results
		$data = ['count' => 500];
		if (false === ($stored = get_transient('cnc_mc_segments'))) {
			$result = $this->MC->get("lists/$list_id/segments", $data, $this->timeout);
			if ($result !== false) {
				set_transient('cnc_mc_segments', $result, 3 * HOUR_IN_SECONDS);
			}
		} else {
			$result = $stored;
		}
		return $result;
	}

	/**
	 * Get templates
	 * @return object          Result
	 */
	public function getTemplates()
	{
		// check stored results
		if (false === ($stored = get_transient('cnc_mc_templates'))) {
			// filter user generated templates
			$data = ['type' => 'user'];
			$result = $this->MC->get("templates", $data, $this->timeout);
			if ($this->MC->success()) {
				set_transient('cnc_mc_templates', $result, 6 * HOUR_IN_SECONDS);
			}
		} else {
			$result = $stored;
		}
		return $result;
	}

	/**
	 * Create new campaign for given subscriber list
	 * @param  int  $list_id    List ID
	 * @param  string  $name       Name of the campaign
	 * @param  int $template_id Template ID
	 * @param  int,boolean $segment_id Segment ID
	 * @return int,boolean              Campaign id or false
	 */
	public function createCampaign($list_id, $name, $segment_id = false, $ga_slug = '')
	{
		$defaults = $this->getCampaignDefaults($list_id);
		$data = [
			'type' => 'regular',
			'recipients' => (object)['list_id' => $list_id],
			'settings' => (object)[
				'subject_line' => $name,
				'from_name' => $defaults['from_name'],
				'reply_to' => $defaults['from_email'],
				'inline_css' => true,
			],
			'tracking' => (object)[
				'google_analytics' => $ga_slug,
			],
		];
		if ($segment_id) {
			$data['recipients']->segment_opts = new \stdClass;
			$data['recipients']->segment_opts->saved_segment_id = $segment_id;
		}
		$this->MC->post("campaigns", $data, $this->timeout);
		if($this->MC->success()) {
			$response = $this->MC->getLastResponse();
			return json_decode($response['body']);
		} else {
			return false;
		}
	}

	/**
	 * Get the given list's camaign defaults
	 * @param  int $list_id List ID
	 * @return array          Result
	 */
	private function getCampaignDefaults($list_id)
	{
		// check stored results
		if (false === ($stored = get_transient('cnc_mc_campaign_defaults'))) {
			$result = $this->MC->get("lists/$list_id", array(), $this->timeout);
			if ($result !== false) {
				$defaults = $result['campaign_defaults'];
				set_transient('cnc_mc_campaign_defaults', $defaults, 6 * HOUR_IN_SECONDS);
			}
		} else {
			$defaults = $stored;
		}
		return $defaults;
	}

	/**
	 * Create HTML Teplate
	 * @param  string $name Template name
	 * @param  string $html HTML markup
	 * @return int,boolean	Template ID or false
	 */
	private function createHTMLTepmlate($name, $html)
	{
		$data = [
			'name' => $name,
			'html' => $html,
		];
		$this->MC->post("templates", $data);
		if(!$this->MC->getLastError()) {
			$result = $this->MC->getLastResponse();
			$body = json_decode($result['body']);
			return $body->id;
		} else {
			return false;
		}
	}

	/**
	 * Update content of specified campaign
	 * @param  string $campaign_id Campaign ID
	 * @param  int $template_id Template ID
	 * @param  object $sections    Content data
	 * @return string,bool              HTML email or false
	 */
	public function updateCampaignContent($campaign_id, $template_id, $sections)
	{
		$args = [
			'template' => [
				'id' => $template_id,
				'sections' => $sections,
			],
		];
		$response = $this->MC->put("campaigns/$campaign_id/content", $args, $this->timeout);
		if ($this->MC->success()) {
			return $response['html'];
		} else {
			return false;
		}
	}

	/**
	 * Update Campaign data
	 * @param  string $campaign_id Campaign ID
	 * @param  array $data        Campaign data
	 * @return bool              Operation success
	 */
	public function updateCampaign($campaign_id, $data)
	{
		$args = [
			'settings' => (object)[
				'subject_line' => $data['title'],
			],
			'recipients' => (object) [
				'segment_opts' => (object) [
					'saved_segment_id' => $data['segment'],
				],
			],
		];
		$this->MC->patch("campaigns/$campaign_id", $args, $this->timeout);
		if ($this->MC->success()) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Send MC campaign
	 * @param  string $campaign_id Campaign ID
	 * @return bool              Success
	 */
	public function sendCampaign($campaign_id)
	{
		$result = $this->MC->post("campaigns/$campaign_id/actions/send", array(), $this->timeout);
		if(!$this->MC->getLastError()) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get campaign arhive ulr (using in lists is resource heavy)
	 * @param  string $camaign_id Campaign ID
	 * @return string,bool             Archive url or false
	 */
	public function getArchiveUrl($campaign_id)
	{
		$result = $this->MC->get("campaigns/$campaign_id");
		if ($this->MC->success()) {
			return $result['archive_url'];
		} else {
			return false;
		}
	}

	/**
	 * Delete selected campaign
	 * @param  string $campaign_id Campaign ID
	 * @return bool              Deletion success
	 */
	public function deleteCampaign($campaign_id)
	{
		$this->MC->delete("campaigns/$campaign_id", array(), $this->timeout);
		if (!$this->MC->getLastError()) {
			return true;
		} else {
			return false;
		}
	}
}
