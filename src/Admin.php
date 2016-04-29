<?php
namespace cncNL;

class Admin {
	private $dsz;

	function __construct()
	{
		add_action('admin_menu', [$this, 'registerAdminMenu'] );
		add_action('admin_enqueue_scripts', [$this, 'registerAdminScripts']);
		add_action('admin_enqueue_scripts', [$this, 'registerAdminStyles']);
	}

	public function registerAdminMenu()
	{
		add_menu_page('Hírlevél', 'Hírlevél',  'moderate_comments', 'hirlevel',  [$this, 'getAdminListPage'], 'dashicons-email-alt', '55.8');
		add_submenu_page( 'hirlevel', 'Hírlevél lista', 'Hírlevél lista', 'moderate_comments', 'hirlevel', [$this, 'getAdminListPage'] );
		add_submenu_page( 'hirlevel', 'Új hírlevél', 'Új hírlevél', 'moderate_comments', 'hirlevel-add', [$this, 'getAdminCreatePage'] );
	}

	public function getAdminListPage()
	{
	}

	public function getAdminCreatePage($value='')
	{
		require_once(dirname(CNCNL_PROJECT_PATH) . CNCNL_DS . 'dsz_daily_ad_sum/inc/das.class.php');
		require_once(CNCNL_THEME . CNCNL_DS . 'inc/class/dumaszinhaz.class.php');
		$this->dsz = new \Dumaszinhaz\Dumaszinhaz();

		$view = new \cncNL\View();
		$view->assign('selector', $view->render('admin_selector'));
		$view->assign('lead', $view->render('admin_lead'));
		$view->assign('featured', $view->render('admin_featured'));

		// recommended shows list
		$show_list = $this->listifyShowsList($this->getRecommendedShows());
		$view->assign('list_shows_recommended', $view->renderList($show_list, 'shows-recommended', 'sel-recommendations'));
		$view->assign('recommendations', $view->render('admin_recommendations'));

		$view->assign('youtube', $view->render('admin_youtube'));
		$html = $view->render('admin_index');
		echo $html;
	}

	public function registerAdminScripts($hook_suffix) {
		if ($hook_suffix == 'hirlevel_page_hirlevel-add') {
			wp_register_script('nl-script', CNCNL_PROJECT_URL . CNCNL_DS . 'assets/js/admin.js', array('jquery','media-upload','thickbox'));
			wp_enqueue_script('nl-script');
			// scripts for metaboxes
			wp_enqueue_script('postbox');
			// scripts for media selector
			wp_enqueue_media();
			wp_enqueue_script('thb_media_selector', CNCNL_PROJECT_URL . CNCNL_DS . 'assets/js/thb.media_selector.js', array( 'jquery' ));

			$this->prepareAJAX();
		}
	}

	private function prepareAJAX()
	{
			// Prepare AJAX
			$nonce = wp_create_nonce('ajax_newsletter_admin');
		    // Get the protocol of the current page
			$protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
		    // Set the ajaxurl Parameter which will be output right before
		    // our ajax-delete-posts.js file so we can use ajaxurl
			$params = array(
			    // Get the url to the admin-ajax.php file using admin_url()
				'ajax_url' => admin_url( 'admin-ajax.php', $protocol ),
				'nonce'    => $nonce,
				);
		    // Print the script to our page
			wp_localize_script( 'nl-script', 'nl_params', $params );
	}

	public function registerAdminStyles($hook_suffix) {
		if ($hook_suffix == 'hirlevel_page_hirlevel-add') {
			wp_enqueue_style( 'nl-style' , CNCNL_PROJECT_URL . CNCNL_DS . 'assets/css/admin.css');
			wp_enqueue_style('thickbox');
		}
	}

	/**
	 * Get lead show id from dsz_daily_ad_sum plugin
	 * @param  string $location Location name ("bp" or "videk")
	 * @return int           Show ID
	 */
	private function getLeadShow($location)
	{
		$DAS = new \DAS();
		$leadID = $DAS->get_lead_show_id($location);
		return $leadID;
	}

	/**
	 * Get shows for recommended list
	 * @return array Array of shows objects
	 */
	public function getRecommendedShows()
	{
		$shows = $this->dsz->getMusorLista();
		return $shows;
	}

	/**
	 * Get show by given ID
	 * @param  int $id ID of the show
	 * @return object     Show data
	 */
	public function getShowById($id)
	{
		$show = $this->dsz->getMusorById($id);
		return $show;
	}

	/**
	 * Create a list from given shows list
	 * @param  array $shows Shows list
	 * @return array        List of items
	 */
	public function listifyShowsList($shows)
	{
		$list = array();
		if (!empty($shows)) {
			foreach ($shows as $show) {
				$list[] = ['label' => $show->cim, 'id' => $show->id];
			}
		}
		return $list;
	}
}
