<?php
namespace cncNL;

class Admin {
	private $dsz; // for Dumaszinhaz object
	private $messages = []; // for status messages
	private $campaign_id = NULL;
	private $preview; // for campaign preview makup

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

	/**
	 * Register admin menu items
	 */
	public function registerAdminMenu()
	{
		add_menu_page('Hírlevél', 'Hírlevél',  'moderate_comments', 'hirlevel',  [$this, 'getAdminListPage'], 'dashicons-email-alt', '55.8');
		add_submenu_page( 'hirlevel', 'Hírlevél lista', 'Hírlevél lista', 'moderate_comments', 'hirlevel', [$this, 'getAdminListPage'] );
		add_submenu_page( 'hirlevel', 'Új hírlevél', 'Új hírlevél', 'moderate_comments', 'hirlevel-add', [$this, 'getAdminCreatePage'] );
	}

	/**
	 * Generate Newsletter list page
	 */
	public function getAdminListPage()
	{
		$view = new \cncNL\View();

		if (isset($_GET['action']) && $_GET['action'] == 'edit' )	{
			if (!isset($_POST['nl-phase'])) {
				$phase = 0;
			} else {
				$phase = intval($_POST['nl-phase']);
				$phase++;
			}
			$id = intval($_GET['id']);
			switch ($_GET['action']) {
				case 'edit':
					if (isset($_POST['nl-form-save'])) {
						$this->updateNewsletter();
					}
					$nl_data = $this->model->getNewsletter($id);

					$view->assign('page_title', __('Edit Newsletter', 'dsz-newsletter'));
					$view->assign('action', 'edit');

					$view->assign('campaign_title', $nl_data->title);
					$view->assign('admin_title', $view->render('admin_title'));
					// create recommended shows markup
					$show_list = $this->listifyShowsList($this->getAllShows());
					
					$segments = $this->newsletter->getSegments($this->list_id);
					$segments_list = $this->prepareSegmentsList($segments);
					$view->assign('list_segments', $view->renderList($segments_list, 'segments', 'sel-segments', $nl_data->segment_id));
					$view->assign('selector', $view->render('admin_selector'));

					$lead_show = $this->getLeadShowDetails($this->getShowById($nl_data->lead_id), $nl_data->lead_image);
					$view->assign('lead_image', $nl_data->lead_image);
					$view->assign('list_shows_recommended_lead', $view->renderList($show_list, 'sel-lead-recommendations', 'sel-lead-recommendations'));
					$view->assign('lead_show', $lead_show);
					$view->assign('lead', $view->render('admin_lead'));

					// prepare nnews select markup
					$nnews_list = $this->listifyNnewsQuery($this->model->getNnews());
					$view->assign('list_nnews', $view->renderList($nnews_list, 'nnews', 'sel-nnews'));

					// selected nnews
					$selected_nnews_list = $this->listifySelectedNnewsList($nl_data->nnews);
					$view->assign('list_selected_nnews', $view->renderSelectedNnewsList($selected_nnews_list));
					// selected featured shows
					$selected_featured_list = $this->listifySelectedList($nl_data->featured);
					$view->assign('list_selected_featured', $view->renderSelectedList($selected_featured_list));

					// create featured shows markup
					$view->assign('list_shows_featured', $view->renderList($show_list, 'shows-featured', 'sel-featured'));
					$view->assign('featured', $view->render('admin_featured'));

					// recommended shows list
					$selected_recommended_list = $this->listifySelectedList($nl_data->recommendations);
					$view->assign('list_selected_recommended', $view->renderSelectedList($selected_recommended_list));

					$view->assign('list_shows_recommended', $view->renderList($show_list, 'shows-recommended', 'sel-recommendations'));
					$view->assign('recommendations', $view->render('admin_recommendations'));

					$view->assign('yt_url', $nl_data->yt_url);
					$view->assign('yt_title', $nl_data->yt_title);
					$view->assign('youtube', $view->render('admin_youtube'));
					$view->assign('segment', $nl_data->segment_id);

					$templates = $this->newsletter->getTemplates();
					$template_list = $this->prepareTemplateList($templates);
					$view->assign('selected_id', $nl_data->template_id);
					$view->assign('list_templates', $view->renderList($template_list, 'templates', 'sel-templates'));
					$view->assign('mc_template', $view->render('admin_template'));
				break;
			}
			$view->assign('phase', $phase);
			$view->assign('messages', $this->messages);
			$html = $view->render('admin_index');
		} else {
			// Init campaign deletion
			if(isset($_GET['action']) && $_GET['action'] == 'delete') {
				$this->deleteNewsletter();
			}
			// Init campaign send
			if(isset($_GET['action']) && $_GET['action'] == 'send') {
				$this->sendNewsletter();
			}

			//Our class extends the WP_List_Table class, so we need to make sure that it's there
			if(!class_exists('WP_List_Table')){
				require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
			}
			$this->table = new \cncNL\Table();
			$this->table->prepare_items();
			ob_start();
			$this->table->display();
			$list_table = ob_get_clean();

			$view->assign('list_table', $list_table);
			$view->assign('messages', $this->messages);
			$html = $view->render('admin_campaign_list');
		}
		echo $html;
	}

	/**
	 * Generate Newsletter creation page
	 */
	public function getAdminCreatePage()
	{
		$view = new \cncNL\View();



		if (!isset($_POST['nl-phase'])) {
			$phase = 0;
		} else {
			$phase = intval($_POST['nl-phase']);
			$phase++;
		}
		switch ($phase) {
			case 0 :
				// newsletter sent out
				$segments = $this->newsletter->getSegments($this->list_id);
				$segments_list = $this->prepareSegmentsList($segments);
				$view->assign('list_segments', $view->renderList($segments_list, 'segments', 'sel-segments'));
				$view->assign('selector', $view->render('admin_selector'));
			break;
			case 1 :
				// newsletter editor
				if ($_POST['sel-segments'] == $this->getCapitalId()) {
					// do Budapest stuff
					$location = 'bp';
				} else {
					// do Videk stuff
					$location = 'videk';
				}
				$view->assign('admin_title', $view->render('admin_title'));
				// create recommended shows markup
				$show_list = $this->listifyShowsList($this->getAllShows());
				
				$lead_show = $this->getLeadShowDetails($this->getShowById($this->getLeadShow($location)));
				$view->assign('list_shows_recommended_lead', $view->renderList($show_list, 'sel-lead-recommendations', 'sel-lead-recommendations'));
				$view->assign('lead_show', $lead_show);
				$view->assign('lead', $view->render('admin_lead'));

				// prepare nnews select markup
				$nnews_list = $this->listifyNnewsQuery($this->model->getNnews());
				$view->assign('list_nnews', $view->renderList($nnews_list, 'nnews', 'sel-nnews'));

				// create featured shows markup
				$featured_list = $this->listifyShowsList($this->getAllShows());
				$view->assign('list_shows_featured', $view->renderList($show_list, 'shows-featured', 'sel-featured'));
				$view->assign('featured', $view->render('admin_featured'));

				// recommended shows list
				$view->assign('list_shows_recommended', $view->renderList($show_list, 'shows-recommended', 'sel-recommendations'));
				$view->assign('recommendations', $view->render('admin_recommendations'));

				$view->assign('youtube', $view->render('admin_youtube'));
				$view->assign('segment', $_POST['sel-segments']);
				$view->assign('location', $location);

				$templates = $this->newsletter->getTemplates();
				$template_list = $this->prepareTemplateList($templates);
				$view->assign('list_templates', $view->renderList($template_list, 'templates', 'sel-templates'));
				$view->assign('mc_template', $view->render('admin_template'));
			break;
			case 2 :
				// campaign created
				$this->storeNewsletter();
				$view->assign('campaign_id', $this->campaign_id);
				$view->assign('preview', $this->preview);
			break;
			case 3 :
				// newsletter sent out
				$campaign_id = sanitize_text_field($_POST['nl-campaign-id']);
				$this->executeNewsletter($campaign_id);
			break;
		}
		$view->assign('page_title', __('Create Newsletter', 'dsz-newsletter'));
		$view->assign('phase', $phase);
		$view->assign('messages', $this->messages);
		$html = $view->render('admin_index');

		echo $html;
	}

	/**
	 * Register scripts for admin interface
	 * @param  string $hook_suffix Hook suffix
	 */
	public function registerAdminScripts($hook_suffix) {
		if ($hook_suffix == 'hirlevel_page_hirlevel-add' || $hook_suffix == 'toplevel_page_hirlevel') {
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
		if ($hook_suffix == 'hirlevel_page_hirlevel-add' || $hook_suffix == 'toplevel_page_hirlevel') {
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
		if (!$leadID) {
			return $this->dsz->getLatestMusorId();
		}
		return $leadID;
	}

	/**
	 * Sipmlify show data structure
	 * @param  object $show Show object
	 * @param string $image Image URL
	 * @return array       Simplified data
	 */
	private function getLeadShowDetails($show, $image = null)
	{
		if ($image === null) {
			$image = $this->cropLeadImage($show->eloadas_kepek[0]->original, true);
		}
		$details = [
			'id' => $show->id,
			'title' => $show->cim,
			'image' => $image,
			'date' => $show->ido,
			'location' => $show->helyszin_nev,
		];
		return $details;
	}

	/**
	 * Stores resized show image and gives back the URL
	 * @param  int $show_id Show ID
	 * @return string          Image URL
	 */
	private function getLeadShowImage($show_id)
	{
		$show = $this->dsz->getMusorById($show_id);
		return $this->cropLeadImage($show->eloadas_kepek[0]->original);
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
	 * Get shows for lists
	 * @return array Array of shows objects
	 */
	private function getAllShows()
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
	private function listifyShowsList($shows)
	{
		$list = array();
		if (!empty($shows)) {
			foreach ($shows as $show) {
				$date = $this->datetimeToShort($show->ido);
				$label = $show->cim . ' - ' . $date . ' - ' . $show->varos;
				$list[] = ['label' => $label, 'id' => $show->id];
			}
		}
		return $list;
	}

	/**
	 * Create a list from given nnews query
	 * @param  object $nnews Newsletter news list
	 * @return array        List of items
	 */
	private function listifyNnewsQuery($nnews)
	{
		$list = array();
		if ($nnews->have_posts()) {
			foreach ($nnews->posts as $item) {
				$label = $item->post_title;
				$list[] = ['label' => $label, 'id' => $item->ID];
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

	/**
	 * Set new message
	 * @param string $text Message markup
	 * @param string $type Message type (updated | error | update-nag)
	 */
	private function setMessage($text, $type)
	{
		$this->messages[] = (object) [
			'text' => $text,
			'type' => $type,
		];
	}

	/**
	 * Get segment name by id
	 * @param  int $id Segment ID
	 * @return string,bool     Segment name or false
	 */
	private function getSegmentNameById($id)
	{
		$segments = $this->newsletter->getSegments($this->list_id);
		foreach ($segments['segments'] as $segment) {
			if ($segment['id'] == $id) {
				return $segment['name'];
			}
		}
		return false;
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

	/**
	 * Handle newsletter storage and populating to MC
	 * @return boolean,string Error message or true
	 */
	private function storeNewsletter()
	{
		if (!empty($_POST['upload_image'])) {
			$image_url = $_POST['upload_image'];
		} else {
			$image_url = $this->getLeadShowImage($_POST['lead-id']);
		}
		$data = [
			'title' => $_POST['input-title'],
			'segment' => $_POST['input-segment'],
			'lead-id' => $_POST['lead-id'],
			'lead-image' => $image_url,
			'nnews' => $_POST['input-nnews'],
			'featured' => $_POST['input-featured'],
			'recommendations' => $_POST['input-recommendations'],
			'yt-url' => $_POST['youtube-url'],
			'yt-title' => $_POST['youtube-title'],
			'mc_template' => $_POST['sel-templates'],
		];

		$data = $this->model->filterNlData($data);

		// Create campaign based on segment data
		$response = $this->newsletter->createCampaign($this->list_id, $data['title'], $data['segment']);

		if ($response) {
			$campaign_id = $response->id;
			$data['campaign_id'] = $campaign_id;
			$data['archive_url'] = $response->archive_url;
			$this->campaign_id = $campaign_id;
			$sections = $this->getNlSections($data);

			// Add content data to campaign
			$this->preview = $this->newsletter->updateCampaignContent($campaign_id, $data['mc_template'], $sections);
			// Store newsletter data
			if(!$this->model->insertNewsletter($data)) {
				$this->setMessage(__('Error storing campaign details in database.', 'dsz-newsletter'), 'error');
			} else {
				$this->setMessage(__('MC Campaign creation successful.', 'dsz-newsletter'), 'updated');
			}
		} else {
			$this->setMessage(__('MC Campaign creation failed.', 'dsz-newsletter'), 'error');
		}
	}

	/**
	 * Handle updating newsletter data
	 */
	private function updateNewsletter()
	{
		$data = [
			'id' => $_GET['id'],
			'title' => $_POST['input-title'],
			'segment' => $_POST['sel-segments'],
			'lead-id' => $_POST['lead-id'],
			'lead-image' => $_POST['upload_image'],
			'nnews' => $_POST['input-nnews'],
			'featured' => $_POST['input-featured'],
			'recommendations' => $_POST['input-recommendations'],
			'yt-url' => $_POST['youtube-url'],
			'yt-title' => $_POST['youtube-title'],
			'mc_template' => $_POST['sel-templates'],
		];

		$data = $this->model->filterUpdateNlData($data);

		$campaign_id = $this->model->getCampaignIdByID($data['id']);
		if ($this->newsletter->updateCampaign($campaign_id, $data)) {
			$sections = $this->getNlSections($data);
			if ($this->newsletter->updateCampaignContent($campaign_id, $data['mc_template'], $sections)) {
				$this->model->updateNewsletter($data);
				$this->setMessage(__('Campaign data updated', 'dsz-newsletter'), 'updated');
			} else {
				$this->setMessage(__('Error updating campaign content', 'dsz-newsletter'), 'error');
			}
		} else {
			$this->setMessage(__('Error updating campaign data', 'dsz-newsletter'), 'error');
		}
	}

	/**
	 * Handle newsletter deletion
	 */
	private function deleteNewsletter()
	{
	    // Verify the nonce.
		$nonce = esc_attr( $_REQUEST['_wpnonce'] );
		if ( !wp_verify_nonce( $nonce, 'nl_delete_campaign' ) ) {
			die( 'Go get a life script kiddies' );
		}

		$stored_id = intval($_GET['id']);
		$deletable_nl = $this->model->getNewsletter($stored_id);
		$mc_res = $this->newsletter->deleteCampaign($deletable_nl->campaign_id);
		$db_res = $this->model->deleteNewsletter($stored_id);
		if ($mc_res && $db_res) {
			$this->setMessage(sprintf(__('<i>%s</i> successfully deleted.', 'dsz-newsletter'), $deletable_nl->title), 'updated');
		} else {
			if (!$mc_res) {
				$this->setMessage(__('Error deleting Mailchimp campaign', 'dsz_newsletter'), 'error');
			}
			if (!$db_res) {
				$this->setMessage(__('Error deleting saved newsletter', 'dsz_newsletter'), 'error');
			}
		}
	}

	/**
	 * Handle campaign send
	 */
	private function sendNewsletter()
	{
	    // Verify the nonce.
		$nonce = esc_attr( $_REQUEST['_wpnonce'] );
		if ( !wp_verify_nonce( $nonce, 'nl_send_campaign' ) ) {
			die( 'Go get a life script kiddies' );
		}

		$stored_id = intval($_GET['id']);
		$ready_nl = $this->model->getNewsletter($stored_id);
		$mc_res = $this->newsletter->sendCampaign($ready_nl->campaign_id);
		if ($mc_res) {
			$this->model->setNlStatus($ready_nl->campaign_id, '1');
			$this->setMessage(sprintf(__('<i>%s</i> successfully sent.', 'dsz-newsletter'), $ready_nl->title), 'updated');
		} else {
			if (!$mc_res) {
				$this->setMessage(__('Error sending Mailchimp campaign', 'dsz_newsletter'), 'error');
			}
		}
	}

	/**
	 * Handle campaign send
	 * @param  string $campaign_id Campaign ID
	 */
	private function executeNewsletter($campaign_id)
	{
		if($this->newsletter->sendCampaign($campaign_id)) {
			$this->setMessage(__('Successful campaign sending', 'dsz-newsletter'), 'updated');
			$this->model->setNlStatus($campaign_id, 1);
		} else {
			$this->setMessage(__('Failed to send campaign', 'dsz-newsletter'), 'error');
			$this->model->setNlStatus($campaign_id, 9);
		}
	}

	/**
	 * Generate campaign content sections
	 * @param  array $data Given newsletter data
	 * @return object       Campaign content sections
	 */
	private function getNlSections($data)
	{
		$lead = $this->dsz->getMusorById($data['lead-id']);
		$utm = $this->generateUTM($data);

		$view = new \cncNL\View();
		if (!empty($data['nnews'])) {
			$nnews = $this->model->prepareNnewsList($data['nnews']['items']);
			$view->assign('nnews', $nnews);
			$nnews_html = $view->render('nl-nnews-list');
		} else {
			$nnews_html = '';
		}
		if (!empty($data['featured'])) {
			$featured_shows = $this->model->prepareShowsList($data['featured']['items']);
			$view->assign('featured_shows', $featured_shows);
			$view->assign('utm', $utm);
			$featured_html = $view->render('nl-featured-list');
		} else {
			$featured_html = '';
		}
		if (!empty($data['recommendations'])) {
			$recommended_shows = $this->model->prepareShowsList($data['recommendations']['items']);
			$view->assign('recommended_shows', $recommended_shows);
			$view->assign('utm', $utm);
			$recommended_html = $view->render('nl-recommended-list');
		} else {
			$recommended_html = '';
		}

		if(!empty($data['yt-url'])) {
			$yt_image = '<a href="' . $data['yt-url'] . '"><img src="' . $view->getVideoThumbnail($data['yt-url']) . '" /></a>';
		} else {
			$yt_image = '';
		}
		$sections = (object) [
			'lead_title' => $lead->cim,
			'lead_image' => '<img class="head-lead-image" src="' . $data['lead-image'] . '" />',
			'lead_excerpt' => $this->dsz->getMusorExcerpt($data['lead-id']),
			'lead_button' => '<a href="' . get_site_url() . '/eloadasok/' .
				$lead->seo . '?' . $utm . '">TOVÁBB >></a>',
			'nnews_list' => $nnews_html,
			'featured_list' => $featured_html,
			'recommended_list' => $recommended_html,
			'youtube_image' => $yt_image,
			'youtube_title' => $data['yt-title'],
			'utm' => $utm,
		];
		return $sections;
	}

	/**
	 * Generate UTM url variables
	 * @param  array $data  Newsletter data structure
	 * @return string                Generated variables
	 */
	public function generateUTM($data)
	{
		$segment_name = $this->getSegmentNameById($data['segment']);
		$campaign_name = $data['title'];
		$utm = 'utm_source=Dumaszinhaz-nezoi-lista-' .
			$segment_name.'&amp;utm_medium=email&amp;utm_campaign=' .
			sanitize_title($campaign_name);
		return $utm;
	}

	/**
	 * Create a list from give serialized data
	 * @param  string $data Serialized data
	 * @return array       Structured data
	 */
	private function listifySelectedList($data)
	{
		$data = unserialize($data);
		if (!empty($data)) {
			$list = [];
			foreach ($data['items'] as $show_id) {
				$show = $this->dsz->getMusorById($show_id);

				$date = $this->datetimeToShort($show->ido);
				$list[] = [
					'id' => $show->id,
					'title' => $show->cim,
					'date' => $date,
					'location' => $show->helyszin_nev,
				];
			}
		} else {
			$list = '';
		}
		return $list;
	}

	/**
	 * Create a list from given serialized nnews data
	 * @param  string $data Serialized data
	 * @return array       Structured data
	 */
	private function listifySelectedNnewsList($data)
	{
		$data = unserialize($data);
		if (!empty($data)) {
			$list = [];
			foreach ($data['items'] as $nnews_id) {
				$list[] = [
					'id' => $nnews_id,
					'title' => get_the_title($nnews_id),
				];
			}
		} else {
			$list = '';
		}
		return $list;
	}

	/**
	 * Convert db datetime to shorter format
	 * @param  string $datetime Datetime format
	 * @return string           Shorter date format
	 */
	public function datetimeToShort($datetime)
	{
		$full_date = strtotime($datetime);
		return date('y.m.d', $full_date);
	}
}
