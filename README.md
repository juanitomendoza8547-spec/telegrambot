<div align="center">

![Header](https://capsule-render.vercel.app/api?type=waving&color=gradient&customColorList=0,1,2,3&height=300&section=header&text=CC%20Checker%20Bot&fontSize=70&animation=fadeIn&fontAlignY=38&desc=Educational%20Credit%20Card%20Validation%20Bot&descAlignY=51&descAlign=62&fontColor=000000&descFontColor=000000)

[![GitHub stars](https://img.shields.io/github/stars/mat1520/telegram-bot-cc-checker-.svg?style=for-the-badge&logo=github&logoColor=white&color=DC143C&labelColor=000000)](https://github.com/mat1520/telegram-bot-cc-checker-/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/mat1520/telegram-bot-cc-checker-.svg?style=for-the-badge&logo=github&logoColor=white&color=FF0000&labelColor=1a1a1a)](https://github.com/mat1520/telegram-bot-cc-checker-/network)
[![GitHub issues](https://img.shields.io/github/issues/mat1520/telegram-bot-cc-checker-.svg?style=for-the-badge&logo=github&logoColor=white&color=B22222&labelColor=000000)](https://github.com/mat1520/telegram-bot-cc-checker-/issues)
[![License](https://img.shields.io/github/license/mat1520/telegram-bot-cc-checker-.svg?style=for-the-badge&logo=mit&logoColor=white&color=8B0000&labelColor=1a1a1a)](LICENSE)

[![PHP](https://img.shields.io/badge/PHP-7.4+-FF0000?style=for-the-badge&logo=php&logoColor=white&labelColor=000000)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-DC143C?style=for-the-badge&logo=mysql&logoColor=white&labelColor=1a1a1a)](https://mysql.com)
[![Telegram](https://img.shields.io/badge/Telegram-Bot-B22222?style=for-the-badge&logo=telegram&logoColor=white&labelColor=000000)](https://telegram.org)

</div>

## 🎯 Descripción

Bot de Telegram diseñado con **fines educativos** para validar mediante gateways números de tarjetas generadas mediante el algoritmo Luhn. Este proyecto demuestra la implementación de sistemas de validación y procesamiento de pagos en un entorno controlado.

## ✨ Características

<table>
<tr>
<td>

### 🔥 **Funcionalidades Principales**
- 💳 **Validación Multi-Gateway** - Soporte para múltiples procesadores de pago
- 🧩 **Resolución de CAPTCHA** - Integración con Capsolver
- 🔐 **Encriptación Adyen** - Sistema de encriptación seguro
- ⚡ **Procesamiento Multi-hilo** - Validaciones masivas eficientes
- 📊 **Panel de Administración** - Control total del sistema
- 📝 **Sistema de Logs** - Monitoreo completo de actividades

</td>
<td>

### 🛠️ **Tecnologías**
- 🐘 **PHP 7.4+** - Backend robusto
- 🗄️ **MySQL/MariaDB** - Base de datos confiable
- 🤖 **Telegram Bot API** - Interfaz de usuario
- 🌐 **cURL & HTTP Clients** - Comunicación con APIs
- 🔧 **Composer** - Gestión de dependencias
- 📦 **Multi-threading** - Procesamiento paralelo

</td>
</tr>
</table>

## 📁 Estructura del Proyecto

```bash
📦 telegram-bot-cc-checker
├── 🗃️ database/              # Configuración de base de datos
│   ├── 📄 database_structure.sql    # Estructura de BD
│   └── ⚙️ config_example.php        # Configuración de ejemplo
├── 🏠 admin/                 # Panel de administración
├── 💳 adyen/                 # Integración con Adyen
├── 🔓 Capsolver/             # Servicios de resolución de CAPTCHA
├── 🌐 Gateway/               # Gateways de validación
│   ├── 💰 CCN/              # Gateways premium con cargo
│   ├── 💳 CCN CHARGED/      # Gateways con cargo confirmado
│   ├── 🆓 Free/             # Gateways gratuitos
│   ├── ⚙️ Funtcion/         # Funciones de gateway
│   └── 📊 mass/             # Procesamiento masivo
├── 🛠️ Tool/                  # Herramientas auxiliares
├── ⚡ MultiHilos/            # Procesamiento multi-hilo
├── 🔐 Encryptions/           # Sistema de encriptación
├── 📋 logs/                  # Archivos de registro
├── 🌍 traductor/             # Sistema de traducción
└── 📄 index.php              # Archivo principal
```

## 🚀 Instalación Rápida

### 📋 Prerrequisitos

```bash
✅ PHP 7.4 o superior
✅ MySQL/MariaDB 5.7+
✅ Composer
✅ Extensiones PHP: curl, mysqli, json, mbstring
✅ Bot de Telegram (Token de @BotFather)
```

### 🔧 Pasos de Instalación

1. **📥 Clonar el repositorio**
```bash
git clone https://github.com/mat1520/telegram-bot-cc-checker-.git
cd telegram-bot-cc-checker-
```

2. **📦 Instalar dependencias**
```bash
composer install
```

3. **🗄️ Configurar la base de datos**
```bash
# Importar la estructura de BD
mysql -u tu_usuario -p tu_base_datos < database/database_structure.sql
```

4. **⚙️ Configurar el entorno**
```bash
# Copiar el archivo de configuración
cp database/config_example.php config.php
# Editar config.php con tus datos
```

5. **🔑 Configurar las credenciales**
```php
// En config.php
define('DB_HOST', 'tu_host');
define('DB_USERNAME', 'tu_usuario');
define('DB_PASSWORD', 'tu_contraseña');
define('DB_NAME', 'tu_base_datos');
$botToken = "TU_BOT_TOKEN";
$Mi_Id = "TU_TELEGRAM_ID";
```

6. **🚀 Iniciar el bot**
```bash
php index.php
```

## 📱 Uso del Bot

### 🎮 Comandos Principales

| Comando | Descripción | Ejemplo |
|---------|-------------|---------|
| `!chk` | Validación básica | `!chk 4111111111111111\|12\|25\|123` |
| `!mass` | Procesamiento masivo | `!mass lista_de_tarjetas.txt` |
| `!bin` | Información del BIN | `!bin 411111` |
| `!gen` | Generar tarjetas | `!gen 411111xxxxxxxxxx` |

### 👨‍💼 Comandos de Administración

| Comando | Descripción | Acceso |
|---------|-------------|--------|
| `.ban` | Banear usuario | 🔴 Admin |
| `.gn` | Generar keys premium | 🔴 Owner |
| `.stats` | Estadísticas del sistema | 🟡 Admin |

## 🛡️ Seguridad y Configuración

<details>
<summary>🔒 <strong>Configuración de Seguridad</strong></summary>

### 🔐 Variables de Entorno Críticas

Asegúrate de configurar correctamente:

- ✅ **Token del Bot**: Obtenido de @BotFather
- ✅ **Credenciales de BD**: Usuario, contraseña y host
- ✅ **IDs de Administrador**: Control de acceso
- ✅ **APIs Externas**: Keys de servicios de terceros

### 🛡️ Medidas de Seguridad

- 🔒 Encriptación de datos sensibles
- 🚫 Validación de entrada de usuarios
- 📝 Logging completo de actividades
- 🔐 Control de acceso por roles
- 🚨 Sistema de baneos automático

</details>

## 🤝 Contribuir

<div align="center">

[![Contribuir](https://img.shields.io/badge/🔥%20¿QUIERES%20CONTRIBUIR?-¡ÚNETE%20AL%20PROYECTO!-red?style=for-the-badge&logo=github&logoColor=white&labelColor=black)](https://github.com/mat1520/telegram-bot-cc-checker-/pulls)
[![Pull Requests](https://img.shields.io/badge/Pull%20Requests-Bienvenidos-FF0000?style=for-the-badge&logo=git&logoColor=white&labelColor=000000)](https://github.com/mat1520/telegram-bot-cc-checker-/pulls)
[![Issues](https://img.shields.io/badge/Reportar%20Issues-Ayúdanos%20a%20Mejorar-DC143C?style=for-the-badge&logo=github&logoColor=white&labelColor=1a1a1a)](https://github.com/mat1520/telegram-bot-cc-checker-/issues)

</div>

### 📝 Cómo Contribuir

1. 🍴 Fork el proyecto
2. 🌿 Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. 💻 Commitea tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. 📤 Push a la rama (`git push origin feature/AmazingFeature`)
5. 🔃 Abre un Pull Request

## 💝 Donaciones y Apoyo

<div align="center">

### 🎁 **¡Apoya el Desarrollo!**

Si este proyecto te ha sido útil, considera hacer una donación para apoyar el desarrollo continuo.

[![PayPal](https://img.shields.io/badge/PayPal-Donar-FF0000?style=for-the-badge&logo=paypal&logoColor=white&labelColor=000000)](https://paypal.com/paypalme/ArielMelo200?country.x=EC&locale.x=es_XC)
[![Ko-fi](https://img.shields.io/badge/Ko--fi-Apoyar-DC143C?style=for-the-badge&logo=ko-fi&logoColor=white&labelColor=1a1a1a)](https://ko-fi.com/mat1520)

### 🌟 **Otras Formas de Apoyar**

- ⭐ **Dale una estrella** al repositorio
- 🍴 **Fork** el proyecto
- 📢 **Comparte** con otros desarrolladores
- 🐛 **Reporta bugs** que encuentres
- 💡 **Sugiere mejoras** nuevas

</div>

## 📞 Contacto y Comunidad

<div align="center">

### 🌐 **Únete a Nuestra Comunidad**

[![Telegram](https://img.shields.io/badge/Telegram-Contacto-FF0000?style=for-the-badge&logo=telegram&logoColor=white&labelColor=000000)](https://t.me/MAT3810)
[![GitHub](https://img.shields.io/badge/GitHub-Perfil-DC143C?style=for-the-badge&logo=github&logoColor=white&labelColor=1a1a1a)](https://github.com/mat1520)

### 📧 **Información de Contacto**

- 👨‍💻 **Desarrollador**: [mat1520](https://github.com/mat1520)
- 💬 **Telegram**: [@MAT3810](https://t.me/MAT3810)
- 🌐 **Repositorio**: [telegram-bot-cc-checker](https://github.com/mat1520/telegram-bot-cc-checker-)

</div>

## ⚖️ Licencia

<div align="center">

[![License: MIT](https://img.shields.io/badge/License-MIT-FF0000.svg?style=for-the-badge&logoColor=white&labelColor=000000)](https://opensource.org/licenses/MIT)

Este proyecto está licenciado bajo la **Licencia MIT** - mira el archivo [LICENSE](LICENSE) para más detalles.

</div>

## ⚠️ Disclaimer Legal

<div align="center">

### 🎓 **SOLO PARA FINES EDUCATIVOS**

</div>

> **⚠️ AVISO IMPORTANTE**: Este software se proporciona **exclusivamente con fines educativos y de investigación**. 
> 
> 🔴 **PROHIBIDO**:
> - ❌ Uso para actividades ilegales
> - ❌ Fraude o robo de tarjetas
> - ❌ Violación de términos de servicio
> - ❌ Cualquier actividad maliciosa
> 
> ✅ **PERMITIDO**:
> - 📚 Aprendizaje de seguridad
> - 🔍 Investigación académica
> - 🛡️ Testing de seguridad autorizado
> - 💻 Desarrollo de sistemas seguros

**📖 El usuario es completamente responsable del uso que haga de este software.** Los desarrolladores no se hacen responsables de cualquier uso indebido o ilegal.

---

<div align="center">

![Footer](https://capsule-render.vercel.app/api?type=waving&color=gradient&customColorList=0,1,2,3&height=100&section=footer&fontColor=FF0000)

**⭐ Si te gustó el proyecto, no olvides darle una estrella ⭐**

**🔄 Última actualización**: Julio 2025

</div> 