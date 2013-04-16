<?php
/*
Plugin Name: EVP Event Organizer
Plugin URI: http://evp.lt
Description: Create ant manage your events. Print tickets with QR code and check their validity with your smartphone
Version: 0.5
Author: EVP
Text Domain: evp-event
Domain Path: /languages
*/

if(defined('WPE')) { 
	die('Several instances of this plugin could not be used');
}
else {
	define('WPE', '');
}
if(!defined( 'WPE_PLUGIN_DIR'))
	define( 'WPE_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ )));

if(!session_id())
	session_start();

if(!defined('DS')) 
	define('DS', '/');

require WPE_PLUGIN_DIR . DS . 'admin.php';
require WPE_PLUGIN_DIR . DS . 'lib' . DS .'functions.php';
require WPE_PLUGIN_DIR . DS . 'install.php';


register_activation_hook(__FILE__,  'wpe_install');
register_deactivation_hook(__FILE__, 'wpe_deactivate');
register_uninstall_hook(__FILE__, 'wpe_uninstall');

// Adding localization
add_action('plugins_loaded', 'wpe_plugin_init');
function wpe_plugin_init() {
		load_plugin_textdomain('evp-event', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );	
}
// Some pages doesn't require wordpress rendering
add_action('init', 'wpe_noheader');
function wpe_noheader() {
  
	if(isset($_GET['payment_callback'])) {
		wpe_payment_callback();
	}
	
	if(isset($_GET['event_ticket'])) {
		wpe_ticket_generate();
	}
 	
	if(isset($_GET['t'])) {
		wpe_ticket_check();
	}

	if(isset($_GET['ticket_checker'])) {
		wpe_checker();
	}

	if(isset($_GET['guaranty_template'])) {
		wpe_guaranty_template();
	}
	
	
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery.validate', plugins_url('tmpl' . DS . 'js' . DS . 'jquery.validate.js', __FILE__ ));
	wp_enqueue_script('jquery.validate', plugins_url('tmpl' . DS . 'js' . DS . 'jquery.requiredstar.min.js', __FILE__ ));
	wp_enqueue_style('admin-css', plugins_url('tmpl' . DS . 'css' . DS . 'jquery.requiredstar.css', __FILE__ ));
}

// Adding filter to content, search for [EVP_EVENT_PLUGIN] placeholder and replacing with
// our content
add_filter('the_content', 'wpe_init');
function wpe_init($content) {
	
	global $event_cfg, $wpdb;
	
	if(preg_match("#EVP_EVENT_([0-9]+)#", $content, $matches)) {
		
		$event_id = (int) $matches[1];
		
		$event_cfg = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}event_events WHERE id = '$event_id'", ARRAY_A);
		
		//Filtering inputs
		$_POST = array_map_deep($_POST, 'stripslashes');	
		$_POST = array_map_deep($_POST, 'sanitize_text_field');
		
	

		ob_start();
		if($event_cfg) {
			$event_cfg['tickets'] = unserialize($event_cfg['tickets']);
			$event_cfg['smtp'] = unserialize($event_cfg['smtp_options']);
		
				switch(@$_GET['action']) {
					case 'confirm':
						wpe_confirm();
						break;
					case 'payment_redirect':
						wpe_payment_redirect();
						break;
					case 'payment_cancel':
						wpe_payment_cancel();
						break;
					case 'payment_success':
						wpe_payment_success();
						break;
					case 'payment_guaranty':
						wpe_payment_guaranty();
						break;
					default:
						wpe_form();
				}
				
				$output = ob_get_contents();
			
		}
		else {
			$output = "<h2>".sprintf(__('Event id %s not found', 'evp-event'), $event_id)."</h2>";
		}
		ob_get_clean();
		return str_replace("[EVP_EVENT_{$event_id}]", $output, $content);
	}
	else {
		return $content;
	}
}

/*----------------------------------------------------------------------------------*/


function wpe_form() {
	
	global $event_cfg, $wpdb;
	
	$ticket_types = $wpdb->get_results("SELECT count(*) as count, payment_type FROM {$wpdb->prefix}event_invoices GROUP by payment_type", ARRAY_A);	
	$used = array();
	
	foreach($ticket_types as $t) {
		$used[$t['payment_type']] = $t['count'];
	}
	
	
	
	require WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'form.php';
}

function wpe_confirm() {

	global $event_cfg, $wpdb;
	
	if(!empty($_POST) ) {

        if(!empty($_POST['discount_code'])) {
            $_SESSION['FORM']['DISCOUNT'] =  use_discount_code($_POST['discount_code'], $event_cfg['id']);
        }

        if(!isset($_SESSION['FORM']['DISCOUNT']) || !$_SESSION['FORM']['DISCOUNT']) {
            $_SESSION['FORM']['DISCOUNT'] = false;
        }

		$_SESSION['FORM']['ERROR'] = array();
		
		foreach($_POST['users'] as $index => $user) {
			if(!is_email($user['email'])) {
				$_SESSION['FORM']['ERROR'][] = __("Invalid participant email", "evp-event");
			}
			
			if(empty($user['name'])) {
				$_SESSION['FORM']['ERROR'][] = __("Please enter participant name and surname", "evp-event");
			}
	
			if(count(explode(' ', $user['name'])) < 2) {
				$_SESSION['FORM']['ERROR'][] = __("Please enter participant name and surname", "evp-event");
			}
		}
	
		//  Saving form data to $_SESSION
		$_SESSION['FORM']['USERS']  = $_POST['users'];
		
		if(isset($_POST['ticket_price']) &&  in_array((int)$_POST['ticket_price'], array(0,1,2)))
			$_SESSION['FORM']['TICKET_PRICE'] = (int)$_POST['ticket_price'];
		else
			$_SESSION['FORM']['TICKET_PRICE'] = 0;	
			
		
		if(isset($_POST['is_company']) && $_POST['is_company'] == 'on') {
			if(empty($_POST['company']['name'])) {
				$_SESSION['FORM']['ERROR'][] = __("Enter company name", "evp-event");
			}
			
			if(empty($_POST['company']['code'])) {
				$_SESSION['FORM']['ERROR'][] = __("Enter company code", "evp-event");
			}

            if(empty($_POST['company']['address'])) {
                $_SESSION['FORM']['ERROR'][] = __("Enter company address", "evp-event");
            }
			
			if(empty($_POST['company']['pvm_code'])) {
				$_SESSION['FORM']['ERROR'][] = __("Enter company PVM code", "evp-event");
			}
			
			if(empty($_POST['company']['person_name'])) {
				$_SESSION['FORM']['ERROR'][] = __("Enter contact person name and surname", "evp-event");
			}
            else {
                if(!preg_match('/\s/', $_POST['company']['person_name'])) {
                    $_SESSION['FORM']['ERROR'][] = __("Enter contact person name and surname", "evp-event");
                }
            }

			if(empty($_POST['company']['person_phone'])) {
				$_SESSION['FORM']['ERROR'][] = __("Enter contact person phone", "evp-event");
			}
			
			if(empty($_POST['company']['person_email'])) {
				$_SESSION['FORM']['ERROR'][] = __("Enter contact person email", "evp-event");
			}
			else {
				if(!is_email($_POST['company']['person_email'])) {
					$_SESSION['FORM']['ERROR'][] = __("Contact person email is invalid", "evp-event");
				}
			}
					
			$_SESSION['FORM']['COMPANY'] = $_POST['company'];
		}
		else {
			$_SESSION['FORM']['COMPANY'] = false;
		}

		if(isset($_POST['payment_type']) && in_array($_POST['payment_type'], array('webtopay','prebill','guaranty'))) {
			$_SESSION['FORM']['payment_type'] = $_POST['payment_type'];
		}
		else {
			$_SESSION['FORM']['payment_type'] = 'webtopay';
		}
			
		if(empty($_SESSION['FORM']['USERS'])) {
			$_SESSION['FORM']['ERROR'][] = __("Please enter at least one participant", "evp-event");
		}
		
		$tid = (int) $_SESSION['FORM']['TICKET_PRICE'];
		$used_count = $wpdb->get_var("SELECT count(*) as c FROM {$wpdb->prefix}event_invoices WHERE payment_type = '$tid'");
		
		if($event_cfg['tickets'][$tid]['amount'] <= $used_count) {
			$_SESSION['FORM']['ERROR'][] = __("No tickets left", "evp-event");
		}
	}
	
	if(!empty($_POST) && empty($_SESSION['FORM']['ERROR'])) {

		$total_users = count($_SESSION['FORM']['USERS']);
        $discount = discount_for_ticket();
	    $total_price = get_total_price();
		require WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'confirm.php';
	}
	else {
		wpe_form();
	}
}

function wpe_payment_redirect() {

	global $event_cfg, $wpdb;

	if(isset($_SESSION['FORM']['USERS']) && !empty($_SESSION['FORM']['USERS']) && empty($_SESSION['FORM']['ERROR'])) {
		
		$data = $_SESSION['FORM'];
		
		// 1. Surasom duomenis i DB
		//-------------------------------
				
		// Jeigu imone registruoja savo darbuotojus, sukuriam nauja irasa `event_companies` table'e
		if(isset($data['COMPANY']) && !empty($data['COMPANY'])) {
			
			$wpdb->insert($wpdb->prefix . 'event_companies', array(
				'name' => $data['COMPANY']['name'],
                'address' => $data['COMPANY']['address'],
				'code' => $data['COMPANY']['code'],
				'pvm_code' => $data['COMPANY']['pvm_code'],
				'fax' => $data['COMPANY']['fax'],
 				'person_name'  => $data['COMPANY']['person_name'],
				'person_phone' => $data['COMPANY']['person_phone'],
				'person_email' => $data['COMPANY']['person_email'],
				'created'	   => current_time('mysql')
			));
			$company_id = $wpdb->insert_id;
		}
		else {
			$company_id = null;
		}
	
		// Sukuriam nauja bendro pirkimo grupe, net jeigu i rengini registruojasi vienas asmuo
		if(is_null($company_id)) {
			$wpdb->insert($wpdb->prefix . 'event_groups', array('id' => '', 'event_id' => $event_cfg['id']));
		}
		else {
			$wpdb->insert($wpdb->prefix . 'event_groups', array('id' => '', 'company_id' => $company_id, 'event_id' => $event_cfg['id']));
		}
		
		$group_id = $wpdb->insert_id;
		
		// Surasom dalyvius i event_customers lentele
		foreach($data['USERS'] as $user) {
			$wpdb->insert($wpdb->prefix . 'event_customers', 
				array(
					'name'  => $user['name'],
					'email' => $user['email'],
					'phone' => $user['phone'],
					'group_id' => $group_id,
					'created' => current_time('mysql')
				
				)); 
		}

        $amount = get_total_price();

        
        if($_SESSION['FORM']['payment_type'] == 'webtopay') {
        	$ptype = 0;
        }
        elseif($_SESSION['FORM']['payment_type'] == 'guaranty') {
        	$ptype = 1;
        }
        else {
        	$ptype = 2;
        }
        $payment_type =
        
		$wpdb->insert($wpdb->prefix . 'event_invoices', array(
			'event_id' => $event_cfg['id'],
			'amount'   => $amount,
			'created'  => current_time('mysql'),
			'group_id' => $group_id,
			'payment_type' => $ptype,
			'paid_in_date' => current_time('mysql')
			//'ticket_type' => $_SESSION['FORM']['TICKET_PRICE']
		));
		
		$invoice_id = $wpdb->insert_id;
		
		// 2. Formuojam web2pay uzklausa
		//-------------------------------------	
		
		// Registruojasi privatus asmenys
		if(empty($data['COMPANY'])) {
			list($firstname, $lastname) = explode(" ", $data['USERS'][1]['name']);
			$contact['p_firstname'] = $firstname;
			$contact['p_lastname'] = $lastname;
			$contact['p_email'] = $data['USERS'][1]['email'];			
		}
		// Registruojasi imone
		else {
			list($firstname, $lastname) = explode(" ", $data['COMPANY']['person_name']);
			$contact['p_email'] = $data['COMPANY']['person_email'];
			$contact['p_firstname'] = $firstname;
			$contact['p_lastname'] = $lastname;
		}

        $date = strtotime("+3 day", time());
        $time_limit = date("Y-m-d h:m:s", $date);

		$request_data = array(
			'projectid'     => $event_cfg['webtopay_project_id'],
			'sign_password' => $event_cfg['webtopay_project_sign'],
			'orderid'       => $invoice_id,
			'amount'        => ceil($amount * 100),
			'currency'      => $event_cfg['currency'],
			'country'       => 'LT',
			'accepturl'     => add_query_arg( 'action', 'payment_success', get_permalink()),
			'cancelurl'     => add_query_arg( 'action', 'payment_cancel', get_permalink()),
			'callbackurl'   => get_site_url().'/?payment_callback',
			'test'          => $event_cfg['webtopay_project_test'],
            'time_limit'    => $time_limit,
			'event_id'		=> $event_cfg['id']
		);

        if($data['payment_type'] == 'prebill') {
            $request_data['payment'] = 'lthand';
        }

		$request_data = array_merge($request_data, $contact);
			
		if(!class_exists('WebToPay')) {		
			require_once('lib/WebToPay.php');
		}
	//	print_r($request_data);
		try {
            $request = WebToPay::buildRequest($request_data);
		}
		catch (WebToPayException $e) {
		    echo $e->getMessage();
		}

        if($data['payment_type'] == 'prebill') {
//print_r($request);
            $id = getRequestId($request);
			
            if($id == 0) {
                die(__('System error', 'evp-event'));
            }
            generateBillPdf($id, $invoice_id, $data);
            $pdf_url =  WP_PLUGIN_URL . '/evp-events/pdf/' . $invoice_id .'.pdf';
            require WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'payment_bankwire.php';
            _wpe_send_prebill($invoice_id, $data['COMPANY']['person_email'], $pdf_url);
            unset($_SESSION['FORM']);
        }
        else {
		    require WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'payment_redirect.php';
        }
	}
	else {
		wpe_form();
	}
					
}

function  wpe_guaranty_template() {
	global $wpdb;

	$company_id = (int) $_GET['id'];
	
	
	$company = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}event_companies WHERE id = $company_id");

	$event_id = $wpdb->get_var("SELECT event_id FROM {$wpdb->prefix}event_groups WHERE company_id = '$company_id'");
	
	if(empty($company)) {
		die('Klaida');
	}

	$users = $wpdb->get_col("SELECT customer.name FROM {$wpdb->prefix}event_customers as customer
				  INNER JOIN {$wpdb->prefix}event_groups as `group` ON (customer.group_id = `group`.id)
				  WHERE `group`.company_id = {$company_id}
				");
					
	$html = file_get_contents(WPE_PLUGIN_DIR. "/tmpl/personalized_templates/{$event_id}_payment_guaranty.html");	
	
	$html = str_replace('[company_name]', $company->name, $html);
	$html = str_replace('[company_code]', $company->code, $html);
	$html = str_replace('[created]', date("Y-m-d", strtotime($company->created)), $html);
	$html = str_replace('[customer_name]', implode('<br />', $users), $html);
	
	exit($html);
}

function wpe_payment_callback($web2pay = true, $invoice_id = null) {
	
	global $event_cfg, $wpdb;
	
	if($web2pay) {
		parse_str(base64_decode(strtr($_GET['data'], array('-' => '+', '_' => '/'))), $data);
		$invoice_id = (int) $data['orderid'];
	}
	else {
		$invoice_id = (int) $_GET['id'];
	}
	
	$event_cfg = $wpdb->get_row("SELECT event.*
				    FROM {$wpdb->prefix}event_events as event
				    INNER JOIN {$wpdb->prefix}event_groups as `group` ON (event.id = group.event_id)
				    INNER JOIN {$wpdb->prefix}event_invoices as invoice ON (invoice.group_id = group.id)
				    WHERE invoice.id = '{$invoice_id}'", ARRAY_A);

	$event_cfg['tickets'] = unserialize($event_cfg['tickets']);
	$event_cfg['smtp'] = unserialize($event_cfg['smtp_options']);
	
	if(!class_exists('WebToPay')) {
		require_once(WPE_PLUGIN_DIR . '/lib/WebToPay.php');
	}

	require_once(WPE_PLUGIN_DIR . '/lib/PHPMailer/class.phpmailer.php');
	
	// tikrininama ar mokejimai.lt padaro mokejimo callback'a ar saito administratorius
	if($web2pay) {
		try {
		    $response = WebToPay::checkResponse($_REQUEST, array(
			  
			    'projectid'     => $event_cfg['webtopay_project_id'],
			    'sign_password' => $event_cfg['webtopay_project_sign'],
		    	//'test' => $event_cfg['webtopay_project_test']	
			));
			//print_r($response);
			if($response['status'] != 1) {
				die('Wrong status');
			}
			
		}
		catch (Exception $e) {
			echo get_class($e).': '.$e->getMessage();
		}
		$invoice_id = (int) $response['orderid'];
	}
	
	$invoice =  $wpdb->get_row("SELECT id, group_id, amount 
				    FROM {$wpdb->prefix}event_invoices 
				    WHERE id = {$invoice_id}
				    ");
	
	if(empty($invoice)) {
		die("Order with ID: {$invoice_id} doesn't exists");
	}
	
	$amount = $invoice->amount;
	$group_id = (int) $invoice->group_id;	
	
	if($web2pay && $response['amount'] < $amount * 100) {
		die("Order amounts miss match, expected: $amount, got: {$response['amount']}");
	}

	
	$status = $wpdb->update($wpdb->prefix. 'event_invoices', 
		array(
			'paid_in' => 1,
			'paid_in_date' => current_time('mysql')
			), 
			array(
				'id' => $invoice_id
			)
	);
	
	if(!$status) {
		die('Error in saving data');
	}
	
	//----------------------------
	// Creating ticket entries

	$customers = $wpdb->get_results("SELECT id, name, email FROM {$wpdb->prefix}event_customers WHERE group_id = {$group_id}");

	$amount_per_user = $amount/count($customers);
	
	$users_updated = array();

	// Sukuriam asmeniskai kiekvienam useriui po tiketa ir nusiunciam jam i mail
	foreach($customers as $user) {
		while(1) {
			$unique_id = _ticket_nr();
			$count = $wpdb->get_var("SELECT count(*) FROM {$wpdb->prefix}event_tickets WHERE ticket_nr = '".uniqid()."'");
				
			if($count == 0) {
				$status = $wpdb->insert($wpdb->prefix . 'event_tickets', array(
					'amount'     => $amount_per_user,
					'currency'   => 'LTL',
					'created'    => current_time('mysql'),
					'ticket_nr'  => $unique_id,
					'valid_from' => $event_cfg['valid_from'],
					'valid_to'   => $event_cfg['valid_to'],
					'event_id' => $event_cfg['id']
				));
				
				if($status) {
					$ticket_id = $wpdb->insert_id;
					$status = $wpdb->update($wpdb->prefix. 'event_customers', 
						array('ticket_id' => $ticket_id), 
						array('id' => $user->id)
					);

					_wpe_send_ticket($user->email, $unique_id);
					$users_updated[] = array($user->name, $unique_id);
					
					break;
				}
			}
		}
	}

	$company = $wpdb->get_col(" SELECT company_id FROM {$wpdb->prefix}event_groups WHERE id = $group_id ");
	$company_id = $company[0];
	
	// jeigu dalyviu daugiau nei vienas arba registruojasi imone
	if(count($customers) > 1 || !is_null($company_id) ) {
		
		if(is_null($company_id)) {
			$mail_to = $customers[0]->email;
		}
		else {
			$a = $wpdb->get_col("SELECT person_email FROM {$wpdb->prefix}event_companies WHERE id = {$company_id}");
			$mail_to = $a[0];
		}
		
		_wpe_send_to_representative($users_updated, $mail_to); 
	}
	if($web2pay) {
		die('ok');
	}
}

function _wpe_send_to_representative($customers, $mail_to) {
	
	global $event_cfg;
	
	$mail = new PHPMailer();
	$body = file_get_contents(WPE_PLUGIN_DIR. "/tmpl/personalized_templates/{$event_cfg['id']}_mail_to_representative1.html");	
	
	if($event_cfg['smtp']['enabled']) {
		$mail->IsSMTP(); 
		$mail->SMTPAuth   = true;                  
		$mail->SMTPSecure = 'tls';                 
		$mail->Host       = $event_cfg['smtp']['host'];      
		$mail->Port       = $event_cfg['smtp']['port'];                  
		$mail->Username   = $event_cfg['smtp']['username']; 
		$mail->Password   = $event_cfg['smtp']['password'];
	}
	
	$mail->CharSet = 'UTF-8';
	$mail->SetFrom($event_cfg['email_from'], $event_cfg['email_from_name']);
	$mail->Subject = $event_cfg['email_to_rep1_sb'];
	$mail->AddAddress($mail_to);

	$site_url = site_url();
	
	$participants = "<table>";
	foreach($customers as $user) {
		$participants .= "<tr><td>{$user['0']}</td><td><a href='{$site_url}?event_ticket&id={$user['1']}'>{$user['1']}</a></td></tr>";	
	}	
	$participants .= "</table>";
	$body = str_replace('[participants]', $participants, $body);
	$mail->MsgHTML($body);	
	$mail->Send();
}

function _wpe_send_ticket($email, $ticket_nr) {
	
	global $event_cfg;
	
	$mail = new PHPMailer();
	$body = file_get_contents(WPE_PLUGIN_DIR. "/tmpl/personalized_templates/{$event_cfg['id']}_mail_to_customer.html");	

	$body = str_replace('[ticket_nr]', $ticket_nr, $body);
	$body = str_replace('[site_url]', site_url(), $body);
	
	if($event_cfg['smtp']['enabled']) {
		$mail->IsSMTP(); 
		$mail->SMTPAuth   = true;                  
		$mail->SMTPSecure = 'tls';                 
		$mail->Host       = $event_cfg['smtp']['host'];      
		$mail->Port       = $event_cfg['smtp']['port'];                  
		$mail->Username   = $event_cfg['smtp']['username']; 
		$mail->Password   = $event_cfg['smtp']['password'];
	}
	
	$mail->CharSet = 'UTF-8';
	$mail->SetFrom($event_cfg['email_from'], $event_cfg['email_from_name']);

	$mail->Subject = $event_cfg['email_to_cust_sb'];
	$mail->MsgHTML($body);
	$mail->AddAddress($email);
	$mail->Send();
}

function _wpe_send_prebill($order_id, $mail_to, $bill_url) {
	
	global $event_cfg;
	
	require_once(WPE_PLUGIN_DIR . '/lib/PHPMailer/class.phpmailer.php');
	
	$mail = new PHPMailer();
	$body = file_get_contents(WPE_PLUGIN_DIR. "/tmpl/personalized_templates/{$event_cfg['id']}_mail_to_representative3.html");	
	$body = str_replace("[bill_url]", $bill_url, $body);
	
	if($event_cfg['smtp']['enabled']) {
		$mail->IsSMTP();
		$mail->SMTPAuth   = true;
		$mail->SMTPSecure = 'tls';
		$mail->Host       = $event_cfg['smtp']['host'];
		$mail->Port       = $event_cfg['smtp']['port'];
		$mail->Username   = $event_cfg['smtp']['username'];
		$mail->Password   = $event_cfg['smtp']['password'];
	}
	$mail->CharSet = 'UTF-8';
	$mail->SetFrom($event_cfg['email_from'], $event_cfg['email_from_name']);
	
	$mail->Subject = $event_cfg['email_to_rep3_sb'];
	$mail->MsgHTML($body);
	$mail->AddAddress($mail_to);
	
	$mail->Send();
	
}

function wpe_ticket_generate() {
	
	global $wpdb;
	
	ini_set('memory_limit', '128M');
	
	$ticket_nr = esc_sql($_GET['id']);
		
	require (WPE_PLUGIN_DIR . '/lib/phpqrcode/phpqrcode.php');
	//require_once(WPE_PLUGIN_DIR  . '/lib/pdf/tcpdf.php');

	$ticket = $wpdb->get_row("SELECT id, amount, event_id, ticket_nr FROM {$wpdb->prefix}event_tickets WHERE ticket_nr = '$ticket_nr'");
		
	
	if(empty($ticket)) {
		die(__("Ticket with such code doesn't exists", 'evp-event'));
	}

	$tmp_file_name = tempnam(WPE_PLUGIN_DIR . DS . 'tmp', "qr_code");
	
	if(!$tmp_file_name) {
		die(__("System error, please try again", "evp-event"));
	}
		
	QRcode::png(get_site_url().'?t&id='.$ticket_nr, $tmp_file_name, 'L', 6, 0); 

	
	$raw_png = base64_encode(file_get_contents($tmp_file_name));
	
	$body = file_get_contents(WPE_PLUGIN_DIR. "/tmpl/personalized_templates/{$ticket->event_id}_ticket.html");
	
	$body = str_replace('[ticket_amount]', $ticket->amount, $body);
	$body = str_replace('[ticket_id]', $ticket->ticket_nr, $body);
	$body = str_replace('[raw_png]', $raw_png, $body);
	
	
	exit($body);
	
	/*
	// create new PDF document
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('EVP');

	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	//set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// ---------------------------------------------------------

	$pdf->AddPage();

	$img_file = WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'ticket_templates' .DS . $ticket->event_id.'.png';
	
	// Bilieto templeitas
	$pdf->Image($img_file, 0, 0, 200, 0, '', '', '', true, 300, '', false, false, 0);
	
	
	// QR CODE
	$pdf->Image($tmp_file_name, 142, 29, 0, 0, '', '', '', true, 300, '', false , false, 0);	

	$pdf->setFontSize(10);
	$pdf->SetTextColor(63,63,63);
	$pdf->SetFont('helvetica');
	$pdf->setFontSpacing(0.2);


	$pdf->text(100, 57.5, "{$ticket->amount} Lt");
	$pdf->text(95, 65, $ticket_nr);	

	unlink($tmp_file_name);
	$pdf->Output();
	die;
	*/
}

function wpe_ticket_check() {
	
	global $wpdb;

	$error_message = "";
	if(!isset($_COOKIE['ticket_checker'])) {
		$error_message = __('Do not have permission to check tickets', 'evp-event');
	}
		
	if(!isset($_GET['id']) || empty($_GET['id'])) {
		$error_message = __('Invalid ticket id', 'evp-event');
	}

	if(empty($error_message)) {
		$ticket_nr = esc_attr($_GET['id']);
			
		$event_id = (int) $_COOKIE['ticket_checker'];
			
		$ticket = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}event_tickets WHERE ticket_nr = '{$ticket_nr}' AND event_id = '{$event_id}'");

		$time_now = current_time('mysql');
		
		if ($ticket == false ) {
			$error_message = __("Ticket doesn't exit", "evp-event");
		} else if ($ticket->valid_from > $time_now) {
			$error_message = __("Ticket no valid yet", "evp-event");
		} else if ($ticket->valid_to < $time_now) {
			$error_message = __("Ticket validation time has been expired ", "evp-event");
		} else if ($ticket->used !=  NULL) {
			$error_message = __("Ticket used:",  'evp-event')."<BR>".$ticket->used;
		}
	
		if (strlen($error_message) > 0) {
			$bg_color = "red";
			$message = $error_message;
		} else {
			
			
			$total_tickets = $wpdb->get_var("SELECT count(*) FROM {$wpdb->prefix}event_tickets WHERE event_id = '{$event_id}' ");
			$used_tickets = $wpdb->get_var("SELECT count(*)+1 FROM {$wpdb->prefix}event_tickets WHERE event_id = '{$event_id}' AND used IS NOT null");
			
			$bg_color = "green";
			$message = __('Valid', 'evp-event') . ' ' . __('id:', 'evp-event'). $ticket->ticket_nr."<br />".
						"{$used_tickets}/{$total_tickets}";
				
			$status = $wpdb->update($wpdb->prefix. 'event_tickets', 
					array(
					'used' => $time_now,
					'validated_by' => $_SERVER['HTTP_USER_AGENT'].' '.$_SERVER['REMOTE_ADDR']
					),
					array(
						 'ticket_nr'  => $ticket_nr
					)
				);
		}
	}

	die("<html>
	<HEAD>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
	<STYLE type=\"text/css\">
	BODY {text-align: center; width: 100%; font-size: 700%;
	background-color: $bg_color }
	</STYLE>
	</HEAD>
	<body>$message</body></html>");
}

function wpe_payment_guaranty() {

	global $event_cfg, $wpdb;

	require_once(WPE_PLUGIN_DIR . '/lib/PHPMailer/class.phpmailer.php');
		
	if(isset($_SESSION['FORM']['USERS']) && !empty($_SESSION['FORM']['USERS']) && empty($_SESSION['FORM']['ERROR'])) {
		$data = $_SESSION['FORM'];
		
		// 1. Surasom duomenis i DB
		//-------------------------------
				
		// Jeigu imone registruoja savo darbuotojus, sukuriam nauja irasa `event_companies` table'e
		if(isset($data['COMPANY']) && !empty($data['COMPANY'])) {
			
			$wpdb->insert($wpdb->prefix . 'event_companies', array(
				'name' => $data['COMPANY']['name'],
				'code' => $data['COMPANY']['code'],
				'pvm_code' => $data['COMPANY']['pvm_code'],
				'address' => $data['COMPANY']['address'],
				'fax'	  => $data['COMPANY']['fax'],
				'person_name'  => $data['COMPANY']['person_name'],
				'person_phone' => $data['COMPANY']['person_phone'],
				'person_email' => $data['COMPANY']['person_email'],
				'created'	   => current_time('mysql')
			));
			$company_id = $wpdb->insert_id;
		}
		else {
			$company_id = null;
		}
	
			// Sukuriam nauja bendro pirkimo grupe, net jeigu i rengini registruojasi vienas asmuo
			if(is_null($company_id)) {
				$wpdb->insert($wpdb->prefix . 'event_groups', array('id' => '', 'event_id' => $event_cfg['id']));
			}
			else {
				$wpdb->insert($wpdb->prefix . 'event_groups', array('id' => '', 'company_id' => $company_id, 'event_id' => $event_cfg['id']));
			}
		
		$group_id = $wpdb->insert_id;
		
		// Surasom dalyvius i event_customers lentele
		foreach($data['USERS'] as $user) {
			$wpdb->insert($wpdb->prefix . 'event_customers', 
				array(
					'name'  => $user['name'],
					'email' => $user['email'],
					'phone' => $user['phone'],
					'group_id' => $group_id,
					'created' => current_time('mysql')
				
				)); 
			}
		
		$amount = get_total_price();	
		
		$wpdb->insert($wpdb->prefix . 'event_invoices', array(
			'event_id' => $event_cfg['id'],
			'amount'   => $amount,
			'created'  => current_time('mysql'),
			'group_id' => $group_id,
			'payment_type' => 1,
			//'ticket_type' => $_SESSION['FORM']['TICKET_PRICE']
		));
		
		$invoice_id = $wpdb->insert_id;
		

		$mail = new PHPMailer();
		$body = file_get_contents(WPE_PLUGIN_DIR. "/tmpl/personalized_templates/{$event_cfg['id']}_mail_to_representative2.html");	
		
		if($event_cfg['smtp']['enabled']) {
			$mail->IsSMTP(); 
			$mail->SMTPAuth   = true;                  
			$mail->SMTPSecure = 'tls';                 
			$mail->Host       = $event_cfg['smtp']['host'];      
			$mail->Port       = $event_cfg['smtp']['port'];                  
			$mail->Username   = $event_cfg['smtp']['username']; 
			$mail->Password   = $event_cfg['smtp']['password'];
		}
	
		$mail->CharSet = 'UTF-8';
		$mail->SetFrom($event_cfg['email_from'], $event_cfg['email_from_name']);

		
		$mail->Subject = $mail->Subject = $event_cfg['email_to_rep2_sb'];
		
		$data = $_SESSION['FORM']['USERS'];
	
		
		$a = $wpdb->get_col("SELECT person_email FROM {$wpdb->prefix}event_companies WHERE id = {$company_id}");
		$mail_to = $a[0];
			
		
		$mail->AddAddress($mail_to);
		
		$body = str_replace("[guaranty_url]", site_url().'?guaranty_template&id=' . $company_id, $body);
		$mail->MsgHTML($body);	
		
		$mail->Send();

		require WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'payment_guaranty.php';
	}
	else {
		wpe_form();
	}	

	unset($_SESSION['FORM']);
}

function wpe_payment_cancel() {
	require WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'payment_cancel.php';
}

function wpe_payment_success() {
	unset($_SESSION['FORM']);
	require WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'payment_success.php';
}

function wpe_checker() {
	global $wpdb;

	$code = esc_sql($_GET['c']);
	
	$event_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}event_events WHERE unique_code = '{$code}' LIMIT 1 ");
	
	if($event_id>0) {
		setcookie("ticket_checker", $event_id, time() + (20 * 365 * 24 * 60 * 60));
		$message = __(sprintf('Ticket checker rights have been granted for event #%s',$event_id), 'evp-event');
		$bg_color = 'green';
	}
	else {
		$message = __('Access denied', 'evp-event');
		$bg_color = 'red';
	}
	
	die("<html>
	<HEAD>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
	<STYLE type=\"text/css\">
	BODY {text-align: center; width: 100%; font-size: 700%;
	background-color: $bg_color }
	</STYLE>
	</HEAD>
	<body>$message</body></html>");
	
}
