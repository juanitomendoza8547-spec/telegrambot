#!/bin/bash

# Script de instalación automática para dependencias del bot
# Este script soluciona el issue #2: "Vendor/Vendor not present + V2SOLVER Composer Failure"

echo "🔧 Instalando dependencias del Telegram Bot CC Checker..."

# Función para mostrar mensajes con colores
print_status() {
    echo -e "\e[32m[✓]\e[0m $1"
}

print_error() {
    echo -e "\e[31m[✗]\e[0m $1"
}

print_info() {
    echo -e "\e[34m[ℹ]\e[0m $1"
}

# Verificar si composer está instalado
if ! command -v composer &> /dev/null; then
    print_error "Composer no está instalado. Por favor instala Composer primero."
    exit 1
fi

print_info "Instalando dependencias de Capsolver..."
cd Capsolver
if composer install --no-dev --optimize-autoloader; then
    print_status "Dependencias de Capsolver instaladas correctamente"
else
    print_error "Error instalando dependencias de Capsolver"
    exit 1
fi
cd ..

print_info "Instalando dependencias de V2SOLVER..."
cd V2SOLVER
if composer install --no-dev --optimize-autoloader; then
    print_status "Dependencias de V2SOLVER instaladas correctamente"
else
    print_error "Error instalando dependencias de V2SOLVER"
    exit 1
fi
cd ..

print_info "Instalando dependencias del traductor..."
cd traductor
if composer install --no-dev --optimize-autoloader; then
    print_status "Dependencias del traductor instaladas correctamente"
else
    print_error "Error instalando dependencias del traductor"
    exit 1
fi
cd ..

print_info "Instalando dependencias del detector de lenguaje..."
cd traductor/Dector
if composer install --no-dev --optimize-autoloader; then
    print_status "Dependencias del detector de lenguaje instaladas correctamente"
else
    print_error "Error instalando dependencias del detector de lenguaje"
    exit 1
fi
cd ../..

print_info "Creando autoload.php principal..."
mkdir -p vendor/vendor
cat > vendor/vendor/autoload.php << 'EOF'
<?php
// Autoload file for vendor/vendor directory
// This is a compatibility autoload that includes all necessary dependencies

// Include Capsolver dependencies
if (file_exists(__DIR__ . '/../../Capsolver/vendor/autoload.php')) {
    require_once __DIR__ . '/../../Capsolver/vendor/autoload.php';
}

// Include V2SOLVER dependencies
if (file_exists(__DIR__ . '/../../V2SOLVER/vendor/autoload.php')) {
    require_once __DIR__ . '/../../V2SOLVER/vendor/autoload.php';
}

// Include traductor dependencies
if (file_exists(__DIR__ . '/../../traductor/vendor/autoload.php')) {
    require_once __DIR__ . '/../../traductor/vendor/autoload.php';
}

// Include traductor/Dector dependencies
if (file_exists(__DIR__ . '/../../traductor/Dector/vendor/autoload.php')) {
    require_once __DIR__ . '/../../traductor/Dector/vendor/autoload.php';
}
EOF

print_status "Autoload.php principal creado correctamente"

print_info "Verificando sintaxis de archivos principales..."
if php -l index.php > /dev/null 2>&1; then
    print_status "index.php tiene sintaxis correcta"
else
    print_error "index.php tiene errores de sintaxis"
fi

if php -l callback_bot.php > /dev/null 2>&1; then
    print_status "callback_bot.php tiene sintaxis correcta"
else
    print_error "callback_bot.php tiene errores de sintaxis"
fi

echo ""
print_status "🎉 ¡Instalación completada exitosamente!"
echo ""
print_info "Archivos autoload.php creados:"
find . -name "autoload.php" -type f | sort

echo ""
print_info "Para ejecutar el bot, asegúrate de configurar:"
echo "  1. La base de datos en database/config.php"
echo "  2. Tu token de Telegram bot"
echo "  3. Las claves de API necesarias"
