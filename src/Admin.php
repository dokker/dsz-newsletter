<?php
namespace cncNL;

class Admin {
	function __construct()
	{
		add_action('admin_menu', [$this, 'registerAdminMenu'] );
		add_action('admin_enqueue_scripts', [$this, 'registerAdminScripts']);
		add_action('admin_enqueue_scripts', [$this, 'registerAdminStyles']);

	}

	public function registerAdminMenu()
	{
		add_menu_page('Hírlevél', 'Hírlevél',  'moderate_comments', 'hirlevel',  [$this, 'getAdminPage'], 'dashicons-email-alt', '55.8');
	}

	public function getAdminPage()
	{
	}

	public function registerAdminScripts($hook_suffix) {
		echo $hook_suffix;
		if ($hook_suffix == 'toplevel_page_hirlevel') {
			wp_enqueue_script('media-upload');
			wp_enqueue_script('postbox');
			wp_enqueue_script('thickbox');
			wp_register_script('nl-script', CNCNL_PROJECT_PATH . CNCNL_DS . 'assets/js/admin.js', array('jquery','media-upload','thickbox'));
			wp_enqueue_script('nl-script');
		}
	}

	public function registerAdminStyles($hook_suffix) {
		if ($hook_suffix == 'toplevel_page_hirlevel') {
			wp_enqueue_style( 'nl-style' , CNCNL_PROJECT_PATH . CNCNL_DS . 'assets/css/admin.css');
			wp_enqueue_style('thickbox');
		}
	}
}
