<?php
/*
Plugin Name: dsz Newsletter Generator
Plugin URI: https://github.com/dokker/dsz-newsletter
Description: Wordpress plugin for generate MailChimp newsletters from various contents
Version: 1.0
Author: docker
Author URI: https://hu.linkedin.com/in/docker
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


function __dsz_newsletter_load_plugin()
{
	
	/**
	 * Initial settings
	 */
	define('CNCNL_DS', DIRECTORY_SEPARATOR);
	define('CNCNL_PROJECT_PATH', realpath(dirname(__FILE__)));
	define('CNCNL_PROJECT_URL', plugins_url() . CNCNL_DS . 'dsz-newsletter');
	define('CNCNL_TEMPLATE_DIR', CNCNL_PROJECT_PATH . CNCNL_DS . 'templates');
	define('CNCNL_THEME', get_stylesheet_directory());

	/**
	 * Autoload
	 */
	$vendorAutoload = CNCNL_PROJECT_PATH . CNCNL_DS . 'vendor' . CNCNL_DS . 'autoload.php';
	if (is_file($vendorAutoload)) {
		require_once($vendorAutoload);
	}

	// load translations
	load_plugin_textdomain( 'dsz-newsletter', false, 'dsz-newsletter/languages' );

	$admin = new \cncNL\Admin();
	if (is_admin()) {
		$newsletter = new \cncNL\Newsletter();
	}

}

add_action('plugins_loaded', '__dsz_newsletter_load_plugin');
