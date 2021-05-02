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