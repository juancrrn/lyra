<?php

/**
 * Inicialización de la aplicación
 *
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

/**
 * Habilitar escritura estricta (strict typing) en PHP para mayor seguridad.
 */
declare(strict_types = 1);

/**
 * Carga del fichero de configuración.
 */
require_once __DIR__ . '/config.php';

/**
 * Habilitar errores para depuración.
 */
if (LYRA_DEV_MODE) {
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

/**
 * Configuración de codificación y zona horaria.
 */
ini_set('default_charset', 'UTF-8');
setlocale(LC_ALL, "es_ES", 'Spanish_Spain', 'Spanish');
date_default_timezone_set('Europe/Madrid');

/**
 * Función para depuración rápida.
 */
function dd($var)
{
    var_dump($var);

    die();
}

/**
 * Función para depuración rápida con nombre de fichero y número de línea.
 */
function ddl(?string $message, mixed $var)
{
    $bt = debug_backtrace();
    $caller = array_shift($bt);

    echo 'ddl(): ' . $caller['file'] . ':' . $caller['line'] . "\n";

    if (! is_null($message)) {
        echo $message . "\n";
    }

    if (! is_null($var)) {
        var_dump($var);
    }

    die();
}

/**
 * Cargar dependencias con Composer.
 */
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Inicialización de la instancia de la aplicación.
 */
$app = Juancrrn\Lyra\Common\App::getSingleton();

$app->init(
    [
        'host' => LYRA_DB_HOST,
        'user' => LYRA_DB_USER,
        'password' => LYRA_DB_PASSWORD,
        'name' => LYRA_DB_NAME
    ],

    LYRA_ROOT,
    LYRA_URL,
    LYRA_PATH_BASE,
    LYRA_NAME,

    LYRA_DEF_PASSWORD,

    LYRA_DEV_MODE,

    [
        'enable'            => LYRA_EMAIL_ENABLE,
        'smtp_host'         => LYRA_EMAIL_SMTP_HOST,
        'smtp_port'         => LYRA_EMAIL_SMTP_PORT,
        'smtp_user'         => LYRA_EMAIL_SMTP_USER,
        'smtp_password'     => LYRA_EMAIL_SMTP_PASSWORD,
        'no_reply'          => LYRA_EMAIL_NO_REPLY,
        'reply_to'          => LYRA_EMAIL_REPLY_TO,
        'dkim_domain'       => LYRA_EMAIL_DKIM_DOMAIN,
        'dkim_selector'     => LYRA_EMAIL_DKIM_SELECTOR,
        'dkim_private_key'  => LYRA_EMAIL_DKIM_PRIVATE_KEY,
        'dkim_private_key_passphrase' => LYRA_EMAIL_DKIM_PRIVATE_KEY_PASSPHRASE
    ]
);