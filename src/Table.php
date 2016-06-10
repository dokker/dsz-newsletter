<?php
namespace cncNL;

class Table extends \WP_List_Table {
	private $model;
	private $NL;

	function __construct()
	{
		parent::__construct(array(
	      'singular'=> 'wp_list_newsletter', //Singular label
	      'plural' => 'wp_list_newsletters', //plural label, also this well be one of the table css class
	      'ajax'   => false //We won't support Ajax for this table
	    ));
		$this->model = new \cncNL\Model();
		$this->NL = new \cncNL\Newsletter();
	}

	/**
	 * Add extra markup in the toolbars before or after the list
	 * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	public function extra_tablenav( $which ) {
	   if ( $which == "top" ){
	      //The code that goes before the table is here
	      // echo "Hello, I'm before the table";
	   }
	   if ( $which == "bottom" ){
	      //The code that goes after the table is there
	      // echo "Hi, I'm after the table";
	   }
	}	

	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	public function get_columns() {
		$columns = [
			'col_nl_id' => __('ID', 'dsz-newsletter'),
			'col_nl_title' => __('Title', 'dsz-newsletter'),
			'col_nl_campaign' => __('Campaign', 'dsz-newsletter'),
			'col_nl_creation' => __('Creation', 'dsz-newsletter'),
			'col_nl_status' => __('Status', 'dsz-newsletter'),
		];
		return $columns;
	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	public function prepare_items() {
	   global $wpdb, $_wp_column_headers;
	   $screen = get_current_screen();
	   $db_table = $this->model->getTableName();

	   /* -- Preparing your query -- */
	        $query = "SELECT `id`,`title`,`campaign_id`,`creation`,`status`, `archive_url` FROM $db_table";

	   /* -- Ordering parameters -- */
	       //Parameters that are going to be used to order the result
	       // $orderby = !empty($_GET["orderby"]) ? \esc_sql($_GET["orderby"]) : 'ASC';
	       // $order = !empty($_GET["order"]) ? \esc_sql($_GET["order"]) : '';
	       // if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }
		   $query.=' ORDER BY `creation` DESC'; 

	   /* -- Pagination parameters -- */
	        //Number of elements in your table?
	        $totalitems = $wpdb->query($query); //return the total number of affected rows
	        //How many to display per page?
	        $perpage = 20;
	        //Which page is this?
	        $paged = !empty($_GET["paged"]) ? \esc_sql($_GET["paged"]) : '';
	        //Page Number
	        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
	        //How many pages do we have in total?
	        $totalpages = ceil($totalitems/$perpage);
	        //adjust the query to take pagination into account
	       if(!empty($paged) && !empty($perpage)){
	          $offset=($paged-1)*$perpage;
	         $query.=' LIMIT '.(int)$offset.','.(int)$perpage;
	       }

	   /* -- Register the pagination -- */
	      $this->set_pagination_args( array(
	         "total_items" => $totalitems,
	         "total_pages" => $totalpages,
	         "per_page" => $perpage,
	      ) );
	      //The pagination links are automatically built according to those parameters

	   /* -- Register the Columns -- */
	      $columns = $this->get_columns();
	      // $_wp_column_headers[$screen->id]=$columns;
	      $hidden = array();
	      $sortable = array();
	      $this->_column_headers = array($columns, $hidden, $sortable);

	   /* -- Fetch the items -- */
	      $this->items = $wpdb->get_results($query);
	}

	/**
	 * Display the rows of records in the table
	 * @return string, echo the markup of the rows
	 */
	public function display_rows() {

	   //Get the records registered in the prepare_items method
	   $records = $this->items;

	   //Get the columns registered in the get_columns and get_sortable_columns methods
	   // list( $columns, $hidden ) = $this->get_column_info();
	   $columns = $this->get_columns();

	   //Loop for each record
	   if(!empty($records)){
	   	foreach($records as $rec){

	      //Open the line
	   		echo '<tr id="record_'.$rec->id.'">';
	   		foreach ( $columns as $column_name => $column_display_name ) {

		         //Style attributes for each col
	   			$class = "class='$column_name column-$column_name has-row-actions'";
	   			$style = "";
		         // if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
	   			$attributes = $class . $style;

		        //edit link
				$editlink = admin_url('admin.php?page=hirlevel&id='.(int)$rec->id.'&action=edit');

				switch ($rec->status) {
					case 0: $status = __('Waiting', 'dsz-newsletter'); break;
					case 1: $status = __('Sent', 'dsz-newsletter'); break;
					case 9: $status = __('Failed', 'dsz-newsletter'); break;
				}

				// Create delete action
				$delete_nonce = wp_create_nonce( 'sp_delete_customer' );
				$actions = [
					'delete' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">' . __('Delete', 'dsz-newsletter') . '</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $rec->id ), $delete_nonce )
				];

		         //Display the cell
	   			switch ( $column_name ) {
	   				case "col_nl_id":  echo '<td '.$attributes.'>'.stripslashes($rec->id).'</td>';   break;
	   				case "col_nl_title": echo '<td '.$attributes.'><strong><a href="' . $editlink . '">'.stripslashes($rec->title).'</a></strong>' . $this->row_actions( $actions ) . '</td>'; break;
	   				case "col_nl_campaign": echo '<td '.$attributes.'><a target="_blank" href="' . $rec->archive_url . '">'.stripslashes($rec->campaign_id).'</a></td>'; break;
	   				case "col_nl_creation": echo '<td '.$attributes.'>'.$rec->creation.'</td>'; break;
	   				case "col_nl_status": echo '<td '.$attributes.'>'.$status.'</td>'; break;
	   			}
	   		}

		    //Close the line
	   		echo'</tr>';
	   	}
	   }
	}
}
