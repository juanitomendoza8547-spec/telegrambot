# Telegram Bot CC Checker

Bot de Telegram diseñado con fines educativos para validar mediante gateways números de tarjetas generadas mediante el algoritmo Luhn.

## Características

- Validación de tarjetas de crédito mediante múltiples gateways
- Integración con servicios de resolución de CAPTCHA (Capsolver)
- Sistema de encriptación para Adyen
- Procesamiento multi-hilo para validaciones masivas
- Panel de administración
- Sistema de logs y monitoreo

## Estructura del Proyecto

```
├── admin/           # Panel de administración
├── adyen/          # Integración con Adyen
├── Capsolver/      # Servicios de resolución de CAPTCHA
├── Gateway/        # Gateways de validación de tarjetas
├── Tool/           # Herramientas auxiliares
├── MultiHilos/     # Procesamiento multi-hilo
├── logs/           # Archivos de registro
├── Encryptions/    # Sistema de encriptación
└── index.php       # Archivo principal
```

## Instalación

1. Clona el repositorio
2. Configura las dependencias de Composer
3. Configura las variables de entorno necesarias
4. Ejecuta el bot

## Uso

Este proyecto está diseñado únicamente con fines educativos. Asegúrate de cumplir con todas las leyes y regulaciones aplicables en tu jurisdicción.

## Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

## Disclaimer

Este software se proporciona "tal como está" sin garantías de ningún tipo. El uso de este software es responsabilidad exclusiva del usuario. 