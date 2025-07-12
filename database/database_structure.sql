-- =======================================================
-- ESTRUCTURA DE BASE DE DATOS PARA TELEGRAM BOT CC CHECKER
-- =======================================================
-- Base de datos necesaria para el funcionamiento del bot
-- Ejecutar estos comandos en MySQL/MariaDB

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS `telegram_bot_cc` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `telegram_bot_cc`;

-- =======================================================
-- TABLA: users
-- Almacena información de usuarios del bot
-- =======================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `iduser` bigint(20) NOT NULL,
    `username` varchar(100) DEFAULT NULL,
    `firstname` varchar(100) DEFAULT NULL,
    `sk_live` text DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_iduser` (`iduser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLA: prmiumtime  
-- Gestiona usuarios premium y sus tiempos
-- =======================================================
CREATE TABLE IF NOT EXISTS `prmiumtime` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `userid` bigint(20) NOT NULL,
    `timedate` varchar(50) NOT NULL,
    `apodo` varchar(100) DEFAULT 'Premium',
    `Antispma` int(11) DEFAULT 20,
    `Idioma` varchar(10) DEFAULT 'es',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLA: createkey
-- Gestiona las keys de premium generadas
-- =======================================================
CREATE TABLE IF NOT EXISTS `createkey` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `key` varchar(255) NOT NULL,
    `timedate` varchar(50) NOT NULL,
    `used` tinyint(1) DEFAULT 0,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `used_at` timestamp NULL DEFAULT NULL,
    `used_by` bigint(20) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLA: creditos
-- Sistema de créditos para usuarios
-- =======================================================
CREATE TABLE IF NOT EXISTS `creditos` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `userdid` bigint(20) NOT NULL,
    `creditos` int(11) DEFAULT 0,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_userdid` (`userdid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLA: gateroff
-- Control de gateways offline/online
-- =======================================================
CREATE TABLE IF NOT EXISTS `gateroff` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `Gater` varchar(100) NOT NULL,
    `razon` text DEFAULT 'Maintenance',
    `status` enum('offline','online') DEFAULT 'offline',
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_gater` (`Gater`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLA: admin
-- Usuarios administradores
-- =======================================================
CREATE TABLE IF NOT EXISTS `admin` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `userid` bigint(20) NOT NULL,
    `username` varchar(100) DEFAULT NULL,
    `permissions` text DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_admin_userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLA: grupos
-- Gestión de grupos autorizados
-- =======================================================
CREATE TABLE IF NOT EXISTS `grupos` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `groupid` bigint(20) NOT NULL,
    `groupname` varchar(255) DEFAULT NULL,
    `timedate` varchar(50) DEFAULT NULL,
    `status` enum('active','inactive') DEFAULT 'active',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_groupid` (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLA: banned_users
-- Usuarios baneados
-- =======================================================
CREATE TABLE IF NOT EXISTS `banned_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `userid` bigint(20) NOT NULL,
    `reason` text DEFAULT NULL,
    `banned_by` bigint(20) DEFAULT NULL,
    `banned_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_banned_userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLA: logs
-- Sistema de logs del bot
-- =======================================================
CREATE TABLE IF NOT EXISTS `logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `userid` bigint(20) DEFAULT NULL,
    `action` varchar(255) NOT NULL,
    `details` text DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_userid` (`userid`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLA: Amx_cookie
-- Cookies para American Express
-- =======================================================
CREATE TABLE IF NOT EXISTS `Amx_cookie` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `userid` bigint(20) NOT NULL,
    `cookie` text NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_amx_userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLA: mx_cookies  
-- Cookies para MX gateway
-- =======================================================
CREATE TABLE IF NOT EXISTS `mx_cookies` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `userid` bigint(20) NOT NULL,
    `cookies` text NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_mx_userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLA: settings
-- Configuraciones generales del bot
-- =======================================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) NOT NULL,
    `setting_value` text DEFAULT NULL,
    `description` text DEFAULT NULL,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- INSERTAR CONFIGURACIONES INICIALES
-- =======================================================
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES 
('bot_token', 'YOUR_BOT_TOKEN_HERE', 'Token del bot de Telegram'),
('owner_id', 'YOUR_TELEGRAM_USER_ID', 'ID del propietario del bot'),
('db_host', 'localhost', 'Host de la base de datos'),
('db_username', 'YOUR_DB_USERNAME', 'Usuario de la base de datos'),
('db_password', 'YOUR_DB_PASSWORD', 'Contraseña de la base de datos'),
('db_name', 'telegram_bot_cc', 'Nombre de la base de datos'),
('google_translate_api_key', 'YOUR_GOOGLE_API_KEY', 'API Key de Google Translate'),
('proxy_server', 'YOUR_PROXY_SERVER', 'Servidor proxy para requests'),
('proxy_auth', 'YOUR_PROXY_AUTH', 'Autenticación del proxy');

-- =======================================================
-- CREAR ARCHIVO banned.txt PARA BINS BANEADOS
-- Este archivo debe crearse manualmente en la raíz del proyecto
-- =======================================================
-- Contenido sugerido para banned.txt:
-- 123456
-- 654321
-- (Agregar bins baneados uno por línea)

-- =======================================================
-- NOTAS IMPORTANTES:
-- =======================================================
-- 1. Cambiar todos los valores YOUR_* por los valores reales
-- 2. Crear el archivo banned.txt en la raíz del proyecto
-- 3. Configurar permisos de base de datos apropiados
-- 4. Usar conexiones SSL en producción
-- 5. Hacer backup regular de la base de datos
-- =======================================================
