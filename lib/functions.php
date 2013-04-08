<?php

/*
 * Sugeneruoja request'a i webtopay gateway'u. Grazina Request ID
 */
function getRequestID($request) {

    if(!class_exists('WebToPay')) {
        require_once('lib/WebToPay.php');
    }
    
    $url = WebToPay::PAY_URL . '?' . http_build_query($request);

    $cookie_file = tempnam(WPE_PLUGIN_DIR . DS . 'tmp', 'aaa');
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_COOKIESESSION, true);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    
    // Kadangi hostinge ijungtas safe_mode, FOLLOWLOCATION 1 nustatyti negalima
    // todel vykdom querius manualu
    
    // 1 requestas
    $header = curl_exec($ch);
    preg_match('/Location:(.*?)\n/', $header, $matches);
    $newurl = trim(array_pop($matches));
    
    //2 requestas
    curl_setopt($ch, CURLOPT_URL, "http://mokejimai.lt/".$newurl);
    $header = curl_exec($ch);
    preg_match('/Location:(.*?)\n/', $header, $matches);
    $newurl = trim(array_pop($matches));


    //3 requestas
    curl_setopt($ch, CURLOPT_URL, $newurl);
    $header = curl_exec($ch);
    preg_match('/location:(.*?)\n/', $header, $matches);
    $newurl = trim(array_pop($matches));

    unlink($cookie_file);

    if(preg_match('#\d+#', $newurl, $matches)) {
       return $matches[0];
    }
    else {
        return 0;
    }
}


function array_map_deep($array, $callback) {
    $new = array();
    if( is_array($array) ) foreach ($array as $key => $val) {
        if (is_array($val)) {
            $new[$key] = array_map_deep($val, $callback);
        } else {
            $new[$key] = call_user_func($callback, $val);
        }
    }
    else $new = call_user_func($callback, $array);
    return $new;
}

function _ticket_nr() {
    $alphabet = "abcdefghijklmnopqrstuwxyz0123456789";
    $pass = array(); //remember to declare $pass as an array
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, strlen($alphabet)-1); //use strlen instead of count
        $pass[$i] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}


function generateBillPdf($request_id, $order_id, $data) {
    global $event_cfg;
    $pdf_path = WPE_PLUGIN_DIR . DS . 'pdf' . DS . $order_id . '.pdf';

    ini_set('memory_limit', '128M');
    require_once(WPE_PLUGIN_DIR . DS . 'lib' . DS .'pdf' . DS . 'tcpdf.php');


    // create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('EVP');
    $pdf->SetTitle('Sąskaita');

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    //set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    //set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    $pdf->SetFont('freeserif', '', 10);

    //set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


	
	$html = file_get_contents(WPE_PLUGIN_DIR. "/tmpl/personalized_templates/{$event_cfg['id']}_invoice_advanced.html");	
	
    $amount = get_total_price();
      	
    $html = str_replace("[request_id]", $request_id, $html);
    $html = str_replace("[date]", date("Y-m-d"), $html);
    $html = str_replace("[company_name]", $data['COMPANY']['name'], $html);
    $html = str_replace("[company_address]", $data['COMPANY']['address'], $html);
    $html = str_replace("[company_code]", $data['COMPANY']['code'], $html);
    $html = str_replace("[company_pvm_code]", $data['COMPANY']['pvm_code'], $html);
    $html = str_replace("[ticket_amount]", count($data['USERS']), $html);
    $html = str_replace("[ticket_sum]", $amount, $html);
    $html = str_replace("[ticket_price]", $event_cfg['tickets'][$data['TICKET_PRICE']]['price'] - discount_for_ticket(), $html);


    $sum = sprintf('%01.2f', $amount);
    list($p1, $p2) = explode('.', $sum);
    $html = str_replace("[sum_words]", getSumZodziais($p1) . ' '.getLitai($p1).' '.$p2.' cnt.', $html);
    $pdf->AddPage();

    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->Output($pdf_path, 'F');

}

function use_discount_code($code, $event_id) {
    global $wpdb;

    $row =  $wpdb->get_row( $wpdb->prepare( "SELECT discount 
    										FROM {$wpdb->prefix}event_discount_codes 
    										WHERE code = %s 
    										AND amount_left > 0 
    										AND event_id = %d", array($code, $event_id)));

    if(!$row) {
        return false;
    }


    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}event_discount_codes
                  SET amount_left = amount_left - 1
                  WHERE code = %s", $code));

    return $row->discount;
}

function discount_for_ticket() {
	
    global $event_cfg;
    $data= $_SESSION['FORM'];
    $discount = 0;
   
	
	$ticket_price = $_SESSION['FORM']['TICKET_PRICE'];
	$discount_persons = $event_cfg['tickets'][$ticket_price]['discount_persons'];
	$discount_percent = $event_cfg['tickets'][$ticket_price]['discount_percent'];
	
    // Jeigu registruojasi daugiau nei 3 useriai, 20% nuolaida
    if(count($data['USERS']) >= $discount_persons) {
       $discount =  $event_cfg['tickets'][$ticket_price]['price'] * ($discount_percent)/100;
    }
    
    // Checkinam ar panaudotas promo kodas
    if( (float) $data['DISCOUNT'] > 0 && (float) $data['DISCOUNT'] <= 100) {
        $discount += $event_cfg['tickets'][$data['TICKET_PRICE']]['price'] * ($data['DISCOUNT']/100);
    }
    
    // Jeigu nuolaida didesne uz bilieto kaina, grazinam nuolaida lygia bilieto kainai
    if($discount > $event_cfg['tickets'][$ticket_price]['price']) {
    	return $event_cfg['tickets'][$ticket_price]['price'];
    }
    else {
    	return money_format('%i', $discount);
    }
}

function get_total_price() {
    global $event_cfg;
    return money_format('%i', count($_SESSION['FORM']['USERS']) * ($event_cfg['tickets'][$_SESSION['FORM']['TICKET_PRICE']]['price']-discount_for_ticket()));
}

function getTrys($skaicius)
{
    $vienetai = array ('', 'vienas', 'du', 'trys', 'keturi', 'penki', 'šeši', 'septyni', 'aštuoni', 'devyni');
    $niolikai = array ('', 'vienuolika', 'dvylika', 'trylika', 'keturiolika', 'penkiolika', 'šešiolika', 'septyniolika', 'aštuoniolika', 'devyniolika');
    $desimtys = array ('', 'dešimt', 'dvidešimt', 'trisdešimt', 'keturiasdešimt', 'penkiasdešimt', 'šešiasdešimt', 'septyniasdešimt', 'aštuoniasdešimt', 'devyniasdešimt');

    $skaicius = sprintf("%03d", $skaicius);
    $simtai = ($skaicius{0} == 1)?"šimtas":"šimtai";
    if ($skaicius{0} == 0) $simtai = "";

    $du = substr($skaicius, 1);
    if  (($du > 10) && ($du < 20))
        return getSumZodziais($skaicius{0}."00")." ".$niolikai[$du{1}];
    else
        return $vienetai[$skaicius{0}]." ".$simtai." ".$desimtys[$skaicius{1}]." ".$vienetai[$skaicius{2}];
}

function getSumZodziais($skaicius)
{
    $zodis = array(
        array("", "", ""),
        array("tūkstančių", "tūkstantis", "tūkstančiai"),
        array("milijonų", "milijonas", "milijonai"),
        array("milijardų", "milijardas", "milijardai"),
        array("bilijonų", "bilijonas", "bilijonai"));

    $return = "";
    if ($skaicius == 0) return "nulis";

    settype($skaicius, "string");
    $size = strlen($skaicius);
    $skaicius = str_pad($skaicius, ceil($size/3)*3, "0", STR_PAD_LEFT);

    for ($ii=0; $ii<$size; $ii+=3)
    {
        $tmp = substr($skaicius, 0-$ii-3, 3);
        $return = getTrys($tmp)." ".$zodis[$ii/3][($tmp{2}>1)?2:$tmp{2}]." ".$return;
    }
    return $return;
}

function getLitai($number)
{
    if ($number == 0)
        return 'litų';

    $last = substr($number, -1);
    $du = substr($number, -2, 2);

    if (($du > 10) && ($du < 20))
        return 'litų';
    else
    {
        if ($last == 0)
            return 'litų';
        elseif ($last == 1)
            return 'litas';
        else
            return 'litai';
    }
}

function admin_get_event_id() {
	if(isset($_GET['event_id'])) {
		return (int) $_GET['event_id'];
	}
	elseif(preg_match('/([\d]+)/', $_GET['page'], $match)) {
		return (int) @$match[0];
	}
	else {
		return null;
	}
}

function isPngFile($filename)
{
	// check if the file exists
	if (!file_exists($filename)) {
		return null;
	}

	// define the array of first 8 png bytes
	$png_header = array(137, 80, 78, 71, 13, 10, 26, 10);
	// or: array(0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A);

	// open file for reading
	$f = fopen($filename, 'r');

	// read first 8 bytes from the file and close the resource
	$header = fread($f, 8);
	fclose($f);

	// convert the string to an array
	$chars = preg_split('//', $header, -1, PREG_SPLIT_NO_EMPTY);

	// convert each charater to its ascii value
	$chars = array_map('ord', $chars);

	// return true if there are no differences or false otherwise
	return (count(array_diff($png_header, $chars)) === 0);
}