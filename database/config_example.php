<?php
// =======================================================
// ARCHIVO DE CONFIGURACIÓN PARA TELEGRAM BOT CC CHECKER
// =======================================================
// IMPORTANTE: Renombrar este archivo a 'config.php' y completar con tus datos

// =======================================================
// CONFIGURACIÓN DE BASE DE DATOS
// =======================================================
define('DB_HOST', 'YOUR_DATABASE_HOST');           // Ejemplo: 'localhost' o 'mysql.example.com'
define('DB_USERNAME', 'YOUR_DATABASE_USERNAME');   // Tu usuario de base de datos
define('DB_PASSWORD', 'YOUR_DATABASE_PASSWORD');   // Tu contraseña de base de datos
define('DB_NAME', 'YOUR_DATABASE_NAME');           // Nombre de tu base de datos

// =======================================================
// CONFIGURACIÓN DEL BOT DE TELEGRAM
// =======================================================
$botToken = "YOUR_BOT_TOKEN_HERE";                 // Token de tu bot de Telegram (@BotFather)
$Mi_Id = "YOUR_TELEGRAM_USER_ID";                  // Tu ID de usuario de Telegram
$website = "https://api.telegram.org/bot" . $botToken;

// =======================================================
// CONFIGURACIÓN DE APIs EXTERNAS
// =======================================================
// Google Translate API
$google_translate_api = "YOUR_GOOGLE_TRANSLATE_API_KEY";  // API Key de Google Cloud Translate

// =======================================================
// CONFIGURACIÓN DE PROXIES/VPNS
// =======================================================
// Configuración para servicios de proxy (opcional)
$proxy_config = [
    'server' => 'YOUR_PROXY_SERVER',               // Ejemplo: 'proxy.example.com:8080'
    'auth' => 'YOUR_PROXY_USERNAME:YOUR_PROXY_PASSWORD',  // Autenticación del proxy
    'type' => 'SOCKS5'                             // Tipo de proxy: SOCKS5, HTTP, etc.
];

// =======================================================
// CONFIGURACIÓN DE SEGURIDAD
// =======================================================
// Canales/grupos autorizados (agregar IDs negativos para grupos)
$authorized_chats = [
    // 'YOUR_TELEGRAM_USER_ID',
    // '-1001234567890',  // Ejemplo de ID de grupo
];

// URLs y dominios permitidos para validaciones
$allowed_domains = [
    'api.telegram.org',
    'translation.googleapis.com',
    // Agregar otros dominios necesarios
];

// =======================================================
// CONFIGURACIÓN GENERAL
// =======================================================
define('typing', 'typing');                        // Estado de escritura

// Configuración de logs
$log_config = [
    'enable_logs' => true,
    'log_file' => __DIR__ . '/logs/bot.log',
    'error_log' => __DIR__ . '/logs/error.log'
];

// =======================================================
// FUNCIONES DE CONFIGURACIÓN
// =======================================================

/**
 * Obtiene la configuración de la base de datos
 */
function getDbConfig() {
    return [
        'host' => DB_HOST,
        'username' => DB_USERNAME,
        'password' => DB_PASSWORD,
        'database' => DB_NAME
    ];
}

/**
 * Obtiene la configuración del proxy
 */
function getProxyConfig() {
    global $proxy_config;
    return $proxy_config;
}

/**
 * Obtiene el Google Translate API Key
 */
function getGoogleTranslateApiKey() {
    global $google_translate_api;
    return $google_translate_api;
}

/**
 * Verifica si un chat está autorizado
 */
function isChatAuthorized($chat_id) {
    global $authorized_chats, $Mi_Id;
    return in_array($chat_id, $authorized_chats) || $chat_id == $Mi_Id;
}

/**
 * Obtiene el token del bot
 */
function getBotToken() {
    global $botToken;
    return $botToken;
}

/**
 * Obtiene el ID del propietario
 */
function getOwnerId() {
    global $Mi_Id;
    return $Mi_Id;
}

// =======================================================
// INSTRUCCIONES DE CONFIGURACIÓN
// =======================================================
/*
1. Renombrar este archivo a 'config.php'
2. Completar todos los campos que dicen 'YOUR_*'
3. Configurar la base de datos según database_structure.sql
4. Asegurarse de que las carpetas de logs existan
5. Verificar permisos de archivos y carpetas
6. Mantener este archivo seguro y no exponerlo públicamente

CAMPOS OBLIGATORIOS:
- DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME
- botToken (obtenido de @BotFather en Telegram)
- Mi_Id (tu ID de usuario de Telegram)

CAMPOS OPCIONALES:
- google_translate_api (para traducción automática)
- proxy_config (para usar proxies/VPNs)
- authorized_chats (para restringir acceso)
*/
?>
