<?php
date_default_timezone_set('Europe/Vilnius');

if(!defined('WPE')) die();

add_action('admin_menu', 'wpe_plugin_menu' );

ob_start();


// Setting Administration Menu
function wpe_plugin_menu() {
	global $wpdb;
	
	add_menu_page(__('EVP: new', 'evp-event'), __('Evp: new', 'evp-event'), 'manage_options', 'create-event', 'wpe_admin_event_form' );

	$events = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}event_events");
	
	foreach($events as $event) {
		add_menu_page(__('EVP: #'. $event->id, 'evp-event'), __('EVP: #'. $event->id, 'evp-event'), 'manage_options', "event-{$event->id}-participants", 'wp_admin_participants' );
	    add_submenu_page("event-{$event->id}-participants", __('Discount codes', 'evp-event'), __('Discount codes', 'evp-event'), 'manage_options', "event-{$event->id}-discount-codes", 'wpe_admin_discount_codes' );
	    add_submenu_page("event-{$event->id}-participants", __('Statistics', 'evp-event'), __('Statistics', 'evp-event'), 'manage_options', "event-{$event->id}-stats", 'wpe_admin_stats');
	    add_submenu_page("event-{$event->id}-participants", __('Templates', 'evp-event'), __('Templates', 'evp-event'), 'manage_options', "event-{$event->id}-email-templates", 'wpe_email_template' );
	    add_submenu_page("event-{$event->id}-participants", __('Pair device', 'evp-event'), __('Pair device', 'evp-event'), 'manage_options', "event-{$event->id}-checker-rights", 'wpe_admin_checker_rights' );
	    add_submenu_page("event-{$event->id}-participants", __('Settings', 'evp-event'), __('Settings', 'evp-event'), 'manage_options', "event-{$event->id}-settings", 'wpe_admin_event_form' );
	}
}


// Admin actions
function wp_admin_participants() {
	switch(@$_GET['action']) {
		
		case 'remove_event':
			wpe_admin_event_remove();
		break;
		case 'remove_order':
			wpe_admin_order_remove();
			break;
		
		case 'view':
			wpe_admin_event_view();
		break;
		case 'guarantee_confirm':
			wpe_admin_event_guarantee_confirm();
		break;


		default:
			wpe_admin_event_list();
	}
}

function wpe_admin_event_list() {
	global $wpdb;
	
	if(!class_exists('Participants_List_Table')){
		require_once( WPE_PLUGIN_DIR . DS . '/lib/participants_table.php' );
	}

	
	$table = new Participants_List_Table(admin_get_event_id());
	$table->prepare_items();

	require WPE_PLUGIN_DIR . DS . 'tmpl' .  DS . 'admin' . DS . 'participants_list.php';
}

function wpe_admin_event_view() {
	global $wpdb;
	
	if (!current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'evp-event' ) );
	}

	$invoice_id = (int) $_GET['id'];
	
	if($invoice_id == 0) {
		die(__(sprintf("Invoice id:%s wasn't found", $invoice_id), 'evp-event'));
	}

	if(isset($_GET['activate'])) {		
		wpe_payment_callback(false, (int) $_GET['id']);
	}

	$event_id = admin_get_event_id();
	$event_cfg = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}event_events WHERE id = '{$event_id}'", ARRAY_A); 
	
	$invoice = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}event_invoices WHERE id = $invoice_id");
	$group = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}event_groups WHERE id = {$invoice->group_id}");
	if(!is_null($group->company_id)) {
		$company = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}event_companies WHERE id = {$group->company_id}");
	}
	$users = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}event_customers as customer 
				     LEFT JOIN {$wpdb->prefix}event_tickets as ticket ON (ticket.id = customer.ticket_id)
				     WHERE customer.group_id = {$group->id}");

	require WPE_PLUGIN_DIR . DS . 'tmpl' .  DS . 'admin' . DS . 'participants_view.php';
}

function wpe_admin_event_guarantee_confirm() {
	
	if (!current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'evp-event' ) );
	}
	
	wpe_payment_callback(false, (int) $_GET['id']);
	
	
	$location = 'admin.php?page=event-'.admin_get_event_id().'-participants&action=view&success&id='.$_GET['id'];
	wp_redirect($location);
	
}

function wpe_admin_order_remove() {
	global $wpdb;
	
	if (!current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'evp-event' ) );
	}
	
	$invoice_id = (int) $_GET['id'];

	if($invoice_id == 0) {
		die(__('System error', 'evp-event'));
	}

	
	$group_id = $wpdb->get_var("SELECT group_id FROM {$wpdb->prefix}event_invoices WHERE id = {$invoice_id} ");
	$company_id = $wpdb->get_var("SELECT company_id FROM {$wpdb->prefix}event_groups WHERE id = {$group_id}");
	
	$tickets_ids = $wpdb->get_col("SELECT ticket_id FROM {$wpdb->prefix}event_customers WHERE group_id = {$group_id}"); 
	
	$tickets_ids = implode(',', $tickets_ids);			
	
	if(!empty($tickets_ids)) {
		$q3 = $wpdb->prepare("DELETE FROM {$wpdb->prefix}event_tickets WHERE id IN ({$tickets_ids})");
	}
	else {
		$q3 = "";
	}

	// Istrinam dalyvius
	$q2 = $wpdb->prepare("DELETE FROM {$wpdb->prefix}event_customers WHERE group_id = {$group_id}");

	// Istrinam saskaita
	$q4 = $wpdb->prepare("DELETE FROM {$wpdb->prefix}event_invoices WHERE group_id = {$group_id}");

	// Istrinam grupe
	$q5 = $wpdb->prepare("DELETE FROM {$wpdb->prefix}event_groups WHERE id = {$group_id}");
	
	// Istrinam kompanija
	$q6 = $wpdb->prepare("DELETE FROM {$wpdb->prefix}event_companies WHERE id = {$company_id}");

	
	$a2 = $wpdb->query($q2);

	$a3 = $wpdb->query($q3);
	
	$a4 = $wpdb->query($q4);

	$a5 = $wpdb->query($q5);
	
	$a6 = $wpdb->query($q6);
	
	$event_id = admin_get_event_id();
	$location = 'admin.php?page=event-'.$event_id.'-participants';
	wp_redirect($location);
	
}

function wpe_email_template() {
	if (!current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'evp-event' ) );
	}
	
	add_filter("mce_external_plugins", "add_fulpage_tinymce_plugin");

	$template = empty($_POST) ? 'mail_to_customer.html' : $_POST['template'];
	
	$event_id = admin_get_event_id();
	
	$tmpl_path = WPE_PLUGIN_DIR . DS . 'tmpl' .  DS . 'personalized_templates' . DS . $event_id . '_' . $template;

	if(isset($_POST['submit_button'])) {
		
		remove_filter("the_content", "wptexturize");
		remove_filter("the_content", "convert_chars");
		
		echo '<div id="message" class="updated below-h2"><p>' . __('Template has been succesfully saved', 'evp-event') .'</div>';
		$_POST['content'] = stripslashes($_POST['content']);
		file_put_contents($tmpl_path, $_POST['content']);
		$html = $_POST['content'];
	}
	else{
		$html = file_get_contents($tmpl_path);
	}
	
	$html = preg_replace("~[\r\n]~", ' ', $html);
	
	
	add_filter( 'the_content', 'filter_function_name' );
	require WPE_PLUGIN_DIR . DS . 'tmpl' .  DS . 'admin' . DS . 'template_editor.php';
}
function add_fulpage_tinymce_plugin($plugin_array) {
	$plugin_array['fullpage'] = plugins_url() . '/evp-event/tmpl/js/fullpage/editor_plugin.js';
	return $plugin_array;
}

/*
 * Statistics
 */

function wpe_admin_stats() {
	
	global $wpdb;
	
	$event_id = (int) admin_get_event_id();
	
	$event_cfg = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}event_events WHERE id = '{$event_id}'", ARRAY_A);
	
	$tickets_count = $wpdb->get_var("SELECT count(*) FROM {$wpdb->prefix}event_tickets WHERE event_id = '{$event_id}'");
	
	$orders_count = $wpdb->get_var("SELECT count(*) FROM {$wpdb->prefix}event_invoices WHERE paid_in = 1 AND event_id = {$event_id}");
	
	$unconfirmed_payment_guaranties = $wpdb->get_var("SELECT count(*) FROM {$wpdb->prefix}event_invoices WHERE paid_in = 0 AND payment_type = 1 AND event_id = $event_id ");
	
	$unpaid_prebill = $wpdb->get_var("SELECT count(*) FROM {$wpdb->prefix}event_invoices WHERE paid_in = 0 AND payment_type = 2
	 AND event_id = '{$event_id}' ");
		
	$mokejimai_gateway = $wpdb->get_var("SELECT sum(amount) FROM {$wpdb->prefix}event_invoices WHERE payment_type = 0 AND paid_in = 1 AND event_id = '{$event_id}'");
	
	$payment_guaranty = $wpdb->get_var("SELECT sum(amount) FROM {$wpdb->prefix}event_invoices WHERE payment_type = 1 AND paid_in = 1 AND event_id = '{$event_id}'");
	
	$prebill = $wpdb->get_var("SELECT sum(amount) FROM {$wpdb->prefix}event_invoices WHERE payment_type = 2 AND paid_in = 1
	AND event_id = '{$event_id}'
	");
	
	$total_amount = $wpdb->get_var("SELECT sum(amount) FROM {$wpdb->prefix}event_invoices WHERE paid_in = 1 AND event_id = '{$event_id}'");
	
	require WPE_PLUGIN_DIR . DS . 'tmpl' .  DS . 'admin' . DS . 'stats.php';
}

/*
 * Create discount codes
 */

function wpe_admin_discount_codes() {
    global $wpdb;

    $event_id = admin_get_event_id();
    switch(@$_GET['action']) {
        case 'create':
            if(empty($_POST)) {
                require WPE_PLUGIN_DIR . DS . 'tmpl' .  DS . 'admin' . DS . 'discount_codes_add.php';
            }
            else {
            		$timeStamp = time();
            	
                $wpdb->insert($wpdb->prefix . 'event_discount_codes', array(
                    'code'      => $_POST['code'],
                	'event_id'	=> $event_id,
                    'discount'  => (float) $_POST['discount'],
                    'amount'    => (int) $_POST['amount'],
                    'amount_left'    => (int) $_POST['amount'],
                    'created'	=> date('Y-m-d H:i:s', $timeStamp)  //current_time('mysql') //date('Y-m-d H:i:s')
                ));
                
				
               $location = "admin.php?page=event-{$event_id}-discount-codes&success";
               wp_redirect($location);
            }
            break;
        case 'remove':
        break;
        default:
            if(!class_exists('Codes_List_Table')){
                require_once( WPE_PLUGIN_DIR . DS . '/lib/codes_table.php' );
            }
            $table = new Codes_List_Table($event_id);
            $table->prepare_items();
            require WPE_PLUGIN_DIR . DS . 'tmpl' .  DS . 'admin' . DS . 'discount_codes_list.php';
    }
}

/*
 * Create or edit event
 */
function wpe_admin_event_form() {
	
	global $wpdb;
	
	$id = admin_get_event_id();
	
	// If form wasn't submited
	if(empty($_POST)) {
		if(is_null($id)) {
			$data = array();
			
			$result = $wpdb->get_row("SHOW TABLE STATUS LIKE '{$wpdb->prefix}event_events'", ARRAY_A);
			
			$data['id'] = $result['Auto_increment'];
			
			$data['placeholder'] = "[EVP_EVENT_{$data['id']}]";
			$data['valid_from'] = date('Y-m-d H:m:s'); //current_time('mysql');
			$data['valid_to'] = null;
			$data['ticket_amount'] = 0;
			$data['currency'] = 'LTL';
			$data['public']['enabled'] = true;
			$data['public']['ticket_price'] = null;
			$data['public']['webtopay'] = null;
			$data['public']['prebill'] = null;
			$data['public']['payment_guaranty'] = null;
			$data['public']['discount_persons'] = null;
			$data['public']['discount_percent'] = null;
			$data['privatee']['enabled'] = true;
			$data['private']['ticket_price'] = null;
			$data['private']['webtopay'] = null;
			$data['private']['prebill'] = null;
			$data['private']['payment_guaranty'] = null;
			$data['private']['discount_persons'] = null;
			$data['private']['discount_percent'] = null;
			$data['webtopay_project_id'] = null;
			$data['webtopay_project_sign'] = null;
			$data['webtopay_project_test'] = null;
			$data['email_from'] = null;
			$data['email_from_name'] = null;
			$data['email_subject'] = null;
			$data['smtp']['enabled'] = false;
			$data['smtp']['host'] = null;
			$data['smtp']['port'] = null;
			$data['smtp']['username'] = null;
			$data['smtp']['password'] = null;
			$data['email_to_cust_sb'] = null;
			$data['email_to_rep1_sb'] = null;
			$data['email_to_rep2_sb'] = null;
			$data['email_to_rep3_sb'] = null;
			
		}
		else {
			$data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}event_events WHERE id = '{$id}'", ARRAY_A);
			
			$data['placeholder'] = "[EVP_EVENT_{$data['id']}]";
			
			$data['tickets'] = unserialize($data['tickets']);			
			$data['smtp'] = unserialize($data['smtp_options']);
		}
				
	}		
	// If form was submited
	else {	
		
		//Filtering inputs
		$_POST = array_map_deep($_POST, 'stripslashes');
		$_POST = array_map_deep($_POST, 'sanitize_text_field');
		$data = $_POST;
		
		// Kadangi nepazymeti checkbox'ai negrazina nieko, nustatom pradines reiksmes
		if(!isset($data['smtp']) || !isset($data['smtp']['enabled'])) {
			$data['smtp'] = array('enabled' => false, 'host' => null, 'port' => null, 'username' => null, 'password' => null);
		}
		
		if(!isset($data['webtopay_project_test'])) {
			$data['webtopay_project_test'] = null;
		}
		
		// Insert new event
		if(is_null($id)) {
			/*
			if(empty($_FILES['ticket']) || !isPngFile($_FILES['ticket']['tmp_name'])) {
				$errors[] = __('Could not save event, invalid ticket template. Should be *.png file', 'evp-event');
			}
			*/
			if(empty($errors)) {
				$wpdb->insert($wpdb->prefix . 'event_events',array(
						'valid_from' => $data['valid_from'],
						'valid_to'	=> $data['valid_to'],
						'ticket_amount' => $data['tickets']['0']['amount'], //$data['ticket_amount'],
						'webtopay_project_id' 	=> $data['webtopay_project_id'],
						'webtopay_project_sign' => $data['webtopay_project_sign'],
						'webtopay_project_test' => $data['webtopay_project_test'],
						'currency' => $data['currency'],
						'tickets' => serialize($data['tickets']),
						'smtp_options' 	=> serialize($data['smtp']),
						'email_from'		=> $data['email_from'],
						'email_from_name' 	=> $data['email_from_name'],
						'email_to_cust_sb' => $data['email_to_cust_sb'],
						'email_to_rep1_sb' => $data['email_to_rep1_sb'],
						'email_to_rep2_sb' => $data['email_to_rep2_sb'],
						'email_to_rep3_sb' => $data['email_to_rep3_sb'],
						'unique_code' => _ticket_nr()
					));
				
				$id = $wpdb->insert_id;
				
				//move_uploaded_file($_FILES['ticket']['tmp_name'], WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'ticket_templates' . DS . $id . '.png');
			
			
				$p1 = WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'templates' . DS;
				$p2 = WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'personalized_templates' . DS;
				
				copy($p1 . DS . 'invoice_advanced.html', $p2 . DS . $id . '_invoice_advanced.html');
				copy($p1 . DS . 'mail_to_representative1.html', $p2 . DS . $id . '_mail_to_representative1.html');
				copy($p1 . DS . 'mail_to_representative2.html', $p2 . DS . $id . '_mail_to_representative2.html');
				copy($p1 . DS . 'mail_to_customer.html', $p2 . DS . $id . '_mail_to_customer.html');
				copy($p1 . DS . 'mail_to_representative3.html', $p2 . DS . $id . '_mail_to_representative3.html');
				copy($p1 . DS . 'payment_guaranty.html', $p2 . DS . $id . '_payment_guaranty.html');
				copy($p1 . DS . 'ticket.html', $p2 . DS . $id . '_ticket.html');
				
				$location = "admin.php?page=event-$id-settings";
				wp_redirect($location);
			}
		}
		// Edit existing event
		else {
			
			/*
			if(!empty($_FILES['ticket']) || isPngFile($_FILES['ticket']['tmp_name'])) {
				//move_uploaded_file($_FILES['ticket']['tmp_name'], WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'personalized_templates' . DS . $id . '_ticket.html');
				//WPE_PLUGIN_DIR. "/tmpl/personalized_templates/{$ticket->event_id}_ticket.html
			}
			else {
				$errors[] = __('Ticket template was not updated, invalid ticket template. Should be *.png file', 'evp-event');
			}
			*/
			
			$file_exists = WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'personalized_templates' . DS . $id . '_ticket.html';
			$file_exists2 = WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'personalized_templates' . DS . $id . '_mail_to_representative1.html';
			$file_exists3 = WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'personalized_templates' . DS . $id . '_mail_to_representative2.html';
			$file_exists4 = WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'personalized_templates' . DS . $id . '_mail_to_customer.html';
			$file_exists5 = WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'personalized_templates' . DS . $id . '_mail_to_representative3.html';
			$file_exists6 = WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'personalized_templates' . DS . $id . '_payment_guaranty.html';
			$file_exists7 = WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'personalized_templates' . DS . $id . '_invoice_advanced.html';
			
			if(empty($file_exists) || empty($file_exists2) || empty($file_exists3) || empty($file_exists4) || empty($file_exists5) || empty($file_exists6) || empty($file_exists7)){
				$errors[] = __('Ticket template was not updated, invalid ticket template. Should be *.html file', 'evp-event');
			}
			else {
			
			if(empty($errors)) {
				$wpdb->update($wpdb->prefix . 'event_events',array(
						'valid_from' 	=> $data['valid_from'],
						'valid_to'		=> $data['valid_to'],
						'ticket_amount' => $data['tickets']['0']['amount'], //$data['ticket_amount'],
						'webtopay_project_id' 	=> $data['webtopay_project_id'],
						'webtopay_project_sign' => $data['webtopay_project_sign'],
						'webtopay_project_test' => $data['webtopay_project_test'],
						'currency' 			=> $data['currency'],
						'tickets' 			=> serialize($data['tickets']),
						'smtp_options'		=> serialize($data['smtp']),
						'email_from'		=> $data['email_from'],
						'email_from_name' 	=> $data['email_from_name'],
						'email_to_cust_sb' => $data['email_to_cust_sb'],
						'email_to_rep1_sb' => $data['email_to_rep1_sb'],
						'email_to_rep2_sb' => $data['email_to_rep2_sb'],
						'email_to_rep3_sb' => $data['email_to_rep3_sb'],
				),array(
					'id' => $id
				));
				
				$wpdb->update($wpdb->prefix . 'event_tickets', array(
						'valid_from' => $data['valid_from'],
						'valid_to'	=> $data['valid_to']
				), array('event_id' => $id));
						
				$edit_success = true;
			}
		}
		}
		$data['placeholder'] = "[EVP_EVENT_{$data['id']}]";
			
	}
		
	if(isset($_GET['noheader']) && (!empty($errors) || $id)) {
		require_once(ABSPATH . 'wp-admin/admin-header.php');
	}	
		
	require WPE_PLUGIN_DIR . DS . 'tmpl' .  DS . 'admin' . DS . 'event_add.php';
}

function wpe_admin_event_remove() {
	global $wpdb; 
	$id = admin_get_event_id();
	$wpdb->query("
		DELETE FROM {$wpdb->prefix}event_events WHERE id = '$id'				
	");
	$wpdb->query("
		DELETE FROM {$wpdb->prefix}event_discount_codes WHERE event_id = '$id'				
	");
	wp_redirect('admin.php?page=create-event');
}


function wpe_admin_checker_rights() {
	
	global $wpdb;
	
	$id = admin_get_event_id();
	$code = $wpdb->get_var("SELECT unique_code FROM {$wpdb->prefix}event_events WHERE id = '{$id}'");
	
	
	if(!$code) {
		die('Such event doesnt exits');
	}
	
	require (WPE_PLUGIN_DIR . '/lib/phpqrcode/phpqrcode.php');
	
	$tmp_file_name = tempnam(WPE_PLUGIN_DIR . DS . 'tmp', "link");
	
	
	QRcode::png(get_site_url()."?ticket_checker&c=".$code, $tmp_file_name, 'L', 10, 0);
	
	$raw_png = base64_encode(file_get_contents($tmp_file_name));
	
	require WPE_PLUGIN_DIR . DS . 'tmpl' .  DS . 'admin' . DS . 'checker_rights.php';
	
	unlink($tmp_file_name);
	
}


