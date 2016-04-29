<?php
namespace cncNL;
use \DrewM\MailChimp\MailChimp;

class Newsletter
{
	private $api_key;
	private $list_id;
	
	function __construct()
	{
		// Call in config
		$cac_donation_config = include(CNCNL_PROJECT_PATH . CNCNL_DS . 'config.php');
		$this->api_key = $cac_donation_config['api_key'];

		$this->MC = new MailChimp($this->api_key);
	}

	/**
	 * Get segments for given list
	 * @param  int $list_id List ID
	 * @return object          Result
	 */
	public function getSegments($list_id)
	{
		$result = $this->MC->get("lists/$list_id/segments");
		if ($result !== false) {
			return $result;
		}
	}

	/**
	 * Create new campaign for given subscriber list
	 * @param  int  $list_id    List ID
	 * @param  string  $name       Name of the campaign
	 * @param  int $template_id Template ID
	 * @param  int,boolean $segment_id Segment ID
	 * @return int,boolean              Operation result
	 */
	private function createCampaign($list_id, $name, $template_id, $segment_id = false)
	{
		$defaults = $this->getCampaignDefaults($list_id);
		$data = [
			'type' => 'regular',
			'recipients' => (object)['list_id' => $list_id],
			'settings' => (object)[
				'subject_line' => $name,
				'from_name' => $defaults['from_name'],
				'reply_to' => $defaults['from_email'],
				'template_id' => $template_id,
			]
		];
		if ($segment_id) {
			$data['recipients']->segment_opts = new \stdClass;
			$data['recipients']->segment_opts->saved_segment_id = $segment_id;
		}
		$this->MC->post("campaigns", $data);
		if(!$this->MC->getLastError()) {
			return true;
		}
	}

	/**
	 * Get the given list's camaign defaults
	 * @param  int $list_id List ID
	 * @return array          Result
	 */
	private function getCampaignDefaults($list_id)
	{
		$result = $this->MC->get("lists/$list_id");
		if ($result !== false) {
			return $result['campaign_defaults'];
		}
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
}
