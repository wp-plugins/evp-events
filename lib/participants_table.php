<?php

class Participants_List_Table extends WP_List_Table {

	/**
	 * Constructor, we override the parent to pass our own arguments
	 * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	 */
	
	var $event_id;
	
	 function __construct($event_id) {
	 	
	 	$this->event_id = $event_id;
	 	
		 parent::__construct( array(
		'singular'=> __("Participant", "evp-event"), //Singular label
		'plural' => __("Participants", "evp-event"), //plural label, also this well be one of the table css class
		'ajax'	=> false //We won't support Ajax for this table
		) );
	 }

	function get_columns() {
		return $columns= array(
			'col_name'	   => __("Company name", "evp-event"),
			'col_pay_guarantee' => __("Payment guaranty", "evp-event"),
			'col_pay_guarantee_confirmed' => __("Payment guaranty confirmed", "evp-event"),
			'col_pre_bill' => __("PreBill", "evp-event"),
			'col_pay_paid_in' => __("Paid in", "evp-event"),
			'col_amount'	=> __("Amount", "evp-event"),
			'col_created'	=> __("Created", "evp-event")
		);
	}


	public function get_sortable_columns() {
		return $sortable = array(
			'col_amount'=> array('invoice.amount', true),
			'col_name'=> array('company.name', true),
			'col_created'=> array('invoice.created', true)
		);
	}

	
	function prepare_items() {

		global $wpdb, $_wp_column_headers;

		$screen = get_current_screen();


		/* -- Preparing your query -- */
		$query = "			
			SELECT *, invoice.id, invoice.created FROM {$wpdb->prefix}event_invoices as invoice 
			INNER JOIN {$wpdb->prefix}event_groups as `group` ON (invoice.group_id = `group`.id)
			LEFT JOIN {$wpdb->prefix}event_companies as company ON (company.id = `group`.company_id)
			WHERE `group`.event_id = '$this->event_id'
		
		";
		
		/* -- Ordering parameters -- */
		    //Parameters that are going to be used to order the result
		$orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'ASC';
		    $order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : '';
		if(!empty($orderby) & !empty($order)){
			 $query.=' ORDER BY '.$orderby.' '.$order;
		}
		else {
			$query .= " ORDER BY invoice.id DESC";
		}
		

		/* -- Pagination parameters -- */
		//Number of elements in your table?
		$totalitems = $wpdb->query($query); //return the total number of affected rows
		//How many to display per page?
		$perpage = 20;
		//Which page is this?
		$paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
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

		$hidden = array();

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		/* -- Fetch the items -- */
		$this->items = $wpdb->get_results($query);
	}
	
	/**
	 * Display the rows of records in the table
	 * @return string, echo the markup of the rows
	 */
	function display_rows() {

		//Get the records registered in the prepare_items method
		$records = $this->items;
		$columns = $this->get_columns();
	
		//Loop for each record
		if(!empty($records)){
			foreach($records as $rec){
		
				//Open the line
				echo '<tr id="record_'.$rec->id.'">';
					foreach ( $columns as $column_name => $column_display_name ) {
				
						//Style attributes for each col
				
						$class = "class='$column_name column-$column_name'";
						$style = "";

						$attributes = $class . $style;
						
						//edit link
						$confirm_link  = admin_url().'admin.php?page=event-'.$this->event_id.'-participants&action=view&id='.$rec->id;

						//Display the cell
						switch ( $column_name ) {
				
							case "col_name": 
								if(isset($rec->name)) {
									echo '<td '.$attributes.'><strong><a href="'.$confirm_link.'" title="Edit">'.stripslashes($rec->name).'</a></strong></td>'; 
								}
								else {
									echo '<td '.$attributes.'><strong><a href="'.$confirm_link.'" title="Edit">-</a></strong></td>';
								}
							break;
							case "col_pay_guarantee": 
								echo '<td '.$attributes.'>';
								echo $rec->payment_type == 1 ? __('Yes', 'evp-event').'</td>' : __('No', 'evp-event').'</td>';
								echo '</td>';
							break;
							case "col_pay_guarantee_confirmed": 
								echo '<td '.$attributes.'>';
								if($rec->payment_type != 1) 
									echo '-';
								else
									echo $rec->paid_in == 0 ? __('No', 'evp-event').'</td>' : __('Yes', 'evp-event').'</td>';
								echo '</td>';
							break;
							case 'col_pre_bill':
								echo '<td '.$attributes.'>';
								echo $rec->payment_type == 2 ? __('Yes', 'evp-event').'</td>' : __('No', 'evp-event').'</td>';
								break;
							case "col_pay_paid_in":
								echo '<td '.$attributes.'>';
								if($rec->payment_type == 1 ) 
									echo '-';
								else
									echo $rec->paid_in == 0 ? __('No', 'evp-event').'</td>' : __('Yes', 'evp-event').'</td>';
								echo '</td>';
							break;
							case "col_amount": echo '<td '.$attributes.'>'.stripslashes($rec->amount).'</td>'; break;
							case "col_created": echo '<td '.$attributes.'>'.stripslashes($rec->created).'</td>'; break;				
						}
					}
						//Close the line
				echo'</tr>';
			}
		}
	}
}





?>
