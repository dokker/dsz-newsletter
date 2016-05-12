<?php
namespace cncNL;

class Admin {
	private $dsz;

	function __construct()
	{
		add_action('admin_menu', [$this, 'registerAdminMenu'] );
		add_action('admin_enqueue_scripts', [$this, 'registerAdminScripts']);
		add_action('admin_enqueue_scripts', [$this, 'registerAdminStyles']);
		$cac_donation_config = include(CNCNL_PROJECT_PATH . CNCNL_DS . 'config.php');
		$this->list_id = $cac_donation_config['list_id'];
		$this->setupAjaxHandlers();

		require_once(dirname(CNCNL_PROJECT_PATH) . CNCNL_DS . 'dsz_daily_ad_sum/inc/das.class.php');
		require_once(CNCNL_THEME . CNCNL_DS . 'inc/class/dumaszinhaz.class.php');
		$this->dsz = new \Dumaszinhaz\Dumaszinhaz();
		$this->newsletter = new \cncNL\Newsletter();

		$this->model = new \cncNL\Model();
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

	/**
	 * Generate Newsletter creation page
	 */
	public function getAdminCreatePage()
	{
		$view = new \cncNL\View();



		if (!isset($_POST['sel-segments'])) {
			// segment selector
			$segments = $this->newsletter->getSegments($this->list_id);
			$segments_list = $this->prepareSegmentsList($segments);
			$view->assign('list_segments', $view->renderList($segments_list, 'segments', 'sel-segments'));
			$view->assign('selector', $view->render('admin_selector'));
			$view->assign('location', false);
		} else {
			if ($_POST['sel-segments'] == $this->getCapitalId()) {
				// do Budapest stuff
				$location = 'bp';
			} else {
				// do Videk stuff
				$location = 'videk';
			}
			// create recommended shows markup
			$show_list = $this->listifyShowsList($this->getRecommendedShows());
			
			$lead_show = $this->getLeadShowDetails($this->getShowById($this->getLeadShow($location)));
			$view->assign('list_shows_recommended_lead', $view->renderList($show_list, 'sel-lead-recommendations', 'sel-lead-recommendations'));
			$view->assign('lead_show', $lead_show);
			$view->assign('lead', $view->render('admin_lead'));

			// create featured shows markup
			$featured_list = $this->listifyShowsList($this->getFeaturedShows($location));
			$view->assign('list_shows_featured', $view->renderList($show_list, 'shows-featured', 'sel-featured'));
			$view->assign('featured', $view->render('admin_featured'));

			// recommended shows list
			$view->assign('list_shows_recommended', $view->renderList($show_list, 'shows-recommended', 'sel-recommendations'));
			$view->assign('recommendations', $view->render('admin_recommendations'));

			$view->assign('youtube', $view->render('admin_youtube'));
			$view->assign('location', $location);

			$templates = $this->newsletter->getTemplates();
			$template_list = $this->prepareTemplateList($templates);
			$view->assign('list_templates', $view->renderList($template_list, 'templates', 'sel-templates'));
			$view->assign('mc_template', $view->render('admin_template'));
		}
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

	/**
	 * Setting up ajax handlers
	 */
	private function setupAjaxHandlers()
	{
		add_action('wp_ajax_get_lead_show_details', array($this, 'ajaxGetLeadShowDetails'));
	}

	/**
	 * Prepare AJAX scripts
	 */
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
	 * Sipmlify show data structure
	 * @param  object $show Show object
	 * @return array       Simplified data
	 */
	private function getLeadShowDetails($show)
	{
		$details = [
			'id' => $show->id,
			'title' => $show->cim,
			'image' => $this->cropLeadImage($show->eloadas_kepek[0]->original, true),
			'date' => $show->ido,
			'location' => $show->helyszin_nev,
		];
		return $details;
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
	 * Get shows for featured list
	 * @param string $location Chosen location (bp, videk)
	 * @return array Array of shows objects
	 */
	private function getFeaturedShows($location)
	{
		// For testing purposes only. Will be changed in the future.
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
	private function listifyShowsList($shows)
	{
		$list = array();
		if (!empty($shows)) {
			foreach ($shows as $show) {
				$list[] = ['label' => $show->cim, 'id' => $show->id];
			}
		}
		return $list;
	}

	/**
	 * Listify and order properly the Segments list
	 * @param  array $segments Segments list from MC API
	 * @return array           Formatted segments list
	 */
	private function prepareSegmentsList($segments)
	{
		$list = array();
		foreach ($segments['segments'] as $segment) {
			$list[] = ['id' => $segment['id'], 'label' => $segment['name']];
		}

		$list = $this->sortByKey($list, 'label');

		$budapest = array();
		foreach ($list as $key => $item) {
			if($item['label'] == 'Budapest') {
				$budapest = $item;
				unset($list[$key]);
			}
		}
		if (!empty($budapest)) {
			array_unshift($list, $budapest);
		}
		return $list;
	}

	/**
	 * Listify and order properly the Template list
	 * @param  array $templates Segments list from MC API
	 * @return array           Formatted template list
	 */
	private function prepareTemplateList($templates)
	{
		$list = array();
		foreach ($templates['templates'] as $template) {
			$list[] = ['id' => $template['id'], 'label' => $template['name']];
		}
		// $list = $this->sortByKey($list, 'label');
		return $list;
	}

	/**
	 * Sort array by given key values
	 * @param  array $sortable     Sortable array
	 * @param  string $selected_key Key to sort by
	 * @return array               Sorted array
	 */
	private function sortByKey($sortable, $selected_key)
	{
		$keys = array();
		foreach ($sortable as $key => $value) {
			$keys[$key] = $value[$selected_key];
		}
		array_multisort($keys, SORT_ASC, $sortable);
		return $sortable;
	}

	/**
	 * Crop image by url
	 * @param  string  $src     Image URL
	 * @param  boolean $preview Store temporarily
	 * @return string           Created image URL
	 */
	private function cropLeadImage($src, $preview = false)
	{
		$image = wp_get_image_editor($src);
		$image->resize(600, 276, true);
		$uploads = wp_upload_dir();
		$dir = $uploads['basedir'] . '/nl-images';
		if ($preview) {
			$filename = $dir . '/temp-preview2.jpg';
		} else {
			$filename = $image->generate_filename( NULL, $dir, NULL );
		}
		$info = $image->save($filename);
		return $uploads['baseurl'] . '/nl-images/' . $info['file'];
	}

	/**
	 * Lead Show Details for AJAX call
	 */
	public function ajaxGetLeadShowDetails()
	{
		check_ajax_referer( 'ajax_newsletter_admin', '_ajax_nonce' );
		$id = intval($_POST['show_id']);
		$details = $this->getLeadShowDetails($this->getShowById($id));
		wp_send_json_success(wp_json_encode($details));
	}

	private function getCapitalId()
	{
		$segments = $this->newsletter->getSegments($this->list_id);
		foreach ($segments['segments'] as $segment) {
			if ($segment['name'] == 'Budapest') {
				return $segment['id'];
			}
		}
		return false;
	}
}
