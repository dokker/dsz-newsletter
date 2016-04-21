<?php
namespace cncNL;
use \DrewM\MailChimp\MailChimp;

class Newsletter
{
	
	function __construct()
	{
		// Call in config
		$cac_donation_config = include(CNCNL_PROJECT_PATH . '/config.php');
		$this->api_key = $cac_donation_config['api_key'];
		$this->list_id = $cac_donation_config['list_id'];

		$this->MC = new MailChimp($this->api_key);

	}
}
