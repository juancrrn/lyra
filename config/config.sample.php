<?php

/**
 * Configuración de la aplicación
 *
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

/**
 * Host, usuario, contraseña y nombre de la base de datos.
 */
define('LYRA_DB_HOST',       'localhost');
define('LYRA_DB_USER',       '');
define('LYRA_DB_PASSWORD',   '');
define('LYRA_DB_NAME',       '');

/**
 * Directorio raíz, URL y nombre de la instalación.
 */
define('LYRA_ROOT',          __DIR__ . '/../');
define('LYRA_URL',           'https://');
define('LYRA_PATH_BASE',     '');
define('LYRA_NAME',          '');

/**
 * Contraseña por defecto para las cuentas de usuario.
 */
define('LYRA_DEF_PASSWORD',  'holamundo');

/**
 * Modo de desarrollo, activa errores de PHP y MySQL.
 */
define('LYRA_DEV_MODE',      false);

/**
 * Configuración de envío de correo electrónico.
 */
define('LYRA_EMAIL_ENABLE',         false);
define('LYRA_EMAIL_SMTP_HOST',      '');
define('LYRA_EMAIL_SMTP_PORT',      '');
define('LYRA_EMAIL_SMTP_USER',      '');
define('LYRA_EMAIL_SMTP_PASSWORD',  '');
define('LYRA_EMAIL_NO_REPLY',       '');
define('LYRA_EMAIL_REPLY_TO',       '');
define('LYRA_EMAIL_DKIM_DOMAIN',    '');
define('LYRA_EMAIL_DKIM_SELECTOR',  '');
define('LYRA_EMAIL_DKIM_PRIVATE_KEY', realpath(__DIR__ . '/email/dkim_private.pem'));
define('LYRA_EMAIL_DKIM_PRIVATE_KEY_PASSPHRASE', '');