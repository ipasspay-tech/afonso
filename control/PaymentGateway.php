<?php
/**
 * @describe Ipasspay payment Gateway
 * @version 1.0
 * @author Afonso
 * @email tech@ipasspay.com
 */
$config = require('../config/config.php'); // import config file
$params = array(
    'merchantid'  => $config['mid'], // Merchant ID
    'siteid'      => $config['site_id'],
    'invoiceid'   => isset($_POST["invoiceid"]) ? $_POST["invoiceid"] : "", // Merchant Order ID
    'amount'      => isset($_POST["amount"]) ? $_POST["amount"] : "", // Order Amount,currency format
    'currency'    => isset($_POST["currency"]) ? $_POST["currency"] : "", // 3 upper-case letters
    'card_number' => isset($_POST["card_number"]) ? $_POST["card_number"] : "",
    'expiry_month'=> isset($_POST["expiry_month"]) ? $_POST["expiry_month"] : "",
    'expiry_year' => isset($_POST["expiry_year"]) ? $_POST["expiry_year"] : "",
    'cvv'         => isset($_POST["cvv"]) ? $_POST["cvv"] : "",
    'email'       => isset($_POST["email"]) ? $_POST["email"] : "",
    'phonenumber' => isset($_POST["phonenumber"]) ? $_POST["phonenumber"] : "",
    'country'     => isset($_POST["country"]) ? $_POST["country"] : "",
    'state'       => isset($_POST["state"]) ? $_POST["state"] : "",
    'city'        => isset($_POST["city"]) ? $_POST["city"] : "",
    'address1'    => isset($_POST["address1"]) ? $_POST["address1"] : "",
    'postcode'    => isset($_POST["postcode"]) ? $_POST["postcode"] : "",
    'firstname'   => isset($_POST["firstname"]) ? $_POST["firstname"] : "",
    'lastname'    => isset($_POST["lastname"]) ? $_POST["lastname"] : "",
    'syn_url'     => $config['sysUrl'],
    'asyn_url'    => $config['asynUrl'],
    'client_ip'   => $_SERVER["REMOTE_ADDR"],
    'rebill_cycle'=> '1',
    'rebill_amount'=>'1.00',
    'rebill_firstdate'=>'2029-02-01',
    'rebill_count'=>'1',
);

//encrypted the sensitive data
$hash_info=hash("sha256",$params['merchantid'].$params['siteid'].
$params['invoiceid'].$params['amount'].$params['currency'].$config['api_key']);


$curlPost="order_amount=".$params['amount'];
$curlPost.="&order_currency=".$params['currency'];
$curlPost.="&mid=".$params['merchantid'];
$curlPost.="&site_id=".$params['siteid'];
$curlPost.="&oid=".$params['invoiceid'];
$curlPost.="&hash_info=".$hash_info;

$curlPost.="&card_no=".$params["card_number"];
$curlPost.="&card_ex_year=".$params["expiry_year"];
$curlPost.="&card_ex_month=".$params["expiry_month"];
$curlPost.="&card_cvv=".$params["cvv"];

$curlPost.='&bill_firstname='.$params['firstname'];
$curlPost.='&bill_lastname='.$params['lastname'];
$curlPost.='&bill_street='.$params['address1'];
$curlPost.='&bill_city='.$params['city'];
$curlPost.='&bill_state='.$params['state'];
$curlPost.='&bill_country='.$params['country'];
$curlPost.='&bill_zip='.$params['postcode'];
$curlPost.='&bill_phone='.$params['phonenumber'];
$curlPost.='&bill_email='.$params['email'];

$curlPost.='&syn_url='.$params['syn_url'];
$curlPost.='&asyn_url='.$params['asyn_url'];

$curlPost.='&source_ip='.$params['client_ip'];
$curlPost.='&source_url='.$_SERVER["HTTP_REFERER"];
$curlPost.='&gateway_version=1.0';
$curlPost.='&uuid='.create_guid();

/*If it's a rebilling transaction*/
$curlPost.='&rebill_flag=1';
$curlPost.='&rebill_cycle='.$params['rebill_cycle'];
$curlPost.='&rebill_amount='.$params['rebill_amount'];
$curlPost.='&rebill_count='.$params['rebill_count'];
$curlPost.='&rebill_firstdate='.$params['rebill_firstdate'];

/*If the gateway version is 2.0*/
$curlPost.='&ship_email='.$params['ship_email'];
$curlPost.='&ship_phone='.$params['ship_phone'];
$curlPost.='&ship_country='.$params['ship_country'];
$curlPost.='&ship_state='.$params['ship_state'];
$curlPost.='&ship_city='.$params['ship_city'];
$curlPost.='&ship_street='.$params['ship_street'];
$curlPost.='&ship_zip='.$params['ship_zip'];
$curlPost.='&order_items='.$params['order_items'];

/*Payment Gateway(Direct)*/
function paymentDirect($curlPost){
    $ch = curl_init();
    $gateway_url = "https://www.ipasspay.biz/index.php/Gateway/securepay";
    curl_setopt($ch, CURLOPT_URL, $gateway_url);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$curlPost);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    curl_close($ch);

    //$result = json_decode($response,true);
    //var_dump($result);die(); // Get the return parameter
    echo $response;
}

/*Payment Gateway(Host)*/
function paymentHost($curlPost){
    $gateway_url = "https://www.ipasspay.biz/index.php/Gateway/paygates";
    $gatewau_host_url = $gateway_url."?".$curlPost;
    Header("HTTP/1.1 303 See Other");
    Header("Location: $gatewau_host_url");
    exit;
}


/*Creating uuid in PHP*/
function create_guid() {
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $hyphen = chr(45); // "-"
    $uuid = substr($charid, 0, 8) . $hyphen
        . substr($charid, 8, 4) . $hyphen
        . substr($charid, 12, 4) . $hyphen
        . substr($charid, 16, 4) . $hyphen
        . substr($charid, 20, 12);
    return $uuid;
}

// Execute different programs according to the specified parameters.
switch($_POST['paymentStyle'])
{
    case "direct":
        paymentDirect($curlPost);
        break;
    case "host":
        paymentHost($curlPost);
        break;
    default:
        break;
}
?>